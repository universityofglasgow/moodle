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

class local_gugcat_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest();
        global $DB;

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

        $DB->insert_record('grade_grades', array(
            'itemid' => $this->gradeitem->id,
            'userid' => $this->student->id
        ));

        $DB->insert_record('user', array(
            'id' => '0',
            'firstname' => 'admin'
        ));
    }

    public function test_filter_grade_version(){
        global $DB;

        $course = $DB->get_record('course', ['id' => $this->course->id], '*', MUST_EXIST);
        $gradeitems = local_gugcat::get_grade_grade_items($course, $this->cm);
        $gradeversions = local_gugcat::filter_grade_version($gradeitems, $this->student->id);

        foreach($gradeitems as $gradeitem){
            $arrayofgradeitem = (array)$gradeitem;

            $this->assertContains($this->gradeitem->courseid, $arrayofgradeitem);
            $this->assertContains($this->gradeitem->iteminfo, $arrayofgradeitem);
        }
        
        $this->assertNotContains($this->provisionalgi, $gradeversions);
    }

    public function test_get_grade_grade_items(){
        global $DB;

        $course = $DB->get_record('course', ['id' => $this->course->id], '*', MUST_EXIST);
        $gradeitems = local_gugcat::get_grade_grade_items($course, $this->cm);
        
        $this->assertArrayHasKey($this->gradeitem->id, (array)$gradeitems);
        $this->assertCount(2, $gradeitems);
    }   

    public function test_get_grade_categories() {
        //create grade categories
        $gen = $this->getDataGenerator();
        $cid = $this->course->id;
        $gc1a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc1b = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2c = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);

        $categories = local_gugcat::get_grade_categories($this->course->id);
        //check if uncategorised is included
        $this->assertCount(4, $categories);
        //check category ids
        $this->assertArrayHasKey($gc1a->id, $categories);
        $this->assertArrayHasKey($gc1b->id, $categories);
        $this->assertArrayHasKey($gc2c->id, $categories);
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

    public function test_check_prv_grade_item() {
        $prvgradeid = $this->provisionalgi->id;
        local_gugcat::set_prv_grade_id($this->course->id, $this->cm);
        $this->assertEquals(local_gugcat::$PRVGRADEID, $prvgradeid);
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

    public function test_add_grade_item(){
        $sndgradestr = get_string('gi_secondgrade', 'local_gugcat');
        $gradeitemid = local_gugcat::add_grade_item($this->course->id, $sndgradestr, $this->cm);
        $id = local_gugcat::get_grade_item_id($this->course->id, $this->cm->id, $sndgradestr);
        $this->assertEquals($gradeitemid, $id);
    }

    public function test_add_grade(){
        global $gradeitems, $prvgradeid;
        $gradeitems = array();
        $expectedgrade = '5.00000';
        $prvgradeid = $this->provisionalgi->id;
        grade_capture::get_rows($this->course, $this->cm, $this->students);
        $mggradeitemstr = get_string('moodlegrade', 'local_gugcat');
        $mggradeitem = local_gugcat::add_grade_item($this->course->id, $mggradeitemstr, $this->cm); 
        local_gugcat::add_update_grades($this->student->id, $mggradeitem, $expectedgrade);
        $firstrows = grade_capture::get_rows($this->course, $this->cm, $this->students);
        $firstrow = $firstrows[0];
        $this->assertEquals($firstrow->firstgrade, $expectedgrade);
    }

    public function test_add_grade_notes(){
        global $gradeitems, $prvgradeid, $DB;
        $gradeitems = array();
        $expectednotes = 'testnote';
        $prvgradeid = $this->provisionalgi->id;
        grade_capture::get_rows($this->course, $this->cm, $this->students);
        $mggradeitemstr = get_string('moodlegrade', 'local_gugcat');
        $mggradeitem = local_gugcat::add_grade_item($this->course->id, $mggradeitemstr, $this->cm); 
        local_gugcat::add_update_grades($this->student->id, $mggradeitem, '5.00000', $expectednotes);
        $notes = $DB->get_field('grade_grades', 'feedback', array('userid'=>$this->student->id, 'itemid'=>$mggradeitem));
        $this->assertEquals($notes, $expectednotes);
    }

    public function test_add_grade_document(){
        global $gradeitems, $DB;
        $gradeitems = array();
        $documentitemid = '524106396';
        $prvgradeid = $this->provisionalgi->id;
        grade_capture::get_rows($this->course, $this->cm, $this->students);
        $mggradeitemstr = get_string('moodlegrade', 'local_gugcat');
        $mggradeitem = local_gugcat::add_grade_item($this->course->id, $mggradeitemstr, $this->cm); 
        local_gugcat::add_update_grades($this->student->id, $mggradeitem, '5.00000', null, $documentitemid);
        $notes = $DB->get_field('grade_grades', 'information', array('userid'=>$this->student->id, 'itemid'=>$mggradeitem));
        $this->assertEquals($notes, $documentitemid);
    }

    public function test_get_grade_history(){
        global $gradeitems, $DB;
        $gradeitems = array();
        $documentitemid1 = '524106396';
        $notesitemid1 = 'Test notes';
        grade_capture::get_rows($this->course, $this->cm, $this->students);
        $mggradeitemstr = get_string('moodlegrade', 'local_gugcat');
        $mggradeitem = local_gugcat::add_grade_item($this->course->id, $mggradeitemstr, $this->cm); 
        local_gugcat::add_update_grades($this->student->id, $mggradeitem, '5.00000', $notesitemid1, $documentitemid1);
        $DB->set_field_select(GRADE_GRADES, 'usermodified', $this->teacher->id, "itemid = ".$mggradeitem." AND userid = ".$this->student->id);
        $sndgrditemstr = get_string('gi_secondgrade', 'local_gugcat');    
        $expectednotes = 'N/A - '.$sndgrditemstr;
        $sndgradeitem = local_gugcat::add_grade_item($this->course->id, $sndgrditemstr, $this->cm); 
        local_gugcat::add_update_grades($this->student->id, $sndgradeitem, '21.00000', null, null);
        $DB->set_field_select(GRADE_GRADES, 'usermodified', $this->teacher->id, "itemid = ".$sndgradeitem ." AND userid = ".$this->student->id);
        grade_capture::get_rows($this->course, $this->cm, $this->students);
        $gradehistory = local_gugcat::get_grade_history($this->course->id, $this->cm, $this->student->id);
        $type = preg_replace('/<br>.*/i', '', $gradehistory[0]->type);
        $this->assertEquals($mggradeitemstr, $type);
        $this->assertEquals($notesitemid1, $gradehistory[0]->notes);
        $this->assertEquals($sndgrditemstr, $gradehistory[1]->type);
        $this->assertEquals($expectednotes, $gradehistory[1]->notes);
    }
}
