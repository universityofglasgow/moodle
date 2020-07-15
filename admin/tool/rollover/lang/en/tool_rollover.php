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
 * @package    tool_rollover
 * @copyright  2019 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['appendtext'] = 'Course name append text';
$string['appendtext_desc'] = 'Archived courses will have this text appended to their full name';
$string['backupfilepath'] = 'Backup file path';
$string['backupfilepath_desc'] = 'Location to store backup files';
$string['buildhelp'] = 'Create (or erase) rollover table according to <a href="{$a}">settings</a>';
$string['categoryexclude'] = 'Categories to exclude';
$string['categoryexclude_desc'] = 'List category ids to exclude. Both these and all their child categories will not take part in the rollover';
$string['countbackup'] = 'Backup completed';
$string['countrestore'] = 'Restore completed';
$string['countwaiting'] = 'Waiting for backup';
$string['currentstatus'] = 'Current status';
$string['destinationcategory'] = 'Destination category ID';
$string['destinationcategory_desc'] = 'Category course archive will be written *to*. You should probably create this in preparation for each rollover';
$string['enable'] = 'Enable rollover';
$string['enable_desc'] = 'Rollover process will operate with the above settings. Enable will switch off when complete';
$string['enablerestore'] = 'Enable restore';
$string['enablerestore_desc'] = 'Enable restore of backups to archive area';
$string['erasedatabase'] = 'Erase database';
$string['keepbackups'] = 'Keep backups';
$string['keepbackups_desc'] = 'Do not delete backup files after restore (only if restore enabled).';
$string['rollover:config'] = 'Configure CoreHR web service in a course';
$string['pluginname'] = 'UofG rollover configuration';
$string['populatedatabase'] = 'Populate database';
$string['prependtext'] = 'Course name prepend text';
$string['prependtext_desc'] = 'Archived courses will have this text prepended to their full name';
$string['privacy:metadata'] = 'The rollover settings plugin does not store any personal data';
$string['rollovertask'] = 'Rollover task';
$string['session'] = 'Session year';
$string['session_desc'] = 'Session to be processed (e.g. 2018, 2019). Used to match selected courses with backups. Change manually before starting each years backups';
$string['shortprependtext'] = 'Short name prepend text';
$string['shortprependtext_desc'] = 'Archived courses will have this text prepended to their short name';
$string['sourcecategory'] = 'Source category ID';
$string['sourcecategory_desc'] = 'Category course archive will be copied *from*. This will typically be (and remain as) the tree of live courses';
$string['timelimit'] = 'Time limit (minutes)';
$string['timelimit_desc'] = 'Maximum time (in minutes) backup or restore Moosh process will run at one time';

