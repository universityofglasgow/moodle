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
 * Define function get_alter_weight_form
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
 * Get the information to construct add grade form
 */
class get_alter_weight_form extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'categoryid' => new external_value(PARAM_INT, 'Grade category id'),
            'userid' => new external_value(PARAM_INT, 'User id - for user we are adding grade'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $categoryid
     * @param int $userid
     * @return array
     */
    public static function execute($courseid, $categoryid, $userid) {
        global $DB;

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'categoryid' => $categoryid,
            'userid' => $userid,
        ]);

        // More security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        return \local_gugrades\api::get_alter_weight_form($courseid, $categoryid, $userid);
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'categoryname' => new external_value(PARAM_TEXT, 'Category name'),
            'userfullname' => new external_value(PARAM_TEXT, 'User full name'),
            'idnumber' => new external_value(PARAM_TEXT, 'User ID number'),
            'items' => new external_multiple_structure(
                new external_single_structure([
                    'fullname' => new external_value(PARAM_TEXT, 'Name of grade item'),
                    'gradeitemid' => new external_value(PARAM_INT, 'Grade item id'),
                    'gradetype' => new external_value(PARAM_TEXT, 'Grade type'),
                    'display' => new external_value(PARAM_TEXT, 'Current displayed grade'),
                    'originalweight' => new external_value(PARAM_FLOAT, 'Weight from grade_items table.'),
                    'alteredweight' => new external_value(PARAM_FLOAT, 'Altered weighting (same as original is isaltered = false)'),
                    'isaltered' => new external_value(PARAM_BOOL, 'Is this an altered weight?'),
                ])
            ),
        ]);
    }

}
