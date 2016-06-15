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
 * @package    atto_wordimport
 * @copyright  2015 Eoin Campbell
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_wordimport-button
 */

/**
 * Atto text editor import Microsoft Word file plugin.
 *
 * This plugin adds the ability to drop a Word file in and have it automatically
 * convert the contents into XHTML and into the text box.
 *
 * @namespace M.atto_wordimport
 * @class Button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_wordimport',
    // @codingStandardsIgnoreStart
    IMAGETEMPLATE = '' +
        '<img src="{{url}}" alt="{{alt}}" ' +
            '{{#if width}}width="{{width}}" {{/if}}' +
            '{{#if height}}height="{{height}}" {{/if}}' +
            '{{#if presentation}}role="presentation" {{/if}}' +
            'style="{{alignment}}{{margin}}{{customstyle}}"' +
            '{{#if classlist}}class="{{classlist}}" {{/if}}' +
            '{{#if id}}id="{{id}}" {{/if}}' +
            '/>';
    // @codingStandardsIgnoreEnd

Y.namespace('M.atto_wordimport').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
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
     * Add event listeners.
     *
     * @method initializer
     */

    initializer: function() {
        // If we don't have the capability to view then give up.
        if (this.get('disabled')){
            return;
        }

        this.addButton({
            icon: 'wordimport',
            iconComponent: COMPONENTNAME,
            callback: function() {
                    this.get('host').showFilepicker('link', this._handleWordFileUpload, this);
            },
            callbackArgs: 'wordimport'
        });
        this.editor.on('drop', this._handleWordFileDragDrop, this);
    },

    /**
     * Handle a Word file upload via the filepicker
     *
     * @method _handleWordFileUpload
     * @param {object} params The parameters provided by the filepicker.
     * containing information about the file.
     * @private
     */
    _handleWordFileUpload: function(params) {
        var host = this.get('host'),
            fpoptions = host.get('filepickeroptions'),
            options = fpoptions.link,
            self = this,
            xhr = new XMLHttpRequest();

        if (params.url === '') {
            return false;
        }

        // Return if selected file doesn't have Word 2010 suffix.
        if (/\.doc[xm]$/.test(params.file) === false) {
            Y.log(M.util.get_string('xmlnotsupported', COMPONENTNAME) + params.file);
            return false;
        }

        // Kick off a XMLHttpRequest.
        xhr.onreadystatechange = function() {
            var upload_result;

            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    upload_result = JSON.parse(xhr.responseText);
                    if (upload_result) {
                        if (upload_result.error) {
                            return new M.core.ajaxException(upload_result);
                        }

                        // Insert content from file at current focus point.
                        host.insertContentAtFocusPoint(upload_result.html);
                        self.markUpdated();
                    }
                } else {
                    Y.use('moodle-core-notification-alert', function() {
                        new M.core.alert({message: M.util.get_string('servererror', 'moodle')});
                    });
                }
            }
        };

        var filename = 'filename=' + params.file,
            contextID = 'ctx_id=' + options.context.id,
            itemid = 'itemid=' + options.itemid,
            sessionkey = 'sesskey=' + M.cfg.sesskey,
            phpImportURL = '/lib/editor/atto/plugins/wordimport/import.php?';
        Y.log('WordImport: File info: ' + contextID + ';' + itemid + ';' + filename);
        xhr.open("GET", M.cfg.wwwroot + phpImportURL + contextID + '&' + itemid + '&' + filename + '&' + sessionkey, true);
        xhr.send();

        return true;
    },

    /**
     * Handle a drag and drop event with a Word file.
     *
     * @method _handleWordFileDragDrop
     * @param {EventFacade} e
     * @private
     */
    _handleWordFileDragDrop: function(e) {

        var self = this,
            host = this.get('host'),
            template = Y.Handlebars.compile(IMAGETEMPLATE),
            requiredFileType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

        host.saveSelection();
        e = e._event;

        // Only handle the event if a Word 2010 file was dropped in.
        var handlesDataTransfer = (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length);
        if (handlesDataTransfer && requiredFileType === e.dataTransfer.files[0].type) {
            var options = host.get('filepickeroptions').link,
                savepath = (options.savepath === undefined) ? '/' : options.savepath,
                formData = new FormData(),
                timestamp = 0,
                uploadid = "",
                xhr = new XMLHttpRequest(),
                imagehtml = "",
                keys = Object.keys(options.repositories);

            Y.log('WordImport: Word 2010 (.docx) file dragged in ' + e.dataTransfer.files[0].type);
            e.preventDefault();
            e.stopPropagation();

            formData.append('repo_upload_file', e.dataTransfer.files[0]);
            formData.append('itemid', options.itemid);

            // List of repositories is an object rather than an array.  This makes iteration more awkward.
            for (var i = 0; i < keys.length; i++) {
                if (options.repositories[keys[i]].type === 'upload') {
                    formData.append('repo_id', options.repositories[keys[i]].id);
                    break;
                }
            }
            formData.append('env', options.env);
            formData.append('sesskey', M.cfg.sesskey);
            formData.append('client_id', options.client_id);
            formData.append('savepath', savepath);
            formData.append('ctx_id', options.context.id);
            Y.log('WordImport: options.context.id = ' + options.context.id);

            // Insert spinner as a placeholder.
            timestamp = new Date().getTime();
            uploadid = 'moodleimage_' + Math.round(Math.random() * 100000) + '-' + timestamp;
            Y.log('WordImport: _handleWordFileDragDrop initial uploadid = ' + uploadid);
            host.focus();
            host.restoreSelection();
            imagehtml = template({
                url: M.util.image_url("i/loading_small", 'moodle'),
                alt: M.util.get_string('uploading', COMPONENTNAME),
                id: uploadid
            });
            host.insertContentAtFocusPoint(imagehtml);
            self.markUpdated();

            // Kick off a XMLHttpRequest to upload the dragged-in file.
            xhr.onreadystatechange = function() {
                var placeholder = self.editor.one('#' + uploadid),
                    dragdrop_result,
                    file;
                Y.log('WordImport: _handleWordFileDragDrop referenced uploadid = ' + uploadid);

                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        dragdrop_result = JSON.parse(xhr.responseText);
                        if (dragdrop_result) {
                            if (dragdrop_result.error) {
                                if (placeholder) {
                                    placeholder.remove(true);
                                }
                                Y.log('WordImport: _handleWordFileDragDrop upload failed.');
                                Y.log('WordImport: dragdrop_result = ' + dragdrop_result);
                                Y.use('moodle-core-notification-alert', function() {
                                    new M.core.alert({message: M.util.get_string('fileuploadfailed', 'atto_wordimport')});
                                });
                                // return new M.core.ajaxException(dragdrop_result);
                            }

                            file = dragdrop_result.file;
                            if (dragdrop_result.event && dragdrop_result.event === 'fileexists') {
                                // A file with this name is already in use here - rename to avoid conflict.
                                file = dragdrop_result.newfile;
                                Y.log('WordImport: _handleWordFileDragDrop upload is a duplicate file, renaming.');
                            }

                            // Word file uploaded, so kick off another XMLHttpRequest to convert it into HTML.
                            xhr.onreadystatechange = function() {
                                var placeholder = self.editor.one('#' + uploadid),
                                    convert_result,
                                    newhtml;

                                Y.log('WordImport: _handleWordFileDragDrop referenced uploadid = ' + uploadid);
                                if (xhr.readyState === 4) {
                                    if (xhr.status === 200) {
                                        convert_result = JSON.parse(xhr.responseText);
                                        if (convert_result) {
                                            if (convert_result.error) {
                                                if (placeholder) {
                                                    placeholder.remove(true);
                                                }
                                                Y.log('WordImport: _handleWFDD: convert_result = ' + convert_result);
                                                Y.log('WordImport: _handleWFDD: typeof = ' + typeof(M.core.ajaxException));
                                                Y.use('moodle-core-notification-alert', function() {
                                                    new M.core.alert({message: M.util.get_string('fileconversionfailed',
                                                            'atto_wordimport')});
                                                });
                                                // var error_obj = M.core.ajaxException(convert_result);
                                                // return error_obj;
                                            }

                                            // Replace placeholder with actual content from Word file.
                                            newhtml = Y.Node.create(convert_result.html);
                                            if (placeholder) {
                                                placeholder.replace(newhtml);
                                            } else {
                                                self.editor.appendChild(newhtml);
                                            }
                                        }
                                    } else {
                                        Y.use('moodle-core-notification-alert', function() {
                                            new M.core.alert({message: M.util.get_string('servererror', 'moodle')});
                                        });
                                    }
                                }
                            };

                            var contextID = 'ctx_id=' + options.context.id,
                                itemID = 'itemid=' + options.itemid,
                                fileName = 'filename=' + file,
                                sessKey = 'sesskey=' + M.cfg.sesskey,
                                importParams = contextID + '&' + itemID + '&' + fileName + '&' + sessKey,
                                phpImportURL = '/lib/editor/atto/plugins/wordimport/import.php?';
                            xhr.open("POST", M.cfg.wwwroot + phpImportURL + importParams, true);
                            xhr.send();
                            Y.log('WordImport: _handleWordFileDragDrop sent conversion request for ' + fileName);
                            self.markUpdated();
                        }
                    } else {
                        Y.use('moodle-core-notification-alert', function() {
                            new M.core.alert({message: M.util.get_string('servererror', 'moodle')});
                        });
                        if (placeholder) {
                            placeholder.remove(true);
                        }
                    }
                }
            };
            xhr.open("POST", M.cfg.wwwroot + '/repository/repository_ajax.php?action=upload', true);
            xhr.send(formData);
            Y.log('WordImport: File upload request sent');
        }
        return false;

    }


});
