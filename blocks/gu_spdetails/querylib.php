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
 * Contains the DB query methods for UofG Assessments Details block.
 *
 * @package    block_gu_spdetails
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao, Jose Maria Abreu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir. '/grade/grade_item.php');
require_once($CFG->libdir. '/grade/grade_grade.php');
require_once($CFG->libdir. '/grade/grade_category.php');
require_once($CFG->libdir. '/grade/grade_scale.php');
require_once($CFG->dirroot. '/mod/assign/locallib.php');
require_once($CFG->dirroot. '/mod/quiz/locallib.php');
require_once($CFG->dirroot. '/mod/workshop/locallib.php');

/**
 * Retrieves gradable activities of all courses of a specific user
 *
 * @todo subwhere, gi.itemnumber = 1 for whole grading forums 
 * @param int $userid
 * @return mixed
 */
function get_all_user_courses_gradable_activities($userid) {
    global $DB;

    $allowedactivities = "'assign', 'quiz', 'forum', 'workshop'";

    $params = array('siteid' => SITEID, 'userid' => $userid, 'contextlevel' => CONTEXT_COURSE, 'fieldname' => 'show_on_studentdashboard',
                    'active' => ENROL_USER_ACTIVE, 'enabled' => ENROL_INSTANCE_ENABLED, 'gradetype' => GRADE_TYPE_NONE);   
    $fields = array('id as courseid', 'category', 'sortorder',
                    'fullname', 'shortname', 'idnumber',
                    'summary', 'summaryformat', 'format',
                    'showgrades', 'newsitems', 'startdate',
                    'enddate', 'relativedatesmode', 'marker',
                    'maxbytes', 'legacyfiles', 'showreports',
                    'visible', 'visibleold', 'groupmode',
                    'groupmodeforce', 'defaultgroupingid', 'lang',
                    'calendartype', 'theme', 'timecreated',
                    'timemodified' , 'requested', 'enablecompletion',
                    'completionnotify', 'cacherev');
    $coursefields = 'c.' .join(',c.', $fields);
    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";

    $gradegetselect = ", cm.*, md.name as modname,
                       gi.id as gradeitemid, gi.categoryid, gi.gradetype,
                       gi.grademax, gi.grademin, gi.scaleid,
                       gi.aggregationcoef, gi.aggregationcoef2";
    $gradegetjoin = "JOIN {course_modules} cm ON (cm.course = c.id)
                     JOIN {modules} md ON (md.id = cm.module)
                     JOIN (SELECT * FROM {grade_items} GROUP BY iteminstance, itemmodule) gi
                     ON (gi.iteminstance = cm.instance
                         AND gi.courseid = c.id
                         AND gi.itemmodule = md.name)";

    $customfieldselect = ", cfd.value";
    $customfieldjoin = "JOIN {customfield_field} cff ON (cff.shortname = :fieldname)
                        JOIN {customfield_data} cfd ON (cfd.fieldid = cff.id AND cfd.instanceid = c.id)";

    $custonfieldwhere = "AND cfd.value > 0";
    $gradegetwhere =    "AND gi.itemtype = 'mod'
                         AND (gi.itemnumber = 0 OR gi.itemmodule = 'forum')
                         AND gi.gradetype != :gradetype";
    $filtermodname =    "AND md.name IN ($allowedactivities)";
    $sql = "SELECT cm.id, $coursefields $ccselect $gradegetselect $customfieldselect
            FROM {course} c $gradegetjoin $customfieldjoin
            JOIN (SELECT DISTINCT e.courseid
            FROM {enrol} e
            JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
            ) en ON (en.courseid = c.id)
            $ccjoin
            WHERE c.id <> :siteid
            $gradegetwhere $filtermodname $custonfieldwhere";
    return $DB->get_records_sql($sql, $params);
}

/**
 * Groups assessment grade item values in one object
 *
 * @param array $params
 * @return stdClass
 */
function set_assessmentgradeitem($params) {
    $keys = array('id', 'categoryid', 'gradetype',
                  'grademax', 'grademin', 'scaleid',
                  'aggregationcoef', 'aggregationcoef2');
    $values = $params;
    $gradeitem = array_combine($keys, $values);
    return (object) $gradeitem;
}

/**
 * Retrieves the 'section' value from 'course sections' table
 *
 * @param int $courseid
 * @param int $sectionid
 * @return int
 */
function retrieve_section($courseid, $sectionid) {
    global $DB;

    $cs = $DB->get_record('course_sections', array('id' => $sectionid), 'section');
    return (!empty($cs)) ? $cs->section : '0';
}

/**
 * Retrieves 'grade_category' values of a specific activity instance
 *
 * @param int $categoryid
 * @return grade_category
 */
function retrieve_gradecategory($categoryid) {
    $category = grade_category::fetch(array('id' => $categoryid));
    return $category;
}

