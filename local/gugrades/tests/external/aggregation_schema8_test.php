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
 * Tests combinations of admin grades at 2nd level (MGU-726)
 * ...and how the result propogate up to Level 1
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
final class aggregation_schema8_test extends \local_gugrades\external\gugrades_aggregation_testcase {

    /**
     * @var object $gradecatsummer
     */
    protected object $gradecatsummer;

    /**
     * @var object $gradecatsummative
     */
    protected object $gradecatsummative;

    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();

        // Install test schema.
        $this->gradeitemids = $this->load_schema('schema8');

        // Get the grade category 'Summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

        // Get the grade category 'Summer exam'.
        $this->gradecatsummer = $DB->get_record('grade_categories', ['fullname' => 'Summer exam'], '*', MUST_EXIST);

    }

    /**
     * Test simple weighted mean with admin grades in Summer axam
     * This tests NS result.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_admin_grades(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data8a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Update droplow.
        // This won't update aggregation.
        $category = $DB->get_record('grade_categories', ['id' => $this->gradecatsummer->id], '*', MUST_EXIST);
        $category->droplow = 0;
        $DB->update_record('grade_categories', $category);

        // Set NS for question 3.
        // This will.
        $this->apply_admingrade('Question 3', $this->student->id, 'NS');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("CW", $fred['displaygrade']);
        $this->assertEquals(0.0, $fred['rawgrade']);
        $this->assertEquals(66.667, $fred['completed']);

        // Change item 1 to an MV
        $this->apply_admingrade('Item 1', $this->student->id, 'MV');

        // This should result in a GCW (MGU-1009)
        // Superceded by MGU-1210
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals("MV", $fred['displaygrade']);
        $this->assertEquals(0.0, $fred['rawgrade']);
        $this->assertEquals(33.333, $fred['completed']);

        // Change question 3 to 07 admingrade
        $this->apply_admingrade('Question 3', $this->student->id, '07');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );
    }

    /**
     * Test completion with 0 grades
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_completion_with_h0(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data8c', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Update droplow.
        // This won't update aggregation.
        $category = $DB->get_record('grade_categories', ['id' => $this->gradecatsummer->id], '*', MUST_EXIST);
        $category->droplow = 0;
        $DB->update_record('grade_categories', $category);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals(100, $fred['completed']);
    }

}
