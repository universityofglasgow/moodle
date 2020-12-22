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

        //setting up student
        $student = $this->getDataGenerator()->create_user(array('email'=>'user1@example.com', 'username'=>'user1'));
        $this->setUser($student);

        //creating course
        $category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(array('name'=>'Some course', 'category'=>$category->id));
        $course = $this->course;
        $this->getDataGenerator()->enrol_user($student->id, $course->id);

        $this->assign = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id));
        $this->quiz = $this->getDataGenerator()->create_module('quiz', array('course' => $this->course->id));
        $this->survey = $this->getDataGenerator()->create_module('survey', array('course' => $this->course->id));
        $this->wiki = $this->getDataGenerator()->create_module('wiki', array('course' => $this->course->id));
        $this->workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $this->course->id));
        $this->attendance = $this->getDataGenerator()->create_module('attendance', array('course' => $this->course->id));

        $this->gradeitem = $this->getDataGenerator()->create_grade_item(array(
            'itemtype' => 'mod',
            'itemmodule' => 'quiz',
            'courseid' => $this->course->id,
            'iteminstance' => $this->quiz->id
        ));

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
                        FROM `". $CFG->prefix ."assign` ma
                        JOIN `". $CFG->prefix ."grade_items` mgi ON mgi.iteminstance = ma.id
                        AND mgi.itemmodule = ?
                        WHERE ma.id = ?";
        $assessmentrecord = $DB->get_record_sql($assignSQL, array('assign', $this->assign->id));
        $this->assign->gradeid = $assessmentrecord->gradeid;

        //quiz
        $quizNotSubmitted = $this->getDataGenerator()->create_module('quiz', array('course' => $this->course->id));
        $DB->insert_record('quiz_grades', array(
            'quiz' => $this->quiz->id,
            'userid' => $userid,
            'grade' => 10,
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

    public function test_applicable_formats(){
        $returned = $this->spdetails->applicable_formats();
        $this->assertEquals($returned, array('my' => true));
    }

    public function test_get_content(){
        global $DB, $USER;
        $userid = $USER->id;

        $this->add_submissions();

        $DB->insert_record('quiz_grades', array(
            'quiz' => $this->notsubmittedquiz->id,
            'userid' => $userid,
            'grade' => 10,
            'timemodified' => time()
        ));

        $html = $this->spdetails->get_content();

        $coursename = $this->course->shortname . ' ' . $this->course->fullname;
        $assignname = $this->assign->name;
        $assignweight = $DB->get_record('grade_items', array('iteminstance'=> $this->assign->id), 'aggregationcoef')->aggregationcoef;
        $assignduedate = $this->assign->duedate;

        foreach(array($coursename, $assignname, $assignweight, $assignduedate) as $value){
            $this->assertStringContainsString($value, $html->text);
        }
    }

    public function test_retrieve_assessments(){
        global $DB, $CFG;

        $courseids = array($this->course->id);
        $activities = array('assign', 'quiz', 'survey', 'wiki', 'workshop');

        list($inactivities, $aparams) = $DB->get_in_or_equal($activities, SQL_PARAMS_NAMED);
        list($incourses, $cparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        $params = array();
        $params += $aparams;
        $params += $cparams;


        $sql = "SELECT DISTINCT mcm.id, mcm.course, mcm.instance, mm.name,
                    mcm.completionexpected
                    FROM `". $CFG->prefix ."course_modules` mcm
                    JOIN `". $CFG->prefix ."modules` mm ON mm.id = mcm.module
                    WHERE mm.name {$inactivities}
                    AND mcm.course {$incourses}";

        $expected1 = $DB->get_records_sql($sql, $params);
        $returned1 = $this->spdetails->retrieve_assessments($courseids, $activities);

        $expected2 = array();
        $returned2 = $this->spdetails->retrieve_assessments(array('book'), array(-1));

        $this->assertEquals($expected1, $returned1);
        $this->assertEquals($expected2, $returned2);
    }

    public function test_retrieve_courserecord(){ 
        global $DB;
        $expected = $DB->get_record('course', array('id' => $this->course->id), 'fullname, shortname');
        $returned = $this->spdetails->retrieve_courserecord($this->course->id);
        $this->assertEquals($returned, $expected);
    }

    public function test_retrieve_assessmentrecord(){
        global $DB, $CFG, $USER;
        $userid = $USER->id;

        $assignSQL = "SELECT ma.id, ma.name,
                        ma.allowsubmissionsfromdate as `startdate`,
                        ma.duedate, ma.cutoffdate, ma.gradingduedate,
                        mgi.id as `gradeid`, mgi.aggregationcoef as `weight`,
                        mao.allowsubmissionsfromdate as `overridestartdate`,
                        mao.duedate as `overrideduedate`,
                        mao.cutoffdate as `overridecutoffdate`,
                        mgc.fullname as `categoryname`
                        FROM `". $CFG->prefix ."assign` ma
                        JOIN `". $CFG->prefix ."grade_items` mgi
                        ON mgi.iteminstance = ma.id AND mgi.itemmodule = ?
                        JOIN `". $CFG->prefix ."grade_categories` mgc
                        ON mgc.id = mgi.categoryid AND mgc.courseid = mgi.courseid
                        LEFT JOIN `". $CFG->prefix ."assign_overrides` mao
                        ON mao.userid = ? AND mao.assignid = ?
                        WHERE ma.id = ?";

        $quizSQL = "SELECT DISTINCT mq.id as `gradeid`, mq.name,
                        mq.timeopen as `startdate`,
                        mq.timeclose as `duedate`, mq.timelimit,
                        (mq.timeclose + (86400 * 14)) as `gradingduedate`,
                        mgi.aggregationcoef as `weight`, mgg.feedback,
                        mqo.timeopen as `overridestartdate`,
                        mqo.timeclose as `overrideduedate`,
                        mqo.timelimit as `overridelimit`,
                        mgc.fullname as `categoryname`
                        FROM `". $CFG->prefix ."quiz` mq
                        JOIN `". $CFG->prefix ."grade_items` mgi
                        ON mgi.iteminstance = mq.id AND mgi.itemmodule = ?
                        JOIN `". $CFG->prefix ."grade_grades` mgg
                        ON mgg.itemid = mgi.id
                        JOIN `". $CFG->prefix ."grade_categories` mgc
                        ON mgc.id = mgi.categoryid AND mgc.courseid = mgi.courseid
                        LEFT JOIN `". $CFG->prefix ."quiz_overrides` mqo
                        ON mqo.userid = ? AND mqo.quiz = ?
                        WHERE mq.id = ?";

        //assignment
        $expectedAssign = $DB->get_record_sql($assignSQL, array('assign', $userid, $this->assign->id, $this->assign->id));
        $assignReturn = $this->spdetails->retrieve_assessmentrecord('assign', $this->assign->id, $userid);
        $this->assertEquals($assignReturn, $expectedAssign);
        $this->assertEquals(new stdClass(), $this->spdetails->retrieve_assessmentrecord('assign', -1, $userid));

        //quiz
        $expectedQuiz = $DB->get_record_sql($quizSQL, array('quiz', $userid, $this->quiz->id, $this->quiz->id));
        $quizReturn = $this->spdetails->retrieve_assessmentrecord('quiz', $this->quiz->id, $userid);
        $this->assertEquals($quizReturn, $expectedQuiz);
        $this->assertEquals(new stdClass(), $this->spdetails->retrieve_assessmentrecord('quiz', -1, $userid));

        //workshop
        $expectedWorkshop = $DB->get_record('workshop', array('id' => $this->workshop->id),
                                                                    'name, id as `gradeid`, submissionstart as `startdate`, ' .
                                                                    'submissionend as `duedate`, assessmentend as `gradingduedate`');
        $workshopReturn = $this->spdetails->retrieve_assessmentrecord('workshop', $this->workshop->id, $userid);
        $this->assertEquals($workshopReturn, $expectedWorkshop);

        //default (attendance)
        $expectedDefault1 = $DB->get_record('attendance', array('id' => $this->attendance->id), 'name');
        $defaultReturn1 = $this->spdetails->retrieve_assessmentrecord('attendance', $this->attendance->id, $userid);
        $this->assertEquals($defaultReturn1, $expectedDefault1);
        $this->assertEquals(new stdClass(), $this->spdetails->retrieve_assessmentrecord('attendance', -1, $userid));
    }

    public function test_return_courseurl(){
        global $CFG;

        $url = $this->spdetails->return_courseurl("1");

        $expectedURL = $CFG->wwwroot . "/course/view.php?id=1";
        $this->assertEquals($expectedURL, $url);
    }

    public function test_return_assessmenturl(){
        global $CFG;

        $url1 = $this->spdetails->return_assessmenturl("name", "1", true);
        $expectedURL1 = $CFG->wwwroot."/mod/" . "name/view.php?id=1&action=editsubmission";

        $url2 = $this->spdetails->return_assessmenturl("name", "1", false);
        $expectedURL2 = $CFG->wwwroot."/mod/" . "name/view.php?id=1";

        $this->assertEquals($expectedURL1, $url1);
        $this->assertEquals($expectedURL2, $url2);
    }

    public function test_retrieve_submission(){
        global $DB, $USER;
        $userid = $USER->id;

        $this->add_submissions();

        //assign
        $returnedAssign1 = $this->spdetails->retrieve_submission('assign', $this->assign->id, $userid);
        $returnedAssign2 = $this->spdetails->retrieve_submission('assign', $this->notsubmittedassign->id, $userid);

        $this->assertEquals($returnedAssign1->status, 'submitted');
        $this->assertEquals($returnedAssign2, new stdClass());

        //quiz
        $returnedQuiz1 = $this->spdetails->retrieve_submission('quiz', $this->quiz->id, $userid);
        $returnedQuiz2 = $this->spdetails->retrieve_submission('quiz', $this->notsubmittedquiz->id, $userid);
        $this->assertEquals($returnedQuiz1->status, 'submitted');
        $this->assertEquals($returnedQuiz2->status, null);

        //workshop
        $returnedWorkshop1 = $this->spdetails->retrieve_submission('workshop', $this->workshop->id, $userid);
        $returnedWorkshop2 = $this->spdetails->retrieve_submission('workshop', $this->notsubmittedworkshop->id, $userid);
        $this->assertEquals($returnedWorkshop1->status, 'submitted');
        $this->assertEquals($returnedWorkshop2->status, null);

        //default
        $returnedDefault = $this->spdetails->retrieve_submission('default', 0, $userid);
        $this->assertEquals($returnedDefault, new stdClass());
    }

    public function test_return_status(){

        $lang = 'block_gu_spdetails';

        $submission1 = $this->spdetails->return_status('submitted', 0, 0, 10);
        $expected1 = new stdClass();
        $expected1->hasurl = false;
        $expected1->status = get_string('status_graded', $lang);
        $expected1->suffix = get_string('status_graded', $lang);

        $this->assertEquals($submission1, $expected1);

        $submission2 = $this->spdetails->return_status('', time() - 1, time() + 1, 10);
        $expected2 = new stdClass();
        $expected2->hasurl = true;
        $expected2->status = get_string('status_submit', $lang);
        $expected2->suffix = get_string('status_submit', $lang);

        $this->assertEquals($submission2, $expected2);

        $submission3 = $this->spdetails->return_status('', time() - 1, time() - 1, 10);
        $expected3 = new stdClass();
        $expected3->hasurl = false;
        $expected3->status = get_string('status_overdue', $lang);
        $expected3->suffix = get_string('status_overdue', $lang);

        $this->assertEquals($submission3, $expected3);

        $submission4 = $this->spdetails->return_status('', time() + 1, time() - 1, 10);
        $expected4 = new stdClass();
        $expected4->hasurl = false;
        $expected4->status = get_string('status_notopen', $lang);
        $expected4->suffix = get_string('class_notopen', $lang);

        $this->assertEquals($submission4, $expected4);

        $submission5 = $this->spdetails->return_status('submitted', time() + 1, time() - 1, null);
        $expected5 = new stdClass();
        $expected5->hasurl = false;
        $expected5->status = 'submitted';
        $expected5->suffix = 'submitted';

        $this->assertEquals($submission5, $expected5);
    }

    public function test_retrieve_grades(){
        global $DB, $USER;
        $this->add_submissions();
        $userid = $USER->id;

        $expected = array();

        $expected['assign'] = $DB->get_record('grade_grades',
                                        array('itemid' => $this->assign->gradeid, 'userid' => $userid),
                                        'finalgrade, feedback');
        $expected['quiz'] = $DB->get_record('quiz_grades',
                                        array('quiz' => $this->quiz->id, 'userid' => $userid),
                                        '*');
        $expected['quiz']->finalgrade = $expected['quiz']->grade;
        $expected['workshop'] = $DB->get_record('workshop_submissions',
                                        array('workshopid' => $this->workshop->id, 'authorid' => $userid),
                                        'grade as `finalgrade`, feedbackauthor as `feedback`');

        $returned = $this->spdetails->retrieve_grades('assign', $this->assign->gradeid , $userid);
        $this->assertEquals($returned, $expected['assign']);

        $returned = $this->spdetails->retrieve_grades('quiz', $this->quiz->id, $userid);
        $this->assertEquals($returned, $expected['quiz']);

        $returned = $this->spdetails->retrieve_grades('workshop', $this->workshop->id, $userid);
        $this->assertEquals($returned, $expected['workshop']);

        $returned = $this->spdetails->retrieve_grades('default', -1, $userid);
        $this->assertEquals($returned, new stdClass());
    }

    public function test_return_grade(){
        $lang = 'block_gu_spdetails';
        $grade = 10;

        $expected1 = $grade;
        $expected2 = get_string('due', $lang).userdate(time(),  get_string('convertdate', $lang));
        $expected3 = get_string('emptyvalue', $lang);

        $this->assertEquals($expected1, $this->spdetails->return_grade($grade, time()));
        $this->assertEquals($expected2, $this->spdetails->return_grade(false, time()));
        $this->assertEquals($expected3, $this->spdetails->return_grade(false, 0));
    }

    public function test_return_feedback(){
        $lang = 'block_gu_spdetails';
        $gradingduedate = time();
        
        $expected1 = new stdClass();
        $expected1->hasurl = true;
        $expected1->text = get_string('readfeedback', $lang);

        $expected2 = new stdClass();
        $expected2->hasurl = false;
        $expected2->text = get_string('due', $lang).userdate($gradingduedate, get_string('convertdate', $lang));

        $expected3 = new stdClass();
        $expected3->hasurl = true;
        $expected3->text = get_string('emptyvalue', $lang);

        $this->assertEquals($expected1, $this->spdetails->return_feedback(true, $gradingduedate));
        $this->assertEquals($expected2, $this->spdetails->return_feedback(false, $gradingduedate));
        $this->assertEquals($expected3, $this->spdetails->return_feedback(true, 0));
    }

}