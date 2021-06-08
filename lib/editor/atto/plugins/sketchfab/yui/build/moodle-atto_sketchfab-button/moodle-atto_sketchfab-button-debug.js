YUI.add('moodle-atto_sketchfab-button', function (Y, NAME) {

// This file is part of Moodle - http://mdl.org/
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
// along with mdl.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    atto_sketchfab
 * @copyright  2015 Jetha Chan <jetha@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_sketchfab-button
 */

/**
 * Atto text editor Sketchfab plugin. Largely based upon atto_media.
 *
 * @namespace M.atto_sketchfab
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */
var COMPONENTNAME = 'atto_sketchfab',
    COMPONENTCSS = {
        URLINPUT: 'atto_sketchfab_urlentry'
    },
    SELECTORS = {
        URLINPUT: '.' + COMPONENTCSS.URLINPUT
    },
    TEMPLATE = '' +
        '<form class="atto_form">' +
            '<label for="{{elementid}}_atto_sketchfab_urlentry">{{get_string "enterurl" component}}</label>' +
            '<input class="fullwidth {{COMPONENTCSS.URLINPUT}}" type="url" ' +
            'id="{{elementid}}_atto_sketchfab_urlentry" size="32"/><br/>' +
            '<div class="mdl-align">' +
                '<br/>' +
                '<button class="submit" type="submit">{{get_string "insertmodel" component}}</button>' +
            '</div>' +
        '</form>',
    SKETCHFAB_HOME_URL = 'sketchfab.com',
    TEMPLATE_EMBED = '' +
            '<a href="{{{ mdl.assethref }}}" class="atto_sketchfab-embed-thumb">' +
            '<img src="{{{ thumbnail_url }}}" />' +
            '</a>' +
            '<div class="atto_sketchfab-embed-desc">' +
                '{{{get_string "modeldesc" mdl.component modelname=mdl.asset author=mdl.profile sketchfab=mdl.svc }}}' +
            '</div>',
    PLACEHOLDER_CLASS = 'placeholder',
    PLACEHOLDER_ID_TEXT = 'atto_sketchfab-embed-',
    TEMPLATE_EMBED_PLACEHOLDER = '' +
        '<div class="atto_sketchfab-embed ' + PLACEHOLDER_CLASS +'" id="{{ id }}">' +
        '</div>',
    NOTIFY_WARNING = 'warning',
    ERROR_NOTIFY_TIMEOUT = 3000;


Y.namespace('M.atto_sketchfab').Button = Y.Base.create(
    'button',
    Y.M.editor_atto.EditorPlugin,
    [],
    {
        /**
         * A reference to the current selection at the time that the dialogue
         * was opened.
         *
         * @property _currentSelection
         * @type Range
         * @private
         */
        _currentSelection: null,

        /**
         * A reference to the dialogue content.
         *
         * @property _content
         * @type Node
         * @private
         */
        _content: null,

        initializer: function() {
            this.addButton({
                icon: 'e/insert_sketchfab',
                iconComponent: COMPONENTNAME,
                callback: this._displayDialogue
            });
        },

        /**
         * Display the media editing tool.
         *
         * @method _displayDialogue
         * @private
         */
        _displayDialogue: function() {
            // Store the current selection.
            this._currentSelection = this.get('host').getSelection();
            if (this._currentSelection === false) {
                return;
            }

            var dialogue = this.getDialogue({
                headerContent: M.util.get_string('insertmodel', COMPONENTNAME),
                focusAfterHide: true,
                focusOnShowSelector: SELECTORS.URLINPUT
            });

            // Set the dialogue content, and then show the dialogue.
            dialogue.set('bodyContent', this._getDialogueContent())
                    .show();
        },

        /**
         * Return the dialogue content for the tool, attaching any required
         * events.
         *
         * @method _getDialogueContent
         * @return {Node} The content to place in the dialogue.
         * @private
         */
        _getDialogueContent: function() {
            var template = Y.Handlebars.compile(TEMPLATE);

            this._content = Y.Node.create(template({
                component: COMPONENTNAME,
                elementid: this.get('host').get('elementid'),
                COMPONENTCSS: COMPONENTCSS
            }));

            this._content.one('.submit').on('click', this._setModel, this);

            return this._content;
        },

        _getNextAvailableId: function() {

            var str = "";
            var found = false;
            var i = 0;
            while (!found) {
                str = PLACEHOLDER_ID_TEXT + (i++);

                found = Y.one('#' + str) === null;
            }

            return str;
        },

        /**
         * Update the model in the contenteditable.
         *
         * @method _setModel
         * @param {EventFacade} e
         * @private
         */
        _setModel: function(e) {
            e.preventDefault();
            this.getDialogue({
                focusAfterHide: null
            }).hide();

            var form = e.currentTarget.ancestor('.atto_form'),
                url = form.one(SELECTORS.URLINPUT).get('value'),
                host = this.get('host'),
                self = this;

            var urlok = url !== '' && url.indexOf(SKETCHFAB_HOME_URL) > -1;

            if (urlok) {

                var tokens = url.split('/');
                var modeltoken = tokens[tokens.length - 1];

                // Insert a placeholder at the focus point and then get a reference to it as a YUI Node.
                var placeholderid = this._getNextAvailableId();
                var templateplaceholder = Y.Handlebars.compile(TEMPLATE_EMBED_PLACEHOLDER);
                var newnodehtml = Y.Node.create(templateplaceholder({ id: placeholderid })).get('outerHTML');
                host.setSelection(self._currentSelection);
                host.insertContentAtFocusPoint(newnodehtml);
                var placeholder = Y.one('#' + placeholderid);

                // Kick off a request to Sketchfab's API.
                Y.io(
                    M.cfg.wwwroot + '/lib/editor/atto/plugins/sketchfab/api.php?modelid=' + modeltoken,
                    {
                        on: {
                            success: function (id, o) {
                                var sfdata = Y.JSON.parse(o.responseText);
                                sfdata.mdl = {};
                                sfdata.mdl.component = COMPONENTNAME;

                                var linkmeta = '?utm_source=oembed&utm_medium=embed&utm_campaign=' + modeltoken;

                                sfdata.mdl.assethref = 'http://www.sketchfab.com' +
                                    '/models/' +
                                    modeltoken +
                                    linkmeta;

                                sfdata.mdl.asset =
                                    '<a href="' +
                                    sfdata.mdl.assethref +
                                    '" target="_blank">' +
                                    sfdata.title +
                                    '</a>';
                                sfdata.mdl.profile =
                                    '<a href="' +
                                    sfdata.author_url + linkmeta +
                                    '" target="_blank">' +
                                    sfdata.author_name +
                                    '</a>';
                                sfdata.mdl.svc =
                                    '<a href="' +
                                    'http://www.sketchfab.com' + linkmeta +
                                    '" target="_blank">' +
                                    sfdata.provider_name +
                                    '</a>';

                                var template = Y.Handlebars.compile(TEMPLATE_EMBED);
                                var modelhtml = Y.Node.create(template(sfdata));

                                placeholder.removeClass(PLACEHOLDER_CLASS);
                                modelhtml.appendTo(placeholder);

                                self.markUpdated();
                            },
                            failure: function (id, o) {
                                var sfdata = Y.JSON.parse(o.responseText);

                                // Remove the placeholder.
                                placeholder.remove(true);
                                self.markUpdated();

                                if (host === null) {
                                    host = self.get('host');
                                }

                                host.showMessage(M.util.get_string('error', 'webservice', sfdata.detail.__all__[0]),
                                        NOTIFY_WARNING, ERROR_NOTIFY_TIMEOUT);
                            }
                        }
                    }
                );
            }
        }

    }
);


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
