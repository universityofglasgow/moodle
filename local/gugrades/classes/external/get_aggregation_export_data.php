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
 * Get the aggregation export data ready to download
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
class get_aggregation_export_data extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradecategoryid' => new external_value(PARAM_INT, 'Selected grade category ID (in case needed).'),
            'groupid' => new external_value(PARAM_INT, 'Group id. 0 for everybody'),
            'plugin' => new external_value(PARAM_ALPHA, 'Name of export plugin (class)'),
            'form' => new external_multiple_structure(
                new external_single_structure([
                    'identifier' => new external_value(PARAM_TEXT, 'Unique identifier for field'),
                    'selected' => new external_value(PARAM_BOOL, 'Previously selected by this user'),
                ])
            ),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $groupid
     * @param string $plugin
     * @param array $form
     * @return array
     */
    public static function execute($courseid, $gradecategoryid, $groupid, $plugin, $form) {

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradecategoryid' => $gradecategoryid,
            'groupid' => $groupid,
            'plugin' => $plugin,
            'form' => $form,
        ]);

        // Security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        // Log.
        $event = \local_gugrades\event\export_aggregation::create([
            'objectid' => $gradecategoryid,
            'context' => \context_course::instance($courseid),
            'other' => [
            'gradecategoryid' => $gradecategoryid,
            ],
        ]);
        $event->trigger();

        return \local_gugrades\api::get_aggregation_export_data($courseid, $gradecategoryid, $groupid, $plugin, $form);
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'filename' => new external_value(PARAM_TEXT, 'Filename of export'),
            'csv' => new external_value(PARAM_TEXT, 'CSV string'),
        ]);
    }
}
