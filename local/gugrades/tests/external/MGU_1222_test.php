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
final class MGU_1222_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Import and then release Assignment grades
     *
     * @covers \local_gugrades\external\import_grades_users::execute
     */
    public function test_assignment_release(): void {
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

        // Check for grades in Moodle Gradebook
        // There should be none as nothing released
        $mgrades = $DB->get_records('grade_grades', ['userid' => $this->student->id]);
        $this->assertCount(0, $mgrades);

        // Release grade for $assignment2
        $status = release_grades::execute($this->course->id, $this->gradeitemidassign2, 0, false);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Check the grade is released in MyGrades grade table.
        $grades = array_values($DB->get_records('local_gugrades_grade', [
            'gradeitemid' => $this->gradeitemidassign2,
            'userid' => $this->student->id,
        ]));
        $this->assertEquals('RELEASED', $grades[1]->gradetype);
        $this->assertEquals('A3:20', $grades[1]->displaygrade);
        $this->assertEquals('1', $grades[1]->iscurrent);

        // Check assignment user flags
        $flags = array_values($DB->get_records('assign_user_flags', ['userid' => $this->student->id]));
        $this->assertEquals('released', $flags[0]->workflowstate);

        // Check for grades in Moodle Gradebook
        // Grades should have been copied to the Gradebook
        $mgrades = array_values($DB->get_records('grade_grades', ['userid' => $this->student->id, 'itemid' => $this->gradeitemidassign2]));
        $this->assertEquals('21.00000', $mgrades[0]->finalgrade);
        $this->assertEquals('Your work is terrible', $mgrades[0]->feedback);

        // (un)release grade for $assignment2
        $status = release_grades::execute($this->course->id, $this->gradeitemidassign2, 0, true);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Check the grade is (un)released in MyGrades grade table.
        $grades = array_values($DB->get_records('local_gugrades_grade', [
            'gradeitemid' => $this->gradeitemidassign2,
            'userid' => $this->student->id,
        ]));
        $this->assertEquals('RELEASED', $grades[0]->gradetype);
        $this->assertEquals('A3:20', $grades[0]->displaygrade);
        $this->assertEquals('0', $grades[0]->iscurrent);

        // Check assignment user flags
        $flags = array_values($DB->get_records('assign_user_flags', ['userid' => $this->student->id]));
        $this->assertEquals('readyforrelease', $flags[0]->workflowstate);

        // Check for grades in Moodle Gradebook
        // Grades should have been removed from the Gradebook
        $mgrades = array_values($DB->get_records('grade_grades', ['userid' => $this->student->id, 'itemid' => $this->gradeitemidassign2]));
        $this->assertNull($mgrades[0]->finalgrade);

    }

}