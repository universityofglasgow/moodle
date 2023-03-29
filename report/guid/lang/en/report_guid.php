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
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['accessed'] = 'Accessed {$a}';
$string['addgroups'] = 'Add groups';
$string['addgroups_help'] = 'Additional columns will be used to add user to specified groups. Groups are created if they do not exist. If this is set to \'No\' additional columns are ignored';
$string['accountcreated'] = 'Account created for {$a}';
$string['accountexists'] = 'Account not created, {$a} already exists';
$string['allowmultiple'] = 'Allow multiple enrolment methods';
$string['allowmultiple_help'] = 'If \'Yes\' user can be enrolled with more than one enrolment method. If \'No\' they are skipped if already enrolled. If in doubt, leave at \'No\'';
$string['attempt'] = 'Attempt';
$string['categoryupload'] = 'Upload users to category';
$string['categoryuploadinstructions'] = 'Upload your CSV file. First column is either GUID, ID number (staff number or matric number) or email as selected below.
    Users will be created and added to the Course Category with the selected role.
    <b>NOTE:</b> First line is for headers and is always ignored.';
$string['changeuserdesc'] = 'Change username for \'{$a}\'. Take care - mistakes will stop them logging in';
$string['changeusername'] = '<i class="fa fa-wrench"></i> Update';
$string['corehrcompletion'] = 'CoreHR Completion';
$string['corehrresults'] = 'CoreHR extract';
$string['counterrors'] = '{$a} lines caused an error';
$string['countexistingaccounts'] = '{$a} accounts already existed';
$string['countnewaccounts'] = '{$a} new accounts created';
$string['countunenrol'] = '{$a} users unenrolled';
$string['coursename'] = 'Course name';
$string['courseupload'] = 'Upload users and groups to course';
$string['courseuploadinstructions'] = 'Upload your CSV file. First column is either GUID, ID number (= matric number for students) or email as selected below. 
    Remaining columns (if present) contain the names of groups those students should be added to. 
    Users will be created, enrolled on the course and added to groups as required<br /><br />
    <b>NOTE:</b> First line is for headers and is always ignored.';
