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
        $this->gradeitem = new grade_item($gen->create_grade_item(['courseid' => $this->course->id, 'iteminfo' => $this->cm->id]), false);

    }

    public function test_check_course_activities() {
        $activities = get_activities($this->course->id, $this->cm->id);
        $this->assertEquals($activities[0]->id, $this->cm->id);
    }

    public function test_check_columns() {
        global $gradeitems;
        $gradeitems = [];
        array_push($gradeitems, $this->gradeitem);
        $columns = get_columns();
        $this->assertContains('Candidate no.', $columns);
        $this->assertContains('Student no.', $columns);
        $this->assertContains('Surname', $columns);
        $this->assertContains('Forename', $columns);
        $this->assertContains('1st Grade', $columns);
    }

    public function test_check_course_gradeitems() {
        global $DB;
        $this->assertCount(1, $DB->get_records_select('grade_items',
            $DB->sql_compare_text('courseid') . " = " . $DB->sql_compare_text(':courseid')
            . " AND " . $DB->sql_compare_text('iteminfo') . " = " . $DB->sql_compare_text(':iteminfo'), [
                'courseid' => $this->course->id,
                'iteminfo' => $this->cm->id,
            ]));
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
