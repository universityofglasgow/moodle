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
 * Test get_levelonecategories web service
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
 * Test get_levelonecategories web service.
 */
final class get_levelonecategories_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Check correct categories are returned
     *
     * @covers \local_gugrades\external\get_levelonecategories::execute
     */
    public function test_categories_returned(): void {

        $categories = get_levelonecategories::execute($this->course->id);
        $categories = external_api::clean_returnvalue(
            get_levelonecategories::execute_returns(),
            $categories
        );

        // Check data is correct.
        // Formative has no items, so will not be included.
        $this->assertIsArray($categories);
        $this->assertCount(1, $categories);
        $this->assertEquals('Summative', $categories[0]['fullname']);
    }
}
