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

 
use local_gugcat\grade_capture;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/gugcat/lib.php');
require_once($CFG->dirroot.'/local/gugcat/locallib.php');

class grade_capture_testcase extends advanced_testcase {
    public function setUp() {
        global $DB;
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

        $cm = local_gugcat::get_activities($this->course->id);
        $key = key($cm);
        $this->cm = $cm[$key];

        //create grade items
        $this->gradeitem = new grade_item($gen->create_grade_item(['courseid' => $this->course->id, 'iteminfo' => $this->cm->id]), false);
        $this->provisionalgi = new grade_item($gen->create_grade_item(['courseid' => $this->course->id, 
        'iteminfo' => $this->cm->id, 
        'itemname' => get_string('provisionalgrd', 'local_gugcat')
        ]), false);

        $prvgradeid = $this->provisionalgi->id;
        local_gugcat::set_prv_grade_id($this->course->id, $this->cm);

        $DB->insert_record('grade_grades', array(
            'itemid' => $this->gradeitem->id,
            'userid' => $this->student->id
        ));
    }

    public function test_grade_capture_columns() {
        global $gradeitems, $prvgradeid;
        $gradeitems = [];
        $prvgradeid = $this->provisionalgi->id;
        array_push($gradeitems, $this->gradeitem);
        $firstgrade = get_string('moodlegrade', 'local_gugcat').'<br>[Date]';
        $columns = grade_capture::get_columns();
        $this->assertContains($firstgrade, $columns);
    }

    public function test_grade_capture_rows() {
        global $gradeitems, $prvgradeid;
        $gradeitems = array();
        $prvgradeid = $this->provisionalgi->id;
        $rows = grade_capture::get_rows($this->course, $this->cm, $this->students);
        $row = $rows[0];
        $this->assertEquals($row->cnum, 1);
        $this->assertEquals($row->studentno, $this->student->id);
        $this->assertEquals($row->firstgrade, get_string('nogradeimport', 'local_gugcat'));
        $this->assertFalse($row->discrepancy);
    }

    public function test_import_grade(){
        global $gradeitems, $prvgradeid;
        $gradeitems = array();
        $prvgradeid = $this->provisionalgi->id;
        $mggradeitemstr = get_string('moodlegrade', 'local_gugcat');
        $firstrows = grade_capture::get_rows($this->course, $this->cm, $this->students);
        $firstrow = $firstrows[0];
        $columns = grade_capture::get_columns();
        $mgcolumn = $mggradeitemstr.'<br>[Date]';
        $this->assertEquals($firstrow->firstgrade, get_string('nogradeimport', 'local_gugcat'));
        $this->assertContains($mgcolumn, $columns);

        $firststudent = key($this->students);
        $mggradeitem = local_gugcat::add_grade_item($this->course->id, $mggradeitemstr, $this->cm); 
        $gradeid = local_gugcat::add_update_grades($this->students[$firststudent]->id, $mggradeitem, 0);
        grade_capture::import_from_gradebook($this->course->id, $this->cm, $this->students);
        $rows = grade_capture::get_rows($this->course, $this->cm, $this->students);
        $row = $rows[0];
        $columns = grade_capture::get_columns();
        $this->assertEquals($row->firstgrade, get_string('nograde', 'local_gugcat'));
        $this->assertNotContains($mgcolumn, $columns);
    }
}