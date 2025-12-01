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
 * Test functionality around grading out of (exactly) 22 points
 * Should function exactly as though Schedule A had been selected.
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
 * Test import_grades_users web service.
 */
final class points_22_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Get the types and check
     *
     * @covers \local_gugrades\external\get_grade_item::execute
     */
    public function test_get_grade_item(): void {
        global $DB;

        // Use the test teacher.
        $this->setUser($this->teacher->id);

        $item = get_grade_item::execute($this->gradeitemidassign22);
        $item = external_api::clean_returnvalue(
            get_grade_item::execute_returns(),
            $item
        );

        $this->assertEquals('Assignment', $item['itemmodule']);
        $this->assertEquals('Schedule A (22)', $item['scalename']);
        $this->assertTrue($item['isscale']);
    }

    /**
     * Import first grades.
     * Check that maxgrade=22 grade imports correctly
     *
     * @covers \local_gugrades\external\import_grades_users::execute
     */
    public function test_import_and_add(): void {

        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign22.
        $this->import_grades($this->course->id, $this->gradeitemidassign22, $userlist);

        // Check the capture page shows the correct grades.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign22, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $users = $page['users'];
        $this->assertEquals('H', $users[0]['grades'][0]['displaygrade']);
        $this->assertEquals('A1', $users[1]['grades'][0]['displaygrade']);

        // Get the add grade form and check it thinks this is Schedule A.
        $form = get_add_grade_form::execute($this->course->id, $this->gradeitemidassign22, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $this->assertTrue($form['usescale']);
        $this->assertEquals(22, $form['grademax']);

        // Check that capture page now shows correct data and statuses.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign22, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['showconversion']);
    }

}
