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
 * Define function import_grades_users
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
 * Define function import_grades_users
 */
class import_grades_users extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item id number'),
            'additional' => new external_value(PARAM_BOOL, 'Only import where no grades currently exist for that user'),
            'fillns' => new external_value(PARAM_BOOL, 'Users with no submission given NS admin grade'),
            'userlist' => new external_multiple_structure(
                new external_value(PARAM_INT)
            ),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param bool $additional
     * @param bool $fillns
     * @param array $userlist
     * @return array
     */
    public static function execute(int $courseid, int $gradeitemid, bool $additional, bool $fillns, array $userlist) {

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'additional' => $additional,
            'fillns' => $fillns,
            'userlist' => $userlist,
        ]);
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        // If already converted then import is not permitted.
        if (\local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid)) {
            throw new \moodle_exception('Import is not permitted after conversion applied.');
        }

        // Get conversion object for whatever grade type this is.
        // Used to convert from Moodle grade to MyGrades format.
        $conversion = \local_gugrades\grades::conversion_factory($courseid, $gradeitemid);
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid);

        $userids = $userlist;
        $importcount = 0;
        foreach ($userids as $userid) {

            // If additional selected then skip users who already have data.
            if ($additional && \local_gugrades\grades::user_has_grades($gradeitemid, $userid)) {
                continue;
            }
            if (\local_gugrades\api::import_grade(
                $courseid,
                $gradeitemid,
                $conversion,
                $activity,
                intval($userid),
                $additional,
                $fillns
                )) {
                $importcount++;
            }
        }

        // Log.
        $event = \local_gugrades\event\import_grades_users::create([
            'objectid' => $gradeitemid,
            'context' => \context_course::instance($courseid),
            'other' => [
                'gradeitemid' => $gradeitemid,
            ],
        ]);
        $event->trigger();

        // Audit.
        \local_gugrades\audit::write($courseid, 0, $gradeitemid, 'Grades imported.');

        return ['importcount' => $importcount];
    }

    /**
     * Define function return
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'importcount' => new external_value(PARAM_INT, 'Number of grades imported'),
        ]);
    }

}
