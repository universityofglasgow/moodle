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
 * Test get_dashboard_enabled web service
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
final class get_dashboard_enabled_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Check the enabled state under different circumstances
     *
     * @covers \local_gugrades\external\get_dashboard_enabled::execute
     */
    public function test_get_dashboard_enabled_states(): void {
        global $DB;

        // Use the test teacher.
        $this->setUser($this->teacher->id);

        // Check current courses. MyGrades should not be enabled.
        $enabled = get_dashboard_enabled::execute($this->course->id);
        $enabled = external_api::clean_returnvalue(
            get_dashboard_enabled::execute_returns(),
            $enabled
        );

        $this->assertFalse($enabled['enabled']);
        $this->assertFalse($enabled['gradesreleased']);

        // MyGrades is enabled by releasing grades for a course.
        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];
        $status = import_grades_users::execute($this->course->id, $this->gradeitemidassign2, false, false, $userlist);
        $status = external_api::clean_returnvalue(
            import_grades_users::execute_returns(),
            $status
        );
        $status = release_grades::execute($this->course->id, $this->gradeitemidassign2, 0, false);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Check current courses. MyGrades should now be enabled.
        $enabled = get_dashboard_enabled::execute($this->course->id);
        $enabled = external_api::clean_returnvalue(
            get_dashboard_enabled::execute_returns(),
            $enabled
        );

        $this->assertTrue($enabled['enabled']);
        $this->assertTrue($enabled['gradesreleased']);

        // Switch off the course.
        $this->disable_dashboard($this->course->id, true);

        // Check once more. MyGrades should not be enabled.
        $enabled = get_dashboard_enabled::execute($this->course->id);
        $enabled = external_api::clean_returnvalue(
            get_dashboard_enabled::execute_returns(),
            $enabled
        );

        $this->assertFalse($enabled['enabled']);
        $this->assertTrue($enabled['gradesreleased']);
    }
}
