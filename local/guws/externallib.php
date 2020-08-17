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
     * Return definition for amd_download
     * @returns external_multiple_structure
     */
    public static function ams_download_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'userid' => new external_value(PARAM_INT, 'Moodle internal user id'),
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
     * Get assignment and cm
     * @param int $assignmentid
     * @return [object $course, object $assignment, object $assign, object $cm]
     */
    protected static function ams_assignment_from_id($id) {
        global $CFG, $DB;

        // load the Assign module class
        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        // Get assignment (one or two steps).
        $assignment = $DB->get_record('assign', ['id' => $id], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $assignment->course], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('assign', $assignment->id, $course->id, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        $assign = new local_guws\assign($context, $cm, $course);

        return [$course, $assignment, $assign, $context];
    }

    /**
     * Download Assignment details and feedback
     * @param int feedback instance id
     */
    public static function ams_download($id) {
        global $CFG, $DB, $USER;

        // Check params
        $params = self::validate_parameters(self::ams_download_parameters(), ['id' => $id]);

        // Get assignment
        list($course, $assignment, $assign, $context) = self::ams_assignment_from_id($params['id']);

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

        // fix any missing grades
        $assign->fix_null_grades();

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
            $grades = $DB->get_record('assign_grades', ['assignment' => $assignment->id, 'userid' => $participant->id]);
            if ($grades && ($grades->grade > -1)) {
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

    /**
     * Parameter definition for ams_upload
     * @return external_funtion_parameters
     */
    public static function ams_upload_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Assignment instance id'),
            'participants' => new external_multiple_structure(
                new external_single_structure([
                    'userid' => new external_value(PARAM_INT, 'Moodle internal user id'),
                    'grade' => new external_value(PARAM_TEXT, 'Grade value or scale item'),
                    'feedback' => new external_value(PARAM_RAW, 'Feedback comments'),
                ])
            )
        ]);
    }

    /**
     * Return definition for ams_upload
     * @returns external_multiple_structure
     */
    public static function ams_upload_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'userid' => new external_value(PARAM_INT, 'Moodle user id'),
                'success' => new external_value(PARAM_BOOL, 'Upload succeeded'),
                'message' => new external_value(PARAM_TEXT, 'Error message if success = false'),
            ])
        );
    }

    /**
     * Upload grade/feedback data to Assignment
     * @param int $id instance id of Assignment
     * @param array $data data to upload to Assignment
     */
    public static function ams_upload($id, $participants) {
        global $CFG, $DB, $USER;

        // Check params
        $params = self::validate_parameters(self::ams_upload_parameters(), ['id' => $id, 'participants' => $participants]);

        // Get assignment
        list($course, $assignment, $assign, $context) = self::ams_assignment_from_id($params['id']);

        // Negative grade means a scale
        if ($assignment->grade < 0) {
            $scaleid = abs($assignment->grade);
            $scale = $DB->get_record('scale', ['id' => $scaleid], '*', MUST_EXIST);
            $scaleitems = array_map('trim', explode(',', $scale->scale));
        } else {
            $scaleitems = null;
        }

        // Ok to grade Assignment?
        require_capability('mod/assign:grade', $context);

        // Get list of assignment participants (to compare against
        // those supplied)
        $currentparticipants = $assign->list_participants(0, false);

        // Build results
        $results = [];
        foreach ($params['participants'] as $participant) {
            $userid = $participant['userid'];
      
            // Check the user is a participant
            if (!array_key_exists($userid, $currentparticipants)) {
                $results[] = [
                    'userid' => $userid,
                    'success' => false,
                    'message' => 'User is not a participant in the Assignment'
                ];
                continue;
            }

            // If grade is scale then check/translate
            if ($scaleitems) {
                $key = array_search($participant['grade'], $scaleitems);
                if ($key === false) {
                    throw new invalid_parameter_exception('Grade item is not valid. A scale item is expected for this Assignment - ' . $participant['grade']);
                } else {
                    $grade = $key + 1;
                }
            } else {
                $grade = $participant['grade'];
            }

            // Create fake grading form
            $data = new stdClass;
            $data->attemptnumber = 0;
            $data->advancedgrading = 0;
            $data->grade = $grade;
            $data->assignfeedbackcomment = [];
            $data->assignfeedbackcomments_editor['text'] = $participant['feedback'];
            $data->assignfeedbackcomments_editor['format'] = FORMAT_MOODLE;
            $updated = $assign->save_grade($userid, $data);

            // Check
            if (!$updated) {
                $results[] = [
                    'userid' => $userid,
                    'success' => false,
                    'message' => 'Error saving grade - see logs'
                ];
                continue;
            }

            // Record success
            $results[] = [
                'userid' => $userid,
                'success' => true,
                'message' => '',
            ];
        }

        return $results;
    }

    /**
     * Parameter definition for alarmbell query
     * @return external_funtion_parameters
     */
    public static function alarmbell_query_parameters() {
        error_log(print_r($_POST, true));
        return new external_function_parameters([
            'guids' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'GUID')
            ),
            'eventnames' => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Event name')
            )
        ]);
    }

    /**
     * Return definition for alarmbell query
     * @returns external_multiple_structure
     */
    public static function alarmbell_query_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Logs record ID'),
                'guid' => new external_value(PARAM_TEXT, 'GUID'),
                'eventname' => new external_value(PARAM_TEXT, 'Event name'),
                'courseid' => new external_value(PARAM_INT, 'Moodle course id'),
                'timecreated' => new external_value(PARAM_INT, 'Timestamp when logged'),
                'ip' => new external_value(PARAM_TEXT, 'Origin IP address'),
            ])
        );
    }

    /**
     * Alarm Bell Query
     * @param array $guids
     * @param array $eventnames
     */
    public static function alarmbell_query($guids, $eventnames) {
        global $CFG, $DB;

        // Check params
        $params = self::validate_parameters(self::alarmbell_query_parameters(), ['guids' => $guids, 'eventnames' => $eventnames]);

        // Get userids from guid
        $userids = [];
        foreach ($guids as $guid) {
            if ($user = $DB->get_record('user', ['username' => $guid, 'mnethostid' => $CFG->mnet_localhost_id])) {
                $userids[] = $user->id;
            }
        }

        // If no users then no data
        if (!userids) {
            return [];
        }

        // Get log data
        list($useridsql, $useridparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        list($eventsql, $eventparams) = $DB->get_in_or_equal($eventnames, SQL_PARAMS_NAMED);
        $sql = "SELECT lsl.id, uu.username AS guid, lsl.eventname, lsl.courseid, lsl.timecreated, lsl.ip from {logstore_standard_log} lsl
            JOIN {user} uu ON uu.id = lsl.userid
            WHERE userid $useridsql
            AND eventname $eventsql";
        $logs = $DB->get_records_sql($sql, $useridparams + $eventparams);

        return $logs;
    }

    /**
     * Parameter definition for portal_courses
     * @return external_funtion_parameters
     */
    public static function portal_courses_parameters() {
        return new external_function_parameters([
            'guid' => new external_value(PARAM_TEXT, 'GUID'),
            'maxresults' => new external_value(PARAM_INT, 'Maximum number of results required')
        ]);
    }

    /**
     * Return definition for portal_courses
     * @returns external_multiple_structure
     */
    public static function portal_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'guid' => new external_value(PARAM_TEXT, 'GUID'),
                'name' => new external_value(PARAM_TEXT, 'Course full name'),
                'shortname' => new external_value(PARAM_TEXT, 'Course short name'),
                'courseid' => new external_value(PARAM_INT, 'Moodle course id'),
                'starred' => new external_value(PARAM_BOOL, 'Course is starred'),
                'visible' => new external_value(PARAM_BOOL, 'Course is visible to students'),
                'lastvisit' => new external_value(PARAM_INT, 'Last visited (timestamp)'),
                'timecreated' => new external_value(PARAM_INT, 'Timestamp when logged'),
            ])
        );
    }

    /**
     * `portal courses
     * @param array $guids
     * @param array $eventnames
     */
    public static function portal_courses($guids, $eventnames) {

    }
}
