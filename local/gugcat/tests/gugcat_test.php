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
require_once($CFG->dirroot . '/local/gugcat/lib.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');

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
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'grademax', '22.00000', array('id' => $assignid));
        $cm = local_gugcat::get_activities($this->course->id);
        $key = key($cm);
        $this->cm = $cm[$key];
        $modinfo = get_fast_modinfo($this->course);
        $cminfo = $modinfo->get_cm($this->cm->id);
        $this->assign = new assign(context_module::instance($cminfo->id), $cminfo, $this->course->id);

        // Create grade items.
        $this->gradeitem = new grade_item($gen->create_grade_item(['courseid' => $this->course->id,
         'iteminfo' => $this->cm->gradeitemid]), false);
        $this->provisionalgi = new grade_item($gen->create_grade_item([
            'courseid' => $this->course->id,
            'iteminfo' => $this->cm->gradeitemid,
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

    public function test_filter_grade_version() {
        global $DB;

        $course = $DB->get_record('course', ['id' => $this->course->id], '*', MUST_EXIST);
        $gradeitems = local_gugcat::get_grade_grade_items($course, $this->cm);
        $gradeversions = local_gugcat::filter_grade_version($gradeitems, $this->student->id);

        foreach ($gradeitems as $gradeitem) {
            $arrayofgradeitem = (array)$gradeitem;

            $this->assertContains($this->gradeitem->courseid, $arrayofgradeitem);
            $this->assertContains($this->gradeitem->iteminfo, $arrayofgradeitem);
        }

        $this->assertNotContains($this->provisionalgi, $gradeversions);
    }

    public function test_get_grade_grade_items() {
        $gradeitems = local_gugcat::get_grade_grade_items($this->course, $this->cm);
        $this->assertArrayHasKey($this->gradeitem->id, (array)$gradeitems);
        $this->assertCount(2, $gradeitems);
    }

    public function test_get_grade_categories() {
        // Create grade categories.
        $gen = $this->getDataGenerator();
        $cid = $this->course->id;
        $gc1a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc1b = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2c = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);

        $categories = local_gugcat::get_grade_categories($this->course->id);
        // Check if uncategorised is included.
        $this->assertCount(4, $categories);
        // Check category ids.
        $this->assertArrayHasKey($gc1a->id, $categories);
        $this->assertArrayHasKey($gc1b->id, $categories);
        $this->assertArrayHasKey($gc2c->id, $categories);
    }

    public function test_check_course_activities() {
        $activities = local_gugcat::get_activities($this->course->id);
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
        $this->assertEquals(local_gugcat::$prvgradeid, $prvgradeid);
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
            'iteminfo' => $this->cm->gradeitemid,
            'itemname' => $sndgradestr,
        ]), false);

        $id = local_gugcat::get_grade_item_id($this->course->id, $this->cm->gradeitemid, $sndgradestr);
        $this->assertEquals($id, $sndgradegi->id);
    }

    public function test_add_grade_item() {
        $sndgradestr = get_string('gi_secondgrade', 'local_gugcat');
        $gradeitemid = local_gugcat::add_grade_item($this->course->id, $sndgradestr, $this->cm);
        $id = local_gugcat::get_grade_item_id($this->course->id, $this->cm->gradeitemid, $sndgradestr);
        $this->assertEquals($gradeitemid, $id);
    }

    public function test_add_grade() {
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

    public function test_add_grade_notes() {
        global $gradeitems, $prvgradeid, $DB;
        $gradeitems = array();
        $expectednotes = 'testnote';
        $prvgradeid = $this->provisionalgi->id;
        grade_capture::get_rows($this->course, $this->cm, $this->students);
        $mggradeitemstr = get_string('moodlegrade', 'local_gugcat');
        $mggradeitem = local_gugcat::add_grade_item($this->course->id, $mggradeitemstr, $this->cm);
        local_gugcat::add_update_grades($this->student->id, $mggradeitem, '5.00000', $expectednotes);
        $notes = $DB->get_field('grade_grades', 'feedback', array('userid' => $this->student->id, 'itemid' => $mggradeitem));
        $this->assertEquals($notes, $expectednotes);
    }

    public function test_get_grade_history() {
        global $gradeitems, $DB;
        $gradeitems = array();
        $notesitemid1 = 'Test notes';
        grade_capture::get_rows($this->course, $this->cm, $this->students);
        $mggradeitemstr = get_string('moodlegrade', 'local_gugcat');
        $mggradeitem = local_gugcat::add_grade_item($this->course->id, $mggradeitemstr, $this->cm);
        local_gugcat::add_update_grades($this->student->id, $mggradeitem, '5.00000', $notesitemid1);
        $DB->set_field_select('grade_grades', 'usermodified', $this->teacher->id, "itemid = "
        . $mggradeitem . " AND userid = " . $this->student->id);
        $sndgrditemstr = get_string('gi_secondgrade', 'local_gugcat');
        $expectednotes = 'N/A - ' . $sndgrditemstr;
        $sndgradeitem = local_gugcat::add_grade_item($this->course->id, $sndgrditemstr, $this->cm);
        local_gugcat::add_update_grades($this->student->id, $sndgradeitem, '21.00000', null);
        $DB->set_field_select('grade_grades', 'usermodified', $this->teacher->id, "itemid = "
        . $sndgradeitem . " AND userid = " . $this->student->id);
        grade_capture::get_rows($this->course, $this->cm, $this->students);
        $gradehistory = local_gugcat::get_grade_history($this->course->id, $this->cm, $this->student->id);
        $type = preg_replace('/<br>.*/i', '', $gradehistory[0]->type);
        $this->assertEquals($mggradeitemstr, $type);
        $this->assertEquals($notesitemid1, $gradehistory[0]->notes);
        $this->assertEquals($sndgrditemstr, $gradehistory[1]->type);
        $this->assertEquals($expectednotes, $gradehistory[1]->notes);
    }

    public function test_blind_marking() {
        global $DB;
        // True for staff role, null module.
        $this->assertTrue(local_gugcat::is_blind_marking());

        // False for staff role, with module blind marking = 0.
        $this->assertFalse(local_gugcat::is_blind_marking($this->cm));

        // Enable blind marking.
        $assign = $this->assign;
        $instance = $assign->get_instance();
        $instance->instance = $instance->id;
        $instance->blindmarking = 1;
        $assign->update_instance($instance);
        // True for staff role, with module blind marking = 1.
        $this->assertTrue(local_gugcat::is_blind_marking($this->cm));

        // Check for Manager role.
        $manager = $this->getDataGenerator()->create_user();
        $context = context_system::instance();
        $managerroleid = $DB->get_field('role', 'id', array('shortname' => 'manager'));
        role_assign($managerroleid, $manager->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();
        $this->setUser($manager); // Set user as manager.

        // False for manager role regardless of course module.
        $this->assertFalse(local_gugcat::is_blind_marking($this->cm));
    }

    public function test_switch_display_of_assessments_on_student_dashboard_without_customfield_category() {
        global $DB;

        $contextid = $this->coursecontext->id;
        $instanceid = $this->course->id;

        $switchdisplay = local_gugcat::switch_display_of_assessment_on_student_dashboard($instanceid, $contextid);

        $this->assertEquals(1, $switchdisplay);
    }

    public function test_switch_display_of_assessments_on_student_dashboard_with_customfield_category_and_customfield_data() {
        global $DB;

        $contextid = $this->coursecontext->id;
        $instanceid = $this->course->id;

        $customfieldcategoryobj = self::default_custom_field_category_object();
        $customfieldcategoryid = $DB->insert_record('customfield_category', $customfieldcategoryobj);

        self::create_custom_field_field($customfieldcategoryid);

        $customfieldfield = $DB->get_record('customfield_field', array('categoryid' => $customfieldcategoryid));

        $customfieldddata = local_gugcat::default_contextfield_data_value($customfieldfield->id, $instanceid, $contextid);
        $customfielddata = $DB->insert_record('customfield_data', $customfieldddata);

        $switchdisplayoff = local_gugcat::switch_display_of_assessment_on_student_dashboard($instanceid, $contextid);

        $this->assertEquals(0, $switchdisplayoff);

        $switchdisplayon = local_gugcat::switch_display_of_assessment_on_student_dashboard($instanceid, $contextid);
        $this->assertEquals(1, $switchdisplayon);
    }

    public function test_get_value_of_customfield_checkbox() {
        global $DB;

        $contextid = $this->coursecontext->id;
        $instanceid = $this->course->id;

        $customfieldcategoryobj = self::default_custom_field_category_object();
        $customfieldcategoryid = $DB->insert_record('customfield_category', $customfieldcategoryobj);

        self::create_custom_field_field($customfieldcategoryid);

        $checkboxvalue = local_gugcat::get_value_of_customfield_checkbox($instanceid, $contextid);

        $this->assertEquals(1, $checkboxvalue);
    }

    public function test_get_activity() {
        // Test will include the function get_category_gradeitem($courseid, $gradecategory).
        $gen = $this->getDataGenerator();
        $cid = $this->course->id;
        // Create grade sub category.
        $gc = new grade_category($gen->create_grade_category(['courseid' => $cid, 'fullname' => 'Sub category']), false);
        // Create grade item of the sub category.
        $gcgi = new grade_item(['courseid' => $cid, 'itemtype' => 'category', 'categoryid' => null,
         'iteminstance' => $gc->id], true);
        $module = local_gugcat::get_activity($cid, $gcgi->id);

        // Get_activity will return gradeitem of sub category.
        $this->assertEquals($module->modname, 'category');
        $this->assertEquals($module->name, $gc->fullname . ' total');
        $this->assertEquals($module->gradeitemid, $gcgi->id);

        // Get_activity will return assessment data .
        $module = local_gugcat::get_activity($cid, $this->cm->gradeitemid);
        $this->assertEquals($module->modname, 'assign');
        $this->assertEquals($module->name, $this->cm->name);
        $this->assertEquals($module->gradeitemid, $this->cm->gradeitemid);
    }

    public function test_delete_gcat_items() {
        global $DB;
        $cid = $this->course->id;
        // Create an assessment first.
        $gen = $this->getDataGenerator();
        $mod = $gen->create_module('assign', array('name' => 'Assessment Deletion', 'course' => $this->course->id));
        $DB->set_field('grade_items', 'grademax', '22.00000', array('iteminstance' => $mod->id, 'itemmodule' => 'assign'));
        // Get the grade item of the new assessment.
        $gi = new grade_item(['courseid' => $cid, 'iteminstance' => $mod->id, 'itemmodule' => 'assign'], true);
        $module = local_gugcat::get_activity($cid, $gi->id);

        // Import assessment to GCAT.
        grade_capture::import_from_gradebook($cid, $module, array($module));

        // Assert module is imported.
        $moodlegi = grade_item::fetch(array('iteminfo' => $module->gradeitemid, 'itemtype' => 'manual',
         'itemname' => get_string('moodlegrade', 'local_gugcat')));
        $prvgi = grade_item::fetch(array('iteminfo' => $module->gradeitemid, 'itemtype' => 'manual',
         'itemname' => get_string('provisionalgrd', 'local_gugcat')));
        $this->assertNotFalse($moodlegi);
        $this->assertNotFalse($prvgi);

        $gcatactivities = local_gugcat::get_activities($this->course->id);
        // Assert $gcatactivities is 2 as we added 1 new assessment.
        $this->assertCount(2, $gcatactivities);

        // Delete assessment in moodle, this will set deletioninprogress = 1 in course_modules table.
        $DB->set_field('course_modules', 'deletioninprogress', 1, array('id' => $mod->cmid, 'course' => $cid));

        // Delete gcat items for this assessment.
        local_gugcat::delete_gcat_items($cid, $module);

        /* Call get_activities again, this will call the delete_gcat_items() function
         because of the deletioninprogress = 1. */
        $gcatactivities = local_gugcat::get_activities($cid);
        // Assert $gcatactivities is 1 as the previously added assessment was already deleted in moodle.
        $this->assertCount(1, $gcatactivities);

        // Assert module will not have moodle and provisional grade items.
        $moodlegi = grade_item::fetch(array('iteminfo' => $module->gradeitemid, 'itemtype' => 'manual',
         'itemname' => get_string('moodlegrade', 'local_gugcat')));
        $prvgi = grade_item::fetch(array('iteminfo' => $module->gradeitemid, 'itemtype' => 'manual',
         'itemname' => get_string('provisionalgrd', 'local_gugcat')));
        $this->assertFalse($moodlegi);
        $this->assertFalse($prvgi);
    }

    public function test_convert_grade() {
        // Populate static $grades scales.
        // Setting it to null will call to get gcat scale in json file.
        local_gugcat::set_grade_scale(null);

        // The convert_grade function will return the grade accd to gradetype, default is GRADE_TYPE_SCALE.
        $grade = 10; // D2 in 22 point scale.

        $return = local_gugcat::convert_grade($grade, GRADE_TYPE_VALUE);
        $this->assertEquals(10.00, $return);
        // As we are adjust grade in converting to 22 pt scale, we will add 1.
        $return = local_gugcat::convert_grade($grade + 1);
        $this->assertEquals('D2', $return);
    }

    public static function default_custom_field_category_object() {
        $customfieldcategory = new stdClass();
        $customfieldcategory->name = get_string('gugcatoptions', 'local_gugcat');
        $customfieldcategory->component = "core_course";
        $customfieldcategory->area = "course";
        $customfieldcategory->timecreated = time();
        $customfieldcategory->timemodified = time();

        return $customfieldcategory;
    }


    public static function create_custom_field_field($customfieldcategoryid) {
        $configdata = '{"required":"0","uniquevalues":"0","checkbydefault":"0","locked":"0","visibility":"0"}';
        $category = \core_customfield\category_controller::create($customfieldcategoryid);
        $field = \core_customfield\field_controller::create(0, (object)[
            'type' => 'checkbox',
            'configdata' => $configdata
        ], $category);

        $handler = $field->get_handler();
        $handler->save_field_configuration($field, (object)[
            'name' => get_string('showassessment', 'local_gugcat'),
            'shortname' => get_string('showonstudentdashboard', 'local_gugcat')
        ]);
    }

    public function test_get_child_activities_id() {
        global $DB;
        $gen = $this->getDataGenerator();
        $cid = $this->course->id;
        $gc1a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a->depth = 3;
        $gc2a->path = $gc1a->path . $gc2a->id . '/';
        $gc2a->parent = $gc1a->id;
        $gc2a->update();
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'categoryid', $gc2a->id, array('id' => $assignid));
        $cm = local_gugcat::get_child_activities_id($cid, $gc2a->id);
        $this->assertNotEmpty($cm);
        $this->assertEquals($cm[key($cm)]->gradeitemid, $this->cm->gradeitemid);
    }

    public function test_get_prvgrd_item_ids() {
        global $DB;
        $gen = $this->getDataGenerator();
        $cid = $this->course->id;
        $gc1a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a->depth = 3;
        $gc2a->path = $gc1a->path . $gc2a->id . '/';
        $gc2a->parent = $gc1a->id;
        $gc2a->update();
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'categoryid', $gc2a->id, array('id' => $assignid));
        $cm = local_gugcat::get_child_activities_id($cid, $gc2a->id);
        $prvgrdid = local_gugcat::add_grade_item($cid, get_string('provisionalgrd', 'local_gugcat'), $cm[key($cm)]);
        $prvgrds = local_gugcat::get_prvgrd_item_ids($cid, $cm);
        $this->assertNotEmpty($prvgrds);
        foreach ($prvgrds as $prvgrd) {
            $this->assertNotEmpty($prvgrd);
            $this->assertEquals($prvgrdid, $prvgrd->id);
        }
    }

    public function test_update_components_notes() {
        global $DB;
        $exepectednotes = 'testnotes';
        $subcatgi = local_gugcat::add_grade_item($this->course->id, get_string('subcategorygrade', 'local_gugcat'), null);
        local_gugcat::update_grade($this->student->id, $subcatgi, 19);
        local_gugcat::update_components_notes($this->student->id, $subcatgi, $exepectednotes);
        $notes = $DB->get_field('grade_grades', 'feedback', array('userid' => $this->student->id, 'itemid' => $subcatgi));
        $this->assertEquals($exepectednotes, $notes);
    }

    public function test_get_aggregated_assessment_history() {
        global $DB;
        $gen = $this->getDataGenerator();
        $cid = $this->course->id;
        $gc1a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a = new grade_category($gen->create_grade_category(['courseid' => $cid]), false);
        $gc2a->depth = 3;
        $gc2a->path = $gc1a->path . $gc2a->id . '/';
        $gc2a->parent = $gc1a->id;
        $gc2a->update();
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));
        $DB->set_field('grade_items', 'categoryid', $gc2a->id, array('id' => $assignid));
        // Create subcat gi.
        $subcatgi = $DB->get_record('grade_items', array('courseid' => $this->course->id, 'iteminstance' => $gc2a->id));
        $subcatgiid = local_gugcat::add_grade_item($this->course->id, get_string('subcategorygrade', 'local_gugcat'), null);
        $DB->set_field('grade_items', 'iteminfo', $gc2a->id, array('id' => $subcatgiid));
        local_gugcat::update_grade($this->student->id, $subcatgiid, 19, 'import');
        $module = local_gugcat::get_activity($this->course->id, $subcatgi->id);
        $rows = local_gugcat::get_aggregated_assessment_history($this->course->id, $this->student->id, $module);
        $this->assertNotEmpty($rows);
        $this->assertEquals($rows[0]->grade, '19.00000');
        $this->assertEquals($rows[0]->notes, get_string('import', 'local_gugcat'));
    }
}
