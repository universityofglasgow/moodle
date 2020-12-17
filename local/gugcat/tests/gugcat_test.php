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
}
