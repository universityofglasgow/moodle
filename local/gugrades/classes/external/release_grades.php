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
 * Define function realase_grades
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
 * Define function release_grades
 */
class release_grades extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course id'),
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item id number'),
            'groupid' => new external_value(PARAM_INT, 'Group ID. 0 means everybody'),
            'revert' => new external_value(PARAM_BOOL, 'Revert release of grades if true'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @param bool $revert
     */
    public static function execute($courseid, $gradeitemid, $groupid, $revert) {

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'groupid' => $groupid,
            'revert' => $revert,
        ]);
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        \local_gugrades\api::release_grades($courseid, $gradeitemid, $groupid, $revert);

        // Log.
        $event = \local_gugrades\event\release_grades::create([
            'objectid' => $gradeitemid,
            'context' => $context,
            'other' => [
                'gradeitemid' => $gradeitemid,
                'groupid' => $groupid,
            ],
        ]);
        $event->trigger();

        // Audit.
        \local_gugrades\audit::write($courseid, 0, $gradeitemid, 'Grades released.');

        return [];
    }

    /**
     * Define result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
        ]);
    }

}
