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
 * Test get_all_strings
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;

use externallib_advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Test(s) for get_all_strings webservice
 */
final class get_all_strings_test extends externallib_advanced_testcase {

    /**
     * Called before every test
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Just check that strings are returned
     *
     * @covers \local_gugrades\external\get_all_strings::execute
     * @return void
     */
    public function test_get_all_strings_returns_data(): void {
        $mstrings = get_all_strings::execute();

        // Clean up return values.
        $mstrings = external_api::clean_returnvalue(
            get_all_strings::execute_returns(),
            $mstrings
        );

        // Was something returned?
        $this->assertNotEmpty($mstrings);

        // Check that a few required strings exist.
        $this->assertContains('pluginname', array_column($mstrings, 'tag'));
        $this->assertContains('captureaggregation', array_column($mstrings, 'tag'));
    }
}
