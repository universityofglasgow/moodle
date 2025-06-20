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
 * Test get_gradetypes web service
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
 * Test import_grades_users web service.
 */
final class get_capture_cell_form_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Grade item with points
     *
     * @covers \local_gugrades\external\get_capture_cell_form::execute
     */
    public function test_points(): void {
        global $DB;

        // Use the test teacher.
        $this->setUser($this->teacher->id);

        $data = get_capture_cell_form::execute($this->course->id, $this->gradeitemidassign1);
        $data = external_api::clean_returnvalue(
            get_capture_cell_form::execute_returns(),
            $data
        );

        $usescale = $data['usescale'];
        $grademax = $data['grademax'];
        $scalemenu = $data['scalemenu'];
        $admingrades = $data['adminmenu'];

        $this->assertFalse($usescale);
        $this->assertEquals(100, $grademax);
        $this->assertCount(0, $scalemenu);
        $this->assertGreaterThan(0, count($admingrades));
    }

    /**
     * Grade item with scale
     *
     * @covers \local_gugrades\external\get_capture_cell_form::execute
     */
    public function test_scale(): void {
        global $DB;

        // Use the test teacher.
        $this->setUser($this->teacher->id);

        $data = get_capture_cell_form::execute($this->course->id, $this->gradeitemidassign2);
        $data = external_api::clean_returnvalue(
            get_capture_cell_form::execute_returns(),
            $data
        );

        $usescale = $data['usescale'];
        $grademax = $data['grademax'];
        $scalemenu = $data['scalemenu'];
        $admingrades = $data['adminmenu'];

        $this->assertTrue($usescale);
        $this->assertEquals(0, $grademax);
        $this->assertCount(24, $scalemenu);
        $this->assertGreaterThan(0, count($admingrades));;
        $this->assertEquals('H:0', $scalemenu[22]['label']);
    }
}
