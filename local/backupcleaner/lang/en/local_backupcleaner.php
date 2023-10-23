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
 * Backup cleaner
 *
 * @package    local_backupcleaner
 * @copyright  2012 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Backup Cleaner';
$string['min_backup_age'] = 'Delete backups older than';
$string['min_backup_age_desc'] = 'Any course backups older than this will be deleted by the scheduled task.';
$string['age_30_days'] = '1 Month';
$string['age_90_days'] = '3 Months';
$string['age_180_days'] = '6 Months';
$string['age_365_days'] = '1 Year';
$string['age_730_days'] = '2 Years';
$string['age_1095_days'] = '3 Years';
$string['age_1825_days'] = '5 Years';
$string['age_3650_days'] = '10 Years';
$string['deletebackups'] = 'Delete Old Backups';
$string['max_delete'] = 'Maximum number to delete';
$string['max_delete_desc'] = 'This is the maximum number of backups that the scheduled task will delete each time it runs. It is set to a low value by default to stop the plugin deleting 100,000 backups during its first run and trashing your storage layer\'s performance.';
$string['max_delete_1'] = '1 File';
$string['max_delete_5'] = '5 Files';
$string['max_delete_10'] = '10 Files';
$string['max_delete_25'] = '25 Files';
$string['max_delete_50'] = '50 Files';
$string['max_delete_100'] = '100 Files';
$string['max_delete_250'] = '250 Files';
$string['max_delete_500'] = '500 Files';
$string['max_delete_1000'] = '1000 Files';