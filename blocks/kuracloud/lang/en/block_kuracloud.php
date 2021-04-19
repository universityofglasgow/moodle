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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'kuraCloud';
$string['kuracloud:addinstance'] = 'Add a kuraCloud block';
$string['kuracloud:myaddinstance'] = 'Add a kuraCloud block to my Moodle';
$string['kuracloud:mapcourses'] = 'Map courses between Moodle and kuraCloud';
$string['kuracloud:syncusers'] = 'Sync users with kuraCloud';
$string['kuracloud:syncgrades'] = 'Sync grades from kuraCloud';
$string['kuracloud:participate'] = 'Can be exported to kuraCloud';
$string['manageendpoints'] = 'Manage kuraCloud API Connections';
$string['token'] = 'API Token';
$string['endpoint'] = 'KuraCloud API URL';
$string['status'] = 'Status';
$string['instancename'] = 'Instance Name';
$string['lmsenabled'] = 'LMS Integration';
$string['apistatus:ok'] = 'OK';
$string['enabled'] = 'Enabled';
$string['disabled'] = 'Disabled';
$string['unknown'] = 'Unknown';
$string['actions'] = 'Actions';
$string['addnewendpoint'] = 'Add a new connection';
$string['confirmendpointdelete'] = 'Are you sure you want to delete this API Token and any associated course mappings?';
$string['coursemapping'] = 'kuraCloud course mapping';
$string['remotecourse'] = 'kuraCloud course';
$string['remotecoursedefault'] = 'Please select a course';
$string['currentmapping'] = 'Current course: ';
$string['confirmdeletemapping'] = 'Are you sure you want to delete the mapping to "{$a}"?';
$string['nomapping'] = 'Setup kuraCloud mapping';
$string['checkcoursemappings'] = 'Check kuraCloud course mappings';
$string['notlmsenabled'] = 'LMS integration not enabled on kuraCloud course';
$string['remotecoursemissing'] = 'kuraCloud course has been deleted';
$string['syncusers'] = 'Sync user enrolments';
$string['toupdatecount'] = 'Students to update: {$a}';
$string['toaddcount'] = 'Students to add: {$a}';
$string['todeletecount'] = 'Students to delete: {$a}';
$string['torestorecount'] = 'Students to restore: {$a}';
$string['morethan'] = 'More than {$a} students';
$string['apierrorunknown'] = 'The kuraCloud API has returned an unknown error. Please try again later. Code: {$a}';
$string['apierrortransport'] = 'There was an error connecting to the kuraCloud API. The error was: {$a}';
$string['apierrorgeneral'] = '{$a}';
$string['confirmsyncusers'] = 'Click \'Continue\' to make the following enrolment changes in kuraCloud: ';
$string['nosyncusers'] = 'Enrolments are currently in sync - no changes to make';
$string['gradeitemname'] = '{$a->title} kuraID: {$a->lessonid}-{$a->revisionid}';
$string['syncgrades'] = 'Sync grades from kuraCloud';
$string['needusersync'] = 'Moodle and kuraCloud users need to be synced first';
$string['confirmgradesync'] = 'Sync grades from kuraCloud?';
$string['gradesynccomplete'] = 'All grades have been synced';
$string['statusok'] = 'Connection to kuraCloud OK';
$string['remotecoursedeleted'] = 'Error: The mapped kuraCloud course has been deleted - Please restore the course or map this course to a different kuraCloud course.';

$string['privacy:metadata:block_kuracloud_users'] = 'Information to match Moodle users with kuraCloud users';
$string['privacy:metadata:block_kuracloud_users:userid'] = 'Moodle user id';
$string['privacy:metadata:block_kuracloud_users:remote_studentid'] = 'kuraCloud student id';

$string['privacy:metadata:kuracloud_sync'] = 'In order to sync kuraCloud with Moodle, user data needs to be sent to kuraCloud';
$string['privacy:metadata:kuracloud_sync:firstname'] = 'Your first name is sent to kuraCloud to allow a better user experience';
$string['privacy:metadata:kuracloud_sync:lastname'] = 'Your last name is sent to kuraCloud to allow a better user experience';
$string['privacy:metadata:kuracloud_sync:idnumber'] = 'Your Moodle id number is sent to help match up with your Moodle account, and help manage student accounts in kuraCloud';
$string['privacy:metadata:kuracloud_sync:email'] = 'Your email address is used to create your account and to login to kuraCloud, and for emails from kuraCloud';
