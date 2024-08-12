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
 * Test get_activities web service
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
final class get_activities_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Just check that strings are returned
     *
     * @covers \local_gugrades\external\get_activities::execute
     */
    public function test_get_activities(): void {
        global $DB;

        // Test ws function.
        $tree = get_activities::execute($this->course->id, $this->gradecatsumm->id);
        $tree = external_api::clean_returnvalue(
            get_activities::execute_returns(),
            $tree
        );

        // Array item $tree['activities'] should be valid JSON.
        $this->assertArrayHasKey('activities', $tree);
        $this->assertJson($tree['activities']);

        // JSON contents.
        $data = json_decode($tree['activities']);

        // Should be an object - check category.
        $this->assertIsObject($data);
        $this->assertObjectHasProperty('category', $data);
        $category = $data->category;
        $this->assertObjectHasProperty('fullname', $category);
        $fullname = $category->fullname;
        $this->assertEquals('Summative', $fullname);

        // Check items.
        $this->assertObjectHasProperty('items', $data);
        $items = $data->items;
        $this->assertIsArray($items);
        $this->assertCount(6, $items);
        $readassign1 = $items[0];
        $this->assertIsObject($readassign1);
        $this->assertObjectHasProperty('itemname', $readassign1);
        $this->assertEquals('Assignment 1', $readassign1->itemname);
    }
}
