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
 * Custom class for setting up our course types, gradebook and activities.
 * We should try and represent all the activities that Moodle provides,
 * however, the main focus, for now at least, should be on the activities
 * that are used regularly, namely assignment, quiz, possibly workshop and forum.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

use externallib_advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Class containing setUp, activities and other utility methods.
 */
class newgu_spdetails_advanced_testcase extends externallib_advanced_testcase {

    /**
     * @var object $course
     */
    protected $course;

    /**
     * @var object $teacher
     */
    protected $teacher;

    /**
     * @var object $student1
     */
    protected $student1;

    /**
     * @var object $lib
     */
    protected $lib;

    /**
     * @var object $courseapi
     */
    protected $courseapi;

    /**
     * @var object $activityapi
     */
    protected $activityapi;

    /**
     * @var object $assignment1
     */
    protected $assignment1;

    /**
     * @var object $assignment2
     */
    protected $assignment2;

    /**
     * @var object $assignment3
     */
    protected $assignment3;

    /**
     * @var obejct $mygradescourse
     */
    protected $mygradescourse;

    /**
     * @var object $mygrades_summativecategory
     */
    protected $mygrades_summativecategory;

    /**
     * @var object $mygrades_summative_subcategory
     */
    protected $mygrades_summative_subcategory;

    /**
     * var @object $mygrades_summative_subcategory2
     */
    protected $mygrades_summative_subcategory2;

    /**
     * @var object $mygrades_formativecategory
     */
    protected $mygrades_formativecategory;

    /**
     * @var object $assignment4
     */
    protected $assignment4;

    /**
     * @var object $assignment5
     */
    protected $assignment5;

    /**
     * @var object $assignment6
     */
    protected $assignment6;

    /**
     * @var object $gradebookcourse
     */
    protected $gradebookcourse;

    /**
     * @var object $gradebookcategory
     */
    protected $gradebookcategory;

    /**
     * @var object $assignment7
     */
    protected $assignment7;

    /**
     * @var object $course_past
     */
    protected $course_past;

    /**
     * @var object $summativecategory_past
     */
    protected $summativecategory_past;

    /**
     * @var object $assignment_past
     */
    protected $assignment_past;

    /**
     * @var object $attendance_activity
     */
    protected $attendance_activity;

    /**
     * @var object $checklist_activity
     */
    protected $checklist_activity;

    /**
     * @var object $data_activity
     */
    protected $data_activity;

    /**
     * @var object $default_activity
     */
    protected $default_activity;

    /**
     * @var object $forum_activity
     */
    protected $forum_activity;

    /**
     * @var object $game_activity
     */
    protected $game_activity;

    /**
     * @var object $glossary_activity
     */
    protected $glossary_activity;

    /**
     * @var object $h5p_activity
     */
    protected $h5p_activity;

    /**
     * @var object $hsuforum_activity
     */
    protected $hsuforum_activity;

    /**
     * @var object $hvp_activity
     */
    protected $hvp_activity;

    /**
     * @var object $kalvidassign_activity
     */
    protected $kalvidassign_activity;

    /**
     * @var object $lesson_activity
     */
    protected $lesson_activity;

    /**
     * @var object $lti_activity
     */
    protected $lti_activity;

    /**
     * @var object $oublog_activity
     */
    protected $oublog_activity;

    /**
     * @var object $peerwork_activity
     */
    protected $peerwork_activity;

    /**
     * @var object $questionnaire_activity
     */
    protected $questionnaire_activity;

    /**
     * @var object $quiz_activity
     */
    protected $quiz_activity;

    /**
     * @var object $scheduler_activity
     */
    protected $scheduler_activity;

    /**
     * @var object $scorm_activity
     */
    protected $scorm_activity;

    /**
     * @var object $workshop_activity
     */
    protected $workshop_activity;

    /**
     * Get gradeitemid
     * @param string $itemtype
     * @param string $itemmodule
     * @param int $iteminstance
     * @return int
     */
    protected function get_grade_item(string $itemtype, string $itemmodule, int $iteminstance) {
        global $DB;

        $params = [
            'iteminstance' => $iteminstance,
        ];
        if ($itemtype) {
            $params['itemtype'] = $itemtype;
        }
        if ($itemmodule) {
            $params['itemmodule'] = $itemmodule;
        }
        $gradeitem = $DB->get_record('grade_items', $params, '*', MUST_EXIST);

        return $gradeitem->id;
    }

