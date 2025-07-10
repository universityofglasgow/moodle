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
 * @package    local_loadtest
 * @copyright  2025 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

$services = [
    'local_loadtest' => [
        'functions' => [
            'local_loadtest_get_loads',
            'local_loadtest_get_redis',
            'local_loadtest_get_eventcounts',
            'local_loadtest_get_stats',
            'local_loadtest_get_database',
        ],
        'enabled' => 1,
    ]
];

 $functions = [
    'local_loadtest_get_loads' => [
        'classname' => 'local_loadtest\external\get_loads',
        'description' => 'Read cached server loads',
        'type' => 'read',
    ],
    'local_loadtest_get_redis' => [
        'classname' => 'local_loadtest\external\get_redis',
        'description' => 'Read current redis stats',
        'type' => 'read',
    ],
    'local_loadtest_get_eventcounts' => [
        'classname' => 'local_loadtest\external\get_eventcounts',
        'description' => 'Read event/log counts since time',
        'type' => 'read',
    ],
    'local_loadtest_get_stats' => [
        'classname' => 'local_loadtest\external\get_stats',
        'description' => 'Bunch of random statistics about the site',
        'type' => 'read',
    ],
    'local_loadtest_get_database' => [
        'classname' => 'local_loadtest\external\get_database',
        'description' => 'Get database processes and other stats',
        'type' => 'read',
    ]
];