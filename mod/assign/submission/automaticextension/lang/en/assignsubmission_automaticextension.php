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
 * @package    assignsubmission_automaticextension
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['accept'] = 'Accept automatic extension';
$string['automaticextension:requestextension'] = 'Request an automatic extension';
$string['cancel'] = 'Cancel';
$string['enabled'] = 'Automatic extension';
$string['enabled_help'] = 'If enabled, students can request an automatic extension.';
$string['event_automatic_extension_applied'] = 'Automatic extension applied';
$string['event_automatic_extension_applied_desc'] = 'The user with \'{$a->userid}\' has applied an automatic extension for course module id \'{$a->contextinstanceid}\', extension date is {$a->extensionduedate}.';
$string['extensionrequest'] = 'Extension Request';
$string['pluginname'] = 'Automatic extension';
$string['privacy:metadata'] = 'The automatic extension submission plugin does not store any personal data.';
$string['requesterror'] = 'There was an issue applying your automatic extension, please contact your instructor.';
$string['requestextension'] = 'Request extension';
$string['requestsuccess'] = 'Your automatic extension has been applied. Your new due date is {$a}.';
$string['settings:default'] = 'Enabled by default';
$string['settings:default_help'] = 'If set, automatic extension will be enabled by default for all new and existing assignments (created before this plugin was installed), giving students the ability to request an automatic extension.';
$string['settings:conditions'] = 'Condition details';
$string['settings:conditions_help'] = 'The extension condition details to be shown to students when they request an extension';
$string['settings:extensionlength'] = 'Extension length';
$string['settings:extensionlength_help'] = 'The extension period that will be applied to the user when a request is made. Subsqent requests (if allowed) will increase the extension period by this ammount.';
$string['settings:maximumrequests'] = 'Maximum requests';
$string['settings:maximumrequests_help'] = 'The maximum requests that each user can make for each assignment';
$string['unabletorequest'] = 'An automatic extension cannot be requested right now. Please contact your instructor for a manual request.';
