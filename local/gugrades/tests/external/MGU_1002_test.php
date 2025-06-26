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
 * Test the specific CoS for the IS admin grade.
 * @package    local_gugrades
 * @copyright  2025
 * @author     Greg Pedder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_aggregation_testcase.php');

/**
 * The main class.
 */
final class MGU_1002_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
        $this->gradeitemids = $this->load_schema('schema_mgu1002');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

        // Get the grade category 'Summer exam'.
        $this->gradecatsummer = $DB->get_record('grade_categories', ['fullname' => 'Summer exam'], '*', MUST_EXIST);
    }

    /**
     * Test IS admin grade CoS 5.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_CoS5_admin_grade(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data12a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Set INTERRUPTIONOFSTUDIES for Item 2.
        $this->apply_admingrade('Item 2', $this->student->id, 'INTERRUPTIONOFSTUDIES');

        // Get aggregation page for total.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // IS should become the Total (course grade).
        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
    }

    /**
     * Test IS admin grade CoS 6.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_CoS6_admin_grade(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data12a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Set INTERRUPTIONOFSTUDIES for Item 2.
        $this->apply_admingrade('Item 2', $this->student->id, 'INTERRUPTIONOFSTUDIES');

        // Set DFR for Item 4.
        $this->apply_admingrade('Item 4', $this->student->id, 'DEFERRED');

        // Get aggregation page for total.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // DFR should take precedence here.
        $fred = $page['users'][0];
        $this->assertEquals('DFR', $fred['displaygrade']);
    }

    /**
     * Test IS admin grade CoS 7 IS+EC.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_CoS7_EC_admin_grade(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data12a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Set INTERRUPTIONOFSTUDIES for Item 2.
        $this->apply_admingrade('Item 2', $this->student->id, 'INTERRUPTIONOFSTUDIES');

        // Set EC for Item 4.
        $this->apply_admingrade('Item 4', $this->student->id, 'GOODCAUSE_FO');

        // Get aggregation page for total.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // IS should be the Total here.
        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
    }

    /**
     * Test IS admin grade CoS 7 IS+NS.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_CoS7_NS_admin_grade(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data12a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Set INTERRUPTIONOFSTUDIES for Item 2.
        $this->apply_admingrade('Item 2', $this->student->id, 'INTERRUPTIONOFSTUDIES');

        // Set NOSUBMISSION for Item 2.
        $this->apply_admingrade('Item 4', $this->student->id, 'NOSUBMISSION');

        // Get aggregation page for total.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // IS should be the Total here.
        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
    }

    /**
     * Test IS admin grade CoS 10.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_CoS10_admin_grade(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data12a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Set INTERRUPTIONOFSTUDIES for Question 3.
        $this->apply_admingrade('Question 3', $this->student->id, 'INTERRUPTIONOFSTUDIES');

        $itemstocheck = [
            'Question 1' => 'D1:11',
            'Question 2' => 'B1:17',
            'Question 4' => 'C3:12'
        ];

        $gradehaschanged = false;

        // Get the user data for this grade category.
        $user = get_aggregation_user::execute($this->course->id, $this->gradecatsummer->id, $this->student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );
        foreach ($user['fields'] as $usergrade) {
            // Check that the above Question's grade hasn't been changed
            if (array_key_exists($usergrade['itemname'], $itemstocheck)) {
                $itemtocheck = $itemstocheck[$usergrade['itemname']];
                if ($usergrade['display'] != $itemtocheck) {
                    $gradehaschanged = true;
                }
            }

        }

        $this->assertFalse($gradehaschanged);
    }

    /**
     * Test IS admin grade CoS 11.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_CoS11_admin_grade(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data12a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Set INTERRUPTIONOFSTUDIES for Question 3.
        $this->apply_admingrade('Question 3', $this->student->id, 'INTERRUPTIONOFSTUDIES');

        // Get the user data for this grade category.
        $user = get_aggregation_user::execute($this->course->id, $this->gradecatsummer->id, $this->student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );

        $this->assertEquals('IS', $user['displaygrade']);
    }

    /**
     * Test IS admin grade CoS 12.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_CoS12_admin_grade(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data12a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Set DFR for Question 1.
        $this->apply_admingrade('Question 1', $this->student->id, 'DEFERRED');

        // Set INTERRUPTIONOFSTUDIES for Question 3.
        $this->apply_admingrade('Question 3', $this->student->id, 'INTERRUPTIONOFSTUDIES');

        // Get the user data for this grade category.
        $user = get_aggregation_user::execute($this->course->id, $this->gradecatsummer->id, $this->student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );

        $this->assertEquals('DFR', $user['displaygrade']);
    }

    /**
     * Test IS admin grade CoS 13 - EC.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_CoS13_EC_admin_grade(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data12a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Set INTERRUPTIONOFSTUDIES for Question 1.
        $this->apply_admingrade('Question 1', $this->student->id, 'INTERRUPTIONOFSTUDIES');

        // Set EC for Question 3.
        $this->apply_admingrade('Question 3', $this->student->id, 'GOODCAUSE_FO');

        // Get the user data for this grade category.
        $user = get_aggregation_user::execute($this->course->id, $this->gradecatsummer->id, $this->student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );

        $this->assertEquals('IS', $user['displaygrade']);
    }

    /**
     * Test IS admin grade CoS 13 - NS0.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_IS_CoS13_NS0_admin_grade(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data12a', $this->student->id);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Set INTERRUPTIONOFSTUDIES for Question 1.
        $this->apply_admingrade('Question 1', $this->student->id, 'INTERRUPTIONOFSTUDIES');

        // Set NOSUBMISSION_0 for Question 3.
        $this->apply_admingrade('Question 3', $this->student->id, 'NOSUBMISSION_0');

        // Get the user data for this grade category.
        $user = get_aggregation_user::execute($this->course->id, $this->gradecatsummer->id, $this->student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );

        $this->assertEquals('IS', $user['displaygrade']);
    }
}
