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
 * Javascript Module to handle rendering of course modules (e.g. resource/PDF, resource/html, page) in modal windows
 *
 * When the user clicks a PDF course module subtile or old style resource
 * if we are using modals for it (e.g. PDF) , create, populate, launch and size the modal
 *
 * @module      format_tiles/course_mod_modal
 * @copyright   2018 David Watson {@link http://evolutioncode.uk}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since       Moodle 3.3
 */

define(["jquery", "core/modal_factory", "core/config", "core/templates", "core/notification", "core/ajax", 'core/fragment'],
    function ($, modalFactory, config, Templates, Notification, ajax, Fragment) {
        "use strict";

        /**
         * Keep references for all modals we have already added to the page,
         * so that we can relaunch then if needed
         * @type {{}}
         */
        var modalStore = {};
        var loadingIconHtml;
        const win = $(window);
        var courseId;
        var tilesConfig;

        const Selector = {
            modal: ".modal",
            modalDialog: ".modal-dialog",
            modalBody: ".modal-body",
            sectionMain: ".section.main",
            pageContent: "#page-content",
            regionMain: "#region-main",
            completionState: "#completion-check-",
            cmModal: ".embed_cm_modal",
            moodleMediaPlayer: ".mediaplugin_videojs",
            closeBtn: "button.close",
            ACTIVITY: "li.activity",
            URLACTIVITYPOPUPLINK: ".activity.modtype_url.urlpopup a",
            modalHeader: ".modal-header",
            embedModuleButtons: ".embed-module-buttons",
            iframe: "iframe"
        };

        const CLASS = {
            COMPLETION_ENABLED: "completion-enabled",
            COMPLETION_MANUAL: "completion-manual",
            COMPLETION_AUTO: "completion-auto", // E.g. grade based.
            COMPLETION_VIEW: "completion-view",
            COMPLETION_CHECK_BOX: "completioncheckbox",
            COMPLETION_DROPDOWN: "completion-dropdown"
        };

        const modalMinWidth = function () {
            return Math.min(win.width(), 1100);
        };

        /**
         * Some modals contain videos in iframes or objects, which need to stop playing when dismissed.
         * @param {object} modal the modal which contains the video.
         */
        const stopAllVideosOnDismiss = function(modal) {
            const iframes = modal.find(Selector.iframe);
            if (iframes.length > 0) {
                modal.find(Selector.closeBtn).click(function(e) {
                    $(e.currentTarget).closest(Selector.cmModal).find(Selector.iframe).each(function (index, iframe) {
                        iframe = $(iframe);
                        iframe.attr('src', iframe.attr("src"));
                    });
                });
            }
            const objects = modal.find("object");
            if (objects.length > 0) {
                // In this case resetting the URL does not seem to work so we clear it and clear modal from storage.
                modal.find(Selector.closeBtn).click(function(e) {
                    const modal = $(e.currentTarget).closest(Selector.cmModal);
                    modal.find("object").each(function (index, object) {
                        object = $(object);
                        object.attr('data', "");
                    });
                    modalStore[modal.data("cmid")] = undefined;
                });
            }

            const moodleMediaPlayer = modal.find(Selector.moodleMediaPlayer);
            if (moodleMediaPlayer.length > 0) {
                modal.find(Selector.closeBtn).click(function() {
                    modal.find(Selector.moodleMediaPlayer).html("");
                });
                // Ensure we create a new modal next time.
                modalStore[modal.data("cmid")] = undefined;
            }
        };
        /**
         *
         * @param {number} cmId
         * @param {number} moduleContextId
         * @param {number} sectionNum
         * @param {string} title
         * @param {string} objectType
         * @param {string} pluginfileUrl
         * @param {boolean} completionEnabled
         * @param {number} existingCompletionState
         * @param {boolean} isManualCompletion
         * @param {string} secondaryUrl URL to be shown to user as a fallback if embedded URL does not laod.
         * @returns {boolean}
         */
        const launchCmModal = function (
                cmId, moduleContextId, sectionNum, title, objectType, pluginfileUrl,
                completionEnabled, existingCompletionState, isManualCompletion, secondaryUrl
            ) {
            modalFactory.create({
                type: modalFactory.types.DEFAULT,
                title: title,
                body: loadingIconHtml
            }).done(function (modal) {
                modalStore[cmId] = modal;
                modal.setLarge();
                modal.show();
                const modalRoot = $(modal.root);
                modalRoot.attr("id", "embed_mod_modal_" + cmId);
                modalRoot.data("cmid", cmId);
                modalRoot.data("section", sectionNum);
                modalRoot.addClass("embed_cm_modal");

                // If it's a page activity, we simply add the page HTML as the modal body.
                // Otherwise, we set the body by rendering from a template.
                if (objectType === 'page') {
                    modalRoot.addClass('mod_' + objectType);
                    stopAllVideosOnDismiss(modalRoot);
                    Fragment.loadFragment(
                        'format_tiles', `get_cm_content`, moduleContextId, {contextid: moduleContextId}
                    )
                       .done(function(html, js) {
                            modal.setBody(html);
                            Templates.runTemplateJS(js);
                        });
                } else {
                    // Render the modal body and set it to the page.
                    // First a blank template data object.
                    var templateData = {
                        id: cmId,
                        pluginfileUrl: pluginfileUrl,
                        objectType: null,
                        width: "100%",
                        height: Math.round(win.height() - 60), // Embedded object height in modal - make as high as poss.
                        cmid: cmId,
                        tileid: sectionNum,
                        isediting: 0,
                        sesskey: config.sesskey,
                        activityname: title,
                        config: {wwwroot: config.wwwroot},
                        completionstring: '',
                        secondaryurl: secondaryUrl
                    };

                    var template = null;
                    if (objectType === "resource_html") {
                        templateData.objectType = "text/html";
                        template = 'format_tiles/embed_file_modal_body';
                    } else if (objectType === "resource_pdf") {
                        templateData.objectType = 'application/pdf';
                        template = 'format_tiles/embed_file_modal_body';
                    } else if (objectType === "url") {
                        templateData.objectType = 'url';
                        template = 'format_tiles/embed_url_modal_body';
                    }

                    Templates.render(template, templateData).done(function (html) {
                        modal.setBody(html);
                        modalRoot.find(Selector.modalBody).animate({"min-height": Math.round(win.height() - 120)}, "fast");

                        if (objectType === "resource_html" || objectType === 'url') {
                            // HTML files only - set widths to 100% since they may contain embedded videos etc.
                            modalRoot.find(Selector.modal).animate({"max-width": "100%"}, "fast");
                            modalRoot.find(Selector.modalDialog).animate({"max-width": "100%"}, "fast");
                            modalRoot.find(Selector.modalBody).animate({"max-width": "100%"}, "fast");
                            stopAllVideosOnDismiss(modalRoot);
                            if (objectType === 'url') {
                                modalRoot.find(Selector.modalBody).addClass("text-center");
                            }
                        } else if (objectType === "resource_pdf") {
                            // Otherwise (e.g. for PDF) we don't need 100% width.
                            modalRoot.find(Selector.modal).animate({"max-width": modalMinWidth()}, "fast");
                            // We do modal-dialog too since Moove theme uses it.
                            modalRoot.find(Selector.modalDialog).animate({"max-width": modalMinWidth()}, "fast");
                        }

                    }).fail(Notification.exception);
                }

                // Render the modal header / title and set it to the page.
                var headerTemplateData = {
                    cmid: cmId,
                    activityname: title,
                    tileid: sectionNum,
                    showDownload: objectType === "resource_pdf" ? 1 : 0,
                    showNewWindow: ["resource_pdf", 'url'].includes(objectType) ? 1 : 0,
                    pluginfileUrl: pluginfileUrl,
                    forModal: true,
                    secondaryurl: secondaryUrl
                };
                if (completionEnabled) {
                    headerTemplateData.istrackeduser = 1;
                    headerTemplateData.hascompletion = 1;
                    const oldState = existingCompletionState === 1;

                    // Core completion button template has 'overallcomplete' arg relating to this cm.
                    // See course/templates/completion_manual.mustache.
                    headerTemplateData.overallcomplete = oldState ? 1 : 0;
                    headerTemplateData.overallincomplete = oldState ? 0 : 1;
                    headerTemplateData.completionIsManual = isManualCompletion;
                    if (!headerTemplateData.completionIsManual) {
                        // Auto completion has different vars for core template core_course/completion_automatic.
                        headerTemplateData.statuscomplete = headerTemplateData.overallcomplete;
                        headerTemplateData.statusincomplete = headerTemplateData.overallincomplete;
                    }
                    // Trigger event to check if other items in course have updated availability.
                    if (oldState !== headerTemplateData.completionstate) {
                        require(["format_tiles/completion"], function (completion) {
                            completion.triggerCompletionChangedEvent(parseInt(sectionNum), parseInt(cmId));
                        });
                    }
                }

                Templates.render("format_tiles/embed_module_modal_header_btns", headerTemplateData).done(function (html) {
                    modalRoot.find(Selector.embedModuleButtons).remove();
                    modalRoot.find($('button.close')).remove();
                    modalRoot.find(Selector.modalHeader).append(html);
                    modalRoot.find(Selector.closeBtn).detach().appendTo(modalRoot.find(Selector.embedModuleButtons));
                    const toggleCompletionSelector = '[data-action="toggle-manual-completion"]';
                    modalRoot.find(toggleCompletionSelector).on('click', () => {
                        require(["format_tiles/completion"], function (completion) {
                            // In this case, core will handle the request to set the new completion value in the DB.
                            // We wait a moment to allow that to get a head start.
                            // Then we trigger an event which course.js will see and update section content to show new statuses.
                            // Use will not notice this as they are looking at the modal, but it's ready when they dismiss modal.
                            setTimeout(() => {
                                completion.triggerCompletionChangedEvent(
                                    parseInt(modalRoot.data('section')), parseInt(modalRoot.data("cmid"))
                                );
                            }, 300);
                        });
                    });
                }).fail(Notification.exception);

                // Allow a short delay before we resize the modal, and check a few times, as content may be loading.
                setTimeout(() => {
                    modalHeightChangeWatcher(modalRoot, 3, 1000);
                }, 500);

                return true;
            });
            return false;
        };

        /**
         * Resize the modal to account for its content.
         * @param {object} modalRoot
         */
        var resizeModal = function(modalRoot) {
            modalRoot.find(Selector.modal).animate({"max-width": modalMinWidth()}, "fast");

            var MODAL_MARGIN = 70;

            // If the modal contains a Moodle mediaplayer div, remove the max width css rule which Moodle applies.
            // Otherwise video will be 400px max wide.
            var mediaPlayer = $(Selector.moodleMediaPlayer);
            mediaPlayer.find("div").each(function(index, child) {
                $(child).css("max-width", "");
            });
            if (mediaPlayer.length > 0) {
                stopAllVideosOnDismiss(modalRoot);
            }

            // If the activity contains an iframe (e.g. is a page with a YouTube video in it, or H5P), ensure modal is big enough.
            // Do this for every iframe in the course module.
            modalRoot.find(Selector.iframe).each(function (index, iframe) {

                const iframeSelector = $(iframe);

                // Get the modal.
                var modal;
                // Boost calls the modal "modal dialog" so try this first.
                modal = modalRoot.find(Selector.modalDialog);

                // If no luck, try what Clean and Adaptable do instead.
                if (modal.length === 0) {
                    modal = modalRoot.find(Selector.modal);
                }

                // Now check and adjust the width of the modal.
                var iframeWidth = Math.min(iframeSelector.width(), win.width());
                if (iframeWidth > modal.width() - MODAL_MARGIN) {
                    modal.animate(
                        {"max-width": Math.max(iframeWidth + MODAL_MARGIN, modalMinWidth())},
                        "fast"
                    );
                    modalRoot.find(Selector.modal).animate(
                        {"max-width": Math.max(iframeWidth + MODAL_MARGIN, modalMinWidth())},
                        "fast"
                    );
                }

                // Then the height of the modal body.
                var modalBody = modalRoot.find(Selector.modalBody);
                if (iframeSelector.height() > modalBody.height() - MODAL_MARGIN) {
                    iframeSelector.attr('height', modalBody.height() - MODAL_MARGIN);
                }
                stopAllVideosOnDismiss(modalRoot);
            });
        };

        /**
         * Check the modal height to see if the iframe in it is bigger.  If it is, adjust modal height up.
         * Do this a few times so that, if iframe content is loading, we can check after it's loaded.
         * @param {object} modalRoot
         * @param {number} howManyChecks
         * @param {number}duration
         * @param {number} oldHeight
         */
        const modalHeightChangeWatcher = function (modalRoot, howManyChecks, duration, oldHeight = 0) {
            const iframe = modalRoot.find(Selector.modalBody);
            if (iframe) {
                const newHeight = Math.round(iframe.height());
                if (newHeight && newHeight > oldHeight + 10) {
                    resizeModal(modalRoot);
                }
                if (howManyChecks > 0) {
                    setTimeout(() => {
                        modalHeightChangeWatcher(modalRoot, howManyChecks - 1, duration, newHeight);
                    }, duration);
                }
            }
        };

        const logCmView = function(cmId) {
            ajax.call([{
                methodname: "format_tiles_log_mod_view", args: {
                    courseid: courseId,
                    cmid: cmId
                }
            }])[0].fail(Notification.exception);
        };

        /**
         * Do we need a modal for this cm?
         * @param {number} cmId course module ID
         * @param {string} url
         * @return boolean
         */
        const modalRequired = function(cmId, url) {
            if (tilesConfig.modalallowedmodnames === undefined) {
                return false;
            }
            if (tilesConfig.modalallowedcmids === undefined) {
                return false;
            }
            if (!(tilesConfig.modalallowedcmids).includes(cmId)) {
                return false;
            }

            return ((tilesConfig.modalallowedmodnames).includes('page') && url.startsWith(`${config.wwwroot}/mod/page/view.php`))
                || ((tilesConfig.modalallowedmodnames).includes('url') && url.startsWith(`${config.wwwroot}/mod/url/view.php`))
                || ((tilesConfig.modalallowedmodnames).includes('pdf') && url.startsWith(`${config.wwwroot}/mod/resource/view.php`))
                || ((tilesConfig.modalallowedmodnames).includes('html')
                    && url.startsWith(`${config.wwwroot}/mod/resource/view.php`));
        };

        return {
            init: function (courseIdInit, isEditing, pageType, launchModalCmid) {
                courseId = courseIdInit;
                $(document).ready(function () {
                    tilesConfig = $('#format-tiles-js-config').data();
                    const courseIndex = $('nav#courseindex');

                    if (pageType === 'course-view-tiles') {
                        // We are on the main tiles page.
                        // If any link in the course index on the left is clicked, check if it needs a modal.
                        // If it does, launch the modal instead of following the link.
                        // This isn't ideal but saves plugin re-implementing / maintaining large volume of course index code.
                        if (courseIndex.length > 0) {
                            courseIndex.on('click', function(e) {
                                const target = $(e.target);
                                const link = target.hasClass('courseindex-link') ? target : target.find('a.courseindex-link');
                                if (link && link.data('for') === 'cm_name') {
                                    e.preventDefault();
                                    const linkUrl = link.attr('href');
                                    if (linkUrl) {
                                        const cmId = link.closest('li.courseindex-item').data('id');
                                        if (modalRequired(cmId, linkUrl)) {
                                            ajax.call([{
                                                methodname: "format_tiles_get_course_mod_info", args: {cmid: cmId}
                                            }])[0].done(function (data) {
                                                if (!data || !data.modalallowed) {
                                                    window.location.href = linkUrl;
                                                }
                                                const expandedSection = $(`li#section-${data.sectionnumber}.state-visible`);
                                                if (expandedSection.length === 0) {
                                                    require(["format_tiles/course"], function (course) {
                                                        course.populateAndExpandSection(
                                                            data.coursecontextid, data.sectionid, data.sectionnumber
                                                        );
                                                    });
                                                }

                                                launchCmModal(
                                                    cmId,
                                                    data.modulecontextid,
                                                    data.sectionnumber,
                                                    data.name,
                                                    data.modname === 'resource' ? `resource_${data.resourcetype}` : data.modname,
                                                    data.modname === 'url' || data.resourcetype === 'html'
                                                        ? data.pluginfileurl : linkUrl,
                                                    data.completionenabled ? 1 : 0,
                                                    data.iscomplete ? 1 : 0,
                                                    data.ismanualcompletion,
                                                    data.pluginfileurl
                                                );
                                            })
                                                .fail(function() {
                                                    window.location.href = linkUrl;
                                                });
                                            } else {
                                                window.location.href = linkUrl;
                                            }
                                    }
                                }
                            });
                        }

                        // If we are passing ?cmid=xxx in the URL this suggests we are trying to launch course mod modal.
                        // This would be from clicking a course index link while in another activity.
                        // E.g. from /mod/xxx/view.php for another course module.
                        // This isn't ideal but saves this plugin re-implementing / maintaining large volume of course index code.
                        if (launchModalCmid) {
                            ajax.call([{
                                methodname: "format_tiles_get_course_mod_info", args: {cmid: launchModalCmid}
                            }])[0].done(function (data) {
                                if (data && data.modalallowed) {
                                    const expandedSection = $(`li#section-${data.sectionnumber}.state-visible`);
                                    if (expandedSection.length === 0) {
                                        require(["format_tiles/course"], function (course) {
                                            course.populateAndExpandSection(
                                                data.coursecontextid, data.sectionid, data.sectionnumber
                                            );
                                        });
                                    }

                                    launchCmModal(
                                        launchModalCmid,
                                        data.modulecontextid,
                                        data.sectionnumber,
                                        data.name,
                                        data.modname === 'resource' ? `resource_${data.resourcetype}` : data.modname,
                                        ['url', 'resource'].includes(data.modname) ? data.pluginfileurl : '',
                                        data.completionenabled ? 1 : 0,
                                        data.iscomplete ? 1 : 0,
                                        data.ismanualcompletion,
                                        data.secondaryurl
                                    );
                                }
                            });
                        }

                        const launchModalDataActions =
                            ["launch-tiles-resource-modal", "launch-tiles-module-modal", "launch-tiles-url-modal"];
                        var modalSelectors = launchModalDataActions.map(function (action) {
                            return `[data-action="${action}"]`;
                        }).join(", ");

                        var pageContent = $(Selector.pageContent);
                        if (pageContent.length === 0) {
                            // Some themes e.g. RemUI do not have a #page-content div, so use #region-main.
                            pageContent = $(Selector.regionMain);
                        }
                        pageContent.on("click", modalSelectors, function (e) {
                            // If click is on a completion checkbox within activity, ignore here as handled elsewhere.
                            const tgt = $(e.target);
                            const isExcludedControl = tgt.hasClass(CLASS.COMPLETION_CHECK_BOX)
                                || tgt.parent().hasClass(CLASS.COMPLETION_CHECK_BOX)
                                || tgt.hasClass(CLASS.COMPLETION_DROPDOWN)
                                || tgt.parent().hasClass(CLASS.COMPLETION_DROPDOWN)
                                || tgt.is(":button")
                                || tgt.hasClass('expanded-content') // "Show less" link on restrictions.
                                || tgt.hasClass('collapsed-content'); // "Show more" link on restrictions
                            if (isExcludedControl) {
                                return;
                            }
                            e.preventDefault();
                            const currTgt = $(e.currentTarget);
                            var clickedCmObject = currTgt.closest("li.activity");
                            const cmId = clickedCmObject.data('cmid');
                            const moduleContextId = clickedCmObject.data('contextid');
                            const sectionNum = clickedCmObject.closest(Selector.sectionMain).data('section');

                            // If we already have this modal on the page, launch it.
                            var existingModal = modalStore[cmId];
                            if (typeof existingModal === "object") {
                                existingModal.show();
                            } else {
                                // Log the fact we viewed it (only do this once not every time the modal launches).
                                logCmView(cmId);

                                // We don't already have it, so make it.
                                launchCmModal(
                                    cmId,
                                    moduleContextId,
                                    sectionNum,
                                    clickedCmObject.data('title'),
                                    clickedCmObject.data('modtype'),
                                    clickedCmObject.data('url'),
                                    clickedCmObject.hasClass(CLASS.COMPLETION_ENABLED),
                                    clickedCmObject.data('completion-state')
                                        ? parseInt(clickedCmObject.data('completion-state')) : null,
                                    clickedCmObject.hasClass(CLASS.COMPLETION_MANUAL),
                                    clickedCmObject.data("url-secondary")
                                );
                            }
                        });

                        // Render the loading icon and append it to body so that we can use it later.
                        Templates.render("format_tiles/loading", {})
                            .catch(Notification.exception)
                            .done(function (html) {
                                loadingIconHtml = html; // TODO get this from elsewhere.
                            }).fail(Notification.exception);

                        // If completion of a cm changes, remove it from store so that it can be re-rendered with correct heading.
                        $(document).on('format-tiles-completion-changed', function(e, data) {
                            if (data.cmid && modalStore[data.cmid]) {
                                modalStore[data.cmid] = undefined;
                            }
                        });
                    } else if (pageType.match('^mod-[a-z]+-view$')) {
                        courseIndex.on('click', function (e) {
                            const target = $(e.target);
                            const link = target.hasClass('courseindex-link') ? target : target.find('a.courseindex-link');
                            if (link && link.data('for') === 'cm_name') {
                                e.preventDefault();
                                const linkUrl = link.attr('href');
                                if (linkUrl) {
                                    const link = $(e.target);
                                    const cmId = link.closest('li.courseindex-item').data('id');
                                    if (modalRequired(cmId, linkUrl)) {
                                        window.location.href =
                                            `${config.wwwroot}/course/view.php?id=${courseId}&cmid=${cmId}`;
                                    } else {
                                        window.location.href = linkUrl;
                                    }
                                }
                            }
                        });
                    }
                });
            }
        };
    }
);
