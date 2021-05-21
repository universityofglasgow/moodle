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
 * Lang strings for the UofG Assessments Details block.
 *
 * @package    block_gu_spdetails
 * @copyright  2020 Accenture
 * @author     Jose Maria C. Abreu <jose.maria.abreu@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG, $DB;

require_once('config.php');
require_once($CFG->dirroot .'/blocks/moodleblock.class.php');
require_once($CFG->dirroot .'/blocks/gu_spdetails/block_gu_spdetails.php');
require_once($CFG->libdir . '/gradelib.php');

class block_gu_spdetails_testcase extends advanced_testcase {

    public function setUp() {
        global $DB;
        $this->resetAfterTest(true);
        $this->spdetails = new block_gu_spdetails();
        $this->lib = new assessments_details();

        // Setting up student.
        $student = $this->getDataGenerator()->create_user(array('email' => 'user1@example.com', 'username' => 'user1'));
        $this->setUser($student);
        $this->student = $student;

        // Creating course.
        $category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(array('name' => 'Some course', 'category' => $category->id));
        $course = $this->course;
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $this->get_roleid());

        $this->assign = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id));
        $this->quiz = $this->getDataGenerator()->create_module('quiz', array('course' => $this->course->id));
        $this->survey = $this->getDataGenerator()->create_module('survey', array('course' => $this->course->id));
        $this->wiki = $this->getDataGenerator()->create_module('wiki', array('course' => $this->course->id));
        $this->workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $this->course->id));
        $this->forum = $this->getDataGenerator()->create_module('forum', array('course' => $this->course->id,
                                                                               'grade_forum' => 100));
        $this->forum2 = $this->getDataGenerator()->create_module('forum', array('course' => $this->course->id,
                                                                                'grade_forum' => 100));

        $this->gradeitem = $this->getDataGenerator()->create_grade_item(array(
            'itemtype' => 'mod',
            'itemmodule' => 'quiz',
            'courseid' => $this->course->id,
            'iteminstance' => $this->quiz->id
        ));

        $this->getDataGenerator()->create_grade_item(
            array(
                'itemtype' => 'mod',
                'itemmodule' => 'forum',
                'courseid' => $this->course->id,
                'iteminstance' => $this->forum->id,
                'itemnumber' => 0
            )
        );

        $this->getDataGenerator()->create_grade_item(
            array(
                'itemtype' => 'mod',
                'itemmodule' => 'forum',
                'courseid' => $this->course->id,
                'iteminstance' => $this->forum2->id,
                'itemnumber' => 1
            )
        );

        $DB->insert_record('grade_grades', array(
            'itemid' => $this->gradeitem->id,
            'userid' => $student->id
        ));
    }

    public function add_submissions() {
        global $DB, $USER, $CFG;

        $userid = $USER->id;

        // Assign.
        $assignnotsubmitted = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id));
        $DB->insert_record('assign_submission', array(
            'assignment' => $this->assign->id,
            'userid' => $userid,
            'attemptnumber' => 0,
            'status' => 'submitted'
        ));

        $assignsql = "SELECT ma.name,
                        ma.allowsubmissionsfromdate as startdate,
                        ma.duedate, ma.gradingduedate, mgi.id as gradeid,
                        mgi.aggregationcoef as weight, mgi.aggregationcoef2 as weight01
                        FROM {assign} ma
                        JOIN {grade_items} mgi ON mgi.iteminstance = ma.id
                        AND mgi.itemmodule = ?
                        WHERE ma.id = ?";
        $assessmentrecord = $DB->get_record_sql($assignsql, array('assign', $this->assign->id));
        $this->assign->gradeid = $assessmentrecord->gradeid;

        // Quiz.
        $quiznotsubmitted = $this->getDataGenerator()->create_module('quiz', array('course' => $this->course->id));
        $DB->insert_record('quiz_grades', array(
            'quiz' => $this->quiz->id,
            'userid' => $userid,
            'grades' => 10,
            'timemodified' => time()
        ));

        // Survey.
        $surveynotsubmitted = $this->getDataGenerator()->create_module('survey', array('course' => $this->course->id));
        $DB->insert_record('survey_answers', array(
            'survey' => $this->survey->id,
            'userid' => $userid,
            'answer1' => '',
            'answer2' => ''
        ));

        // Workshop.
        $workshopnotsubmitted = $this->getDataGenerator()->create_module('workshop', array('course' => $this->course->id));
        $DB->insert_record('workshop_submissions', array(
            'workshopid' => $this->workshop->id,
            'authorid' => $userid,
            'timecreated' => time(),
            'timemodified' => time()
        ));

        $this->notsubmittedassign = $assignnotsubmitted;
        $this->notsubmittedquiz = $quiznotsubmitted;
        $this->notsubmittedsurvey = $surveynotsubmitted;
        $this->notsubmittedworkshop = $workshopnotsubmitted;
    }

    public function get_roleid($archetype = 'student') {
        global $DB;

        $role = $DB->get_record("role", array('archetype' => $archetype));
        return $role->id;
    }

    public function show_ondashboard($courseid) {
        global $DB;

        $DB->insert_record("customfield_field", array(
            'shortname' => 'show_on_studentdashboard',
            'name' => 'show_on_studentdashboard',
            'type' => 'checkbox',
            'description' => '',
            'descriptionformat' => 1,
            'sortorder' => 0,
            'categoryid' => null,
            'configdata' => '',
            'timecreated' => time(),
            'timemodified' => time()
        ));

        $cff = $DB->get_record("customfield_field", array('shortname' => 'show_on_studentdashboard'));

        $DB->insert_record("customfield_data", array(
            'fieldid' => $cff->id,
            'instanceid' => $courseid,
            'intvalue' => 1,
            'decvalue' => null, 'shortcharvalue' => null, 'charvalue' => null,
            'value' => '1',
            'valueformat' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
            'contextid' => null
        ));
        $cfd = $DB->get_records("customfield_data");
    }

    /**
     * block_gu_spdetails.php
     */
    public function test_applicable_formats() {
        $returned = $this->spdetails->applicable_formats();
        $this->assertEquals($returned, array('my' => true));
    }

    public function test_get_content() {
        global $DB;

        $returned1 = $this->spdetails->get_content();
        $this->assertEquals(null, $returned1->text);

        $this->show_ondashboard($this->course->id);
        $this->spdetails->content = null;
        $this->spdetails->page = new moodle_page();

        $returned2 = $this->spdetails->get_content();
        $this->assertTrue($returned2->text != null);
    }

    public function test_return_enrolledcourses() {
        global $USER;

        $this->show_ondashboard($this->course->id);
        $this->assertEquals(array($this->course->id), $this->spdetails->return_enrolledcourses($USER->id));
    }

    public function test_return_isstudent() {
        $courseid = $this->course->id;

        $this->assertTrue($this->spdetails->return_isstudent($courseid));

        $user = $this->getDataGenerator()->create_user();
        $teacherroleid = $this->get_roleid('teacher');
        $this->getDataGenerator()->enrol_user($user->id, $courseid, $teacherroleid);

        $this->setUser($user);
        $this->assertFalse($this->spdetails->return_isstudent($courseid));
    }

    /**
     * lib.php
     */

    public function test_return_courseurl() {
        global $CFG;

        $url = $this->lib->return_courseurl("1");

        $expectedurl = new moodle_url('/course/view.php', array('id' => "1"));
        $this->assertEquals($expectedurl, $url);
    }

    public function test_return_assessmenturl() {
        $id = $this->assign->id;
        $modname = 'assign';
        $expected = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));

        $this->assertEquals($expected, $this->lib->return_assessmenturl($id, $modname));
    }

    public function return_statusbaseobj() {
        $baseobject = new stdClass;
        $baseobject->statustext = get_string('status_notopen', 'block_gu_spdetails');
        $baseobject->class = null;
        $baseobject->hasstatusurl = false;
        $baseobject->issubcategory = false;

        return $baseobject;
    }

    public function test_return_status() {
        $graded = get_string('status_graded', 'block_gu_spdetails');
        $notopen = get_string('status_notopen', 'block_gu_spdetails');
        $notsubmitted = get_string('status_notsubmitted', 'block_gu_spdetails');
        $overdue = get_string('status_overdue', 'block_gu_spdetails');
        $submit = get_string('status_submit', 'block_gu_spdetails');
        $submitted = get_string('status_submitted', 'block_gu_spdetails');
        $unavailable = get_string('status_unavailable', 'block_gu_spdetails');

        $classgraded = get_string('class_graded', 'block_gu_spdetails');
        $classoverdue = get_string('class_overdue', 'block_gu_spdetails');
        $classsubmit = get_string('class_submit', 'block_gu_spdetails');
        $classsubmitted = get_string('class_submitted', 'block_gu_spdetails');

        $modname = null; $hasgrade = null; $status = null; $submissions = null;
        $allowsubmissionsfromdate = null; $duedate = null; $cutoffdate = null;
        $gradingduedate = null; $hasextension = null; $feedback = null;

        // Test1 $hasgrade.
        $hasgrade = true;
        $returned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $hasgrade = null;

        $expected1 = $this->return_statusbaseobj();
        $expected1->statustext = $graded;
        $expected1->class = $classgraded;

        $this->assertEquals($expected1, $returned1);

        // Test2 $feedback === 'NS' && $duedate < time() && $cutoffdate > time() && $gradingduedate > time().
        $feedback = 'NS'; $duedate = time() - 1; $cutoffdate = time() + 1; $gradingduedate = time() + 1;
        $returned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $feedback = null; $duedate = null; $cutoffdate = null; $gradingduedate = null;

        $expected2 = $this->return_statusbaseobj();
        $expected2->statustext = $overdue;
        $expected2->class = $classoverdue;
        $expected2->hasstatusurl = true;

        $this->assertEquals($expected2, $returned2);

        // Test3 $feedback === 'NS' && $duedate < time() && $cutoffdate < time() && $gradingduedate > time().
        $feedback = 'NS'; $duedate = time() - 1; $cutoffdate = time() - 1; $gradingduedate = time() + 1;
        $returned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $feedback = null; $duedate = null; $cutoffdate = null; $gradingduedate = null;

        $expected3 = $this->return_statusbaseobj();
        $expected3->statustext = $unavailable;

        $this->assertEquals($expected3, $returned3);

        // Test4 $feedback === 'NS' && $duedate < time() && $cutoffdate < time() && $gradingduedate < time().
        $feedback = 'NS'; $duedate = time() - 1; $cutoffdate = time() - 1; $gradingduedate = time() - 1;
        $returned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $feedback = null; $duedate = null; $cutoffdate = null; $gradingduedate = null;

        $expected4 = $this->return_statusbaseobj();
        $expected4->statustext = $notsubmitted;

        $this->assertEquals($expected4, $returned4);

        // Switch to assign.
        $modname = 'assign';
        // Test1 $status === $submitted.
        $status = $submitted;
        $assignreturned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $status = null;

        $assignexpected1 = $this->return_statusbaseobj();
        $assignexpected1->statustext = $submitted;
        $assignexpected1->class = $classsubmitted;

        $this->assertEquals($assignexpected1, $assignreturned1);

        // Test2 $submissions > 0.
        $submissions = 1;
        $assignreturned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null;

        $assignexpected2 = $this->return_statusbaseobj();
        $assignexpected2->statustext = $unavailable;

        $this->assertEquals($assignexpected2, $assignreturned2);

        // Test3 allowsubmissionsfromdate > time() || $duedate == 0.
        $allowsubmissionsfromdate = time() + 1; $duedate = 0;
        $assignreturned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null; $duedate = null;

        $assignexpected3 = $this->return_statusbaseobj();
        $assignexpected3->statustext = $notopen;

        $this->assertEquals($assignexpected3, $assignreturned3);

        // Test4.
        $allowsubmissionsfromdate = time() - 1; $duedate = time() - 1; $cutoffdate = 0;
        $assignreturned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null; $duedate = null; $cutoffdate = null;

        $assignexpected4 = $this->return_statusbaseobj();
        $assignexpected4->statustext = $overdue;
        $assignexpected4->class = $classoverdue;
        $assignexpected4->hasstatusurl = true;

        $this->assertEquals($assignexpected4, $assignreturned4);

        // Test5.
        $allowsubmissionsfromdate = time() - 1; $duedate = time() - 1; $cutoffdate = time() - 1;
        $assignreturned5 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null; $duedate = null; $cutoffdate = null;

        $assignexpected5 = $this->return_statusbaseobj();
        $assignexpected5->statustext = $notsubmitted;

        $this->assertEquals($assignexpected5, $assignreturned5);

        // Test6.
        $allowsubmissionsfromdate = time() - 1; $duedate = time() + 1;
        $assignreturned6 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null; $duedate = null;

        $assignexpected6 = $this->return_statusbaseobj();
        $assignexpected6->hasstatusurl = true;
        $assignexpected6->statustext = $submit;
        $assignexpected6->class = $classsubmit;

        $this->assertEquals($assignexpected6, $assignreturned6);

        // Switch to quiz.
        $modname = 'quiz';
        // Test1 allowsubmissionsfromdate > time().
        $allowsubmissionsfromdate = time() + 1;
        $quizreturned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null;

        $quizexpected1 = $this->return_statusbaseobj();
        $quizexpected1->statustext = $notopen;

        $this->assertEquals($quizexpected1, $quizreturned1);

        // Test2 $status === 'finished'.
        $status = 'finished';
        $quizreturned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $status = null;

        $quizexpected2 = $this->return_statusbaseobj();
        $quizexpected2->statustext = $submitted;
        $quizexpected2->class = $classsubmitted;

        $this->assertEquals($quizexpected2, $quizreturned2);

        // Test3 $duedate < time() && $duedate != 0.
        $duedate = time() - 1;
        $quizreturned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $duedate = null;

        $quizexpected3 = $this->return_statusbaseobj();
        $quizexpected3->statustext = $notsubmitted;

        $this->assertEquals($quizexpected3, $quizreturned3);

        // Test4 not duedate < time() && $duedate != 0.
        $duedate = time() + 1;
        $quizreturned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $duedate = null;
        $quizexpected4 = $this->return_statusbaseobj();
        $quizexpected4->hasstatusurl = true;
        $quizexpected4->statustext = $submit;
        $quizexpected4->class = $classsubmit;

        $this->assertEquals($quizexpected4, $quizreturned4);

        // Switch to 'workshop'.
        $modname = 'workshop';
        // Test1 not empy submissions.
        $submissions = array(1, 2, 3);
        $workshopreturned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null;

        $workshopexpected1 = $this->return_statusbaseobj();
        $workshopexpected1->statustext = $submitted;
        $workshopexpected1->class = $classsubmitted;

        $this->assertEquals($workshopexpected1, $workshopreturned1);

        // Test2.
        $submissions = array(); $allowsubmissionsfromdate = time() + 1; $duedate = 0;
        $workshopreturned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null; $allowsubmissionsfromdate = null; $duedate = null;

        $workshopexpected2 = $this->return_statusbaseobj();
        $workshopexpected2->statustext = $notopen;

        $this->assertEquals($workshopexpected2, $workshopreturned2);

        // Test3.
        $submissions = array(); $allowsubmissionsfromdate = time() - 1; $duedate = time() - 1;
        $workshopreturned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null; $allowsubmissionsfromdate = null; $duedate = null;

        $workshopexpected3 = $this->return_statusbaseobj();
        $workshopexpected3->statustext = $notsubmitted;

        $this->assertEquals($workshopexpected3, $workshopreturned3);

        // Test4.
        $submissions = array(); $allowsubmissionsfromdate = time() - 1; $duedate = time() + 1;
        $workshopreturned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null; $allowsubmissionsfromdate = null; $duedate = null;

        $workshopexpected4 = $this->return_statusbaseobj();
        $workshopexpected4->hasstatusurl = true;
        $workshopexpected4->statustext = $submit;
        $workshopexpected4->class = $classsubmit;

        $this->assertEquals($workshopexpected4, $workshopreturned4);

        // Switch to 'forum'.
        $modname = 'forum';
        // Test1.
        $duedate = time() - 1;
        $status = 'submitted';
        $forumreturned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $status = null;

        $forumexpected1 = $this->return_statusbaseobj();
        $forumexpected1->statustext = $submitted;
        $forumexpected1->class = $classsubmitted;

        $this->assertEquals($forumexpected1, $forumreturned1);

        // Test2 $cutoffdate == 0 || $cutoffdate > time() $duedate < time().
        $cutoffdate = time() + 1;
        $forumreturned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $cutoffdate = null;

        $forumexpected2 = $this->return_statusbaseobj();
        $forumexpected2->statustext = $overdue;
        $forumexpected2->class = $classoverdue;
        $forumexpected2->hasstatusurl = true;

        $this->assertEquals($forumexpected2, $forumreturned2);

        // Test3 ! ($cutoffdate == 0 || $cutoffdate > time()) $duedate < time().
        $cutoffdate = time() - 1;
        $forumreturned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $cutoffdate = null;

        $forumexpected3 = $this->return_statusbaseobj();
        $forumexpected3->statustext = $notsubmitted;

        $this->assertEquals($forumexpected3, $forumreturned3);

        // Test4.
        $duedate = time() + 1;
        $forumreturned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $duedate = null;

        $forumexpected4 = $this->return_statusbaseobj();
        $forumexpected4->hasstatusurl = true;
        $forumexpected4->statustext = $submit;
        $forumexpected4->class = $classsubmit;

        $this->assertEquals($forumexpected4, $forumreturned4);
    }

    public function return_feedbackbaseobject() {
        $fb = new stdClass;
        $fb->feedbacktext = null;
        $fb->hasfeedback = false;
        $fb->issubcategory = false;

        return $fb;
    }

    public function test_return_feedback() {
        $id = null; $modname = null; $hasgrade = null; $feedback = null; $feedbackfiles = null; $status = null;
        $hasturnitin = null; $gradingduedate = null; $duedate = null; $cutoffdate = null; $quizfeedback = null;

        $duedate = get_string('due', 'block_gu_spdetails').userdate(time() + 1,
                   get_string('date_month_d', 'block_gu_spdetails'));
        $na = get_string('notavailable', 'block_gu_spdetails');
        $overdue = get_string('overdue', 'block_gu_spdetails');
        $tbc = get_string('tobeconfirmed', 'block_gu_spdetails');
        $readfeedback = get_string('readfeedback', 'block_gu_spdetails');
        $idintro = get_string('id_intro', 'block_gu_spdetails');
        $idfooter = get_string('id_pagefooter', 'block_gu_spdetails');

        // Hasgrade.
        $hasgrade = true;
        // Switch case to 'assign'.
        $modname = 'assign';
        $feedbackurl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));
        // Test1.
        $hasturnitin = 1;

        $assignreturned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $hasturnitin = null;

        $assignexpected1 = $this->return_feedbackbaseobject();
        $assignexpected1->feedbackurl = $feedbackurl.$idintro;
        $assignexpected1->feedbacktext = $readfeedback;
        $assignexpected1->hasfeedback = true;

        $this->assertEquals($assignexpected1, $assignreturned1);

        // Test2.
        $hasturnitin = 0; $feedback = array(1); $feedbackfiles = 1;

        $assignreturned2 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $hasturnitin = null; $feedback = null; $feedbackfiles = null;

        $assignexpected2 = $this->return_feedbackbaseobject();
        $assignexpected2->feedbackurl = $feedbackurl.$idfooter;
        $assignexpected2->feedbacktext = $readfeedback;
        $assignexpected2->hasfeedback = true;

        $this->assertEquals($assignexpected2, $assignreturned2);

        // Test3.
        $hasturnitin = 0; $feedback = array(); $feedbackfiles = 0; $gradingduedate = time() + 1;
        $duedate = get_string('due', 'block_gu_spdetails').userdate($gradingduedate,
                   get_string('date_month_d', 'block_gu_spdetails'));
        $assignreturned3 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $hasturnitin = null; $feedback = null; $feedbackfiles = null;

        $assignexpected3 = $this->return_feedbackbaseobject();
        $assignexpected3->feedbackurl = null;
        $assignexpected3->feedbacktext = $duedate;

        $this->assertEquals($assignexpected3, $assignreturned3);

        // Test4.
        $hasturnitin = 0; $feedback = array(); $feedbackfiles = 0; $gradingduedate = time() - 1;
        $assignreturned4 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $hasturnitin = null; $feedback = null; $feedbackfiles = null;

        $assignexpected4 = $this->return_feedbackbaseobject();
        $assignexpected4->feedbackurl = null;
        $assignexpected4->feedbacktext = $overdue;

        $this->assertEquals($assignexpected4, $assignreturned4);

        // Test5.
        $hasturnitin = 0; $feedback = array(); $feedbackfiles = 0; $gradingduedate = 0;
        $assignreturned5 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $hasturnitin = null; $feedback = null; $feedbackfiles = null; $gradingduedate = null;

        $assignexpected5 = $this->return_feedbackbaseobject();
        $assignexpected5->feedbackurl = null;
        $assignexpected5->feedbacktext = $tbc;

        $this->assertEquals($assignexpected5, $assignreturned5);

        // Switch case to 'quiz'.
        $modname = 'quiz';
        $feedbackurl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));
        // Test1.
        $quizfeedback = true;
        $quizreturned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $quizfeedback = null;

        $quizexpected1 = $this->return_feedbackbaseobject();
        $quizexpected1->feedbacktext = $readfeedback;
        $quizexpected1->hasfeedback = true;
        $quizexpected1->feedbackurl = $feedbackurl.get_string('id_feedback', 'block_gu_spdetails');

        $this->assertEquals($quizexpected1, $quizreturned1);

        // Test2.
        $quizfeedback = false; $gradingduedate = time() + 1;
        $quizreturned2 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $quizfeedback = null; $gradingduedate = null;

        $quizexpected2 = $this->return_feedbackbaseobject();
        $quizexpected2->feedbacktext = $duedate;

        $this->assertEquals($quizexpected2, $quizreturned2);

        // Test3.
        $quizfeedback = false; $gradingduedate = time() - 1;
        $quizreturned3 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $quizfeedback = null; $gradingduedate = null;

        $quizexpected3 = $this->return_feedbackbaseobject();
        $quizexpected3->feedbacktext = $overdue;

        $this->assertEquals($quizexpected3, $quizreturned3);

        // Test4.
        $quizfeedback = false; $gradingduedate = 0;
        $quizreturned3 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $quizfeedback = null; $gradingduedate = null;

        $quizexpected3 = $this->return_feedbackbaseobject();
        $quizexpected3->feedbacktext = $tbc;

        $this->assertEquals($quizexpected3, $quizreturned3);

        // Switch case to 'workshop'.
        $modname = 'workshop';
        $workshopreturned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                        $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                        $quizfeedback, $status);

        $workshopexpected1 = $this->return_feedbackbaseobject();
        $workshopexpected1->hasfeedback = true;
        $workshopexpected1->feedbacktext = $readfeedback;
        $workshopurl = new moodle_url('/mod/workshop/submission.php', array('cmid' => $id));
        $workshopexpected1->feedbackurl = $workshopurl.$idfooter;

        $this->assertEquals($workshopexpected1, $workshopreturned1);

        // Switch case to 'forum'.
        $modname = 'forum';
        $forumreturned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback, $status);
        $feedbackurl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));

        $forumexpected1 = $this->return_feedbackbaseobject();
        $forumexpected1->hasfeedback = true;
        $forumexpected1->feedbacktext = $readfeedback;
        $forumexpected1->feedbackurl = $feedbackurl.$idfooter;

        $this->assertEquals($forumexpected1, $forumreturned1);

        $modname = null;

        // No grade.
        $hasgrade = false;
        // Test1.
        $gradingduedate = time() + 1; $feedback = 'MV';
        $returned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback, $status);

        $expected1 = $this->return_feedbackbaseobject();
        $expected1->feedbacktext = $duedate;

        $this->assertEquals($returned1, $expected1);

        // Test2.
        $gradingduedate = time() + 1; $feedback = 'NS'; $cutoffdate = time() - 1; $duedate = time() - 1;
        $returned2 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback, $status);

        $expected2 = $this->return_feedbackbaseobject();
        $expected2->feedbacktext = $na;

        $this->assertEquals($returned2, $expected2);

        // Test3.
        $gradingduedate = time() - 1; $feedback = 'NS';
        $returned3 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback, $status);

        $expected3 = $this->return_feedbackbaseobject();
        $expected3->feedbacktext = $na;

        $this->assertEquals($returned3, $expected3);

        // Test4.
        $gradingduedate = time() - 1; $feedback = null;
        $returned4 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback, $status);

        $expected4 = $this->return_feedbackbaseobject();
        $expected4->feedbacktext = $overdue;

        $this->assertEquals($returned4, $expected4);

        // Test5.
        $gradingduedate = 0;
        $returned5 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback, $status);

        $expected5 = $this->return_feedbackbaseobject();
        $expected5->feedbacktext = $tbc;

        $this->assertEquals($returned5, $expected5);
    }

    public function return_gradebaseobj() {
        $grading = new stdClass;
        $grading->gradetext = null;
        $grading->hasgrade = false;
        $grading->isprovisional = false;

        return $grading;
    }

    public function test_return_grading() {
        $finalgrade = null; $gradetype = null; $grademin = null; $grademax = null;
        $gradeinformation = null; $gradingduedate = null; $duedate = null;
        $cutoffdate = null; $scale = null; $feedback = null;
        $convertedgradeid = null; $provisionalgrade = null; $status = null;
        $idnumber = null; $outcomeid = null;
        $finalgrade = 3;
        $intgrade = 3;

        // Test1.
        $gradetype = '1'; $grademax = 22; $grademin = 0; $gradeinformation = true;
        $returned1 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected1 = $this->return_gradebaseobj();
        $expected1->hasgrade = true; $expected1->isprovisional = false;
        $expected1->gradetext = $this->lib->return_22grademaxpoint($intgrade, 1);

        $this->assertEquals($expected1, $returned1);
        // Test2.
        $gradetype = '1'; $grademax = 21; $grademin = 1; $gradeinformation = false;
        $returned2 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected2 = $this->return_gradebaseobj();
        $expected2->hasgrade = true; $expected2->isprovisional = true;
        $expected2->gradetext = "$finalgrade / $grademax";

        $this->assertEquals($expected2, $returned2);

        // Test3.
        $gradetype = '2'; $scale = 'A:1,B:2,C:3,D:4,E:5';
        $returned3 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected3 = $this->return_gradebaseobj();
        $expected3->gradetext = 'C';
        $expected3->hasgrade = true;
        $expected3->isprovisional = true;

        $this->assertEquals($expected3, $returned3);

        // Test4.
        $gradetype = '2'; $scale = 'A1,B2,C3,D4,E5';
        $returned4 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected4 = $this->return_gradebaseobj();
        $expected4->gradetext = 'C3';
        $expected4->hasgrade = true;
        $expected4->isprovisional = true;

        $this->assertEquals($expected4, $returned4);

        // Test5.
        $gradetype = '0'; $feedback = 'feedback';
        $returned4 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected4 = $this->return_gradebaseobj();
        $expected4->gradetext = $feedback;
        $expected4->hasgrade = true;
        $expected4->isprovisional = true;

        $this->assertEquals($expected4, $returned4);

        // Test6.
        $gradetype = '0'; $feedback = null;
        $returned5 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected5 = $this->return_gradebaseobj();
        $expected5->gradetext = get_string('emptyvalue', 'block_gu_spdetails');
        $expected5->hasgrade = true;
        $expected5->isprovisional = true;

        $this->assertEquals($expected5, $returned5);

        $finalgrade = null;
        $duedate = get_string('due', 'block_gu_spdetails').userdate(time() + 1,
                   get_string('date_month_d', 'block_gu_spdetails'));
        $na = get_string('notavailable', 'block_gu_spdetails');
        $overdue = get_string('overdue', 'block_gu_spdetails');
        $tbc = get_string('tobeconfirmed', 'block_gu_spdetails');

        // Test6.
        $gradingduedate = time() + 1; $feedback = 'MV';
        $returned6 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected6 = $this->return_gradebaseobj();
        $expected6->gradetext = $duedate;

        $this->assertEquals($expected6, $returned6);
        // Test7.
        $feedback = 'NS'; $cutoffdate = time() - 1; $duedate = time() - 1;
        $returned7 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected7 = $this->return_gradebaseobj();
        $expected7->gradetext = $na;

        $this->assertEquals($expected7, $returned7);
        // Test8.
        $feedback = null; $cutoffdate = time() + 1; $duedate = time() + 1; $gradingduedate = time() + 1;
        $returned8 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected8 = $this->return_gradebaseobj();
        $expected8->gradetext = get_string('due', 'block_gu_spdetails').userdate($duedate,
                                get_string('date_month_d', 'block_gu_spdetails'));

        $this->assertEquals($expected8, $returned8);
        // Test9.
        $gradingduedate = time() - 1; $feedback = 'NS';
        $returned9 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $duedate = get_string('due', 'block_gu_spdetails').userdate(time() + 1,
                   get_string('date_month_d', 'block_gu_spdetails'));
        $expected9 = $this->return_gradebaseobj();
        $expected9->gradetext = $na;

        $this->assertEquals($expected9, $returned9);
        // Test10.
        $feedback = null;
        $returned10 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected10 = $this->return_gradebaseobj();
        $expected10->gradetext = $overdue;

        $this->assertEquals($expected10, $returned10);
        // Test11.
        $gradingduedate = 0;
        $returned11 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback,
                                $convertedgradeid, $provisionalgrade, $status,
                                $idnumber, $outcomeid);

        $expected11 = $this->return_gradebaseobj();
        $expected11->gradetext = $tbc;

        $this->assertEquals($expected11, $returned11);
    }

    public function test_return_assessmenttype() {
        $lang = 'block_gu_spdetails';

        $expected1 = get_string("formative", $lang);
        $expected2 = get_string("summative", $lang);
        $expected3 = get_string("emptyvalue", $lang);

        $this->assertEquals($expected1, $this->lib->return_assessmenttype("12312 formative"));
        $this->assertEquals($expected2, $this->lib->return_assessmenttype("123123 summative"));
        $this->assertEquals($expected3, $this->lib->return_assessmenttype(time()));
    }

    public function test_return_weight() {
        $lang = 'block_gu_spdetails';
        $assessmenttype = get_string('summative', $lang);
        $aggregation = '10';
        $aggregationcoef = 2;
        $aggregationcoef2 = 0;
        $weight = ($aggregation == '10') ? (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100) :
                      $aggregationcoef2 * 100;

        $expected1 = round($aggregationcoef, 2).'%';
        $this->assertEquals($expected1, $this->lib->return_weight($assessmenttype, $aggregation,
                                                                  $aggregationcoef, $aggregationcoef2, ""));

        $aggregationcoef = 1;
        $expected2 = round($aggregationcoef * 100, 2).'%';
        $this->assertEquals($expected2, $this->lib->return_weight($assessmenttype, $aggregation,
                                                                  $aggregationcoef, $aggregationcoef2, ""));

        $aggregation = '1';
        $expected3 = '—';
        $this->assertEquals($expected3, $this->lib->return_weight($assessmenttype, $aggregation,
                                                                  $aggregationcoef, $aggregationcoef2, ""));
    }

    public function test_return_22grademaxpoint() {

        $values = array('H', 'G2', 'G1', 'F3', 'F2', 'F1',
                        'E3', 'E2', 'E1', 'D3', 'D2', 'D1',
                        'C3', 'C2', 'C1', 'B3', 'B2', 'B1',
                        'A5', 'A4', 'A3', 'A2', 'A1');

        foreach ($values as $index => $value) {
            $this->assertEquals($value, $this->lib->return_22grademaxpoint($index, 1));
            $stringarray = str_split($value);
            if ($stringarray[0] != 'H') {
                $value = $stringarray[0] . '0';
            }
            $this->assertEquals($value, $this->lib->return_22grademaxpoint($index, 2));
        }
    }

    public function test_retrieve_courses() {
        $this->show_ondashboard($this->course->id);

        // 0 student id
        $activetab = TAB_CURRENT;
        $return = $this->lib->retrieve_courses($activetab, 0);
        $this->assertEmpty($return);

        // Current tab.
        $activetab = TAB_CURRENT;
        $return = $this->lib->retrieve_courses($activetab, $this->student->id);
        $this->assertContains($this->course->id, $return);

        // Not current tab.
        $activetab = "";
        $return = $this->lib->retrieve_courses($activetab, $this->student->id);
        $this->assertContains($this->course->id, $return);
    }

    public function test_retrieve_gradable_activities() {
        // Setting up student.
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);

        // Creating course.
        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $this->get_roleid());

        $assign = $this->getDataGenerator()->create_module('assign', array('course' => $course->id));

        $activetab = 'past';
        $userid = $student->id;
        $sortby = 'coursetitle';
        $sortorder = 'ASC';
        $returned1 = $this->lib->retrieve_gradable_activities($activetab, $userid, $sortby, $sortorder, null);

        $this->assertEquals(array(), $returned1);

        $this->show_ondashboard($course->id);
        $returned2 = $this->lib->retrieve_gradable_activities($activetab, $userid, $sortby, $sortorder, null);
        $this->assertEquals($assign->name, $returned2[0]->assessmentname);
    }

    public function test_retrieve_formattedduedate() {
        $duedate = 0;
        $return = $this->lib->return_formattedduedate($duedate);
        $expectedempty = '—';
        $this->assertEquals($expectedempty, $return);

        $duedate = time();
        $return = $this->lib->return_formattedduedate($duedate);
        $expecteddate = userdate($duedate, get_string('date_month_d', 'block_gu_spdetails'));
        $this->assertEquals($expecteddate, $return);

    }

    public function test_retrieve_assessments() {
        global $OUTPUT;
        // Setting up student.
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);

        // Creating course.
        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $this->get_roleid());

        $assign = $this->getDataGenerator()->create_module('assign', array('course' => $course->id));

        $activetab = 'past';
        $page = 0;
        $sortby = 'coursetitle';
        $sortorder = 'ASC';
        $returned1 = $this->lib->retrieve_assessments($activetab, $page, $sortby, $sortorder);

        $html  = html_writer::start_tag('div', array('class' => 'text-xs-center text-center mt-3'));
        $html .= html_writer::tag('img', '', array('class' => 'empty-placeholder-image-lg mt-1',
                                            'src' => $OUTPUT->image_url('noassessments', 'theme'),
                                            'alt' => get_string('noassessments', 'block_gu_spdetails')));
        $html .= html_writer::tag('p', get_string('noassessments', 'block_gu_spdetails'),
                                       array('class' => 'text-muted mt-3'));
        $html .= html_writer::end_tag('div');

        $this->assertEquals($html, $returned1);

        $this->show_ondashboard($course->id);
        $returned2 = $this->lib->retrieve_assessments($activetab, $page, $sortby, $sortorder);

        $this->assertContains($assign->name, $returned2);
    }
}