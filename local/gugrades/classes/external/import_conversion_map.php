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
 * Define function import_conversion_map
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
 * Import a single map
 */
class import_conversion_map extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'jsonmap' => new external_value(PARAM_TEXT, 'New map in json format'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param string $jsonmap
     * @return int
     */
    public static function execute($courseid, $jsonmap) {
        global $DB;

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'jsonmap' => $jsonmap,
        ]);

        // More security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $mapid = \local_gugrades\api::import_conversion_map($courseid, $jsonmap);

        // Log.
        $event = \local_gugrades\event\import_conversion_map::create([
            'objectid' => $mapid,
            'context' => \context_course::instance($courseid),
        ]);
        $event->trigger();

        // Audit.
        \local_gugrades\audit::write($courseid, 0, 0, 'Conversion map imported. ID = ' . $mapid);

        return ['mapid' => $mapid];
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'mapid' => new external_value(PARAM_INT, '(new) map ID'),
        ]);
    }
}
