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
 * Define function dashboard_get_grades
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
 * Get the data associated with a grade item
 */
class dashboard_get_grades extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User to fetch courses for'),
            'gradecategoryid' => new external_value(PARAM_INT, 'Grade category ID'),
        ]);
    }

    /**
     * Execute function
     * @param int $userid
     * @param int $gradecategoryid
     */
    public static function execute($userid, $gradecategoryid) {

        // Security.
        $params = self::validate_parameters(self::execute_parameters(),
            ['userid' => $userid, 'gradecategoryid' => $gradecategoryid]);

        $context = \context_system::instance();
        self::validate_context($context);

        return \local_gugrades\api::dashboard_get_grades($userid, $gradecategoryid);
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'grades' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'id from gugrades_grade table'),
                    'courseid' => new external_value(PARAM_INT, 'Course ID'),
                    'itemtype' => new external_value(PARAM_TEXT, 'Item type'),
                    'itemmodule' => new external_value(PARAM_TEXT, 'Module name'),
                    'iteminstance' => new external_value(PARAM_INT, 'ID of grade item / activity'),
                    'itemname' => new external_value(PARAM_TEXT, 'Full name of item'),
                    'gradetype' => new external_value(PARAM_INT, 'Grade type'),
                    'grademax' => new external_value(PARAM_FLOAT, 'Maximum grade'),
                    'grademin' => new external_value(PARAM_FLOAT, 'Minimum grade'),
                    'displaygrade' => new external_value(PARAM_TEXT, 'Grade formatted for display'),
                    'convertedgrade' => new external_value(PARAM_FLOAT, 'Underlying converted grade value'),
                    'admingrade' => new external_value(PARAM_TEXT, 'Admin grade'),
                ])
            ),
            'childcategories' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Category ID'),
                    'fullname' => new external_value(PARAM_TEXT, 'Full name of grade category'),
                ])
            ),
        ]);
    }

}
