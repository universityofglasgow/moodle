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
 * Test specific admin grades work as per spec
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
final class admin_grades_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
     * Test 07 admin grade
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_07_admin_grade(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        // Question 2 is missing
        $this->load_data('data8b', $this->student->id);

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
        $category->droplow = 1;
        $DB->update_record('grade_categories', $category);

        // Set 07 for question 3.
        // This is technically the lowest value grade but the 07 should override the drop low
        $this->apply_admingrade('Question 3', $this->student->id, '07');

        // Get aggregation page for sub-category.
        // 07 should override grades missing.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('07', $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);

        // Get aggregation page for total.
        // 07 should override grades missing.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // 07 should 'bubble up' to top.
        $fred = $page['users'][0];
        $this->assertEquals('07', $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);

    }

    /**
     * Test MV admin grade
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_MV_admin_grade(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        // Question 2 is missing
        $this->load_data('data8b', $this->student->id);

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
        $category->droplow = 1;
        $DB->update_record('grade_categories', $category);

        // Set MV for question 3.
        // This is technically the lowest value grade but the MV should override the drop low
        $this->apply_admingrade('Question 3', $this->student->id, 'MV');

        // Get aggregation page for sub-category.
        // MV should override grades missing.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('MV', $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);

        // Get aggregation page for total.
        // MV should override grades missing.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // MV should 'bubble up' to top.
        $fred = $page['users'][0];
        $this->assertEquals('MV', $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);

        // Set 07 for question 4.
        // This is technically the lowest value grade but the 07 should override the drop low and MV.
        $this->apply_admingrade('Question 4', $this->student->id, '07');

        // Get aggregation page for total.
        // Should change to 07.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // 07 should 'bubble up' to top.
        $fred = $page['users'][0];
        $this->assertEquals('07', $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);
    }

    /**
     * Test IS admin grade
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_admin_grade(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        // Question 2 is missing
        $this->load_data('data8b', $this->student->id);

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
        $category->droplow = 1;
        $DB->update_record('grade_categories', $category);

        // Set IS for question 3.
        // This is technically the lowest value grade but the IS should override the drop low
        $this->apply_admingrade('Question 3', $this->student->id, 'IS');

        // Get aggregation page for sub-category.
        // IS should override grades missing.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);

        // Get aggregation page for total.
        // MV should override grades missing.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // IS should 'bubble up' to top.
        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);

        // Set 07 for question 4.
        // This is technically the lowest value grade but the 07 should override the drop low and IS.
        $this->apply_admingrade('Question 4', $this->student->id, '07');

        // Get aggregation page for total.
        // Should change to 07.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // 07 should 'bubble up' to top.
        $fred = $page['users'][0];
        $this->assertEquals('07', $fred['displaygrade']);
        $this->assertEquals(0, $fred['rawgrade']);
    }

}