/**
 * Retrieves activity object based on modname ('assign', 'forum', 'quiz', 'workshop')
 *
 * @param stdClass $user
 * @param stdClass $cm
 * @param stdClass $course
 * @return stdClass
 */
function retrieve_activity($user, $cm, $course, $gradeitem) {
    global $DB;

    $activity = new stdClass;
    $context = context_module::instance($cm->id);
    $grades = retrieve_grades($user->id, $gradeitem->id);
    $activity->cmid = $cm->id;
    $activity->gradeinformation = ($grades) ? $grades->information : null;

    switch($cm->modname) {
        case 'assign':
            $assign = new assign($context, $cm, $course);
            $submission = $assign->get_assign_submission_status_renderable($user, false);
            $overrides = $assign->override_exists($user->id);

            $activity->name = $assign->get_instance()->name;
            $activity->startdate = $assign->get_instance()->allowsubmissionsfromdate;
            $activity->duedate = $assign->get_instance()->duedate;
            $activity->cutoffdate = $assign->get_instance()->cutoffdate;
            $activity->extensionduedate = ($submission && $submission->extensionduedate > 0) ?
                                          $submission->extensionduedate : null;
            $activity->gradingduedate = $assign->get_instance()->gradingduedate;
            $activity->submissionstatus = ($submission && isset($submission->submission->status)) ?
                                          $submission->submission->status : null;
            $activity->submissionsenabled = ($submission) ? $submission->submissionsenabled : false;
            $activity->submissionslocked = ($submission) ? $submission->locked : false;
            $activity->graded = ($submission) ? $submission->graded : false;
            $activity->feedback = ($grades) ? $grades->feedback : null;
            $activity->hasfeedback = false;

            if(isset($overrides->id)) {
                $activity->startdate = $overrides->allowsubmissionsfromdate;
                $activity->duedate = $overrides->duedate;
                $activity->cutoffdate = $overrides->cutoffdate;
                $activity->hasduedateoverride = true;
            }

            if($activity->graded) {
                $feedback = $assign->get_assign_feedback_status_renderable($user);

                $activity->formattedgrade = ($feedback) ? $feedback->gradefordisplay : null;
                $activity->grade = ($feedback) ? $feedback->grade->grade : null;
                $activity->hasfeedbackfiles = false;
                $activity->hasturnitin = false;

                if($grades->feedbackformat > 0 || !empty($grades->feedback)) {
                    $activity->hasfeedback = true;
                }else{
                    // check for feedback files only if feedback is empty
                    $feedbackfile = retrieve_assignfeedbackfile($cm->instance, $feedback->grade->id);
                    $activity->hasfeedbackfiles = ($feedbackfile) ? true : false;
                    $activity->hasfeedback = ($activity->hasfeedbackfiles) ? true : false;
                }

                // check if turnitin is enabled only if feedback and feedback files are empty
                if($grades->feedbackformat == 0 && empty($grades->feedback) && !$activity->hasfeedbackfiles) {
                    $turnitin = retrieve_turnitincfg($cm->id);
                    $activity->hasturnitin = ($turnitin) ? true : false;
                    $activity->hasfeedback = ($activity->hasturnitin) ? true : false;
                }
            }
            break;
        case 'quiz':
            $quizobj = quiz::create($cm->instance, $user->id);
            $accessmanager = new quiz_access_manager($quizobj, time(),
                                                     has_capability('mod/quiz:ignoretimelimits',
                                                                    $context, null, false));
            $quiz = $quizobj->get_quiz();
            $attempts = quiz_get_user_attempts($quiz->id, $user->id, 'finished', true);
            $numattempts = count($attempts);
            $lastfinishedattempt = end($attempts);
            $lockedmsg = $accessmanager->prevent_new_attempt($numattempts, $lastfinishedattempt);

            $activity->name = $quiz->name;
            $activity->startdate = $quiz->timeopen;
            $activity->duedate = $quiz->timeclose;
            $activity->gradingduedate = $quiz->timeclose;
            $activity->submissionsenabled = $quizobj->has_questions();
            $activity->submissionslocked = ($lockedmsg) ? true : false;
            $activity->grade = quiz_get_best_grade($quiz, $user->id);
            $activity->gradeinformation = (isset($activity->grade)) ? $grades->information : null;
            $activity->graded = isset($activity->grade) ? $activity->grade : false;
            $activity->hasfeedback = false;
            $activity->feedback = ($grades) ? $grades->feedback : null;

            if($activity->graded) {
                $feedback = quiz_feedback_for_grade((float) $activity->grade, $quiz, $context);
                $activity->feedback = $feedback;
                $activity->hasfeedback = ($feedback) ? true: false;
            }
            break;
        case 'forum':
            $forum = $DB->get_record('forum', array('id' => $cm->instance, 'course' => $course->id));

            $activity->name = $forum->name;
            $activity->startdate = '0';
            $activity->duedate = $forum->duedate;
            $activity->cutoffdate = $forum->cutoffdate;
            $activity->gradingduedate = ($activity->cutoffdate >= 0) ?
                                         $activity->cutoffdate : $activity->duedate;  // review logic
            $activity->gradeforum = ($forum->grade_forum) ? 1 : 0;
            $activity->grade = ($grades && isset($grades->finalgrade)) ? $grades->finalgrade : null;
            $activity->graded = isset($activity->grade) ? $activity->grade : false;
            $activity->hasfeedback = false;
            $activity->feedback = ($grades) ? $grades->feedback : null;

            if($activity->graded) {
                $activity->graded = true;
                $activity->hasfeedback = true;
                $activity->feedback = null;
                if(isset($gradeitem->scaleid)) {
                    $scales = retrieve_scale($gradeitem->scaleid);
                    $scale = make_menu_from_list($scales->scale);
                    $intgrade = round($activity->grade);
                    $scalegrade = (round($intgrade) >= 0) ? $scale[$intgrade] : null;
                    $activity->formattedgrade = $scalegrade;
                }
            }
            break;
        case 'workshop':
            $workshoprecord = $DB->get_record('workshop', array('id' => $cm->instance, 'course' => $course->id));
            $workshop = new workshop($workshoprecord, $cm, $course);
            $submissionscount = $workshop->count_submissions($user->id);

            $activity->name = $workshop->name;
            $activity->startdate = $workshop->submissionstart;
            $activity->duedate = $workshop->submissionend;
            $activity->gradingduedate = $workshop->assessmentend;
            $activity->submissionstatus = null;
            $activity->grade = ($grades && isset($grades->finalgrade)) ? $grades->finalgrade : null;
            $activity->graded = false;
            $activity->hasfeedback = false;
            $activity->feedback = ($grades) ? $grades->feedback : null;

            if(isset($activity->grade)) {
                $activity->graded = true;

                if($submissionscount > 0) {
                    $activity->submissionstatus = get_string('submitted', 'block_gu_spdetails');
                    $submissions = $workshop->get_submissions($user->id);
                    $submissionid = (isset($submissions)) ? reset($submissions)->id : null;
                    $activity->submissionid = $submissionid;
                    $assessments = $workshop->get_assessments_of_submission($submissionid);
                    $activity->hasfeedback = (isset($assessments)) ? true : false;
                    $activity->feedback = null;
                }
            }

            break;
        default:
            break;
    }

    return $activity;
}

