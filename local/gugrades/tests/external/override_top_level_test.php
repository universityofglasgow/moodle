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
 * Test functions around overriding the top level total
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
final class override_top_level_test extends \local_gugrades\external\gugrades_aggregation_testcase {

    /**
     * @var object $gradecatsummative
     */
    protected object $gradecatsummative;

    /**
     * @var object $summativeitem
     */
    protected object $summativeitem;

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
        $this->summativeitem = $DB->get_record('grade_items',
            ['itemtype' => 'category', 'iteminstance' => $this->gradecatsummative->id], '*', MUST_EXIST);
    }

    /**
     * Test top-level aggregation, Schedule A/B mix.
     * Test with data - more than 75% completion
     * Also tests default weighted mean
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_get_form(): void {

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

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // Should be 'grades missing'
        $this->assertEquals('Grades missing', $page['users'][0]['displaygrade']);

        // Check override form.
        // As the gradetype is known, the form can be created.
        $form = get_add_grade_form::execute($this->course->id, $this->summativeitem->id, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $this->assertCount(9, $form['gradetypes']);
        $this->assertTrue($form['available']);
        $this->assertFalse($form['error']);
        $this->assertCount(23, $form['scalemenu']);
        $this->assertCount(13, $form['adminmenu']);

        // Write grade to level one total.
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $this->summativeitem->id,
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

        // Get aggregation page for the original scale exam category.
        // Make sure above grade has added and scale exam has aggregated.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertTrue($fred['overridden']);
        $this->assertEquals('C2', $fred['displaygrade']);
        $this->assertEquals(13, $fred['rawgrade']);

        // Get the history for this grade category.
        $history = get_history::execute($this->course->id, $this->summativeitem->id, $this->student->id);
        $history = external_api::clean_returnvalue(
            get_history::execute_returns(),
            $history
        );

        $this->assertCount(2, $history);
        $this->assertEquals('Grades missing', $history[1]['displaygrade']);
        $this->assertEquals('C2', $history[0]['displaygrade']);

        // Remove grade.
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $this->summativeitem->id,
            userid:         $this->student->id,
            reason:         'CATEGORY',
            other:          '',
            admingrade:     '',
            scale:          0,
            grade:          0,
            notes:          'Remove notes',
            delete:         true
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page for the original scale exam category.
        // Make sure above grade is removed.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // Should be back to 'grades missing'
        $this->assertEquals('Grades missing', $page['users'][0]['displaygrade']);

        // Get the history for this grade category.
        $history = get_history::execute($this->course->id, $this->summativeitem->id, $this->student->id);
        $history = external_api::clean_returnvalue(
            get_history::execute_returns(),
            $history
        );

        $this->assertCount(3, $history);
        $this->assertEquals('Grades missing', $history[0]['displaygrade']);
        $this->assertEquals('C2', $history[1]['displaygrade']);
        $this->assertEquals('Grades missing', $history[2]['displaygrade']);

        // Write grade to level one total again.
        // This time, we'll read data for single user
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $this->summativeitem->id,
            userid:         $this->student->id,
            reason:         'CATEGORY',
            other:          '',
            admingrade:     '',
            scale:          8, // E1.
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get data for this user.
        $user = get_aggregation_user::execute($this->course->id, $this->gradecatsummative->id, $this->student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );

        $this->assertTrue($user['overridden']);
        $this->assertEquals('E1', $user['displaygrade']);
    }

}
