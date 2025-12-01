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
 * Test functions around aggregation export
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
 * Test(s) aggregation export
 */
final class aggregation_export_test extends \local_gugrades\external\gugrades_aggregation_testcase {


    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();

        // Install test schema.
        $this->gradeitemids = $this->load_schema('schema1');

    }

    /**
     * Test get_aggregation_export_plugins
     */
    public function test_get_aggregation_export_plugins(): void {

        $courseid = $this->course->id;
        $categoryid = $this->get_grade_category('Summative');

        // Get plugins.
        $exportplugins = get_aggregation_export_plugins::execute($courseid, $categoryid);
        $exportplugins = external_api::clean_returnvalue(
            get_aggregation_export_plugins::execute_returns(),
            $exportplugins
        );

        $plugins = $exportplugins['plugins'];
        $filename = $exportplugins['filename'];

        $this->assertEquals('mycampus', $plugins[1]['name']);
        $this->assertEquals('MyCampus export', $plugins[1]['description']);
        $this->assertEquals('MyGrades_tc_1_2025', $filename);
    }

    /**
     * Clean up form array to only have expected keys
     * @param array $form
     * @return array
     */
    protected function clean_form($form) {
        $newform = [];
        foreach ($form as $record) {
            $newform[] = [
                'identifier' => $record['identifier'],
                'selected' => $record['selected'],
            ];
        }

        return $newform;
    }

    /**
     * Test get_aggregation_export_form
     */
    public function test_get_aggregation_export_form(): void {

        global $DB;

        $courseid = $this->course->id;
        $categoryid = $this->get_grade_category('Summative');

        // Get form for 'mycampus' plugin
        // (which doesn't have a form).
        $form = get_aggregation_export_form::execute($courseid, $categoryid, 'mycampus');
        $form = external_api::clean_returnvalue(
            get_aggregation_export_form::execute_returns(),
            $form
        );

        $this->assertFalse($form['hasform']);
        $this->assertCount(0, $form['form']);

        // Same again for 'custom' form plugin
        // (which does).
        $form = get_aggregation_export_form::execute($courseid, $categoryid, 'custom');
        $form = external_api::clean_returnvalue(
            get_aggregation_export_form::execute_returns(),
            $form
        );

        $this->assertTrue($form['hasform']);
        $form = $form['form'];
        $this->assertEquals('studentname', $form[0]['identifier']);
        $this->assertEquals(get_string('studentname', 'local_gugrades'), $form[0]['description']);
        $this->assertEquals('Summative', $form[7]['description']);
        $this->assertEquals('strategy', $form[25]['identifier']);
        $this->assertEquals(get_string('showstrategy', 'local_gugrades'), $form[25]['description']);

        // Set *everything* for initial test.
        foreach ($form as $key => $record) {
            $form[$key]['selected'] = true;
        }

        // Get CSV data.
        $form = $this->clean_form($form);
        $data = get_aggregation_export_data::execute($courseid, $categoryid, 0, 'custom', $form);
        $data = external_api::clean_returnvalue(
            get_aggregation_export_data::execute_returns(),
            $data
        );

        $expected = '"Student name","ID number","Email","Reassessment required","Completed","Letter Grade","Numerical Grade","Summative","Summative Strategy","Summative > Summer exam","Summative > Summer exam Strategy","Summative > Summer exam Weight","Summative > Summer exam > Question 1","Summative > Summer exam > Question 1 Weight","Summative > Summer exam > Question 2","Summative > Summer exam > Question 2 Weight","Summative > Summer exam > Question 3","Summative > Summer exam > Question 3 Weight","Summative > Scale exam","Summative > Scale exam Strategy","Summative > Scale exam Weight","Summative > Scale exam > Schedule B exam","Summative > Scale exam > Schedule B exam Strategy","Summative > Scale exam > Schedule B exam Weight","Summative > Scale exam > Schedule B exam > Question X","Summative > Scale exam > Schedule B exam > Question X Weight","Summative > Scale exam > Schedule B exam > Question Y","Summative > Scale exam > Schedule B exam > Question Y Weight","Summative > Scale exam > Schedule B exam > Question Z","Summative > Scale exam > Schedule B exam > Question Z Weight","Summative > Scale exam > Question A","Summative > Scale exam > Question A Weight","Summative > Scale exam > Question B","Summative > Scale exam > Question B Weight","Summative > Scale exam > Question C","Summative > Scale exam > Question C Weight","Summative > Item 1","Summative > Item 1 Weight","Summative > Item 2","Summative > Item 2 Weight","Summative > Item 3","Summative > Item 3 Weight"
"Fred Bloggs","1234567","username2@example.com","No","0","","","Cannot aggregate","Weighted mean of grades","Grades missing","Weighted mean of grades","100.00%","No data","100.00%","No data","100.00%","No data","100.00%","Grades missing","Weighted mean of grades","100.00%","Grades missing","Weighted mean of grades","25.00%","No data","75.00%","No data","65.00%","No data","30.00%","No data","75.00%","No data","65.00%","No data","30.00%","No data","50.00%","No data","50.00%","No data","50.00%"
"Juan Perez","1234560","username3@example.com","No","0","","","Cannot aggregate","Weighted mean of grades","Grades missing","Weighted mean of grades","100.00%","No data","100.00%","No data","100.00%","No data","100.00%","Grades missing","Weighted mean of grades","100.00%","Grades missing","Weighted mean of grades","25.00%","No data","75.00%","No data","65.00%","No data","30.00%","No data","75.00%","No data","65.00%","No data","30.00%","No data","50.00%","No data","50.00%","No data","50.00%"
';

        $this->assertEquals($expected, $data['csv']);

        // Check user preferences have been set
        $preferences = explode(',', get_user_preferences('local_gugrades_customaggregationexportselect_' . $categoryid));
        $this->assertCount(27, $preferences);
        $this->assertEquals('idnumber', $preferences[1]);

        // Get form again, to check saved settings
        $form = get_aggregation_export_form::execute($courseid, $categoryid, 'custom');
        $form = external_api::clean_returnvalue(
            get_aggregation_export_form::execute_returns(),
            $form
        );

        $this->assertTrue($form['hasform']);
        $form = $form['form'];
        $this->assertTrue($form[0]['selected']);
    }

    /**
     * Tests for mycampus plugin
     */
    public function test_mycampus_export(): void {

        $courseid = $this->course->id;
        $categoryid = $this->get_grade_category('Summative');

        // Get form for 'mycampus' plugin
        // (which doesn't have a form).
        $form = get_aggregation_export_form::execute($courseid, $categoryid, 'mycampus');
        $form = external_api::clean_returnvalue(
            get_aggregation_export_form::execute_returns(),
            $form
        );

        $this->assertFalse($form['hasform']);
        $this->assertCount(0, $form['form']);

        // Get CSV data.
        $data = get_aggregation_export_data::execute($courseid, $categoryid, 0, 'mycampus', []);
        $data = external_api::clean_returnvalue(
            get_aggregation_export_data::execute_returns(),
            $data
        );

        $expected = '"EMPLID","Name","Grade"
"1234567","Bloggs,Fred",""
"1234560","Perez,Juan",""
';
        $this->assertEquals($expected, $data['csv']);
    }
}
