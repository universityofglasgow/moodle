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
            'additional' => new external_value(PARAM_ALPHA, 'Import additional grades type. Options are admin, missing, update'),
            'fillns' => new external_value(PARAM_ALPHANUM, 'Users with no submission given NS admin grade. Can be none, fillns or fillns0'),
            'reason' => new external_value(PARAM_TEXT, 'Reason for grade - SECOND, AGREED etc.'),
            'other' => new external_value(PARAM_TEXT, 'Detail if reason == OTHER'),
            'dryrun' => new external_value(PARAM_BOOL, 'Dry run if true, only return numbers'),
            'userlist' => new external_multiple_structure(
                new external_value(PARAM_INT)
            ),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradeitemid
     * @param string $additional
     * @param string $fillns
     * @param string $reason
     * @param string $other
     * @param bool $dryrun
     * @param array $userlist
     * @return array
     */
    public static function execute(int $courseid, int $gradeitemid, string $additional, string $fillns, string $reason, string $other, bool $dryrun, array $userlist) {

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'additional' => $additional,
            'fillns' => $fillns,
            'reason' => $reason,
            'other' => $other,
            'dryrun' => $dryrun,
            'userlist' => $userlist,
        ]);
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        set_time_limit(0);

        // If already converted then import is not permitted.
        if (\local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid)) {
            throw new \moodle_exception('Import is not permitted after conversion applied.');
        }

        // Get mapping object for whatever grade type this is.
        // Used to convert from Moodle grade to MyGrades format.
        $mapping = \local_gugrades\grades::mapping_factory($courseid, $gradeitemid);
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid);

        $userids = $userlist;
        $importcount = 0;

        //\local_gugrades\development::xhprof_start();

        // Track progress
        $count = 0;

        foreach ($userids as $userid) {

            // If additional selected then skip users who already have data.
            //if ($additional && \local_gugrades\grades::user_has_grades($gradeitemid, $userid)) {
            //    continue;
            //}
            if (\local_gugrades\api::import_grade(
                courseid:       $courseid,
                gradeitemid:    $gradeitemid,
                mapping:        $mapping,
                activity:       $activity,
                userid:         intval($userid),
                additional:     $additional,
                fillns:         $fillns,
                reason:         $reason,
                other:          $other,
                noaggregation:  false,
                dryrun:         $dryrun,
                )) {
                $importcount++;
            }

            // Record progress.
            $count++;
            $progress = 100 * $count / count($userids);
            \local_gugrades\progress::record($courseid, 0, 'import', $progress);
        }

        //\local_gugrades\development::xhprof_stop();

        // Log.
        if (!$dryrun) {
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
        }

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
