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
 * Test of the assessments due soon feature.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024
 * @author     Greg Pedder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

use core_external\external_api;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/newgu_spdetails/tests/external/newgu_spdetails_advanced_testcase.php');

/**
 * Unit tests for activities that are due in the near future.
 */
final class assessments_due_soon_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * Test that the returned assessments due date falls into
     * one of the following categories:
     * 24 hours
     * 7 days
     * 1 month
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsduesoon
     */
    public function test_get_assessments_due_soon(): void {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate = mktime(date("H") + 5, date("i"), date("s"), date("m"), date("d"), date("Y"));

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

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsduesoon::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsduesoon::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('24hours', $stats[0]);
        $this->assertArrayHasKey('week', $stats[0]);
        $this->assertArrayHasKey('month', $stats[0]);

        $this->assertIsNumeric($stats[0]['24hours']);
        $this->assertIsNumeric($stats[0]['week']);
        $this->assertIsNumeric($stats[0]['month']);
        $this->assertEquals(1, $stats[0]['24hours']);
    }

    /**
     * Test the method returns activities due, by type, e.g. due in 1 week.
     *
     * @covers \blocks\newgu_spdetails\classes\external\get_assessmentsduebytype
     */
    public function test_get_assessments_due_by_type(): void {
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
        $type = 1; // 7 days
        $stats = get_assessmentsduebytype::execute($type);
        $stats = external_api::clean_returnvalue(
            get_assessmentsduebytype::execute_returns(),
            $stats
        );

        $stat = json_decode($stats['result']);
        $result = count($stat->assessmentitems);
        $this->assertIsArray($stat->assessmentitems);
        $this->assertEquals(2, $result);
    }
}
