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
 * Test functions around get_aggregation_page
 * Schema2 tests the "75% rule" - we check the displayed grade when completion
 * is less than and greater than 75%
 * Also tests all aggregation strategies for scales
 * @package    local_gugrades
 * @copyright  2025
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
 * Test importing more than once into same grad item.
 */
final class import_reload_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * @var object $gradecatsummative
     */
    protected object $gradecatsummative;

    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();
    }

    /**
     * Import then import again and so on. 
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_import_sequence(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        $userlist = [
            $this->student->id,
            //$this->student2->id,
        ];

        // Assign2 (which is useing scale).
        $this->import_grades($this->course->id, $this->gradeitemidassign2, $userlist);

        // Get capture page to demonstrate it worked.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Check for first grades
        $fred = $page['users'][0];
        $this->assertEquals('FIRST', $fred['grades'][0]['gradetype']);
        $this->assertEquals('A3:20', $fred['grades'][0]['displaygrade']);

        // Do the same thing again. 
        $this->import_grades($this->course->id, $this->gradeitemidassign2, $userlist, '', 'SECOND');

        // Get capture page to demonstrate it worked.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Check for second grades
        $fred = $page['users'][0];
        $this->assertEquals('FIRST', $fred['grades'][0]['gradetype']);
        $this->assertEquals('A3:20', $fred['grades'][0]['displaygrade']);
        $this->assertEquals('SECOND', $fred['grades'][1]['gradetype']);
        $this->assertEquals('A3:20', $fred['grades'][1]['displaygrade']);

        // Add admin grade for Fred. 
        $nothing = write_additional_grade::execute(
            courseid:          $this->course->id,
            gradeitemid:       $this->gradeitemidassign2,
            userid:            $this->student->id,
            reason:            'SECOND',
            other:             '',
            admingrade:        'GOODCAUSE_FO',
            scale:             0,
            grade:             0,
            notes:             'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get capture page to demonstrate it worked.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Check admin grade has 'stuck'.
        $fred = $page['users'][0];
        $this->assertEquals('PROVISIONAL', $fred['grades'][2]['gradetype']);
        $this->assertEquals('EC', $fred['grades'][2]['displaygrade']);

        // Re-import but only if valid missing grade
        $this->import_grades(
            courseid:           $this->course->id,
            gradeitemid:        $this->gradeitemidassign2,
            userlist:           $userlist,
            fillns:             '',
            reason:             'THIRD',
            importadditional:   'missing'
        );

        // Get capture page to demonstrate it worked.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Should not have changed as overwriting admin grade is not allowed.
        $fred = $page['users'][0];
        $this->assertEquals('PROVISIONAL', $fred['grades'][2]['gradetype']);
        $this->assertEquals('EC', $fred['grades'][2]['displaygrade']);

        // Re-import but this time allow admin grades.
        $this->import_grades(
            courseid:           $this->course->id,
            gradeitemid:        $this->gradeitemidassign2,
            userlist:           $userlist,
            fillns:             '',
            reason:             'AGREED',
            importadditional:   'admin'
        );

        // Get capture page to demonstrate it worked.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('AGREED', $fred['grades'][2]['gradetype']);
        $this->assertEquals('A3:20', $fred['grades'][2]['displaygrade']);
        $this->assertEquals('PROVISIONAL', $fred['grades'][3]['gradetype']);
        $this->assertEquals('A3:20', $fred['grades'][3]['displaygrade']);
    }

 
}
