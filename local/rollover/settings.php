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
 * @package    local_rollover
 * @copyright  2016 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_rollover', get_string('pluginname', 'local_rollover'));

    // Limit backup
    $settings->add(new admin_setting_configtext(
        'local_rollover/session',
        get_string('session', 'local_rollover'),
        get_string('session_desc', 'local_rollover'),
        2018, // in minutes (4 hours)
        PARAM_INT
    ));

    // List of category ids to exclude
    $settings->add(new admin_setting_configtextarea(
        'local_rollover/categoryexclude',
        get_string('categoryexclude', 'local_rollover'),
        get_string('categoryexclude_desc', 'local_rollover'),
        ''
    ));

    // Source category
    $settings->add(new admin_setting_configtext(
        'local_rollover/sourcecategory',
        get_string('sourcecategory', 'local_rollover'),
        get_string('sourcecategory_desc', 'local_rollover'),
        '',
        PARAM_INT
    ));

    // Destination category
    $settings->add(new admin_setting_configtext(
        'local_rollover/destinationcategory',
        get_string('destinationcategory', 'local_rollover'),
        get_string('destinationcategory_desc', 'local_rollover'),
        '',
        PARAM_INT
    ));

    // Append text
    $settings->add(new admin_setting_configtext(
        'local_rollover/appendtext',
        get_string('appendtext', 'local_rollover'),
        get_string('appendtext_desc', 'local_rollover'),
        ''
    ));

    // Prepend text
    $settings->add(new admin_setting_configtext(
        'local_rollover/prependtext',
        get_string('prependtext', 'local_rollover'),
        get_string('prependtext_desc', 'local_rollover'),
        ''
    ));

    // Short name text
    $settings->add(new admin_setting_configtext(
        'local_rollover/shortprependtext',
        get_string('shortprependtext', 'local_rollover'),
        get_string('shortprependtext_desc', 'local_rollover'),
        ''
    ));

    // Backup location
    $settings->add(new admin_setting_configtext(
        'local_rollover/backupfilepath',
        get_string('backupfilepath', 'local_rollover'),
        get_string('backupfilepath_desc', 'local_rollover'),
        ''
    ));

    // Keep all backups
    $settings->add(new admin_setting_configcheckbox(
        'local_rollover/keepbackups',
        get_string('keepbackups', 'local_rollover'),
        get_string('keepbackups_desc', 'local_rollover'),
        1
    ));

    // Enable restore
    $settings->add(new admin_setting_configcheckbox(
        'local_rollover/enablerestore',
        get_string('enablerestore', 'local_rollover'),
        get_string('enablerestore_desc', 'local_rollover'),
        0
    ));

    // Limit backup
    $settings->add(new admin_setting_configtext(
        'local_rollover/timelimit',
        get_string('timelimit', 'local_rollover'),
        get_string('timelimit_desc', 'local_rollover'),
        240, // in minutes (4 hours)
        PARAM_INT
    ));

    // Enable
    $settings->add(new admin_setting_configcheckbox(
        'local_rollover/enable',
        get_string('enable', 'local_rollover'),
        get_string('enable_desc', 'local_rollover'),
        0
    ));

    $ADMIN->add('localplugins', $settings);

}
