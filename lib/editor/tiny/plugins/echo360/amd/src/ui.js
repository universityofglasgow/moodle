// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tiny Echo360 UI.
 *
 * @module      tiny_echo360/ui
 * @copyright   2023 Echo360 Inc.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalFactory from 'core/modal_factory';
import Ajax from 'core/ajax';
import Echo360Modal from 'tiny_echo360/modal';
import {getContextId} from "editor_tiny/options";
import Config from 'core/config';

let instanceOpeningSelection = null;
let instanceEditor = null;
let instanceModal = null;

/**
 * Handle action.
 *
 * @param {TinyMCE} editor
 */
export const handleAction = (editor) => {
    instanceEditor = editor;
    instanceOpeningSelection = editor.selection.getBookmark();
    displayDialogue(editor);
};

const echoEventListeners = [];

/**
 * Returns the query parameters from data as an object
 *
 * @method  getQueryParams
 * @param   {String} data from the Message object sent from the IFrame
 */
const getQueryParams = (data) => {
    try {
        // Only parse query params if data is a string value to split.
        if (data !== null && typeof data == 'string') {
            let jsonData = decodeURI(data.split('?')[1])
              .replace(/"/g, '\\"')
              .replace(/&/g, '","')
              .replace(/[=]/g, '":"')
              .replace(/\\/g, '\\\\');
            let queryParams = JSON.parse('{"' + jsonData + '"}');
            if (!queryParams.hasOwnProperty('url')) {
                return null;
            } else {
                queryParams.url = decodeURIComponent(queryParams.url);
            }
            queryParams.title = queryParams.title || queryParams.url;
            return queryParams;
        }
    } catch (err) {
        return null;
    }
};

/**
 * Inserts the users input onto the page
 *
 * @method  _doInsert
 * @param   {String} media to embed in editor
 * @private
 */
const doInsert = (media) => {
    instanceEditor.selection.moveToBookmark(instanceOpeningSelection);
    instanceEditor.execCommand('mceInsertContent', false, media);
    instanceEditor.selection.moveToBookmark(instanceOpeningSelection);
    instanceModal.destroy();
};

const receiveEchoMessage = (e, cmid) => {
    if (e.data) {
        let media = null;
        let filterLaunchUrl = Config.wwwroot + '/filter/echo360tiny/lti_launch.php';
        let queryParameters = getQueryParams(e.data);
        if (queryParameters) {
            switch (queryParameters.return_type) {
                case 'iframe':              // Public IFrame Embed.
                    media = '<iframe ' + 'src="' + queryParameters.url
                        + '" height="' + queryParameters.height
                        + '" width="' + queryParameters.width
                        + '" title="' + queryParameters.title
                        + '" allowfullscreen="allowfullscreen'
                        + '" webkitallowfullscreen="webkitallowfullscreen'
                        + '" mozallowfullscreen="mozallowfullscreen'
                        + '"></iframe> ';
                    break;
                case 'url':                 // Public Link.
                    media = '<a href="' + queryParameters.url + '" target="_blank">' + queryParameters.title + '</a> ';
                    break;
                case 'lti_launch_url':      // Authenticated Link/Embed with LTI Launch Proxy.
                    if (queryParameters.link_type === 'link') {
                        let url = filterLaunchUrl
                            + '?url=' + encodeURIComponent(queryParameters.url)
                            + '&cmid=' + cmid;
                        media = '<a href="' + url + '" target="_blank">' + queryParameters.title + '</a> ';
                    } else {
                        let url = filterLaunchUrl
                            + '?url=' + encodeURIComponent(queryParameters.url)
                            + '&cmid=' + cmid
                            + '&width=' + queryParameters.width
                            + '&height=' + queryParameters.height;
                        media = '<a href="' + url + '" target="_blank">' + queryParameters.title + '</a> ';
                    }
                    break;
                case 'homework': {           // Authenticated Homework Link with LTI Launch Proxy.
                    let url = filterLaunchUrl
                      + '?url=' + encodeURIComponent(queryParameters.url)
                      + '&cmid=' + cmid
                      + '&width=640'
                      + '&height=360';
                    media = '<a href="' + url + '" target="_blank">' + queryParameters.title + '</a> ';
                    break;
                }
                default:
                    // Return type invalid
            }

            // Only embed if media is assigned data.
            if (media !== null && media != '') {
                doInsert(media);
            }
        } else {
            // DO NOT RESET EDITOR REFERENCE IF NO QUERY PARAMETERS ARE PARSED YET.
        }
    } else {
        // No data returned from message
    }

};

/**
 * Display the link dialogue.
 *
 * @param {TinyMCE} editor
 * @returns {Promise<void>}
 */
const displayDialogue = async(editor) => {

    const contextid = getContextId(editor);
    const prom = Ajax.call([{
        methodname: 'tiny_echo360_request_lti_configuration',
        args: {
            contextid: contextid
        }
    }]);

    prom[0].done(function (ltiConfiguration) {
        ltiConfiguration['element_id'] = editor.id;

        ModalFactory.create({
            type: Echo360Modal.TYPE,
            templateContext: ltiConfiguration,
            large: true,
        }).then(function(modal) {
            instanceModal = modal;
            modal.show();
            let form = document.getElementById("echo360-form-" + editor.id);
            form.submit();
            form.parentNode.removeChild(form);
        });

        if(!echoEventListeners[editor.id]) {
            window.addEventListener(
              'message', function (e) {
                  e.stopPropagation();
                  return receiveEchoMessage(e, this.resource_link_id);
              }.bind(ltiConfiguration), true
            );
        }
        echoEventListeners[editor.id] = true;
    });
};
