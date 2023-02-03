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

/* eslint space-before-function-paren: 0 */

/**
 * Load the format_tiles JavaScript for the course edit settings page /course/edit.php?id=xxx
 *
 * @module      format_tiles/completion
 * @copyright   2018 David Watson {@link http://evolutioncode.uk}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/templates", "core/config", "core/ajax", "core/str", "core_course/manual_completion_toggle"],
    function ($, Templates, config, ajax, str, coreManualCompletion) {
        "use strict";

        var courseId;
        const dataKeys = {
            cmid: "data-cmid",
            numberComplete: "data-numcomplete",
            numberOutOf: "data-numoutof",
            section: "data-section",
            completionState: "data-toggletype"
        };

        const Selector = {
            launchModuleModal: '[data-action="launch-tiles-module-modal"]',
            launchResourceModal: '[data-action="launch-tiles-resource-modal"]',
            pageContent: "#page-content",
            regionMain: "#region-main",
            resourceModule: '.activity.resource',
            completeonevent: ".completeonevent",
            completeonview: ".completeonview",
            activity: "li.activity",
            section: "li.section.main",
            togglecompletion: '[data-action="toggle-manual-completion"]',
            tileId: "#tile-",
            progressIndicatorId: '#tileprogress-',
            tile: '.tile',
            spacer: '.spacer',
            availabilityinfo: '.availabilityinfo',
            sectionId: '#section-'
        };

        var isBlurred = false;

        /**
         * When completion is changed it may be necessary to re-render a progress indicator.
         * This helps assemble the data.
         * @param {number} tileId which tile is this for
         * @param {number} numComplete how many items has the user completed
         * @param {number} outOf how many items are there to complete
         * @param {boolean} asPercent should we show this as a percentage
         * @returns {{}}
         */
        var progressTemplateData = function (tileId, numComplete, outOf, asPercent) {
            var data = {
                tileid: tileId,
                numComplete: numComplete,
                numOutOf: outOf,
                showAsPercent: asPercent,
                percent: outOf > 0 ? Math.round(numComplete / outOf * 100) : 0,
                percentCircumf: 106.8,
                percentOffset: outOf > 0 ? Math.round(((outOf - numComplete) / outOf) * 106.8) : 0,
                isComplete: false,
                isSingleDigit: false,
                hastilephoto: $(Selector.tileId + tileId).hasClass("phototile"),
            };
            if (tileId === 0) {
                data.isOverall = 1;
            } else {
                data.isOverall = 0;
            }
            if (outOf > 0 && numComplete >= outOf) {
                data.isComplete = true;
            }
            if (data.percent < 10) {
                data.isSingleDigit = true;
            }
            return data;
        };

        /**
         * When a progress change happens, e.g. an item is marked as complete or not, this fires.
         * It changes the current tile's progress up or down by 1 according to the progressChange arg.
         * @param {int} sectionNum the number of this tile/section.
         * @param {object} tileProgressIndicator the indicator for this tile
         * @param {int} newTileProgressValue the new value
         */
        var changeProgressIndicatorSection = function(sectionNum, tileProgressIndicator, newTileProgressValue) {
            if (newTileProgressValue < 0 || newTileProgressValue > tileProgressIndicator.attr(dataKeys.numberOutOf)) {
                // If we are already at zero, do not reduce.  May happen rarely if user presses repeatedly.
                // Will not cause a long term issue as will be resolved when user refreshes page.
                return;
            }

            if (!sectionNum) {
                // Section zero doesn't have a section progress indicator.
                return;
            }

            // Render and replace the progress indicator for *this tile*.
            Templates.render("format_tiles/progress", progressTemplateData(
                sectionNum,
                newTileProgressValue,
                parseInt(tileProgressIndicator.attr(dataKeys.numberOutOf)),
                tileProgressIndicator.hasClass("percent")
            )).done(function (html) {
                // Need to repeat jquery selector as it is being replaced (replacwith).
                tileProgressIndicator.replaceWith(html);

            });
        };

        const setOverallProgressIndicator = function(newValue, outOf) {
            // Render and replace the *overall* progress indicator for the *whole course*.
            Templates.render("format_tiles/progress", progressTemplateData(
                0, newValue, outOf, true
            )).done(function (html) {
                $("#tileprogress-0").replaceWith(html).fadeOut(0).animate({opacity: 1}, 500);
            });
        };

        /**
         * Trigger an event so that other JS modules can be notified to check completion status.
         * Used to refresh section contents when completion is checked.
         * Can also be used by other components e.g. blocks that show completion.
         * @param {number} sectionNum the number of the section where completion changed.
         * @param {number} cmid the course module where completion changed.
         */
        const triggerCompletionChangedEvent = function (sectionNum, cmid) {
            $(document).trigger('format-tiles-completion-changed', {section: sectionNum, cmid: cmid});
        };

        /**
         * If we have called format_tiles_get_section_information then we need to add the result to the DOM.
         * @param {array} sections the section in
         * @param {number} overallcomplete how many activities complete in the section overall
         * @param {number}overalloutof how many activities in the section overall
         */
        const updateSectionsInfo = function(sections, overallcomplete, overalloutof) {
            sections.forEach(sec => {
                const tile = $(Selector.tileId + sec.sectionnum);
                // If this tile is now unrestricted / visible, give it the right classes.
                if (sec.isavailable && tile.hasClass('tile-restricted')) {
                    tile.removeClass('tile-restricted');
                } else if (!sec.isavailable) {
                    tile.addClass('tile-restricted');
                }
                if (sec.isclickable && !tile.hasClass('tile-clickable')) {
                    tile.addClass('tile-clickable');
                } else if (!sec.isclickable && tile.hasClass('tile-clickable')) {
                    tile.removeClass('tile-clickable');
                }
                if (sec.iscomplete) {
                    tile.addClass('is-complete');
                } else {
                    tile.removeClass('is-complete');
                }
                // Now re-render the progress indicator if necessary with correct data.
                const progressIndicator = $(Selector.progressIndicatorId + sec.sectionnum);
                changeProgressIndicatorSection(sec.sectionnum, progressIndicator, sec.numcomplete);
                setOverallProgressIndicator(overallcomplete, overalloutof);

                // Finally change or re-render the availability message if necessary.
                const availabilityInfoDiv = tile.find(Selector.availabilityinfo);
                if (availabilityInfoDiv.length > 0 && sec.isavailable && !sec.availabilitymessage) {
                    // Display no message any more.
                    availabilityInfoDiv.fadeOut();
                } else if (!sec.isavailable && sec.availabilitymessage) {
                    // Sec is not available and we have a message to display.
                    if (availabilityInfoDiv.length > 0) {
                        availabilityInfoDiv.html = 'NEW' + sec.availabilitymessage;
                        availabilityInfoDiv.fadeIn();
                    } else {
                        Templates.render("format_tiles/availability_info", {
                            availabilitymessage: sec.availabilitymessage,
                            visible: true
                        }).done(function (html) {
                            // Need to repeat jquery selector as it is being replaced (replacwith).
                            progressIndicator.replaceWith(html);

                        });
                    }
                }
            });
        };

        /**
         * Sometimes we must check the availability and completion status of/some all tiles using AJAX.
         * This might happen if for example a tile expands and some embedded activities are then complete.
         * Other tiles might use the completion of a previous tile for their availability.
         * This especially applies if teh H5P filter is being used to display embedded H5P in labels.
         * @param {Number[]} sectionNums
         */
        var updateTileInformation = function (sectionNums) {
            if (sectionNums === undefined) {
                // Use all sections if no arg.
                sectionNums = $(Selector.tile).not(Selector.spacer).map((i, t) => {
                    return parseInt($(t).attr(dataKeys.section));
                }).toArray();
            }
            ajax.call([{
                methodname: "format_tiles_get_section_information",
                args: {
                    courseid: courseId,
                    sectionnums: sectionNums
                }
            }])[0].done((res) => {
                    updateSectionsInfo(res.sections, res.overall.complete, res.overall.outof);
                })
                .fail(err => {
                    require(["core/log"], function(log) {
                        log.debug(
                            "Failed to get section information to check completion status of section"
                        );
                        log.debug(err);
                    });
                });
        };

        return {
            init: function (courseIdInit) {
                courseId = courseIdInit;
                $(document).ready(function () {
                    var loadingString = '...';
                    str.get_strings([{key: "loading", component: "format_tiles"}]).done(function (s) {
                        loadingString = s[0] + '  ...';
                    });
                    // Included like this so that later dynamically added boxes are covered.
                    $("body").on("click", Selector.togglecompletion, function (e) {
                        // If this is a subtile, replace button with a spinner pending reload of activities over JS.
                        // Otherwise the core JS will replace with its own with different style.
                        // See core_course/manual_completion_toggle.
                        const currentTarget = $(e.currentTarget);
                        if (currentTarget.closest('.section').hasClass('subtiles')) {
                            currentTarget.replaceWith(
                                '<div class="spinner-grow spinner-grow-sm text-secondary mt-2 mr-2 pull-right"'
                                + ' role="status"><span class="sr-only">' + loadingString + '</span></div>'
                            );
                        }
                    });

                    var pageContent = $("#page-content");
                    if (pageContent.length === 0) {
                        // Some themes e.g. RemUI do not have a #page-content div, so use #region-main.
                        pageContent = $("#region-main");
                    }
                    pageContent
                        .on("click", Selector.launchModuleModal + ", " + Selector.launchResourceModal, function (e) {
                            var clickedActivity = $(e.currentTarget).closest(Selector.activity);
                            if (clickedActivity.hasClass("completeonview")) {
                                const sectionNum = clickedActivity.closest(Selector.section).attr(dataKeys.section);
                                const cmid = clickedActivity.attr('data-cmid');
                                triggerCompletionChangedEvent(
                                    sectionNum ? parseInt(sectionNum) : 0, cmid ? parseInt(cmid) : 0
                                );
                            }
                        });

                    // When the user returns to the main tab/window, refresh completion data.
                    // (Completion may have changed since the last focus, e.g. activity opened in new window).
                    $(window).on('focus', function() {
                        if (isBlurred) {
                            // We are returning to current window.
                            const openSection = $('li.section.state-visible').attr('data-section');
                            isBlurred = false;
                            triggerCompletionChangedEvent(openSection ? parseInt(openSection) : 0, 0);
                        }
                    });
                    $(window).on('blur', function() {
                        isBlurred = true;
                    });

                    // When behat tests are running, for whatever reason core completion is not initialised, so we do it here.
                    coreManualCompletion.init();
                });
            },
            triggerCompletionChangedEvent: function(sectionNum, cmid) {
                triggerCompletionChangedEvent(sectionNum, cmid);
            },
            updateTileInformation: function(sectionNumbers) {
                try {
                    updateTileInformation(sectionNumbers);
                } catch (err) {
                    require(["core/log"], function(log) {
                        log.debug(err);
                    });
                }
            },
            updateSectionsInfo: function(sections, overallcomplete, overalloutof) {
                updateSectionsInfo(sections, overallcomplete, overalloutof);
            }
        };
    }
);
