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
 * Schema2 tests the "75% rule" - we check the displayed grade when completion
 * is less than and greater than 75%
 * Also tests all aggregation strategies for scales
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
final class aggregation_schema2_test extends \local_gugrades\external\gugrades_aggregation_testcase {

    /**
     * @var object $gradecatsummative
     */
    protected object $gradecatsummative;

    /**
     * @var int $mapid
     */
    protected int $mapid;

    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();

        // Install test schema.
        $this->gradeitemids = $this->load_schema('schema2');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

        // Make a conversion map.
        $this->mapid = $this->make_conversion_map();
    }

    /**
     * Test top-level aggregation, Schedule A/B mix.
     * Test no data
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_empty(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(0, $fred['completed']);
        $this->assertEquals("Grades missing", $fred['displaygrade']);
    }

    /**
     * Test top-level aggregation, Schedule A/B mix.
     * Test with data - less than 75% completion
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_below_75(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data2a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummative->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Add admin grades to 'Item 2' and 'Item 4'.
        $this->apply_admingrade('Item 2', $this->student->id, 'GOODCAUSE_FO');
        $this->apply_admingrade('Item 4', $this->student->id, 'GOODCAUSE_FO');

        $grades = $DB->get_records('local_gugrades_grade', ['userid' => $this->student->id]);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(55, $fred['completed']);
        $this->assertEquals("MV", $fred['displaygrade']);
    }

    /**
     * Test top-level aggregation, Schedule A/B mix.
     * Test with data - more than 75% completion
     * Also tests default weighted mean
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_above_75(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data2b', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Add admin grades to 'Item 4'.
        $this->apply_admingrade('Item 4', $this->student->id, 'GOODCAUSE_FO');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(80, $fred['completed']);
        $this->assertEquals("MV", $fred['displaygrade']);
        $this->assertEquals(0.0, $fred['rawgrade']);
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
        $this->load_data('data2c', $this->student->id);

        // Set aggregation strategy.
        // (has to be before import, otherwise there's no re-aggregation)
        $this->set_strategy($this->gradecatsummative->id, \GRADE_AGGREGATE_WEIGHTED_MEAN2);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(100, $fred['completed']);
        $this->assertEquals("D1 (10.5)", $fred['displaygrade']);
        $this->assertEquals(10.5, $fred['rawgrade']);
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
        $this->load_data('data2c', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummative->id, \GRADE_AGGREGATE_MODE);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(100, $fred['completed']);
        $this->assertEquals("A5 (18)", $fred['displaygrade']);
        $this->assertEquals(18.0, $fred['rawgrade']);
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
        $this->load_data('data2c', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummative->id, \GRADE_AGGREGATE_MEDIAN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(100, $fred['completed']);
        $this->assertEquals("D2 (9.5)", $fred['displaygrade']);
        $this->assertEquals(9.5, $fred['rawgrade']);
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
        $this->load_data('data2c', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummative->id, \GRADE_AGGREGATE_MAX);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(100, $fred['completed']);
        $this->assertEquals("A5 (18)", $fred['displaygrade']);
        $this->assertEquals(18.0, $fred['rawgrade']);
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
        $this->load_data('data2c', $this->student->id);

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummative->id, \GRADE_AGGREGATE_MIN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(100, $fred['completed']);
        $this->assertEquals("F1 (5)", $fred['displaygrade']);
        $this->assertEquals(5.0, $fred['rawgrade']);
    }
}
