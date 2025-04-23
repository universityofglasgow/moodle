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
 * Strategies test checks the various aggregation "strategies"
 * e.g., weighted mean, simple weighted mean and so on
 * Weighted mean is the default and checked all over so we don't
 * bother with that
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
final class aggregation_strategies_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
        $this->gradeitemids = $this->load_schema('schema4');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

    }

    /**
     * Test strategy mean (no weighting)
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_mean(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data4a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get "Summer exam" category.
        $summerexamid = $this->get_grade_category("Summer exam");

        // Switch to mean.
        $this->set_strategy($summerexamid, \GRADE_AGGREGATE_MEAN);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $summerexamid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("85.25926", $fred['displaygrade']);
    }

    /**
     * Test strategy simple weighted mean
     * The maxgrade is also the weight
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
        $this->load_data('data4a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get "Summer exam" category.
        $summerexamid = $this->get_grade_category("Summer exam");

        // Switch to mean.
        $this->set_strategy($summerexamid, \GRADE_AGGREGATE_WEIGHTED_MEAN2);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $summerexamid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("84.62963", $fred['displaygrade']);
    }

    /**
     * Test strategy minimum
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_minimum(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data4a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get "Summer exam" category.
        $summerexamid = $this->get_grade_category("Summer exam");

        // Switch to mean.
        $this->set_strategy($summerexamid, \GRADE_AGGREGATE_MIN);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $summerexamid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("84", $fred['displaygrade']);
    }

    /**
     * Test strategy maximum
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_maximum(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data4a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get "Summer exam" category.
        $summerexamid = $this->get_grade_category("Summer exam");

        // Switch to mean.
        $this->set_strategy($summerexamid, \GRADE_AGGREGATE_MAX);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $summerexamid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("87.03704", $fred['displaygrade']);
    }

    /**
     * Test strategy median
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
        $this->load_data('data4a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get "Summer exam" category.
        $summerexamid = $this->get_grade_category("Summer exam");

        // Switch to mean.
        $this->set_strategy($summerexamid, \GRADE_AGGREGATE_MEDIAN);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $summerexamid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("85", $fred['displaygrade']);
    }

    /**
     * Test strategy mode
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
        $this->load_data('data4a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get "Summer exam" category.
        $summerexamid = $this->get_grade_category("Summer exam");

        // Switch to mean.
        $this->set_strategy($summerexamid, \GRADE_AGGREGATE_MODE);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $summerexamid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("84", $fred['displaygrade']);
    }
}
