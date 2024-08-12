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
 * Test write_column web service
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
final class write_column_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Test that a new column appears in the database
     *
     * @covers \local_gugrades\external\write_column::execute
     */
    public function test_new_column(): void {
        global $DB;

        // Log in as teacher.
        $this->setUser($this->teacher);

        $nullreturn = write_column::execute($this->course->id, $this->gradeitemidassign1, 'AGREED', '', '', true);
        $nullreturn = external_api::clean_returnvalue(
            write_column::execute_returns(),
            $nullreturn
        );

        $columns = array_values($DB->get_records('local_gugrades_column'));
        $this->assertCount(1, $columns);
        $this->assertEquals('AGREED', $columns[0]->gradetype);

        // Check that trying to add it again doesn't create a new column.
        $nullreturn = write_column::execute($this->course->id, $this->gradeitemidassign1, 'AGREED', '', '', true);
        $nullreturn = external_api::clean_returnvalue(
            write_column::execute_returns(),
            $nullreturn
        );

        $columns = array_values($DB->get_records('local_gugrades_column'));
        $this->assertCount(1, $columns);
        $this->assertEquals('AGREED', $columns[0]->gradetype);
    }

}
