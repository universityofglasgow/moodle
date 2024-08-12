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
 * Define function get_conversion_map
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
 * Read a single map
 */
class get_conversion_map extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'mapid' => new external_value(PARAM_INT, 'Map ID - 0 for new/default map'),
            'schedule' => new external_value(PARAM_ALPHA, 'schedulea or scheduleb - only when mapid = 0'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $mapid
     * @param string $schedule
     * @return array
     */
    public static function execute($courseid, $mapid, $schedule) {
        global $DB;

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'mapid' => $mapid,
            'schedule' => $schedule,
        ]);

        // More security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $map = \local_gugrades\api::get_conversion_map($courseid, $mapid, $schedule);

        return $map;
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'name' => new external_value(PARAM_TEXT, 'Conversion map name'),
            'schedule' => new external_value(PARAM_ALPHA, 'schedulea or scheduleb'),
            'maxgrade' => new external_value(PARAM_FLOAT, 'Maximum grade value'),
            'inuse' => new external_value(PARAM_BOOL, 'Is conversion map in use?'),
            'map' => new external_multiple_structure(
                new external_single_structure([
                    'band' => new external_value(PARAM_ALPHANUM, 'Scale band - A1, A2 etc'),
                    'bound' => new external_value(PARAM_FLOAT, 'Lower boundary for this band (as a percentage)'),
                    'grade' => new external_value(PARAM_INT, 'Grade point'),
                ])
            ),
        ]);
    }
}
