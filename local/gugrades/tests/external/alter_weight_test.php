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
 * Test functions around altering weights
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
final class alter_weight_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
        $this->gradeitemids = $this->load_schema('schema4');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);
    }

    /**
     * Test getting the alter weight form
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_altering_weights(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data4a', $this->student->id);

        // Get the grade category 'Summer exam'.
        $gradecatsummer = $DB->get_record('grade_categories', ['fullname' => 'Summer exam'], '*', MUST_EXIST);
        $summercategoryid = $gradecatsummer->id;

        // Set aggregation strategy.
        $this->set_strategy($gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Check student mygrades API returns correct data
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $summercategoryid, $this->student->id);
        $this->assertEquals(25.0, $user->fields[0]['normalisedweight']);
        $this->assertEquals(25.0, $user->fields[1]['normalisedweight']);
        $this->assertEquals(25.0, $user->fields[2]['normalisedweight']);
        $this->assertEquals(25.0, $user->fields[3]['normalisedweight']);

        // Get alter weights form
        $form = get_alter_weight_form::execute($this->course->id, $gradecatsummer->id, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_alter_weight_form::execute_returns(),
            $form
        );

        $this->assertEquals('Summer exam', $form['categoryname']);
        $this->assertEquals('Fred Bloggs', $form['userfullname']);
        $this->assertEquals('1234567', $form['idnumber']);
        $items = $form['items'];
        $this->assertCount(4, $items);
        $this->assertEquals('Question 4', $items[3]['fullname']);

        // Create items array for changing some weights
        $saveitems = [
            [
                'gradeitemid' => $items[0]['gradeitemid'],
                'weight' => '0.33',
            ],
            [
                'gradeitemid' => $items[1]['gradeitemid'],
                'weight' => '0.28',
            ],
        ];

        // Reason for the update
        $reason = 'Why ever not?';

        // Save weights.
        $nothing = save_altered_weights::execute($this->course->id, $gradecatsummer->id, $this->student->id, false, $reason, $saveitems);
        $nothing = external_api::clean_returnvalue(
            save_altered_weights::execute_returns(),
            $nothing
        );

        // Check that they have been written to local_gugrades_altered_weight.
        $weights = $DB->get_records('local_gugrades_altered_weight', ['courseid' => $this->course->id]);
        $weights = array_values($weights);

        $this->assertCount(2, $weights);
        $this->assertEquals(0.33, $weights[0]->weight);

        // Get aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $users = $page['users'];
        $this->assertTrue($users[0]['alteredweight']);

        // Check student mygrades API returns correct data
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $summercategoryid, $this->student->id);
        $this->assertEquals(12.64368, $user->fields[0]['normalisedweight']);
        $this->assertEquals(10.72797, $user->fields[1]['normalisedweight']);
        $this->assertEquals(38.31418, $user->fields[2]['normalisedweight']);
        $this->assertEquals(38.31418, $user->fields[3]['normalisedweight']);

        // Revert weights.
        $nothing = save_altered_weights::execute($this->course->id, $gradecatsummer->id, $this->student->id, true, '', []);
        $nothing = external_api::clean_returnvalue(
            save_altered_weights::execute_returns(),
            $nothing
        );

        // Check that they have been reverted.
        $weights = $DB->get_records('local_gugrades_altered_weight', ['courseid' => $this->course->id]);
        $weights = array_values($weights);

        $this->assertCount(0, $weights);

        // Get aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $gradecatsummer->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $users = $page['users'];
        $this->assertFalse($users[0]['alteredweight']);

        // Check student mygrades API returns correct data
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $summercategoryid, $this->student->id);
        $this->assertEquals(25.0, $user->fields[0]['normalisedweight']);
        $this->assertEquals(25.0, $user->fields[1]['normalisedweight']);
        $this->assertEquals(25.0, $user->fields[2]['normalisedweight']);
        $this->assertEquals(25.0, $user->fields[3]['normalisedweight']);

    }

}
