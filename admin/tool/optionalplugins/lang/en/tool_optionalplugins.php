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
 * Language file
 *
 * English descriptions for commonly used strings in the plugin.
 *
 * @package tool_optionalplugins
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Manage optional plugins';
$string['pagetitle'] = 'Manage optional plugins';
$string['exportfiles'] = 'Export optional plugins';
$string['exportpluginsstring'] = 'Export plugin list';
$string['importfile'] = 'Import optional plugins';
$string['importfile_error'] = 'There was a problem reading the file. Please try again.<p>If you continue to see this message, contact your Moodle administrator: {$a}</p>';
$string['importfile_jsonerror'] = 'There was a problem reading the file. The error was: {$a}</p>';
$string['pluginstoinstall'] = 'Plugins to be installed';
$string['pluginsinstalled'] = 'Plugins installed';
$string['pluginstoinstall_extra'] = 'This <strong>{$a}</strong> installed';
$string['pluginsalreadyinstalled'] = 'Plugins already installed';
$string['pluginstoskip'] = 'Plugins unable to be installed';
$string['pluginsnotinstalled'] = 'Plugins not installed';
$string['installationdetails'] = 'Installation details';
$string['installationchoice_y'] = 'Check to install this version';
$string['installationchoice_n'] = 'Uncheck to ignore this version';
$string['conditiontext'] = 'will be';
$string['plugindirectory_text'] = 'Nothing returned from the plugin directory.';
$string['plugindependency_text'] = 'Installed as part of: ';
$string['pluginversionmismatch_text'] = 'Unable to install for this version of Moodle.';
$string['errormsg'] = 'The filename needs to be of the format "optionalplugins-moodle-[version no].json"';
$string['reportname'] = 'Optional Plugin Installations';
$string['reportpagetitle'] = 'Optional Plugins Report';
$string['reportintro'] = '<p>This report allows you to see when additional plugins were installed. It gives a summary of plugins already installed, plugins that were successfully installed, and plugins that couldn\'t be installed. Information about who carried out the installation and when is included also.</p>';
$string['reportdate'] = 'Date';
$string['renderer_columnname'] = 'Repository name';
$string['notes_string'] = 'notes';
$string['available_string'] = ' was available.';
$string['user_string'] = 'User';
$string['date_string'] = 'Date';
$string['additional_string'] = 'Additional';
$string['action_btn_string'] = 'Upload and preview';
$string['install_btn_string'] = 'Install plugins';
$string['display_btn_string'] = 'Display';

// Capabilities.
$string['optionalplugins:importplugins'] = 'Import from a JSON file a series of optional plugins';
