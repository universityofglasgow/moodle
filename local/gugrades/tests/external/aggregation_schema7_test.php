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
 * Schema7 tests drop low functionality with scales
 * AND tests combinations of admin grades at 2nd level (MGU-726)
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
final class aggregation_schema7_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
        $this->gradeitemids = $this->load_schema('schema7');

        // Get the grade category 'Summer exam'.
        $this->gradecatsummer = $DB->get_record('grade_categories', ['fullname' => 'Summer exam'], '*', MUST_EXIST);

    }

    /**
     * Test simple weighted mean with droplow = 2
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_weighted_mean_droplow(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data7a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("B2", $fred['displaygrade']);
        $this->assertEquals(15.8, $fred['rawgrade']);
        $this->assertTrue($fred['fields'][2]['dropped']);
        $this->assertTrue($fred['fields'][5]['dropped']);
    }

    /**
     * Test simple weighted mean with droplow exceeding number of items
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_weighted_mean_large_droplow(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data7a', $this->student->id);

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
        $category = $DB->get_record('grade_categories', ['id' => $this->gradecatsummer->id], '*', MUST_EXIST);
        $category->droplow = 8;
        $DB->update_record('grade_categories', $category);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("Cannot aggregate", $fred['displaygrade']);
        $this->assertEquals(null, $fred['rawgrade']);
    }

    /**
     * Test simple weighted mean with admin grades
     * This tests NS result.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_ns(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data7a', $this->student->id);

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
        $category = $DB->get_record('grade_categories', ['id' => $this->gradecatsummer->id], '*', MUST_EXIST);
        $category->droplow = 0;
        $DB->update_record('grade_categories', $category);

        // Set NS for question 3.
        $this->apply_admingrade('Question 3', $this->student->id, 'NS');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("NS", $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);

        // Set MV for question 4. Total should be MV.
        $this->apply_admingrade('Question 4', $this->student->id, 'MV');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("MV", $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);
    }

    /**
     * Test simple weighted mean with admin grades
     * This tests NMV result.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_mv(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);
        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data7a', $this->student->id);

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
        $category = $DB->get_record('grade_categories', ['id' => $this->gradecatsummer->id], '*', MUST_EXIST);
        $category->droplow = 0;
        $DB->update_record('grade_categories', $category);

        // Set MV for question 3.
        $this->apply_admingrade('Question 3', $this->student->id, 'MV');

        // Set MV for question 4.
        $this->apply_admingrade('Question 4', $this->student->id, 'MV');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("MV", $fred['displaygrade']);
        $this->assertEquals(0.0, $fred['rawgrade']);

        // Set IS for question 7. Should now be IS.
        $this->apply_admingrade('Question 7', $this->student->id, 'IS');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals("IS", $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);
    }

    /**
     * Test for MGU-1184
     * Reason text is appearing in sub-category total column when grade is overridden
     */
    public function test_mgu_1184() {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);
        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data7a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Check we have a valid total.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('B2', $fred['displaygrade']);
        $this->assertEquals('', $fred['error']);

        // Override the summer category total.
        $summeritemid = $this->get_gradeitemid_from_grade_category('Summer exam');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $summeritemid,
            userid:         $this->student->id,
            reason:         'CATEGORY',
            other:          '',
            admingrade:     '',
            scale:          13, // C2.
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Check the overriden total.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('C2', $fred['displaygrade']);
        $this->assertEquals('', $fred['error']);
    }

}
