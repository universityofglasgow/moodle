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
        local_gugcat::$STUDENTS = $this->students;
        grade_capture::import_from_gradebook($this->course->id, $this->cm, $cm);
        $this->provisionalgi = local_gugcat::add_grade_item($this->course->id, get_string('provisionalgrd', 'local_gugcat'), $this->cm);
    }

    public function test_grade_aggregation_rows() {
        //student provisonal grades
        $s1grd =  5;
        $s2grd =  10;
        //expected grades
        $exp_s1grd = '5.00000';
        $exp_s2grd =  '10.00000';

        foreach ($this->students as $student) {
            // Provisional grades
            $grade_ = new grade_grade(array('userid' => $student->id, 'itemid' => $this->provisionalgi), true);
            $grade_->information = '1.00000';
            $grade_->rawgrade = ($student->id != $this->student1->id) ? $s2grd : $s1grd;
            $grade_->finalgrade = ($student->id != $this->student1->id) ? $s2grd : $s1grd;
            $grade_->update();
        }
        $modules = array($this->cm);
        $rows = grade_aggregation::get_rows($this->course, $modules, $this->students);
        //get the weight of the main activity grade item
        $gi = $this->cm->gradeitem;
        $weightcoef1 = $gi->aggregationcoef; //Aggregation coeficient used for weighted averages or extra credit
        $weightcoef2 = $gi->aggregationcoef2; //Aggregation coeficient used for weighted averages only
        $weight = ((float)$weightcoef1 > 0) ? (float)$weightcoef1 : (float)$weightcoef2;
        $exp_aggregatedgrd1 = (float)$exp_s1grd * (float)$weight;
        $exp_aggregatedgrd2 = (float)$exp_s2grd * (float)$weight;
        $expectedcompleted = "100%"; //expected completed percent since there's only one activity
        $this->assertCount(2, $rows);
        //assert each rows that it has the provisional grade
        $row1 = $rows[1];
        $this->assertEquals($row1->cnum, 2);
        $this->assertEquals($row1->studentno, $this->student1->id);
        $this->assertEquals(local_gugcat::convert_grade($exp_s1grd), $row1->grades[0]->grade);
        $this->assertEquals($row1->completed, $expectedcompleted); //assert complete percent
        $this->assertEquals(local_gugcat::convert_grade($exp_aggregatedgrd1), $row1->aggregatedgrade->grade); //assert aggregated grade
        $row2 = $rows[0];
        $this->assertEquals($row2->cnum, 1);
        $this->assertEquals($row2->studentno, $this->student2->id);
        $this->assertEquals(local_gugcat::convert_grade($exp_s2grd), $row2->grades[0]->grade);
        $this->assertEquals($row2->completed, $expectedcompleted);
        $this->assertEquals(local_gugcat::convert_grade($exp_aggregatedgrd2), $row2->aggregatedgrade->grade);
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
        local_gugcat::update_grade($this->student1->id, $aggradeitem, 19, $expectednotes, time());
        $rows = grade_aggregation::get_rows($this->course, $modules, $student);
        $aggrade = $DB->get_record('grade_grades', array('userid'=>$this->student1->id, 'itemid'=>$aggradeitem));
        $this->assertEquals($rows[0]->aggregatedgrade->rawgrade, '19.00000');
        $this->assertEquals($expectednotes, $aggrade->feedback);
        $this->assertNotEquals($defaultoverridden, $aggrade->overridden);
    }

    public function test_release_final_grades() {
        $expectedgradeint = 10;
        $expectedgrade = '9.00000'; // -1 for the grade offset
        $grade_ = new grade_grade(array('userid' => $this->student1->id, 'itemid' => $this->provisionalgi), true);
        $grade_->information = '1.00000';
        $grade_->rawgrade = $expectedgradeint;
        $grade_->finalgrade = $expectedgradeint;
        $grade_->update();
        $gradeitemid = $this->cm->gradeitem->id;
        grade_aggregation::release_final_grades($this->course->id);
        $gg = new grade_grade(array('userid' => $this->student1->id, 'itemid' => $gradeitemid), true);
        $this->assertEquals($this->student1->id, $gg->userid);//assert updated user = student 1
        $this->assertEquals($expectedgrade, $gg->finalgrade);//assert finalgrade = 10.00000
        $this->assertEquals('final', $gg->information); //assert information = final
    }

    public function test_get_course_grade_history(){
        $modules = array($this->cm);
        $aggradeitem = local_gugcat::add_grade_item($this->course->id, get_string('aggregatedgrade', 'local_gugcat'), null);
        $expectednotes = 'testnote';
        local_gugcat::update_grade($this->student1->id, $aggradeitem, 19, $expectednotes, time());

        $gradehistory = grade_aggregation::get_course_grade_history($this->course, $modules, $this->student1);
        $this->assertEquals($gradehistory[1]->grade, 'A5');
        $this->assertEquals($gradehistory[1]->notes, $expectednotes);
    }

    public function test_normalize_grades(){
        $grades = array(1 => 10, 2 => 20, 3 => 30);
        $gi1 = new grade_item(array('id'=>1));
        $gi2 = new grade_item(array('id'=>2));
        $gi3 = new grade_item(array('id'=>3));
        $gradeitems = array(1 => $gi1, 2 => $gi2, 3 => $gi3);

        // Assert normalize grades
        $normgrades = grade_aggregation::normalize_grades($grades, $gradeitems);
        // grademax = 100 (default) grade/100
        $this->assertEquals($normgrades, $grades);

        $grademax = 50;
        $gi1->grademax = $grademax;
        $gi2->grademax = $grademax;
        $gi3->grademax = $grademax;
        $gradeitems = array(1 => $gi1, 2 => $gi2, 3 => $gi3);
        // Assert normalize grades
        $normgrades = grade_aggregation::normalize_grades($grades, $gradeitems);
        // grademax = 50 grade/50
        $this->assertContains(20, $normgrades);
        $this->assertContains(40, $normgrades);
        $this->assertContains(60, $normgrades);
    }

    public function test_calculate_grade(){
        $grades = array(1 => 10, 2 => 20, 3 => 30);
        $aggregationcoef = '1.000000';
        $gi1 = new grade_item(array('id'=>1));
        $gi1->aggregationcoef = $aggregationcoef;
        $gi2 = new grade_item(array('id'=>2));
        $gi2->aggregationcoef = $aggregationcoef;
        $gi3 = new grade_item(array('id'=>3));
        $gi3->aggregationcoef = $aggregationcoef;
        $gradeitems = array(1 => $gi1, 2 => $gi2, 3 => $gi3);

        $subcatobj = new stdClass();
        $subcatobj->aggregation = GRADE_AGGREGATE_MAX;
        $subcatobj->gradeitem = new grade_item();
        $subcatobj->gradeitem->gradetype = GRADE_TYPE_VALUE;
        // Assert calculation to get the Highest grade
        $calgrade = grade_aggregation::calculate_grade($subcatobj, $grades, $gradeitems);
        // grades max[10, 20, 30] = 30
        $this->assertEquals($calgrade, 30);

        // Assert calculation to get the weighted mean grade
        $subcatobj->aggregation = GRADE_AGGREGATE_WEIGHTED_MEAN;
        $calgrade = grade_aggregation::calculate_grade($subcatobj, $grades, $gradeitems);
        // grades [10 x aggregationcoef, 20 x aggregationcoef, 30 x aggregationcoef] / 3 = 20
        $this->assertEquals($calgrade, 20);

        // Assert calculation to get the simple mean grade
        $subcatobj->aggregation = GRADE_AGGREGATE_MEAN;
        $calgrade = grade_aggregation::calculate_grade($subcatobj, $grades, $gradeitems);
        // grades [10 + 20 + 30] / 3 = 20
        $this->assertEquals($calgrade, 20);

        // Assert calculation to get the lowest grade
        $subcatobj->aggregation = GRADE_AGGREGATE_MIN;
        $calgrade = grade_aggregation::calculate_grade($subcatobj, $grades, $gradeitems);
        // grades [10, 20, 30] => 10
        $this->assertEquals($calgrade, 10);

        // Assert calculation to get the natural sum
        $subcatobj->aggregation = GRADE_AGGREGATE_SUM;
        $calgrade = grade_aggregation::calculate_grade($subcatobj, $grades, $gradeitems);
        // grades [10 + 20 + 30] = 60
        $this->assertEquals($calgrade, 60);

        // Assert calculation to get the median
        $subcatobj->aggregation = GRADE_AGGREGATE_MEDIAN;
        $calgrade = grade_aggregation::calculate_grade($subcatobj, $grades, $gradeitems);
        // grades [10, 20, 30] => 20
        $this->assertEquals($calgrade, 20);

        // Assert calculation to get the mode
        $subcatobj->aggregation = GRADE_AGGREGATE_MODE;
        $grades[] = 10;
        $gi4 = new grade_item(array('id'=>4));
        $gi4->aggregationcoef = $aggregationcoef;
        $grades[4] = $gi4;
        $calgrade = grade_aggregation::calculate_grade($subcatobj, $grades, $gradeitems);
        // grades [10, 20, 30, 10] = 10
        $this->assertEquals($calgrade, 10);
    }

    public function test_get_aggregated_grade(){
        $categoryid = 10;
        $userid = $this->student1->id;

        $prvgi = local_gugcat::add_grade_item($this->course->id,
        get_string('subcategorygrade', 'local_gugcat'), null, [$this->student1]);

        $pgobj = grade_grade::fetch(array('itemid' => $prvgi, 'userid' => $userid));
        // Create components array with grades obj
        $gradeitems = array();
        // Create subcat grade item with grades obj
        $subcatobj = new stdClass();
        $subcatobj->id = $categoryid; // category id type of sub cat
        $subcatobj->aggregateonlygraded = 1; // Aggregate only graded
        $subcatobj->droplow = 0; // Drop lowest
        $subcatobj->aggregation = GRADE_AGGREGATE_MEAN; // aggregation type, get the Mean grade
        $subcatobj->aggregation_type = GRADE_AGGREGATE_MEAN; // aggregation type, get the Mean grade
        $subcatobj->gradeitem = new stdClass();
        $subcatobj->gradeitem->gradetype = GRADE_TYPE_VALUE; // Sub cat gradetype
        $subcatobj->is_converted = false; // Sub cat converted

        // Assert return null if provisional grade is undefined
        $subcatobj->grades->provisional[] = $pgobj;
        list($aggregatedgrade, $processed, $error) = grade_aggregation::get_aggregated_grade($userid, $subcatobj, $gradeitems);
        $this->assertNull($aggregatedgrade);
        $this->assertFalse($processed);
        $this->assertNull($error);

        // Assert return null if there are no components
        $subcatobj->grades->provisional[$userid] = $pgobj;
        list($aggregatedgrade, $processed, $error) = grade_aggregation::get_aggregated_grade($userid, $subcatobj, $gradeitems);
        $this->assertNull($aggregatedgrade);
        $this->assertFalse($processed);
        $this->assertNull($error);

        // Assert getting the calculation done if item was not overridden
        $pgobj->overridden = 0; // Set overridden
        $subcatobj->grades->provisional[$userid] = $pgobj;

        // Create components obj
        $componentpg1 = new stdClass();
        $componentpg1->finalgrade = 10;
        $componentpg1->itemid = 1;
        $gi1 = new stdClass();
        $gi1->id = 1;
        $gi1->gradeitem = new grade_item(array('id'=>1));
        $gi1->gradeitem->gradetype = GRADE_TYPE_VALUE;
        $gi1->gradeitem->categoryid = $categoryid;
        // Add provisional grades on the grades property of component
        $gi1->grades->provisional[$userid] = $componentpg1;
        $gradeitems[$gi1->id] = $gi1;

        $componentpg2 = new stdClass();
        $componentpg2->finalgrade = 20;
        $componentpg2->itemid = 2;
        $gi2 = new stdClass();
        $gi2->id = 2;
        $gi2->gradeitem = new grade_item(array('id'=>2));
        $gi2->gradeitem->categoryid = $categoryid;
        $gi2->gradeitem->gradetype = GRADE_TYPE_SCALE;
        // Add provisional grades on the grades property of component
        $gi2->grades->provisional[$userid] = $componentpg2;
        $gradeitems[$gi2->id] = $gi2;

        // Assert returns error if not all components have the same gradetypes
        list($aggregatedgrade, $processed, $error) = grade_aggregation::get_aggregated_grade($userid, $subcatobj, $gradeitems);
        $this->assertNull($aggregatedgrade);
        $this->assertTrue($processed);
        $this->assertEquals($error, get_string('aggregationwarningcomponents', 'local_gugcat'));

        // Same grade type
        $gi2->gradeitem->gradetype = GRADE_TYPE_VALUE;
        $gradeitems[$gi2->id] = $gi2;

        // Assert return is the provisional obj itself if sub cat is overridden
        $pgobj->overridden = 1;
        $subcatobj->grades->provisional[$userid] = $pgobj;
        list($aggregatedgrade, $processed, $error) = grade_aggregation::get_aggregated_grade($userid, $subcatobj, $gradeitems);
        $this->assertEquals($aggregatedgrade->grade, $pgobj->finalgrade);
        $this->assertFalse($processed);
        $this->assertNull($error);

        $pgobj->overridden = 0;
        $subcatobj->grades->provisional[$userid] = $pgobj;
        // Assert return gets the mean grade
        list($aggregatedgrade, $processed, $error) = grade_aggregation::get_aggregated_grade($userid, $subcatobj, $gradeitems);
        // Grades - [10, 20] / 2 = 15
        $this->assertEquals($aggregatedgrade->grade, 15);
        $this->assertTrue($processed);
        $this->assertNull($error);

        // Check if grade from db was also updated
        $updatedgradeobj = grade_grade::fetch(array('itemid' => $prvgi, 'userid' => $userid));
        $this->assertEquals($updatedgradeobj->finalgrade, '15.00000');
        $this->assertEquals($updatedgradeobj->rawgrade, '15.00000');

        // Test grades including Non submission -1
        $componentpg3 = new stdClass();
        $componentpg3->finalgrade = null;
        $componentpg3->itemid = 3;
        $componentpg3->rawgrade = -1;
        $gi3 = new stdClass();
        $gi3->id = 3;
        $gi3->gradeitem = new grade_item(array('id'=>3));
        $gi3->gradeitem->categoryid = $categoryid;
        $gi1->gradeitem->gradetype = GRADE_TYPE_VALUE;
        // Add provisional grades on the grades property of component
        $gi3->grades->provisional[$userid] = $componentpg3;
        $gradeitems[$gi3->id] = $gi3;
        // Assert return is non submission grade -1
        list($aggregatedgrade, $processed, $error) = grade_aggregation::get_aggregated_grade($userid, $subcatobj, $gradeitems);
        // grades [10, 20, -1] => [-1]
        $this->assertEquals($aggregatedgrade->grade, -1);
        $this->assertTrue($processed);
        $this->assertNull($error);

        // Test including drop lowest
        $subcatobj->droplow = 1; // Drop 1 lowest
        list($aggregatedgrade, $processed, $error) = grade_aggregation::get_aggregated_grade($userid, $subcatobj, $gradeitems);
        // grades [10, 20, -1] => [10, 20] / 2 = 15
        $this->assertEquals($aggregatedgrade->grade, 15);
        $this->assertTrue($processed);
        $this->assertNull($error);
    }

    public function test_get_parent_child_activities(){
        global $DB;
        $assignid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign'));

        $courseid = $this->course->id;
        //create main category
        $category = $this->getDataGenerator()->create_grade_category(array('courseid' => $courseid));

        $DB->set_field('grade_items', 'categoryid', $category->id, array('id'=> $assignid));

        $modarray = grade_aggregation::get_parent_child_activities($courseid, $category->id);

        $this->assertEquals($this->assign1->id, $modarray[0]->gradeitem->iteminstance);

        //create sub category
        $subcategory = $this->getDataGenerator()->create_grade_category(
            array(
                'courseid' => $courseid,
                'parent' => $category->id)
        );

        $modarray = grade_aggregation::get_parent_child_activities($courseid, $category->id);
        $this->assertEquals($subcategory->id, $modarray[1]->gradeitem->iteminstance);
        $this->assertEquals('category', $modarray[1]->modname);

        //create module inside subcategory
        $assign = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id));
        $gradeitemid = $DB->get_field('grade_items', 'id', array('courseid' => $this->course->id, 'itemmodule' => 'assign', 'iteminstance' => $assign->id));
        $DB->set_field('grade_items', 'grademax', '22.00000', array('id'=> $gradeitemid));
        $DB->set_field('grade_items', 'categoryid', $subcategory->id, array('id'=> $gradeitemid));

        $modarray = grade_aggregation::get_parent_child_activities($courseid, $category->id);
        $this->assertEquals($assign->id, $modarray[1]->gradeitem->iteminstance);
        $this->assertEquals('assign', $modarray[1]->modname);

        $this->assertCount(3, $modarray);
    }

    public function test_create_edit_alt_grades(){
        global $DB, $COURSE;
        $COURSE = $this->course;

        // Create merit grade item first
        // Sample 2 assessments and 50 each weights
        $assessments = array(1 => 1, 2 => 2);
        $weights = array(1 => 50, 2 => 50);
        grade_aggregation::create_edit_alt_grades(MERIT_GRADE, $assessments, $weights);

        // Get for merit grade item
        $meritgi = grade_item::fetch(array('courseid' => $COURSE->id, 'iteminfo' => 0, 'itemname' => get_string('meritgrade', 'local_gugcat')));
        $this->assertNotNull($meritgi);
        $this->assertEquals($meritgi->itemname, get_string('meritgrade', 'local_gugcat'));
        $this->assertEquals($meritgi->courseid, $COURSE->id);
        $this->assertEquals($meritgi->iteminfo, '0');
        // Retrieve merit setup
        $meritsettings = $DB->get_records('gcat_acg_settings', array('acgid'=>$meritgi->id));
        $this->assertCount(count($assessments), $meritsettings);
        foreach($meritsettings as $setup){
            $this->assertEquals($setup->acgid, $meritgi->id);
            $this->assertEquals($setup->weight, '0.50000');
            $this->assertNull($setup->cap);
        }

        // Create gpa alt grade item

        // Sample 2 assessments and 50 each weights
        $resits = array(1 => 1, 2 => 2);
        $cap = 10;
        grade_aggregation::create_edit_alt_grades(GPA_GRADE, $resits, [], $cap);

        // Get for gpa grade item
        $gpagi = grade_item::fetch(array('courseid' => $COURSE->id, 'iteminfo' => 0, 'itemname' => get_string('gpagrade', 'local_gugcat')));
        $this->assertNotNull($gpagi);
        $this->assertEquals($gpagi->itemname, get_string('gpagrade', 'local_gugcat'));
        $this->assertEquals($gpagi->courseid, $COURSE->id);
        $this->assertEquals($gpagi->iteminfo, '0');
        // Retrieve gpa setup
        $gpasettings = $DB->get_records('gcat_acg_settings', array('acgid'=>$gpagi->id));
        $this->assertCount(count($resits), $gpasettings);
        foreach($gpasettings as $setup){
            $this->assertEquals($setup->acgid, $gpagi->id);
            $this->assertEquals($setup->cap, '10.00000');
            $this->assertNull($setup->weight);
        }
    }

    public function test_get_alt_grade(){
        global $DB, $COURSE;
        $COURSE = $this->course;

        // Sample assessments
        $act1 = new stdClass();
        $act1->meritweight = 0.5;
        $act1->gpacap = 10;
        // Add grade in act 1 for student 1
        $grades = new stdClass();
        $grades->altgrades = array($this->student->id => 23);
        $act1->grades = $grades;

        $act2 = new stdClass();
        $act2->meritweight = 0.5;
        $act2->gpacap = 10;
        // Add grade in act 2 for student 1
        $grades = new stdClass();
        $grades->altgrades = array($this->student->id => 11);
        $act2->grades = $grades;

        // Handle missing grade
        $act3 = new stdClass();
        $act3->meritweight = 0.5;
        $act3->gpacap = 10;
        // Add grade in act 2 for student 1
        $grades = new stdClass();
        $grades->altgrades = array($this->student->id => null);// null grade
        $act3->grades = $grades;

        // Get merit grade --------

        // Create merit setup first
        $assessments = array(1 => 1, 2 => 2);
        $weights = array(1 => 50, 2 => 50);
        grade_aggregation::create_edit_alt_grades(MERIT_GRADE, $assessments, $weights);

        $meritgi = local_gugcat::get_grade_item_id($COURSE->id, '0', get_string('meritgrade', 'local_gugcat'));

        // Merit grade calculated successfully
        $gradeobj = grade_aggregation::get_alt_grade(true, $meritgi, array($act1, $act2), $this->student->id);
        $this->assertNotNull($gradeobj);
        $this->assertFalse($gradeobj->overridden);
        // Grades are 23 and 11 (-1 to normalize) (22 x 0.50 + 10 x 0.50)/ 1.00 = 16 => B2
        $this->assertEquals($gradeobj->rawgrade, 16);
        $this->assertEquals($gradeobj->grade, 'B2');

        // Handles one null grade
        $gradeobj = grade_aggregation::get_alt_grade(true, $meritgi, array($act1, $act2, $act3), $this->student->id);
        $this->assertNotNull($gradeobj);
        $this->assertFalse($gradeobj->overridden);
        // Grades are 23, 11, null
        $this->assertNull($gradeobj->rawgrade);
        $this->assertEquals($gradeobj->grade, get_string('missinggrade', 'local_gugcat'));


        // Get gpa grade --------

        // Create gpa setup first
        $resits = array(1 => 1, 2 => 2);
        $cap = 10;
        grade_aggregation::create_edit_alt_grades(GPA_GRADE, $resits, [], $cap);
        $gpagi = local_gugcat::get_grade_item_id($COURSE->id, '0', get_string('gpagrade', 'local_gugcat'));
        // Sample aggregated grade
        $aggrdobj = new stdClass();
        $aggrdobj->rawgrade = 5;
        // GPA grade calculated successfully
        $gradeobj = grade_aggregation::get_alt_grade(false, $gpagi, array($act1, $act2), $this->student->id, $aggrdobj);
        $this->assertNotNull($gradeobj);
        $this->assertFalse($gradeobj->overridden);
        // Grades are 23 and 11, Cap is 10 (-1 to normalize), Aggregated grade is 5, 9 > 5 = 9 => D3
        $this->assertEquals($gradeobj->rawgrade, 9);
        $this->assertEquals($gradeobj->grade, 'D3');

        // Aggregated grade greater than cap
        $aggrdobj->rawgrade = 15;
        $gradeobj = grade_aggregation::get_alt_grade(false, $gpagi, array($act1, $act2), $this->student->id, $aggrdobj);
        $this->assertNotNull($gradeobj);
        $this->assertFalse($gradeobj->overridden);
        // Grades are 23 and 11, Cap is 10 (-1 to normalize), Aggregated grade is 15, 9 < 15 = 15 => B3
        $this->assertEquals($gradeobj->rawgrade, 15);
        $this->assertEquals($gradeobj->grade, 'B3');

        // Handles one null grade
        $gradeobj = grade_aggregation::get_alt_grade(false, $gpagi, array($act1, $act3), $this->student->id, $aggrdobj);
        $this->assertNotNull($gradeobj);
        $this->assertFalse($gradeobj->overridden);
        // Grades are 23 and null, display Aggregated grade is 15 => B3
        $this->assertEquals($gradeobj->rawgrade, 15);
        $this->assertEquals($gradeobj->grade, 'B3');

        // Overridden grades
        $DB->set_field('grade_grades', 'overridden', time(), array('itemid'=>$gpagi, 'userid'=>$this->student->id));
        $DB->set_field('grade_grades', 'finalgrade', 23, array('itemid'=>$gpagi, 'userid'=>$this->student->id));

        $gradeobj = grade_aggregation::get_alt_grade(false, $gpagi, array($act1, $act3), $this->student->id, $aggrdobj);
        $this->assertNotNull($gradeobj);
        $this->assertTrue($gradeobj->overridden);
        // Overridden grade is 23 => A1
        $this->assertEquals($gradeobj->rawgrade, '23.00000');
        $this->assertEquals($gradeobj->grade, 'A1');
    }

    public function test_acg_grade_history(){
        $expectedgrade = 'A3';
        $expectednotes = get_string('systemupdatecreateupdate', 'local_gugcat');
        $expectedtype = get_string('systemupdate', 'local_gugcat');
        $acgid = local_gugcat::add_grade_item($this->course->id, get_string('meritgrade', 'local_gugcat'), null);
        $grade_ = new grade_grade(array('userid' => $this->student1->id, 'itemid' => $acgid), true);
        $grade_->information = '1.00000';
        $grade_->rawgrade = 20;
        $grade_->finalgrade = 20;
        $grade_->update();

        $gradehistory = grade_aggregation::acg_grade_history($this->course, $this->student1, MERIT_GRADE);
        $grdhistory = $gradehistory[key($gradehistory)];
        $this->assertEquals($expectedgrade, $grdhistory->grade);
        $this->assertEquals($expectednotes, $grdhistory->notes);
        $this->assertEquals($expectedtype, $grdhistory->type);
    }
}
