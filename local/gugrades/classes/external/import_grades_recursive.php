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
 * Define function import_grades_recursive
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
 * Define function import_grades_users
 */
class import_grades_recursive extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item id number - import peers and children'),
            'groupid' => new external_value(PARAM_INT, 'Group to import for'),
            'additional' => new external_value(PARAM_ALPHA, 'Import additional grades type. Options are admin, missing, update'),
            'fillns' => new external_value(PARAM_ALPHANUM, 'Users with no submission given NS admin grade. Can be none, fillns or fillns0'),
            'reason' => new external_value(PARAM_TEXT, 'Reason for grade - SECOND, AGREED etc.'),
            'other' => new external_value(PARAM_TEXT, 'Detail if reason == OTHER'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @param string $additional
     * @param string $fillns
     * @return array
     */
    public static function execute(int $courseid, int $gradeitemid, int $groupid, string $additional, string $fillns, string $reason, string $other) {

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'groupid' => $groupid,
            'additional' => $additional,
            'fillns' => $fillns,
            'reason' => $reason,
            'other' => $other,
        ]);
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        // If already converted then import is not permitted.
        if (\local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid)) {
            throw new \moodle_exception('Import is not permitted after conversion applied.');
        }

        [$itemcount, $gradecount] = \local_gugrades\api::import_grades_recursive($courseid,
            $gradeitemid, $groupid, $additional, $fillns, $reason, $other);

        // Log.
        $event = \local_gugrades\event\import_grades_recursive::create([
            'objectid' => $gradeitemid,
            'context' => \context_course::instance($courseid),
            'other' => [
                'gradeitemid' => $gradeitemid,
            ],
        ]);
        $event->trigger();

        // Audit.
        \local_gugrades\audit::write($courseid, 0, $gradeitemid, 'Grades imported - recursive.');

        return ['itemcount' => $itemcount, 'gradecount' => $gradecount];
    }

    /**
     * Define function return
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'itemcount' => new external_value(PARAM_INT, 'Number of individual gradeitems processed'),
            'gradecount' => new external_value(PARAM_INT, 'Number of individual grades imported'),
        ]);
    }

}
