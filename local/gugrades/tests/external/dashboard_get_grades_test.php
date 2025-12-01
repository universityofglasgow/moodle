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
 * Test dashboard_get_courses web service
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
 * Test get_activities web service.
 */
final class dashboard_get_grades_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Set up some grades for a student and check that they can be read
     *
     * @covers \local_gugrades\external\dashboard_get_grades::execute
     */
    public function test_get_grades(): void {
        global $DB;

        $studentid = $this->student->id;

        // Staff for next bit
        $this->setUser($this->teacher->id);

        // Summative grade category.
        $gradecategoryid = $this->gradecatsumm->id;



        // Need to release grades for this grade item -assignment1.
        $mapping1 = \local_gugrades\grades::mapping_factory($this->course->id, $this->gradeitemidassign1);
        $activity = \local_gugrades\users::activity_factory($this->gradeitemidassign1, $this->course->id, 0);
        \local_gugrades\api::import_grade(
            courseid:       $this->course->id,
            gradeitemid:    $this->gradeitemidassign1,
            mapping:        $mapping1,
            activity:       $activity,
            userid:         $studentid,
            additional:     'update',
            fillns:         'none',
            reason:         'FIRST',
            other:          ''
        );
        \local_gugrades\api::release_grades($this->course->id, $this->gradeitemidassign1, 0, false);

        // Need to release grades for this grade item - assignment2.
        $mapping2 = \local_gugrades\grades::mapping_factory($this->course->id, $this->gradeitemidassign2);
        $activity = \local_gugrades\users::activity_factory($this->gradeitemidassign2, $this->course->id, 0);
        \local_gugrades\api::import_grade(
            courseid:       $this->course->id,
            gradeitemid:    $this->gradeitemidassign2,
            mapping:        $mapping2,
            activity:       $activity,
            userid:         $studentid,
            additional:     'update',
            fillns:         'none',
            reason:         'FIRST',
            other:          ''
        );
        \local_gugrades\api::release_grades($this->course->id, $this->gradeitemidassign2, 0, false);

        // Use the test student.
        $this->setUser($studentid);

        // Get/check grades.
        $grades = dashboard_get_grades::execute($studentid, $gradecategoryid);
        $grades = external_api::clean_returnvalue(
            dashboard_get_grades::execute_returns(),
            $grades
        );

        // Should be two grades returned.
        $this->assertCount(2, $grades['grades']);

        // Check grades look correct.
        $assign1 = $grades['grades'][0];
        $assign2 = $grades['grades'][1];
        $this->assertEquals('Assignment 1', $assign1['itemname']);
        $this->assertEquals(95.5, $assign1['convertedgrade']);
        $this->assertEquals(100, $assign1['grademax']);
        $this->assertEquals('Assignment 2', $assign2['itemname']);
        $this->assertEquals(20, $assign2['convertedgrade']);
        $this->assertEquals('A3:20', $assign2['displaygrade']);
        //$this->assertEquals(23, $assign2['grademax']);

    }

}
