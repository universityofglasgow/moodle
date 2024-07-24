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
 *  Sharing Cart
 *
 *  @package    block_sharing_cart
 *  @copyright  2017 (C) VERSION2, INC.
 *  @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';

/**
 * @param {string} addMethod
 */
export const init = function(addMethod) {
    $(document).ready(function() {
        let isDragging = false;

        /**
         *  Returns a localized string
         *
         *  @param {String} identifier
         *  @return {String}
         */
        function str(identifier) {
            return M.str.block_sharing_cart[identifier] || M.str.moodle[identifier];
        }

        /**
         *  Get an action URL
         *
         *  @param {String} name   The action name
         *  @param {Object} [args] The action parameters
         *  @return {String}
         */
        function get_action_url(name, args) {
            let url = M.cfg.wwwroot + '/blocks/sharing_cart/' + name + '.php';
            if (args) {
                const q = [];
                for (let k in args) {
                    q.push(k + '=' + encodeURIComponent(args[k]));
                }
                url += '?' + q.join('&');
            }
            return url;
        }

        /**
         *  Shake the basket to indicate cancel/submit
         */
        function shake_basket() {
            if (addMethod === 'drag_and_drop') {
                const sharingCartBasket = document.querySelector('button.sharing_cart_basket');
                sharingCartBasket?.classList.add('shake_basket');
            }
        }

        /**
         *  Remove the shake effect and basket icon
         */
        function remove_basket() {
            if (addMethod === 'drag_and_drop' && !isDragging) {
                const footer = document.getElementById('page-footer');
                const footerIconContainer = footer.querySelector('div[data-region="footer-container-popover"]');
                const sharingCartBasket = document.querySelector('button.sharing_cart_basket');

                if (sharingCartBasket) {
                    footerIconContainer?.removeChild(sharingCartBasket);
                    sharingCartBasket.classList.remove('shake_basket');
                }
            }
        }

        /**
         * Modal called when confirming an action.
         *
         * @param obj
         */
        function confirm_modal(obj) {

            // Checkbox for copying userdata confirmation.
            if (obj.checkbox) {
                obj.body +=
                    '<div class="modal-checbox-wrapper modal-sharing_cart">' +
                    '<input type="checkbox" id="modal-checkbox" class="modal-checkbox">' +
                    '<label for="modal-checkbox">' + str('modal_checkbox') + '</label>' +
                    '</div>';
            }


            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: obj.title,
                body: obj.body,
            }).done(function(modal) {
                let is_submitted = false;
                modal.setSaveButtonText(obj.save_button);

                // On save save check - if checkbox is checked.
                modal.getRoot().on(ModalEvents.save, function(e) {

                    const response = {
                        'checkbox': $(e.target).find('.modal-checkbox').is(':checked'),
                    };

                    obj.next(response);
                    is_submitted = true;
                });

                modal.getRoot().on(ModalEvents.cancel, function() {
                    remove_basket();
                });

                // Remove modal from html.
                modal.getRoot().on(ModalEvents.hidden, function() {
                    $('body').removeClass('modal-open');

                    if (!is_submitted) {
                        remove_basket();
                    }
                });

                modal.show();
            });
        }

        /**
         * Get the section name from the section when
         * it's changed with the in place editor
         *
         * @param $section
         * @returns {*}
         */
        function in_place_edit_section_name($section) {
            let sectionName = '';
            const $inPlaceEditable = $section.find('h3.sectionname .inplaceeditable');
            if ($inPlaceEditable.length) {
                sectionName = $inPlaceEditable.data('value');
            }
            return sectionName;
        }

        /**
         * @param post_data
         * @param title_str
         * @param body_str
         * @param isSection
         */
        function on_backup_modal(post_data, title_str, body_str, isSection) {
            (function(on_success) {
                $.post(get_action_url('rest'), post_data,
                    function(response) {
                        on_success(response);
                    }, "text")
                    .fail(function(response) {
                        show_error(response);
                    });
            })(function(response) {
                const copyable = response === '1';
                let checkbox = false;

                if (copyable) {
                    checkbox = true;
                }

                confirm_modal({
                    'title': title_str,
                    'body': body_str,
                    'save_button': str('modal_confirm_backup'),
                    'checkbox': checkbox,
                    'next': function(data) {
                        if (isSection === true) {
                            backup_section(post_data.sectionid, post_data.sectionnumber, post_data.courseid, data.checkbox);
                        } else {
                            backup(post_data.cmid, data.checkbox);
                        }

                        shake_basket();
                    }
                });
            });
        }

        /** @var {Object}  The icon configurations */
        const icon = {
            // Actions
            'backup': {
                css: 'editing_backup',
                iconClass: 'fa fa-frown-o',
            },
            'movedir': {
                css: 'editing_right',
                iconClass: 'fa fa-arrow-right',
            },
            'move': {
                css: 'editing_move_',
                iconClass: 'fa fa-arrows-v',
            },
            'edit': {
                css: 'editing_update',
                iconClass: 'fa fa-pencil',
            },
            'cancel': {
                css: 'editing_cancel',
                iconClass: 'fa fa-ban',
            },
            'delete': {
                css: 'editing_update',
                iconClass: 'fa fa-trash',
            },
            'restore': {
                css: 'editing_restore',
                iconClass: 'fa fa-clone',
            },
            // Directories
            'dir-open': {
                iconClass: 'fa fa-folder-open-o'
            },
            'dir-closed': {
                iconClass: 'fa fa-folder-o'
            },
        };

        /** @var {Node}  The Sharing Cart block container node */
        const $block = $('.block_sharing_cart');

        /** @var {Object}  The current course */
        const course = new function () {
            const body = $('body');
            this.id = body.attr('class').match(/course-(\d+)/)[1];
            this.is_frontpage = body.hasClass('pagelayout-frontpage');
        }();

        /**
         *  Shows an error message with given Ajax error
         *
         *  @param {Object} response  The Ajax response
         */
        function show_error(response) {
            try {
                const ex = JSON.parse(response.responseText);
                new M.core.exception({
                    name: str('pluginname') + ' - ' + str('error'),
                    message: ex.message
                });
            } catch (e) {
                new M.core.exception({
                    name: str('pluginname') + ' - ' + str('error'),
                    message: response.responseText
                });
            }
        }

        /**
         *  Check special layout (theme boost)
         *
         *  @return {Boolean}
         */
        function verify_layout() {
            const menuelement = $block.find('.menubar .dropdown .dropdown-menu');
            return (menuelement.length);
        }

        /**
         * Set Cookie
         * @param name
         * @param value
         * @param expireTimeInMillisecond
         */
        function setCookie(name, value, expireTimeInMillisecond) {
            const d = new Date();
            d.setTime(d.getTime() + expireTimeInMillisecond);
            const expires = 'expires=' + d.toUTCString();
            document.cookie = name + '=' + value + ';' + expires + '';
        }

        /**
         * Get Cookie Value
         * @param param
         * @returns {*}
         */
        function getCookieValue(param) {
            const readCookie = document.cookie.match('(^|;)\\s*' + param + '\\s*=\\s*([^;]+)');
            return readCookie ? readCookie.pop() : '';
        }

        /**
         * Create a command icon
         *
         *  @param {String} name  The command name, predefined in icon
         *  @param {String} [pix] The icon pix name to override
         */
        function create_command(name) {
            const iconElement = $('<i/>')
                .attr('alt', str(name))
                .attr('class', icon[name].iconClass);
            // If (verify_layout()) {
            //     iconElement.addClass('iconcustom');
            // }

            return $('<a href="javascript:void(0)"/>')
                .addClass(icon[name].css)
                .attr('title', str(name))
                .append(iconElement);
        }

        /**
         * Create a spinner
         * @param $node
         * @returns {*|jQuery}
         */
        function add_spinner() {
            const $spinner = ($('<div class="block_spinner"><i class="fa fa-shopping-basket sharing_cart_basket shake_basket fa-2x"></i></div>'));
            $('section.block_sharing_cart').append($spinner);
            return $spinner;
        }

        /**
         *
         * @param $node
         * @returns {jQuery.fn.init}
         */
        function add_node_spinner($node) {
            const $node_spinner = $('<i class="fa fa-circle-o-notch fa-spin node_spinner node_spinner-sharing_cart"></i>');
            $node.append($node_spinner);
            return $node_spinner;
        }

        $(document).on('click', 'a.restore', function() {
            add_spinner();
        });

        /**
         *
         *  Reload the Sharing Cart item tree
         */
        function reload_tree() {
            $.post(get_action_url("rest"),
                {
                    "action": "render_tree",
                    "courseid": course.id
                },
                function(response) {
                    $block.find(".tree").replaceWith($(response));
                    $.init_item_tree();
                }, "html")
                .fail(function(response) {
                    show_error(response);
                });
        }

        /**
         *  Backup an activity
         *
         *  @param {int} cmid
         *  @param {Boolean} userdata
         */
        function backup(cmid, userdata) {
            let $commands = $('#module-' + cmid + ' .actions');
            if (!$commands.length) {
                $commands = $('[data-owner="#module-' + cmid + '"]');
            }

            const $spinner = add_spinner();
            const $node_spinner = add_node_spinner($commands);

            $.post(get_action_url("rest"),
                {
                    "action": "backup",
                    "cmid": cmid,
                    "userdata": userdata,
                    "sesskey": M.cfg.sesskey,
                    "courseid": course.id
                },
                function() {
                    reload_tree();
                })
                .fail(function(response) {
                    show_error(response);
                })
                .always(function() {
                    $node_spinner.hide();
                    $spinner.hide();
                    remove_basket();
                });
        }

        /**
         *  Backup an activities in a section
         *
         *  @param {int} sectionId
         *  @param {int} sectionNumber
         *  @param {int} courseId
         *  @param {Boolean} userdata
         */
        function backup_section(sectionId, sectionNumber, courseId, userdata) {
            const $commands = $('span.inplaceeditable[data-itemtype=sectionname][data-itemid=' + sectionId + ']');
            const $section = $commands.closest("li.section.main");
            let sectionName = $section.attr('aria-label') || $section.find('.sectionname').text().trim();

            if (sectionName === null) {
                sectionName = String($('#region-main .section_action_menu[data-sectionid=\'' + sectionId + '\']')
                    .parent().parent().find('h3.sectionname').text());
            }

            const inPlaceEditSectionName = in_place_edit_section_name($section);
            sectionName = (inPlaceEditSectionName !== '') ? inPlaceEditSectionName : sectionName;

            const $spinner = add_spinner();
            const $node_spinner = add_node_spinner($commands);

            $.post(get_action_url("rest"),
                {
                    "action": "backup_section",
                    "sectionid": sectionId,
                    "sectionnumber": sectionNumber,
                    "courseid": courseId,
                    "sectionname": sectionName,
                    "userdata": userdata,
                    "sesskey": M.cfg.sesskey
                },
                function() {
                    reload_tree();
                })
                .fail(function(response) {
                    show_error(response);
                })
                .always(function() {
                    $spinner.hide();
                    $node_spinner.hide();
                    remove_basket();
                });
        }


        // /////// CLASSES /////////

        /**
         *  @class Directory states manager
         */
        const directories = new function () {
            const KEY = 'block_sharing_cart-dirs';

            let opens = getCookieValue(KEY).split(',').map(function (v) {
                return parseInt(v);
            });

            function save() {
                const expires = new Date();
                expires.setDate(expires.getDate() + 30);
                setCookie(KEY, opens.join(','), expires);
            }

            function open($dir, visible) {
                const iconIndex = visible ? 'dir-open' : 'dir-closed';
                const iconElement = icon[iconIndex].iconClass;
                $dir.find('> div i.icon').attr('class', 'icon ' + iconElement);
                $dir.find('> ul.list')[visible ? 'show' : 'hide']();
            }

            function toggle(e) {
                const $dir = $(e.target).closest('li.directory');
                const i = $dir.attr('id').match(/(\d+)$/)[1];
                const v = $dir.find('> ul.list').css('display') === 'none';

                open($dir, v);
                opens[i] = v ? 1 : 0;
                save();
            }

            /**
             *  Initialize directory states
             */
            this.init = function () {
                let i = 0;
                $block.find('li.directory').each(function (index, dir) {
                    const $dir = $(dir);
                    $dir.attr('id', 'block_sharing_cart-dir-' + i);
                    if (i >= opens.length) {
                        opens.push(0);
                    } else if (opens[i]) {
                        open($dir, true);
                    }
                    $dir.find('> div div.toggle-wrapper').css('cursor', 'pointer').on('click', function (e) {
                        toggle(e);
                    });
                    i++;
                });
            };

            /**
             *  Reset directory states
             */
            this.reset = function () {
                opens = [];
                this.init();
                save();
            };
        }();

        /**
         *  @class Targets for moving an item directory
         */
        const move_targets = new function () {
            let $cancel = null,
                targets = [];

            /**
             *  Hide move targets
             */
            this.hide = function () {
                if ($cancel !== null) {
                    const $commands = $cancel.closest('.commands');
                    $cancel.remove();
                    $cancel = null;
                    $commands.closest('li.activity').css('opacity', 1.0);
                    $commands.find('a').each(function () {
                        $(this).show();
                    });
                    $.each(targets, function (index, $target) {
                        $target.remove();
                    });
                    targets = [];
                }
            };

            /**
             *  Show move targets for a given item
             *
             *  @param {int} id  The item ID
             */
            this.show = function (item_id) {
                this.hide();

                function move(e) {

                    const m = $(e.target).closest('a').attr('class').match(/move-(\d+)-to-(\d+)/);
                    const item_id = m[1],
                        area_to = m[2];

                    const $spinner = add_spinner();
                    $.post(get_action_url("rest"),
                        {
                            "action": "move",
                            "item_id": item_id,
                            "area_to": area_to,
                            "sesskey": M.cfg.sesskey
                        },
                        function () {
                            reload_tree();
                        })
                        .fail(function (response) {
                            show_error(response);
                        })
                        .always(function () {
                            $spinner.hide();
                        });
                }

                const $current = $block.find('#block_sharing_cart-item-' + item_id);
                const $next = $current.next();
                const $list = $current.closest('ul');

                let next_id = 0;
                if ($next.length) {
                    next_id = $next.attr('id').match(/item-(\d+)$/)[1];
                }

                /**
                 *
                 * @param item_id
                 * @param area_to
                 * @returns {jQuery}
                 */
                function create_target(item_id, area_to) {
                    const $anchor = $('<a href="javascript:void(0)"/>')
                        .addClass('move-' + item_id + '-to-' + area_to)
                        .attr('title', str('movehere'))
                        .append(
                            $('<p>' + str('clicktomove') + '</p>')
                                .attr('alt', str('movehere'))
                        );

                    const $target = $('<li class="activity move-to"/>')
                        .append($anchor);
                    $anchor.on('click', function (e) {
                        move(e);
                    });

                    return $target;
                }

                $list.find('> li.activity').each(function (index, item) {
                    const $item = $(item);
                    const to = $item.attr('id').match(/item-(\d+)$/)[1];
                    if (to === item_id) {
                        $cancel = create_command('cancel', 't/left');
                        $cancel.on('click', function () {
                            move_targets.hide();
                        });
                        const $commands = $item.find('.commands');
                        $commands.find('a').each(function () {
                            $(this).hide();
                        });
                        $commands.append($cancel);
                        $item.css('opacity', 0.5);
                    } else if (to !== next_id) {
                        const $target = create_target(item_id, to);
                        $item.before($target);
                        targets.push($target);
                    }
                }, this);

                if ($next) {
                    var $target = create_target(item_id, 0);
                    $list.append($target);
                    targets.push($target);
                }
            };
        }();

        /**
         *  @class Targets for restoring an item
         */
        const restore_targets = new function () {
            this.is_directory = null;
            let $clipboard = null,
                targets = [];

            /**
             *
             * @param id
             * @param section
             * @returns {jQuery}
             */

            function create_target(id, section) {
                const href = get_action_url('restore', {
                    'directory': (restore_targets.is_directory === true),
                    'target': id,
                    'course': course.id,
                    'section': section,
                    'in_section': $('#copy-section-form').data('in-section'),
                    'sesskey': M.cfg.sesskey,
                    'returnurl': document.URL,
                });

                let $target = $('<a/>').attr('href', href).attr('title', str('copyhere')).append(
                    $('<img class="move_target"/>').attr('alt', str('copyhere')).attr('src', M.util.image_url('dropzone_arrow', 'block_sharing_cart'))
                );

                targets.push($target);

                return $target;
            }

            /**
             *  Hide restore targets
             */
            this.hide = function () {
                if ($clipboard !== null) {
                    $clipboard.remove();
                    $clipboard = null;
                    $.each(targets, function (index, $target) {
                        $target.remove();
                    });
                    targets = [];
                }
            };

            /**
             *
             *
             *  @param {int} id  The item ID
             */
            this.show = function (id) {
                this.hide();

                let $view = $("<span/>");

                if (this.is_directory) {
                    $view.html(id).css('display', 'inline');
                    $view.prepend(
                        $("<i/>").addClass("icon")
                            .attr("alt", id)
                        // .attr("src", M.util.image_url(icon['dir-closed'].pix, null))
                    );
                } else {
                    const $item = $block.find('#block_sharing_cart-item-' + id);
                    $view = $($item.find('div')[0].cloneNode(true)).css('display', 'inline');
                    $view.attr('class', $view.attr('class').replace(/mod-indent-\d+/, ''));
                    $view.find('.commands').remove();
                }

                const $cancel = create_command('cancel');

                $cancel.on('click', this.hide);

                $clipboard = $('<div class="clipboard"/>');
                $clipboard.append(str('clipboard') + ": ").append($view).append($cancel);

                if (course.is_frontpage) {
                    const $sitetopic = $('.sitetopic');
                    const $mainmenu = $('.block_site_main_menu');
                    if ($sitetopic) {
                        $sitetopic.find('*').before($clipboard);
                    } else if ($mainmenu) {
                        $mainmenu.find('.content').before($clipboard);
                    }

                    // Mainmenu = section #0, sitetopic = section #1
                    if ($mainmenu) {
                        $mainmenu.find('.footer').before(create_target(id, 0));
                    }
                    if ($sitetopic) {
                        $sitetopic.find('ul.section').append(create_target(id, 1));
                    }
                } else {
                    const $container = $('.course-content');
                    $container.prepend($clipboard);
                    $container.find('li.section').each(function (index, sectionDOM) {
                        const $section = $(sectionDOM);
                        const section = $section.attr('id').match(/(\d+)$/)[1];
                        $section.find('ul.section').first().append(create_target(id, section));
                    }, this);
                }
            };
        }();

        // /////// INITIALIZATION /////////

        /**
         *
         * @returns {string|*}
         */
        $.get_plugin_name = function() {
            let $blockheader = $block.find("h2");

            if (!$blockheader.length) {
                $blockheader = $block.find("h3");

                if ($blockheader.length) {
                    return $blockheader.html();
                }
            } else {
                return $blockheader.html();
            }

            return "";
        };

        /**
         *
         * @param e
         * @param activityName
         * @param {int} cmId
         */
        $.on_backup = function(e, activityName, cmId = 0) {
            if (cmId === 0) {
                cmId = (function ($backup) {
                    const $activity = $backup.closest('li.activity');
                    if ($activity.length) {
                        return $activity.attr('id').match(/(\d+)$/)[1];
                    }
                    const $commands = $backup.closest('.commands');
                    const dataowner = $commands.attr('data-owner');
                    if (dataowner.length) {
                        return dataowner.match(/(\d+)$/)[1];
                    }
                    return $commands.find('a.editing_delete').attr('href').match(/delete=(\d+)/)[1];
                })($(e.target));
            }

            const data =
                {
                    "action": "is_userdata_copyable",
                    "cmid": cmId
                };

            on_backup_modal(data, activityName, str('confirm_backup'), false);
        };

        /**
         *  On movedir command clicked
         *
         *  @param {DOMEventFacade} e
         */
        $.on_movedir = function(e) {
            const $commands = $(e.target).closest('.commands');

            const $current_dir = $commands.closest('li.directory');
            const current_path = $current_dir.length ? $current_dir.attr('directory-path') : '/';

            const item_id = $(e.target).closest('li.activity').attr('id').match(/(\d+)$/)[1];

            const dirs = [];
            $block.find('li.directory').each(function() {
                dirs.push($(this).attr('directory-path'));
            });

            const $form = $('<form/>');
            // eslint-disable-next-line no-script-url
            $form.attr('action', 'javascript:void(0)');

            function submit() {
                const folder_to = $form.find('[name="to"]').val();
                const $spinner = add_spinner();
                $.post(get_action_url('rest'),
                    {
                        "action": "movedir",
                        "item_id": item_id,
                        "folder_to": folder_to,
                        "sesskey": M.cfg.sesskey
                    },
                    function() {
                        reload_tree();
                        directories.reset();
                    })
                    .fail(function(response) {
                        show_error(response);
                    })
                    .always(function() {
                        $spinner.hide();
                    });
            }

            $form.submit(submit);

            if (dirs.length === 0) {
                var $input = $('<input class="form-control" type="text" name="to"/>').val(current_path);
                setTimeout(function() {
                    $input.focus();
                }, 1);
                $form.append($input);
            } else {
                dirs.unshift('/');

                const $select = $('<select class="custom-select" name="to"/>');
                for (let i = 0; i < dirs.length; i++) {
                    $select.append($('<option/>').val(dirs[i]).append(dirs[i]));
                }
                $select.val(current_path);
                $select.change(submit);
                $form.append($select);

                const $edit = create_command('edit');

                $edit.on('click', function() {
                    const $input = $('<input type="text" name="to"/>').val(current_path);
                    $select.remove();
                    $edit.replaceWith($input);
                    $input.focus();
                });

                $form.append($edit);
            }

            const $cancel = create_command('cancel');
            $cancel.on('click', function() {
                $form.remove();
                $commands.find('a').show();
            });
            $form.append($cancel);

            $commands.find('a').each(function() {
                $(this).hide();
            });
            $commands.append($form);
        };

        /**
         *  On move command clicked
         *
         *  @param {DOMEventFacade} e
         */
        $.on_move = function(e) {
            const $item = $(e.target).closest('li.activity');
            const id = $item.attr('id').match(/(\d+)$/)[1];

            move_targets.show(id);
        };

        /**
         *  On delete command clicked
         *
         *  @param {DOMEventFacade} e
         */
        $.on_delete = function(e) {
            const $item = $(e.target).closest('li');
            const liText = $item[0].innerText;

            let isDirectory = false;
            let modalBody;
            let item;
            let description_text = '';

            if ($item.hasClass("directory")) {
                isDirectory = true;
                item = str('folder_string');
                description_text = str('delete_folder');
            } else {
                item = str('activity_string');
            }

            modalBody = '<p class="delete-item">' + item + ' ' + liText + description_text + '</p>';

            confirm_modal({
                'title': str('confirm_delete'),
                'body': modalBody,
                'save_button': str('modal_confirm_delete'),
                'checkbox': false,
                'next': function() {

                    let data = {};

                    if (isDirectory === true) {
                        data = {
                            "action": "delete_directory",
                            "path": $item.attr("directory-path"),
                            "sesskey": M.cfg.sesskey
                        };
                    } else if ($item.hasClass("activity")) {
                        data = {
                            "action": "delete",
                            "id": $item.attr('id').match(/(\d+)$/)[1],
                            "sesskey": M.cfg.sesskey
                        };
                    }

                    const $spinner = add_spinner();

                    $.post(get_action_url("rest"), data,
                        function() {
                            reload_tree();
                        })
                        .fail(function(response) {
                            show_error(response);
                        })
                        .always(function() {
                            $spinner.hide();
                        });

                    e.stopPropagation();
                }
            });
        };

        /**
         *  On restore command clicked
         *
         *  @param {DOMEventFacade} e
         */
        $.on_restore = function(e) {
            const $item = $(e.target).closest('li');
            let id = null;

            if ($item.hasClass("directory")) {
                id = $item.attr("directory-path");
                restore_targets.is_directory = true;
            } else if ($item.hasClass("activity")) {
                id = $item.attr('id').match(/(\d+)$/)[1];
                restore_targets.is_directory = false;
            }

            restore_targets.show(id);
        };

        /**
         * On backup the whole section as a folder
         *
         * @param {int} sectionId
         * @param {int} sectionNumber
         * @param {int} courseId
         * @param {string} sectionName
         */
        $.on_section_backup = function(sectionId, sectionNumber, courseId, sectionName) {
            const data =
                {
                    "action": "is_userdata_copyable_section",
                    "sectionid": sectionId,
                    "sectionnumber": sectionNumber,
                    "courseid": courseId,
                };

            const body_html = '<p class="alert alert-danger mt-3">' + str('backup_heavy_load_warning_message') +
                '</p>' + str('confirm_backup_section');

            on_backup_modal(data, sectionName, body_html, true);
        };

        /**
         *  Initialize the delete bulk
         */
        $.init_bulk_delete = function(isspeciallayout) {
            const bulkdelete = $block.find('.editing_bulkdelete');
            if (bulkdelete.length) {
                if (isspeciallayout) {
                    bulkdelete.attr('role', 'menuitem').addClass('dropdown-item menu-action');
                    bulkdelete.append($("<span class='menu-action-text'/>").append(bulkdelete.attr('title')));

                    $block.find('.menubar .dropdown .dropdown-menu').append(bulkdelete);
                } else {
                    $block.find('.header .commands').append(bulkdelete);
                }
            }
        };

        /**
         *  Initialize the help icon
         */
        $.init_help_icon = function(isspeciallayout) {
            const helpicon = $block.find('.header-commands > .help-icon');

            if (isspeciallayout) {
                $block.find('.header-commands').parent().css('display', 'block');
            } else {
                $block.find('.header .commands').append(helpicon);
            }
        };

        /**
         *  Initialize the Sharing Cart block header
         */
        $.init_block_header = function() {
            const isspeciallayout = verify_layout();
            $.init_bulk_delete(isspeciallayout);
            $.init_help_icon(isspeciallayout);
        };

        /**
         *  Initialize the Sharing Cart item tree
         */
        $.init_item_tree = function() {
            function add_actions(item, actions) {
                const $item = $(item);
                const isCopying = $item.attr('data-is-copying') === '1';
                const $commands = $item.find('.commands').first();

                $.each(actions, function(index, action) {
                    if (action === 'restore' && isCopying) {
                        return;
                    }
                    const $command = create_command(action);
                    $command.on('click', function(e) {
                        $['on_' + action](e);
                    });
                    $commands.append($command);
                }, this);
            }

            const activity_actions = ['movedir', 'move', 'delete'];
            if (course) {
                activity_actions.push('restore');
            }

            const directory_actions = ['delete', 'restore'];

            // Initialize items
            $block.find('li.activity').each(function(index, item) {
                if($(item).attr('data-disable-copy') == 1) {
                    add_actions(item, ['movedir', 'move', 'delete']);
                    return;
                }
                add_actions(item, activity_actions);
            });

            // Initialize directory items
            $block.find('li.directory').each(function(index, item) {
                add_actions(item, directory_actions);
            });

            // Initialize directories
            directories.init();
        };

        /**
         * Extract html object from area where moodle ajax was called.
         *
         * Call add_activity_backup_control to re append sharing cart icon.
         */
        $.init_activity_commands = function() {
            $(document).ajaxComplete(function(event, xhr, settings) {

                const url = settings.url;
                const lastslashindex = url.lastIndexOf('=');
                const result = url.substring(lastslashindex + 1);

                if (result === 'core_course_edit_module' || result === 'core_course_get_module') {

                    const data = JSON.parse(settings.data);
                    const action = data[0].args.action;

                    // Don't try to add icon if activity has been deleted.
                    if (action === 'delete') {
                        return;
                    }

                    setTimeout(function() {
                        const activity_id = data[0].args.id;
                        const activity = $('#module-' + activity_id);
                        add_activity_backup_control(activity);

                        if (action === 'duplicate') {
                            const duplicated = activity.next();
                            add_activity_backup_control(duplicated);
                        }
                    }, 1);
                }
            });

            /**
             * Create the backup icon
             *
             * @returns $backupIcon
             */
            function create_backup_icon() {

                const $backupIcon = $('<a href="javascript:void(0)" class="add-to-sharing-cart" />')
                    .append($('<i class="fa fa-shopping-basket icon"></i>'))
                    .attr('title', str('backup'));

                if (addMethod !== 'click_to_add') {
                    $backupIcon.addClass('d-none');
                }

                return $backupIcon;
            }

            /**
             * Add backup control with a click event to an activity
             * Added fix for copying an activity without backup routine
             *
             * @param $activity
             */
            function add_activity_backup_control($activity) {

                const activityClass = $activity[0].className;

                // Selecting modtype without prefix.
                const modtype = activityClass.substr(activityClass.indexOf('modtype_') + 8);

                // Default activity name.
                let activityName = str('activity_string');

                // Label is using a different html / css layout, so it's needed to get the name by using another $find.
                if (modtype !== 'label') {
                    activityName = $('.activity#' + $activity[0].id)
                        .find('.mod-indent-outer .activityinstance span.instancename')
                        .html();
                }

                const $backupIcon = create_backup_icon();

                $backupIcon.on('click', function(e) {
                    $.on_backup(e, activityName);
                });

                const $actionMenuItem = $activity.find('.action-menu.section-cm-edit-actions').parent('.actions');

                if (!$actionMenuItem.find('.add-to-sharing-cart').length) {
                    $actionMenuItem.append($backupIcon);
                }
            }

            /**
             * Add backup control with a click event to a section
             *
             * @param $section
             */
            function add_section_backup_control($section) {

                let sectionId = $section.find('.section_action_menu').data('sectionid');
                const sectionNumber = parseInt(String($section.attr('id')).match(/\d+/)[0]);
                let sectionName = $section.attr('aria-label') || $section.find('.sectionname').text().trim();

                const isFlexibleCourseFormat = $('body[id$=flexsections]').length;

                // Extract the section ID from the section if this is a Flexible
                // course format (since this format doesn't have an action menu)
                if (isFlexibleCourseFormat && (typeof sectionId === 'undefined' || sectionId === null)) {
                    sectionId = $section.data('section-id');
                }

                // A bit unsafe to extract the course ID from the body but it's the best option we got at the moment
                const courseId = parseInt(String($('body').attr('class')).match(/course-([0-9]*)( |$)/)[1]);

                const $backupIcon = create_backup_icon();

                $backupIcon.on('click', function() {
                    const inPlaceEditSectionName = in_place_edit_section_name($section);
                    sectionName = (inPlaceEditSectionName !== '') ? inPlaceEditSectionName : sectionName;
                    $.on_section_backup(sectionId, sectionNumber, courseId, sectionName);
                });

                let $sectionTitle = $section.find('h3.sectionname').first().find('a').last();

                const $inPlaceEditable = $section.find('h3.sectionname .inplaceeditable').first();
                if ($inPlaceEditable.length) {
                    $sectionTitle = $inPlaceEditable;
                }

                // Add the backup icon after the cog wheel if this is a Flexible course format
                if (isFlexibleCourseFormat && sectionNumber === 0) {
                    $sectionTitle = $section.find('> .controls');
                    $sectionTitle.prepend($backupIcon);
                } else {
                    $backupIcon.insertAfter($sectionTitle);
                }

                const activitySelector = 'li.activity';

                const $activities = $section.find(activitySelector);

                $($activities).each(function() {
                    add_activity_backup_control($(this));
                });
            }

            $("body.editing .course-content li.section").each(function() {
                add_section_backup_control($(this));
            });
        };

        /**
         *  Initialize the Sharing Cart footer basket for 4.0+.
         */
        function init_footer_basket() {
            let currentDragging;
            const activities = document.querySelectorAll(".activity.activity-wrapper");
            const sections = document.querySelectorAll(".course-section-header");
            const sharingCartBlock = document.querySelector('section[data-block="sharing_cart"]');

            add_draggable_to_first_section();

            const footer = document.getElementById('page-footer');
            const footerIconContainer = footer.querySelector('div[data-region="footer-container-popover"]');

            const basket = document.createElement('i');
            basket.setAttribute('class', 'fa fa-shopping-basket');

            const basketButton = document.createElement('button');
            basketButton.setAttribute('class', 'btn btn-icon bg-secondary icon-no-margin btn-footer-popover sharing_cart_basket');
            basketButton.setAttribute('style', 'z-index: 1001;');
            basketButton.append(basket);

            const dropAreaText = document.createElement('p');
            dropAreaText.setAttribute('class', 'font-weight-bold text-white');
            dropAreaText.innerText = str('drop_here');

            const dropArea = document.createElement('div');
            dropArea.setAttribute('class',
                'h-100 w-100 position-absolute d-flex justify-content-center align-items-center');
            dropArea.append(dropAreaText);

            sections.forEach(section => {
                drag_event_listeners(section);
            });

            activities.forEach(activity => {
                drag_event_listeners(activity);
            });

            /**
             *  Initialize events for dragging
             * @param {object} draggable
             */
            function drag_event_listeners(draggable) {
                draggable.addEventListener('dragstart', (e) => {
                    basketButton.classList.remove('shake_basket');

                    footerIconContainer?.prepend(basketButton);
                    sharingCartBlock.children[0].classList.add('dragging_item');
                    sharingCartBlock.append(dropArea);
                    currentDragging = e.target;
                    isDragging = true;
                });

                draggable.addEventListener('dragend', () => {
                    if (currentDragging instanceof HTMLElement) {
                        footerIconContainer?.removeChild(basketButton);
                    }

                    sharingCartBlock.children[0].classList.remove('dragging_item');
                    sharingCartBlock.removeChild(dropArea);
                    isDragging = false;
                });
            }

            [basketButton, sharingCartBlock].forEach((dropzone) => {
                dropzone.addEventListener("dragover", (e) => {
                    e.preventDefault();
                    dropzone.classList.add('drag_over');
                });

                dropzone.addEventListener("dragenter", (e) => {
                    e.preventDefault();
                    dropzone.classList.add('drag_over');
                });

                dropzone.addEventListener("dragleave", () => {
                    dropzone.classList.remove('drag_over');
                });

                dropzone.addEventListener("drop", () => {
                    if (currentDragging instanceof HTMLElement) {
                        currentDragging.querySelector('.add-to-sharing-cart').click();
                    }

                    dropzone.classList.remove('drag_over');
                    currentDragging = undefined;
                    isDragging = false;
                });
            });
        }

        /**
         *  Make the first section (General) draggable
         */
        function add_draggable_to_first_section() {
            const courseSectionHeader = document.getElementsByClassName("course-section-header")[0] ?? null;

            if (courseSectionHeader instanceof HTMLElement) {
                courseSectionHeader.classList.add('draggable');
                courseSectionHeader.setAttribute('draggable', true);
            }
        }

        /**
         * Initialize the Sharing Cart block
         */
        $.init = function() {
            M.str.block_sharing_cart.pluginname = this.get_plugin_name();

            // Arrange header icons (bulkdelete, help)
            $.init_block_header();
            $.init_item_tree();
            $.init_activity_commands();

            if (addMethod === 'drag_and_drop') {
                init_footer_basket();
            }
        };
        var $spinner = $('<i/>').addClass('spinner fa fa-3x fa-circle-o-notch fa-spin');
        $('div#sharing-cart-spinner-modal div.spinner-container').prepend($spinner);

        $.init();
    });

    $('.copy_section').on('click', function() {

        const $section_selected = ($('.section-dropdown option:selected'));
        const sectionId = $section_selected.data('section-id');
        const sectionNumber = $section_selected.data('section-number');
        const courseId = $section_selected.data('course-id');
        const sectionName = $section_selected.data('section-name');

        $.on_section_backup(sectionId, sectionNumber, courseId, sectionName);
    });

    $('.copy_activity').on('click', function(e) {
        const activitySelected = ($('.activity-dropdown option:selected'));
        const activityId = activitySelected.data('activity-id');
        const activityName = activitySelected.data('activity-name');

        $.on_backup(e, activityName, activityId);
    });
};
