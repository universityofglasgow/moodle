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
final class is_grades_imported_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Check that a top=level activiy shows recursiveavailable = true
     * Note this functionality changed MGU-1103
     *
     * @covers \local_gugrades\external\is_grades_imported::execute
     */
    public function test_recursiveavailable_false(): void {

        // Log in as teacher.
        $this->setUser($this->teacher);

        $gradesimported = is_grades_imported::execute($this->course->id, $this->gradeitemidassign1, 0);
        $gradesimported = external_api::clean_returnvalue(
            is_grades_imported::execute_returns(),
            $gradesimported
        );

        // Check recursiveavailable field.
        $this->assertArrayHasKey('recursiveavailable', $gradesimported);
        $this->assertTrue($gradesimported['recursiveavailable']);

        // Check recursivematch field.
        $this->assertArrayHasKey('recursivematch', $gradesimported);
        $this->assertFalse($gradesimported['recursivematch']);
    }

    /**
     * Check that a top=level activiy shows recursiveavailable = true
     *
     * @covers \local_gugrades\external\is_grades_imported::execute
     */
    public function test_recursiveavailable_true(): void {

        // Log in as teacher.
        $this->setUser($this->teacher);

        $gradesimported = is_grades_imported::execute($this->course->id, $this->gradeitemsecond1, 0);
        $gradesimported = external_api::clean_returnvalue(
            is_grades_imported::execute_returns(),
            $gradesimported
        );

        // Check recursiveavailable field.
        $this->assertArrayHasKey('recursiveavailable', $gradesimported);
        $this->assertTrue($gradesimported['recursiveavailable']);

        // Check recursivematch field.
        $this->assertArrayHasKey('recursivematch', $gradesimported);
        $this->assertTrue($gradesimported['recursivematch']);

        // Check all grades valid.
        $this->assertArrayHasKey('allgradesvalid', $gradesimported);
        $this->assertTrue($gradesimported['allgradesvalid']);
    }


    /**
     * Check introducing an invalid gradetype into the recursive set
     *
     * @covers \local_gugrades\external\is_grades_imported::execute
     */
    public function test_recursiveavailable_bad_gradetype(): void {
        global $DB;

        // Log in as teacher.
        $this->setUser($this->teacher);

        // Final item has an invalid grade type
        // Just being there is the thing.
        $seconditemx = $this->getDataGenerator()->create_grade_item(
            ['courseid' => $this->course->id, 'gradetype' => GRADE_TYPE_TEXT, 'fullname' => 'Second item XX']
        );
        $this->move_gradeitem_to_category($seconditemx->id, $this->gradecatsecond->id);

        $gradesimported = is_grades_imported::execute($this->course->id, $this->gradeitemsecond2, 0);
        $gradesimported = external_api::clean_returnvalue(
            is_grades_imported::execute_returns(),
            $gradesimported
        );

        // Check recursiveavailable field.
        $this->assertArrayHasKey('recursiveavailable', $gradesimported);
        $this->assertTrue($gradesimported['recursiveavailable']);

        // Check recursivematch field.
        $this->assertArrayHasKey('recursivematch', $gradesimported);
        $this->assertFalse($gradesimported['recursivematch']);

        // Check all grades valid.
        $this->assertArrayHasKey('allgradesvalid', $gradesimported);
        $this->assertFalse($gradesimported['allgradesvalid']);
    }
}
