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
 * Test functions around aggregation conversion
 * @package    local_gugrades
 * @copyright  2025
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
 * Specific tests for list of examples in the Excel file on Teams somewhere.
 */
final class aggregation_examples_dl_test extends \local_gugrades\external\gugrades_aggregation_testcase {

    protected $gradecatsummative;

    protected $gradecatquizzes;

    protected $itemids = [];

    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();

        // Install test schema.
        $gradeitemids = $this->load_schema('schema_examples_dl');

        // Get the grade categories 'summative' and 'Quizzes'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);
        $this->gradecatquizzes = $DB->get_record('grade_categories', ['fullname' => 'Quizzes'], '*', MUST_EXIST);

        // Get the itemids
        for ($i = 1; $i <= 4; $i++) {
            $item = $DB->get_record('grade_items', ['itemname' => 'Quiz ' . $i], '*', MUST_EXIST);
            $itemids['quiz' . $i] = $item->id;
        }
        $this->itemids = $itemids;
    }

    /**
     * Define scale mapping
     * @return array
     */
    public function get_map() {
        return array_flip([
            0 => 'H',
            1 => 'G2',
            2 => 'G1',
            3 => 'F3',
            4 => 'F2',
            5 => 'F1',
            6 => 'E3',
            7 => 'E2',
            8 => 'E1',
            9 => 'D3',
            10 => 'D2',
            11 => 'D1',
            12 => 'C3',
            13 => 'C2',
            14 => 'C1',
            15 => 'B3',
            16 => 'B2',
            17 => 'B1',
            18 => 'A5',
            19 => 'A4',
            20 => 'A3',
            21 => 'A2',
            22 => 'A1',
        ]);
    }

    /**
     * Write a new grade
     */
    protected function write_grade($gradeitemid, $userid, $scale, $admingrade, $iscategory = false) {
        $schedulea = $this->get_map();
        $scalevalue = $scale ? $schedulea[$scale] : -1;
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $gradeitemid,
            userid:         $userid,
            reason:         $iscategory ? 'CATEGORY' : 'SECOND',
            other:          '',
            admingrade:     $admingrade,
            scale:          $scalevalue,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );
    }

    //==================================================================
    // TESTS FROM DROP LOWEST PRIORITY TAB
    //==================================================================

    /**
     * Test "With NS grade and 0 grade both present"
     */
    public function test_with_NS_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'NOSUBMISSION');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'H', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B1', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('C3', $fred['displaygrade']);
        $this->assertTrue($fred['fields'][0]['dropped']);
    }

    /**
     * Test "With NS0 grade and 0 grade both present"
     */
    public function test_with_NS0_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'NOSUBMISSION_0');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'H', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B1', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('C3', $fred['displaygrade']);
        $this->assertTrue($fred['fields'][0]['dropped']);
    }    

    /**
     * Test "With EC grade and 0 grade both present"
     */
    public function test_with_EC_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_FO');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'H', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B1', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('EC', $fred['displaygrade']);
        for ($i = 0; $i <= 3; $i++) {
            $this->assertFalse($fred['fields'][$i]['dropped']);
        }
    }  

    /**
     * Test "With ECC grade and 0 grade both present"
     */
    public function test_with_ECC_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_NR');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'H', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B1', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('A5', $fred['displaygrade']);
        $this->assertTrue($fred['fields'][1]['dropped']);
    } 

    /**
     * Test "With ECC grade, NS grade and 0 grade present"
     */
    public function test_with_ECC_NS_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_NR');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'NOSUBMISSION'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'H', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('D3', $fred['displaygrade']);
        $this->assertTrue($fred['fields'][1]['dropped']);
    } 

    /**
     * Test "With ECC grade, NS0 grade and 0 grade present"
     */
    public function test_with_ECC_NS0_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_NR');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'NOSUBMISSION_0'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'H', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('D3', $fred['displaygrade']);
        $this->assertTrue($fred['fields'][1]['dropped']);
    } 

    /**
     * Test "With NS, EC grades and 0 grade present"
     */
    public function test_with_NS_EC_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'NOSUBMISSION');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'GOODCAUSE_FO'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'H', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('EC', $fred['displaygrade']);
        for ($i = 0; $i <= 3; $i++) {
            $this->assertFalse($fred['fields'][$i]['dropped']);
        }
    }  

    /**
     * Test "With NS, ECC grades and 0 grade present"
     */
    public function test_with_NS_ECC_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'NOSUBMISSION');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'GOODCAUSE_NR'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'H', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('D3', $fred['displaygrade']);
        $this->assertTrue($fred['fields'][0]['dropped']);
    }

    /**
     * Test "With NS, NS0 grades and 0 grade present"
     */
    public function test_with_NS_NS0_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'NOSUBMISSION');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'NOSUBMISSION_0'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'H', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('E3', $fred['displaygrade']);
        $this->assertTrue($fred['fields'][0]['dropped']);
    }

    /**
     * Test "With EC, ECC grades and 0 grade present"
     */
    public function test_with_EC_ECC_and_0_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_FO');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'GOODCAUSE_NR'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'H', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('EC', $fred['displaygrade']);
        for ($i = 0; $i <= 3; $i++) {
            $this->assertFalse($fred['fields'][$i]['dropped']);
        }
    } 
}