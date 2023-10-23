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
 * GUID report
 *
 * @package    report_guid
 * @copyright  2017-19 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

// Parameters.
$courseid = required_param('id', PARAM_INT);

// Security.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);
require_login($course);
require_capability('report/guid:enroldownload', $context);

// Get enrolment info
$users = get_enrolled_users($context);
$allgroups = groups_get_all_groups($courseid);
$items = [];
foreach ($users as $user) {
    $item = new stdClass;
    $item->username = $user->username;

    // Groups
    $groups = groups_get_user_groups($courseid, $user->id);
    $grouplist = [];
    $usergroups = $groups[0];
    foreach ($usergroups as $usergroup) {
        $grouplist[] = $allgroups[$usergroup]->name;
    }
    $item->groups = $grouplist;
    
    $items[] = $item;
}

$filename = 'enrolexport_' . date('Y-m-d');
$dataformat = 'csv';
$columns = ['username', 'groups'];
\core\dataformat::download_data($filename, $dataformat, $columns, $items, function($item) {
    return array_merge([$item->username], $item->groups);
});
exit;