    /**
     * Add assignment grade
     * @param int $assignid
     * @param int $studentid
     * @param int $graderid
     * @param float $gradeval
     * @param string $status
     */
    protected function add_assignment_grade(int $assignid, int $studentid, int $graderid, float $gradeval,
    string $status = ASSIGN_SUBMISSION_STATUS_NEW) {
        global $DB;

        $submission = new \stdClass();
        $submission->assignment = $assignid;
        $submission->userid = $studentid;
        $submission->status = $status;
        $submission->latest = 0;
        $submission->attemptnumber = 0;
        $submission->groupid = 0;
        $submission->timecreated = time();
        $submission->timemodified = time();
        $DB->insert_record('assign_submission', $submission);

        $grade = new \stdClass();
        $grade->assignment = $assignid;
        $grade->userid = $studentid;
        $grade->timecreated = time();
        $grade->timemodified = time();
        $grade->grader = $graderid;
        $grade->grade = $gradeval;
        $grade->attemptnumber = 0;
        $DB->insert_record('assign_grades', $grade);
    }

    protected function add_forum_grade() {

    }

    protected function add_lesson_grade() {

    }

    protected function add_mygrades_grade() {

    }

    /**
     * Add a peerwork grade
     * @param int $peerworkid
     * @param int $studentid
     * @param int $graderid
     * @param float $gradeval
     */
    protected function add_peerwork_grade(int $peerworkid, int $studentid, int $graderid, float $gradeval, int $score) {

        global $DB;

        $grade = new \stdClass();
        $grade->peerworkid = $peerworkid;
        $grade->userid = $studentid;
        $grade->grade = $gradeval;
        $grade->gradedby = $graderid;
        $grade->timecreated = time();
        $grade->timemodified = time();
        $DB->insert_record('peerwork_submission', $grade);

        $grade = new \stdClass();
        $grade->peerworkid = $peerworkid;
        $grade->userid = $studentid;
        $grade->grade = $gradeval;
        $grade->score = $score;
        $DB->insert_record('peerwork_grades', $grade);
    }

    protected function add_quiz_grade() {

    }

    protected function add_workshop_grade() {

    }

