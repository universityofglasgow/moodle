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
 * Test functions around MGU-1191 (Option to add NS0 grade)
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
final class MGU_1191_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
        $this->gradeitemids = $this->load_schema('schema_mgu1191');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);
    }

    /**
     * Test that form shows NS0 (or not) at correct levels.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_add_level1(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data_mgu1191', $this->student->id);

        // Get the grade category 'Summer exam'.
        $gradecatsummer = $DB->get_record('grade_categories', ['fullname' => 'Summer exam'], '*', MUST_EXIST);

        // Set aggregation strategy.
        $this->set_strategy($gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get add grade form for Item 1
        // As this is on level 1, NS0 should not be available.
        $item1id = $this->get_gradeitemid('Item 1');
        $form = get_add_grade_form::execute($this->course->id, $item1id, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $adminmenu = $form['adminmenu'];
        $this->assertCount(4, $adminmenu);

        // Try category 'Summer exam'.
        // Should still not work
        $summerexamitemid = $this->get_gradeitemid_for_category('Summer exam');
        $form = get_add_grade_form::execute($this->course->id, $summerexamitemid, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $adminmenu = $form['adminmenu'];
        $this->assertCount(4, $adminmenu);

        // Try 'Question 1'.
        // Should now be available in menu.
        $question1id = $this->get_gradeitemid('Question 1');
        $form = get_add_grade_form::execute($this->course->id, $question1id, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $adminmenu = $form['adminmenu'];
        $this->assertCount(5, $adminmenu);
    }

    /**
     * Test aggregation results with NS0
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_ns0_aggregation(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data_mgu1191', $this->student->id);

        // Get the grade category 'Summer exam'.
        $gradecatsummer = $DB->get_record('grade_categories', ['fullname' => 'Summer exam'], '*', MUST_EXIST);

        // Set aggregation strategy.
        $this->set_strategy($gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get aggregation page for above with no admin grades.
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('66', $fred['displaygrade']);

        // Add NS0 to 'Question 1'.
        $question1id = $this->get_gradeitemid('Question 1');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question1id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'NOSUBMISSION_0',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page with single NS0
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('45', $fred['displaygrade']);

        // Add NS0 to category 'Sub question'.
        $subquestionid = $this->get_gradeitemid_for_category('Sub question');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $subquestionid,
            userid:         $this->student->id,
            reason:         'CATEGORY',
            other:          '',
            admingrade:     'NOSUBMISSION_0',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page with two NS0
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('42.5', $fred['displaygrade']);

        // Add NS0 to 'Question 2'.
        $question2id = $this->get_gradeitemid('Question 2');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question2id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'NOSUBMISSION_0',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Add NS0 to 'Question 4'.
        $question3id = $this->get_gradeitemid('Question 3');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question3id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'NOSUBMISSION_0',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page for all NS0
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('NS', $fred['displaygrade']);
}

}
