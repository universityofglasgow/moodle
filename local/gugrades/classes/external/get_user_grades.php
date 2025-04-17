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
 * Define function get_user_grades
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
 * Define function get_user_grades
 */
class get_user_grades extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID'),
        ]);
    }

    /**
     * Execute function
     * @param int $userid
     * @return array
     */
    public static function execute(int $userid) {

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'userid' => $userid,
        ]);
        $context = \context_user::instance($userid);
        self::validate_context($context);

        // NB. this returns a moodle_url (not a string).
        $grades = \local_gugrades\api::get_user_grades($userid);

        return $grades;
    }

    /**
     * Define function result
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Mystery id'),
                'coursefullname' => new external_value(PARAM_TEXT, 'Course full name'),
                'courseshortname' => new external_value(PARAM_TEXT, 'Course short name'),
                'reasonname' => new external_value(PARAM_TEXT, 'Reason description'),
                'itemname' => new external_value(PARAM_TEXT, 'Grade item name'),
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'gradeitemid' => new external_value(PARAM_INT, 'Grade item ID'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'grade' => new external_value(PARAM_NUMBER, 'Raw grade value'),
                'weightedgrade' => new external_value(PARAM_NUMBER, 'Weighted grade'),
                'reason' => new external_value(PARAM_INT, 'Reason ID'),
                'other' => new external_value(PARAM_TEXT, 'if reason = other'),
                'iscurrent' => new external_value(PARAM_BOOL, 'Is this the current value'),
                'auditby' => new external_value(PARAM_INT, 'User ID. User who added grade'),
                'audittimecreated' => new external_value(PARAM_INT, 'Time created (unix time stamp)'),
                'auditcomment' => new external_value(PARAM_TEXT, 'Audit comment'),
            ])
        );

    }
}

