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
 * GuGrades initial install
 *
 * @package    local_gugrades
 * @copyright  2025 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/gugrades/locallib.php');

function xmldb_local_gugrades_install() {
    global $CFG, $DB;

    // Add scales for Schedule A and Schedule B.
    $schedulea = (object)[
        'courseid' => 0,
        'userid' => 0,
        'name' => 'Schedule A',
        'scale' => 'H,G2,G1,F3,F2,F1,E3,E2,E1,D3,D2,D1,C3,C2,C1,B3,B2,B1,A5,A4,A3,A2,A1',
        'description' => 'University of Glasgow Schedule A',
        'descriptionformat' => 0,
        'timemodified' => time(),
    ];
    $scheduleaid = $DB->insert_record('scale', $schedulea);
    $scheduleb = (object)[
        'courseid' => 0,
        'userid' => 0,
        'name' => 'Schedule B',
        'scale' => 'H,G0,F0,E0,D0,C0,B0,A0',
        'description' => 'University of Glasgow Schedule B',
        'descriptionformat' => 0,
        'timemodified' => time(),
    ];
    $schedulebid = $DB->insert_record('scale', $schedulea);

    // Add plugins config for above scales.
    set_config(
        'scaletype_' . $scheduleaid,
        'schedulea',
        'local_gugrades'
    );
    set_config(
        'scaletype_' . $schedulebid,
        'scheduleb',
        'local_gugrades'
    );
    $mapa = "H, 0
 G2, 1
 G1, 2
 F3, 3
 F2, 4
 F1, 5
 E3, 6
 E2, 7
 E1, 8
 D3, 9
 D2, 10
 D1, 11
 C3, 12
 C2, 13
 C1, 14
 B3, 15
 B2, 16
 B1, 17
 A5, 18
 A4, 19
 A3, 20
 A2, 21
 A1, 22
";
    set_config(
        'scalevalue' . $scheduleaid,
        $mapa,
        'local_gugrades'
    );
    $mapb = "H, 0
G0, 2
F0, 5
E0, 8
D0, 11
C0, 14
B0, 17
A0, 22";
    set_config(
        'scalevalue' . $schedulebid,
        $mapb,
        'local_gugrades'
    );

    // Scales updated routine.
    scale_setting_updated('scaletype_' . $scheduleaid);
    scale_setting_updated('scaletype_' . $schedulebid);

    // Set admingrades.
    \local_gugrades\admingrades::setting_defaults();

    // Set conversion map defaults.
     $defaulta = '10, 15, 20, 24, 27, 30, 34, 37, 40, 44, 47, 50, 54, 57, 60, 64, 67, 70, 74, 79, 85, 92';
     set_config('mapdefault_schedulea', $defaulta, 'local_gugrades');
     $defaultb = '9, 19, 29, 39, 53, 59, 69';
     set_config('mapdefault_scheduleb', $defaultb, 'local_gugrades');
}
