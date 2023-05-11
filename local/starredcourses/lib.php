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
 * Implementation of the Starred Courses feature that was introduced
 * into the left nav under the Moodle 3.x install.
 *
 * @package    local_starredcourses
 * @copyright  2023 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_starredcourses_extend_navigation_course(navigation_node $parent, stdClass $course, context_course $context) {
    global $USER;

    if (empty($USER->id) || !has_capability('local/starredcourses:view', $context, $USER)) {
        return;
    }

    $usercontext = context_user::instance($USER->id);
    $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

    if ($ufservice->favourite_exists('core_course', 'courses', $course->id, \context_course::instance($course->id))) {
        $name = get_string('unstarcourse', 'local_starredcourses');
        $url = new moodle_url('/theme/hillhead40/course-unstar.php?id=' . $course->id);
    } else {
        $name = get_string('starcourse', 'local_starredcourses');
        $url = new moodle_url('/theme/hillhead40/course-star.php?id=' . $course->id);
    }

    $parent->add($name, $url, navigation_node::NODETYPE_LEAF, $name, 'starredcourses-settings');
}
