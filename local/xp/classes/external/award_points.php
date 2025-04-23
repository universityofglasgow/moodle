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
use block_xp\external\external_single_structure;
use block_xp\external\external_value;
use block_xp\local\utils\user_utils;
use block_xp\local\xp\state_store_with_reason;
use local_xp\local\reason\manual_reason;
use moodle_exception;

/**
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class award_points extends external_api {

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'userid' => new external_value(PARAM_INT),
            'points' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * Search grade items.
     *
     * @param int $courseid The course ID.
     * @param int $userid The user ID.
     * @param int $points The points.
     * @return array
     */
    public static function execute($courseid, $userid, $points) {
        global $CFG, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact('courseid', 'userid', 'points'));
        $courseid = $params['courseid'];
        $userid = $params['userid'];
        $points = $params['points'];

        $world = di::get('course_world_factory')->get_world($courseid);
        self::validate_context($world->get_context());
        di::get('addon')->require_activated();
        $world->get_access_permissions()->require_manage();

        if (!user_utils::can_earn_points($world->get_context(), $userid)) {
            throw new moodle_exception('cannotearnpoints', 'block_xp');
        } else if ($points <= 0) {
            throw new moodle_exception('invaliddata', 'core_error');
        }

        $store = $world->get_store();
        if ($store instanceof state_store_with_reason) {
            $store->increase_with_reason($userid, $points, new manual_reason($USER->id));
        } else {
            $store->increase($userid, $points);
        }

        return ['xp' => $store->get_state($userid)->get_xp()];
    }

    /**
     * External function return values.
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_single_structure([
            'xp' => new external_value(PARAM_INT),
        ]);
    }

}
