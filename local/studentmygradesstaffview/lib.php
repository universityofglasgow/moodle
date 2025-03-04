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
 * Extend navigation
 *
 * @package     local_studentmygradesstaffview
 * @author      Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright   2025 University of Glasgow
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * Adds link to course navigation
  * @param object $parentnode
  * @param object $course
  * @param object $context
  */
function local_studentmygradesstaffview_extend_navigation_course($parentnode, $course, $context) {
    if ($course->showgrades == 1) {
        if (!has_capability('local/studentmygradesstaffview:staffview', $context)) {
            return;
        }
        $url = new moodle_url('/local/studentmygradesstaffview/ui/dist/index.php', ['id' => $course->id]);
        $name = get_string('staffview', 'local_studentmygradesstaffview');
        $icon = new pix_icon('t/grades', '');
        $parentnode->add($name, $url, navigation_node::NODETYPE_LEAF, 'studentmygradesstaffview', null, $icon);
        $parentnode->make_active();
    }    
}

