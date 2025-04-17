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
 * Test of manual grade items and the various states they can be in.
 *
 * @package    block_newgu_spdetails
 * @copyright  2025
 * @author     Greg Pedder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

use local_gugrades\api;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/newgu_spdetails/tests/external/newgu_spdetails_advanced_testcase.php');

/**
 * Unit tests for admin grades.
 */

class manual_grade_item_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * Test that a manual grade item appears on Student MyGrades when initially created.
     */
    public function test_return_manual_grade_item() {
        // We're the test student.
        $this->setUser($this->student1->id);

        // Create the manual grade item
        $manualgradeitem = $this->getDataGenerator()->create_grade_item([
            'itemname' => 'Manual grade item test',
            'itemtype' => 'manual',
            'itemmodule' => NULL,
            'courseid' => $this->mygradescourse->id,
            'categoryid' => $this->mygrades_summative_category->id,
            'gradetype' => 1,
            'grademax' => 100,
        ]);

        
        $activities = api::get_activities($this->mygradescourse->id, $this->mygrades_summative_category->id);

        $this->assertCount(1, $activities->items);
        $this->assertEquals(0, $activities->items[0]->hidden);
    }

    /**
     * Test of a manual grade item being hidden using the global setting.
     * This item won't appear in Student MyGrades, however, in Student MyGrades Staff View this will appear
     * as a hidden item.
     */
     public function test_manual_grade_item_global_hidden_setting() {
        // We're the test student.
        $this->setUser($this->student1->id);

        // Create the manual grade item, set it to hidden to mock the global setting.
        $manualgradeitem = $this->getDataGenerator()->create_grade_item([
            'itemname' => 'Manual grade item - hidden globally test',
            'itemtype' => 'manual',
            'itemmodule' => NULL,
            'courseid' => $this->mygradescourse->id,
            'categoryid' => $this->mygrades_summative_category->id,
            'gradetype' => 1,
            'grademax' => 100,
            'hidden' => 1
        ]);

        $activities = api::get_activities($this->mygradescourse->id, $this->mygrades_summative_category->id);
        $processedmanualgradeitem = $this->activityapi->process_manual_grade_item($activities->items[0], 'manual', true,
            $this->student1->id);
        $icon_text = get_string('manual_grade_item_hidden_icon_alt_text', 'block_newgu_spdetails');
        $icon_alt = "<i class='icon fa fa-eye-slash fa-fw' title='" . $icon_text . "' alt='" . $icon_text
            . "' aria-hidden='true' role='img' aria-label='" . $icon_text . "'></i>";
        $expected = $icon_alt . $manualgradeitem->itemname;
        $this->assertEquals($expected, $processedmanualgradeitem->item_name);
    }

    /**
     * Test of a manual grade item being hidden using the local setting (grade_grades item).
     * This item won't appear in Student MyGrades, however, in Student MyGrades Staff View this will appear
     * as a hidden item.
     */
    public function test_manual_grade_item_individual_hidden_setting() {
        // We're the test student.
        $this->setUser($this->student1->id);

        // Create the manual grade item - the global hidden option should default to 0.
        $manualgradeitem = $this->getDataGenerator()->create_grade_item([
            'itemname' => 'Manual grade item - hidden for an individual student test',
            'itemtype' => 'manual',
            'itemmodule' => NULL,
            'courseid' => $this->mygradescourse->id,
            'categoryid' => $this->mygrades_summative_category->id,
            'gradetype' => 1,
            'grademax' => 100
        ]);

        // Now mock a grade_grades record, but spoof it being hidden just for the student.
        $gradegradesitem = $this->getDataGenerator()->create_grade_grade([
            'itemid' => $manualgradeitem->id, 
            'userid' => $this->student1->id,
            'hidden' => 1
        ]);
        
        $activities = api::get_activities($this->mygradescourse->id, $this->mygrades_summative_category->id);
        $processedmanualgradeitem = $this->activityapi->process_manual_grade_item($activities->items[0], 'manual', true,
            $this->student1->id);
        $icon_text = get_string('manual_grade_item_hidden_icon_alt_text', 'block_newgu_spdetails');
        $icon_alt = "<i class='icon fa fa-eye-slash fa-fw' title='" . $icon_text . "' alt='" . $icon_text
            . "' aria-hidden='true' role='img' aria-label='" . $icon_text . "'></i>";
        $expected = $icon_alt . $manualgradeitem->itemname;
        $this->assertEquals($expected, $processedmanualgradeitem->item_name);
    }

    /**
     * Test of a manual grade item being hidden using the global setting but released via MyGrades.
     * This item will appear in Student MyGrades as a hidden item but with the grade from MyGrades.
     * In Student MyGrades Staff View this item will appear as a hidden item but displaying the grade
     * that was set in MyGrades.
     */
    public function test_manual_grade_item_global_hidden_setting_released_in_mygrades() {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Create the manual grade item, set it to hidden to mock the global setting.
        $manualgradeitem = $this->getDataGenerator()->create_grade_item([
            'itemname' => 'Manual grade item - hidden globally, released in MyGrades test',
            'itemtype' => 'manual',
            'itemmodule' => NULL,
            'courseid' => $this->mygradescourse->id,
            'categoryid' => $this->mygrades_summative_category->id,
            'gradetype' => 1,
            'grademax' => 100,
            'hidden' => 1
        ]);

        // Create the RELEASED entry in MyGrades and related tables.
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'PROVISIONAL',
            'other' => '',
            'points' => 0
        ]);
        $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'FIRST',
            'other' => '',
            'points' => 0
        ]);
        $columnid = $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'RELEASED',
            'other' => '',
            'points' => 0
        ]);
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'userid' => $this->student1->id,
            'points' => 1,
            'rawgrade' => 13.0,
            'convertedgrade' => 13.0,
            'admingrade' => '',
            'displaygrade' => '28',
            'gradetype' => 'RELEASED',
            'columnid' => $columnid,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);

        $gradedata = api::get_aggregation_dashboard_user($this->mygradescourse->id, $this->mygrades_summative_category->id,
            $this->student1->id);
        $tmpitems = $gradedata->fields;
        $gradecategories = [];
        $gradeitems = [];
        foreach($tmpitems as $tmpitem) {
            if ($tmpitem['iscategory'] == true) {
                $gradecategories[] = $tmpitem;
            } elseif ($tmpitem['iscategory'] == false) {
                // Fudge some values here for this test to pass.
                $tmpitem['grademissing'] = false;
                $gradeitems[] = $tmpitem;
            }
        }

        $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
        $activities = api::get_activities($this->mygradescourse->id, $this->mygrades_summative_category->id);
        $processedmygradesitem = $this->activityapi->process_mygrades_items($gradeitems, $activities->items, 'current',
            $ltiactivities, 'summative');
        $processedmanualgradeitem = $processedmygradesitem[0];
        $expectedicontext = get_string('hidden_icon_alt_text', 'block_newgu_spdetails');
        $expectedstatus = get_string('status_graded', 'block_newgu_spdetails');
        $expectedgrade = 28;

        // This will be the student's view - Student MyGrades Staff View now displays "-".
        $expectedfeedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');;
        $this->assertEquals($expectedicontext, $processedmanualgradeitem->icon_alt);
        $this->assertEquals($expectedstatus, $processedmanualgradeitem->grade_status);
        $this->assertEquals($expectedgrade, $processedmanualgradeitem->grade);
        $this->assertEquals($expectedfeedback, $processedmanualgradeitem->grade_feedback);
    }

    /**
     * Test of a manual grade item being hidden using the individual setting but released via MyGrades.
     * This item will appear in Student MyGrades as a hidden item but with the grade from MyGrades.
     * In Student MyGrades Staff View this item will appear as a hidden item but displaying the grade
     * that was released in MyGrades.
     */
    public function test_manual_grade_item_individual_hidden_setting_released_in_mygrades() {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Create the manual grade item - the global hidden option should default to 0.
        $manualgradeitem = $this->getDataGenerator()->create_grade_item([
            'itemname' => 'Manual grade item - hidden for an individual, released in MyGrades test',
            'itemtype' => 'manual',
            'itemmodule' => NULL,
            'courseid' => $this->mygradescourse->id,
            'categoryid' => $this->mygrades_summative_category->id,
            'gradetype' => 1,
            'grademax' => 100
        ]);

        // Now mock a grade_grades record, but spoof it being hidden just for the student.
        $gradegradesitem = $this->getDataGenerator()->create_grade_grade([
            'itemid' => $manualgradeitem->id, 
            'userid' => $this->student1->id,
            'hidden' => 1
        ]);

        // Create the RELEASED entry in MyGrades and related tables.
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'PROVISIONAL',
            'other' => '',
            'points' => 0
        ]);
        $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'FIRST',
            'other' => '',
            'points' => 0
        ]);
        $columnid = $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'RELEASED',
            'other' => '',
            'points' => 0
        ]);
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'userid' => $this->student1->id,
            'points' => 1,
            'rawgrade' => 13.0,
            'convertedgrade' => 13.0,
            'admingrade' => '',
            'displaygrade' => '28',
            'gradetype' => 'RELEASED',
            'columnid' => $columnid,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);

        $gradedata = api::get_aggregation_dashboard_user($this->mygradescourse->id, $this->mygrades_summative_category->id,
            $this->student1->id);
        $tmpitems = $gradedata->fields;
        $gradecategories = [];
        $gradeitems = [];
        foreach($tmpitems as $tmpitem) {
            if ($tmpitem['iscategory'] == true) {
                $gradecategories[] = $tmpitem;
            } elseif ($tmpitem['iscategory'] == false) {
                // Fudge some values here for this test to pass.
                $tmpitem['grademissing'] = false;
                $gradeitems[] = $tmpitem;
            }
        }

        $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
        $activities = api::get_activities($this->mygradescourse->id, $this->mygrades_summative_category->id);
        $processedmygradesitem = $this->activityapi->process_mygrades_items($gradeitems, $activities->items, 'current',
            $ltiactivities, 'summative');
        $processedmanualgradeitem = $processedmygradesitem[0];
        $expectedicontext = get_string('hidden_icon_alt_text', 'block_newgu_spdetails');
        $expectedstatus = get_string('status_graded', 'block_newgu_spdetails');
        $expectedgrade = 28;

        // This will be the student's view - Student MyGrades Staff View now displays "-".
        $expectedfeedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');;
        $this->assertEquals($expectedicontext, $processedmanualgradeitem->icon_alt);
        $this->assertEquals($expectedstatus, $processedmanualgradeitem->grade_status);
        $this->assertEquals($expectedgrade, $processedmanualgradeitem->grade);
        $this->assertEquals($expectedfeedback, $processedmanualgradeitem->grade_feedback);
    }

    /**
     * Test of a manual grade item being hidden using the global setting but released via MyGrades.
     * The item is then hidden in MyGrades.
     * This item will appear in Student MyGrades as a hidden item with no grade displaying.
     * In Student MyGrades Staff View this item will appear as a hidden item with no grade displaying.
     */
    public function test_manual_grade_item_global_hidden_setting_released_in_mygrades_hidden_from_student() {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Create the manual grade item, set it to hidden to mock the global setting.
        $manualgradeitem = $this->getDataGenerator()->create_grade_item([
            'itemname' => 'Manual grade item - hidden globally, released in MyGrades then hidden test',
            'itemtype' => 'manual',
            'itemmodule' => NULL,
            'courseid' => $this->mygradescourse->id,
            'categoryid' => $this->mygrades_summative_category->id,
            'gradetype' => 1,
            'grademax' => 100,
            'hidden' => 1
        ]);

        // Create the RELEASED entry in MyGrades and related tables, plus the hidden entry
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'PROVISIONAL',
            'other' => '',
            'points' => 0
        ]);
        $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'FIRST',
            'other' => '',
            'points' => 0
        ]);
        $columnid = $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'RELEASED',
            'other' => '',
            'points' => 0
        ]);
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'userid' => $this->student1->id,
            'points' => 1,
            'rawgrade' => 13.0,
            'convertedgrade' => 13.0,
            'admingrade' => '',
            'displaygrade' => '28',
            'gradetype' => 'RELEASED',
            'columnid' => $columnid,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);
        $hiddenid = $DB->insert_record('local_gugrades_hidden', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'userid' => $this->student1->id
        ]);

        $gradedata = api::get_aggregation_dashboard_user($this->mygradescourse->id, $this->mygrades_summative_category->id,
            $this->student1->id);
        $tmpitems = $gradedata->fields;
        $gradecategories = [];
        $gradeitems = [];
        foreach($tmpitems as $tmpitem) {
            if ($tmpitem['iscategory'] == true) {
                $gradecategories[] = $tmpitem;
            } elseif ($tmpitem['iscategory'] == false) {
                // Fudge some values here for this test to pass.
                $tmpitem['grademissing'] = false;
                $gradeitems[] = $tmpitem;
            }
        }

        $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
        $activities = api::get_activities($this->mygradescourse->id, $this->mygrades_summative_category->id);
        $processedmygradesitem = $this->activityapi->process_mygrades_items($gradeitems, $activities->items, 'current',
            $ltiactivities, 'summative');
        $processedmanualgradeitem = $processedmygradesitem[0];
        $expectedicontext = get_string('hidden_icon_alt_text', 'block_newgu_spdetails');
        $expectedstatus = get_string('status_graded', 'block_newgu_spdetails');
        $expectedgrade = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');

        // This will be the view in Student MyGrades, and Student MyGrades Staff View.
        $expectedfeedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');;
        $this->assertEquals($expectedicontext, $processedmanualgradeitem->icon_alt);
        $this->assertEquals($expectedstatus, $processedmanualgradeitem->grade_status);
        $this->assertEquals($expectedgrade, $processedmanualgradeitem->grade);
        $this->assertEquals($expectedfeedback, $processedmanualgradeitem->grade_feedback);
    }

    /**
     * Test of a manual grade item being hidden using the individual setting but released via MyGrades.
     * The item is then hidden in MyGrades.
     * This item will appear in Student MyGrades as a hidden item with no grade displaying.
     * In Student MyGrades Staff View this item will appear as a hidden item with no grade displaying.
     */
    public function test_manual_grade_item_individual_hidden_setting_released_in_mygrades_hidden_from_student() {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Create the manual grade item - the global hidden option should default to 0.
        $manualgradeitem = $this->getDataGenerator()->create_grade_item([
            'itemname' => 'Manual grade item - hidden for an individual, released in MyGrades test',
            'itemtype' => 'manual',
            'itemmodule' => NULL,
            'courseid' => $this->mygradescourse->id,
            'categoryid' => $this->mygrades_summative_category->id,
            'gradetype' => 1,
            'grademax' => 100
        ]);

        // Now mock a grade_grades record, but spoof it being hidden just for the student.
        $gradegradesitem = $this->getDataGenerator()->create_grade_grade([
            'itemid' => $manualgradeitem->id, 
            'userid' => $this->student1->id,
            'hidden' => 1
        ]);

        // Create the RELEASED entry in MyGrades and related tables, plus the hidden entry
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'PROVISIONAL',
            'other' => '',
            'points' => 0
        ]);
        $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'FIRST',
            'other' => '',
            'points' => 0
        ]);
        $columnid = $DB->insert_record('local_gugrades_column', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'gradetype' => 'RELEASED',
            'other' => '',
            'points' => 0
        ]);
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'userid' => $this->student1->id,
            'points' => 1,
            'rawgrade' => 13.0,
            'convertedgrade' => 13.0,
            'admingrade' => '',
            'displaygrade' => '28',
            'gradetype' => 'RELEASED',
            'columnid' => $columnid,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);
        $hiddenid = $DB->insert_record('local_gugrades_hidden', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $manualgradeitem->id,
            'userid' => $this->student1->id
        ]);

        $gradedata = api::get_aggregation_dashboard_user($this->mygradescourse->id, $this->mygrades_summative_category->id,
            $this->student1->id);
        $tmpitems = $gradedata->fields;
        $gradecategories = [];
        $gradeitems = [];
        foreach($tmpitems as $tmpitem) {
            if ($tmpitem['iscategory'] == true) {
                $gradecategories[] = $tmpitem;
            } elseif ($tmpitem['iscategory'] == false) {
                // Fudge some values here for this test to pass.
                $tmpitem['grademissing'] = false;
                $gradeitems[] = $tmpitem;
            }
        }

        $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
        $activities = api::get_activities($this->mygradescourse->id, $this->mygrades_summative_category->id);
        $processedmygradesitem = $this->activityapi->process_mygrades_items($gradeitems, $activities->items, 'current',
            $ltiactivities, 'summative');
        $processedmanualgradeitem = $processedmygradesitem[0];
        $expectedicontext = get_string('hidden_icon_alt_text', 'block_newgu_spdetails');
        $expectedstatus = get_string('status_graded', 'block_newgu_spdetails');
        $expectedgrade = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');

        // This will be the view in Student MyGrades, and Student MyGrades Staff View.
        $expectedfeedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');;
        $this->assertEquals($expectedicontext, $processedmanualgradeitem->icon_alt);
        $this->assertEquals($expectedstatus, $processedmanualgradeitem->grade_status);
        $this->assertEquals($expectedgrade, $processedmanualgradeitem->grade);
        $this->assertEquals($expectedfeedback, $processedmanualgradeitem->grade_feedback);
    }
}