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
 * Generate loads of data and check how long it takes
 *
 * Start profiler with (something like)
 * php -d xdebug.mode=profile vendor/bin/phpunit local/gugrades/tests/external/aggregation_schema9_test.php
 *
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;

defined('MOODLE_INTERNAL') || die();

define('TEST_USERS_COUNT', 800);

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_aggregation_testcase.php');

/**
 * More test(s) for get_aggregation_page webservice
 */
final class aggregation_schema9_test extends \local_gugrades\external\gugrades_aggregation_testcase {

    /**
     * @var object $gradecatsummative
     */
    protected object $gradecatsummative;

    /**
     * @var array $studentids
     */
    protected array $studentids;

    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();

        // Install test schema.
        $this->gradeitemids = $this->load_schema('schema9');

        // Get the grade category 'Summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

        // Create LOTS of users.
        // ID number is just in sequence so it's unique.
        $start = microtime(true);
        $this->studentids = [];
        $idnumber = 1111111;
        for ($i = 0; $i <= TEST_USERS_COUNT; $i++) {
            $student = $this->getDataGenerator()->create_user(['idnumber' => $idnumber++]);
            $this->getDataGenerator()->enrol_user($student->id, $this->course->id, 'student');
            $this->studentids[] = $student->id;
        }
        $stop = microtime(true);
        echo("Time to create user accounts " . $stop - $start);

        // Get gradeitems.
        $gradeitems = [];
        foreach ($this->gradeitemids as $gradeitemid) {
            $gradeitems[] = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        }

        $schedulea = [
            0 => 'H:0', 1 => 'G2:1', 2 => 'G1:2', 3 => 'F3:3', 4 => 'F2:4', 5 => 'F1:5', 6 => 'E3:6', 7 => 'E2:7', 8 => 'E1:8',
            9 => 'D3:9', 10 => 'D2:10', 11 => 'D1:11', 12 => 'C3:12', 13 => 'C2:13', 14 => 'C1:14', 15 => 'B3:15', 16 => 'B2:16',
            17 => 'B1:17', 18 => 'A5:18', 19 => 'A4:19', 20 => 'A3:20', 21 => 'A2:21', 22 => 'A1:22',
        ];

        // Generate lots of random grades.
        foreach ($this->studentids as $studentid) {
            foreach ($gradeitems as $gradeitem) {
                $gradevalue = rand(0, 22);
                $grade = $schedulea[$gradevalue];
                $this->write_grade_grades($gradeitem, $studentid, $grade);
            }
        }
    }

    /**
     * Test simple weighted mean with admin grades in Summer axam
     * This tests NS result.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_big_aggregation(): void {
        global $DB;

        // Turn debugging up full
        set_debugging(DEBUG_DEVELOPER);

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $this->studentids);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Set aggregation strategy.
        $this->set_strategy($this->gradecatsummative->id, \GRADE_AGGREGATE_WEIGHTED_MEAN);

        // Get aggregation page for above.
        $start = microtime(true);
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );
        $end = microtime(true);
        $elapsed = $end - $start;

        $debug = $page['debug'];
        var_dump($debug);
        var_dump($elapsed);
    }

}
