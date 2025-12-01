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
 * Test functions around get_aggregation_page
 * Schema5 tests all the different aggregation strategies for points
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
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_aggregation_testcase.php');

/**
 * More test(s) for get_aggregation_page webservice
 */
final class aggregation_schema5_test extends \local_gugrades\external\gugrades_aggregation_testcase {

    /**
     * @var object $gradecatsummer
     */
    protected object $gradecatsummer;


    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();

        // Install test schema.
        $this->gradeitemids = $this->load_schema('schema5');

        // Get the grade category 'Summer exam'.
        $this->gradecatsummer = $DB->get_record('grade_categories', ['fullname' => 'Summer exam'], '*', MUST_EXIST);

    }

    /**
     * Test weighted mean
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_weighted_mean(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data5a', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("69.66102", $fred['displaygrade']);
        $this->assertEquals(69.66102, $fred['rawgrade']);
    }

    /**
     * Test simple weighted mean
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_simple_weighted_mean(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data5a', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN2);

        foreach ($this->gradeitemids as $gradeitemid) {
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("63.26042", $fred['displaygrade']);
        $this->assertEquals(63.26042, $fred['rawgrade']);
    }

    /**
     * Test mode
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_mode(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data5a', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_MODE);

        foreach ($this->gradeitemids as $gradeitemid) {
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("35", $fred['displaygrade']);
        $this->assertEquals(35.0, $fred['rawgrade']);
    }

    /**
     * Test median
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_median(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data5a', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_MEDIAN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("75.95", $fred['displaygrade']);
        $this->assertEquals(75.95, $fred['rawgrade']);
    }

    /**
     * Test max
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_max(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data5a', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_MAX);

        foreach ($this->gradeitemids as $gradeitemid) {
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("85.6", $fred['displaygrade']);
        $this->assertEquals(85.6, $fred['rawgrade']);
    }

    /**
     * Test min
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_min(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data5a', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_MIN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("35.6", $fred['displaygrade']);
        $this->assertEquals(35.6, $fred['rawgrade']);
    }
}
