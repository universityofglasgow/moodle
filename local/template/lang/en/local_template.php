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
 * Language strings
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course template selector';

$string['addnewcourse'] = 'Add new course with template';
$string['usedefaultmoodlefunctions'] = 'Use default moodle course functions';

$string['addnewcoursehook'] = 'Add new course hook';
$string['addnewcoursehook_desc'] = 'Enable add new course redirection to course template selector';

$string['addnewcoursebutton'] = 'Add new course button';
$string['addnewcoursebutton_desc'] = 'Add addtional Add new course via template button alongside Add new course buttons (performed by theme)';

$string['addnewcoursecategoryactionbar'] = 'Add new course to category action bar';
$string['addnewcoursecategoryactionbar_desc'] = 'Add additional Add new course via template option to category page options dropdown (performed by theme)';

$string['addnewcoursecoursemanagement'] = 'Add new course to course management';
$string['addnewcoursecoursemanagement_desc'] = 'Add additional Add new course via template button to course management page (performed by theme)';

$string['categories'] = 'Course Categories';
$string['categories_desc'] = 'List of course categories that are defined as holding template courses';

$string['categories_desc'] = 'List of course categories that are defined as holding template courses';
$string['noaccess'] = 'No access to use the course template plugin';

$string['introduction'] = 'Introduction';
$string['introduction_desc'] = 'Introduction text shown to users during course creation process';
$string['introduction_default'] = 'Default <b>here</b>';

$string['templateadmin'] = 'Admin';
$string['templatecourse'] = 'Template Course';

$string['template'] = 'Template';
$string['templates'] = 'Templates';
$string['backupcontrollers'] = 'Backup Controllers';

$string['settings'] = 'Settings';
$string['dashboard'] = 'Dashboard';

$string['managetemplate'] = 'Your user has the <strong>Manage Template capability <i class="fa fa-user-secret"></i></strong>.';


$string['createnewtemplate'] = 'Create new course via template';
$string['templatecourse'] = 'Template Course';
$string['importcourse'] = 'Import Course';
$string['summary'] = 'Course Summary';


$string['templateintro'] = 'Template Intro';

$string['copybackup'] = 'Copy backup';
$string['copyrestore'] = 'Copy Restore';
$string['importbackup'] = 'Import Backup';
$string['importrestore'] = 'Import Restore';

$string['addtemplate'] = 'Add template';
$string['timemodified'] = 'Time Modified';
$string['fullname'] = 'Full Name';
$string['username'] = 'Username';
$string['edit'] = 'Edit';

$string['edittemplate'] = 'Edit template';

$string['gudbenrolment'] = 'Automated Database Enrolment';

$string['gudbaddenrolment'] = 'Add enrolment method';
$string['gudbaddenrolment_help'] = 'Whether to add an automatic enrolment method';

$string['addnewtemplate'] = 'Add new template';

$string['addnewbackupcontroller'] = 'Add New Backup Controller';
$string['editbackupcontroller'] = 'Edit Backup Controller';

$string['admin'] = 'Admin';
$string['coursedetails'] = 'Course Details';
$string['createdcourse'] = 'Created course';
$string['description'] = 'Description';
$string['enrolment'] = 'Enrolment';
$string['import'] = 'Import';
$string['controllers'] = 'Controllers';

$string['category'] = 'Category';
$string['missingcategory'] = 'Missing Category';

$string['missingcreatedcourse'] = 'No Created Course';
$string['missingtemplatecourse'] = 'Missing Template Course';
$string['missingimportcourse'] = 'No Import Course';

$string['notemplatesfound'] = 'No templates found.';

$string['backupcontroller'] = 'Backup Controller';
$string['nobackupcontrollersdefined'] = 'No backup controllers';
$string['addbackupcontroller'] = 'Add Backup Controller';
$string['createbackupcontroller'] = 'Create Backup Controller';

$string['backupcontrollerstatuscreated'] = 'Created';
$string['backupcontrollerstatusrequireconv'] = 'Requires Conversion';
$string['backupcontrollerstatusplanned'] = 'Planned';
$string['backupcontrollerstatusconfigured'] = 'Configured';
$string['backupcontrollerstatussettingui'] = 'Setting User Interface';
$string['backupcontrollerstatusneedprecheck'] = 'Needs Precheck';
$string['backupcontrollerstatusawaiting'] = 'Awating';
$string['backupcontrollerstatusexecuting'] = 'Executing';
$string['backupcontrollerstatusfinishederr'] = 'Finished with error';
$string['backupcontrollerstatusfinishedok'] = 'Finished OK'; // backupfinished

$string['backupcontrolleroperationbackup'] = 'Backup';
$string['backupcontrolleroperationrestore'] = 'Restore';

$string['backupcontrollertypeunknown'] = 'Unknown';
$string['backupcontrollertypeactivity'] = 'Activity';
$string['backupcontrollertypescetion'] = 'Section';
$string['backupcontrollertypecourse'] = 'Course';

$string['backupcontrollerpurposegeneral'] = 'General'; // backupmode10
$string['backupcontrollerpurposeimport'] = 'Import'; // backupmode20
$string['backupcontrollerpurposehub'] = 'Hub'; // backupmode30
$string['backupcontrollerpurposesamesite'] = 'Same Site'; // backupmode40
$string['backupcontrollerpurposeautomated'] = 'Automated'; // backupmode50
$string['backupcontrollerpurposeconverted'] = 'Converted'; // backupmode60
$string['backupcontrollerpurposeasync'] = 'Asynchronous'; // backupmode70
$string['backupcontrollerpurposecopy'] = 'Copy';

$string['execution'] = 'Execution';
$string['backupcontrollerexecutionimmediate'] = 'Immediate';
$string['backupcontrollerexecutiondelayed'] = 'Delayed';

$string['executiontime'] = 'Execution Time';

$string['backupid'] = 'Backup ID';
$string['operation'] = 'Operation';
$string['type'] = 'Type';
$string['status'] = 'Status';
$string['purpose'] = 'Purpose';
$string['itemid'] = 'Item ID';
$string['format'] = 'Format';
$string['interactive'] = 'Interactive';
$string['progress'] = 'Progress';
$string['checksum'] = 'Interactive';
$string['controller'] = 'Controller';


$string['createandredirect'] = 'Create and go to course';
$string['createcourse'] = 'Create course';
$string['savetemplate'] = 'Save draft without creating course';

$string['addnewcourseviatemplate'] = 'Add a new course via template';

$string['log'] = 'Log';

