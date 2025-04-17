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
 * Lang strings
 *
 * @package    report
 * @subpackage guenrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['afterenddate'] = '<b>Automatic enrolment disabled!</b> A course end date is set and is in the past.';
$string['beforestartdate'] = '<b>Automatic enrolment disabled!</b> The course start date is in the future.';
$string['coursecode'] = 'Course code';
$string['coursename'] = 'Course \'{$a}\'.';
$string['duplicate'] = 'Duplicate';
$string['enrolled'] = 'Enrolled';
$string['enrolmentscode'] = 'Enrolments for code <strong>{$a}</strong>.';
$string['guenrol:view'] = 'View course participation report';
$string['idnumber'] = 'ID number...';
$string['lastupdate'] = 'Course last synchronised at {$a}';
$string['listofcodes'] = 'Valid course codes';
$string['location'] = 'Location';
$string['locationnotdefined'] = 'undefined';
$string['more'] = 'More...';
$string['never'] = 'never';
$string['nocode'] = 'No code';
$string['nocodes'] = 'There are no automatic enrolment codes defined in this course';
$string['noremovedusers'] = 'There are no users that should not be here (according to MyCampus feed)';
$string['notenrolled'] = 'Not enrolled';
$string['notices'] = 'Notices';
$string['nousers'] = 'No users to display';
$string['notvisible'] = '<b>Automatic enrolment disabled!</b> This course is hidden.';
$string['notpossible'] = 'Enrolment is "not possible" for this course';
$string['oktoremove'] = 'Ok to unenrol users?';
$string['oktorevert'] = 'Are you sure you want to unenrol all MyCampus users?';
$string['page-report-guenrol-x'] = 'Any participation report';
$string['page-report-guenrol-index'] = 'Course participation report';
$string['plugin'] = 'Plugin...';
$string['pluginname'] = 'UofG enrolment status';
$string['privacy:metadata'] = 'The enrolment status report does not store any personal data';
$string['remove'] = 'Remove extras';
$string['removelist'] = 'These are all the users who have previously been automatically enrolled but are no longer in MyCampus lists. Click the \'Unenrol now\' button at the bottom of the page to unenrol them from this course';
$string['remove_desc'] = 'Unenrol users who are in the course but not listed in MyCampus data. Cleans up users who may have left the course. Run Synchronise first!';
$string['removed'] = 'Show users removed from MyCampus but still in course';
$string['removeddone'] = 'Users have been unenrolled';
$string['removedusers'] = 'Users removed from MyCampus but still in course';
$string['revert'] = 'Remove auto enrolled';
$string['revert_desc'] = 'Unenrol users who ARE in MyCampus list. Use this if auto-enrolment is enabled by mistake. Auto enrolment plugin will be disabled and any valid codes in shortname or idnumber fields will be prefixed by an underscore to disable them.';
$string['shortname'] = 'Short name...';
$string['showall'] = 'Show all enrolments';
$string['subjectname'] = 'Subject \'{$a}\'.';
$string['sync'] = 'Synchronise';
$string['sync_desc'] = 'Synchronise current enrolments against MyCampus data. Strongly advised. For larger classes this may take some time. No users are removed.';
$string['synccourse'] = 'Re-sync course enrolments (may take a little time)';
$string['title'] = 'Automatic enrolment status';
$string['totalcodeusers'] = 'Total number of users of this code is {$a}';
$string['totalremoveusers'] = 'Total number of removed users is {$a}';
$string['unenrol'] = 'Unenrol removed users';
$string['usercodes'] = 'Users for the following course codes';
$string['userlist'] = 'List of users for specified course code(s) from MyCampus feeds.';
$string['utilities'] = 'Utilities';
