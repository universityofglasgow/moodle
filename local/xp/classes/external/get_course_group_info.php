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
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\external;

use block_xp\di;
use block_xp\external\external_function_parameters;
use block_xp\external\external_multiple_structure;
use block_xp\external\external_single_structure;
use block_xp\external\external_value;
use context_course;

/**
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_course_group_info extends external_api {

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function execute($courseid) {
        global $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact('courseid'));
        extract($params); // @codingStandardsIgnoreLine

        $userid = $USER->id;
        $context = context_course::instance($courseid);
        self::validate_context($context);
        di::get('addon')->require_activated();

        $course = get_course($courseid);
        $groupmode = groups_get_course_groupmode($course);
        $aag = has_capability('moodle/site:accessallgroups', $context);

        if ($groupmode == NOGROUPS && !$aag) {
            $allowedgroups = [];
            $usergroups = [];
        } else if ($groupmode == VISIBLEGROUPS || $aag) {
            $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
            $usergroups = groups_get_all_groups($course->id, $userid, $course->defaultgroupingid);
        } else {
            $allowedgroups = groups_get_all_groups($course->id, $userid, $course->defaultgroupingid);
            $usergroups = $allowedgroups;
        }

        $canseeallparticipants = empty($allowedgroups) || $groupmode == VISIBLEGROUPS || $aag;

        return [
            'canaccessallgroups' => $aag,
            'canseeallparticipants' => $canseeallparticipants,
            'groupmode' => $groupmode,
            'groups' => array_values(array_map(function($group) use ($usergroups, $context) {
                return [
                    'id' => $group->id,
                    'name' => self::format_string($group->name, $context),
                    'ismember' => array_key_exists($group->id, $usergroups),
                ];
            }, $allowedgroups)),
        ];
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure([
            'canaccessallgroups' => new external_value(PARAM_BOOL, 'Whether the user has the permission to access all groups.'),
            'canseeallparticipants' => new external_value(PARAM_BOOL, 'Whether the user can see "All participants".'),
            'groupmode' => new external_value(PARAM_INT),
            'groups' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT),
                'name' => new external_value(PARAM_RAW),
                'ismember' => new external_value(PARAM_BOOL),
            ]), 'The groups that the current user can access.'),
        ]);
    }


}
