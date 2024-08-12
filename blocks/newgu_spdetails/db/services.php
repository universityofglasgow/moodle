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
 * Web Service function calls.
 *
 * @package    block_newgu_spdetails
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Not mentioned in the Moodle spec - but a list of the services our plugin provides...
$services = [
    'block_newgu_spdetails' => [
        'functions' => [
            'block_newgu_spdetails_get_assessmentsummary',
            'block_newgu_spdetails_get_assessments',
            'block_newgu_spdetails_get_assessmentsduesoon',
        ],
        'requiredcapability' => '',
        'restrictedusers' => 1,
        'enabled' => 1,
    ],
];

$functions = [
    'block_newgu_spdetails_get_assessmentsummary' => [
        'classname'   => 'block_newgu_spdetails\external\get_assessmentsummary',
        'description' => 'Get users assessment statistics',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
        'services'    => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'block_newgu_spdetails_get_assessmentsummarybytype' => [
        'classname'   => 'block_newgu_spdetails\external\get_assessmentsummarybytype',
        'description' => 'Return only assessments due by selected type: submitted, overdue etc',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
        'services'    => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'block_newgu_spdetails_get_assessments' => [
        'classname'   => 'block_newgu_spdetails\external\get_assessments',
        'description' => 'Display current and past assessments on the Student Dashboard',
        'type'        => 'read',
        'ajax'          => true,
        'loginrequired' => true,
        'services'    => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'block_newgu_spdetails_get_assessmentsduesoon' => [
        'classname'   => 'block_newgu_spdetails\external\get_assessmentsduesoon',
        'description' => 'Return assessments due in the immediate or near future',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
        'services'    => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
    'block_newgu_spdetails_get_assessmentsduebytype' => [
        'classname'   => 'block_newgu_spdetails\external\get_assessmentsduebytype',
        'description' => 'Return only assessments due by selected type: 24hrs, 7 days, 1 month',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
        'services'    => [
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
];
