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
 * Test releasing grades from capture page
 * ESPECIALLY all the crap to do with Assignment (Workflow)
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
 * Test get_dashboard_enabled web service.
 */
final class capture_release_grades_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Check the enabled state under different circumstances
     *
     * @covers \local_gugrades\external\get_dashboard_enabled::execute
     */
    public function test_release_assignment(): void {
        global $DB;

        // Use the test teacher.
        $this->setUser($this->teacher->id);

        // MyGrades is enabled by releasing grades for a course.
        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];
        $this->import_grades($this->course->id, $this->gradeitemidassign2, $userlist);

        // Get the capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('A3:20', $fred['grades'][0]['displaygrade']);
        $this->assertEquals('FIRST', $fred['grades'][0]['gradetype']);
        $this->assertEquals('A3:20', $fred['grades'][1]['displaygrade']);
        $this->assertEquals('PROVISIONAL', $fred['grades'][1]['gradetype']);

        // Check student mygrades API returns correct data.
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $this->gradecatsumm->id, $this->student->id);

        $this->assertEquals('Assignment 2', $user->fields[2]['itemname']);
        $this->assertFalse($user->fields[2]['released']);

        $status = release_grades::execute($this->course->id, $this->gradeitemidassign2, 0, false);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Get the capture page - post release.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign2, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('A3:20', $fred['grades'][0]['displaygrade']);
        $this->assertEquals('FIRST', $fred['grades'][0]['gradetype']);
        $this->assertEquals('A3:20', $fred['grades'][1]['displaygrade']);
        $this->assertEquals('RELEASED', $fred['grades'][1]['gradetype']);
        $this->assertEquals('A3:20', $fred['grades'][2]['displaygrade']);
        $this->assertEquals('PROVISIONAL', $fred['grades'][2]['gradetype']);

        // Check student mygrades API returns correct data.
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $this->gradecatsumm->id, $this->student->id);

        $this->assertEquals('Assignment 2', $user->fields[2]['itemname']);
        $this->assertTrue($user->fields[2]['released']);

    }
}
