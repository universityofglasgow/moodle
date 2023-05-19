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
    'local_gugrades_get_user_picture_url' => [
        'classname' => 'local_gugrades\external\get_user_picture_url',
        'description' => 'Get the URL of the user picture',
        'type' => 'read',
        'ajax' => true,
    ],
];    
