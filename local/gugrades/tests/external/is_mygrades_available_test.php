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
 * Test is_mygrades_available web service
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
final class is_mygrades_available_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Check for the default setup which only has two participants
     *
     * @covers \local_gugrades\external\is_mygrades_available::execute
     */
    public function test_available_for_small_number(): void {

        // Check for default course.
        $available = is_mygrades_available::execute($this->course->id);
        $available = external_api::clean_returnvalue(
            is_mygrades_available::execute_returns(),
            $available
        );

        // Check data returned.
        $this->assertTrue($available['available']);
    }

    /**
     * Check after adding huge numbers of enrollments.
     * This is a bit slow - sorry :(
     *
     * @covers \local_gugrades\external\is_mygrades_available::execute
     */
    public function test_available_for_large_number(): void {

        // Add a LOT of students to the course.
        $idnumber = 1111111;
        for ($i = 0; $i <= 500; $i++) {
            $student = $this->getDataGenerator()->create_user(['idnumber' => $idnumber++]);
            $this->getDataGenerator()->enrol_user($student->id, $this->course->id, 'student');
        }

        // Reduce the setting.
        set_config('maxparticipants', 250, 'local_gugrades');

        // Check for default course.
        $available = is_mygrades_available::execute($this->course->id);
        $available = external_api::clean_returnvalue(
            is_mygrades_available::execute_returns(),
            $available
        );

        // Check data returned.
        $this->assertFalse($available['available']);
    }
}
