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
 * Test functions around get_aggregation_user - especially checking
 * totals are picked up correctly.
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
 * Test(s) for get_aggregation_user webservice
 */
final class get_aggregation_user_result_test extends \local_gugrades\external\gugrades_aggregation_testcase {

    /**
     * @var int $gradeitemsecondx
     */
    protected int $gradeitemsecondx;

    /**
     * @var array $gradeitemids
     */
    protected array $gradeitemids;

    /**
     * @var object $gradecatsummative
     */
    protected object $gradecatsummative;

    /**
     * @var int $mapid
     */
    protected int $mapid;

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

        // Make a conversion map.
        $this->mapid = $this->make_conversion_map();
    }

    /**
     * Checking aggregated result changes when any item is updated
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     * @return void
     */
    public function test_total_change(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        // Complete set of data.
        $this->load_data('data2c', $this->student->id);

        // Import ALL gradeitems.
        foreach ($this->gradeitemids as $gradeitemid) {
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Get data for this user.
        $user = get_aggregation_user::execute($this->course->id, $this->gradecatsummative->id, $this->student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );

        $this->assertEquals('D1 (10.8)', $user['displaygrade']);

        // Change a grade and see what happens
        $item2id = $this->get_gradeitemid('Item 2');
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $item2id,
            userid:         $this->student->id,
            reason:         'SECOND',
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

        // Get data for this user.
        $user = get_aggregation_user::execute($this->course->id, $this->gradecatsummative->id, $this->student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );

        $this->assertEquals('C3 (12.05)', $user['displaygrade']);

    }

}


