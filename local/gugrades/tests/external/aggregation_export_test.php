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
        $plugins = get_aggregation_export_plugins::execute($courseid, $categoryid);
        $plugins = external_api::clean_returnvalue(
            get_aggregation_export_plugins::execute_returns(),
            $plugins
        );

        $this->assertEquals('mycampus', $plugins[1]['name']);
        $this->assertEquals('MyCampus export', $plugins[1]['description']);
    }

    /**
     * Test get_aggregation_export_form
     */
    public function test_get_aggregation_export_form(): void {

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
        $this->assertEquals("ITEM_345001", $form[5]['identifier']);
        $this->assertEquals('Summative', $form[5]['description']);
        $this->assertEquals('strategy', $form[23]['identifier']);
        $this->assertEquals(get_string('showstrategy', 'local_gugrades'), $form[23]['description']);
    }

}
