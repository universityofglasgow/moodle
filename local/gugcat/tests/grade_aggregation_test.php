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

class grade_aggregation_testcase extends advanced_testcase {

    public function setUp() {
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
        $this->students = get_enrolled_users($this->coursecontext, 'mod/coursework:submit');
        $gen->create_module('assign', array('id' => 1, 'course' => $this->course->id));

        $cm = local_gugcat::get_activities($this->course->id);
        $key = key($cm);
        $this->cm = $cm[$key];
        $modinfo = get_fast_modinfo($this->course);
        $cm_info = $modinfo->get_cm($this->cm->id);
        $this->assign = new assign(context_module::instance($cm_info->id), $cm_info, $this->course->id);
    }

    public function test_grade_aggregation_rows() {
        global $DB;
        $gen = $this->getDataGenerator();
        //create provisional grade item
        $this->provisionalgi = new grade_item($gen->create_grade_item([
            'courseid' => $this->course->id, 
            'iteminfo' => $this->cm->id, 
            'itemname' => get_string('provisionalgrd', 'local_gugcat')
            ]), false);
        // Give a prv grade to the students.
        // $this->provisionalgi->update_final_grade($this->student1->id, 5);
        // $this->provisionalgi->update_final_grade($this->student2->id, 10);

        //add grades to students so  aggregation weight in grade_grades
        $gradeitemid = $this->cm->gradeitem->id;
        $user1 = $this->student1->id;
        $user2 = $this->student2->id;
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
        $data[] = [//1st student grade for provisional grade
            'itemid' => $this->provisionalgi->id,
            'userid' => $user1,
            'excluded' => 1,
            'rawgrade' => 5,
            'finalgrade' => 5
        ];
        $data[] = [//2nd student grade for provisional grade
            'itemid' => $this->provisionalgi->id,
            'userid' => $user2,
            'excluded' => 1,
            'rawgrade' => 10,
            'finalgrade' => 10
        ];
        $DB->insert_records('grade_grades', $data);
        
        $modules = array($this->cm);
        $rows = grade_aggregation::get_rows($this->course, $modules, $this->students);
        $expectedcompleted = "100%"; //expected completed percent since there's only one activity
        $this->assertCount(2, $rows);
        //assert each rows that it has the provisional grade
        $row1 = $rows[1];
        $this->assertEquals($row1->cnum, 2);
        $this->assertEquals($row1->studentno, $this->student2->id);
        $this->assertContains('10.00000', $row1->grades);
        $this->assertEquals($row1->completed, $expectedcompleted);
        $row2 = $rows[0];
        $this->assertEquals($row2->cnum, 1);
        $this->assertEquals($row2->studentno, $this->student1->id);
        $this->assertContains('5.00000', $row2->grades);
        $this->assertEquals($row2->completed, $expectedcompleted);

    }

}
