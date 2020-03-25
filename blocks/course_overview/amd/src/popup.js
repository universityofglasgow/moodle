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
 * A javascript module to popup activity overviews
 *
 * @module     block_course_overview
 * @class      block
 * @package    block_course_overview
 * @copyright  2017 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'jqueryui', 'core/config'], function($, UI, mdlconfig) {

    return {
        init: function() {

            // Dialogues on activity icons.
            $(".dialogue").dialog({
                autoOpen: false,
                minWidth: 400,
                classes: {
                    'ui-dialog': 'course-overview-dialog'
                },
                closeText: '',
                modal: true
            });

            // Opens the appropriate dialog.
            $(".overview-icon").click(function () {

                // Takes the ID of appropriate dialogue.
                var id = $(this).data('id');

                // Open dialogue.
                $(id).dialog("open");
            });

        }
    };
});
