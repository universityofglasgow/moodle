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
 * Define function get aggregation user
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
 * Define function get_aggregation_page
 */
class get_aggregation_user extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradecategoryid' => new external_value(PARAM_INT, 'Grade category id number'),
            'userid' => new external_value(PARAM_INT, 'User ID of required user'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $userid
     * @return array
     */
    public static function execute($courseid, $gradecategoryid, $userid) {

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradecategoryid' => $gradecategoryid,
            'userid' => $userid,
        ]);

        // Security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $page = \local_gugrades\api::get_aggregation_user(
            $courseid, $gradecategoryid, $userid);

        return $page;
    }

    /**
     * Define result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'User ID'),
            'displayname' => new external_value(PARAM_TEXT, 'Name to display for this user'),
            'pictureurl' => new external_value(PARAM_URL, 'URL of user avatar'),
            'profileurl' => new external_value(PARAM_URL, 'Like to user profile page'),
            'idnumber' => new external_value(PARAM_TEXT, 'User ID number'),
            'resitrequired' => new external_value(PARAM_BOOL, 'Is resit required?'),
            'completed' => new external_value(PARAM_INT, '%age of course completed'),
            'displaygrade' => new external_value(PARAM_TEXT, 'Content for total column'),
            'rawgrade' => new external_value(PARAM_FLOAT, 'Aggregated grade before any conversion'),
            'total' => new external_value(PARAM_FLOAT, 'Total grade used for ongoing aggregation'),
            'error' => new external_value(PARAM_TEXT, 'Error condition'),
            'fields' => new external_multiple_structure(
                new external_single_structure([
                    'fieldname' => new external_value(PARAM_TEXT, 'Identifier for column'),
                    'itemname' => new external_value(PARAM_TEXT, 'Shortened item name (for debugging, mostly)'),
                    'display' => new external_value(PARAM_TEXT, 'Grade for display'),
                    'dropped' => new external_value(PARAM_BOOL, 'Has this grade been dropped?'),
                ])
            ),
        ]);
    }

}
