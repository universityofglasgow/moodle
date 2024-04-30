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
class get_user_state extends external_api {

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'userid' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @param int $userid The user ID.
     * @return object
     */
    public static function execute($courseid, $userid) {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact('courseid', 'userid'));
        extract($params); // @codingStandardsIgnoreLine

        $userid = $userid ? $userid : $USER->id;
        $world = di::get('course_world_factory')->get_world($courseid);
        $courseid = $world->get_courseid();
        self::validate_context($world->get_context());
        di::get('addon')->require_activated();

        $world->get_access_permissions()->require_access();
        if ($userid != $USER->id) {
            $world->get_access_permissions()->require_manage();
        }

        $store = $world->get_store();
        $state = $store->get_state($userid);

        return self::serialize_state($state);
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return self::state_description();
    }

}
