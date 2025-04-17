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
 * @package    local_gugrades
 * @copyright  2022
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * Adds link to course navigation
  * @param object $parentnode
  * @param object $course
  * @param object $context
  */
function local_gugrades_extend_navigation_course($parentnode, $course, $context) {
    if (!has_capability('local/gugrades:view', $context)) {
        return;
    }
    $url = new moodle_url('/local/gugrades/ui/dist/index.php', ['id' => $course->id]);
    $name = get_string('mygradesbeta', 'local_gugrades');
    $icon = new pix_icon('t/grades', '');
    $parentnode->add($name, $url, navigation_node::NODETYPE_LEAF, 'gugrades', null, $icon);
    $parentnode->make_active();
}

