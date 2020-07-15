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

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_rollover', get_string('pluginname', 'tool_rollover'));
    $ADMIN->add('tools', $settings);

    // Link in Admin->Courses
    $ADMIN->add('courses', new admin_externalpage('toolrollover',
    get_string('pluginname', 'tool_rollover'), "$CFG->wwwroot/$CFG->admin/tool/rollover/index.php"));

    // Limit backup
    $settings->add(new admin_setting_configtext(
        'tool_rollover/session',
        get_string('session', 'tool_rollover'),
        get_string('session_desc', 'tool_rollover'),
        2019, // in minutes (4 hours)
        PARAM_INT
    ));

    // List of category ids to exclude
    $settings->add(new admin_setting_configtextarea(
        'tool_rollover/categoryexclude',
        get_string('categoryexclude', 'tool_rollover'),
        get_string('categoryexclude_desc', 'tool_rollover'),
        ''
    ));

    // Source category
    $settings->add(new admin_setting_configtext(
        'tool_rollover/sourcecategory',
        get_string('sourcecategory', 'tool_rollover'),
        get_string('sourcecategory_desc', 'tool_rollover'),
        '',
        PARAM_INT
    ));

    // Destination category
    $settings->add(new admin_setting_configtext(
        'tool_rollover/destinationcategory',
        get_string('destinationcategory', 'tool_rollover'),
        get_string('destinationcategory_desc', 'tool_rollover'),
        '',
        PARAM_INT
    ));

    // Append text
    $settings->add(new admin_setting_configtext(
        'tool_rollover/appendtext',
        get_string('appendtext', 'tool_rollover'),
        get_string('appendtext_desc', 'tool_rollover'),
        ''
    ));

    // Prepend text
    $settings->add(new admin_setting_configtext(
        'tool_rollover/prependtext',
        get_string('prependtext', 'tool_rollover'),
        get_string('prependtext_desc', 'tool_rollover'),
        ''
    ));

    // Short name text
    $settings->add(new admin_setting_configtext(
        'tool_rollover/shortprependtext',
        get_string('shortprependtext', 'tool_rollover'),
        get_string('shortprependtext_desc', 'tool_rollover'),
        ''
    ));

    // Backup location
    $settings->add(new admin_setting_configtext(
        'tool_rollover/backupfilepath',
        get_string('backupfilepath', 'tool_rollover'),
        get_string('backupfilepath_desc', 'tool_rollover'),
        ''
    ));

    // Keep all backups
    $settings->add(new admin_setting_configcheckbox(
        'tool_rollover/keepbackups',
        get_string('keepbackups', 'tool_rollover'),
        get_string('keepbackups_desc', 'tool_rollover'),
        1
    ));

    // Enable restore
    $settings->add(new admin_setting_configcheckbox(
        'tool_rollover/enablerestore',
        get_string('enablerestore', 'tool_rollover'),
        get_string('enablerestore_desc', 'tool_rollover'),
        0
    ));

    // Limit backup
    $settings->add(new admin_setting_configtext(
        'tool_rollover/timelimit',
        get_string('timelimit', 'tool_rollover'),
        get_string('timelimit_desc', 'tool_rollover'),
        240, // in minutes (4 hours)
        PARAM_INT
    ));

    // Enable
    $settings->add(new admin_setting_configcheckbox(
        'tool_rollover/enable',
        get_string('enable', 'tool_rollover'),
        get_string('enable_desc', 'tool_rollover'),
        0
    ));

}
