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
 * Main Javascript module for format_tiles for when user *IS* editing.
 * See course.js for if they are not editing.
 * Handles the UI changes when tiles are selected and anything else not
 * covered by the specific modules
 *
 * @module format_tiles/edit_course
 * @copyright 2019 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 3.3
 */

/* eslint space-before-function-paren: 0 */

define(
    ["jquery", "core/config", "core/str", "core/ajax", "format_tiles/edit_browser_storage"],
    function($, config, str, ajax, browserStorageEdit) {
        "use strict";

        var courseId;

        return {
            // All args down to "filttilestowidth" are copied from course.js.
            init: function(
                courseIdInit,
                useJavascriptNav, // Set by site admin see settings.php.
                isMobile,
                sectionNum,
                useFilterButtons,
                assumeDataStoreConsent, // Set by site admin see settings.php.
                reopenLastSection, // Set by site admin see settings.php.
                userId,
                fitTilesToWidth,
                enablecompletion,
                useSubTiles,
                pageType,
                allowPhotoTiles,
                documentationurl
            ) {
                courseId = courseIdInit;
                // Some args are strings or ints but we prefer bool.  Change to bool now as they are passed on elsewhere.
                assumeDataStoreConsent = assumeDataStoreConsent === "1";
                // This is also called from lib.php, via edit_form_helper, if user is on course/edit.php or editsection.php.
                require(['format_tiles/edit_icon_picker'], function(iconPicker) {
                    iconPicker.init(courseId, pageType, allowPhotoTiles, documentationurl);
                });

                $(document).ready(function() {

                    // If the user preference is for JS off, or site admin has disabled, or user is mobile, no JS nav.
                    if (useJavascriptNav && !isMobile) {
                        var collapsingAllSectionFromURL = (window.location.search).indexOf("expanded=-1") !== -1;
                        var finalSectionInCourse = $("li.section.main").last().data("section");
                        browserStorageEdit.init(
                            userId,
                            courseId,
                            assumeDataStoreConsent,
                            finalSectionInCourse,
                            collapsingAllSectionFromURL
                        );
                    }

                    if (!isMobile) {
                        // Initialise tooltips shown for example when hover over tile icon "Click to change icon".
                        // But not on mobile as they make clicks harder.
                        var toolTips = $("[data-toggle=tooltip]");
                        if (toolTips.length !== 0 && typeof toolTips.tooltip == 'function') {
                            try {
                                toolTips.tooltip();
                            } catch (err) {
                                require(["core/log"], function(log) {
                                    log.debug(err);
                                });
                            }
                        }
                    }

                    const anchor = window.location.hash;
                    if (anchor) {
                        const section = anchor.replace('#section-', '', anchor);
                        const courseContent = $('#coursecontentcollapse' + section);
                        if (courseContent && !courseContent.hasClass('show')) {
                            courseContent.addClass('show');
                            $('#collapssesection' + section).removeClass('collapsed').attr('aria-expanded', true);
                        }
                    }
                });
            }
        };
    }
);