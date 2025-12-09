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
 * TODO describe file
 *
 * @package    local_ltiusage
 * @copyright  2025 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_ltiusage_get_pagination' => [
        'classname' => 'local_ltiusage\\external\\get_pagination',
        'methodname' => 'execute',
        'description' => 'Get paginated LTI usage data for a specific tool type',
        'type' => 'read',
        'capabilities' => 'local/ltiusage:viewltiusage',
        'ajax' => true,
    ],
];

$services = [
    'LTI Usage' => [
        'functions' => ['local_ltiusage_get_pagination'],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'ltiusage',
    ],
];
