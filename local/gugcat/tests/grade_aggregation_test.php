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
use local_gugcat\grade_capture;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/gugcat/lib.php');
require_once($CFG->dirroot.'/local/gugcat/locallib.php');

class grade_aggregation_testcase extends advanced_testcase {

    public function setUp() {
        global $DB;
        $this->resetAfterTest();

        $gen = $this->getDataGenerator();
        $this->student1 = $gen->create_user();
        $this->student2 = $gen->create_user();
        $this->teacher = $gen->create_user();
        $this->admin = get_admin();
        $this->course = $gen->create_course();
        $this->coursecontext = context_course::instance($this->course->id);
        $gen->enrol_user($this->student1->id, $this->course->id, 'student');
        $gen->enrol_user($this->student2->id, $this->course->id, 'student');
        $gen->enrol_user($this->teacher->id, $this->course->id, 'editingteacher');
        $this->students = get_enrolled_users($this->coursecontext, 'moodle/competency:coursecompetencygradable');
        $gen->create_module('assign', array('id' => 1, 'course' => $this->course->id, 'name'=>'test'));
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'grademax', '22.00000', array('id'=>$assignid));

        $cm = local_gugcat::get_activities($this->course->id);
        $key = key($cm);
        $this->cm = $cm[$key];
        $modinfo = get_fast_modinfo($this->course);
        $cm_info = $modinfo->get_cm($this->cm->id);
        $this->assign = new assign(context_module::instance($cm_info->id), $cm_info, $this->course->id);
        local_gugcat::$STUDENTS = $this->students;
        $this->provisionalgi = local_gugcat::add_grade_item($this->course->id, get_string('provisionalgrd', 'local_gugcat'), $this->cm);
    }

    public function test_grade_aggregation_rows() {
        global $DB;
        //add grades to students so  aggregation weight in grade_grades
        $gradeitemid = $this->cm->gradeitem->id;
        $user1 = $this->student1->id;
        $user2 = $this->student2->id;
        //student provisonal grades
        $s1grd =  5;
        $s2grd =  10;
        //expected grades
        $exp_s1grd = '5.00000';
        $exp_s2grd =  '10.00000';

        $data = [];
        $data[] = [//1st student grade for main activity
            'itemid' => $gradeitemid,
            'userid' => $user1,
            'excluded' => 0,
            'rawgrade' => 17,
            'finalgrade' => 17
        ];
        $data[] = [//2nd student grade for main activity 
            'itemid' => $gradeitemid,
            'userid' => $user2,
            'excluded' => 0,
            'rawgrade' => 22,
            'finalgrade' => 22
        ];
        $DB->insert_records('grade_grades', $data);
        foreach ($this->students as $student) {
            $grade_ = new grade_grade(array('userid' => $student->id, 'itemid' => $this->provisionalgi), true);
            $grade_->information = '1.00000';
            $grade_->rawgrade = ($student->id != $this->student1->id) ? $s2grd : $s1grd;
            $grade_->finalgrade = ($student->id != $this->student1->id) ? $s2grd : $s1grd;
            $grade_->update();  
        }
        $modules = array($this->cm);
        $rows = grade_aggregation::get_rows($this->course, $modules, $this->students);
        //get the weight of the main activity grades
        $gg1 = new grade_grade(array('userid'=>$this->student1->id, 'itemid'=>$gradeitemid), true); //1st student 
        $gg2 = new grade_grade(array('userid'=>$this->student1->id, 'itemid'=>$gradeitemid), true); //2nd student 
        $exp_aggregatedgrd1 = (float)$exp_s1grd * (float)$gg1->get_aggregationweight();
        $exp_aggregatedgrd2 = (float)$exp_s2grd * (float)$gg2->get_aggregationweight();
        $expectedcompleted = "100%"; //expected completed percent since there's only one activity
        $this->assertCount(2, $rows);
        //assert each rows that it has the provisional grade
        $row1 = $rows[1];
        $this->assertEquals($row1->cnum, 2);
        $this->assertEquals($row1->studentno, $this->student1->id);
        $this->assertEquals('F2', $row1->grades[0]->grade);
        $this->assertEquals($row1->completed, $expectedcompleted); //assert complete percent
        $this->assertEquals('F2', $row1->aggregatedgrade->grade); //assert aggregated grade 
        $row2 = $rows[0];
        $this->assertEquals($row2->cnum, 1);
        $this->assertEquals($row2->studentno, $this->student2->id);
        $this->assertEquals('D3', $row2->grades[0]->grade);
        $this->assertEquals($row2->completed, $expectedcompleted);
        $this->assertEquals('D3', $row2->aggregatedgrade->grade);
    }

    public function test_adjust_course_weight() {
        $expectedweight = 30;
        $weights = array();
        $weights[$this->cm->gradeitemid] = $expectedweight;
        $grade_ = new grade_grade(array('userid' => $this->student1->id, 'itemid' => $this->provisionalgi), true);
        $grade_->information = '1.00000';
        $grade_->rawgrade = 20;
        $grade_->finalgrade = 20;
        $grade_->update();  
        grade_aggregation::adjust_course_weight($weights, $this->course->id, $this->student1->id, null);
        $rows = grade_aggregation::get_rows($this->course, array($this->cm), array($this->student1));
        $student = $rows[0];
        $this->assertEquals($expectedweight, $student->grades[0]->weight);
        $this->assertEquals("$expectedweight%", $student->completed);
    }

    public function test_require_resit() {
        global $DB;
        $DB->insert_record('grade_grades', array(
            'itemid' => $this->cm->gradeitem->id,
            'userid' => $this->student1->id
        ));
        $student = array($this->student1);
        $modules = array($this->cm);
        $rows = grade_aggregation::get_rows($this->course, $modules, $student);
        $this->assertNull($rows[0]->resit);

        grade_aggregation::require_resit($this->student1->id);
        $resitRows = grade_aggregation::get_rows($this->course, $modules, $student);
        $match = preg_match('/\b0/i', $resitRows[0]->resit);
        $this->assertEquals($match, 1);
    }

    public function test_override_grade() {
        global $DB;
        $modules = array($this->cm);
        $grade_ = new grade_grade(array('userid' => $this->student1->id, 'itemid' => $this->provisionalgi), true);
        $grade_->information = '1.00000';
        $grade_->rawgrade = 20;
        $grade_->finalgrade = 20;
        $grade_->update();  
        $student = array($this->student1);
        $rows = grade_aggregation::get_rows($this->course, $modules, $student);
        $this->assertNotNull($rows[0]->aggregatedgrade->rawgrade);

        $aggradeitem = local_gugcat::add_grade_item($this->course->id, get_string('aggregatedgrade', 'local_gugcat'), null);
        $expectednotes = 'testnote';
        $defaultoverridden = 0;
        local_gugcat::update_grade($this->student1->id, $aggradeitem, 19, $expectednotes, null, time());
        $rows = grade_aggregation::get_rows($this->course, $modules, $student);
        $aggrade = $DB->get_record('grade_grades', array('userid'=>$this->student1->id, 'itemid'=>$aggradeitem));
        $this->assertEquals($rows[0]->aggregatedgrade->rawgrade, '19.00000');
        $this->assertEquals($expectednotes, $aggrade->feedback);
        $this->assertNotEquals($defaultoverridden, $aggrade->overridden);
    }

    public function test_release_final_grades() {
        $expectedgrade = '10.00000';
        $cms = array();
        $cms[$this->cm->gradeitemid] = $this->cm->instance."_".$this->cm->name;
        $students = array();
        $students[$this->student1->id][$this->cm->gradeitemid] = 10;
        $gradeitemid = $this->cm->gradeitem->id;
        grade_aggregation::release_final_grades($this->course->id, $cms, $students);
        $gg = new grade_grade(array('userid' => $this->student1->id, 'itemid' => $gradeitemid), true);
        $this->assertEquals($this->student1->id, $gg->userid);//assert updated user = student 1
        $this->assertEquals($expectedgrade, $gg->finalgrade);//assert finalgrade = 10.00000
        $this->assertEquals('final', $gg->information); //assert information = final
    }

    public function test_get_course_grade_history(){
        global $DB;
        $modules = array($this->cm);
        $aggradeitem = local_gugcat::add_grade_item($this->course->id, get_string('aggregatedgrade', 'local_gugcat'), null);
        $expectednotes = 'testnote';
        $defaultoverridden = 0;
        local_gugcat::update_grade($this->student1->id, $aggradeitem, 19, $expectednotes, null, time());

        $gradehistory = grade_aggregation::get_course_grade_history($this->course, $modules, $this->student1);
        $this->assertEquals($gradehistory[0]->grade, 'A5');
        $this->assertEquals($gradehistory[0]->notes, $expectednotes);
    }
}
