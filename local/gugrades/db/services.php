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
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 $services = array(
    'local_gugrades' => array(
        'functions' => ['local_gugrades_get_user_grades'],
        'requiredcapability' => '',
        'restrictedusers' => 1,
        'enabled' => 1,
    ),
);

$functions = [
    'local_gugrades_get_levelonecategories' => [
        'classname' => 'local_gugrades\external\get_levelonecategories',
        'description' => 'Gets first level categories (should be summative, formative and so on)',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_gugrades_get_activities' => [
        'classname' => 'local_gugrades\external\get_activities',
        'description' => 'Gets individual activities or nth level categories (sub - category grades)',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_gugrades_get_capture_page' => [
        'classname' => 'local_gugrades\external\get_capture_page',
        'description' => 'Get grade capture table given activity, ',
        'type' => 'read',
        'ajax' => true,
    ],    
    'local_gugrades_get_grade_item' => [
        'classname' => 'local_gugrades\external\get_grade_item',
        'description' => 'Gets the activity information for a given grade item id',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_gugrades_import_grade' => [
        'classname' => 'local_gugrades\external\import_grade',
        'description' => 'Import 1st grade from activity or grade item for given user',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_gugrades_import_grades_users' => [
        'classname' => 'local_gugrades\external\import_grades_users',
        'description' => 'Import 1st grade from activity or grade item for list of users',
        'type' => 'write',
        'ajax' => true,
    ],
    'local_gugrades_get_user_picture_url' => [
        'classname' => 'local_gugrades\external\get_user_picture_url',
        'description' => 'Get the URL of the user picture',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_gugrades_get_user_grades' => [
        'classname' => 'local_gugrades\external\get_user_grades',
        'description' => 'Get all user grades for given user (site-wide)',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_gugrades_get_history' => [
        'classname' => 'local_gugrades\external\get_history',
        'description' => 'Get the grade history for given user / grade item.',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_gugrades_get_audit' => [
        'classname' => 'local_gugrades\external\get_audit',
        'description' => 'Get the audit trail for given or current user.',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_gugrades_has_capability' => [
        'classname' => 'local_gugrades\external\has_capability',
        'description' => 'Check if current user has a given capability.',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_gugrades_is_grades_imported' => [
        'classname' => 'local_gugrades\external\is_grades_imported',
        'description' => 'Check if selected grade item has had any grades imported.',
        'type' => 'read',
        'ajax' => true,
    ],
];    
