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
 }