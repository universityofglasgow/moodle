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
 * Define function get_capture_export_data
 * @package    local_gugrades
 * @copyright  2024
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
 * Define function get_audit
 */
class get_capture_export_data extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item ID'),
            'groupid' => new external_value(PARAM_INT, 'Group ID'),
            'viewfullnames' => new external_value(PARAM_BOOL, 'View full names'),
            'options' => new external_multiple_structure(
                new external_single_structure([
                    'gradetype' => new external_value(PARAM_TEXT, 'Short name of grade type'),
                    'selected' => new external_value(PARAM_BOOL, 'Previously selected by this user'),
                ]),
            )
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @param bool $viewfullnames
     * @param array $options
     * @return array
     */
    public static function execute($courseid, $gradeitemid, $groupid, $viewfullnames, $options) {

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'groupid' => $groupid,
            'viewfullnames' => $viewfullnames,
            'options' => $options,
        ]);

        // Security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $csv = \local_gugrades\api::get_capture_export_data($courseid, $gradeitemid, $groupid, $viewfullnames, $options);

        // Log.
        $event = \local_gugrades\event\export_capture::create([
            'objectid' => $gradeitemid,
            'context' => \context_course::instance($courseid),
            'other' => [
                'gradeitemid' => $gradeitemid,
            ],
        ]);
        $event->trigger();

        // Audit.
        \local_gugrades\audit::write($courseid, 0, $gradeitemid, 'Grade capture data exported.');

        return ['csv' => $csv];
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'csv' => new external_value(PARAM_TEXT, 'CSV string'),
        ]);
    }
}
