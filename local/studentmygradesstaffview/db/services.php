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
 * Define the web services.
 *
 * @package     local_studentmygradesstaffview
 * @author      Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright   2025 University of Glasgow
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$services = [
    'local_studentmygradesstaffview' => [
        'functions' => [
            'local_studentmygradesstaffview_get_all_strings',
            'local_studentmygradesstaffview_get_students',
        ],
        'requiredcapability' => 'local/studentmygradesstaff:view',
        'restrictedusers' => 1,
        'enabled' => 1,
    ],
];

$functions = [
    'local_studentmygradesstaffview_get_all_strings' => [
        'classname' => 'local_studentmygradesstaffview\external\get_all_strings',
        'description' => 'Load all the strings for this plugin.',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_studentmygradesstaffview_get_students' => [
        'classname' => 'local_studentmygradesstaffview\external\get_students',
        'description' => 'Fetch a list of enrolled students for the given course.',
        'type' => 'read',
        'ajax' => true,
    ],
];
