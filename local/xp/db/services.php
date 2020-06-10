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
 * Local services.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_xp_get_course_group_info' => [
        'classname' => 'local_xp\external',
        'methodname' => 'get_course_group_info',
        'description' => 'Get the course group info',
        'type' => 'read',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
    ],
    'local_xp_get_course_world_ladder' => [
        'classname' => 'local_xp\external',
        'methodname' => 'get_course_world_ladder',
        'description' => 'Get the course ladder',
        'type' => 'read',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
    ],
    'local_xp_get_course_world_group_ladder' => [
        'classname' => 'local_xp\external',
        'methodname' => 'get_course_world_group_ladder',
        'description' => 'Get the course group ladder',
        'type' => 'read',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
    ],
    'local_xp_get_levels_info' => [
        'classname' => 'local_xp\external',
        'methodname' => 'get_levels_info',
        'description' => 'Get the course\'s levels information',
        'type' => 'read',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
    ],
    'local_xp_get_recent_activity' => [
        'classname' => 'local_xp\external',
        'methodname' => 'get_recent_activity',
        'description' => 'Get the user\'s recent activity',
        'type' => 'read',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
    ],
    'local_xp_get_setup' => [
        'classname' => 'local_xp\external',
        'methodname' => 'get_setup',
        'description' => 'Get the course\'s setup',
        'type' => 'read',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
    ],
    'local_xp_get_user_state' => [
        'classname' => 'local_xp\external',
        'methodname' => 'get_user_state',
        'description' => 'Get a user\'s state',
        'type' => 'read',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
    ],

    'local_xp_search_grade_items' => [
        'classname' => 'local_xp\external',
        'methodname' => 'search_grade_items',
        'description' => 'Search through grade items of a course',
        'type' => 'read',
        'ajax' => true
    ],
];