$string['create'] = 'Create a Moodle profile for {$a}';
$string['createbutton'] = 'Create';
$string['csverror'] = 'There is an error in the CSV file';
$string['csvfile'] = 'CSV File';
$string['currentusername'] = 'Current username';
$string['delete'] = '<i class="fa fa-trash"></i> Delete';
$string['deleted'] = 'User \'{$a}\' has been deleted.';
$string['downloadcsv'] = 'Download CSV';
$string['duplicateusers'] = 'This username is already in use.';
$string['email'] = 'Email address';
$string['emptycsv'] = 'CSV file is empty';
$string['ended'] = 'ENDED';
$string['enrol'] = 'Enrol';
$string['enroldownloadheader'] = 'Download enrolments & groups';
$string['enroldownloadinstructions'] = 'Download all enrolments and groups to CSV.';
$string['enrolled'] = 'enrolled';
$string['enrolledcourses'] = 'Enrolled';
$string['enrolments'] = 'Enrolments for {$a}';
$string['enrolmentsonsite'] = 'Enrolments on {$a} Moodle';
$string['externalmail'] = 'Emails in italics are non UofG addresses';
$string['filtererror'] = 'Error building filter. Please refine your search and try again';
$string['firstcolumn'] = 'First column data';
$string['firstcolumn_help'] = 'Specify if the first column of your spreadsheet contains the GUID/username, idnumber (matric number) or email. Note: try not to use email - it may be very slow.';
$string['firstname'] = 'First name';
$string['gcat'] = 'GCAT';
$string['groupadded'] = 'Group add {$a}';
$string['groupnotadded'] = 'Group add failed {$a}';
$string['guid'] = 'GUID Search';
$string['guid:courseupload'] = 'Access GUID course upload form';
$string['guid:enroldownload'] = 'Download enrolments and groups to csv';
$string['guid:view'] = 'View GUID form';
$string['guidform'] = 'GUID';
$string['guidnomatch'] = 'GUID does not match in data (name changed?)';
$string['guidusername'] = 'GUID/Username';
$string['heading'] = 'GUID Search';
$string['headingcategoryupload'] = 'Upload users to a Course Category';
$string['headingcourseupload'] = 'Upload users & groups to course';
$string['headingsync'] = 'GUID - Sync user';
$string['headingupdate'] = 'GUID - Update username';
$string['hidden'] = 'Hidden';
$string['hrcode'] = 'HR code';
$string['idnumber'] = 'ID/Matric number';
$string['instructions'] = 'Enter whatever you know about the user. Use a * for wildcards (e.g. Mc*). Data Vault will be searched for matches.';
$string['lastname'] = 'Last name';
$string['ldapnotconfigured'] = 'LDAP is not configured';
$string['ldapnotloaded'] = 'LDAP drivers are not loaded';
$string['ldapresults'] = 'LDAP results';
$string['ldapsearcherror'] = 'LDAP search failed (perhaps try with debugging on)';
$string['missingparams'] = 'A required parameter is missing (id or contextid)';
$string['module'] = 'Module';
$string['more'] = '<i class="fa fa-info-circle"></i> More...';
$string['moreresults'] = 'There are more results (not shown). Please give more specific search criteria';
$string['mycampus'] = 'MyCampus enrolments';
$string['multipleresults'] = 'Error - unexpected multiple results';
$string['newusername'] = 'New username';
$string['nocorehrcompletion'] = 'No CoreHR completion data';
$string['noemail'] = '(Cannot create Moodle account - no email)';
$string['noenrolments'] = 'No Moodle enrolment data found for this user';
$string['nogudatabase'] = 'gudatabase enrolment plugin is not configured (needed for MyCampus results)';
$string['nolocalcourses'] = 'No courses for this code';
$string['noguenrol'] = 'GUSYNC local plugin is not configured (needed for enrolment results)';
$string['nomoodleprofile'] = 'First create a Moodle profile to show this data (or CoreHR data not yet read for this user)';
$string['nomycampus'] = 'No MyCampus data for this user';
$string['noresults'] = 'No results for this search';
$string['notstarted'] = 'NOT STARTED';
$string['notiifiles'] = 'No Turnitin submissions';
$string['nouser'] = 'Error - unable to find the user in LDAP';
$string['nouserresults'] = 'No Moodle profiles found';
$string['numbercsvlines'] = 'Number of lines in CSV file = {$a}';
$string['numberofresults'] = 'Number of results = {$a}';
$string['pluginname'] = 'GUID search';
$string['privacy:metadata'] = 'The GUID report does not store any personal data';
$string['reset'] = 'Reset form';
$string['resultfor'] = 'User record for user {$a}';
$string['retrycount'] = 'Retry count';
$string['roletoassign'] = 'Assign role';
$string['roletoassign_help'] = 'New users will be enrolled with this role. Existing users are ignored';
$string['search'] = 'Search';
$string['searcherror'] = 'Error returned by search (possibly too many results). Please refine your search and try again ({$a})';
$string['status'] = 'Status';
$string['submitfile'] = 'Upload CSV file';
$string['syncuser'] = '<i class="fa fa-refresh"></i> Sync';
$string['tiierror'] = 'Error';
$string['tiieulaaccepted'] = 'Turnitin EULA has been accepted';
$string['tiieulanotaccepted'] = 'Turnitin EULA has <b>NOT</b> been accepted';
$string['tiiid'] = 'Turnitin ID';
$string['tiiresend'] = 'Re-send';
$string['tiiscore'] = 'Score';
$string['tiistatus'] = 'Status';
$string['tiitime'] = 'Last update';
$string['timesent'] = 'Time sent';
$string['toomanyldap'] = 'Too many ldap results. Please try a more specific search';
$string['toomanyuser'] = 'Too many user profile results. Please try a more specific search';
$string['turnitin'] = 'Turnitin submissions';
$string['unenrol'] = 'Unenrol';
$string['unenrolled'] = 'Unenrolled';
$string['unenrolwarn'] = 'WARNING: selecting unenrol below may cause data loss';
$string['updatesuccess'] = 'Profile of \'{$a}\' has been updated';
$string['uploadaction'] = 'Enrol or unenrol';
$string['uploadaction_help'] = 'Normally this form is used to enrol/add users. If you select unenrol they will be removed instead. All other options are ignored. WARNING: removing users may result in irreversible data loss.';
$string['uploadfile'] = 'CSV file';
$string['uploadheader'] = 'Upload csv file';
$string['uploadinstructions'] = 'Upload a csv file. First column must contain the GUID of the users. Subsequent columns (if present) are completely ignored. The first line (headings) is also ignored';
$string['uploadguid'] = '<i class="fa fa-plus-circle"></i> Create users from uploaded CSV file';
$string['usercreated'] = 'User has been created ({$a})';
$string['userenrolled'] = 'User enrolled';
$string['userexists'] = 'User profile exists';
$string['usernotenrolled'] = 'User enrol failed';
$string['usernotfound'] = 'User not found';
$string['userprofilecreated'] = 'User profile created';
$string['userresults'] = 'Existing Moodle user profiles';

