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
use block_xp\external\external_value;

/**
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_recent_activity extends external_api {

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
     * @return array
     */
    public static function execute($courseid) {
        global $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact('courseid'));
        extract($params); // @codingStandardsIgnoreLine

        $worldfactory = di::get('course_world_factory');
        $world = $worldfactory->get_world($courseid);
        $courseid = $world->get_courseid(); // Ensure that we get the real course ID.
        self::validate_context($world->get_context());
        di::get('addon')->require_activated();

        // Permission checks.
        $perms = $world->get_access_permissions();
        $perms->require_access();

        // We must find the block to get how many recent activity to return.
        // This is not ideal, but it is inline with what setup returns.
        $bifinder = di::get('course_world_block_any_instance_finder_in_context');

        // Find the block instance, we use the course context because the front page is a course.
        $bi = $bifinder->get_any_instance_in_context('xp', $world->get_context());
        if (!$bi) {
            return [];
        }

        $blockconfig = self::make_block_config($bi);
        $recentactivity = $blockconfig->get('recentactivity');
        if ($recentactivity < 1) {
            return [];
        }

        $repo = $world->get_user_recent_activity_repository();
        $recentactivityitems = $repo->get_user_recent_activity($USER->id, $recentactivity);

        return array_values(array_map('self::serialize_activity', $recentactivityitems));
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_multiple_structure(self::activity_description());
    }

}
