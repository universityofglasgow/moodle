YUI.add('moodle-atto_echo360attoplugin-button', function (Y, NAME) {

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

/*
 * @package    atto_echo360attoplugin
 * @copyright  COPYRIGHTINFO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_echo360attoplugin-button
 */

/**
 * Atto text editor echo360attoplugin plugin.
 *
 * @namespace M.atto_echo360attoplugin
 * @class     button
 * @extends   M.editor_atto.EditorPlugin
 */

var COMPONENT_NAME = 'atto_echo360attoplugin';
var LOG_NAME = 'atto_echo360attoplugin';
var EDITOR_ID = '';
var ECHO360 = 'echo360';
var ECHO_ICON = 'echoIcon';
var ADD_MESSAGE_HANDLER = {};
var EDITOR_INSTANCE = {};

Y.namespace('M.atto_echo360attoplugin').Button = Y.Base.create(
    'button', Y.M.editor_atto.EditorPlugin, [], {

        /**
         * Initialize the button
         *
         * @method Initializer
         */
        initializer: function () {
            // If we don't have the capability to view then give up.
            if (this.get('disabled')) {
                // It may not be disabled because of an error.
                this.get('error') && console.error(this.get('error'));
                return;
            }
            EDITOR_ID = this.editor._yuid;
            // The button will display the Dialogue modal.
            this.addButton({
                icon: 'ed/' + ECHO_ICON,
                iconComponent: 'atto_echo360attoplugin',
                buttonName: ECHO_ICON,
                callback: this._doOpen,
                callbackArgs: null,
                name: ECHO360,
                tooltip: ECHO360
            });
        },

        /**
         * Opens the modal and displays the Echo360 user library
         *
         * @method  _doOpen
         * @param   e {Object} the event object
         * @private
         */
        _doOpen: function (e) {
            e.preventDefault();
            this._resetEditorInstance();
            // Show the Dialogue modal and add an event listener for messages sent from the iframe.
            EDITOR_INSTANCE = {
                host: this.get('host'),
                editor: this.editor,
                dialogue: this.getDialogue({
                    headerContent: M.util.get_string('dialogtitle', COMPONENT_NAME),
                    focusAfterHide: this.editor,
                    height: 580,
                    width: 800
                })
            };
            EDITOR_INSTANCE.dialogue.show();
            if(!ADD_MESSAGE_HANDLER[EDITOR_ID]) {
                window.addEventListener(
                    'message', function (e) {
                        e.stopPropagation();
                        return this._receiveMessage(e);
                    }.bind(this), true
                );
            }
            ADD_MESSAGE_HANDLER[EDITOR_ID] = true;
            // Request LTI configuration.
            var result = Y.io(M.cfg.wwwroot + '/lib/editor/atto/plugins/echo360attoplugin/ajax.php', {
                context: this,
                method: 'post',
                data: {
                    sesskey: M.cfg.sesskey,
                    contextcourseid: echo360_context_course_id,
                    pagetype: moodle_page_type
                },
                timeout: 500,
                on: {
                    complete: function(id, response) {
                        if (response.status === 200) {
                            var echo360LibraryId = 'echo360-library-' + EDITOR_ID;
                            // Clear existing library from dialogue for subsequent click refresh.
                            var echo360Library = document.getElementById(echo360LibraryId);
                            if (echo360Library != null) {
                                echo360Library.parentNode.removeChild(echo360Library);
                            }
                            // Configure the LTI authentication form to target the iframe on submit.
                            var ltiConfiguration = JSON.parse(response.responseText);
                            var echo360FormId = 'echo360-form-' + EDITOR_ID;
                            var form = document.createElement('form');
                            form.setAttribute('method', 'post');
                            form.setAttribute('id', echo360FormId);
                            form.setAttribute('target', echo360LibraryId);
                            form.setAttribute('hidden', 'true');
                            form.action = ltiConfiguration.launch_url;
                            for (var property in ltiConfiguration) {
                                if (ltiConfiguration.hasOwnProperty(property)) {
                                    var input = document.createElement('input');
                                    input.setAttribute('type', 'text');
                                    input.setAttribute('value', ltiConfiguration[property]);
                                    input.id = input.name = property;
                                    form.appendChild(input);
                                }
                            }
                            // The form must be part of the DOM before you can submit it.
                            document.body.appendChild(form);
                            // Construct iframe for embed library request.
                            var iframe = document.createElement('iframe');
                            iframe.id = echo360LibraryId;
                            iframe.name = echo360LibraryId;
                            iframe.setAttribute('height', '500px');
                            iframe.setAttribute('width', '100%');
                            // Dialogue modal must be showing in order to append the iframe properly.
                            // It will happen so fast that the end user will not see it.
                            var dialogue = this.getDialogue();
                            dialogue.render();
                            // Append the iframe to the Dialogue modal, submit the form, then remove it.
                            dialogue.bodyNode.appendChild(iframe);
                            form.submit();
                            form.parentNode.removeChild(form);
                        } else if (response.status == 404) {
                            var dialogue = this.getDialogue();
                            dialogue.render();
                            dialogue.bodyNode.appendChild(response.responseText);
                        }
                    }
                }
            });
            this.markUpdated();
        },

        /**
         * Handle the message received from the Echo360 user library.
         *
         * @method  _receiveMessage
         * @param   e {Object} the Message object sent from the IFrame.
         * Should contain information about the media we wish to embed
         * @private
         */
        _receiveMessage: function (e) {
            if (e.data) {
                var media = null;
                var queryParameters = this._getQueryParams(e.data);
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
                                var url = echo360_filter_lti_launch_url
                                    + '?url=' + encodeURIComponent(queryParameters.url)
                                    + '&cmid=' + echo360_context_module_id;
                                media = '<a href="' + url + '" target="_blank">' + queryParameters.title + '</a> ';
                            } else {
                                var url = echo360_filter_lti_launch_url
                                    + '?url=' + encodeURIComponent(queryParameters.url)
                                    + '&cmid=' + echo360_context_module_id
                                    + '&width=' + queryParameters.width
                                    + '&height=' + queryParameters.height;
                                    media = '<a href="' + url + '" target="_blank">' + queryParameters.title + '</a> ';
                            }
                            break;
                        case 'homework':            // Authenticated Homework Link with LTI Launch Proxy.
                            var url = echo360_filter_lti_launch_url
                                + '?url=' + encodeURIComponent(queryParameters.url)
                                + '&cmid=' + echo360_context_module_id
                                + '&width=640'
                                + '&height=360';
                            media = '<a href="' + url + '" target="_blank">' + queryParameters.title + '</a> ';
                            break;
                        default:
                            console.error('Return type: ' + queryParameters.return_type + ' invalid');
                            this._resetEditorInstance();
                    }

                    // Only embed if media is assigned data.
                    if (media != null && media != '') {
                        this._doInsert(media);
                    }
                } else {
                    console.warn('No parameters returned from parsed message yet: ' + JSON.stringify(e.data));
                    // DO NOT RESET EDITOR REFERENCE IF NO QUERY PARAMETERS ARE PARSED YET.
                }
            } else {
                console.error('No data returned from message: ' + e);
                this._resetEditorInstance();
            }
        },

        /**
         * Returns the query parameters from data as an object
         *
         * @method  _getQueryParams
         * @param   data {String} from the Message object sent from the IFrame
         * @private
         */
        _getQueryParams: function (data) {
            try {
                // Only parse query params if data is a string value to split.
                if (data != null && typeof data == 'string') {
                      var queryParams = JSON.parse('{"' + decodeURI(data.split('?')[1]).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}');
                    if (!queryParams.hasOwnProperty('url')) {
                        console.error('Required parameter "url" missing from message: ' + data);
                        return null;
                    } else {
                        queryParams.url = decodeURIComponent(queryParams.url);
                    }
                    queryParams.title = queryParams.title || queryParams.url;
                    return queryParams;
                }
            } catch (err) {
                console.error('Error caught while attempting to parse query parameters in message: ' + data + '\n' + err.message);
                return null;
            }
        },

        /**
         * Inserts the users input onto the page
         *
         * @method  _doInsert
         * @param   media {String} to embed in editor
         * @private
         */
        _doInsert: function (media) {
            if (EDITOR_INSTANCE != null) {
                EDITOR_INSTANCE.dialogue.hide();
                // If no file is there to insert, don't do it.
                if (!media) {
                      return;
                }
                EDITOR_INSTANCE.editor.focus();
                EDITOR_INSTANCE.host.insertContentAtFocusPoint(media);
                this._resetEditorInstance();
                this.markUpdated();
            }
        },

        /**
         * Resets EDITOR_INSTANCE
         *
         * @method  _resetEditorInstance
         * @private
         */
        _resetEditorInstance: function () {
            EDITOR_INSTANCE = {};
        },

        _browserIsIE: function () {
            return (
            navigator.appName === 'Microsoft Internet Explorer' ||
            !!(navigator.userAgent.match(/Trident/) ||
            navigator.userAgent.match(/rv:11/)) ||
            !!document.documentMode === true ||
            navigator.userAgent.indexOf("MSIE") !== -1
            );
        }

    }, {
        ATTRS: {
            disabled: {
                value: false
            },

            usercontextid: {
                value: null
            },

            ltiConfiguration: {
                value: ''
            }
        }
    }
);


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
