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
use block_xp\local\iterator\map_iterator;
use block_xp\local\sql\limit;
use moodle_exception;

/**
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_course_world_ladder extends external_api {

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'groupid' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'page' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 50),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @param int $groupid The group ID.
     * @param int $page The page number.
     * @param int $perpage The number of items per page.
     * @return object
     */
    public static function execute($courseid, $groupid, $page, $perpage) {
        global $USER;
        $params = self::validate_parameters(self::execute_parameters(),
            compact('courseid', 'groupid', 'page', 'perpage'));
        extract($params); // @codingStandardsIgnoreLine

        $worldfactory = di::get('course_world_factory');
        $world = $worldfactory->get_world($courseid);
        $config = $world->get_config();
        $courseid = $world->get_courseid(); // Ensure that we get the real course ID.
        $userid = $USER->id;
        self::validate_context($world->get_context());
        di::get('addon')->require_activated();

        // Permission checks: can manage, or can access, and the ladder is enabled.
        $perms = $world->get_access_permissions();
        if (!$perms->can_manage()) {
            $perms->require_access();
            if (!$config->get('enableladder')) {
                throw new moodle_exception('nopermissions', '', '', 'view_ladder_page');
            }
        }

        // Check access to group.
        self::require_group_access($userid, $courseid, $groupid);
        $isgroupmember = $groupid && groups_is_member($groupid, $userid);

        // Config.
        $neighbours = $config->get('neighbours');
        $rankmode = $config->get('rankmode');
        $identitymode = $config->get('identitymode');

        // Leaderboard.
        $leaderboard = \block_xp\di::get('course_world_leaderboard_factory')->get_course_leaderboard($world, $groupid);

        // Determine what page to show first.
        if (!$neighbours && !$page) {
            $pos = $leaderboard->get_position($userid);
            $page = ceil(($pos + 1) / $perpage);
        }

        // Pagination.
        $limit = new limit($perpage);
        if ($neighbours > 0) {
            $page = 1;
        } else {
            $limit = new limit($perpage, ($page - 1) * $perpage);
        }

        $data = [
            'columns' => array_keys($leaderboard->get_columns()),
            'page' => $page,
            'total' => $leaderboard->get_count(),
            'ranking' => iterator_to_array(new map_iterator(
                    $leaderboard->get_ranking($limit),
                    function($rank) {
                        return [
                            'rank' => $rank->get_rank(),
                            'state' => self::serialize_state($rank->get_state(), true),
                        ];
                    }
            ), false),
            'identitymode' => $identitymode,
            'rankmode' => $rankmode,
            'neighbours' => $neighbours,
        ];

        return $data;
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure([
            'columns' => new external_multiple_structure(new external_value(PARAM_ALPHANUMEXT)),
            'page' => new external_value(PARAM_INT),
            'total' => new external_value(PARAM_INT),
            'ranking' => new external_multiple_structure(
                new external_single_structure([
                    'rank' => new external_value(PARAM_INT),
                    'state' => self::state_description(VALUE_OPTIONAL),
                ])
            ),
            'identitymode' => new external_value(PARAM_INT),
            'rankmode' => new external_value(PARAM_INT),
            'neighbours' => new external_value(PARAM_INT),
        ]);
    }

}
