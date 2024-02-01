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
 * Automatic extension settings.
 *
 * @package    assignsubmission_automaticextension
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin = 'assignsubmission_automaticextension';

// Note: This is on by default.
$name = new lang_string('settings:default', 'assignsubmission_automaticextension');
$description = new lang_string('settings:default_help', 'assignsubmission_automaticextension');
$settings->add(new admin_setting_configcheckbox($plugin . '/default', $name, $description, 1));

$name = new lang_string('settings:conditions', 'assignsubmission_automaticextension');
$description = new lang_string('settings:conditions_help', 'assignsubmission_automaticextension');
$settings->add(new admin_setting_confightmleditor($plugin . '/conditions', $name, $description, '', PARAM_RAW));

$name = new lang_string('settings:maximumrequests', 'assignsubmission_automaticextension');
$description = new lang_string('settings:maximumrequests_help', 'assignsubmission_automaticextension');
$settings->add(new admin_setting_configtext($plugin . '/maximumrequests', $name, $description, 1, PARAM_INT));

$name = new lang_string('settings:extensionlength', 'assignsubmission_automaticextension');
$description = new lang_string('settings:extensionlength_help', 'assignsubmission_automaticextension');
$settings->add(new admin_setting_configduration($plugin . '/extensionlength', $name, $description, 86400));
