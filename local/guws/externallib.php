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
 * Export custom web services used by UofG systems
 *
 * @package    local_guws
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
 
class local_guws_external extends external_api {

    /**
     * Parameter definition for ams_searchassign
     * @return external_funtion_parameters
     */
    public static function ams_searchassign_parameters() {
        return new external_function_parameters([
            'code' => new external_value(PARAM_TEXT, 'Substring to search for in Assignment names'),
            'date' => new external_value(PARAM_ALPHANUM, 'Target date in YYYYMMDD format. Will only return Assignments that where active on this date', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Return definition for amd_searchassign
     * @returns external_multiple_structure
     */
    public static function ams_searchassign_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Assignment id'),
                'cm' => new external_value(PARAM_INT, 'Assignment course module id'),
                'name' => new external_value(PARAM_TEXT, 'Assignment name in full'),
                'startdate' => new external_value(PARAM_TEXT, 'Course start date in ISO format'),
                'enddate' => new external_value(PARAM_TEXT, 'Course end date in ISO format'),
            ]),
        '', VALUE_OPTIONAL);
    }

    /**
     * Search assignment for specific string in name
     * @param string $code substring to find
     * @param string $date date must be within course start and end dates
     */
    public static function ams_searchassign($code, $date) {
        global $CFG, $DB, $USER;

        // Check params
        $params = self::validate_parameters(self::ams_searchassign_parameters(), ['code' => $code, 'date' => $date]);

        // Target date converted to timestamp.
        if ($params['date']) {
            $udate = strtotime($params['date']);
        } else {
            $date = 0;
        }

        // Get all the courses this user can access.
        // Final true means all that can be accessed
        $courses = enrol_get_my_courses(['id', 'startdate', 'enddate'], null, 0, [], true);

        // Are there any courses?
        if (!$courses) {
            throw new invalid_response_exception('No courses found for user ' . $USER->username);
        }

        // Find assignments
        $found = [];
        foreach ($courses as $course) {

            // Check for valid date
            if ($udate) {

                // If there's a course start date make sure date is after this.
                if ($udate < $course->startdate) {
                    continue;
                }

                // If there's a course end date then supplied date must be before.
                if ($course->enddate && ($udate > $course->enddate)) {
                    continue;
                }
            }
            $assignments = $DB->get_records('assign', ['course' => $course->id]);
            foreach ($assignments as $assignment) {
                if (stripos($assignment->name, $params['code']) !== false) {

                    // Find cmid
                    $cm = get_coursemodule_from_instance('assign', $assignment->id, $course->id, false, MUST_EXIST);

                    // Happy. Add to results.
                    $found[] = [
                        'id' => $assignment->id,
                        'cm' => $cm->id,
                        'name' => $assignment->name,
                        'startdate' => date('Ymd', $course->startdate),
                        'enddate' => date('Ymd', $course->enddate),
                    ];
                }
            }
        }

        // Exception if there is nothing.
        if (!$found) {
            throw new invalid_response_exception('No matching assignments found for code ' . $params['code']);
        }

        return $found;
    }

    /**
     * Parameter definition for ams_download
     * @return external_funtion_parameters
     */
    public static function ams_download_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Assignment instance id'),
        ]);
    }

    /**
     * Return definition for amd_searchassign
     * @returns external_multiple_structure
     */
    public static function ams_download_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'userid' => new external_value(PARAM_INT, 'Moodle user id'),
                'participantid' => new external_value(PARAM_INT, 'Participant number'),
                'email' => new external_value(PARAM_TEXT, 'User email address'),
                'groups' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'Moodle internal group id'),
                        'name' => new external_value(PARAM_TEXT, 'Group name') 
                    ])
                ),
                'status' => new external_value(PARAM_TEXT, 'Submission status'),
                'timemodifiedsubmission' => new external_value(PARAM_TEXT, 'Date/time submission last modified'),
                'timemodifiedgrade' => new external_value(PARAM_TEXT, 'Date/time grade last modified'),
                'grade' => new external_value(PARAM_TEXT, 'Grade given'),
                'feedbackcomments' => new external_value(PARAM_RAW, 'Feedback comments'),
            ])
        );
    }

    /**
     * Download Assignment details and feedback
     * @param int feedback instance id
     */
    public static function ams_download($id) {
        global $CFG, $DB, $USER;

        // load the Assign module class
        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        // Check params
        $params = self::validate_parameters(self::ams_download_parameters(), ['id' => $id]);

        // Get assignment (one or two steps).
        $assignment = $DB->get_record('assign', ['id' => $params['id']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $assignment->course], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('assign', $assignment->id, $course->id, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        $assign = new assign($context, $cm, $course);

        // Ok to see this data?
        require_capability('mod/assign:view', $context);

        // Negative grade means a scale
        if ($assignment->grade < 0) {
            $scaleid = abs($assignment->grade);
            $scale = $DB->get_record('scale', ['id' => $scaleid], '*', MUST_EXIST);
            $scaleitems = array_map('trim', explode(',', $scale->scale));
        } else {
            $scaleitems = null;
        }

        // Make sure participant IDs are assigned
        $assign->allocate_unique_ids($assignment->id);

        // Get list of assignment participants.
        $participants = $assign->list_participants(0, false);

        // Get results
        $results = [];
        foreach ($participants as $participant) {

            // Participant ID
            $mapping = $DB->get_record('assign_user_mapping', ['assignment' => $assignment->id, 'userid' => $participant->id]);

            // Submission details.
            $submission = $DB->get_record('assign_submission', ['assignment' => $assignment->id, 'userid' => $participant->id]);

            // Assignment grades
            $grades = $DB->get_record('assign_grades', ['assignment' => $assignment->id, 'userid' => $participant->id], '*', MUST_EXIST);
            if ($grades->grade > -1) {
                if ($scaleitems) {
                    $grade = $scaleitems[intval($grades->grade)];
                } else {
                    $grade = $grades->grade;
                }
            } else {
                $grade = '';
            }

            // Get feedback
            if ($grades) {
                $comments = $DB->get_record('assignfeedback_comments', ['grade' => $grades->id]);
            } else {
                $comments = null;
            }

            // Get groups
            $sql = "SELECT g.id, name FROM {groups} g
                JOIN {groups_members} gm ON gm.groupid = g.id
                WHERE courseid = ?
                AND userid = ?";
            $groups = $DB->get_records_sql($sql, [$course->id, $participant->id]);
            $groupdata = [];
            foreach ($groups as $group) {
                $groupdata[] = [
                    'id' => $group->id,
                    'name' => $group->name,
                ];
            }

            // Build data record.
            $results[] = [
                'userid' => $participant->id,
                'participantid' => $mapping ? $mapping->id : 0,
                'email' => $participant->email,
                'groups' => $groupdata,
                'status' => $submission ? $submission->status : '',
                'timemodifiedsubmission' => $submission ? date('YmdHis', $submission->timemodified) : '',
                'timemodifiedgrade' => $grades ? date('YmdHis', $grades->timemodified) : '',
                'grade' => $grade,
                'feedbackcomments' => $comments ? $comments->commenttext : '',
            ]; 
        }

        if (!$results) {
            throw new invalid_response_exception('No matching users found for code assignment ' . $assignment->name);
        }

        return $results;
    }
}
