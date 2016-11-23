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
 * Strings for component 'enrol_database', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   enrol_gudatabase
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['autherror'] = 'An error occurred processing UofG automatic enrolment - additional information has been logged';
$string['cannotunenrol'] = 'You are not permitted to unenrol yourself from this course. You must unenrol in MyCampus first and then wait at
    least 24 hours before trying again.';
$string['classlisttable'] = 'Class list table';
$string['classlisttable_desc'] = 'Table containing information about group enrolments within courses';
$string['codelist'] = 'More codes (one per line)';
$string['codelist_help'] = 'You can add more codes for this course here. Add one code per line then press Save';
$string['codes'] = 'Codes';
$string['codesdefined'] = 'Valid codes defined for this plugin (NOTE: shortname and idnumber field codes are <b>disabled</b>)';
$string['codesenroltable'] = 'Remote codes table';
$string['codesenroltable_desc'] = 'Table containing information about course codes';
$string['config'] = 'Configuration';
$string['coursegroups'] = 'Course groups';
$string['coursegroups_help'] = 'When this box is ticked, groups will be created for each course code defined in the course (filled with those users).';
$string['defaultperiod'] = 'Default enrolment period';
$string['defaultperiod_help'] = 'Set the default duration for enrolments';
$string['displayaverage'] = 'Approx mean courses process each cron = {$a}';
$string['enrolenddate'] = 'Enrol end date';
$string['enrolenddate_help'] = 'If enabled, users will be unenrolled (or their role changed) after this date/time. Note that this only applies to users enrolled AFTER this is set or changed.';
$string['expirerole'] = 'Action after period';
$string['expirerole_help'] = 'After period expires (if set) switch to this role or unenrol completely';
$string['groups'] = 'Groups';
$string['groupsinstruction'] = 'Select the classes for which you require groups to be created. Note, students will only ever be <b>added</b> to groups';
$string['gudatabase:config'] = 'Configure gudatabase plugin';
$string['gudatabase:manage'] = 'Manage gudatabase plugin';
$string['gudatabase:unenrol'] = 'Unenrol users from gudatabase';
$string['gudatabase:unenrolself'] = 'Can unenrol self if not in external database';
$string['legacycodes'] = 'Valid codes defined in course settings (shortname and idnumber fields) and here...';
$string['newwarning'] = 'Click \'Add method\' and then re-edit to define codes and groups.';
$string['nocourseinfo'] = 'No course info';
$string['nolegacycodes'] = 'There are no codes defined for this course';
$string['pluginname'] = 'UofG Enrolment database';
$string['pluginname_desc'] = 'You can use an external database (of nearly any kind) to control your enrolments. It is assumed your external database contains at least a field containing a course ID, and a field containing a user ID. These are compared against fields that you choose in the local course and user tables.';
$string['savewarning'] = 'Saving this page will update all enrolments and groups for this course. It could take several minutes. Please be patient.';
$string['settingsclasslist'] = 'Class list table';
$string['settingscodes'] = 'Enable codes in course settings';
$string['settingscodes_help'] = 'When enabled also process any valid codes found in this course\'s shortname or idnumber fields. Only one enrolment method per course should have this set.';
$string['settingsheadercodes'] = 'Remote codes table';
$string['scheduledname'] = 'Synchronise guenrolments';
$string['starcode'] = 'Full year psuedo-code';
$string['status'] = 'Enable existing enrolments';
$string['status_desc'] = 'Enable gudatabase method in new courses.';
$string['status_help'] = 'If disabled all existing gudatabase enrolments are suspended and new users can not enrol.';
$string['timelimit'] = 'Cron time limit';
$string['timelimit_desc'] = 'Maximum time that cron process may run for';
$string['unenrol'] = 'Unenrol';
$string['unenrolselfconfirm'] = 'Are you sure you want to unenrol from this course?';
