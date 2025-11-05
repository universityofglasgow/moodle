<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     tool_gudelete
 * @category    string
 * @copyright   2024 Michael Clark
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['category'] = 'Category';
$string['pluginname'] = 'GuDelete';
$string['deleteallcourses'] = 'Delete all courses';
$string['deleteconfirm'] = 'This page lets you delete all the courses in the category <strong>{$a}</strong> and its subcategories. This action cannot be undone.';
$string['gudelete:deletecourses'] = 'Delete all courses in a category.';
$string['deletequeued'] = 'An adhoc task has been queued to delete all the courses in the category <strong>{$a}</strong> and subcategories. It will run the next time cron executes.';
$string['disablerecyclebin'] = 'Disable recycle bin';
$string['privacy:metadata'] = 'The Delete courses plugin does not store any personal data.';
$string['recursive'] = 'Recurse through subcategories?';
$string['archive'] = 'Start Archiving';
$string['confirm_archiving'] = 'The following courses will be archived:<br />
<br />
{$a->archived}<br />
<br />
The following courses will be deleted:<br />
<br />
{$a->deleted}';
$string['confirm_header'] = 'Confirm Archiving';
$string['course_archiving_task'] = 'Archiving courses';
$string['days'] = 'Number of days to archive';
$string['include_subcategories'] = 'Include all subcategories';
$string['last_activity'] = 'Last course activity';
$string['notice'] = 'The following courses were archived:<br />
<br />
{$a->archived}<br />
<br />
The following courses where deleted:<br />
<br />
{$a->deleted}';
$string['nothing_to_archive'] = 'No courses to archive or to delete';
$string['remove_success'] = ' - Successfully removed';
$string['remove_error'] = ' - Errors while removing';
$string['run_cron'] = 'Activate cron task';
$string['sourcecat'] = 'Categories to archive';
$string['targetcat'] = 'Archive category';
$string['targettimestamp'] = 'Timestamp to check';
$string['title'] = 'Course Archiving';
$string['timemodified'] = 'Last Modified';
$string['id'] = 'ID';