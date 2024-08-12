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
class get_assessments_due_soon_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * Test that the returned assessments due date falls into
     * one of the following categories:
     * 24 hours
     * 7 days
     * 1 month
     */
    public function test_get_assessments_due_soon() {
        // We're the test student.
        $this->setUser($this->student1->id);

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
    }
}
