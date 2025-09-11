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
final class aggregation_examples_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
        $gradeitemids = $this->load_schema('schema_examples');

        // Get the grade categories 'summative' and 'Quizzes'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);
        $this->gradecatquizzes = $DB->get_record('grade_categories', ['fullname' => 'Quizzes'], '*', MUST_EXIST);

        // Get the itemids
        for ($i = 1; $i <= 5; $i++) {
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
    protected function write_grade($gradeitemid, $userid, $scale, $admingrade) {
        $schedulea = $this->get_map();
        $scalevalue = $scale ? $schedulea[$scale] : -1;
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $gradeitemid,
            userid:         $userid,
            reason:         'SECOND',
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

    /**
     * Test "With DFR deferred grade"
     * 
     * If there is a DFR deferred grade for any of the components at level 2, the level 2 category
     * total automatically becomes DF deferred. This includes if there are other admin grades present.
     */
    public function test_with_DFR_deferred_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'DEFERRED');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'A1', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B3', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');
        $this->write_grade($this->itemids['quiz5'], $studentid, 'A3', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('DFR', $fred['displaygrade']);
    }

    /**
     * Test "With IS interruption of studies"
     * 
     * If there is a IS interruption of studies grade for any weighted grade ite, at level 2, the level 2 category
     * total automatically becomes IS interruption of studies.
     */
    public function test_with_IS_interruption_of_studies_grade(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'INTERRUPTIONOFSTUDIES');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'A1', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B3', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');
        $this->write_grade($this->itemids['quiz5'], $studentid, 'A3', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
    }

    /**
     * Test "With IS interruption of studies and NS no submission"
     * 
     * If there is a IS interruption of studies grade for any weighted grade ite, at level 2, the level 2 category
     * total automatically becomes IS interruption of studies.
     */
    public function test_with_IS_interruption_of_studies_and_NS_no_submission(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'INTERRUPTIONOFSTUDIES');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'A1', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, '', 'NOSUBMISSION');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');
        $this->write_grade($this->itemids['quiz5'], $studentid, 'A3', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
    }

    /**
     * Test "With NS no submission"
     * 
     * If the student gets a NS grade at Leve 2+, they will automatically be awarded
     * a NS grade for the category total
     */
    public function test_with_NS_no_submission(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'NOSUBMISSION');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'A1', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B3', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');
        $this->write_grade($this->itemids['quiz5'], $studentid, 'A3', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('NS', $fred['displaygrade']);
    }

    /**
     * Test "With IS interruption of studies and NS no submission (0 grade)"
     * 
     * If there is a IS interruption of studies grade for any weighted grade ite, at level 2, the level 2 category
     * total automatically becomes IS interruption of studies.
     */
    public function test_with_IS_interruption_of_studies_and_NS0_no_submission0(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'INTERRUPTIONOFSTUDIES');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'A1', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, '', 'NOSUBMISSION_0');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');
        $this->write_grade($this->itemids['quiz5'], $studentid, 'A3', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
    }

    /**
     * Test "With IS interruption of studies and EC extenuating circumstances (further opportunity)"
     * 
     */
    public function test_with_IS_interruption_of_studies_and_EC_extenuating_circumstances_fo(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_FO');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'INTERRUPTIONOFSTUDIES'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B3', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');
        $this->write_grade($this->itemids['quiz5'], $studentid, 'A3', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
    }

    /**
     * Test "With EC extenuating circumstances (further opportunity)"
     * 
     */
    public function test_with_EC_extenuating_circumstances_fo(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_FO');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'A1', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B3', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');
        $this->write_grade($this->itemids['quiz5'], $studentid, 'A3', '');

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
    }

    /**
     * Test "With IS interruption of studies and EC extenuating circumstances (condoned)"
     * 
     */
    public function test_with_IS_interruption_of_studies_and_EC_extenuating_circumstances_condoned(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_NR');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'INTERRUPTIONOFSTUDIES'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B3', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');
        $this->write_grade($this->itemids['quiz5'], $studentid, 'A3', '');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('IS', $fred['displaygrade']);
    }

    /**
     * Test "With  EC extenuating circumstances (condoned)"
     * 
     */
    public function test_with_EC_extenuating_circumstances_condoned(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_NR');
        $this->write_grade($this->itemids['quiz2'], $studentid, 'A1', ''); 
        $this->write_grade($this->itemids['quiz3'], $studentid, 'B3', '');
        $this->write_grade($this->itemids['quiz4'], $studentid, 'A5', '');
        $this->write_grade($this->itemids['quiz5'], $studentid, 'A3', '');

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
    }

    /**
     * Test "All NS no submission"
     * 
     */
    public function test_with_all_NS(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'NOSUBMISSION');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'NOSUBMISSION'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, '', 'NOSUBMISSION');
        $this->write_grade($this->itemids['quiz4'], $studentid, '', 'NOSUBMISSION');
        $this->write_grade($this->itemids['quiz5'], $studentid, '', 'NOSUBMISSION');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('NS', $fred['displaygrade']);
    }

    /**
     * Test "All NS0 no submission 0"
     * 
     */
    public function test_with_all_NS0(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'NOSUBMISSION_0');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'NOSUBMISSION_0'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, '', 'NOSUBMISSION_0');
        $this->write_grade($this->itemids['quiz4'], $studentid, '', 'NOSUBMISSION_0');
        $this->write_grade($this->itemids['quiz5'], $studentid, '', 'NOSUBMISSION_0');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('NS', $fred['displaygrade']);
    }

    /**
     * Test "All EC extenuating circumstances (further opportunity)"
     * 
     */
    public function test_with_all_EC(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_FO');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'GOODCAUSE_FO'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, '', 'GOODCAUSE_FO');
        $this->write_grade($this->itemids['quiz4'], $studentid, '', 'GOODCAUSE_FO');
        $this->write_grade($this->itemids['quiz5'], $studentid, '', 'GOODCAUSE_FO');

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
    }

    /**
     * Test "All ECC extenuating circumstances (condoned)"
     * 
     */
    public function test_with_all_ECC(): void {

        $studentid = $this->student->id;
        $this->write_grade($this->itemids['quiz1'], $studentid, '', 'GOODCAUSE_NR');
        $this->write_grade($this->itemids['quiz2'], $studentid, '', 'GOODCAUSE_NR'); 
        $this->write_grade($this->itemids['quiz3'], $studentid, '', 'GOODCAUSE_NR');
        $this->write_grade($this->itemids['quiz4'], $studentid, '', 'GOODCAUSE_NR');
        $this->write_grade($this->itemids['quiz5'], $studentid, '', 'GOODCAUSE_NR');

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get resulting aggregation page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatquizzes->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals('ECC', $fred['displaygrade']);
    }
}