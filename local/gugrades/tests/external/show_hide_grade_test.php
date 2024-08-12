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
 * Test show_hide_grade web service
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
 * Test has_capability web service.
 */
final class show_hide_grade_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Test that setting and clearing condition works
     *
     * @covers \local_gugrades\external\show_hide_grade::execute
     */
    public function test_show_hide(): void {

        // Log in as teacher.
        $this->setUser($this->teacher);

        // Mark as hidden.
        $nullreturn = show_hide_grade::execute($this->course->id, $this->gradeitemidassign1, $this->student->id, true);
        $nullreturn = external_api::clean_returnvalue(
            show_hide_grade::execute_returns(),
            $nullreturn
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign1, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Test flagged as hidden.
        $fred = $page['users'][0];
        $this->assertTrue($fred['gradehidden']);

        // Mark as shown.
        $nullreturn = show_hide_grade::execute($this->course->id, $this->gradeitemidassign1, $this->student->id, false);
        $nullreturn = external_api::clean_returnvalue(
            show_hide_grade::execute_returns(),
            $nullreturn
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign1, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Test flagged as NOT hidden.
        $fred = $page['users'][0];
        $this->assertFalse($fred['gradehidden']);
    }

}
