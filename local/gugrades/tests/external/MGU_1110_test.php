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
 * Test functions around MGU-1110 (Option to add MV0 grade)
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
final class MGU_1110_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
     * Create conversion map
     * @return int
     */
    protected function make_map() {

        // Read map with id 0 (new map) for Schedule A.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map';
        $schedule = 'schedulea';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapid = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapid = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapid
        );
        $mapid = $mapid['mapid'];

        return $mapid;
    }

    /**
     * Test that form shows MV0 (or not) at correct levels.
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
        // As this is on level 1, MV0 SHOULD be available.
        // (cf NS0)
        $item1id = $this->get_gradeitemid('Item 1');
        $form = get_add_grade_form::execute($this->course->id, $item1id, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $adminmenu = $form['adminmenu'];
        $this->assertCount(5, $adminmenu);

        // Try category 'Summer exam'.
        // Should still work
        $summerexamitemid = $this->get_gradeitemid_for_category('Summer exam');
        $form = get_add_grade_form::execute($this->course->id, $summerexamitemid, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $adminmenu = $form['adminmenu'];
        $this->assertCount(5, $adminmenu);

        // Try 'Question 1'.
        // Should still be available in menu (along with NS0).
        $question1id = $this->get_gradeitemid('Question 1');
        $form = get_add_grade_form::execute($this->course->id, $question1id, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $adminmenu = $form['adminmenu'];
        $this->assertCount(6, $adminmenu);
    }

    /**
     * Test aggregation results with NS0
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_EC0_aggregation(): void {
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

        // Add GOODCAUSE_NR to 'Question 1'.
        $question1id = $this->get_gradeitemid('Question 1');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question1id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'GOODCAUSE_NR',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page with single MV0
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('60', $fred['displaygrade']);

        // Add GOODCAUSE_NR to category 'Sub question'.
        $subquestionid = $this->get_gradeitemid_for_category('Sub question');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $subquestionid,
            userid:         $this->student->id,
            reason:         'CATEGORY',
            other:          '',
            admingrade:     'GOODCAUSE_NR',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page with two MV0
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('85', $fred['displaygrade']);

        // Add MV0 to 'Question 2'.
        $question2id = $this->get_gradeitemid('Question 2');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question2id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'GOODCAUSE_NR',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Add MV0 to 'Question 4'.
        $question3id = $this->get_gradeitemid('Question 3');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question3id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'GOODCAUSE_NR',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page for all MV0. MGU-1110 CoS 8.
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('EC0', $fred['displaygrade']);

        // Get the grade category 'Summative'.
        $gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

        // Create conversion map
        $mapid = $this->make_map();

        // Add conversion map to summer exam category
        $nothing = select_conversion::execute($this->course->id, 0, $gradecatsummer->id, $mapid);
        $nothing = external_api::clean_returnvalue(
            select_conversion::execute_returns(),
            $nothing
        );

        // Get aggregation for Summative.
        // It's MV0 plus 3 grades (all equal weight). So should be remaining 3 grades / 3.
        // MGU-1110 CoS10.
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummative->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals(75, $fred['completed']);
        $this->assertEquals('B3 (15)', $fred['displaygrade']);
        //$this->assertEquals('B2 (16)', $fred['displaygrade']);

        // Add MV0 to Item 1 (pushing it down to 50%)
        $item1id = $this->get_gradeitemid('Item 1');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $item1id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'GOODCAUSE_NR',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation for Summative.
        // It's two MV0 plus two grades (all equal weight). So under 75%.
        // MGU-1110 Cos11 (MV0 for a component < 75% at level 1 == MV)
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummative->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('EC', $fred['displaygrade']);
        $this->assertEquals(50, $fred['completed']);

        // Add NS to Item 1.
        // Generate MGU-1110 CoS 12.
        $item1id = $this->get_gradeitemid('Item 1');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $item1id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'NOSUBMISSION',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation for Summative.
        // It's mix of MV0 and NS under 75%.
        // MGU-1110 Cos12 (MV0 and NS for a component < 75% at level 1 == CW)
        // Superceded by MGU-1213
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummative->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('CW', $fred['displaygrade']);
        $this->assertEquals(50, $fred['completed']);

        // Add MV to Item 1.
        // Generate MGU-1110 CoS 15.
        $item1id = $this->get_gradeitemid('Item 1');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $item1id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'GOODCAUSE_FO',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation for Summative.
        // It's mix of MV0 and MV
        // MGU-1110 Cos14
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummative->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('EC', $fred['displaygrade']);

        // Back to level 2 summer exam.
        // Add MV for Question 1. MGU-1110 CoS 15
        $question1id = $this->get_gradeitemid('Question 1');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question1id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'GOODCAUSE_FO',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page with mix of MV and MV0.
        // Result should be MV
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('EC', $fred['displaygrade']);
    }

    /**
     * Test aggregation results with ALL MV0
     * MGU-1110 CoS 8
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_CoS8_aggregation(): void {
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

        // Set to droplow=1
        $gradecatsummer->droplow = 1;
        $DB->update_record('grade_categories', $gradecatsummer);

        // Set aggregation strategy.
        $this->set_strategy($gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set Q1-3 and subquestion to MV0
        // Add MV0 to category 'Sub question'.
        $subquestionid = $this->get_gradeitemid_for_category('Sub question');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $subquestionid,
            userid:         $this->student->id,
            reason:         'CATEGORY',
            other:          '',
            admingrade:     'GOODCAUSE_NR',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Add grade to 'Question 1'. This is going to get dropped.
        $question1id = $this->get_gradeitemid('Question 1');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question1id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     '',
            scale:          0,
            grade:          10,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Add MV0 to 'Question 2'.
        $question2id = $this->get_gradeitemid('Question 2');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question2id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'GOODCAUSE_NR',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Add MV0 to 'Question 3'.
        $question3id = $this->get_gradeitemid('Question 3');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $question3id,
            userid:         $this->student->id,
            reason:         'AGREED',
            other:          '',
            admingrade:     'GOODCAUSE_NR',
            scale:          0,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page with all MV0.
        // Result should be MV0
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('EC0', $fred['displaygrade']);
    }

}
