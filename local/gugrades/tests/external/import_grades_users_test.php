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
 * Test import_grades_users web service
 * @package    local_gugrades
 * @copyright  2023
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
final class import_grades_users_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Import first grades.
     *
     * @covers \local_gugrades\external\import_grades_users::execute
     */
    public function test_categories_returned(): void {
        global $DB;

        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign2 (which is useing scale).
        $this->import_grades($this->course->id, $this->gradeitemidassign2, $userlist);

        $grades = array_values($DB->get_records('local_gugrades_grade', [
            'gradeitemid' => $this->gradeitemidassign2,
        ]));
        $this->assertCount(2, $grades);
        $this->assertEquals('A3:20', $grades[0]->displaygrade);
        $this->assertEquals('D2:10', $grades[1]->displaygrade);
        $this->assertEquals(20, $grades[0]->convertedgrade);
        $this->assertEquals(10, $grades[1]->convertedgrade);

        // Assign1 (which is useing points).
        $this->import_grades($this->course->id, $this->gradeitemidassign1, $userlist);

        $grades = array_values($DB->get_records('local_gugrades_grade', [
            'gradeitemid' => $this->gradeitemidassign1,
        ]));
        $this->assertCount(2, $grades);
        $this->assertEquals(95.5, $grades[0]->displaygrade);
        $this->assertEquals(33, $grades[1]->displaygrade);
        $this->assertEquals(95.5, $grades[0]->convertedgrade);
        $this->assertEquals(33, $grades[1]->convertedgrade);
    }

    /**
     * Test $fillns parameter when importing
     *
     * @covers \local_gugrades\external\import_grades_users::execute
     */
    public function test_fillns_import(): void {
        global $DB;

        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign4 (which is useing scale).
        $this->import_grades($this->course->id, $this->gradeitemidassign4, $userlist, 'fillns');

        $grades = array_values($DB->get_records('local_gugrades_grade', [
            'gradeitemid' => $this->gradeitemidassign4,
        ]));

        $this->assertCount(2, $grades);
        $this->assertEquals('B1:17', $grades[0]->displaygrade);
        $this->assertEquals('NS', $grades[1]->displaygrade);
    }

    /**
     * Test ScheduleB import
     *
     * @covers \local_gugrades\external\import_grades_users::execute
     */
    public function test_scheduleb_import(): void {
        global $DB;

        $vals = $DB->get_records('local_gugrades_scalevalue');

        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign4 (which is useing scale).
        $this->import_grades($this->course->id, $this->gradeitemidassignb1, $userlist, 'fillns');

        $grades = array_values($DB->get_records('local_gugrades_grade', [
            'gradeitemid' => $this->gradeitemidassignb1,
        ]));

        $this->assertCount(2, $grades);
        $this->assertEquals('C0', $grades[0]->displaygrade);
        $this->assertEquals(14.0, $grades[0]->convertedgrade);
        $this->assertEquals('F0', $grades[1]->displaygrade);
        $this->assertEquals(5.0, $grades[1]->convertedgrade);
    }
}
