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
            'grades' => 10,
            'timemodified' => time()
        ));

        $html = $this->spdetails->get_content();

        $coursename = $this->course->fullname;
        $assignname = $this->assign->name;
        $assignweight = $DB->get_record('grade_items', array('iteminstance'=> $this->assign->id), 'aggregationcoef')->aggregationcoef;
        $assignweight = round($assignweight, 2).'%';
        $assignduedate = $this->assign->duedate;

        foreach(array($coursename, $assignname, $assignduedate) as $value){
            $this->assertStringContainsString($value, $html->text);
        }
    }


    public function test_return_courseurl(){
        global $CFG;

        $url = $this->spdetails->return_courseurl("1");

        $expectedURL = $CFG->wwwroot . "/course/view.php?id=1";
        $this->assertEquals($expectedURL, $url);
    }

    public function test_return_assessmenturl(){
        $id = $this->assign->id;
        $modname = 'assign';
        $expected = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));

        $this->assertEquals($expected, $this->spdetails->return_assessmenturl($id, $modname));
    }

    public function test_retrieve_activity(){
        global $DB, $USER;
        $userid = $USER->id;
        $courseid = $this->course->id;
        $this->add_submissions();

        //assign
        $instance = $this->assign->id;
        $sql = 'SELECT assign.id, assign.name, assign.duedate,
                    assign.allowsubmissionsfromdate as `startdate`, assign.cutoffdate,
                    assign.gradingduedate, assign.teamsubmissiongroupingid,
                    auf.extensionduedate, ao.duedate as `overrideduedate`,
                    ao.allowsubmissionsfromdate as `overridestartdate`,
                    ao.cutoffdate as `overridecutoffdate`, asub.status
                    FROM {assign} assign
                    LEFT JOIN {assign_user_flags} auf
                    ON auf.userid = ? AND auf.assignment = assign.id
                    LEFT JOIN {assign_overrides} ao
                    ON ao.userid = ? AND ao.assignid = assign.id
                    LEFT JOIN {assign_submission} asub
                    ON asub.userid = ? AND asub.assignment = assign.id
                    WHERE assign.id = ? AND assign.course = ?';
        $conditions = array($userid, $userid, $userid, $instance, $courseid);
        $expected = $DB->get_record_sql($sql, $conditions);
        $this->assertEquals($expected, $this->spdetails->retrieve_activity('assign', $instance, $courseid, $userid));

        //forum
        $instance = $this->forum->id;
        $conditions = array('id' => $instance, 'course' => $courseid);
        $columns = 'id, name, duedate, cutoffdate, assessed, grade_forum';
        $expected = $DB->get_record('forum', $conditions, $columns);
        $this->assertEquals($expected, $this->spdetails->retrieve_activity('forum', $instance, $courseid, $userid));

        //quiz
        $instance = $this->quiz->id;
        $sql = 'SELECT quiz.id, quiz.name, quiz.timeopen as `startdate`,
                    quiz.timeclose as `duedate`, quiz.attempts,
                    qo.timeopen as `overridestartdate`,
                    qo.timeclose as `overrideduedate`,
                    qo.attempts as `overrideattempts`,
                    qa.attempt, qa.state
                    FROM {quiz} quiz
                    LEFT JOIN {quiz_overrides} qo ON qo.quiz = quiz.id AND qo.userid = ?
                    LEFT JOIN {quiz_attempts} qa ON qa.quiz = quiz.id AND qa.attempt = quiz.attempts
                    WHERE quiz.id = ? AND quiz.course = ?';
        $conditions = array($userid, $instance, $courseid);
        $expected = $DB->get_record_sql($sql, $conditions);
        $this->assertEquals($expected, $this->spdetails->retrieve_activity('quiz', $instance, $courseid, $userid));

        //workshop
        $instance = $this->workshop->id;
        $sql = 'SELECT workshop.id, workshop.name, workshop.submissionstart as `startdate`,
                    workshop.submissionend as `duedate`, workshop.assessmentstart,
                    workshop.assessmentend as `gradingduedate`,
                    ws.title, ws.grade, wa.gradinggrade, wa.feedbackauthor
                    FROM {workshop} workshop
                    LEFT JOIN {workshop_submissions} ws
                    ON ws.workshopid = workshop.id AND ws.authorid = ?
                    LEFT JOIN {workshop_assessments} wa
                    ON wa.submissionid = ws.id
                    WHERE workshop.id = ? and workshop.course = ?';
        $conditions = array($userid, $instance, $courseid);
        $expected = $DB->get_record_sql($sql, $conditions);
        $this->assertEquals($expected, $this->spdetails->retrieve_activity('workshop', $instance, $courseid, $userid));

        //default
        $this->assertEquals(new stdClass(), $this->spdetails->retrieve_activity('default', $instance, $courseid, $userid));
    }

    public function test_return_status(){
        $lang = 'block_gu_spdetails';

        $expected1 = new stdClass();
        $expected1->text = get_string('graded', $lang);
        $expected1->suffix = get_string('graded', $lang);
        $expected1->hasurl = false;
        $date1 = new stdClass();
        $activity1 = new stdClass();
        $returned1 = $this->spdetails->return_status('', 1, $date1, $activity1);
        $this->assertEquals($expected1, $returned1);

        $expected2 = $expected1;
        $expected2->text = get_string('overdue', $lang);
        $expected2->suffix = get_string('class_overduelinked', $lang);
        $expected2->hasurl = true;
        $date2 = $date1;
        $date2->startdate = time() - 1;
        $date2->duedate = time() + 1;
        $date2->isdueextended = true;
        $activity2 = new stdClass();
        $activity2->status = 'submitted';
        $returned2 = $this->spdetails->return_status('assign', null, $date2, $activity2);
        $this->assertEquals($expected2, $returned2);

        $expected3 = new stdClass();
        $expected3->text = get_string('submitted', $lang);
        $expected3->suffix = get_string('submitted', $lang);
        $expected3->hasurl = false;
        $date3 = $date2;
        $date3->isdueextended = false;
        $activity3 = new stdClass();
        $activity3->status = 'submitted';
        $returned3 = $this->spdetails->return_status('assign', null, $date3, $activity3);
        $this->assertEquals($expected3, $returned3);

        $expected4 = new stdClass();
        $expected4->text = get_string('submit', $lang);
        $expected4->suffix = get_string('submit', $lang);
        $expected4->hasurl = true;
        $date4 = $date3;
        $activity4 = $activity3;
        $returned4 = $this->spdetails->return_status('', null, $date4, $activity4);
        $this->assertEquals($expected4, $returned4);

        $expected5 = new stdClass();
        $expected5->text = get_string('notopen', $lang);
        $expected5->suffix = get_string('class_notopen', $lang);
        $expected5->hasurl = false;
        $date5 = $date4;
        $date5->duedate = 0;
        $date5->isdueextended = false;
        $returned5 = $this->spdetails->return_status('', null, $date5, new stdClass());
        $this->assertEquals($expected5, $returned5);

        $expected6 = new stdClass();
        $expected6->text = get_string('overdue', $lang);
        $expected6->suffix = get_string('class_overduelinked', $lang);
        $expected6->hasurl = true;
        $date6 = $date5;
        $date6->duedate = time() - 1;
        $date6->cutoffdate = time() + 1;
        $returned6 = $this->spdetails->return_status('', null, $date6, new stdClass());
        $this->assertEquals($expected6, $returned6);

        $expected7 = new stdClass();
        $expected7->text = get_string('overdue', $lang);
        $expected7->suffix = get_string('overdue', $lang);
        $expected7->hasurl = false;
        $date7 = $date6;
        $date7->cutoffdate = time() - 1;
        $returned7 = $this->spdetails->return_status('', null, $date7, new stdClass());
        $this->assertEquals($expected7, $returned7);
    }

    public function test_retrieve_grades(){
        global $DB, $USER;
        $this->add_submissions();
        $userid = $USER->id;

        $expected = array();
        $columns = 'id, rawgrade, rawgrademax, rawgrademin, rawscaleid, finalgrade, feedback, feedbackformat, information';

        $expected['assign'] = $DB->get_record('grade_grades',
                                        array('itemid' => $this->assign->gradeid, 'userid' => $userid),
                                        $columns);

        $returned = $this->spdetails->retrieve_grades($userid, $this->assign->gradeid);

        $this->assertEquals($returned, $expected['assign']);
    }

    public function test_return_grade(){
        global $DB;
        $lang = 'block_gu_spdetails';

        $grades = new stdClass();
        $grades->finalgrade = 10;

        $gradeitem = new stdClass();
        $gradeitem->grademax = '20';
        $gradeitem->grademin = '0';

        $expected1 = round(($grades->finalgrade / ($gradeitem->grademax - $gradeitem->grademin)) * 100, 2).'%';
        $expected2 = $this->spdetails->return_22grademaxpoint($grades->finalgrade);

        $gradeitem->gradetype = "1";
        $this->assertEquals($expected1, $this->spdetails->return_grade($gradeitem, $grades));

        $gradeitem->grademax = '22';
        $this->assertEquals($expected2, $this->spdetails->return_grade($gradeitem, $grades));

        //with scale
        $gradeitem->scaleid = 1;
        $gradeitem->gradetype = "2";
        $grades->finalgrade = 1;

        $record = $DB->get_record('scale', array('id'=> 1));
        $scale = make_menu_from_list($record->scale);
        $expectedScale1 = $scale[$grades->finalgrade];

        $this->assertEquals($expectedScale1, $this->spdetails->return_grade($gradeitem, $grades));

        $gradeitem->gradetype = "3";
        $expectedDefault = $grades->finalgrade;

        $this->assertEquals($expectedDefault, $this->spdetails->return_grade($gradeitem, $grades));
    }

    public function test_return_feedbackduedate(){
        $lang = 'block_gu_spdetails';
        $gradingduedate = time();

        $expected1 = get_string('readfeedback', $lang);

        $expected2 = get_string('nofeedback', $lang);

        $expected3 = get_string('due', $lang).
                     userdate($gradingduedate, get_string('convertdate', $lang));

        $this->assertEquals($expected1, $this->spdetails->return_feedbackduedate(true, 1, $gradingduedate));
        $this->assertEquals($expected2, $this->spdetails->return_feedbackduedate(false, 1, $gradingduedate));
        $this->assertEquals($expected3, $this->spdetails->return_feedbackduedate(true, 0, $gradingduedate));
    }

    public function test_return_assessmenttype(){
        $lang = 'block_gu_spdetails';

        $expected1 = get_string("formative", $lang);
        $expected2 = get_string("summative", $lang);
        $expected3 = get_string("emptyvalue", $lang);

        $this->assertEquals($expected1, $this->spdetails->return_assessmenttype("12312 formative"));
        $this->assertEquals($expected2, $this->spdetails->return_assessmenttype("123123 summative"));
        $this->assertEquals($expected3, $this->spdetails->return_assessmenttype(time()));
    }

    public function test_return_coursetitle(){
        global $DB;

        $courseid = $this->course->id;
        $modulename = 'assign';

        $sql = "SELECT cm.*, m.name, md.name as modname
                  FROM {grade_items} gi, {course_modules} cm, {modules} md, {{$modulename}} m
                 WHERE gi.courseid = ? AND
                       gi.itemtype = 'mod' AND
                       gi.itemmodule = ? AND
                       gi.itemnumber = 0 AND
                       gi.gradetype != ? AND
                       gi.iteminstance = cm.instance AND
                       cm.instance = m.id AND
                       md.name = ? AND
                       md.id = cm.module";
        $params = array($courseid, $modulename, 0, $modulename);
        $returned = $DB->get_records_sql($sql, $params);

        foreach($returned as $data){
            $section = $data->section;
            $sectionrecord = $DB->get_record('course_sections', array('id' => $section), 'id, section');

            if ($sectionrecord->section > 0){
                $expected = get_section_name($courseid, $sectionrecord->section);
                $this->assertEquals($expected, $this->spdetails->return_coursetitle($courseid, $section, $this->course->fullname));
            } else {
                $expected = $this->course->fullname;
                $this->assertEquals($expected, $this->spdetails->return_coursetitle($courseid, $section, $this->course->fullname));
            }
        }
    }

    public function test_retrieve_gradeitem() {
        global $DB;

        $instance = $this->assign->id;
        $courseid = $this->course->id;
        $modname = 'assign';
        $itemnumber = 0;
        $activity = new stdClass();
        $activity->grade_forum = 0;

        $conditions = array('courseid' => $courseid, 'itemmodule' => $modname,
                            'iteminstance' => $instance, 'itemnumber' => $itemnumber);
        $columns = 'id, categoryid, itemname, gradetype, grademax, grademin, scaleid,
                    aggregationcoef, aggregationcoef2';

        $expected = $DB->get_record('grade_items', $conditions, $columns);
        $this->assertEquals($expected, $this->spdetails->retrieve_gradeitem($courseid, $modname, $instance, $activity));

        $modname = 'forum';
        $conditions = array('courseid' => $courseid, 'itemmodule' => $modname,
        'iteminstance' => $instance, 'itemnumber' => $itemnumber);

        $expected = $DB->get_record('grade_items', $conditions, $columns);
        $this->assertEquals($expected, $this->spdetails->retrieve_gradeitem($courseid, $modname, $instance, $activity));

        $itemnumber = 1;
        $activity->grade_forum = 1;
        $conditions = array('courseid' => $courseid, 'itemmodule' => $modname,
        'iteminstance' => $instance, 'itemnumber' => $itemnumber);

        $expected = $DB->get_record('grade_items', $conditions, $columns);
        $this->assertEquals($expected, $this->spdetails->retrieve_gradeitem($courseid, $modname, $instance, $activity));
    }

    public function test_retrieve_gradecategory(){
        global $DB;

        $columns = 'id, parent, fullname, aggregation';

        $gradecategory = $this->getDataGenerator()->create_grade_category(array('courseid' => $this->course->id));

        $expected = $DB->get_record('grade_categories', array('id' => $gradecategory->id), $columns);

        $this->assertEquals($expected, $this->spdetails->retrieve_gradecategory($gradecategory->id));
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
        $this->assertEquals($expected1, $this->spdetails->return_weight($assessmenttype, $aggregation, $aggregationcoef, $aggregationcoef2));

        $aggregationcoef = 1;
        $expected2 = round($aggregationcoef * 100, 2).'%';
        $this->assertEquals($expected2, $this->spdetails->return_weight($assessmenttype, $aggregation, $aggregationcoef, $aggregationcoef2));

        $aggregation = '1';
        $expected3 = round($aggregationcoef2 * 100, 2).'%';
        $this->assertEquals($expected3, $this->spdetails->return_weight($assessmenttype, $aggregation, $aggregationcoef, $aggregationcoef2));
    }

    public function test_return_dates(){
        $activity = new stdClass();
        $activity->startdate = time();
        $activity->cutoffdate = time();
        $activity->overrideduedate = time();
        $activity->extensionduedate = time();
        $activity->duedate = time();

        //assign expected 1
        $activity->overrideduedate = time();
        $activity->extensionduedate = time();
        $activity->overridestartdate = time();
        $activity->overridecutoffdate = time();
        $activity->gradingduedate = time();

        $expectedAssign1 = new stdClass();
        $expectedAssign1->duedate = $activity->overrideduedate;
        $expectedAssign1->isdueextended = true;
        $expectedAssign1->startdate = $activity->overridestartdate;
        $expectedAssign1->cutoffdate = $activity->overridecutoffdate;
        $expectedAssign1->gradingduedate = $activity->gradingduedate;

        $this->assertEquals($expectedAssign1, $this->spdetails->return_dates('assign', $activity));

        //assign expected 2
        $activity->overrideduedate = time();
        $activity->extensionduedate = time() + 1;
        $activity->overridestartdate = 0;
        $activity->overridecutoffdate = 0;
        $activity->gradingduedate = 0;

        $expectedAssign2 = new stdClass();
        $expectedAssign2->duedate = $activity->extensionduedate;
        $expectedAssign2->isdueextended = true;
        $expectedAssign2->startdate = $activity->startdate;
        $expectedAssign2->cutoffdate = $activity->cutoffdate;
        $expectedAssign2->gradingduedate = $expectedAssign2->duedate + (86400 * 14);

        $this->assertEquals($expectedAssign2, $this->spdetails->return_dates('assign', $activity));

        //assign expected 3
        $activity->overrideduedate = 0;
        $activity->duedate = 0;
        $activity->gradingduedate = 0;
        $activity->extensionduedate = 0;
        $activity->overridestartdate = 0;
        $activity->overridecutoffdate = 0;
        $activity->gradingduedate = 0;

        $expectedAssign3 = new stdClass();
        $expectedAssign3->duedate = 0;
        $expectedAssign3->startdate = $activity->startdate;
        $expectedAssign3->cutoffdate = $activity->cutoffdate;
        $expectedAssign3->isdueextended = false;
        $expectedAssign3->gradingduedate = '0';

        $this->assertEquals($expectedAssign3, $this->spdetails->return_dates('assign', $activity));

        //quiz expected 1
        $activity->overrideduedate = time();
        $activity->overridestartdate = time();
        $activity->duedate = time();

        $expectedQuiz1 = new stdClass();
        $expectedQuiz1->duedate = $activity->overrideduedate;
        $expectedQuiz1->isdueextended = true;
        $expectedQuiz1->startdate = $activity->overridestartdate;
        $expectedQuiz1->gradingduedate = $expectedQuiz1->duedate;
        $expectedQuiz1->cutoffdate = $activity->cutoffdate;

        $this->assertEquals($expectedQuiz1, $this->spdetails->return_dates('quiz', $activity));

        //quiz expected 2
        $activity->overrideduedate = 0;
        $activity->overridestartdate = 0;
        $activity->startdate = time();
        $activity->duedate = 0;

        $expectedQuiz2 = new stdClass();
        $expectedQuiz2->duedate = 0;
        $expectedQuiz2->isdueextended = false;
        $expectedQuiz2->startdate = $activity->startdate;
        $expectedQuiz2->cutoffdate = $activity->cutoffdate;
        $expectedQuiz2->gradingduedate = '0';

        $this->assertEquals($expectedQuiz2, $this->spdetails->return_dates('quiz', $activity));

        //forum expected 1
        $expectedForum1 = new stdClass();
        $expectedForum1->startdate = $activity->startdate;
        $expectedForum1->cutoffdate = $activity->cutoffdate;
        $expectedForum1->gradingduedate = $activity->cutoffdate;
        $expectedForum1->isdueextended = false;
        $expectedForum1->duedate = $activity->duedate;

        $this->assertEquals($expectedForum1, $this->spdetails->return_dates('forum', $activity));

        //forum expected 2
        $activity->duedate = time();
        unset($activity->cutoffdate);
        unset($activity->startdate);

        $expectedForum2 = new stdClass();
        $expectedForum2->startdate = '0';
        $expectedForum2->cutoffdate = '0';
        $expectedForum2->gradingduedate = $activity->duedate + (86400 * 14);
        $expectedForum2->isdueextended = false;
        $expectedForum2->duedate = $activity->duedate;

        $this->assertEquals($expectedForum2, $this->spdetails->return_dates('forum', $activity));

        //forum expected 3
        $activity->duedate = 0;
        unset($activity->cutoffdate);
        unset($activity->startdate);

        $expectedForum3 = new stdClass();
        $expectedForum3->startdate = '0';
        $expectedForum3->cutoffdate = '0';
        $expectedForum3->gradingduedate = '0';
        $expectedForum3->isdueextended = false;
        $expectedForum3->duedate = $activity->duedate;

        $this->assertEquals($expectedForum3, $this->spdetails->return_dates('forum', $activity));

        //workshop expected 1
        $activity->gradingduedate = time();

        $expectedWorkshop1 = new stdClass();
        $expectedWorkshop1->duedate = $activity->duedate;
        $expectedWorkshop1->isdueextended = false;
        $expectedWorkshop1->startdate = '0';
        $expectedWorkshop1->cutoffdate = '0';
        $expectedWorkshop1->gradingduedate = $activity->gradingduedate;

        $this->assertEquals($expectedWorkshop1, $this->spdetails->return_dates('workshop', $activity));

        //workshop expected 2
        $activity->duedate = time();
        $activity->gradingduedate = 0;

        $expectedWorkshop2 = new stdClass();
        $expectedWorkshop2->duedate = $activity->duedate;
        $expectedWorkshop2->isdueextended = false;
        $expectedWorkshop2->startdate = '0';
        $expectedWorkshop2->cutoffdate = '0';
        $expectedWorkshop2->gradingduedate = $expectedWorkshop2->duedate + (86400 * 14);

        $this->assertEquals($expectedWorkshop2, $this->spdetails->return_dates('workshop', $activity));

        //workshop expected 3
        $activity->gradingduedate = 0;
        $activity->duedate = 0;

        $expectedWorkshop3 = new stdClass();
        $expectedWorkshop3->duedate = $activity->duedate;
        $expectedWorkshop3->isdueextended = false;
        $expectedWorkshop3->startdate = '0';
        $expectedWorkshop3->cutoffdate = '0';
        $expectedWorkshop3->gradingduedate = '0';

        $this->assertEquals($expectedWorkshop3, $this->spdetails->return_dates('workshop', $activity));
    }

    public function test_return_gradingduedate(){
        $lang = 'block_gu_spdetails';

        $finalgrade = 10;
        $gradingduedate = time();

        $expected1 = new stdClass();
        $expected1->hasgrade = true;
        $expected1->gradetext = $finalgrade;
        $this->assertEquals($expected1, $this->spdetails->return_gradingduedate($finalgrade, $gradingduedate));

        $expected2 = new stdClass();
        $expected2->hasgrade = false;
        $expected2->gradetext = get_string('due', $lang) . userdate($gradingduedate, get_string('convertdate', $lang));
        $this->assertEquals($expected2, $this->spdetails->return_gradingduedate(0, $gradingduedate));
    }

    public function test_retrieve_scale(){
        global $DB;
        $scale = $this->getDataGenerator()->create_scale();
        $scaleid = $scale->id;

        $expected = $DB->get_record('scale', array('id' => $scaleid), 'id, scale');
        $this->assertEquals($expected, $this->spdetails->retrieve_scale($scaleid));
    }

    public function test_return_22grademaxpoint(){
        
        $values = array('H', 'G2', 'G1', 'F3', 'F2', 'F1',
                        'E3', 'E2', 'E1', 'D3', 'D2', 'D1',
                        'C3', 'C2', 'C1', 'B3', 'B2', 'B1',
                        'A5', 'A4', 'A3', 'A2', 'A1');

        foreach ($values as $index => $value){
            $this->assertEquals($value, $this->spdetails->return_22grademaxpoint($index));
        }
    }

    public function test_return_assessments(){
        global $USER, $DB;
        $courseid = $this->course->id;
        $userid = $USER->id;

        $returnedData = $this->spdetails->return_assessments(array($courseid), $userid);

        $assessmentIds = array(
            $this->assign->id,
            $this->quiz->id,
            $this->workshop->id,
            $this->forum->id
        );

        foreach ($assessmentIds as $id){
            $index = array_search($id . '', array_column($returnedData, 'instance'));
            $this->assertTrue($index !== false);
        }
    }
}