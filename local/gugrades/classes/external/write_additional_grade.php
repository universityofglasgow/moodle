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
 * Define function write_additional_grade
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
class write_additional_grade extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item id'),
            'userid' => new external_value(PARAM_INT, 'User id - for user we are adding grade'),
            'reason' => new external_value(PARAM_TEXT, 'Reason for grade - SECOND, AGREED etc.'),
            'other' => new external_value(PARAM_TEXT, 'Detail if reason == OTHER'),
            'admingrade' => new external_value(PARAM_ALPHANUM, 'Admin grade code - overrides a grade'),
            'scale' => new external_value(PARAM_INT, 'Scale value'),
            'grade' => new external_value(PARAM_FLOAT, 'Points grade'),
            'notes' => new external_value(PARAM_TEXT, 'Optional notes'),
            'delete' => new external_value(PARAM_BOOL, 'Delete overridden category', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @param string $reason
     * @param string $other
     * @param string $admingrade
     * @param int $scale
     * @param float $grade
     * @param string $notes
     * @parm bool $delete
     * @return array
     */
    public static function execute($courseid, $gradeitemid, $userid, $reason, $other, $admingrade, $scale, $grade, $notes, $delete = false) {
        global $DB;

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'userid' => $userid,
            'reason' => $reason,
            'other' => $other,
            'admingrade' => $admingrade,
            'scale' => $scale,
            'grade' => $grade,
            'notes' => $notes,
            'delete' => $delete,
        ]);

        // Get item (if it exists).
        $item = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);

        // More security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        \local_gugrades\api::write_additional_grade(
            $courseid,
            $gradeitemid,
            $userid,
            $reason,
            $other,
            $admingrade,
            $scale,
            $grade,
            $notes,
            $delete
        );

        // Log.
        $event = \local_gugrades\event\additional_grade::create([
            'objectid' => $gradeitemid,
            'context' => \context_course::instance($courseid),
            'relateduserid' => $userid,
            'other' => [
                'gradeitemid' => $gradeitemid,
                'userid' => $userid,
                'reason' => $reason,
                'other' => $other,
                'scale' => $scale,
                'grade' => $grade,
            ],
        ]);
        $event->trigger();

        // Audit.
        \local_gugrades\audit::write($courseid, $userid, $gradeitemid, 'Additional grade written');

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
