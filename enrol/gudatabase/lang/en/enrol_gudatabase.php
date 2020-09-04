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

$string['allowhidden'] = 'Allow hidden course';
$string['allowhidden_help'] = 'If enabled, automatic enrolment will function even if the course is hidden. By default hidden courses are ignored';
$string['allowunenrol'] = 'Allow unenrol features';
$string['allowunenrol_desc'] = 'Enable remove from course/group features at instance level';
$string['autherror'] = 'An error occurred processing UofG automatic enrolment - additional information has been logged';
$string['cannotunenrol'] = 'You are not permitted to unenrol yourself from this course. You must unenrol in MyCampus first and then wait at
    least 24 hours before trying again.';
$string['classlisttable'] = 'Class list table';
$string['classlisttable_desc'] = 'Table containing information about group enrolments within courses';
$string['codelist'] = 'More codes (one per line)';
$string['codelist_help'] = 'You can add more codes for this course here. Add one code per line then press Save';
$string['codes'] = 'Codes';
$string['codesettings'] = 'Auto enrolment course codes';
$string['codesdefined'] = 'Valid codes defined for this plugin (NOTE: shortname and idnumber field codes are <b>disabled</b>)';
$string['codesenroltable'] = 'Remote codes table';
$string['codesenroltable_desc'] = 'Table containing information about course codes';
$string['config'] = 'Configuration';
$string['coursegroups'] = 'Course groups';
$string['coursegroups_help'] = 'When this box is ticked, groups will be created for each course code defined in the course (filled with those users).';
$string['defaultperiod'] = 'Default enrolment period';
$string['defaultperiod_help'] = 'Set the default duration for enrolments';
$string['days30'] = '30 days';
$string['days60'] = '60 days';
$string['days90'] = '90 days';
$string['days180'] = '180 days';
$string['days270'] = '270 days';
$string['days365'] = '1 year';
$string['daysoff'] = 'Disable';
$string['displayaverage'] = 'Approx mean courses process each cron = {$a}';
$string['enablegroupremove'] = 'Enable removal from groups';
$string['enablegroupremove_help'] = 'If enabled, students will be removed from a group if they are removed from the corresponding class list in MyCampus. Otherwise they will stay in the group until removed manually.<br />WARNING: Possible loss of data if enabled. NOTE: A course end date MUST be set in the course settings.';
$string['enableunenrol'] = 'Enable user unenrol';
$string['enableunenrol_help'] = 'If enabled, students will be unenrolled shortly after they are removed from the course in MyCampus. Otherwise they will stay enrolled even if they are removed in MyCampus.<br />WARNING: Risk of data loss. If students are unenrolled their submissions and data are irrevocably deleted. NOTE: A course end date MUST be set in the course settings.';
$string['enforceenddate'] = 'Enforce end date';
$string['enforceenddate_desc'] = 'Automatic enrolment is only functional between course start and end dates. It will be disabled if no course end date is supplied';
$string['enrolenddate'] = 'Enrol end date';
$string['enrolenddate_help'] = 'If enabled, users will be unenrolled (or their role changed) after this date/time. Note that this only applies to users enrolled AFTER this is set or changed.';
$string['enrolguard'] = 'Enrol guard period';
$string['enrolguard_help'] = 'Enrolment will be disabled after course start date plus this number of days';
$string['enrolguardwarning'] = 'Enrolment disabled as guard period from course start period elapsed';
$string['enrolmentdisabled'] = '<b>Enrolment is disabled for this course</b> See <a href="{$a}">Enrolment Report</a> for more information.';
$string['enrolmentnotpossible'] = 'ENROLMENT IS DISABLED FOR THIS INSTANCE';
$string['expirerole'] = 'Action after period';
$string['expirerole_help'] = 'After period expires (if set) switch to this role or unenrol completely';
$string['groups'] = 'Groups';
$string['groupsettings'] = 'Automatic group creation';
$string['groupsinstruction'] = 'Select the classes for which you require groups to be created. NOTE: You must add course codes first, save, and revisit this page to see the possible groups.';
$string['gudatabase:config'] = 'Configure gudatabase plugin';
$string['gudatabase:enableunenrol'] = 'Can enable option for users to be unenrolled when missing from MyCampus feed (data loss!)';
$string['gudatabase:manage'] = 'Manage gudatabase plugin';
$string['gudatabase:unenrol'] = 'Unenrol users from gudatabase';
$string['gudatabase:unenrolself'] = 'Can unenrol self if not in external database';
$string['legacycodes'] = 'Valid codes defined in course settings (shortname and idnumber fields) and here...';
$string['mainsettings'] = 'Main settings';
$string['newwarning'] = 'Click \'Add method\' and then re-edit to define codes and groups.';
$string['nocourseinfo'] = 'No course info';
$string['noenddatealert'] = 'There is no end date set in the course settings. A valid end date is now mandatory to ensure enrolment works properly';
$string['nolegacycodes'] = 'There are no active and valid codes defined for this course';
$string['notpossiblealert'] = 'Warning: Auto enrolment is currently disabled for this course. Check the start date, end date and visibility (this may be perfectly ok if your course hasn not started or has finished)';
$string['patience'] = 'Please be patient while this process completes. For course with large numbers of students, it may take some time';
$string['pluginname'] = 'UofG Enrolment database';
$string['pluginname_desc'] = 'You can use an external database (of nearly any kind) to control your enrolments. It is assumed your external database contains at least a field containing a course ID, and a field containing a user ID. These are compared against fields that you choose in the local course and user tables.';
$string['privacy:metadata:enrol_gudatabase_users'] = 'Contains a cache of userids linked to the UofG course coAdes on which they are enrolled. Includes the time at which this was last updated';
$string['privacy:metadata:enrol_gudatabase_users:code'] = 'The UofG Course Code for this subject';
$string['privacy:metadata:enrol_gudatabase_users:courseid'] = 'The ID of the course the user is being enrolled on';
$string['privacy:metadata:enrol_gudatabase_users:timeupdated'] = 'The time that the enrolment was last updated';
$string['privacy:metadata:enrol_gudatabase_users:userid'] = 'The ID of the user being enrolled on the course';
$string['processinginstance'] = 'Processing plugin instance \'{$a}\'';
$string['removewarning'] = '<b>DANGER:</b> Enabling the next two settings can cause you to irreversibly delete student data. Only enable if you know what you are doing!';
$string['savedisabled'] = 'Users will NOT be enrolled on this course at the moment. If you think this is wrong check that the course is visible and that the current date is between the course start and end dates.';
$string['savewarning'] = 'Saving this page will queue updates to all enrolments and groups for this course in the background. It will take several minutes for participant lists to change. Please be patient.';
$string['settingsclasslist'] = 'Class list table';
$string['settingscodes'] = 'Enable codes in course settings';
$string['settingscodes_help'] = 'When enabled also process any valid codes found in this course\'s shortname or idnumber fields. Only one enrolment method per course should have this set.';
$string['settingsheadercodes'] = 'Remote codes table';
$string['scheduledname'] = 'Synchronise guenrolments';
$string['starcode'] = 'Full year psuedo-code';
$string['status'] = 'Enable existing enrolments';
$string['status_desc'] = 'Enable gudatabase method in new courses.';
$string['status_help'] = 'If disabled all existing gudatabase enrolments are suspended and new users can not enrol.';
$string['sync'] = 'Processing enrolments';
$string['syncexport'] = 'Exporting enrolments to MyGlasgow';
$string['syncgroups'] = 'Adding users to groups';
$string['syncusers'] = 'Enrolling course users';
$string['timelimit'] = 'Cron time limit';
$string['timelimit_desc'] = 'Maximum time that cron process may run for';
$string['unenrol'] = 'Unenrol';
$string['unenrolguard'] = 'Unenrol guard period';
$string['unenrolguard_help'] = 'Users enrolled for longer than this guard time will not be unenrolled';
$string['unenrolselfconfirm'] = 'Are you sure you want to unenrol from this course?';
