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
 * Testing the assessment overview web service and the various stats that are returned.
 *
 * Due to a limitation with the data generators - assignment submissions (made in the base class) only give us an entry in
 * mdl_assign_submissions - there doesn't seem to be a way to generate submitted assignments - i.e. where we can make use
 * of entries that end up in mdl_grade_grades. Therefore testing anything but 'to submit' isn't possible.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

use core_external\external_api;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/newgu_spdetails/tests/external/newgu_spdetails_advanced_testcase.php');

/**
 * Unit tests for retrieving the assessments overview chart numbers.
 */
final class assessment_overview_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * Test that the assessment overview returns the specific key names.
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsummary
     */
    public function test_get_assessment_overview(): void {
        // We're the test student.
        $this->setUser($this->student1->id);

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsummary::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsummary::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('sub_assess', $stats[0]);
        $this->assertArrayHasKey('tobe_sub', $stats[0]);
        $this->assertArrayHasKey('overdue', $stats[0]);
        $this->assertArrayHasKey('assess_marked', $stats[0]);
    }

    /**
     * Test that the number of submitted items match.
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsummary
     */
    public function test_get_assessment_overview_submitted(): void {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate1 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));
        $duedate2 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7, date("Y"));

        $mygradesassignment1 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate1,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygradessummativesubcategory->id,
            $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries.
        $this->add_assignment_grade($mygradesassignment1->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        $mygradesassignment2 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 2A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate2,
            'gradetype' => 2,
            'grademax' => 75,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygradessummativesubcategory->id,
            $mygradesassignment2->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we test for in the class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment2->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        $this->add_assignment_grade($mygradesassignment2->id, $this->student1->id, $this->teacher->id, 70,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsummary::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsummary::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('sub_assess', $stats[0]);
        $this->assertEquals(2, $stats[0]['sub_assess']);
    }

    /**
     * Test that the number of items to be submitted match.
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsummary
     */
    public function test_get_assessment_overview_tosubmit(): void {
        global $DB;
        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate1 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));
        $duedate2 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7, date("Y"));
        $duedate3 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7, date("Y"));

        $mygradesassignment1 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate1,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygradessummativesubcategory->id,
            $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries.
        $this->add_assignment_grade($mygradesassignment1->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_NEW);

        $mygradesassignment2 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 2A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate2,
            'gradetype' => 2,
            'grademax' => 75,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygradessummativesubcategory->id,
            $mygradesassignment2->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we test for in the class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment2->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        $this->add_assignment_grade($mygradesassignment2->id, $this->student1->id, $this->teacher->id, 70,
        ASSIGN_SUBMISSION_STATUS_NEW);

        $mygradesassignment3 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 2A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate3,
            'gradetype' => 2,
            'grademax' => 75,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygradessummativesubcategory->id,
            $mygradesassignment3->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we test for in the class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment3->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        $this->add_assignment_grade($mygradesassignment3->id, $this->student1->id, $this->teacher->id, 70,
        ASSIGN_SUBMISSION_STATUS_NEW);

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsummary::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsummary::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('tobe_sub', $stats[0]);
        $this->assertEquals(3, $stats[0]['tobe_sub']);
    }

    /**
     * Test that the number of items that are overdue match.
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsummary
     */
    public function test_get_assessment_overview_overdue(): void {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake a due date.
        $duedate1 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") - 7, date("Y"));
        $mygradesassignment1 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate1,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygradessummativesubcategory->id,
            $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries.
        $this->add_assignment_grade($mygradesassignment1->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_NEW);

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsummary::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsummary::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('overdue', $stats[0]);
        $this->assertEquals(1, $stats[0]['overdue']);
    }

    /**
     * Test that the number of items that have been graded match.
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsummary
     */
    public function test_get_assessment_overview_gradebook_graded(): void {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate1 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));

        $mygradesassignment1 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate1,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygradessummativesubcategory->id,
            $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries.
        $this->add_assignment_grade($mygradesassignment1->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        $mygradesgradeitemid1 = $this->get_grade_item('', 'assign', $mygradesassignment1->id);
        $DB->insert_record('grade_grades', [
            'itemid' => $mygradesgradeitemid1,
            'userid' => $this->student1->id,
            'finalgrade' => 13,
        ]);

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsummary::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsummary::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('assess_marked', $stats[0]);
        $this->assertEquals(1, $stats[0]['assess_marked']);
    }

    /**
     * Test for items processed in MyGrades but not released match.
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsummary
     */
    public function test_get_assessment_overview_mygrades_graded_but_unreleased(): void {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate1 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));

        $mygradesassignment1 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate1,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygradessummativesubcategory->id,
            $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries.
        $this->add_assignment_grade($mygradesassignment1->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        $mygradesgradeitemid1 = $this->get_grade_item('', 'assign', $mygradesassignment1->id);
        $DB->insert_record('grade_grades', [
            'itemid' => $mygradesgradeitemid1,
            'userid' => $this->student1->id,
            'rawgrade' => 13,
        ]);

        // Create an "initial" grade for this assignment.
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $mygradesgradeitemid1,
            'userid' => $this->student1->id,
            'rawgrade' => 13,
            'gradetype' => 'FIRST',
            'columnid' => 0,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsummary::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsummary::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('assess_marked', $stats[0]);
        $this->assertEquals(0, $stats[0]['assess_marked']);
    }

    /**
     * Test for items processed in MyGrades and released match.
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsummary
     */
    public function test_get_assessment_overview_mygrades_graded_and_released(): void {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate1 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));

        $mygradesassignment1 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate1,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygradessummativesubcategory->id,
            $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entry.
        $this->add_assignment_grade($mygradesassignment1->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        $mygradesgradeitemid1 = $this->get_grade_item('', 'assign', $mygradesassignment1->id);
        $DB->insert_record('grade_grades', [
            'itemid' => $mygradesgradeitemid1,
            'userid' => $this->student1->id,
            'finalgrade' => 13,
        ]);

        // Fake an imported and "released" grade for this assignment in MyGrades.
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $mygradesgradeitemid1,
            'userid' => $this->student1->id,
            'rawgrade' => 14,
            'gradetype' => 'FIRST',
            'columnid' => 0,
            'iscurrent' => 0,
            'auditby' => $this->teacher->id,
            'audittimecreated' => $now,
        ]);
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $mygradesgradeitemid1,
            'userid' => $this->student1->id,
            'rawgrade' => 13,
            'gradetype' => 'AGREED',
            'columnid' => 0,
            'iscurrent' => 0,
            'auditby' => $this->teacher->id,
            'audittimecreated' => $now,
        ]);
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $mygradesgradeitemid1,
            'userid' => $this->student1->id,
            'rawgrade' => 13,
            'convertedgrade' => 12,
            'gradetype' => 'RELEASED',
            'columnid' => 0,
            'iscurrent' => 1,
            'auditby' => $this->teacher->id,
            'audittimecreated' => $now,
        ]);

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsummary::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsummary::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('assess_marked', $stats[0]);
        $this->assertEquals(1, $stats[0]['assess_marked']);
    }

    /**
     * Test the method returns activities due, by type, e.g. due in 1 week.
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsummary
     */
    public function test_get_assessment_overview_by_type(): void {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 5, date("Y"));

        $mygradesassignment = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $mygradessummativesubcategoryid = $this->mygradessummativesubcategory->id;
        $params = [
            $mygradessummativesubcategoryid,
            $mygradesassignment->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Fake some more due dates.
        $duedate2 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 3, date("Y"));

        $mygradesassignment2 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate2,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $mygradessummativesubcategoryid = $this->mygradessummativesubcategory->id;
        $params = [
            $mygradessummativesubcategoryid,
            $mygradesassignment2->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment2->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Check that our stats values for the given type are returned as expected.
        $type = 0; // To be submitted.
        $stats = get_assessmentsummarybytype::execute($type);
        $stats = external_api::clean_returnvalue(
            get_assessmentsummarybytype::execute_returns(),
            $stats
        );

        $stat = json_decode($stats['result']);
        $result = count($stat->assessmentitems);
        $this->assertIsArray($stat->assessmentitems);
        $this->assertEquals(2, $result);
    }
}
