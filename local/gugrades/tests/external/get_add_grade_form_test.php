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
 * Test get_add_grade_form web service
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
 * Test get_add_grade_form web service.
 */
final class get_add_grade_form_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Check that expected form structure is returned
     *
     * @covers \local_gugrades\external\get_add_grade_form::execute
     */
    public function test_get_activities_with_scale(): void {
        global $DB;

        // Test ws function.
        // Check for Assignment2 which uses a scale.
        $form = get_add_grade_form::execute($this->course->id, $this->gradeitemidassign2, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        // Check gradetypes.
        $this->assertArrayHasKey('gradetypes', $form);
        $gradetypes = $form['gradetypes'];
        $this->assertCount(9, $gradetypes);
        $this->assertEquals('SECOND', $gradetypes[0]['value']);
        $this->assertEquals('2nd grade', $gradetypes[0]['label']);
        $this->assertEquals('LATE', $gradetypes[4]['value']);
        $this->assertEquals('Late penalty', $gradetypes[4]['label']);

        // Check itemname.
        $this->assertArrayHasKey('itemname', $form);
        $this->assertEquals('Assignment 2', $form['itemname']);

        // Check fullname.
        $this->assertArrayHasKey('fullname', $form);
        $this->assertNotEmpty($form['fullname']);

        // Check id number.
        $this->assertArrayHasKey('idnumber', $form);
        $this->assertEquals('1234567', $form['idnumber']);

        // Check usescale.
        $this->assertArrayHasKey('usescale', $form);
        $this->assertEquals(true, $form['usescale']);

        // Check grademax.
        $this->assertArrayHasKey('grademax', $form);
        $this->assertEquals(0, $form['grademax']);

        // Check scalemenu.
        $this->assertArrayHasKey('scalemenu', $form);
        $scalemenu = $form['scalemenu'];
        $this->assertCount(23, $scalemenu);
        $this->assertEquals(22, $scalemenu[0]['value']);
        $this->assertEquals('A1:22', $scalemenu[0]['label']);
        $this->assertEquals(12, $scalemenu[10]['value']);
        $this->assertEquals('C3:12', $scalemenu[10]['label']);

        // Check adminmenu.
        $this->assertArrayHasKey('adminmenu', $form);
        $adminmenu = $form['adminmenu'];
        $this->assertGreaterThan(0, count($adminmenu));
        $this->assertEquals('07', $adminmenu[0]['value']);
        $this->assertEquals('07 - Deferred', $adminmenu[0]['label']);
        $this->assertEquals('NS', $adminmenu[1]['value']);
        $this->assertEquals('NS - No Submission', $adminmenu[1]['label']);
        $this->assertEquals('MV0', $adminmenu[2]['value']);
        $this->assertEquals('MV0 - Good cause (non replicable)', $adminmenu[2]['label']);
    }

    /**
     * Check that expected form structure is returned
     *
     * @covers \local_gugrades\external\get_add_grade_form::execute
     */
    public function test_get_activities_with_points(): void {
        global $DB;

        // Test ws function.
        // Check for Assignment1 which uses a 100% points.
        $form = get_add_grade_form::execute($this->course->id, $this->gradeitemidassign1, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        // Check gradetypes.
        $this->assertArrayHasKey('gradetypes', $form);
        $gradetypes = $form['gradetypes'];
        $this->assertCount(9, $gradetypes);
        $this->assertEquals('SECOND', $gradetypes[0]['value']);
        $this->assertEquals('2nd grade', $gradetypes[0]['label']);
        $this->assertEquals('LATE', $gradetypes[4]['value']);
        $this->assertEquals('Late penalty', $gradetypes[4]['label']);

        // Check itemname.
        $this->assertArrayHasKey('itemname', $form);
        $this->assertEquals('Assignment 1', $form['itemname']);

        // Check fullname.
        $this->assertArrayHasKey('fullname', $form);
        $this->assertNotEmpty($form['fullname']);

        // Check id number.
        $this->assertArrayHasKey('idnumber', $form);
        $this->assertEquals('1234567', $form['idnumber']);

        // Check usescale.
        $this->assertArrayHasKey('usescale', $form);
        $this->assertEquals(false, $form['usescale']);

        // Check grademax.
        $this->assertArrayHasKey('grademax', $form);
        $this->assertEquals(100.0, $form['grademax']);

        // Check scalemenu.
        $this->assertArrayHasKey('scalemenu', $form);
        $scalemenu = $form['scalemenu'];
        $this->assertEmpty($scalemenu);

        // Check adminmenu.
        $this->assertArrayHasKey('adminmenu', $form);
        $adminmenu = $form['adminmenu'];
        $this->assertGreaterThan(0, count($adminmenu));
    }

    /**
     * Specific check that multiple 'other' grades are returned
     * properly in the 'gradetypes' field
     * @covers \local_gugrades\external\get_add_grade_form::execute
     */
    public function test_gradetypes_multiple_others(): void {
        global $DB;

        // Insert 'other' gradetype data.
        $courseid = $this->course->id;
        $gradeitemid = $this->gradeitemidassign1;

        $other1 = (object)[
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'gradetype' => 'OTHER',
            'other' => 'Other Variation One',
        ];
        $id1 = $DB->insert_record('local_gugrades_column', $other1);

        $other2 = (object)[
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'gradetype' => 'OTHER',
            'other' => 'Other Variation Two',
        ];
        $id2 = $DB->insert_record('local_gugrades_column', $other2);

        // Test ws function.
        $form = get_add_grade_form::execute($courseid, $gradeitemid, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        // Check gradetypes.
        $this->assertArrayHasKey('gradetypes', $form);
        $gradetypes = $form['gradetypes'];
        $this->assertCount(11, $gradetypes);

        $this->assertEquals('OTHER_' . $id1, $gradetypes[9]['value']);
        $this->assertEquals('Other Variation One', $gradetypes[9]['label']);
        $this->assertEquals('OTHER_' . $id2, $gradetypes[10]['value']);
        $this->assertEquals('Other Variation Two', $gradetypes[10]['label']);
    }


}
