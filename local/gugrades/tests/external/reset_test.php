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
 * Test reset web service
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
final class reset_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Import first grades.
     *
     * @covers \local_gugrades\external\import_grades_users::execute
     */
    public function test_reset(): void {
        global $DB;

        // Use the test student.
        $studentid = $this->student->id;
        $courseid = $this->course->id;
        $this->setUser($this->teacher->id);

        // Summative grade category.
        $gradecategoryid = $this->gradecatsumm->id;

        // Release grades for this grade item -assignment1.
        $mapping1 = \local_gugrades\grades::mapping_factory($this->course->id, $this->gradeitemidassign1);
        $activity = \local_gugrades\users::activity_factory($this->gradeitemidassign1, $this->course->id, 0);
        \local_gugrades\api::import_grade(
            $this->course->id, $this->gradeitemidassign1, $mapping1, $activity, $studentid, false, false);
        \local_gugrades\api::release_grades($this->course->id, $this->gradeitemidassign1, 0, false);

        // Release grades for this grade item - assignment2.
        $mapping2 = \local_gugrades\grades::mapping_factory($this->course->id, $this->gradeitemidassign2);
        $activity = \local_gugrades\users::activity_factory($this->gradeitemidassign2, $this->course->id, 0);
        \local_gugrades\api::import_grade(
            $this->course->id, $this->gradeitemidassign2, $mapping2, $activity, $studentid, false, false);
        \local_gugrades\api::release_grades($this->course->id, $this->gradeitemidassign2, 0, false);

        // Check that we have data in the grades and columns table for this course.
        $grades = $DB->get_records('local_gugrades_grade', ['courseid' => $courseid]);
        $this->assertCount(6, $grades);
        $columns = $DB->get_records('local_gugrades_column', ['courseid' => $courseid]);
        $this->assertCount(8, $columns);

        // Perform reset.
        $nullreturn = reset::execute($courseid);
        $nullreturn = external_api::clean_returnvalue(
            reset::execute_returns(),
            $nullreturn
        );

        // Check grades and columns tables again to make sure they have been deleted.
        $grades = $DB->get_records('local_gugrades_grade', ['courseid' => $courseid]);
        $this->assertCount(0, $grades);
        $columns = $DB->get_records('local_gugrades_column', ['courseid' => $courseid]);
        $this->assertCount(0, $columns);
    }
}
