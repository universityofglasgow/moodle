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
 * A javascript module to allow drag-and-drop control of course order
 *
 * @module     block_course_overview
 * @class      block
 * @package    block_course_overview
 * @copyright  2017 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'jqueryui'], function($, UI) {

    return {
        init: function() {

            // Change non-js links to be inactive.
            $(".courseovbox a").removeAttr("href");

            // Make the course list sort.
            $(".tab-pane .course-list").sortable({
                update: function(event, ui) {
                    var kids = $(".tab-pane.active .course-list").children();
                    var sortorder = [];
                    $.each(kids, function(index, value) {
                        var id = value.getAttribute('id');
                        sortorder[index] = id.substring(7);
                    });

                    // Send new sortorder.
                    var activetab = $(".block_course_overview .nav-tabs .active").data("tabname");
                    var data = {
                        sesskey : M.cfg.sesskey,
                        tab : activetab,
                        sortorder : sortorder
                    };
                    $.post(
                        M.cfg.wwwroot + '/blocks/course_overview/save.php',
                        data
                    );
                }
            });
        }
    };
});
