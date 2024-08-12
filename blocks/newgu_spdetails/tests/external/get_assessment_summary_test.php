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
 * Testing the assessment summary web service and the various stats that are returned.
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
 * Unit tests for retrieving assessment summaries.
 */
class get_assessment_summary_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * Test that the assessment summary returns the specific key names.
     */
    public function test_get_assessment_summary() {
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
     */
    public function test_get_assessment_summary_submitted() {
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
        $this->assertEquals(1, $stats[0]['sub_assess']);
    }

    /**
     * Test that the number of items to be submitted match.
     */
    public function test_get_assessment_summary_tosubmit() {
        // We're the test student.
        $this->setUser($this->student1->id);

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
     */
    public function test_get_assessment_summary_overdue() {
        // We're the test student.
        $this->setUser($this->student1->id);

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsummary::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsummary::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('overdue', $stats[0]);
        $this->assertEquals(0, $stats[0]['overdue']);
    }

    /**
     * Test that the number of items that have been graded match.
     */
    public function test_get_assessment_summary_graded() {
        // We're the test student.
        $this->setUser($this->student1->id);

        // Check that our stats values are returned as expected.
        $stats = get_assessmentsummary::execute();
        $stats = external_api::clean_returnvalue(
            get_assessmentsummary::execute_returns(),
            $stats
        );
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('assess_marked', $stats[0]);
        $this->assertEquals(4, $stats[0]['assess_marked']);
    }
}