/**
 * Retrieves grade of an assessment from DB
 *
 * @param int $userid
 * @param int $gradeitemid
 * @return grade_grade
 */
function retrieve_grades($userid, $gradeitemid) {
    $grades = grade_grade::fetch(array('userid' => $userid, 'itemid' => $gradeitemid));
    return $grades;
}

/**
 * Retrieves scale of an assessment from DB
 *
 * @param int $scaleid
 * @return grade_scale
 */
function retrieve_scale($scaleid) {
    $scale = grade_scale::fetch(array('id' => $scaleid));
    return $scale;
}

/**
 * Retrieves Turnitin config of an assessment from DB
 *
 * @param int $cmid
 * @return stdClass
 */
function retrieve_turnitincfg($cmid) {
    global $DB;

    $cfg = 'use_turnitin';
    $conditions = array('cm' => $cmid, 'name' => $cfg, 'value' => 1);
    $turnitincfg = $DB->get_record('plagiarism_turnitin_config', $conditions);
    return $turnitincfg;
}

/**
 * Retrieves Feedback files of an assignment from DB
 *
 * @param int $assignmentid
 * @param int $gradeid
 * @return stdClass
 */
function retrieve_assignfeedbackfile($assignmentid, $gradeid) {
    global $DB;

    $conditions = array('assignment' => $assignmentid, 'grade' => $gradeid);
    $assignfeedbackfile = $DB->get_record('assignfeedback_file', $conditions);
    return $assignfeedbackfile;
}

/**
 * Checks if 'Show assessments on Student Dashboard' is checked
 * under GCAT Options in Course Settings
 *
 * @param int $courseid
 * @return boolean
 */
function return_showcourse($courseid) {
    global $DB;
    $fieldname = 'show_on_studentdashboard';

    $sql = "SELECT cfd.value
            FROM {customfield_field} cff
            JOIN {customfield_data} cfd ON cfd.fieldid = cff.id
            AND cfd.instanceid = ?
            WHERE cff.shortname = ?";
    $config = $DB->get_record_sql($sql, array($courseid, $fieldname));
    $showcourse = ($config) ? (($config->value > 0) ? true : false) : false;
    return $showcourse;
}
