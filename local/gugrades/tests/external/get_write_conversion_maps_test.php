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
 * Test get_conversion_maps AND write_conversion_maps
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
 * Test get_activities web service.
 */
final class get_write_conversion_maps_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Check writing and reading data
     * @covers \local_gugrades\external\get_conversion_maps::execute
     */
    public function test_conversion_maps(): void {

        // Read maps for course (should be none).
        $maps = get_conversion_maps::execute($this->course->id);
        $maps = external_api::clean_returnvalue(
            get_conversion_maps::execute_returns(),
            $maps
        );

        // Empty response.
        $this->assertEmpty($maps);
    }

    /**
     * Check reading default map
     * @covers \local_gugrades\external\get_conversion_map::execute
     */
    public function test_get_default_map(): void {

        // Read map with id 0 (new map) for Schedule A.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        $this->assertEquals(100, $mapstuff['maxgrade']);
        $this->assertArrayHasKey('map', $mapstuff);
        $map = $mapstuff['map'];
        $this->assertCount(23, $map);
        $this->assertEquals('H', $map[0]['band']);
        $this->assertEquals(0, $map[0]['grade']);
        $this->assertEquals('A1', $map[22]['band']);
        $this->assertEquals(22, $map[22]['grade']);

        // Read map with id 0 (new map) for Schedule B.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'scheduleb');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        $this->assertEquals(100, $mapstuff['maxgrade']);
        $this->assertArrayHasKey('map', $mapstuff);
        $map = $mapstuff['map'];
        $this->assertCount(8, $map);
        $this->assertEquals('H', $map[0]['band']);
        $this->assertEquals(0, $map[0]['grade']);
        $this->assertEquals('A0', $map[7]['band']);
        $this->assertEquals(22, $map[7]['grade']);
    }

    /**
     * Check getting then writing default map
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     */
    public function test_read_write_default_map(): void {
        global $DB;

        // Read map with id 0 (new map) for Schedule A.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map';
        $schedule = 'schedulea';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapid = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapid = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapid
        );
        $mapid = $mapid['mapid'];

        // Check map table.
        $mapinfo = $DB->get_record('local_gugrades_map', ['id' => $mapid], '*', MUST_EXIST);
        $this->assertEquals('Test conversion map', $mapinfo->name);

        // Check map values.
        $values = array_values($DB->get_records('local_gugrades_map_value', ['mapid' => $mapid]));
        $this->assertCount(23, $values);
        $this->assertEquals(92, $values[22]->percentage);

        // Read back uploaded map.
        $mapstuff = get_conversion_map::execute($this->course->id, $mapid, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Delete map.
        $success = delete_conversion_map::execute($this->course->id, $mapid);
        $success = external_api::clean_returnvalue(
            delete_conversion_map::execute_returns(),
            $success
        );

        // Try to read it again (should fail).
        $this->expectException('dml_missing_record_exception');
        $mapstuff = get_conversion_map::execute($this->course->id, $mapid, 'schedulea');

    }

    /**
     * Similar to above but for ScheduleB
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     */
    public function test_read_write_scheduleb(): void {
        global $DB;

        // Read map with id 0 (new map) for Schedule B.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'scheduleb');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map B';
        $schedule = 'scheduleb';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapid = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapid = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapid
        );
        $mapid = $mapid['mapid'];

        // Read back uploaded map.
        $mapstuff = get_conversion_map::execute($this->course->id, $mapid, 'scheduleb');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        $this->assertEquals('scheduleb', $mapstuff['schedule']);
    }

    /**
     * Test import json
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     */
    public function test_import_json(): void {
        global $DB;

        $jsonmap = '{
            "name": "Test import map",
            "schedule": "scheduleb",
            "maxgrade": 100,
            "inuse": false,
            "map": [
                {
                    "band": "H",
                    "bound": 0,
                    "grade": 0
                },
                {
                    "band": "G0",
                    "bound": 9,
                    "grade": 2
                },
                {
                    "band": "F0",
                    "bound": 19,
                    "grade": 5
                },
                {
                    "band": "E0",
                    "bound": 29,
                    "grade": 8
                },
                {
                    "band": "D0",
                    "bound": 39,
                    "grade": 11
                },
                {
                    "band": "C0",
                    "bound": 53,
                    "grade": 14
                },
                {
                    "band": "B0",
                    "bound": 59,
                    "grade": 17
                },
                {
                    "band": "A0",
                    "bound": 69,
                    "grade": 22
                }
            ]
        }';

        $mapid = import_conversion_map::execute($this->course->id, $jsonmap);
        $mapid = external_api::clean_returnvalue(
            import_conversion_map::execute_returns(),
            $mapid
        );
        $mapid = $mapid['mapid'];

        // Read back uploaded map.
        $mapstuff = get_conversion_map::execute($this->course->id, $mapid, '');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        $this->assertEquals('Test import map', $mapstuff['name']);
    }

    /**
     * Test invalid import json
     * H value must be 0
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     */
    public function test_incorrect_json(): void {

        // Incorrect bound for H (must be zero).
        $jsonmap = '{
            "name": "Test import map",
            "schedule": "scheduleb",
            "maxgrade": 100,
            "inuse": false,
            "map": [
                {
                    "band": "H",
                    "bound": 7,
                    "grade": 0
                },
                {
                    "band": "G0",
                    "bound": 9,
                    "grade": 2
                },
                {
                    "band": "F0",
                    "bound": 19,
                    "grade": 5
                },
                {
                    "band": "E0",
                    "bound": 29,
                    "grade": 8
                },
                {
                    "band": "D0",
                    "bound": 39,
                    "grade": 11
                },
                {
                    "band": "C0",
                    "bound": 53,
                    "grade": 14
                },
                {
                    "band": "B0",
                    "bound": 59,
                    "grade": 17
                },
                {
                    "band": "A0",
                    "bound": 69,
                    "grade": 22
                }
            ]
        }';

        $this->expectException('moodle_exception');
        $mapid = import_conversion_map::execute($this->course->id, $jsonmap);
    }

    /**
     * Test invalid import json
     * schedule field must be 'schedulea' or 'scheduleb'
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     */
    public function test_incorrect_schedule_json(): void {
        $jsonmap = '{
            "name": "Test import map",
            "schedule": "notaschedule",
            "maxgrade": 100,
            "inuse": false,
            "map": [

            ]
        }';

        $this->expectException('moodle_exception');
        $mapid = import_conversion_map::execute($this->course->id, $jsonmap);
    }

    /**
     * Test invalid import json
     * Not enough items in the map
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     */
    public function test_incorrect_count_json(): void {
        $jsonmap = '{
            "name": "Test import map",
            "schedule": "scheduleb",
            "maxgrade": 100,
            "inuse": false,
            "map": [
                {
                    "band": "H",
                    "bound": 0,
                    "grade": 0
                },
                {
                    "band": "G0",
                    "bound": 9,
                    "grade": 2
                },
                {
                    "band": "F0",
                    "bound": 19,
                    "grade": 5
                },
                {
                    "band": "E0",
                    "bound": 29,
                    "grade": 8
                },
                {
                    "band": "D0",
                    "bound": 39,
                    "grade": 11
                },
                {
                    "band": "C0",
                    "bound": 53,
                    "grade": 14
                },
                {
                    "band": "A0",
                    "bound": 69,
                    "grade": 22
                }
            ]
        }';

        $this->expectException('moodle_exception');
        $mapid = import_conversion_map::execute($this->course->id, $jsonmap);
    }

    /**
     * Test incorrect Bound order
     * Bound values must be in order.
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     */
    public function test_invalid_bound_order_json(): void {

        // Invalid bound order.
        $jsonmap = '{
            "name": "Invalid map - Bound order",
            "schedule": "schedulea",
            "maxgrade": 10,
            "inuse": false,
            "map": [
                {
                    "band": "H",
                    "bound": 0,
                    "grade": 0
                },
                {
                    "band": "G2",
                    "bound": 0,
                    "grade": 1
                },
                {
                    "band": "G1",
                    "bound": 15,
                    "grade": 2
                },
                {
                    "band": "F3",
                    "bound": 20,
                    "grade": 3
                },
                {
                    "band": "F2",
                    "bound": 0,
                    "grade": 4
                },
                {
                    "band": "F1",
                    "bound": 27,
                    "grade": 5
                },
                {
                    "band": "E3",
                    "bound": 30,
                    "grade": 6
                },
                {
                    "band": "E2",
                    "bound": 0,
                    "grade": 7
                },
                {
                    "band": "E1",
                    "bound": 37,
                    "grade": 8
                },
                {
                    "band": "D3",
                    "bound": 40,
                    "grade": 9
                },
                {
                    "band": "D2",
                    "bound": 0,
                    "grade": 10
                },
                {
                    "band": "D1",
                    "bound": 47,
                    "grade": 11
                },
                {
                    "band": "C3",
                    "bound": 50,
                    "grade": 12
                },
                {
                    "band": "C2",
                    "bound": 0,
                    "grade": 13
                },
                {
                    "band": "C1",
                    "bound": 57,
                    "grade": 14
                },
                {
                    "band": "B3",
                    "bound": 60,
                    "grade": 15
                },
                {
                    "band": "B2",
                    "bound": 0,
                    "grade": 16
                },
                {
                    "band": "B1",
                    "bound": 67,
                    "grade": 17
                },
                {
                    "band": "A5",
                    "bound": 0,
                    "grade": 18
                },
                {
                    "band": "A4",
                    "bound": 0,
                    "grade": 19
                },
                {
                    "band": "A3",
                    "bound": 79,
                    "grade": 20
                },
                {
                    "band": "A2",
                    "bound": 95,
                    "grade": 21
                },
                {
                    "band": "A1",
                    "bound": 92,
                    "grade": 22
                }
            ]
        }';

        $this->expectException('moodle_exception');
        $mapid = import_conversion_map::execute($this->course->id, $jsonmap);
    }

    /**
     * Test incorrect Grade order
     * Grade values must be in order.
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     */
    public function test_invalid_grade_order_json(): void {

        // Invalid grade order.
        $jsonmap = '{
            "name": "Invalid map - Grade order",
            "schedule": "schedulea",
            "maxgrade": 10,
            "inuse": false,
            "map": [
                {
                    "band": "H",
                    "bound": 0,
                    "grade": 0
                },
                {
                    "band": "G2",
                    "bound": 0,
                    "grade": 1
                },
                {
                    "band": "G1",
                    "bound": 15,
                    "grade": 2
                },
                {
                    "band": "F3",
                    "bound": 20,
                    "grade": 3
                },
                {
                    "band": "F2",
                    "bound": 0,
                    "grade": 4
                },
                {
                    "band": "F1",
                    "bound": 27,
                    "grade": 5
                },
                {
                    "band": "E3",
                    "bound": 30,
                    "grade": 6
                },
                {
                    "band": "E2",
                    "bound": 0,
                    "grade": 7
                },
                {
                    "band": "E1",
                    "bound": 37,
                    "grade": 8
                },
                {
                    "band": "D3",
                    "bound": 40,
                    "grade": 9
                },
                {
                    "band": "D2",
                    "bound": 0,
                    "grade": 10
                },
                {
                    "band": "D1",
                    "bound": 47,
                    "grade": 11
                },
                {
                    "band": "C3",
                    "bound": 50,
                    "grade": 12
                },
                {
                    "band": "C2",
                    "bound": 0,
                    "grade": 13
                },
                {
                    "band": "C1",
                    "bound": 57,
                    "grade": 14
                },
                {
                    "band": "B3",
                    "bound": 60,
                    "grade": 15
                },
                {
                    "band": "B2",
                    "bound": 0,
                    "grade": 16
                },
                {
                    "band": "B1",
                    "bound": 67,
                    "grade": 17
                },
                {
                    "band": "A5",
                    "bound": 0,
                    "grade": 18
                },
                {
                    "band": "A4",
                    "bound": 0,
                    "grade": 19
                },
                {
                    "band": "A3",
                    "bound": 79,
                    "grade": 20
                },
                {
                    "band": "A2",
                    "bound": 85,
                    "grade": 18
                },
                {
                    "band": "A1",
                    "bound": 92,
                    "grade": 22
                }
            ]
        }';

        $this->expectException('moodle_exception');
        $mapid = import_conversion_map::execute($this->course->id, $jsonmap);
    }

    /**
     * Test incorrect Bound and Grade order
     * Bound and Grade values must be in order.
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     */
    public function test_invalid_bound_and_grade_order_json(): void {

        // Invalid bound and grade order.
        $jsonmap = '{
            "name": "Invalid map - Bound and Grade order",
            "schedule": "schedulea",
            "maxgrade": 10,
            "inuse": false,
            "map": [
                {
                    "band": "H",
                    "bound": 0,
                    "grade": 0
                },
                {
                    "band": "G2",
                    "bound": 0,
                    "grade": 1
                },
                {
                    "band": "G1",
                    "bound": 15,
                    "grade": 2
                },
                {
                    "band": "F3",
                    "bound": 20,
                    "grade": 3
                },
                {
                    "band": "F2",
                    "bound": 0,
                    "grade": 4
                },
                {
                    "band": "F1",
                    "bound": 27,
                    "grade": 5
                },
                {
                    "band": "E3",
                    "bound": 30,
                    "grade": 6
                },
                {
                    "band": "E2",
                    "bound": 0,
                    "grade": 7
                },
                {
                    "band": "E1",
                    "bound": 37,
                    "grade": 8
                },
                {
                    "band": "D3",
                    "bound": 40,
                    "grade": 9
                },
                {
                    "band": "D2",
                    "bound": 0,
                    "grade": 10
                },
                {
                    "band": "D1",
                    "bound": 47,
                    "grade": 11
                },
                {
                    "band": "C3",
                    "bound": 50,
                    "grade": 12
                },
                {
                    "band": "C2",
                    "bound": 0,
                    "grade": 13
                },
                {
                    "band": "C1",
                    "bound": 57,
                    "grade": 14
                },
                {
                    "band": "B3",
                    "bound": 60,
                    "grade": 15
                },
                {
                    "band": "B2",
                    "bound": 0,
                    "grade": 16
                },
                {
                    "band": "B1",
                    "bound": 67,
                    "grade": 17
                },
                {
                    "band": "A5",
                    "bound": 0,
                    "grade": 18
                },
                {
                    "band": "A4",
                    "bound": 0,
                    "grade": 19
                },
                {
                    "band": "A3",
                    "bound": 79,
                    "grade": 20
                },
                {
                    "band": "A2",
                    "bound": 78,
                    "grade": 18
                },
                {
                    "band": "A1",
                    "bound": 92,
                    "grade": 22
                }
            ]
        }';

        $this->expectException('moodle_exception');
        $mapid = import_conversion_map::execute($this->course->id, $jsonmap);
    }

    /**
     * Check selecting a default map
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     * @covers \local_gugrades\external\select_conversion::execute
     */
    public function test_select_map(): void {
        global $DB;

        // Read map with id 0 (new map) for Schedule A.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map';
        $schedule = 'schedulea';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapida = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapida = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapida
        );
        $mapida = $mapida['mapid'];

        // Read map with id 0 (new map) for Schedule B.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'scheduleb');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map';
        $schedule = 'scheduleb';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapidb = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapidb = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapidb
        );
        $mapidb = $mapidb['mapid'];

        // Select this map.
        $nothing = select_conversion::execute($this->course->id, $this->gradeitemidassign1, 0, $mapida);
        $nothing = external_api::clean_returnvalue(
            select_conversion::execute_returns(),
            $nothing
        );

        // Check it wrote.
        $mapitems = array_values($DB->get_records('local_gugrades_map_item'));
        $this->assertEquals($this->course->id, $mapitems[0]->courseid);
        $this->assertEquals($this->gradeitemidassign1, $mapitems[0]->gradeitemid);

        // Check it's set through the API.
        $mapstuff = get_selected_conversion::execute($this->course->id, $this->gradeitemidassign1, 0);
        $mapstuff = external_api::clean_returnvalue(
            get_selected_conversion::execute_returns(),
            $mapstuff
        );

        $this->assertEquals('Test conversion map', $mapstuff['name']);
        $this->assertEquals('schedulea', $mapstuff['scale']);

        // Select the other map.
        $nothing = select_conversion::execute($this->course->id, $this->gradeitemidassign1, 0, $mapidb);
        $nothing = external_api::clean_returnvalue(
            select_conversion::execute_returns(),
            $nothing
        );

        // Check it updated.
        $mapitems = array_values($DB->get_records('local_gugrades_map_item'));
        $this->assertCount(1, $mapitems);
        $this->assertEquals($mapidb, $mapitems[0]->mapid);

        // Remove maps.
        $nothing = select_conversion::execute($this->course->id, $this->gradeitemidassign1, 0, 0);
        $nothing = external_api::clean_returnvalue(
            select_conversion::execute_returns(),
            $nothing
        );

        // Check it removed.
        $mapitems = array_values($DB->get_records('local_gugrades_map_item'));
        $this->assertCount(0, $mapitems);
    }

    /**
     * Check performing an actual conversion
     * (fun and games this)
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     * @covers \local_gugrades\external\select_conversion::execute
     */
    public function test_do_conversion(): void {
        global $DB;

        // First step - just create a default map
        // Read map with id 0 (new map) for Schedule A.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map';
        $schedule = 'schedulea';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapida = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapida = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapida
        );
        $mapida = $mapida['mapid'];

        // Next step is to import some grades for some test students.
        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign1 (which is useing points).
        $status = import_grades_users::execute($this->course->id, $this->gradeitemidassign1, false, false, $userlist);
        $status = external_api::clean_returnvalue(
            import_grades_users::execute_returns(),
            $status
        );

        // Apply the test conversion map to Assign1.
        $nothing = select_conversion::execute($this->course->id, $this->gradeitemidassign1, 0, $mapida);
        $nothing = external_api::clean_returnvalue(
            select_conversion::execute_returns(),
            $nothing
        );

        // What's in the grades table?
        $grades = array_values($DB->get_records('local_gugrades_grade', ['gradeitemid' => $this->gradeitemidassign1], 'id'));

        $this->assertCount(4, $grades);
        $this->assertEquals(1, $grades[0]->points);
        $this->assertEquals('CONVERTED', $grades[2]->gradetype);
        $this->assertEquals('A1', $grades[2]->displaygrade);
        $this->assertEquals('CONVERTED', $grades[3]->gradetype);
        $this->assertEquals('E3', $grades[3]->displaygrade);
        $this->assertEquals(0, $grades[3]->points);

        // Get the add grade form and make sure it reflects the converted grade.
        $form = get_add_grade_form::execute($this->course->id, $this->gradeitemidassign1, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $this->assertEquals(true, $form['usescale']);
        $scalemenu = $form['scalemenu'];
        $this->assertCount(23, $scalemenu);
        $this->assertEquals(20, $scalemenu[2]['value']);
        $this->assertEquals('F3', $scalemenu[19]['label']);

        // Write a grade back using the scale.
        $nothing = write_additional_grade::execute(
            $this->course->id,
            $this->gradeitemidassign1,
            $this->student->id,
            'SECOND',
            '',
            '',
            18,
            0,
            'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign1, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Check grades.
        $fredgrades = $page['users'][0]['grades'];
        $this->assertCount(4, $fredgrades);
        $this->assertEquals('95.5', $fredgrades[0]['displaygrade']);
        $this->assertEquals('FIRST', $fredgrades[0]['gradetype']);
        $this->assertEquals('A1', $fredgrades[1]['displaygrade']);
        $this->assertEquals('CONVERTED', $fredgrades[1]['gradetype']);
        $this->assertEquals('A5', $fredgrades[2]['displaygrade']);
        $this->assertEquals('SECOND', $fredgrades[2]['gradetype']);

        // Check columns.
        $columns = $page['columns'];
        $this->assertCount(4, $columns);
        $this->assertEquals('FIRST', $columns[0]['gradetype']);
        $this->assertEquals(true, $columns[0]['points']);
        $this->assertEquals('CONVERTED', $columns[2]['gradetype']);
        $this->assertEquals(false, $columns[2]['points']);
        $this->assertEquals('PROVISIONAL', $columns[3]['gradetype']);
        $this->assertEquals(false, $columns[3]['points']);

        // Get the add grade form and make sure unconverted columns have been removed from list of gradetypes.
        $form = get_add_grade_form::execute($this->course->id, $this->gradeitemidassign1, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

    }

    /**
     * Now that maps can have empty values - check the conversion still works as expected.
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     * @covers \local_gugrades\external\select_conversion::execute
     */
    public function test_do_conversion_with_missing_values(): void {
        global $DB;

        // Fake a map with empty values for certain bands.
        $map = [
            [
                "band" => "H",
                "bound" => 0,
                "grade" => 0
            ],
            [
                "band" => "G2",
                "bound" => 0,
                "grade" => 1
            ],
            [
                "band" => "G1",
                "bound" => 15,
                "grade" => 2
            ],
            [
                "band" => "F3",
                "bound" => 20,
                "grade" => 3
            ],
            [
                "band" => "F2",
                "bound" => 0,
                "grade" => 4
            ],
            [
                "band" => "F1",
                "bound" => 27,
                "grade" => 5
            ],
            [
                "band" => "E3",
                "bound" => 30,
                "grade" => 6
            ],
            [
                "band" => "E2",
                "bound" => 0,
                "grade" => 7
            ],
            [
                "band" => "E1",
                "bound" => 37,
                "grade" => 8
            ],
            [
                "band" => "D3",
                "bound" => 40,
                "grade" => 9
            ],
            [
                "band" => "D2",
                "bound" => 0,
                "grade" => 10
            ],
            [
                "band" => "D1",
                "bound" => 47,
                "grade" => 11
            ],
            [
                "band" => "C3",
                "bound" => 50,
                "grade" => 12
            ],
            [
                "band" => "C2",
                "bound" => 0,
                "grade" => 13
            ],
            [
                "band" => "C1",
                "bound" => 57,
                "grade" => 14
            ],
            [
                "band" => "B3",
                "bound" => 60,
                "grade" => 15
            ],
            [
                "band" => "B2",
                "bound" => 0,
                "grade" => 16
            ],
            [
                "band" => "B1",
                "bound" => 67,
                "grade" => 17
            ],
            [
                "band" => "A5",
                "bound" => 0,
                "grade" => 18
            ],
            [
                "band" => "A4",
                "bound" => 0,
                "grade" => 19
            ],
            [
                "band" => "A3",
                "bound" => 79,
                "grade" => 20
            ],
            [
                "band" => "A2",
                "bound" => 0,
                "grade" => 21
            ],
            [
                "band" => "A1",
                "bound" => 92,
                "grade" => 22
            ]
        ];

        // Write the map back.
        $name = 'Test conversion map';
        $schedule = 'schedulea';
        $maxgrade = 100.0;
        $mapida = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapida = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapida
        );
        $mapida = $mapida['mapid'];

        // Next step is to import some grades for some test students.
        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign1 (which is useing points).
        $status = import_grades_users::execute($this->course->id, $this->gradeitemidassign1, false, false, $userlist);
        $status = external_api::clean_returnvalue(
            import_grades_users::execute_returns(),
            $status
        );

        // Apply the test conversion map to Assign1.
        $nothing = select_conversion::execute($this->course->id, $this->gradeitemidassign1, 0, $mapida);
        $nothing = external_api::clean_returnvalue(
            select_conversion::execute_returns(),
            $nothing
        );

        // What's in the grades table?
        $grades = array_values($DB->get_records('local_gugrades_grade', ['gradeitemid' => $this->gradeitemidassign1], 'id'));

        $this->assertCount(4, $grades);
        $this->assertEquals(1, $grades[0]->points);
        $this->assertEquals('CONVERTED', $grades[2]->gradetype);
        $this->assertEquals('A1', $grades[2]->displaygrade);
        $this->assertEquals('CONVERTED', $grades[3]->gradetype);
        $this->assertEquals('E3', $grades[3]->displaygrade);
        $this->assertEquals(0, $grades[3]->points);

        // Get the add grade form and make sure it reflects the converted grade.
        $form = get_add_grade_form::execute($this->course->id, $this->gradeitemidassign1, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        $this->assertEquals(true, $form['usescale']);
        $scalemenu = $form['scalemenu'];
        $this->assertCount(23, $scalemenu);
        $this->assertEquals(17, $scalemenu[2]['value']);
        $this->assertEquals('A4', $scalemenu[19]['label']);

        // Write a grade back using the scale.
        $nothing = write_additional_grade::execute(
            $this->course->id,
            $this->gradeitemidassign1,
            $this->student->id,
            'SECOND',
            '',
            '',
            17,
            0,
            'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign1, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Check grades.
        $fredgrades = $page['users'][0]['grades'];
        $this->assertCount(4, $fredgrades);
        $this->assertEquals('95.5', $fredgrades[0]['displaygrade']);
        $this->assertEquals('FIRST', $fredgrades[0]['gradetype']);
        $this->assertEquals('A1', $fredgrades[1]['displaygrade']);
        $this->assertEquals('CONVERTED', $fredgrades[1]['gradetype']);
        $this->assertEquals('B1', $fredgrades[2]['displaygrade']);
        $this->assertEquals('SECOND', $fredgrades[2]['gradetype']);

        // Check columns.
        $columns = $page['columns'];
        $this->assertCount(4, $columns);
        $this->assertEquals('FIRST', $columns[0]['gradetype']);
        $this->assertEquals(true, $columns[0]['points']);
        $this->assertEquals('CONVERTED', $columns[2]['gradetype']);
        $this->assertEquals(false, $columns[2]['points']);
        $this->assertEquals('PROVISIONAL', $columns[3]['gradetype']);
        $this->assertEquals(false, $columns[3]['points']);

        // Get the add grade form and make sure unconverted columns have been removed from list of gradetypes.
        $form = get_add_grade_form::execute($this->course->id, $this->gradeitemidassign1, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

    }

    /**
     * Test conversion at limits
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     * @covers \local_gugrades\external\select_conversion::execute
     */
    public function test_conversion_limits(): void {
        global $DB;

        // First step - just create a default map
        // Read map with id 0 (new map) for Schedule A.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map';
        $schedule = 'schedulea';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapida = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapida = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapida
        );
        $mapida = $mapida['mapid'];

        // Next step is to import some grades for some test students.
        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign3 (which is useing points from 0 to 23).
        $status = import_grades_users::execute($this->course->id, $this->gradeitemidassign3, false, false, $userlist);
        $status = external_api::clean_returnvalue(
            import_grades_users::execute_returns(),
            $status
        );

        // Apply the test conversion map to Assign3.
        $nothing = select_conversion::execute($this->course->id, $this->gradeitemidassign3, 0, $mapida);
        $nothing = external_api::clean_returnvalue(
            select_conversion::execute_returns(),
            $nothing
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign3, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        // Check grades.
        $grades1 = $page['users'][0]['grades'];
        $this->assertEquals('H', $grades1[1]['displaygrade']);
        $this->assertEquals('CONVERTED', $grades1[1]['gradetype']);
        $grades2 = $page['users'][1]['grades'];
        $this->assertEquals('A1', $grades2[1]['displaygrade']);
        $this->assertEquals('CONVERTED', $grades2[1]['gradetype']);
    }

    /**
     * Test conversion of admin grades
     * Conversion should 'skip' admin grades
     * @covers \local_gugrades\external\get_conversion_map::execute
     * @covers \local_gugrades\external\write_conversion_map::execute
     * @covers \local_gugrades\external\select_conversion::execute
     */
    public function test_conversion_admin_grades(): void {
        global $DB;

        // First step - just create a default map
        // Read map with id 0 (new map) for Schedule A.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map';
        $schedule = 'schedulea';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapida = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapida = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapida
        );
        $mapida = $mapida['mapid'];

        // Next step is to import some grades for some test students.
        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign1 (which is useing points).
        $status = import_grades_users::execute($this->course->id, $this->gradeitemidassign1, false, false, $userlist);
        $status = external_api::clean_returnvalue(
            import_grades_users::execute_returns(),
            $status
        );

        // Add additional grade.
        // IS not currently used
        /*
        $nothing = write_additional_grade::execute(
            $this->course->id,
            $this->gradeitemidassign1,
            $this->student->id,
            'SECOND',
            '',
            'IS',
            0,
            0,
            'Test admin grade'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Apply the test conversion map to Assign1.
        $nothing = select_conversion::execute($this->course->id, $this->gradeitemidassign1, 0, $mapida);
        $nothing = external_api::clean_returnvalue(
            select_conversion::execute_returns(),
            $nothing
        );

        // Get capture page.
        $page = get_capture_page::execute($this->course->id, $this->gradeitemidassign1, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_capture_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $grades = $fred['grades'];
        $this->assertCount(4, $grades);
        $this->assertEquals('IS', $grades[2]['displaygrade']);
        $this->assertEquals('CONVERTED', $grades[2]['gradetype']);
        $this->assertEquals('IS', $grades[3]['displaygrade']);
        $this->assertEquals('PROVISIONAL', $grades[3]['gradetype']);
        */
    }
}
