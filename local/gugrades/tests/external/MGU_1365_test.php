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
 * Test re-importing grades into 'other' gradetype
 * See bug report MGU-1365
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
 * Test importing grades multiple times into 'other'
 */
final class MGU_1365_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Import grades
     *
     * @covers \local_gugrades\external\import_grades_users::execute
     */
    public function test_import_to_other(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Import users for first time.
        $status = import_grades_users::execute(
            courseid:       $this->course->id, 
            gradeitemid:    $this->gradeitemidassign1, 
            additional:     'update', 
            fillns:         '',
            reason:         'FIRST',
            other:          '',
            dryrun:         false,
            userlist:       $userlist
        );
        $status = external_api::clean_returnvalue(
            import_grades_users::execute_returns(),
            $status
        );

        // Get the capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign1, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertCount(2, $fred['grades']);
        $this->assertEquals('95.5', $fred['grades'][0]['displaygrade']);
        $this->assertEquals('FIRST', $fred['grades'][0]['gradetype']);
        $this->assertEquals('95.5', $fred['grades'][1]['displaygrade']);
        $this->assertEquals('PROVISIONAL', $fred['grades'][1]['gradetype']);

        // Import again, this time to a (new) "other" column.
        $status = import_grades_users::execute(
            courseid:       $this->course->id, 
            gradeitemid:    $this->gradeitemidassign1, 
            additional:     'update', 
            fillns:         '',
            reason:         'OTHER',
            other:          'new column',
            dryrun:         false,
            userlist:       $userlist
        );
        $status = external_api::clean_returnvalue(
            import_grades_users::execute_returns(),
            $status
        );

        // Get the capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign1, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertCount(3, $fred['grades']);
        $this->assertEquals('95.5', $fred['grades'][0]['displaygrade']);
        $this->assertEquals('FIRST', $fred['grades'][0]['gradetype']);
        $this->assertEquals('95.5', $fred['grades'][1]['displaygrade']);
        $this->assertEquals('OTHER', $fred['grades'][1]['gradetype']);
        $this->assertEquals('95.5', $fred['grades'][2]['displaygrade']);
        $this->assertEquals('PROVISIONAL', $fred['grades'][2]['gradetype']);

        $columns = $page['columns'];
        $this->assertEquals('OTHER', $columns[1]['gradetype']);
        $this->assertEquals('new column', $columns[1]['other']);

        // Change grade for Assignment 1.
        $this->update_assignment_grade($this->assign1->id, $this->student->id, 42.3);

        // Get list of gradetypes.
        $data = get_gradetypes::execute($this->course->id, $this->gradeitemidassign1);
        $data = external_api::clean_returnvalue(
            get_gradetypes::execute_returns(),
            $data
        );

        $gradetypes = $data['gradetypes'];
        $this->assertCount(10, $gradetypes);
        $this->assertEquals('new column', $gradetypes[9]['label']);

        // We need the reason for the added column.
        $newreason = $gradetypes[9]['value'];

        // Re-import this grade to existing other grade.
        // It SHOULD NOT create a new column.
        $status = import_grades_users::execute(
            courseid:       $this->course->id, 
            gradeitemid:    $this->gradeitemidassign1, 
            additional:     'update', 
            fillns:         '',
            reason:         $newreason,
            other:          '',
            dryrun:         false,
            userlist:       $userlist
        );
        $status = external_api::clean_returnvalue(
            import_grades_users::execute_returns(),
            $status
        );

        // Get the capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign1, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertCount(3, $fred['grades']);
        $this->assertEquals('95.5', $fred['grades'][0]['displaygrade']);
        $this->assertEquals('FIRST', $fred['grades'][0]['gradetype']);
        $this->assertEquals('42.3', $fred['grades'][1]['displaygrade']);
        $this->assertEquals($newreason, $fred['grades'][1]['gradetype']);
        $this->assertEquals('42.3', $fred['grades'][2]['displaygrade']);
        $this->assertEquals('PROVISIONAL', $fred['grades'][2]['gradetype']);
    }
}
