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
 * Define function csv_upload
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
class upload_csv extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item ID'),
            'groupid' => new external_value(PARAM_INT, 'Group ID'),
            'testrun' => new external_value(PARAM_BOOL, 'If true, only test data and return. Do not write'),
            'reason' => new external_value(PARAM_TEXT, 'Reason (SECOND, THIRD and so on)'),
            'other' => new external_value(PARAM_TEXT, '...if Other reason'),
            'csv' => new external_value(PARAM_TEXT, 'Raw CSV file data'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @param int $testrun
     * @param string $reason
     * @param string $other
     * @param string $csv
     * @return array
     */
    public static function execute($courseid, $gradeitemid, $groupid, $testrun, $reason, $other, $csv) {

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'groupid' => $groupid,
            'testrun' => $testrun,
            'reason' => $reason,
            'other' => $other,
            'csv' => $csv,
        ]);

        // Security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        [$lines, $errorcount, $addcount, $errorlist] = \local_gugrades\api::csv_upload($courseid, $gradeitemid, $groupid,
            $testrun, $reason, $other, $csv);

        // Log.
        $event = \local_gugrades\event\upload_csv::create([
            'objectid' => $gradeitemid,
            'context' => \context_course::instance($courseid),
            'other' => [
                'gradeitemid' => $gradeitemid,
            ],
        ]);
        $event->trigger();

        // Audit.
        \local_gugrades\audit::write($courseid, 0, $gradeitemid, 'Grades uploaded from CSV.');

        return [
            'lines' => $lines,
            'errorcount' => $errorcount,
            'addcount' => $addcount,
            'errorlist' => $errorlist,
        ];
    }

    /**
     * Define function result
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'lines' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'User name'),
                    'idnumber' => new external_value(PARAM_TEXT, 'User ID number'),
                    'grade' => new external_value(PARAM_TEXT, 'Grade to assign'),
                    'gradevalue' => new external_value(PARAM_FLOAT, 'Grade value'),
                    'state' => new external_value(PARAM_INT, '< 0 is error; 0 is skip; >0 is ok'),
                    'error' => new external_value(PARAM_TEXT, 'Any error condition'),
                ])
            ),
            'errorcount' => new external_value(PARAM_INT, 'Count of error lines'),
            'addcount' => new external_value(PARAM_INT, 'Count of added grades'),
            'errorlist' => new external_multiple_structure(
                new external_single_structure([
                    'error' => new external_value(PARAM_TEXT, 'Name of error/warning'),
                    'count' => new external_value(PARAM_INT, 'Error/warning count'),
                ])
            )
        ]);
    }
}
