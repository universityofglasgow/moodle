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
 * Test write additional grade web service
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_advanced_testcase.php');

/**
 * Test has_capability web service.
 */
final class write_additional_grade_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Add some grades and check capture page data is correct
     *
     * @covers \local_gugrades\external\write_additional_grade::execute
     */
    public function test_new_column(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades.
        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign2 (which is using scale).
        $this->import_grades($this->course->id, $this->gradeitemidassign2, $userlist);

        // Add additional grade.
        $nothing = write_additional_grade::execute(
            $this->course->id,
            $this->gradeitemidassign2,
            $this->student->id,
            'SECOND',
            '',
            '',
            18,
            0,
            'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Check data for user 0 (Fred Bloggs).
        $fred = $page['users'][0];
        $this->assertEquals(true, $fred['alert']);

        $grades = $fred['grades'];
        $this->assertCount(3, $grades);
        $this->assertEquals('A5', $grades[1]['displaygrade']);
        $this->assertEquals('SECOND', $grades[1]['gradetype']);
        $this->assertEquals('A5', $grades[2]['displaygrade']);
        $this->assertEquals('PROVISIONAL', $grades[2]['gradetype']);

        // Add agreed grade and make sure alert (discrepancy) clears.
        $nothing = write_additional_grade::execute(
            $this->course->id,
            $this->gradeitemidassign2,
            $this->student->id,
            'AGREED',
            '',
            '',
            19,
            0,
            'Agreed grade'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals(false, $fred['alert']);
        $grades = $fred['grades'];
        $this->assertEquals('A4', $grades[3]['displaygrade']);
        $this->assertEquals('PROVISIONAL', $grades[3]['gradetype']);

        // Change agreed grade to 'No grade'.
        $nothing = write_additional_grade::execute(
            $this->course->id,
            $this->gradeitemidassign2,
            $this->student->id,
            'AGREED',
            '',
            '',
            -1,
            0,
            'Agreed grade is removed'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $grades = $fred['grades'];
        $this->assertEquals('No grade', $grades[2]['displaygrade']);
        $this->assertEquals('No grade', $grades[3]['displaygrade']);
    }

    /**
     * Add the same grade and make sure alert is off
     *
     * @covers \local_gugrades\external\write_additional_grade::execute
     */
    public function test_alert_is_off(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades.
        $userlist = [
            $this->student->id,
            //$this->student2->id,
        ];

        // Assign2 (which is using scale).
        $this->import_grades($this->course->id, $this->gradeitemidassign2, $userlist);

        // Add additional grade.
        $nothing = write_additional_grade::execute(
            $this->course->id,
            $this->gradeitemidassign2,
            $this->student->id,
            'SECOND',
            '',
            '',
            20,
            0,
            'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Check data for user 0 (Fred Bloggs).
        $fred = $page['users'][0];
        $this->assertEquals(false, $fred['alert']);
    }

}
