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
 * Test for MGU-1116
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
 * Test for inconsistent NS aggregation - MGU-1116
 */
final class MGU_1116_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
        $this->gradeitemids = $this->load_schema('schema11');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);
    }

    /**
     * Write an additional grade
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $grade
     * @param string $admingrade
     */
    protected function write_grade(int $courseid, int $gradeitemid, int $grade, string $admingrade) {

            // Override category grade for gradecatsummer
            $nothing = write_additional_grade::execute(
                courseid:       $courseid,
                gradeitemid:    $gradeitemid,
                userid:         $this->student->id,
                reason:         'SECOND',
                other:          '',
                admingrade:     $admingrade,
                scale:          0,
                grade:          $grade,
                notes:          'Test notes'
            );
            $nothing = external_api::clean_returnvalue(
                write_additional_grade::execute_returns(),
                $nothing
            );
    }

    /**
     * Test conversion works at all in 'summer exam' category.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_basic_conversion(): void {
        global $DB;

        $courseid = $this->course->id;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data11a', $this->student->id);

        // Get the grade category 'Summer exam'.
        $gradecatsummer = $DB->get_record('grade_categories', ['fullname' => 'Summer exam'], '*', MUST_EXIST);

        // Set aggregation strategy.
        $this->set_strategy($gradecatsummer->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($courseid, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($courseid, $gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // This is a straightforward aggregation.
        $fred = $page['users'][0];
        $this->assertEquals(33.33333, $fred['displaygrade']);

        // Change Question 4 to NS
        $q4id = $this->get_gradeitemid('Question 4');
        $this->write_grade($courseid, $q4id, 0, 'NOSUBMISSION');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($courseid, $gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals(14.44444, $fred['displaygrade']);

        // Change Question 3 to 0
        $q4id = $this->get_gradeitemid('Question 3');
        $this->write_grade($courseid, $q4id, 0, '');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($courseid, $gradecatsummer->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals(10, $fred['displaygrade']);
    }

}
