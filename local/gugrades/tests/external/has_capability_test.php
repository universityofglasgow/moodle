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
 * Test has_capability web service
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
 * Test has_capability web service.
 */
final class has_capability_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Check that a teacher has the capability that they should have
     *
     * @covers \local_gugrades\external\has_capability::execute
     */
    public function test_teacher_has_capability(): void {

        // Logged in user is teacher.
        // Check that they have local/gugrades:view capability.
        $hascapability = has_capability::execute($this->course->id, 'local/gugrades:view');
        $hascapability = external_api::clean_returnvalue(
            has_capability::execute_returns(),
            $hascapability
        );

        // Check data returned.
        $this->assertArrayHasKey('hascapability', $hascapability);
        $this->assertEquals(true, $hascapability['hascapability']);
    }

    /**
     * Check that a student does NOT have the capability
     *
     * @covers \local_gugrades\external\has_capability::execute
     */
    public function test_student_has_not_capability(): void {

        // Log in as student.
        $this->setUser($this->student);

        // Check that they do not have local/gugrades:view capability.
        $hascapability = has_capability::execute($this->course->id, 'local/gugrades:view');
        $hascapability = external_api::clean_returnvalue(
            has_capability::execute_returns(),
            $hascapability
        );

        // Check data returned.
        $this->assertArrayHasKey('hascapability', $hascapability);
        $this->assertEquals(false, $hascapability['hascapability']);
    }
}
