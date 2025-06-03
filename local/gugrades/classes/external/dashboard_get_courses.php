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
 * Define function dashboard_get_course
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
class dashboard_get_courses extends external_api {

    /**
     * Define parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User to fetch courses for'),
            'current' => new external_value(PARAM_BOOL, 'Return only current courses'),
            'past' => new external_value(PARAM_BOOL, 'Return only past courses'),
            'sort' => new external_value(PARAM_TEXT, 'Comma separated list of fields to sort courses by'),
        ]);
    }

    /**
     * Execute function
     * @param int $userid
     * @param bool $current
     * @param bool $past
     * @param string $sort
     * @return array
     */
    public static function execute($userid, $current, $past, $sort) {

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'userid' => $userid,
            'current' => $current,
            'past' => $past,
            'sort' => $sort,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        return \local_gugrades\api::dashboard_get_courses($userid, $current, $past, $sort);
    }

    /**
     * Define result
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Course ID'),
                'shortname' => new external_value(PARAM_TEXT, 'Short name of course'),
                'fullname' => new external_value(PARAM_TEXT, 'Fullname of course'),
                'startdate' => new external_value(PARAM_INT, 'Start date (unix timestamp)'),
                'enddate' => new external_value(PARAM_INT, 'End date (unix timestamp)'),
                'gugradesenabled' => new external_value(PARAM_BOOL, 'Is display on dashboard enabled for gugrades?'),
                'gcatenabled' => new external_value(PARAM_BOOL, "Is display on dashboard endbaled for GCAT?"),
                'firstlevel' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'Category ID'),
                        'fullname' => new external_value(PARAM_TEXT, 'Full name of grade category'),
                    ])
                ),
            ])
        );
    }

}
