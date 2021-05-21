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

use local_gugcat\api;
use local_gugcat\grade_capture;
use local_gugcat\grade_converter;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/gugcat/lib.php');
require_once($CFG->dirroot.'/local/gugcat/locallib.php');

class grade_converter_testcase extends advanced_testcase {

    public function setUp() {
        global $DB, $COURSE;
        $this->resetAfterTest();

        $gen = $this->getDataGenerator();
        $this->student1 = $gen->create_user();
        $this->student2 = $gen->create_user();
        $this->teacher = $gen->create_user();
        $this->admin = get_admin();
        $this->course = $gen->create_course();
        $COURSE = $this->course;
        $this->coursecontext = context_course::instance($this->course->id);
        $gen->enrol_user($this->student1->id, $this->course->id, 'student');
        $gen->enrol_user($this->student2->id, $this->course->id, 'student');
        $gen->enrol_user($this->teacher->id, $this->course->id, 'editingteacher');
        $this->students = get_enrolled_users($this->coursecontext, 'local/gugcat:gradable');
        $this->assign1 = $gen->create_module('assign', array('id' => 1, 'course' => $this->course->id));
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'grademax', '22.00000', array('id'=>$assignid));

        $cm = local_gugcat::get_activities($this->course->id);
        $key = key($cm);
        $this->cm = $cm[$key];
        $modinfo = get_fast_modinfo($this->course);
        $cm_info = $modinfo->get_cm($this->cm->id);
        $this->assign = new assign(context_module::instance($cm_info->id), $cm_info, $this->course->id);
        local_gugcat::$students = $this->students;
        grade_capture::import_from_gradebook($this->course->id, $this->cm, $cm);
        $this->provisionalgi = local_gugcat::add_grade_item($this->course->id, get_string('provisionalgrd', 'local_gugcat'), $this->cm);
    }

    public function test_save_retrieve_delete_grade_conversion(){
        // Test save grade_conversion
        $expectedlb = '93';
        $expectedgrade = '23'; 
        $modid = $this->cm->gradeitem->id;
        $grdconvert = array(['courseid'=>$this->course->id, 'itemid'=>$modid, 'lowerboundary'=>$expectedlb, 'grade'=>$expectedgrade]);
        grade_converter::save_grade_conversion($grdconvert, $modid, '1');

        // Test retrieve grade_conversion
        $gradeconvert = grade_converter::retrieve_grade_conversion($modid);
        $this->assertNotEmpty($gradeconvert);
        $gc = $gradeconvert[key($gradeconvert)];
        $this->assertEquals($gc->lowerboundary, "$expectedlb.00000");
        $this->assertEquals($gc->grade, $expectedgrade);

        // Test delete grade_conversion
        grade_converter::delete_grade_conversion($modid);
        $gradeconvert = grade_converter::retrieve_grade_conversion($modid);
        $this->assertEmpty($gradeconvert);
    }

    public function test_process_defaults(){
        // Get schedule b
        local_gugcat::set_grade_scale();
        $defaultvalue = array(); // Create a grade conversion like retrieve from db
        $item1 = new stdClass();
        $item1->lowerboundary = 90;
        $item1->grade = 18;
        $defaultvalue[] = $item1;
        $item2 = new stdClass();
        $item2->lowerboundary = 70;
        $item2->grade = 15;
        $defaultvalue[] = $item2;
        // Assert defaultscale is false, returns null lowerboundary of schedule B
        $return = grade_converter::process_defaults(false, local_gugcat::$scheduleb, $defaultvalue);
        $schedBkeys = array_keys(local_gugcat::$scheduleb);
        foreach ($return as $key => $grade) {
            // Assert key is in schedule b keys array
            $this->assertContains($key, $schedBkeys);
            // Assert same alphanumeric from schedule b
            $this->assertEquals($grade->grade, local_gugcat::$scheduleb[$key]);
            // Assert lowerboundary is null
            $this->assertNull($grade->lowerboundary);
        }

        // Assert defaultscale is true, returns the $defaultvalue from db to lowerboundary of schedule B
        $return = grade_converter::process_defaults(true, local_gugcat::$scheduleb, $defaultvalue);

        $keys = array_keys($return);
        // Assert grade 18 from returned array
        $this->assertContains($item1->grade, $keys);
        // Assert grade 15 from returned array
        $this->assertContains($item2->grade, $keys);

        // Assert lowerboundary 90 is already set to grade 18
        $this->assertEquals($item1->lowerboundary, $return[$item1->grade]->lowerboundary);
        // Assert lowerboundary 70 is already set to grade 15
        $this->assertEquals($item2->lowerboundary, $return[$item2->grade]->lowerboundary);
    }

    public function test_convert_provisional_grades(){
        global $DB;
        // Create provisional grades
        $prvid = $this->provisionalgi;
        $stud1grd = 89;
        $stud2grd = 70;
        // Update provisional grade of the students
        local_gugcat::update_grade($this->student1->id, $prvid, $stud1grd);
        local_gugcat::update_grade($this->student2->id, $prvid, $stud2grd);

        // Assert provisional grades are save
        $grade1 = $DB->get_field('grade_grades', 'finalgrade', array('itemid' => $prvid, 'userid' => $this->student1->id));
        $this->assertEquals($grade1, $stud1grd);
        $grade2 = $DB->get_field('grade_grades', 'finalgrade', array('itemid' => $prvid, 'userid' => $this->student2->id));
        $this->assertEquals($grade2, $stud2grd);

        // Create a conversion
        $conversion = array();
        // Schedule A
        // 23 => A1 => 90
        // 22 => A2 => 85
        // 21 => A3 => 80
        // 20 => A4 => <80
        $A1 = new stdClass();
        $A1->lowerboundary = 90;
        $A1->grade = 23;
        $conversion[] = $A1;
        $A2 = new stdClass();
        $A2->lowerboundary = 85;
        $A2->grade = 22;
        $conversion[] = $A2;
        $A3 = new stdClass();
        $A3->lowerboundary = 80;
        $A3->grade = 21;
        $conversion[] = $A3;
        grade_converter::convert_provisional_grades($conversion, $this->cm, $prvid);

         // Assert converted provisional grades 
         $grade1 = $DB->get_field('grade_grades', 'finalgrade', array('itemid' => $prvid, 'userid' => $this->student1->id));
         //stud1grd = 89 ===> 22
         $this->assertEquals($grade1, 22);
         $grade2 = $DB->get_field('grade_grades', 'finalgrade', array('itemid' => $prvid, 'userid' => $this->student2->id));
         //stud2grd = 70 ===> 20
         $this->assertEquals($grade2, 20);

        // Assert converted grade item is created
        $cnvgi = local_gugcat::get_grade_item_id($this->course->id, $this->cm->gradeitemid, get_string('convertedgrade', 'local_gugcat'));
        $this->assertNotFalse($cnvgi);

        // Assert converted grade grades contains the original grades (grades in points)
        $grade1 = $DB->get_field('grade_grades', 'finalgrade', array('itemid' => $cnvgi, 'userid' => $this->student1->id));
        //stud1grd = 89 
        $this->assertEquals($grade1, $stud1grd);
        $grade2 = $DB->get_field('grade_grades', 'finalgrade', array('itemid' => $cnvgi, 'userid' => $this->student2->id));
        //stud2grd = 70
        $this->assertEquals($grade2, $stud2grd);
    }

    public function test_convert(){
        // Create a conversion
        $conversion = array();
        // Schedule A
        // 23 => A1 => 90
        // 22 => A2 => 85
        // 21 => A3 => 80
        // 20 => A4 => <80
        $A1 = new stdClass();
        $A1->lowerboundary = 90;
        $A1->grade = 23;
        $conversion[] = $A1;
        $A2 = new stdClass();
        $A2->lowerboundary = 85;
        $A2->grade = 22;
        $conversion[] = $A2;
        $A3 = new stdClass();
        $A3->lowerboundary = 80;
        $A3->grade = 21;
        $conversion[] = $A3;
        // Start conversion
        // Grade = 91 == converted = 23
        $convertedgraed = grade_converter::convert($conversion, 91);
        $this->assertEquals($convertedgraed, $A1->grade);
        // Grade = 89 == converted = 22
        $convertedgraed = grade_converter::convert($conversion, 89);
        $this->assertEquals($convertedgraed, $A2->grade);
        // Grade = 85 == converted = 22
        $convertedgraed = grade_converter::convert($conversion, 85);
        $this->assertEquals($convertedgraed, $A2->grade);
        // Grade = 83 == converted = 21
        $convertedgraed = grade_converter::convert($conversion, 83);
        $this->assertEquals($convertedgraed, $A3->grade);
        // Grade = 50 == converted = 20
        $convertedgraed = grade_converter::convert($conversion, 50);
        $this->assertEquals($convertedgraed, 20);

        // Test 22 pt with schedule B conversion
        local_gugcat::set_grade_scale();
        // 23 => A0
        $alphanumgrd = grade_converter::convert(local_gugcat::$scheduleb, 23);
        $this->assertEquals($alphanumgrd, 'A0');
        // 18 => B0
        $alphanumgrd = grade_converter::convert(local_gugcat::$scheduleb, 18);
        $this->assertEquals($alphanumgrd, 'B0');
        // 10 => E0
        $alphanumgrd = grade_converter::convert(local_gugcat::$scheduleb, 10);
        $this->assertEquals($alphanumgrd, 'E0');
        // 3 => G0
        $alphanumgrd = grade_converter::convert(local_gugcat::$scheduleb, 3);
        $this->assertEquals($alphanumgrd, 'G0');
        // 1 => H
        $alphanumgrd = grade_converter::convert(local_gugcat::$scheduleb, 1);
        $this->assertEquals($alphanumgrd, 'H');
    }

    public function test_save_retrieve_conversion_template(){
        global $USER;
        // Test save save_new_template
        $expectedtemplate = 'Template 1';
        $expectedscaletype = '1';
        grade_converter::save_new_template($expectedtemplate, $expectedscaletype);

        // Test retrieve grade_conversion
        $templates = grade_converter::get_conversion_templates();
        $this->assertNotEmpty($templates);
        $template = $templates[key($templates)];
        $this->assertEquals($template->userid, $USER->id);
        $this->assertEquals($template->templatename, $expectedtemplate);
        $this->assertEquals($template->scaletype, $expectedscaletype);
    }

    public function test_get_converter_template_data_api(){  
        global $USER;    
        $templateid = null;
        $templatedata = api::get_converter_template_data($templateid);
        // Assert null if no template yet
        $this->assertFalse($templatedata);

        // Save a new template using grade_converter::save_new_template
        $expectedtemplate = 'Template 1';
        $expectedscaletype = '1';
        $templateid = grade_converter::save_new_template($expectedtemplate, $expectedscaletype);

        // Save template grade_conversion
        $expectedlb = '93';
        $expectedgrade = '23'; 
        $grdconvert = array(['templateid'=>$templateid, 'lowerboundary'=>$expectedlb, 'grade'=>$expectedgrade]);
        grade_converter::save_grade_conversion($grdconvert);
    
        $templatedata = api::get_converter_template_data($templateid);

        // Assert return is json string
        $this->assertJson($templatedata);

        // Convert json string to object
        $template = json_decode($templatedata);
        $this->assertEquals($template->id, $templateid);
        $this->assertEquals($template->userid, $USER->id);
        $this->assertEquals($template->templatename, $expectedtemplate);
        $this->assertEquals($template->scaletype, $expectedscaletype);

        $conversion = reset($template->conversion);
        $this->assertEquals($conversion->lowerboundary, "$expectedlb.00000");
        $this->assertEquals($conversion->grade, $expectedgrade);
        $this->assertEquals($conversion->templateid, $templateid);
        $this->assertNull($conversion->itemid);
        $this->assertNull($conversion->courseid);
    }
    
    public function test_convert_point_percentage(){
        $maxgrade = 100;
        $grade = 80;
        $expectedpercent = 80;
        $percent = grade_converter::convert_point_percentage($maxgrade,$grade, false);
        $this->assertEquals($percent, $expectedpercent);

        $convertedgrade = grade_converter::convert_point_percentage($maxgrade, $percent);
        $this->assertEquals($convertedgrade, $grade);
    }
}
