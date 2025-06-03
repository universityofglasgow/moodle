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
 * Define function get_all_strings
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
 * Define function get_all_strings
 */
class get_all_strings extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            // No parameters.
        ]);
    }

    /**
     * Execute function
     * @return array
     */
    public static function execute() {

        \local_gugrades\development::increase_debugging();

        return \local_gugrades\api::get_all_strings();
    }

    /**
     * Define function result
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'tag' => new external_value(PARAM_TEXT, 'String tag'),
                'stringvalue' => new external_value(PARAM_RAW, 'Translated string value'),
            ])
        );
    }

}
