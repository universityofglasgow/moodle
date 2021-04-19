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

class block_gu_spdetails_testcase extends advanced_testcase {

    public function setUp() {
        global $DB;
        $this->resetAfterTest(true);
        $this->spdetails = new block_gu_spdetails();
        $this->lib = new assessments_details();

        //setting up student
        $student = $this->getDataGenerator()->create_user(array('email'=>'user1@example.com', 'username'=>'user1'));
        $this->setUser($student);
        $this->student = $student;

        //creating course
        $category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(array('name'=>'Some course', 'category'=>$category->id));
        $course = $this->course;
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $this->get_roleid());

        $this->assign = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id));
        $this->quiz = $this->getDataGenerator()->create_module('quiz', array('course' => $this->course->id));
        $this->survey = $this->getDataGenerator()->create_module('survey', array('course' => $this->course->id));
        $this->wiki = $this->getDataGenerator()->create_module('wiki', array('course' => $this->course->id));
        $this->workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $this->course->id));
        $this->forum = $this->getDataGenerator()->create_module('forum', array('course' => $this->course->id, 'grade_forum' => 100));
        $this->forum2 = $this->getDataGenerator()->create_module('forum', array('course' => $this->course->id, 'grade_forum' => 100));

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
    
    public function add_submissions(){
        global $DB, $USER, $CFG;

        $userid = $USER->id;

        //assign
        $assignNotSubmitted = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id));
        $DB->insert_record('assign_submission', array(
            'assignment' => $this->assign->id,
            'userid' => $userid,
            'attemptnumber' => 0,
            'status' => 'submitted'
        ));

        $assignSQL = "SELECT ma.name,
                        ma.allowsubmissionsfromdate as `startdate`,
                        ma.duedate, ma.gradingduedate, mgi.id as `gradeid`,
                        mgi.aggregationcoef as `weight`, mgi.aggregationcoef2 as `weight01`
                        FROM {assign} ma
                        JOIN {grade_items} mgi ON mgi.iteminstance = ma.id
                        AND mgi.itemmodule = ?
                        WHERE ma.id = ?";
        $assessmentrecord = $DB->get_record_sql($assignSQL, array('assign', $this->assign->id));
        $this->assign->gradeid = $assessmentrecord->gradeid;

        //quiz
        $quizNotSubmitted = $this->getDataGenerator()->create_module('quiz', array('course' => $this->course->id));
        $DB->insert_record('quiz_grades', array(
            'quiz' => $this->quiz->id,
            'userid' => $userid,
            'grades' => 10,
            'timemodified' => time()
        ));

        //survey
        $surveyNotSubmitted = $this->getDataGenerator()->create_module('survey', array('course' => $this->course->id));
        $DB->insert_record('survey_answers', array(
            'survey' => $this->survey->id,
            'userid' => $userid,
            'answer1' => '',
            'answer2' => ''
        ));

        //workshop
        $workshopNotSubmitted = $this->getDataGenerator()->create_module('workshop', array('course' => $this->course->id));
        $DB->insert_record('workshop_submissions', array(
            'workshopid' => $this->workshop->id,
            'authorid' => $userid,
            'timecreated' => time(),
            'timemodified' => time()
        ));

        $this->notsubmittedassign = $assignNotSubmitted;
        $this->notsubmittedquiz = $quizNotSubmitted;
        $this->notsubmittedsurvey = $surveyNotSubmitted;
        $this->notsubmittedworkshop = $workshopNotSubmitted;
    }

    public function get_roleid($archetype = 'student'){
        global $DB;

        $role = $DB->get_record("role", array('archetype' => $archetype));
        return $role->id;
    }

    public function show_ondashboard($courseid){
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

        $cff = $DB->get_record("customfield_field", array('shortname'=>'show_on_studentdashboard'));

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
    public function test_applicable_formats(){
        $returned = $this->spdetails->applicable_formats();
        $this->assertEquals($returned, array('my' => true));
    }

    public function test_get_content(){
        global $DB;

        $returned1 = $this->spdetails->get_content();
        $this->assertEquals(null, $returned1->text);

        $this->show_ondashboard($this->course->id);
        $this->spdetails->content = null;
        $this->spdetails->page = new moodle_page();

        $returned2 = $this->spdetails->get_content();
        $this->assertTrue($returned2->text != null);
    }

    public function test_return_enrolledcourses(){
        global $USER;

        $this->show_ondashboard($this->course->id);
        $this->assertEquals(array($this->course->id), $this->spdetails->return_enrolledcourses($USER->id));
    }

    public function test_return_isstudent(){
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

    public function test_return_courseurl(){
        global $CFG;

        $url = $this->lib->return_courseurl("1");

        $expectedURL = new moodle_url('/course/view.php', array('id' => "1"));
        $this->assertEquals($expectedURL, $url);
    }

    public function test_return_assessmenturl(){
        $id = $this->assign->id;
        $modname = 'assign';
        $expected = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));

        $this->assertEquals($expected, $this->lib->return_assessmenturl($id, $modname));
    }

    public function return_statusBaseObject(){
        $baseObject = new stdClass;
        $baseObject->statustext = get_string('status_notopen', 'block_gu_spdetails');
        $baseObject->class = null;
        $baseObject->hasstatusurl = false;

        return $baseObject;
    }

    public function test_return_status(){
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

        //test1 $hasgrade
        $hasgrade = true;
        $returned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $hasgrade = null;

        $expected1 = $this->return_statusBaseObject();
        $expected1->statustext = $graded;
        $expected1->class = $classgraded;

        $this->assertEquals($expected1, $returned1);

        //test2 $feedback === 'NS' && $duedate < time() && $cutoffdate > time() && $gradingduedate > time()
        $feedback = 'NS'; $duedate = time() - 1; $cutoffdate = time() + 1; $gradingduedate = time() + 1;
        $returned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $feedback = null; $duedate = null; $cutoffdate = null; $gradingduedate = null;

        $expected2 = $this->return_statusBaseObject();
        $expected2->statustext = $overdue;
        $expected2->class = $classoverdue;
        $expected2->hasstatusurl = true;

        $this->assertEquals($expected2, $returned2);

        //test3 $feedback === 'NS' && $duedate < time() && $cutoffdate < time() && $gradingduedate > time()
        $feedback = 'NS'; $duedate = time() - 1; $cutoffdate = time() - 1; $gradingduedate = time() + 1;
        $returned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $feedback = null; $duedate = null; $cutoffdate = null; $gradingduedate = null;

        $expected3 = $this->return_statusBaseObject();
        $expected3->statustext = $unavailable;

        $this->assertEquals($expected3, $returned3);

        //test4 $feedback === 'NS' && $duedate < time() && $cutoffdate < time() && $gradingduedate < time()
        $feedback = 'NS'; $duedate = time() - 1; $cutoffdate = time() - 1; $gradingduedate = time() - 1;
        $returned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $feedback = null; $duedate = null; $cutoffdate = null; $gradingduedate = null;

        $expected4 = $this->return_statusBaseObject();
        $expected4->statustext = $notsubmitted;

        $this->assertEquals($expected4, $returned4);

        //switch $modname case 'assign'
        $modname = 'assign';
        //test1 $status === $submitted
        $status = $submitted;
        $assignReturned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $status = null;

        $assignExpected1 = $this->return_statusBaseObject();
        $assignExpected1->statustext = $submitted;
        $assignExpected1->class = $classsubmitted;

        $this->assertEquals($assignExpected1, $assignReturned1);

        //test2 $submissions > 0
        $submissions = 1;
        $assignReturned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null;

        $assignExpected2 = $this->return_statusBaseObject();
        $assignExpected2->statustext = $unavailable;

        $this->assertEquals($assignExpected2, $assignReturned2);

        //test3 allowsubmissionsfromdate > time() || $duedate == 0
        $allowsubmissionsfromdate = time() + 1; $duedate = 0;
        $assignReturned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null; $duedate = null;

        $assignExpected3 = $this->return_statusBaseObject();
        $assignExpected3->statustext = $notopen;

        $this->assertEquals($assignExpected3, $assignReturned3);

        //test4 not ($allowsubmissionsfromdate > time() || $duedate == 0) and duedate < time() $cutoffdate == 0 || $cutoffdate > time()
        $allowsubmissionsfromdate = time() - 1; $duedate = time() - 1; $cutoffdate = 0;
        $assignReturned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null; $duedate = null; $cutoffdate = null;

        $assignExpected4 = $this->return_statusBaseObject();
        $assignExpected4->statustext = $overdue;
        $assignExpected4->class = $classoverdue;
        $assignExpected4->hasstatusurl = true;

        $this->assertEquals($assignExpected4, $assignReturned4);

        //test5 not ($allowsubmissionsfromdate > time() || $duedate == 0) duedate < time() not ($cutoffdate == 0 || $cutoffdate > time())
        $allowsubmissionsfromdate = time() - 1; $duedate = time() - 1; $cutoffdate = time() - 1;
        $assignReturned5 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null; $duedate = null; $cutoffdate = null;

        $assignExpected5 = $this->return_statusBaseObject();
        $assignExpected5->statustext = $notsubmitted;

        $this->assertEquals($assignExpected5, $assignReturned5);

        //test6 not ($allowsubmissionsfromdate > time() || $duedate == 0 duedate < time() )
        $allowsubmissionsfromdate = time() - 1; $duedate = time() + 1;
        $assignReturned6 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null; $duedate = null;

        $assignExpected6 = $this->return_statusBaseObject();
        $assignExpected6->hasstatusurl = true;
        $assignExpected6->statustext = $submit;
        $assignExpected6->class = $classsubmit;

        $this->assertEquals($assignExpected6, $assignReturned6);

        //switch $modname case 'assign'
        $modname = 'quiz';
        //test1 allowsubmissionsfromdate > time()
        $allowsubmissionsfromdate = time() + 1;
        $quizReturned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $allowsubmissionsfromdate = null;

        $quizExpected1 = $this->return_statusBaseObject();
        $quizExpected1->statustext = $notopen;

        $this->assertEquals($quizExpected1, $quizReturned1);

        //test2 $status === 'finished'
        $status = 'finished';
        $quizReturned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $status = null;

        $quizExpected2 = $this->return_statusBaseObject();
        $quizExpected2->statustext = $submitted;
        $quizExpected2->class = $classsubmitted;

        $this->assertEquals($quizExpected2, $quizReturned2);

        //test3 $duedate < time() && $duedate != 0
        $duedate = time() - 1;
        $quizReturned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $duedate = null;
        
        $quizExpected3 = $this->return_statusBaseObject();
        $quizExpected3->statustext = $notsubmitted;

        $this->assertEquals($quizExpected3, $quizReturned3);

        //test4 not duedate < time() && $duedate != 0
        $duedate = time() + 1;
        $quizReturned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $duedate = null;
        $quizExpected4 = $this->return_statusBaseObject();
        $quizExpected4->hasstatusurl = true;
        $quizExpected4->statustext = $submit;
        $quizExpected4->class = $classsubmit;

        $this->assertEquals($quizExpected4, $quizReturned4);

        //switch $modname case 'assign'
        $modname = 'workshop';
        //test1 !empty($submissions)
        $submissions = array(1,2,3);
        $workshopReturned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null;

        $workshopExpected1 = $this->return_statusBaseObject();
        $workshopExpected1->statustext = $submitted;
        $workshopExpected1->class = $classsubmitted;

        $this->assertEquals($workshopExpected1, $workshopReturned1);

        //test2 empty($submissions) ($allowsubmissionsfromdate > time() || $duedate == 0)
        $submissions = array(); $allowsubmissionsfromdate = time() + 1; $duedate = 0;
        $workshopReturned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null; $allowsubmissionsfromdate = null; $duedate = null;

        $workshopExpected2 = $this->return_statusBaseObject();
        $workshopExpected2->statustext = $notopen;

        $this->assertEquals($workshopExpected2, $workshopReturned2);

        //test3 empty($submissions) not ($allowsubmissionsfromdate > time() || $duedate == 0) $duedate < time()
        $submissions = array(); $allowsubmissionsfromdate = time() - 1; $duedate = time() - 1;
        $workshopReturned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null; $allowsubmissionsfromdate = null; $duedate = null;

        $workshopExpected3 = $this->return_statusBaseObject();
        $workshopExpected3->statustext = $notsubmitted;

        $this->assertEquals($workshopExpected3, $workshopReturned3);

        //test4 empty($submissions) not ($allowsubmissionsfromdate > time() || $duedate == 0) not $duedate < time()
        $submissions = array(); $allowsubmissionsfromdate = time() - 1; $duedate = time() + 1;
        $workshopReturned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $submissions = null; $allowsubmissionsfromdate = null; $duedate = null;

        $workshopExpected4 = $this->return_statusBaseObject();
        $workshopExpected4->hasstatusurl = true;
        $workshopExpected4->statustext = $submit;
        $workshopExpected4->class = $classsubmit;

        $this->assertEquals($workshopExpected4, $workshopReturned4);

        //switch $modname case 'assign'
        $modname = 'forum';
        //test1 $status === 'submitted' $duedate < time()
        $duedate = time() - 1;
        $status = 'submitted';
        $forumReturned1 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $status = null;

        $forumExpected1 = $this->return_statusBaseObject();
        $forumExpected1->statustext = $submitted;
        $forumExpected1->class = $classsubmitted;

        $this->assertEquals($forumExpected1, $forumReturned1);

        //test2 $cutoffdate == 0 || $cutoffdate > time() $duedate < time()
        $cutoffdate = time() + 1;
        $forumReturned2 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $cutoffdate = null;

        $forumExpected2 = $this->return_statusBaseObject();
        $forumExpected2->statustext = $overdue;
        $forumExpected2->class = $classoverdue;
        $forumExpected2->hasstatusurl = true;

        $this->assertEquals($forumExpected2, $forumReturned2);

        //test3 ! ($cutoffdate == 0 || $cutoffdate > time()) $duedate < time()
        $cutoffdate = time() - 1;
        $forumReturned3 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $cutoffdate = null;

        $forumExpected3 = $this->return_statusBaseObject();
        $forumExpected3->statustext = $notsubmitted;

        $this->assertEquals($forumExpected3, $forumReturned3);

        //test4 !($duedate < time())
        $duedate = time() + 1;
        $forumReturned4 = $this->lib->return_status($modname, $hasgrade, $status, $submissions,
                                                $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                                $gradingduedate, $hasextension, $feedback);
        $duedate = null;

        $forumExpected4 = $this->return_statusBaseObject();
        $forumExpected4->hasstatusurl = true;
        $forumExpected4->statustext = $submit;
        $forumExpected4->class = $classsubmit;

        $this->assertEquals($forumExpected4, $forumReturned4);
    }

    public function return_feedbackBaseObject(){
        $fb = new stdClass;
        $fb->feedbacktext = null;
        $fb->hasfeedback = false;

        return $fb;
    }

    public function test_return_feedback(){
        $id = null; $modname = null; $hasgrade = null; $feedback = null; $feedbackfiles = null;
        $hasturnitin = null; $gradingduedate = null; $duedate = null; $cutoffdate = null; $quizfeedback = null;

        $duedate = get_string('due', 'block_gu_spdetails').userdate(time() + 1,
                   get_string('date_month_d', 'block_gu_spdetails'));
        $na = get_string('notavailable', 'block_gu_spdetails');
        $overdue = get_string('overdue', 'block_gu_spdetails');
        $tbc = get_string('tobeconfirmed', 'block_gu_spdetails');
        $readfeedback = get_string('readfeedback', 'block_gu_spdetails');
        $idintro = get_string('id_intro', 'block_gu_spdetails');
        $idfooter = get_string('id_pagefooter', 'block_gu_spdetails');

        //hasgrade
        $hasgrade = true;
        //switch case 'assign'
        $modname = 'assign';
        $feedbackurl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));
        //test1 $hasturnitin > 0 isset($fb->feedbackurl)
        $hasturnitin = 1;

        $assignReturned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $hasturnitin = null;

        $assignExpected1 = $this->return_feedbackBaseObject();
        $assignExpected1->feedbackurl = $feedbackurl.$idintro;
        $assignExpected1->feedbacktext = $readfeedback;
        $assignExpected1->hasfeedback = true;

        $this->assertEquals($assignExpected1, $assignReturned1);

        //test2 !($hasturnitin > 0) && (!empty($feedback) || $feedbackfiles > 0)
        $hasturnitin = 0; $feedback = array(1); $feedbackfiles = 1;

        $assignReturned2 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $hasturnitin = null; $feedback = null; $feedbackfiles = null;

        $assignExpected2 = $this->return_feedbackBaseObject();
        $assignExpected2->feedbackurl = $feedbackurl.$idfooter;
        $assignExpected2->feedbacktext = $readfeedback;
        $assignExpected2->hasfeedback = true;

        $this->assertEquals($assignExpected2, $assignReturned2);

        //test3 !($hasturnitin > 0) && !(!empty($feedback) || $feedbackfiles > 0) $gradingduedate > 0 $gradingduedate > time()
        $hasturnitin = 0; $feedback = array(); $feedbackfiles = 0; $gradingduedate = time() + 1;
        $duedate = get_string('due', 'block_gu_spdetails').userdate($gradingduedate,
                   get_string('date_month_d', 'block_gu_spdetails'));
        $assignReturned3 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $hasturnitin = null; $feedback = null; $feedbackfiles = null;

        $assignExpected3 = $this->return_feedbackBaseObject();
        $assignExpected3->feedbackurl = null;
        $assignExpected3->feedbacktext = $duedate;

        $this->assertEquals($assignExpected3, $assignReturned3);

        //test4 !($hasturnitin > 0) && !(!empty($feedback) || $feedbackfiles > 0) $gradingduedate > 0 $gradingduedate < time()
        $hasturnitin = 0; $feedback = array(); $feedbackfiles = 0; $gradingduedate = time() - 1;
        $assignReturned4 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $hasturnitin = null; $feedback = null; $feedbackfiles = null;

        $assignExpected4 = $this->return_feedbackBaseObject();
        $assignExpected4->feedbackurl = null;
        $assignExpected4->feedbacktext = $overdue;

        $this->assertEquals($assignExpected4, $assignReturned4);

        //test5 !($hasturnitin > 0) && !(!empty($feedback) || $feedbackfiles > 0) $gradingduedate == 0
        $hasturnitin = 0; $feedback = array(); $feedbackfiles = 0; $gradingduedate = 0;
        $assignReturned5 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $hasturnitin = null; $feedback = null; $feedbackfiles = null; $gradingduedate = null;

        $assignExpected5 = $this->return_feedbackBaseObject();
        $assignExpected5->feedbackurl = null;
        $assignExpected5->feedbacktext = $tbc;

        $this->assertEquals($assignExpected5, $assignReturned5);

        //switch case 'quiz'
        $modname = 'quiz';
        $feedbackurl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));
        //test1 $quizfeedback
        $quizfeedback = true;
        $quizReturned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $quizfeedback = null;

        $quizExpected1 = $this->return_feedbackBaseObject();
        $quizExpected1->feedbacktext = $readfeedback;
        $quizExpected1->hasfeedback = true;
        $quizExpected1->feedbackurl = $feedbackurl.get_string('id_feedback', 'block_gu_spdetails');

        $this->assertEquals($quizExpected1, $quizReturned1);

        //test2 !$quizfeedback $gradingduedate > 0  $gradingduedate > time()
        $quizfeedback = false; $gradingduedate = time() + 1;
        $quizReturned2 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $quizfeedback = null; $gradingduedate = null;

        $quizExpected2 = $this->return_feedbackBaseObject();
        $quizExpected2->feedbacktext = $duedate;

        $this->assertEquals($quizExpected2, $quizReturned2);

        //test3 !$quizfeedback $gradingduedate > 0  $gradingduedate < time()
        $quizfeedback = false; $gradingduedate = time() - 1;
        $quizReturned3 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $quizfeedback = null; $gradingduedate = null;

        $quizExpected3 = $this->return_feedbackBaseObject();
        $quizExpected3->feedbacktext = $overdue;

        $this->assertEquals($quizExpected3, $quizReturned3);

        //test3 !$quizfeedback $gradingduedate > 0  $gradingduedate = 0
        $quizfeedback = false; $gradingduedate = 0;
        $quizReturned3 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $quizfeedback = null; $gradingduedate = null;

        $quizExpected3 = $this->return_feedbackBaseObject();
        $quizExpected3->feedbacktext = $tbc;

        $this->assertEquals($quizExpected3, $quizReturned3);

        //switch case 'workshop'
        $modname = 'workshop';
        $workshopReturned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                        $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                        $quizfeedback);
        
        $workshopExpected1 = $this->return_feedbackBaseObject();
        $workshopExpected1->hasfeedback = true;
        $workshopExpected1->feedbacktext = $readfeedback;
        $workshopurl = new moodle_url('/mod/workshop/submission.php', array('cmid' => $id));
        $workshopExpected1->feedbackurl = $workshopurl.$idfooter;

        $this->assertEquals($workshopExpected1, $workshopReturned1);

        //switch case 'forum'
        $modname = 'forum';
        $forumReturned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                    $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                    $quizfeedback);
        $feedbackurl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));

        $forumExpected1 = $this->return_feedbackBaseObject();
        $forumExpected1->hasfeedback = true;
        $forumExpected1->feedbacktext = $readfeedback;
        $forumExpected1->feedbackurl = $feedbackurl.$idfooter;

        $this->assertEquals($forumExpected1, $forumReturned1);

        $modname = null;

        //no grade
        $hasgrade = false;
        //test1 gradingduedate > 0 feedback === 'MV' gradingduedate > time()
        $gradingduedate = time() + 1; $feedback = 'MV';
        $returned1 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback);

        $expected1 = $this->return_feedbackBaseObject();
        $expected1->feedbacktext = $duedate;

        $this->assertEquals($returned1, $expected1);

        //test2 $gradingduedate > time() $feedback === 'NS' &&  $cutoffdate < time() && $duedate < time()
        $gradingduedate = time() + 1; $feedback = 'NS'; $cutoffdate = time() - 1; $duedate = time() - 1;
        $returned2 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback);

        $expected2 = $this->return_feedbackBaseObject();
        $expected2->feedbacktext = $na;

        $this->assertEquals($returned2, $expected2);

        //test3 $gradingduedate < time() $feedback === 'NS' 
        $gradingduedate = time() - 1; $feedback = 'NS';
        $returned3 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback);

        $expected3 = $this->return_feedbackBaseObject();
        $expected3->feedbacktext = $na;

        $this->assertEquals($returned3, $expected3);

        //test4 $gradingduedate < time() $feedback === 'NS' 
        $gradingduedate = time() - 1; $feedback = null;
        $returned4 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback);

        $expected4 = $this->return_feedbackBaseObject();
        $expected4->feedbacktext = $overdue;

        $this->assertEquals($returned4, $expected4);

        //test5 $gradingduedate < 0
        $gradingduedate = 0;
        $returned5 = $this->lib->return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                                $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                                $quizfeedback);

        $expected5 = $this->return_feedbackBaseObject();
        $expected5->feedbacktext = $tbc;

        $this->assertEquals($returned5, $expected5);
    }

    public function return_gradeBaseObject(){
        $grading = new stdClass;
        $grading->gradetext = null;
        $grading->hasgrade = false;
        $grading->isprovisional = false;

        return $grading;
    }

    public function test_return_grading(){
        $finalgrade = null; $gradetype = null; $grademin = null; $grademax = null;
        $gradeinformation = null; $gradingduedate = null; $duedate = null;
        $cutoffdate = null; $scale = null; $feedback = null;
        //isset($finalgrade)
        $finalgrade = 3;
        $intgrade = 3;

        //test1 $gradetype '1' ($grademax == 22 && $grademin == 0) $gradeinformation
        $gradetype = '1'; $grademax = 22; $grademin = 0; $gradeinformation = true;
        $returned1 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected1 = $this->return_gradeBaseObject();
        $expected1->hasgrade = true; $expected1->isprovisional = false;
        $expected1->gradetext = $this->lib->return_22grademaxpoint($intgrade);

        $this->assertEquals($expected1, $returned1);
        //test2 $gradetype '1' ($grademax != 22 && $grademin != 0) !$gradeinformation
        $gradetype = '1'; $grademax = 21; $grademin = 1; $gradeinformation = false;
        $returned2 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected2 = $this->return_gradeBaseObject();
        $expected2->hasgrade = true; $expected2->isprovisional = true;
        $expected2->gradetext = round(($intgrade / ($grademax - $grademin)) * 100, 2).'%';

        $this->assertEquals($expected2, $returned2);

        //test3
        $gradetype = '2'; $scale = 'A:1,B:2,C:3,D:4,E:5';
        $returned3 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected3 = $this->return_gradeBaseObject();
        $expected3->gradetext = 'C';
        $expected3->hasgrade = true;
        $expected3->isprovisional = true;

        $this->assertEquals($expected3, $returned3);

        //test4
        $gradetype = '2'; $scale = 'A1,B2,C3,D4,E5';
        $returned4 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected4 = $this->return_gradeBaseObject();
        $expected4->gradetext = 'C3';
        $expected4->hasgrade = true;
        $expected4->isprovisional = true;

        $this->assertEquals($expected4, $returned4);

        //test5
        $gradetype = '0'; $feedback = 'feedback';
        $returned4 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected4 = $this->return_gradeBaseObject();
        $expected4->gradetext = $feedback;
        $expected4->hasgrade = true;
        $expected4->isprovisional = true;

        $this->assertEquals($expected4, $returned4);

        //test5
        $gradetype = '0'; $feedback = null;
        $returned5 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected5 = $this->return_gradeBaseObject();
        $expected5->gradetext = get_string('emptyvalue', 'block_gu_spdetails');
        $expected5->hasgrade = true;
        $expected5->isprovisional = true;

        $this->assertEquals($expected5, $returned5);

        //!$finalgrade
        $finalgrade = null;
        $duedate = get_string('due', 'block_gu_spdetails').userdate(time() + 1,
                   get_string('date_month_d', 'block_gu_spdetails'));
        $na = get_string('notavailable', 'block_gu_spdetails');
        $overdue = get_string('overdue', 'block_gu_spdetails');
        $tbc = get_string('tobeconfirmed', 'block_gu_spdetails');

        //test6 gradingduedate > 0 feedback 'MV'
        $gradingduedate = time() + 1; $feedback = 'MV';
        $returned6 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected6 = $this->return_gradeBaseObject();
        $expected6->gradetext = $duedate;

        $this->assertEquals($expected6, $returned6);
        //test7 gradingduedate > 0 ($feedback === 'NS' && $cutoffdate < time() && $duedate < time()
        $feedback = 'NS'; $cutoffdate = time() - 1; $duedate = time() - 1;
        $returned7 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected7 = $this->return_gradeBaseObject();
        $expected7->gradetext = $na;

        $this->assertEquals($expected7, $returned7);
        //test8 gradingduedate > 0 ($feedback === 'NS' && $cutoffdate < time() && $duedate > time()
        $feedback = null; $cutoffdate = time() + 1; $duedate = time() + 1; $gradingduedate = time() + 1;
        $returned8 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected8 = $this->return_gradeBaseObject();
        $expected8->gradetext = get_string('due', 'block_gu_spdetails').userdate($duedate,
                                get_string('date_month_d', 'block_gu_spdetails'));

        $this->assertEquals($expected8, $returned8);
        //test9 gradingduedate < time() $feedback === 'NS'
        $gradingduedate = time() - 1; $feedback = 'NS';
        $returned9 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $duedate = get_string('due', 'block_gu_spdetails').userdate(time() + 1,
                   get_string('date_month_d', 'block_gu_spdetails'));
        $expected9 = $this->return_gradeBaseObject();
        $expected9->gradetext = $na;

        $this->assertEquals($expected9, $returned9);
        //test10 gradingduedate < time() $feedback != 'NS'
        $feedback = null;
        $returned10 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected10 = $this->return_gradeBaseObject();
        $expected10->gradetext = $overdue;

        $this->assertEquals($expected10, $returned10);
        //test10 gradingduedate = 0
        $gradingduedate = 0;
        $returned11 = $this->lib->return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                $gradeinformation, $gradingduedate, $duedate,
                                $cutoffdate, $scale, $feedback);

        $expected11 = $this->return_gradeBaseObject();
        $expected11->gradetext = $tbc;

        $this->assertEquals($expected11, $returned11);
    }

    public function test_return_assessmenttype(){
        $lang = 'block_gu_spdetails';

        $expected1 = get_string("formative", $lang);
        $expected2 = get_string("summative", $lang);
        $expected3 = get_string("emptyvalue", $lang);

        $this->assertEquals($expected1, $this->lib->return_assessmenttype("12312 formative"));
        $this->assertEquals($expected2, $this->lib->return_assessmenttype("123123 summative"));
        $this->assertEquals($expected3, $this->lib->return_assessmenttype(time()));
    }

    public function test_return_weight(){
        $lang = 'block_gu_spdetails';
        $assessmenttype = get_string('summative', $lang);
        $aggregation = '10';
        $aggregationcoef = 2;
        $aggregationcoef2 = 0;
        $weight = ($aggregation == '10') ? (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100) :
                      $aggregationcoef2 * 100;

        $expected1 = round($aggregationcoef, 2).'%';
        $this->assertEquals($expected1, $this->lib->return_weight($assessmenttype, $aggregation, $aggregationcoef, $aggregationcoef2));

        $aggregationcoef = 1;
        $expected2 = round($aggregationcoef * 100, 2).'%';
        $this->assertEquals($expected2, $this->lib->return_weight($assessmenttype, $aggregation, $aggregationcoef, $aggregationcoef2));

        $aggregation = '1';
        $expected3 = '—';
        $this->assertEquals($expected3, $this->lib->return_weight($assessmenttype, $aggregation, $aggregationcoef, $aggregationcoef2));
    }

    public function test_return_22grademaxpoint(){
        
        $values = array('H', 'G2', 'G1', 'F3', 'F2', 'F1',
                        'E3', 'E2', 'E1', 'D3', 'D2', 'D1',
                        'C3', 'C2', 'C1', 'B3', 'B2', 'B1',
                        'A5', 'A4', 'A3', 'A2', 'A1');

        foreach ($values as $index => $value){
            $this->assertEquals($value, $this->lib->return_22grademaxpoint($index));
        }
    }

    public function test_retrieve_courses(){
        $this->show_ondashboard($this->course->id);

        // 0 student id
        $activetab = TAB_CURRENT;
        $return = $this->lib->retrieve_courses($activetab, 0);
        $this->assertEmpty($return);

        // Current tab
        $activetab = TAB_CURRENT;
        $return = $this->lib->retrieve_courses($activetab, $this->student->id);
        $this->assertContains($this->course->id, $return);
        
        // Not current tab
        $activetab = "";
        $return = $this->lib->retrieve_courses($activetab, $this->student->id);
        $this->assertContains($this->course->id, $return);
    }

    public function test_retrieve_gradable_activities(){
        //setting up student
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);

        //creating course
        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $this->get_roleid());

        $assign = $this->getDataGenerator()->create_module('assign', array('course' => $course->id));

        $activetab = 'past';
        $userid = $student->id;
        $sortby = 'coursetitle';
        $sortorder = 'ASC';
        $returned1 = $this->lib->retrieve_gradable_activities($activetab, $userid, $sortby, $sortorder);

        $this->assertEquals(array(), $returned1);

        $this->show_ondashboard($course->id);
        $returned2 = $this->lib->retrieve_gradable_activities($activetab, $userid, $sortby, $sortorder);
        $this->assertEquals($assign->name, $returned2[0]->assessmentname);

    }

    public function test_retrieve_formattedduedate(){
        $duedate = 0;
        $return = $this->lib->return_formattedduedate($duedate);
        $expectedempty = '—';
        $this->assertEquals($expectedempty, $return);

        $duedate = time();
        $return = $this->lib->return_formattedduedate($duedate);
        $expecteddate = userdate($duedate, get_string('date_month_d', 'block_gu_spdetails'));
        $this->assertEquals($expecteddate, $return);
 
    }

    public function test_retrieve_assessments(){
        global $OUTPUT;
        //setting up student
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);

        //creating course
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
        
        // echo($returned2);
        $this->assertContains($assign->name, $returned2);
    }
}