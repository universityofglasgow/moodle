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
 * Test get_groups web service
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
 * Test get_activities web service.
 */
final class get_groups_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Check for no groups and some groups
     *
     * @covers \local_gugrades\external\get_groups::execute
     */
    public function test_get_groups(): void {
        global $DB;

        // There should not be any groups (yet).
        $groups = get_groups::execute($this->course->id);
        $groups = external_api::clean_returnvalue(
            get_groups::execute_returns(),
            $groups
        );

        $this->assertIsArray($groups);
        $this->assertEmpty($groups);

        // Create some groups in the course.
        $courseid = $this->course->id;
        $this->getDataGenerator()->create_group(['courseid' => $courseid]);
        $this->getDataGenerator()->create_group(['courseid' => $courseid]);
        $this->getDataGenerator()->create_group(['courseid' => $courseid]);

        // Check we can 'see' the groups we just created.
        $groups = get_groups::execute($this->course->id);
        $groups = external_api::clean_returnvalue(
            get_groups::execute_returns(),
            $groups
        );

        $this->assertIsArray($groups);
        $this->assertCount(3, $groups);
        $this->assertIsInt($groups[1]['id']);
        $this->assertEquals('group-0003', $groups[2]['name']);
    }
}
