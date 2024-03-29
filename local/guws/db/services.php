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
 * Declare
 *
 * @package    local_guws
 * @copyright  2018 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$services = array(

        // Define service for AMS
        'AMS' => array(
            'functions' => ['local_guws_ams_searchassign', 'local_guws_ams_download', 'local_guws_ams_upload'],
            'requiredcapability' => '',
            'restrictedusers' => 1,
            'enabled' => 1,
        ),
);

$functions = array(
    'local_guws_ams_searchassign' => array(
        'classname'   => 'local_guws_external',
        'methodname'  => 'ams_searchassign',
        'classpath'   => 'local/guws/externallib.php',
        'description' => 'Search assignments for those with name including specified string.',
        'type'        => 'read',
    ),
    'local_guws_ams_download' => array(
        'classname'   => 'local_guws_external',
        'methodname'  => 'ams_download',
        'classpath'   => 'local/guws/externallib.php',
        'description' => 'Download assignment participants, grades and feedback.',
        'type'        => 'read',
    ),
    'local_guws_ams_upload' => array(
        'classname'   => 'local_guws_external',
        'methodname'  => 'ams_upload',
        'classpath'   => 'local/guws/externallib.php',
        'description' => 'Upload assignment grades and feedback.',
        'type'        => 'write',
    ),
    'local_guws_ams_upload' => array(
        'classname'   => 'local_guws_external',
        'methodname'  => 'ams_upload',
        'classpath'   => 'local/guws/externallib.php',
        'description' => 'Upload assignment grades and feedback.',
        'type'        => 'write',
    ),
    'local_guws_alarmbell_query' => array(
        'classname'   => 'local_guws_external',
        'methodname'  => 'alarmbell_query',
        'classpath'   => 'local/guws/externallib.php',
        'description' => 'Query log info for Alarm Bell project',
        'type'        => 'read',
    ),
    'local_guws_portal_courses' => array(
        'classname'   => 'local_guws_external',
        'methodname'  => 'portal_courses',
        'classpath'   => 'local/guws/externallib.php',
        'description' => 'Get list of courses for portal ',
        'type'        => 'read',
    ),
    'local_guws_guid_completion' => array(
        'classname'   => 'local_guws_external',
        'methodname'  => 'guid_completion',
        'classpath'   => 'local/guws/externallib.php',
        'description' => 'Get course completion status for list of GUIDs ',
        'type'        => 'read',
    ),  
    'local_guws_gcat_getdashboard' => array(
        'classname'   => 'local_guws_external',
        'methodname'  => 'gcat_getdashboard',
        'classpath'   => 'local/guws/externallib.php',
        'description' => 'Get data from GCAT student dashboard ',
        'type'        => 'read',
    ),
    'local_guws_gcat_userhasdata' => array(
        'classname'   => 'local_guws_external',
        'methodname'  => 'gcat_userhasdata',
        'classpath'   => 'local/guws/externallib.php',
        'description' => 'Does the user have any GCAT data to display',
        'type'        => 'read',
    ),   
);
