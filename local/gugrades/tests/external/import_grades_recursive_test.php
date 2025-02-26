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
 * Test import_grades_recursive web service
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;
use local_gugrades\external\import_grades_recursive;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_advanced_testcase.php');

/**
 * Test import_grades_recursive web service.
 */
final class import_grades_recursive_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Import first grades.
     *
     * @covers \local_gugrades\external\import_grades_recursive::execute
     */
    public function test_import_no_grades(): void {
        global $DB;

        // Check gradeitemsecond1 (no grades assigned so shouldn't return anything).
        $counts = import_grades_recursive::execute($this->course->id, $this->gradeitemsecond1, 0, false, false);
        $counts = external_api::clean_returnvalue(
            import_grades_recursive::execute_returns(),
            $counts
        );

        $this->assertEquals(2, $counts['itemcount']);
        $this->assertEquals(0, $counts['gradecount']);
    }

    /**
     * Add some grades and see if they import
     *
     * @covers \local_gugrades\external\import_grades_recursive::execute
     */
    public function test_import_with_grades(): void {
        global $DB;

        // Note to self:
        // Manual grades cannot have raw grades, that only applies to 'external'
        // grades (e.g. mod_assign). So, don't try to use update_raw_grade().
        $gradeitem = \grade_item::fetch(['id' => $this->gradeitemsecond1]);
        $gradeitem->update_final_grade($this->student->id, 16.7);

        $gradeitem = \grade_item::fetch(['id' => $this->gradeitemsecond2]);
        $gradeitem->update_final_grade($this->student->id, 48.5);

        // This time we should get those two grades.
        $counts = import_grades_recursive::execute($this->course->id, $this->gradeitemsecond1, 0, false, false);
        $counts = external_api::clean_returnvalue(
            import_grades_recursive::execute_returns(),
            $counts
        );

        // Two grade items and two grades.
        $this->assertEquals(2, $counts['itemcount']);
        $this->assertEquals(2, $counts['gradecount']);

        // Get the capture page
        $page = get_capture_page::execute($this->course->id, $this->gradeitemsecond1, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Check that they have been imported.
        $grades = $DB->get_records('local_gugrades_grade', ['courseid' => $this->course->id]);
        $grades = array_values($grades);

        $this->assertIsArray($grades);
        $this->assertCount(6, $grades);

        $this->assertEquals('16.7', $grades[4]->displaygrade);
        $this->assertEquals(16.7000, $grades[4]->rawgrade);
        $this->assertEquals('FIRST', $grades[4]->gradetype);
        $this->assertEquals(1, $grades[4]->iscurrent);

        $this->assertEquals('48.5', $grades[5]->displaygrade);
        $this->assertEquals(48.5000, $grades[5]->rawgrade);
        $this->assertEquals('FIRST', $grades[5]->gradetype);
        $this->assertEquals(1, $grades[5]->iscurrent);

        $this->assertEquals('CATEGORY', $grades[2]->gradetype);
        $this->assertEquals('32.6', $grades[2]->displaygrade);
    }
}
