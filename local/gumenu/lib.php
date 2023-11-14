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
 * This file contains functions used by the participation report
 *
 * @package    local
 * @subpackage gumenu
 * @copyright  2023 Howard Miller ttp://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function local_gumenu_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/guenrol:view', $context)) {

        // Sync
        $url = new \moodle_url('/report/guenrol/index.php', ['id' => $course->id, 'action' => 'sync']);
        $name = get_string('syncenrolments', 'local_gumenu');
        $icon = new pix_icon('i/report', '');
        $navigation->add($name, $url, navigation_node::NODETYPE_LEAF, 'guenrol', null, $icon);
        $navigation->make_active();
    }
}

