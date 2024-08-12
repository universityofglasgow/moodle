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
 * Define function write_column
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
 * Write the data from the 'add grade' button
 */
class write_column extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item id'),
            'reason' => new external_value(PARAM_TEXT, 'Reason for grade - SECOND, AGREED etc.'),
            'other' => new external_value(PARAM_TEXT, 'Detail if reason == OTHER'),
            'notes' => new external_value(PARAM_TEXT, 'Optional notes'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param string $reason
     * @param string $other
     * @param string $notes
     * @return array
     */
    public static function execute($courseid, $gradeitemid, $reason, $other, $notes) {
        global $DB;

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'reason' => $reason,
            'other' => $other,
            'notes' => $notes,
        ]);

        // More security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $columnid = \local_gugrades\api::write_column(
            $courseid,
            $gradeitemid,
            $reason,
            $other,
            $notes,
        );

        return ['columnid' => $columnid];
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'columnid' => new external_value(PARAM_INT, 'Column ID'),
        ]);
    }

}
