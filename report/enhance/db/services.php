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
 * Web services
 *
 * @package    report_enhance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'report_enhance_get_votes' => [
        'classname' => 'report_enhance_external',
        'methodname' => 'get_votes',
        'classpath' => 'report/enhance/externallib.php',
        'description' => 'Get vote count and user involvement',
        'type' => 'read',
        'ajax' => true,
    ],
    'report_enhance_set_vote' => [
        'classname' => 'report_enhance_external',
        'methodname' => 'set_vote',
        'classpath' => 'report/enhance/externallib.php',
        'description' => 'Set/reset vote for given user.',
        'type' => 'write',
        'ajax' => true,
    ],
];