    /**
     * Set up our test conditions...
     *
     * Our tests will need to cover the 3 course types - namely:
     * 1) MyGrades
     * 2) GCAT
     * 3) GradeBook
     *
     * @return void
     * @throws dml_exception
     */
    protected function setUp(): void {
        global $DB;

        $this->resetAfterTest(true);

        $lib = new \block_newgu_spdetails\api();
        $this->lib = $lib;

        $courseapi = new \block_newgu_spdetails\course();
        $this->courseapi = $courseapi;

        $activityapi = new \block_newgu_spdetails\activity();
        $this->activityapi = $activityapi;

        // Lets add some scales that each course can use.
        // Schedule A.
        $scaleitems = 'H:0, G2:1, G1:2, F3:3, F2:4, F1:5, E3:6, E2:7, E1:8, D3:9, D2:10, D1:11,
            C3:12, C2:13, C1:14, B3:15, B2:16, B1:17, A5:18, A4:19, A3:20, A2:21, A1:22';

        // Schedule B scale.
        $scaleitemsb = 'H, G0, F0, E0, D0, C0, B0, A0';

        // Some dates for our mock courses.
        $lastmonth = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"));
        $nextyear  = mktime(0, 0, 0, date("m"), date("d"), date("Y") + 1);

         // Create a MyGrades type course.
         // We are loosely creating gradable items in local_gugrade_grades
         // on the basis that the dashboard will be pulling data from there.
         // To refine the setup for this, we would need to mock grade items
         // that don't have an entry and are therefore returned from gradebook
         // instead. This would be to simulate gradable items that have yet
         // to be imported/marked/released.
        $mygradescourse = $this->getDataGenerator()->create_course([
            'fullname' => 'MyGrades Test Course',
            'shortname' => 'MYGRADE-TW1',
            'startdate' => $lastmonth,
            'enddate' => $nextyear,
        ]);

        // We also need to mock "enable" this as a MyGrades type course.
        $mygradesparams = [
            'courseid' => $mygradescourse->id,
            'name' => 'enabledashboard',
            'value' => 1,
        ];
        $DB->insert_record('local_gugrades_config', $mygradesparams);

        // Add some grading categories..
        $mygradessummativecategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Summative Assessments',
            'courseid' => $mygradescourse->id,
            'aggregation' => 10,
        ]);
        $mygradessummativesubcategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Assessments Month 1 - Summative - WM',
            'courseid' => $mygradescourse->id,
            'parent' => $mygradessummativecategory->id,
        ]);
        $mygradessummativesubcategory2 = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Sub-Category B Assignments (Resits - highest grade)',
            'courseid' => $mygradescourse->id,
            'parent' => $mygradessummativesubcategory->id,
        ]);
        $mygradesformativecategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Formative Assessments',
            'courseid' => $mygradescourse->id,
            'parent' => $mygradessummativecategory->parent,
        ]);

        // Add the grading scales...
        $mygradesscale1 = $this->getDataGenerator()->create_scale([
            'name' => 'UofG 22 point scale',
            'scale' => $scaleitems,
            'courseid' => $mygradescourse->id,
        ]);
        $mygradesscaleb1 = $this->getDataGenerator()->create_scale([
            'name' => 'UofG Schedule B',
            'scale' => $scaleitemsb,
            'courseid' => $mygradescourse->id,
        ]);

        // Create some context.
        $mygradescontext = \context_course::instance($mygradescourse->id);

        // Set up, enrol and assign role for a teacher...
        $teacher = $this->getDataGenerator()->create_user(['email' => 'teacher1@example.co.uk', 'username' => 'teacher1']);
        $this->getDataGenerator()->enrol_user($teacher->id, $mygradescourse->id, $this->get_roleid('editingteacher'));
        $this->getDataGenerator()->role_assign('editingteacher', $teacher->id, $mygradescontext);

        // Set up, enrol and assign role for a student...
        $student1 = $this->getDataGenerator()->create_user(['email' => 'student1@example.co.uk', 'username' => 'student1']);
        $this->getDataGenerator()->enrol_user($student1->id, $mygradescourse->id, $this->get_roleid());
        $this->getDataGenerator()->role_assign('student', $student1->id, $mygradescontext);

        // Enrol the teacher...
        $this->getDataGenerator()->enrol_user($teacher->id, $mygradescourse->id, $this->get_roleid('editingteacher'));
        $this->getDataGenerator()->role_assign('editingteacher', $teacher->id, $mygradescontext);

        // Enrol the student.
        $this->getDataGenerator()->enrol_user($student1->id, $mygradescourse->id, $this->get_roleid());
        $this->getDataGenerator()->role_assign('student', $student1->id, $mygradescontext);

        // Create some "gradable" activities.
        $duedate4 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));
        $duedate5 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7, date("Y"));
        $duedate6 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 14, date("Y"));
        $assignment4 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'Assessment A - Month 1',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $mygradescourse->id,
            'duedate' => $duedate4,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $mygradesscale1->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $mygradessummativesubcategory->id,
            $assignment4->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        $gradeditem4 = $this->add_assignment_grade($assignment4->id, $student1->id, $teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_NEW);

        $DB->insert_record('grade_grades', [
            'itemid' => $assignment4->id,
            'userid' => $student1->id,
            'rawgrade' => 21,
        ]);

        // We're not doing anything else with assignment4 as we only
        // want to test if gradetype=[PROVISIONAL|RELEASED] on these
        // next two items.

        $assignment5 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'Assessment B1 - Month 1',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $mygradescourse->id,
            'duedate' => $duedate5,
            'gradetype' => 2,
            'grademax' => 75,
            'scaleid' => $mygradesscale1->id,
        ]);
        $gradeitemid5 = $this->get_grade_item('', 'assign', $assignment5->id);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $mygradessummativesubcategory2->id,
            $assignment5->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        $gradeditem5 = $this->add_assignment_grade($assignment5->id, $student1->id, $teacher->id, 70,
        ASSIGN_SUBMISSION_STATUS_NEW);

        $DB->insert_record('grade_grades', [
            'itemid' => $gradeitemid5,
            'userid' => $student1->id,
            'rawgrade' => 13,
        ]);

        // This could be completely wrong of course.
        // Create a "provisional" grade for the first assignment.
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $mygradescourse->id,
            'gradeitemid' => $gradeitemid5,
            'userid' => $student1->id,
            'rawgrade' => 13,
            'gradetype' => 'PROVISIONAL',
            'columnid' => 0,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);

        // No idea why, but this next call creates a shit load of grade_grades entries.
        $assignment6 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'Assessment B1 - Month 1 (Resit)',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $mygradescourse->id,
            'duedate' => $duedate6,
            'gradetype' => 2,
            'grademax' => 100,
            'scaleid' => $mygradesscale1->id,
        ]);
        $gradeitemid6 = $this->get_grade_item('', 'assign', $assignment6->id);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $mygradessummativesubcategory2->id,
            $assignment6->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        $gradeditem6 = $this->add_assignment_grade($assignment6->id, $student1->id, $teacher->id, 75,
        ASSIGN_SUBMISSION_STATUS_NEW);

        // From the earlier create_module() call creating multiple grade_grades, we can now only update the grade_grades record,
        // as this barfs complaining of duplicate records. We're updating the itemid = $gradeitemid5 here as it's no longer
        // $gradeitemid6 that we want
        $params = [
            'finalgrade' => NULL,
            'itemid' => $gradeitemid5,
            'userid' => $student1->id,
        ];
        $DB->execute("UPDATE {grade_grades} SET finalgrade = ? WHERE itemid = ? AND userid = ?", $params);
        // $DB->insert_record('grade_grades', [
        //     'itemid' => $gradeitemid6,
        //     'userid' => $student1->id,
        //     'rawgrade' => 21,
        //     'finalgrade' => 22,
        // ]);

        // This assignment has been given a final grade...
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $mygradescourse->id,
            'gradeitemid' => $gradeitemid6,
            'userid' => $student1->id,
            'displaygrade' => 'A0',
            'gradetype' => 'RELEASED',
            'columnid' => 0,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);

        // Howard's API adds some additional data.
        $mygradescourse->firstlevel[] = [
            'id' => $mygradessummativecategory->id,
            'fullname' => $mygradessummativecategory->fullname,
        ];
        $mygradescourse->mygradesenabled = true;

        // Regular Gradebook type course.
        $gradebookcourse = $this->getDataGenerator()->create_course([
            'fullname' => 'Gradebook Test Course - TW1',
            'shortname' => 'GRADEBOOK-TW1',
            'startdate' => $lastmonth,
            'enddate' => $nextyear,
        ]);
        $gradebookcontext = \context_course::instance($gradebookcourse->id);

        // Add a grading category..
        $gradebookcategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'SPS5022 Oral Presentation 2022-2023',
            'courseid' => $gradebookcourse->id,
        ]);

        $gradebookcourse->firstlevel[] = [
            'id' => $gradebookcategory->id,
            'fullname' => $gradebookcategory->fullname,
        ];
        $gradebookcourse->mygradesenabled = false;

        // Add the grading scales...
        $gradebookscale1 = $this->getDataGenerator()->create_scale([
            'name' => 'UofG 22 point scale',
            'scale' => $scaleitems,
            'courseid' => $gradebookcourse->id,
        ]);
        $gradebookscaleb1 = $this->getDataGenerator()->create_scale([
            'name' => 'UofG Schedule B',
            'scale' => $scaleitemsb,
            'courseid' => $gradebookcourse->id,
        ]);

        // Enrol the teacher.
        $this->getDataGenerator()->enrol_user($teacher->id, $gradebookcourse->id, $this->get_roleid('editingteacher'));
        $this->getDataGenerator()->role_assign('editingteacher', $teacher->id, $gradebookcontext);

        // Enrol the student.
        $this->getDataGenerator()->enrol_user($student1->id, $gradebookcourse->id, $this->get_roleid());
        $this->getDataGenerator()->role_assign('student', $student1->id, $gradebookcontext);

        $duedate7 = mktime(date("H"), date("i"), date("s"), date("m") + 1, date("d") + 7, date("Y"));
        $duedate8 = mktime(date("H"), date("i"), date("s"), date("m") + 1, date("d") + 8, date("Y"));
        $assignment7 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'SPS5022 Essay - FINAL - Thursday 12th',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $gradebookcourse->id,
            'duedate' => $duedate7,
            'grademax' => 100.00000,
            'gradetype' => 2,
            'scaleid' => $gradebookscale1->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $gradebookcategory->id,
            2,
            $gradebookscale1->id,
            $assignment7->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ?, gradetype = ?, scaleid = ?  WHERE iteminstance = ?", $params);

        $gradeditem7 = $this->add_assignment_grade($assignment7->id, $student1->id, $teacher->id, 75,
        ASSIGN_SUBMISSION_STATUS_NEW);
        $gradeitemid7 = $this->get_grade_item('', 'assign', $assignment7->id);
        // This assignment has been given a final grade...
        $DB->insert_record('grade_grades', [
            'itemid' => $gradeitemid7,
            'userid' => $student1->id,
            'finalgrade' => 21,
            'rawscaleid' => $gradebookscale1->id,
            'information' => 'This is a Gradebook assessed final grade',
            'feedback' => 'You have attained the required level according to the Gradebook formula.',
        ]);

        // This is our Provisional grade assignment.
        $assignment8 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'Assessment 8',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $gradebookcourse->id,
            'duedate' => $duedate8,
            'gradetype' => 2,
            'grademax' => 20.0,
            'scaleid' => $gradebookscale1->id,
        ]);
        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $gradebookcategory->id,
            2,
            $gradebookscale1->id,
            $assignment8->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ?, gradetype = ?, scaleid = ? WHERE iteminstance = ?", $params);

        $gradeditem8 = $this->add_assignment_grade($assignment8->id, $student1->id, $teacher->id, 14,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        // No idea why, but the last call to create_module has just created a number of
        // grade_grade entries, when it didn't previously, meaning this now flakes out
        // with a DUPLICATE KEY error.
        $gradeitemid8 = $this->get_grade_item('', 'assign', $assignment8->id);
        // Set a Provisional Grade as this item is 1 of 2 in a subcategory.
        $params = [
            18,
            $gradebookscale1->id,
            $gradeitemid8,
        ];
        $DB->execute("UPDATE {grade_grades} SET rawgrade = ?, rawscaleid = ? WHERE itemid = ?", $params);

        // Create a "past" course for the test student(s).
        $lastmonth = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"));
        $tmpcourse = $this->getDataGenerator()->create_course([
            'fullname' => 'Student Dashboard Test Gradebook Course - Past',
            'shortname' => 'SDTGBCP1',
            'startdate' => $lastmonth,
        ]);
        $coursepast = $DB->get_record('course', ['id' => $tmpcourse->id], '*', MUST_EXIST);
        $pastdate = strtotime("last Monday");
        $coursepast->enddate = $pastdate;
        $DB->update_record('course', $coursepast);

        $this->getDataGenerator()->enrol_user($student1->id, $coursepast->id, $this->get_roleid());

        $gradecategorypast = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Summative Category - Past',
            'courseid' => $coursepast->id,
        ]);
        $summativecategorypast = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Average of assignments - past',
            'courseid' => $coursepast->id,
            'parent' => $gradecategorypast->id,
        ]);

        // Howard's API adds some additional data.
        $coursepast->firstlevel[] = [
            'id' => $summativecategorypast->id,
            'fullname' => $summativecategorypast->fullname,
        ];
        $coursepast->mygradesenabled = false;

        $assignmentpast = $this->getDataGenerator()->create_module('assign', [
            'name' => 'Past Assessment 1',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $coursepast->id,
            'gradetype' => 2,
            'grademax' => 100.00,
            'scaleid' => $gradebookscale1->id,
        ]);
        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $summativecategorypast->id,
            $assignmentpast->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Add a past assignment grade.
        $assignmentgrade1past = $this->add_assignment_grade($assignmentpast->id, $student1->id, $teacher->id, 95.5,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        // This assignment has been given a final grade...
        $DB->insert_record('grade_grades', [
            'itemid' => $assignmentpast->id,
            'userid' => $student1->id,
            'finalgrade' => 22,
            'information' => 'This is a Gradebook assessed final grade',
            'feedback' => 'You have attained the required level according to the Gradebook formula.',
        ]);

        $this->student1 = $student1;
        $this->teacher = $teacher;

        $this->mygradescourse = $mygradescourse;
        $this->mygrades_summativecategory = $mygradessummativecategory;
        $this->mygrades_summative_subcategory = $mygradessummativesubcategory;
        $this->mygrades_summative_subcategory2 = $mygradessummativesubcategory2;
        $this->mygrades_formativecategory = $mygradesformativecategory;
        $this->assignment4 = $assignment4;
        $this->assignment5 = $assignment5;
        $this->assignment6 = $assignment6;

        $this->gradebookcourse = $gradebookcourse;
        $this->gradebookcategory = $gradebookcategory;
        $this->assignment7 = $assignment7;

        $this->course_past = $coursepast;
        $this->summativecategory_past = $summativecategorypast;
        $this->assignment_past = $assignmentpast;
    }

    /**
     * Utility function to provide the roleId.
     *
     * @param string $archetype
     * @return int
     * @throws dml_exception
     */
    public function get_roleid(string $archetype = 'student'): int {
        global $DB;

        $role = $DB->get_record("role", ['archetype' => $archetype]);
        return $role->id;
    }
}
