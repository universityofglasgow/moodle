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
 * Test functions for MGU-1226 - weightings on headers to display to 3 decimal places.
 *
 * @package    local_gugrades
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2025 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_aggregation_testcase.php');

/**
 * Test of the get_columns() method effectively, that it returns weights with the correct decimal placing.
 */
final class MGU_1226_test extends \local_gugrades\external\gugrades_aggregation_testcase {

    /**
     * @var object $gradecatsummative
     */
    protected $gradecatsummative;

    /**
     * @var array $gradeitemids
     */
    protected $gradeitemids;

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
        $this->gradeitemids = $this->load_schema('schema_mgu1226');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

        // Make a conversion map.
        $this->mapid = $this->make_conversion_map();
    }

    /**
     * Test that the weights from the column headers display 3 decimal places.
     * 
     * @covers \local_gugrades\external\get_aggregation_page::execute
     * @return void
     */
    public function test_column_header_weights_display_3dp(): void {
        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Get first csv test string.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, $this->student->firstname,
            $this->student->lastname, 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertIsArray($page['columns']);
        $columns = $page['columns'];

        // This category is using the Weighted Mean of Grades strategy so should return 3 dp's.
        $column0 = $columns[0];
        $this->assertIsFloat($column0['weight'], 'Column weight is not a float');
        $this->assertEqualsWithDelta(26.997, $column0['weight'], 0.0001);
        $this->assertMatchesRegularExpression('/\d{1}\.\d{3}/', $column0['weight']);

        // We've set columm 3 to use a Median strategy, so should only have 1 decimal place.
        $column3 = $columns[3];
        $this->assertIsFloat($column3['weight'], 'Column weight is not a float');
        $this->assertMatchesRegularExpression('/\d{1}\d{0,1}/', $column3['weight']);

        // Column 4 has a weight of 6, but the type is actualy a float.
        $column4 = $columns[4];
        $this->assertIsFloat($column4['weight'], 'Column weight is not a float');
        $this->assertMatchesRegularExpression('/\d{1}/', $column4['weight']);

        // Column 6 has a simpler weight.
        $column6 = $columns[6];
        $this->assertIsFloat($column6['weight'], 'Column weight is not a float');
        $this->assertMatchesRegularExpression('/\d{2}\.\d{1}/', $column6['weight']);
    }
    
}