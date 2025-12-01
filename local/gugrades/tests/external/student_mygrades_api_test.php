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
 * Test the 'get_aggregation_dashboard_user' call for mixtures
 * of grade items and sub-categories
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
final class student_mygrades_api_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $columns = $page['columns'];
        $this->assertEquals(50, $columns[0]['weight']);
        $this->assertEquals(50, $columns[1]['weight']);
        $this->assertEquals(50, $columns[2]['weight']);

        // Check student mygrades API returns correct data
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $this->gradecatsummative->id, $this->student->id);

        $this->assertEquals('33.33333', $user->fields[0]['normalisedweight']);
        $this->assertEquals('Summer exam', $user->fields[0]['itemname']);

        // Release Summer exam aggregated category.
        $summeritemid = $this->get_gradeitemid_from_grade_category('Summer exam');
        $status = release_grades::execute($this->course->id, $summeritemid, 0, false);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Release the normal grade items.
        foreach ($this->gradeitemids as $gradeitemid) {
            $status = release_grades::execute($this->course->id, $gradeitemid, 0, false);
            $status = external_api::clean_returnvalue(
                release_grades::execute_returns(),
                $status
            );
        }

        // Check student mygrades API returns correct data
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $this->gradecatsummative->id, $this->student->id);

        //var_dump($user);

        return;

        // Change item 1 to an MV
        $this->apply_admingrade('Item 1', $this->student->id, 'MV');

        // This should result in a GCW (MGU-1009)
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals("GCW", $fred['displaygrade']);
        $this->assertEquals(0.0, $fred['rawgrade']);
        $this->assertEquals(33, $fred['completed']);

        // Change question 3 to 07 admingrade
        $this->apply_admingrade('Question 3', $this->student->id, '07');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );
    }

}
