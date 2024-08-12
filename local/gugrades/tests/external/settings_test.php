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
 * Test save_settings and get_settings
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
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_advanced_testcase.php');

/**
 * Test(s) for (both) save_settings and get_settings webservices
 */
final class settings_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Just check that strings are returned
     *
     * @covers \local_gugrades\external\save_settings::execute
     * @covers \local_gugrades\external\get_settings::execute
     */
    public function test_write_and_read_settings(): void {
        global $DB;

        // Test settings to apply.
        $settings = [
            [
                'name' => 'nameone',
                'value' => 'value1',
            ],
            [
                'name' => 'nametwo',
                'value' => 'value2',
            ],
        ];

        // Write settings using WS.
        save_settings::execute($this->course->id, 0, $settings);

        // Check that they ended up in the database.
        $setting1 = $DB->get_record('local_gugrades_config', ['courseid' => $this->course->id, 'name' => 'nameone']);
        $this->assertIsObject($setting1);
        $this->assertEquals('value1', $setting1->value);
        $setting2 = $DB->get_record('local_gugrades_config', ['courseid' => $this->course->id, 'name' => 'nametwo']);
        $this->assertIsObject($setting2);
        $this->assertEquals('value2', $setting2->value);

        // Check again reading back using WS.
        $wssettings = get_settings::execute($this->course->id, 0);
        $wssettings = external_api::clean_returnvalue(
            get_settings::execute_returns(),
            $wssettings
        );
        $this->assertIsArray($wssettings);
        $this->assertEquals('nameone', $wssettings[0]['name']);
        $this->assertEquals('value1', $wssettings[0]['value']);
        $this->assertEquals('nametwo', $wssettings[1]['name']);
        $this->assertEquals('value2', $wssettings[1]['value']);
    }

    /**
     * Check that a non-teacher role cannot write settings
     *
     * @covers \local_gugrades\external\save_settings::execute
     */
    public function test_non_teacher(): void {

        $this->setUser($this->student);

        // Test settings to apply.
        $settings = [
            [
                'name' => 'nameone',
                'value' => 'value1',
            ],
            [
                'name' => 'nametwo',
                'value' => 'value2',
            ],
        ];

        // Write settings using WS.
        $this->expectException('required_capability_exception');
        save_settings::execute($this->course->id, 0, $settings);
    }
}
