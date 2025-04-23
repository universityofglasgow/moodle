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
 * Define function get list of plugins for aggregation export
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
class get_aggregation_export_plugins extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradecategoryid' => new external_value(PARAM_INT, 'Selected grade category ID (in case needed).'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradecategoryid
     * @return array
     */
    public static function execute($courseid, $gradecategoryid) {

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradecategoryid' => $gradecategoryid,
        ]);

        // Security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        return \local_gugrades\api::get_aggregation_export_plugins($courseid, $gradecategoryid);
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'plugins' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_ALPHA, 'Plugin class name'),
                    'description' => new external_value(PARAM_TEXT, 'Description of plugin'),
                ])
            ),
            'filename' => new external_value(PARAM_TEXT, 'Proposed filename'),
        ]);
    }
}
