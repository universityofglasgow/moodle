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
 * Define function get_capture_user
 * Get capture information for a single user (after update)
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
 * Define function get_capture_page
 */
class get_capture_user extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item id number'),
            'userid' => new external_value(PARAM_INT, 'User ID'),
            'viewfullnames' => new external_value(PARAM_BOOL, 'Show full names of students (with capability)'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @param bool $viewfullnames
     * @return array
     */
    public static function execute($courseid, $gradeitemid, $userid, $viewfullnames) {

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'userid' => $userid,
            'viewfullnames' => $viewfullnames,
        ]);

        // Security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $user = \local_gugrades\api::get_capture_user($courseid, $gradeitemid, $userid, $viewfullnames);

        return $user;
    }

    /**
     * Define result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'User ID'),
            'displayname' => new external_value(PARAM_TEXT, 'Name to display for this user'),
            'firstinitial' => new external_value(PARAM_TEXT, 'First initial for filtering'),
            'lastinitial' => new external_value(PARAM_TEXT, 'Last initial for filtering'),
            'pictureurl' => new external_value(PARAM_URL, 'URL of user avatar'),
            'profileurl' => new external_value(PARAM_URL, 'Like to user profile page'),
            'idnumber' => new external_value(PARAM_TEXT, 'User ID number'),
            'alert' => new external_value(PARAM_BOOL, 'Show discrepancy alert'),
            'gradehidden' => new external_value(PARAM_BOOL, 'User grades hidden for this grade item'),
            'gradebookhidden' => new external_value(PARAM_BOOL, 'User grade hidden in core Moodle'),
            'grades' => new external_multiple_structure(
                new external_single_structure([
                    'displaygrade' => new external_value(PARAM_TEXT, 'Grade for display'),
                    'gradetype' => new external_value(PARAM_TEXT, 'FIRST, SECOND and so on'),
                    'columnid' => new external_value(PARAM_INT, 'ID in column table'),
                ])
            ),
        ]);
    }

}
