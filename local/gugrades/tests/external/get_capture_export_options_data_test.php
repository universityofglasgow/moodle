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
 * Test functions around get_capture_export_options and
 * get_capture_export_data
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
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_advanced_testcase.php');

/**
 * Test(s) for get_all_strings webservice
 */
final class get_capture_export_options_data_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Checking getting options
     *
     * @covers \local_gugrades\external\get_capture_export_options::execute
     */
    public function test_load_basic_data(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades.
        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign2 (which is using scale).
        $this->import_grades($this->course->id, $this->gradeitemidassign2, $userlist);

        // Get capture options.
        $options = get_capture_export_options::execute($this->course->id, $this->gradeitemidassign2, 0);
        $options = external_api::clean_returnvalue(
            get_capture_export_options::execute_returns(),
            $options
        );

        $this->assertCount(7, $options);
        $this->assertEquals('IDNUMBER', $options[1]['gradetype']);
        $this->assertEquals('FIRST', $options[5]['gradetype']);
        $this->assertFalse($options[5]['selected']);

        // Add additional grade.
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $this->gradeitemidassign2,
            userid:         $this->student->id,
            reason:         'SECOND',
            other:          '',
            admingrade:     '',
            scale:          18,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get capture options.
        $options = get_capture_export_options::execute($this->course->id, $this->gradeitemidassign2, 0);
        $options = external_api::clean_returnvalue(
            get_capture_export_options::execute_returns(),
            $options
        );

        $this->assertCount(8, $options);
        $this->assertEquals('FIRST', $options[5]['gradetype']);
        $this->assertEquals('1st grade', $options[5]['description']);
        $this->assertEquals('SECOND', $options[6]['gradetype']);
        $this->assertEquals('2nd grade', $options[6]['description']);
        $this->assertEquals('PROVISIONAL', $options[7]['gradetype']);
        $this->assertEquals('Latest grade', $options[7]['description']);

        // Add an 'other' additional grade.
        $nothing = write_additional_grade::execute(
            courseid:       $this->course->id,
            gradeitemid:    $this->gradeitemidassign2,
            userid:         $this->student->id,
            reason:         'OTHER',
            other:          'Test other',
            admingrade:     '',
            scale:          18,
            grade:          0,
            notes:          'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get capture options.
        $options = get_capture_export_options::execute($this->course->id, $this->gradeitemidassign2, 0);
        $options = external_api::clean_returnvalue(
            get_capture_export_options::execute_returns(),
            $options
        );

        // Set everything to selected for data test.
        $newoptions = [];
        foreach ($options as $option) {
            $newoptions[] = [
                'gradetype' => $option['gradetype'],
                'selected' => true,
            ];
        }

        // Read data.
        $data = get_capture_export_data::execute($this->course->id, $this->gradeitemidassign2, 0, false, $newoptions);
        $data = external_api::clean_returnvalue(
            get_capture_export_data::execute_returns(),
            $data
        );

        // Unpack CSV string to make sure it survived.
        $csv = $data['csv'];
        $lines = explode(PHP_EOL, $csv);
        $data = [];
        foreach ($lines as $line) {
            $data[] = str_getcsv($line);
        }

        // Finel PHP_EOL causes a null final line.
        $this->assertCount(4, $data);
        $this->assertEquals('Email address', $data[0][2]);
        $this->assertEquals('A3:20', $data[1][5]);

        // Unset some options, so we can test user preferences.
        $newoptions[1]['selected'] = false;
        $newoptions[3]['selected'] = false;
        $newoptions[5]['selected'] = false;
        $newoptions[8]['selected'] = false;

        // Read data (should set preferences).
        $data = get_capture_export_data::execute($this->course->id, $this->gradeitemidassign2, 0, false, $newoptions);
        $data = external_api::clean_returnvalue(
            get_capture_export_data::execute_returns(),
            $data
        );

        // Get capture options (should read stored options).
        $options = get_capture_export_options::execute($this->course->id, $this->gradeitemidassign2, 0);
        $options = external_api::clean_returnvalue(
            get_capture_export_options::execute_returns(),
            $options
        );

        $this->assertTrue($options[7]['selected']);
        $this->assertFalse($options[8]['selected']);
    }

}
