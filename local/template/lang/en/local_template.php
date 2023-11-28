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

$string['pluginname'] = 'Moodle Course Template Wizard';

$string['addnewcourse'] = 'Add new course with template';
$string['usedefaultmoodlefunctions'] = 'Create Course Manually';

$string['addnewcoursehook'] = 'Add new course hook';
$string['addnewcoursehook_desc'] = 'Enable add new course redirection to Moodle Course Template Wizard';

$string['addnewcoursenavigation'] = 'Add new course to category navigation';
$string['addnewcoursenavigation_desc'] = 'Add additional Moodle Course Template Wizard option to category navigation menu';

$string['addnewcoursecategoryactionbar'] = 'Add new course to category action bar';
$string['addnewcoursecategoryactionbar_desc'] = 'Add additional Add new course via template option to category page options dropdown (performed by theme)';

$string['addnewcoursecoursemanagement'] = 'Add new course to course management';
$string['addnewcoursecoursemanagement_desc'] = 'Add additional Add new course via template button to course management page (performed by theme)';

$string['syncrole'] = 'Synchronisation role';
$string['syncrole_desc'] = 'If selected, the role here is applied to template course categories for users with course creation capability in sibling and child course categories';

$string['categories'] = 'Course Categories';
$string['categories_desc'] = 'List of course categories that are defined as holding template courses';

$string['categories_desc'] = 'List of course categories that are defined as holding template courses';
$string['noaccess'] = 'No access to use the course template plugin';

$string['introduction'] = 'Introduction';
$string['introduction_desc'] = 'Introduction text shown to users during course creation process';
$string['introduction_default'] = 'Default <b>here</b>';

$string['templateadmin'] = 'Admin';
$string['templatecourse'] = 'Template Course';
$string['templatecourse_help'] = 'The course template to use as a basis for the newly created course.';

$string['template'] = 'Template';
$string['templates'] = 'Templates';
$string['backupcontrollers'] = 'Backup Controllers';

$string['settings'] = 'Settings';
$string['dashboard'] = 'Dashboard';

$string['managetemplate'] = 'Your user has the <strong>Manage Template capability <i class="fa fa-user-secret"></i></strong>.';

$string['template:usetemplate'] = 'Use Template';
$string['template:managetemplate'] = 'Manage Template';

$string['createnewtemplate'] = 'Create new course via template';
$string['importcourse'] = 'Import Course';

$string['importcourse_desc'] = 'Optionally select a course below to reuse/import activities from another course.';

$string['summary'] = 'Course Summary';


$string['templateintro'] = 'Template Intro';

$string['copybackup'] = 'Copy backup';
$string['copyrestore'] = 'Copy Restore';
$string['importbackup'] = 'Import Backup';
$string['importrestore'] = 'Import Restore';

$string['addtemplate'] = 'Reset Moodle Course Template Wizard';
$string['timemodified'] = 'Time Modified';
$string['fullname'] = 'Full Name';
$string['username'] = 'Username';
$string['edit'] = 'Edit';

$string['shortname'] = 'Short Name';

$string['edittemplate'] = 'Edit template';

$string['gudbenrolment'] = 'Automated Database Enrolment';

$string['gudbaddenrolment'] = 'Add enrolment method';
$string['gudbaddenrolment_help'] = 'Whether to add an automatic enrolment method';

$string['addnewtemplate'] = 'Add new template';

$string['addnewbackupcontroller'] = 'Add New Backup Controller';
$string['editbackupcontroller'] = 'Edit Backup Controller';

$string['admin'] = 'Admin';

$string['selecttemplate'] = 'Select Template';
$string['coursedetails'] = 'Course Details';
$string['createdcourse'] = 'Created course';
$string['description'] = 'Description';
$string['enrolment'] = 'Enrolment';
$string['import'] = 'Import';
$string['process'] = 'Process';



$string['controllers'] = 'Controllers';

$string['category'] = 'Category';
$string['missingcategory'] = 'Missing Category';

$string['missingcreatedcourse'] = 'No Created Course for this record.';
$string['missingtemplatecourse'] = 'Missing Template Course';
$string['missingtemplate'] = 'Missing Course Template';
$string['missingtemplatename'] = 'Missing Course Template Name';
$string['missingimportcourse'] = 'No Import Course Selected.';

$string['notemplatesfound'] = 'No templates found.';

$string['backupcontroller'] = 'Backup Controller';
$string['nobackupcontrollersdefined'] = 'No backup controllers';
$string['addbackupcontroller'] = 'Add Backup Controller';
$string['createbackupcontroller'] = 'Create Backup Controller';

$string['backupcontrollercollection'] = 'Backup controller collection';
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

$string['externallink'] = 'New window';

$string['createandredirect'] = 'Create and go to course';
$string['createcourse'] = 'Create course';
$string['savetemplate'] = 'Save draft without creating course';

$string['addnewcourseviatemplate'] = 'Add a new course via template';

$string['log'] = 'Log';

$string['notemplatedefined'] = 'No log items';

$string['tasksyncroles'] = 'Synchronise roles';

$string['invisiblecategory'] = 'Invisible Category';

$string['availableviews'] = 'Available Views';
$string['availableviews_desc'] = 'Sets the views available to uses of this tool via a select option. If only one option is selected, the view is enforced and the selector is removed.';
$string['availableviews_slider'] = 'Default';
$string['availableviews_staticdisplay'] = 'Static';
$string['availableviews_highcompatabilitymode'] = 'Basic';
