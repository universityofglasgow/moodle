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
        $this->student = $gen->create_user(array('idnumber'=> 1));
        $this->student2 = $gen->create_user(array('idnumber'=> 2));
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

        // Multiple course modules to be imported
        global $DB, $USER;
        $gen = $this->getDataGenerator();
        $mod1 = $gen->create_module('assign', array('name'=> 'Assessment 1','course' => $this->course->id));
        $mod2 = $gen->create_module('assign', array('name'=> 'Assessment 2','course' => $this->course->id));
        $DB->set_field('grade_items', 'grademax', '22.00000', array('iteminstance'=>$mod1->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'grademax', '22.00000', array('iteminstance'=>$mod2->id, 'itemmodule' => 'assign'));

        // Adding invalid scale
        $name = "Non-22 Scale";
        $scale = "0,1,2";
        $non22scale = $this->getDataGenerator()->create_scale(array('name' => $name, 'scale' => $scale, 'courseid' => $this->course->id, 'userid' => $USER->id));

        // Creating invalid activity
        $invalidmod = $gen->create_module('assign', array('name'=> 'Invalid Assessment 1','course' => $this->course->id));
        $DB->set_field('grade_items', 'scaleid', $non22scale->id, array('iteminstance'=>$invalidmod->id, 'itemmodule' => 'assign'));

        $gradeitem = $DB->get_record('grade_items', array('iteminstance'=>$invalidmod->id, 'itemmodule' => 'assign'), 'id');

        $gcatactivities = local_gugcat::get_activities($this->course->id);
        // Assert $gcatactivities is 4 as we added 1 new invalid assessment
        $this->assertCount(4, $gcatactivities);

        // Filter activities
        $gcatactivities = array_filter($gcatactivities, function($k){
            $scaleid = $k->gradeitem->scaleid;
            $gradetype = $k->gradeitem->gradetype;
            $grademax = $k->gradeitem->grademax;
            $grademin = $k->gradeitem->grademin;
            $invalid_import_activity = (is_null($scaleid) ? !local_gugcat::is_validgradepoint($gradetype, $grademin)
                                                          : !local_gugcat::is_scheduleAscale($gradetype, $grademax));
            return !$invalid_import_activity;
        }, ARRAY_FILTER_USE_BOTH);
        
        // Assert $gcatactivities is 3 as we filtered 1 invalid assessment
        $this->assertCount(3, $gcatactivities);

        // Import filtered activities
        grade_capture::import_from_gradebook($this->course->id, $gcatactivities, $gcatactivities);

        foreach ($gcatactivities as $act) {
            $moodlegi = grade_item::fetch(array('iteminfo'=>$act->gradeitemid, 'itemtype' => 'manual', 'itemname' => get_string('moodlegrade', 'local_gugcat')));
            $prvgi = grade_item::fetch(array('iteminfo'=>$act->gradeitemid, 'itemtype' => 'manual', 'itemname' => get_string('provisionalgrd', 'local_gugcat')));
            if ($act->instance == $invalidmod->id){
                $this->assertFalse($moodlegi);
                $this->assertFalse($prvgi);
            } else {
                $this->assertNotFalse($moodlegi);
                $this->assertNotFalse($prvgi);
            }
        }
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

    public function test_get_component_module(){
        global $DB;
        $gen = $this->getDataGenerator();
        $cid = $this->course->id;
        $gc1a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a->depth = 3;
        $gc2a->path = $gc1a->path.$gc2a->id.'/';
        $gc2a->parent = $gc1a->id;
        $gc2a->update();
        $categorygi = $DB->get_field('grade_items', 'id', array('courseid'=> $this->course->id, 'itemtype'=>'category', 'iteminstance'=>$gc2a->id));
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'categoryid', $categorygi, array('id'=>$assignid));
        $cm = local_gugcat::get_activities($cid, $gc1a->id);
        $categoract = grade_category::fetch_all(array('courseid' => $cid, 'parent' => $gc1a->id));
        $gradecatgi = array();
        foreach ($categoract as $gc){
            $gi = local_gugcat::get_category_gradeitem($cid, $gc);
            $gi->name = preg_replace('/\b total/i', '', $gi->name);
            $gradecatgi[$gi->gradeitemid] = $gi; 
        }
        $mods = key($gradecatgi);
        $selectedmodid = $gradecatgi[$mods]->gradeitemid;
        $childactivities = local_gugcat::get_activities($cid, $selectedmodid);
        $childmods = key($childactivities);
        $selectedmodule = $childactivities[$childmods];
        $this->assertEquals($selectedmodule->gradeitem->itemname, $this->cm->gradeitem->itemname);
        $this->assertEquals($selectedmodule->gradeitem->itemtype, $this->cm->gradeitem->itemtype);
        $this->assertEquals($selectedmodule->gradeitem->itemmodule, $this->cm->gradeitem->itemmodule);
        $this->assertNotEquals($selectedmodule->gradeitem->parent_category, $this->cm->gradeitem->parent_category);
    }

    public function test_create_subcategory_gradeitem(){
        global $DB;
        $gen = $this->getDataGenerator();
        $cid = $this->course->id;
        $gc1a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a->depth = 3;
        $gc2a->path = $gc1a->path.$gc2a->id.'/';
        $gc2a->parent = $gc1a->id;
        $gc2a->update();
        $categorygi = $DB->get_field('grade_items', 'id', array('courseid'=> $this->course->id, 'itemtype'=>'category', 'iteminstance'=>$gc2a->id));
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'categoryid', $categorygi, array('id'=>$assignid));
        $cm = local_gugcat::get_activities($cid, $gc1a->id);
        $categoract = grade_category::fetch_all(array('courseid' => $cid, 'parent' => $gc1a->id));
        $gradecatgi = array();
        foreach ($categoract as $gc){
            $gi = local_gugcat::get_category_gradeitem($cid, $gc);
            $gradecatgi[$gi->gradeitemid] = $gi; 
        }
        $mods = key($gradecatgi);
        $selectedmodid = $gradecatgi[$mods]->gradeitemid;
        $childactivities = local_gugcat::get_activities($cid, $selectedmodid);
        $totalactivities = $childactivities + $gradecatgi;
        grade_capture::set_provisional_weights($cid, $totalactivities, $this->students);
        $subcatstr = get_string('subcategorygrade', 'local_gugcat');
        $subcatgi = $DB->get_record('grade_items', array('itemtype'=>'manual', 'itemname'=>$subcatstr));
        $this->assertNotFalse($subcatgi);
    }

    public function test_prepare_import_data(){
        global $CFG, $COURSE;
        $COURSE = $this->course;
        require_once($CFG->libdir . '/csvlib.class.php');

        $id1 = $this->student->idnumber;
        $id2 = $this->student2->idnumber;

        $module = $this->cm;
        $gradereason = get_string('gi_goodcause', 'local_gugcat');
        // Prepare scale
        local_gugcat::set_grade_scale(null);

        //Populate static provisional grade id
        local_gugcat::set_prv_grade_id($this->course->id, $module);

        // Schedule A import
        $module->gradeitem->gradetype = GRADE_TYPE_SCALE;

        $content1 = array(
            "ID Number,Grades",
            ",", //- Empty line will skip the validation
            "$id1,A1",
            "$id2,A2",
        );
        $csvimport = new gradeimport_csv_load_data();
        $csvimport->load_csv_content(implode("\n", $content1), 'UTF-8', 'comma', 0);
        $csvimportdata = new csv_import_reader($csvimport->get_iid(), 'grade');
        
        // Success import of grade 1 => A1, 2 => A2
        // Assert status = true and errors = 0
        list($status, $errors) = grade_capture::prepare_import_data($csvimportdata, $module, $gradereason);
        $this->assertTrue($status);
        $this->assertEmpty($errors);
        unset($csvimportdata);
        //-------- Start asserting errors ----
        $errorobj = new stdClass();
        
        // ---- Assert student not enrolled in current course
        $content2 = array(
            "ID Number,Grades",
            "5,A1",
            "$id2,A2",
        );
        $csvimport->load_csv_content(implode("\n", $content2), 'UTF-8', 'comma', 0);
        $csvimportdata = new csv_import_reader($csvimport->get_iid(), 'grade');
        $errorobj->id = 5;
        $errorobj->value = 'A1';
        // Failed import of grade 5 => A1, 2 => A2
        // Assert status = false and errors = 1
        list($status, $errors) = grade_capture::prepare_import_data($csvimportdata, $module, $gradereason);
        $this->assertFalse($status);
        // Error = User mapping error. Could not find user with ID number of {$a->id}.
        $this->assertContains(get_string('uploaderrornotfound', 'local_gugcat', $errorobj), $errors);
        unset($csvimportdata);

        // ---- Assert student not in the current group
        
        // Create grouping and group first, then add student 1 to the group
        $grouping = $this->getDataGenerator()->create_grouping(array('courseid' => $this->course->id));
        $group = self::getDataGenerator()->create_group(array('courseid' => $this->course->id));
        groups_assign_grouping($grouping->id, $group->id);
        groups_add_member($group->id, $this->student->id);
        // Add grouping id on the module
        $module->groupingid = $grouping->id;
        $csvimport = new gradeimport_csv_load_data();
        $csvimport->load_csv_content(implode("\n", $content1), 'UTF-8', 'comma', 0);
        $csvimportdata = new csv_import_reader($csvimport->get_iid(), 'grade');
        $errorobj->id = 2; // Student id 2 is not a member of the current group
        $errorobj->value = 'A2';
        // Failed import of grade 1 => A1 (grouped), 2 => A2
        // Assert status = false and errors = 1
        list($status, $errors) = grade_capture::prepare_import_data($csvimportdata, $module, $gradereason);
        $this->assertFalse($status);
        // Error = User with ID number of {$a->id} is not a member of current group.
        $this->assertContains(get_string('uploaderrornotmember', 'local_gugcat', $errorobj), $errors);
        unset($csvimportdata);

        $module->groupingid = 0;

        // ---- Assert grade in scale is not alphanumeric
        $content3 = array(
            "ID Number,Grades",
            "$id1,AA"
        );
        $csvimport->load_csv_content(implode("\n", $content3), 'UTF-8', 'comma', 0);
        $csvimportdata = new csv_import_reader($csvimport->get_iid(), 'grade');
        $errorobj->id = $id1;
        $errorobj->value = 'AA';
        // Failed import of grade 1 => AA
        // Assert status = false and errors = 1
        list($status, $errors) = grade_capture::prepare_import_data($csvimportdata, $module, $gradereason);
        $this->assertFalse($status);
        // Error = User with ID number of {$a->id} has an invalid grade: {$a->value}. Grades must be in alphanumeric format.
        $this->assertContains(get_string('uploaderrorgradeformat', 'local_gugcat', $errorobj), $errors);
        unset($csvimportdata);

        // ---- Assert grade in scale is alphanumeric but not within the scale
        $content3 = array(
            "ID Number,Grades",
            "$id1,Z0"
        );
        $csvimport->load_csv_content(implode("\n", $content3), 'UTF-8', 'comma', 0);
        $csvimportdata = new csv_import_reader($csvimport->get_iid(), 'grade');
        $errorobj->id = $id1;
        $errorobj->value = 'Z0';
        // Failed import of grade 1 => Z0
        // Assert status = false and errors = 1
        list($status, $errors) = grade_capture::prepare_import_data($csvimportdata, $module, $gradereason);
        $this->assertFalse($status);
        // Error = User with ID number of {$a->id} has an invalid grade: {$a->value}. Grade is not within the scale.
        $this->assertContains(get_string('uploaderrorgradescale', 'local_gugcat', $errorobj), $errors);
        unset($csvimportdata);

        // Change to module gradetype to point
        $module->gradeitem->gradetype = GRADE_TYPE_VALUE;
        $module->gradeitem->grademax = 10;
        
        // ---- Assert grade in points is greater than grademax
        $content4 = array(
            "ID Number,Grades",
            "$id1,100"
        );
        $csvimport->load_csv_content(implode("\n", $content4), 'UTF-8', 'comma', 0);
        $csvimportdata = new csv_import_reader($csvimport->get_iid(), 'grade');
        $errorobj->id = $id1;
        $errorobj->value = '100';
        // Failed import of grade 1 => 100 > grademax = 10
        // Assert status = false and errors = 1
        list($status, $errors) = grade_capture::prepare_import_data($csvimportdata, $module, $gradereason);
        $this->assertFalse($status);
        // Error = User with ID number of {$a->id} has an invalid grade: {$a->value}. Grade is greater than the maximum grade.
        $this->assertContains(get_string('uploaderrorgrademaxpoint', 'local_gugcat', $errorobj), $errors);
        unset($csvimportdata);
        
        // ---- Assert grade in points is valid
        $csvimport->load_csv_content(implode("\n", $content3), 'UTF-8', 'comma', 0);
        $csvimportdata = new csv_import_reader($csvimport->get_iid(), 'grade');
        $errorobj->id = $id1;
        $errorobj->value = 'Z0';
        // Failed import of grade 1 => Z0
        // Assert status = false and errors = 1
        list($status, $errors) = grade_capture::prepare_import_data($csvimportdata, $module, $gradereason);
        $this->assertFalse($status);
        // Error = User with ID number of {$a->id} has an invalid grade: {$a->value}. Grade is an invalid point grade.
        $this->assertContains(get_string('uploaderrorgradepoint', 'local_gugcat', $errorobj), $errors);
        unset($csvimportdata);

        // ---- Assert success import for admin grades NS/MV
        $content5 = array(
            "ID Number,Grades",
            "$id1,NS",
            "$id2,MV"
        );
        $csvimport->load_csv_content(implode("\n", $content5), 'UTF-8', 'comma', 0);
        $csvimportdata = new csv_import_reader($csvimport->get_iid(), 'grade');
        // Assert status = true and errors = 0
        list($status, $errors) = grade_capture::prepare_import_data($csvimportdata, $module, $gradereason);
        $this->assertTrue($status);
        $this->assertEmpty($errors);
        unset($csvimportdata);
    }
}