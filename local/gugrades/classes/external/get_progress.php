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
 * Define function get progress
 * @package    local_gugrades
 * @copyright  2025
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
class get_progress extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'uniqueid' => new external_value(PARAM_INT, 'Unique identifier or 0'),
            'progresstype' => new external_value(PARAM_TEXT, 'type of progress'),
            'staffuserid' => new external_value(PARAM_TEXT, 'Userid of user'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int uniqueid
     * @param string $progresstype
     * @param int $staffuserid
     * @return array
     */
    public static function execute($courseid, $uniqueid, $progresstype, $staffuserid) {

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'uniqueid' => $uniqueid,
            'progresstype' => $progresstype,
            'staffuserid' => $staffuserid,
        ]);

        return \local_gugrades\api::get_progress($courseid, $uniqueid, $progresstype, $staffuserid);
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'progress' => new external_value(PARAM_INT, 'Progress value'),
        ]);
    }
}
