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

use local_gugcat\grade_aggregation;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/gugcat/lib.php');
require_once($CFG->dirroot.'/local/gugcat/locallib.php');
require_once($CFG->dirroot.'/local/gugcat/classes/form/addeditgradeform.php');


class add_edit_page_testcase extends advanced_testcase {

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
        $gen->create_module('assign', array('id' => 1, 'course' => $this->course->id));

        $cm = local_gugcat::get_activities($this->course->id);
        $key = key($cm);
        $this->cm = $cm[$key];
        $modinfo = get_fast_modinfo($this->course);
        $cm_info = $modinfo->get_cm($this->cm->id);
        $this->assign = new assign(context_module::instance($cm_info->id), $cm_info, $this->course->id);
    }

    public function test_filter_grade_version(){
        global $DB;

        $course = $DB->get_record('course', ['id' => $this->course->id], '*', MUST_EXIST);
        $gradeitems = local_gugcat::get_grade_grade_items($course, $this->cm);
        $gradeversions = local_gugcat::filter_grade_version($gradeitems, $this->student->id);
        $this->assertNotNull($gradeversions, "");
    }

    public function test_get_grade_grade_items(){
        global $DB;

        $course = $DB->get_record('course', ['id' => $this->course->id], '*', MUST_EXIST);
        $gradeitems = local_gugcat::get_grade_grade_items($course, $this->cm);
        $this->assertNotNull($gradeitems, "");
    }

    public function test_add_grade_item(){
        $gcgradestr = get_string('gi_goodcause', 'local_gugcat');
        $gradeitemid = local_gugcat::add_grade_item($this->course->id, $gcgradestr, $this->cm);
        $id = local_gugcat::get_grade_item_id($this->course->id, $this->cm->id, $gcgradestr);
        $this->assertEquals($gradeitemid, $id);
    }

    public function test_add_update_grade(){
        $gcgradestr = get_string('gi_goodcause', 'local_gugcat');
        $expectednotes = 'testnote';
        $gradeitemid = local_gugcat::add_grade_item($this->course->id, $gcgradestr, $this->cm);
        $grades = local_gugcat::add_update_grades($this->student->id, $gradeitemid, '5.00000', $expectednotes);
        $this->assertNotNull($grades, "variable is null or not");
    }
}
