/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/log', 'core/ajax', 'core/notification', 'theme_snap/ajax_notification'],
    function($, log, ajax, notification, ajaxNotify) {

        // TODO - in Moodle 3.1 we should use the core template for this.
        var addCoverImageAlert = function(id, msg) {
            var closestr =  M.util.get_string('closebuttontitle', 'moodle');
            if (!$(id).length) {
                $('#snap-coverimagecontrol').before(
                    '<div id="'+id+'" class="snap-alert-cover-image alert alert-warning" role="alert">' +
                    msg +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="'+closestr+'">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>'
                );
            }
        };

        /**
         * Get human file size from bytes.
         * http://stackoverflow.com/questions/10420352/converting-file-size-in-bytes-to-human-readable.
         * @param size
         * @returns {string}
         */
        var humanFileSize = function(size) {
            var i = Math.floor( Math.log(size) / Math.log(1024) );
            return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
        };

        /**
         * First state - image selection button visible.
         */
        var state1 = function() {
            $('#snap-changecoverimageconfirmation .ok').removeClass('ajaxing');
            $('#snap-alert-cover-image-size').remove();
            $('#snap-alert-cover-image-bytes').remove();
            $('label[for="snap-coverfiles"]').removeClass('ajaxing');
            $('#snap-changecoverimageconfirmation').removeClass('state-visible');
            $('label[for="snap-coverfiles"]').addClass('state-visible');
            $('#snap-coverfiles').val('');
        };

        /**
         * Second state - confirm / cancel buttons visible.
         */
        var state2 = function() {
            $('#snap-alert-cover-image-upload-failed').remove();
            $('#snap-changecoverimageconfirmation').removeClass('disabled');
            $('label[for="snap-coverfiles"]').removeClass('state-visible');
            $('#snap-changecoverimageconfirmation').addClass('state-visible');
            $('body').removeClass('cover-image-change');
        };

        /**
         *
         * @param {int} siteMaxBytes
         * @param {object} ajaxParams
         */
        var coverImage = function(siteMaxBytes, ajaxParams) {
            // Take a backup of what the current background image url is (if any).
            $('#page-header').data('servercoverfile', $('#page-header').css('background-image'));

            $('#changecoverimage').click(function(e) {
                e.preventDefault();
                $(this).removeClass('state-visible');
                $('label[for="snap-coverfiles"]').addClass('state-visible');
            });

            var file,
                filedata;

            $('#snap-coverfiles').on('change', function(e) {
                $('body').addClass('cover-image-change');
                var files = e.target.files; // FileList object
                if (!files.length) {
                    return;
                }

                file = files[0];

                // Only process image files.
                if (!file.type.match('image.*')) {
                    return;
                }

                var reader = new FileReader();

                $('label[for="snap-coverfiles"]').addClass('ajaxing');

                // Closure to capture the file information.
                reader.onload = (function(theFile) {
                    return function(e) {

                        // Set page header to use local version for now.
                        filedata = e.target.result;

                        // Ensure that the page-header in courses has the mast-image class.
                        $('.path-course-view #page-header').addClass('mast-image');

                        // Warn if image file size exceeds max upload size.
                        // Note: The site max bytes is intentional, as the person who can do the upload would be able to
                        // override the course upload limit anyway.
                        var maxbytes = siteMaxBytes;
                        if (theFile.size > maxbytes) {
                            // Go back to initial state and show warning about image file size.
                            state1();
                            var maxbytesstr = humanFileSize(maxbytes);
                            var message = M.util.get_string('error:coverimageexceedsmaxbytes', 'theme_snap', maxbytesstr);
                            addCoverImageAlert('snap-alert-cover-image-bytes', message);
                            return;
                        } else {
                            $('#snap-alert-cover-image-bytes').remove();
                        }

                        // Warn if image resolution is too small.
                        var img = $('<img />');
                        img = img.get(0);
                        img.src = filedata;
                        $(img).on('load', function() {
                            if (img.width < 1024) {
                                addCoverImageAlert('snap-alert-cover-image-size',
                                    M.util.get_string('error:coverimageresolutionlow', 'theme_snap')
                                );
                            } else {
                                $('#snap-alert-cover-image-size').remove();
                            }
                        });

                        $('#page-header').css('background-image', 'url(' + filedata + ')');
                        $('#page-header').data('localcoverfile', theFile.name);

                        state2();
                    };
                })(file);

                // Read in the image file as a data URL.
                reader.readAsDataURL(file);

            });
            $('#snap-changecoverimageconfirmation .ok').click(function(){

                if ($(this).parent().hasClass('disabled')) {
                    return;
                }

                $('#snap-alert-cover-image-size').remove();
                $('#snap-alert-cover-image-bytes').remove();

                $('#snap-changecoverimageconfirmation .ok').addClass('ajaxing');
                $('#snap-changecoverimageconfirmation').addClass('disabled');

                var imageData = filedata.split('base64,')[1];

                ajaxParams.imagedata = imageData;
                ajaxParams.imagefilename = file.name;

                ajax.call([
                    {
                        methodname: 'theme_snap_cover_image',
                        args: {params: ajaxParams},
                        done: function(response) {
                            state1();
                            if (!response.success && response.warning) {
                                addCoverImageAlert('snap-alert-cover-image-upload-failed', response.warning);
                            }
                        },
                        fail: function(response) {
                            state1();
                            ajaxNotify.ifErrorShowBestMsg(response);
                        }
                    }
                ], true, true);
            });
            $('#snap-changecoverimageconfirmation .cancel').click(function(){

                if ($(this).parent().hasClass('disabled')) {
                    return;
                }

                $('#page-header').css('background-image', $('#page-header').data('servercoverfile'));
                state1();
            });
            $('#snap-coverimagecontrol').addClass('snap-js-enabled');
        };

        var categoryCoverImage = function(categoryId, siteMaxBytes) {
            var ajaxParams = {imagefilename: null, imagedata: null, categoryid: categoryId,
                    courseshortname: null};

            coverImage(siteMaxBytes, ajaxParams);
        };

        /**
         * Main function
         * @param {string} courseShortName
         */
        var courseCoverImage = function(courseShortName, siteMaxBytes) {
            var ajaxParams = {imagefilename: null, imagedata: null, categoryid: null,
                    courseshortname: courseShortName};

            coverImage(siteMaxBytes, ajaxParams);
        };
        return {courseImage: courseCoverImage, categoryImage: categoryCoverImage};
    }
);
