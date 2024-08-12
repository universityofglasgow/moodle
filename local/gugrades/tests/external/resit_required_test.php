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
 * Test resit_required
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
 * Test reset_required web service.
 */
final class resit_required_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Test that setting and clearing condition works
     *
     * @covers \local_gugrades\external\resit_required::execute
     */
    public function test_resit_required(): void {

        // Log in as teacher.
        $this->setUser($this->teacher);

        // Get aggregation page.
        // No resits should be marked.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsumm->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $users = $page['users'];
        $this->assertEquals(false, $users[0]['resitrequired']);

        // Set the resit required flag.
        $nothing = resit_required::execute($this->course->id, $this->student->id, true);
        $nothing = external_api::clean_returnvalue(
            resit_required::execute_returns(),
            $nothing
        );

        // Get aggregation page.
        // Resitresits should now be marked.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsumm->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $users = $page['users'];
        $this->assertEquals(true, $users[0]['resitrequired']);

        // Clear the resit required flag.
        $nothing = resit_required::execute($this->course->id, $this->student->id, false);
        $nothing = external_api::clean_returnvalue(
            resit_required::execute_returns(),
            $nothing
        );

        // Get aggregation page.
        // No resits should be marked.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsumm->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $users = $page['users'];
        $this->assertEquals(false, $users[0]['resitrequired']);
    }

}
