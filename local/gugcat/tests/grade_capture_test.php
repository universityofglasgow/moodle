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
        $this->student2 = $gen->create_user();
        $this->teacher = $gen->create_user();
        $this->admin = get_admin();
        $this->course = $gen->create_course();
        $this->coursecontext = context_course::instance($this->course->id);
        $gen->enrol_user($this->student->id, $this->course->id, 'student');
        $gen->enrol_user($this->student2->id, $this->course->id, 'student');
        $gen->enrol_user($this->teacher->id, $this->course->id, 'editingteacher');
        $this->students = get_enrolled_users($this->coursecontext, 'local/gugcat:gradable');
        $gen->create_module('assign', array('id' => 1, 'course' => $this->course->id));
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'grademax', '22.00000', array('id'=>$assignid));

        $cm = local_gugcat::get_activities($this->course->id);
        $this->cms = $cm;
        $key = key($cm);
        $this->cm = $cm[$key];
        $modinfo = get_fast_modinfo($this->course);
        $cm_info = $modinfo->get_cm($this->cm->id);
        $this->assign = new assign(context_module::instance($cm_info->id), $cm_info, $this->course->id);
        
        //create grade items
        $this->gradeitem = new grade_item($gen->create_grade_item(['courseid' => $this->course->id, 'iteminfo' => $this->cm->gradeitemid]), false);
        local_gugcat::$STUDENTS = $this->students;
        $this->provisionalgi = local_gugcat::add_grade_item($this->course->id, get_string('provisionalgrd', 'local_gugcat'), $this->cm);
        local_gugcat::$PRVGRADEID = $this->provisionalgi;
        
        local_gugcat::set_prv_grade_id($this->course->id, $this->cm);
        
        $DB->insert_record('grade_grades', array(
            'itemid' => $this->gradeitem->id,
            'userid' => $this->student->id
        ));
    }

    
    public function test_grade_capture_columns() {
        global $gradeitems, $prvgradeid;
        $gradeitems = [];
        $prvgradeid = $this->provisionalgi;
        array_push($gradeitems, $this->gradeitem);
        $firstgrade = get_string('moodlegrade', 'local_gugcat').'<br>[Date]';
        $columns = grade_capture::get_columns();
        $this->assertContains($firstgrade, $columns);
    }
    
    public function test_import_grade(){
        global $gradeitems, $prvgradeid;
        $gradeitems = array();
        $prvgradeid = $this->provisionalgi;
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
        grade_capture::import_from_gradebook($this->course->id, $this->cm, $this->cms);
        $rows = grade_capture::get_rows($this->course, $this->cm, $this->students);
        $row = $rows[0];
        $columns = grade_capture::get_columns();
        $this->assertEquals($row->firstgrade, get_string('nograde', 'local_gugcat'));
        $this->assertNotContains($mgcolumn, $columns);
    }

    public function test_grade_capture_rows() {
        global $gradeitems, $prvgradeid;
        $gradeitems = array();
        $prvgradeid = $this->provisionalgi;
        $rows = grade_capture::get_rows($this->course, $this->cm, array($this->student));
        $row = $rows[0];
        $this->assertEquals($row->studentno, $this->student->id);
        $this->assertEquals($row->firstgrade, get_string('nogradeimport', 'local_gugcat'));
        $this->assertFalse($row->discrepancy);
    }


    public function test_release_provisional_grades() {
        $assign = $this->assign;
        $instance = $assign->get_instance();
        $instance->instance = $instance->id;
        $instance->markingworkflow = 1; //enable marking workflow
        $assign->update_instance($instance);
        $expectedgradeint = 5;
        $expectedgrade = '4.00000'; // -1 for the grade offset
        // Add provisional grades to student
        $grade_ = new grade_grade(array('userid' => $this->student->id, 'itemid' => $this->provisionalgi), true);
        $grade_->information = '1.00000';
        $grade_->rawgrade = $expectedgradeint;
        $grade_->finalgrade = $expectedgradeint;
        $grade_->update();  
        //test release prv grade
        grade_capture::release_prv_grade($this->course->id, $this->cm);
        //check marking workflow to 'released'
        $wfstate = $assign->get_user_flags($this->student->id, true);
        $this->assertEquals($wfstate->workflowstate, 'released');

        //check assign and gb grades if updated
        $assigngrade = $assign->get_user_grade($this->student->id, false);
        $this->assertEquals($assigngrade->grade, $expectedgrade);
        $gbgrade = grade_get_grades($this->course->id, 'mod', $this->cm->modname, $this->cm->instance, $this->student->id);
        $this->assertEquals($gbgrade->items[0]->grades[$this->student->id]->grade, $expectedgrade);
    }

    public function test_capture_admin_grades() {
        //set grade scale first
        local_gugcat::set_grade_scale(3);
        $scale = local_gugcat::$GRADES;
        //check scale has NS and MV
        $this->assertContains(NON_SUBMISSION_AC, $scale);
        $this->assertContains(MEDICAL_EXEMPTION_AC, $scale);
        $this->assertArrayHasKey(NON_SUBMISSION, $scale);
        $this->assertArrayHasKey(MEDICAL_EXEMPTION, $scale);

        $assign = $this->assign;
        $cm = $this->cm;
        // Provisional grades to students
        foreach ($this->students as $student) {
            $grade_ = new grade_grade(array('userid' => $student->id, 'itemid' => $this->provisionalgi), true);
            $grade_->information = '1.00000';
            $grade_->rawgrade = ($student->id != $this->student->id) ? NON_SUBMISSION: MEDICAL_EXEMPTION ; //first student grade = NS, 2nd student = MV
            $grade_->finalgrade = null;
            $grade_->update();  
        }

        //test release prv grade
        grade_capture::release_prv_grade($this->course->id, $cm);

        //check 1st student assign grade
        $assigngrade1 = $assign->get_user_grade($this->student->id, false);
        $this->assertEquals($assigngrade1->grade, 0);
        //check 2nd student assign grade
        $assigngrade2 = $assign->get_user_grade($this->student2->id, false);
        $this->assertEquals($assigngrade2->grade, 0);

        //check gradebook grades
        $gbgrades = grade_get_grades($this->course->id, 'mod', $cm->modname, $cm->instance, array($this->student->id ,$this->student2->id));
        $items = $gbgrades->items[0]->grades;
        // Gradebook grades are null for admin grades
        $this->assertNull($items[$this->student->id]->grade); 
        $this->assertNull($items[$this->student2->id]->grade); 
    }

    public function test_hideshow_grade() {
        grade_capture::import_from_gradebook($this->course->id, $this->cm, $this->cms);
        // hide grade
        $result = grade_capture::hideshowgrade($this->student->id);
        $firstrows = grade_capture::get_rows($this->course, $this->cm, array($this->student));
        $firstrow = $firstrows[0];
        $this->assertEquals($result, 'hidden');
        $this->assertTrue($firstrow->hidden);

        // show grade
        $result = grade_capture::hideshowgrade($this->student->id);
        $this->assertEquals($result, 'shown');

    }

    public function test_set_provisional_weights() {
        global $DB;
        $gi = $this->cm->gradeitem;
        $weightcoef1 = $gi->aggregationcoef; //Aggregation coeficient used for weighted averages or extra credit
        $weightcoef2 = $gi->aggregationcoef2; //Aggregation coeficient used for weighted averages only
        $expectedweight = ((float)$weightcoef1 > 0) ? (float)$weightcoef1 : (float)$weightcoef2;
        grade_capture::set_provisional_weights($this->course->id, $this->cms, $this->students);
        $prvweight = $DB->get_field('grade_grades', 'information', array('itemid' => $this->provisionalgi, 'userid' => $this->student->id));
        $this->assertEquals($prvweight, strval($expectedweight)); //assert provisional grade_grade copied weight from main act
    }
}