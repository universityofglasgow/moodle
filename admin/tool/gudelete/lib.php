<?php
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
 * Callback implementations for gudelete
 *
 * @package    tool_gudelete
 * @copyright  2024 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 function tool_gudelete_extend_navigation_category_settings($navigation, $context) {
    if (has_capability('tool/gudelete:deletecourses', $context)) {
        $navigation->add_node(
            navigation_node::create(
                get_string('deleteallcourses', 'tool_gudelete'),
                new moodle_url(
                    "/admin/tool/gudelete/delete.php",
                    array('category' => $context->instanceid)
                ),
                navigation_node::TYPE_SETTING,
                null,
                null,
                new pix_icon('i/settings', '')
            )
            );
    }
 }