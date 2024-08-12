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
 * Test import_grades_recursive web service
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_advanced_testcase.php');

/**
 * Test for autoload?
 */
final class autoload_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Add some grades and see if they import
     *
     * @covers \local_gugrades\external\import_grades_recursive::execute
     */
    public function test_autoload(): void {
        global $DB;

        $gradeitem = \grade_item::fetch(['id' => $this->gradeitemsecond1]);
        $counts = import_grades_recursive::execute($this->course->id, $this->gradeitemsecond1, 0, false, false);
    }
}
