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
use block_xp\external\external_value;
use moodle_exception;

/**
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_levels_info extends external_api {

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

        $worldfactory = di::get('course_world_factory');
        $world = $worldfactory->get_world($courseid);
        $courseid = $world->get_courseid(); // Ensure that we get the real course ID.
        self::validate_context($world->get_context());
        di::get('addon')->require_activated();

        // Permission checks: can manage, or can access, and the info page is enabled.
        $perms = $world->get_access_permissions();
        if (!$perms->can_manage()) {
            $perms->require_access();
            if (!$world->get_config()->get('enableinfos')) {
                throw new moodle_exception('nopermissions', '', '', 'view_infos_page');
            }
        }

        return self::serialize_levels_info($world->get_levels_info());
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return self::levels_info_description();
    }

}
