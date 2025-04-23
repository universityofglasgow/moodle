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
 * Define function save_settings
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Write the data from the 'add grade' button
 */
class save_settings extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradeitemid' => new external_value(PARAM_INT, 'GradeItem ID - optional'),
            'settings' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_ALPHA, 'Config item name/key'),
                    'value' => new external_value(PARAM_TEXT, 'Config item value'),
                ])
            ),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param array $settings
     * @return array
     */
    public static function execute($courseid, $gradeitemid, $settings) {
        global $DB;

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'settings' => $settings,
        ]);

        // More security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);
        require_capability('local/gugrades:changesettings', $context);

        \local_gugrades\api::save_settings($courseid, $gradeitemid, $settings);

        // Log.
        $event = \local_gugrades\event\settings_updated::create([
            'objectid' => $gradeitemid,
            'context' => \context_course::instance($courseid),
        ]);
        $event->trigger();

        return [];
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([

        ]);
    }

}
