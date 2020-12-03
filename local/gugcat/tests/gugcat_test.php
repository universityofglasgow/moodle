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
 * Test file.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/gugcat/lib.php');
require_once($CFG->dirroot.'/local/gugcat/locallib.php');

class local_gugcat_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest();

        $gen = $this->getDataGenerator();
        $this->student = $gen->create_user();
        $this->teacher = $gen->create_user();
        $this->admin = get_admin();
        $this->course = $gen->create_course();
        $this->coursecontext = context_course::instance($this->course->id);
        $gen->enrol_user($this->student->id, $this->course->id, 'student');
        $gen->enrol_user($this->teacher->id, $this->course->id, 'editingteacher');
        $this->students = get_enrolled_users($this->coursecontext, 'mod/coursework:submit');
        $assign = $gen->create_module('assign', array('id' => 1, 'course' => $this->course->id));
        $modulecontext = context_module::instance($assign->cmid);
        $assign = new assign($modulecontext, false, false);
        $this->cm = $assign->get_course_module();

        //create grade items
        $this->gradeitem = new grade_item($gen->create_grade_item(['courseid' => $this->course->id, 'iteminfo' => $this->cm->id]), false);
        $this->provisionalgi = new grade_item($gen->create_grade_item(['courseid' => $this->course->id, 
        'iteminfo' => $this->cm->id, 
        'itemname' => get_string('provisionalgrd', 'local_gugcat')
        ]), false);

    }

    public function test_check_course_activities() {
        $activities = local_gugcat::get_activities($this->course->id, $this->cm->id);
        $mods = array_reverse($activities);
        $mod = array_pop($mods);
        $this->assertEquals($mod->id, $this->cm->id);
    }

    public function test_check_course_gradeitems() {
        global $gradeitems;
        $gradeitems = local_gugcat::get_grade_grade_items($this->course, $this->cm);
        $this->assertCount(2, $gradeitems);
    }

    public function test_grade_capture_columns() {
        global $gradeitems, $prvgradeid;
        $gradeitems = [];
        $prvgradeid = $this->provisionalgi->id;
        array_push($gradeitems, $this->gradeitem);
        $date = date("(j/n/Y)", strtotime(userdate($this->cm->added)));
        $firstgrade = get_string('gradebookgrade', 'local_gugcat').'<br>'.$date;

        $columns = local_gugcat::grade_capture_get_columns($this->cm);
        $this->assertContains($firstgrade, $columns);
    }

    public function test_check_prv_grade_item() {
        $prvgradeid = $this->provisionalgi->id;

        local_gugcat::set_prv_grade_id($this->course->id, $this->cm->id, 1);
        $this->assertEquals(local_gugcat::$PRVGRADEID, $prvgradeid);
    }

    public function test_grade_capture_rows() {
        global $gradeitems, $prvgradeid;
        $gradeitems = array();
        $prvgradeid = $this->provisionalgi->id;
        $rows = local_gugcat::grade_capture_get_rows($this->course, $this->cm, $this->students);
        $row = $rows[0];
        $this->assertEquals($row->cnum, 1);
        $this->assertEquals($row->studentno, $this->student->id);
        $this->assertEquals($row->firstgrade, get_string('nograde', 'local_gugcat'));
        $this->assertFalse($row->discrepancy);
    }

    public function test_get_grade_reasons() {
        $reasons = local_gugcat::get_reasons();
        $this->assertContains(get_string('gi_goodcause', 'local_gugcat'), $reasons);
        $this->assertContains(get_string('gi_latepenalty', 'local_gugcat'), $reasons);
        $this->assertContains(get_string('gi_cappedgrade', 'local_gugcat'), $reasons);
        $this->assertContains(get_string('gi_secondgrade', 'local_gugcat'), $reasons);
        $this->assertContains(get_string('gi_thirdgrade', 'local_gugcat'), $reasons);
        $this->assertContains(get_string('gi_agreedgrade', 'local_gugcat'), $reasons);
        $this->assertContains(get_string('gi_moderatedgrade', 'local_gugcat'), $reasons);
        $this->assertContains(get_string('gi_conductpenalty', 'local_gugcat'), $reasons);
        $this->assertContains(get_string('reasonother', 'local_gugcat'), $reasons);
    }

    public function test_get_grade_item_id() {
        $gen = $this->getDataGenerator();
        $sndgradestr = get_string('gi_secondgrade', 'local_gugcat');
        $sndgradegi = new grade_item($gen->create_grade_item([
            'courseid' => $this->course->id, 
            'iteminfo' => $this->cm->id,
            'itemname' => $sndgradestr,
            ]), false);

        $id = local_gugcat::get_grade_item_id($this->course->id, $this->cm->id, $sndgradestr);
        $this->assertEquals( $id, $sndgradegi->id);
    }

    public function test_if_record_of_user_not_empty() {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(array('name'=>'Advanced Programming'));
        $courserecord = $DB->get_records('course');
        $this->assertNotEmpty($courserecord, 'empty');
    }

    public function check_the_data_of_student_if_empty_or_not() {
        global $DB;

        $this->resetAfterTest();

        // Create two users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Add the course creator role to the course contact and assign a user to that role.
        $course = $this->getDataGenerator()->create_course(array('name'=>'Advanced Programming'));
        $coursecontext = context_course::instance($course->id);

        // Enrol users 1 and 2 in first course.
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);

        $students = get_role_users(5 , $coursecontext);

        $this->assertNotEmpty($students, 'empty');
    }

    public function check_the_data_of_first_assignment_if_empty_or_not() {
        global $DB;

        $this->resetAfterTest();

        // Create five users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Add the course creator role to the course contact and assign a user to that role.
        $course = $this->getDataGenerator()->create_course(array('name'=>'Advanced Programming'));
        $coursecontext = context_course::instance($course->id);

        // Enrol users 1 and 2 in first course.
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);

        $students = get_role_users(5 , $coursecontext);

        $ass_no_one = null;
        $arr_of_students = array();
        $arr_of_students_with_assignments = array();

        foreach($students as $keys => $student){
            // Store the fetched data to the array of arr_of_students
            $arr_of_students[$student->id] = (integer)$student->id;
        }

        $implode_user_id = implode(",", $arr_of_students);

        $first_assigmnent_sql = $DB->get_records_sql("SELECT DISTINCT assignment FROM `mdl_assign_grades` WHERE userid IN('$implode_user_id') LIMIT 1 OFFSET 0");

        foreach($first_assigmnent_sql as $ass_1){
            foreach($ass_1 as $ass_value){
                $ass_no_one = $ass_value;
            }
        }

        // Assignment no 1.
        $assign_one_grading_info = grade_get_grades($course->id, 'mod', 'assign', (integer)$ass_no_one, array_keys($arr_of_students));

        $this->assertNotEmpty($assign_one_grading_info, 'empty');
    }

    public function check_the_data_of_second_assignment_if_empty_or_not() {
        global $DB;

        $this->resetAfterTest();

        // Create five users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Add the course creator role to the course contact and assign a user to that role.
        $course = $this->getDataGenerator()->create_course(array('name'=>'Advanced Programming'));
        $coursecontext = context_course::instance($course->id);

        // Enrol users 1 and 2 in first course.
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);

        $students = get_role_users(5 , $coursecontext);

        $ass_no_two = null;
        $arr_of_students = array();
        $arr_of_students_with_assignments = array();

        foreach($students as $keys => $student){
            // Store the fetched data to the array of arr_of_students
            $arr_of_students[$student->id] = (integer)$student->id;
        }

        $implode_user_id = implode(",", $arr_of_students);

        $second_assigment_sql = $DB->get_records_sql("SELECT DISTINCT assignment FROM `mdl_assign_grades` WHERE userid IN('$implode_user_id') LIMIT 1 OFFSET 1");

        foreach($second_assigment_sql as $ass_2){
            foreach($ass_2 as $ass_value){
                $ass_no_two = $ass_value;
            }
        }

        // Assignment no 2.
        $assign_two_grading_info = grade_get_grades($course->id, 'mod', 'assign', (integer)$ass_no_two, array_keys($arr_of_students));

        $this->assertNotEmpty($assign_two_grading_info, 'empty');
    }

    public function check_the_data_of_first_exam_if_empty_or_not() {
        global $DB;

        $this->resetAfterTest();

        // Create five users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Add the course creator role to the course contact and assign a user to that role.
        $course = $this->getDataGenerator()->create_course(array('name'=>'Advanced Programming'));
        $coursecontext = context_course::instance($course->id);

        // Enrol users 1 and 2 in first course.
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);

        $students = get_role_users(5 , $coursecontext);

        $arr_of_students = array();

        foreach($students as $keys => $student){
            // Store the fetched data to the array of arr_of_students
            $arr_of_students[$student->id] = (integer)$student->id;
        }

        // Exam no 1.
        $exam_one_grading_info = grade_get_grades($course->id, 'mod', 'quiz', 1, array_keys($arr_of_students));

        $this->assertNotEmpty($exam_one_grading_info, 'empty');
    }

    public function check_the_data_of_second_exam_if_empty_or_not() {
        global $DB;

        $this->resetAfterTest();

        // Create five users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Add the course creator role to the course contact and assign a user to that role.
        $course = $this->getDataGenerator()->create_course(array('name'=>'Advanced Programming'));
        $coursecontext = context_course::instance($course->id);

        // Enrol users 1 and 2 in first course.
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);

        $students = get_role_users(5 , $coursecontext);

        $arr_of_students = array();

        foreach($students as $keys => $student){
            // Store the fetched data to the array of arr_of_students
            $arr_of_students[$student->id] = (integer)$student->id;
        }

        // Exam no 2.
        $exam_two_grading_info = grade_get_grades($course->id, 'mod', 'quiz', 2, array_keys($arr_of_students));

        $this->assertNotEmpty($exam_two_grading_info, 'empty');
    }
}
