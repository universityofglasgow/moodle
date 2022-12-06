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
 * Language file.
 *
 * @package    theme_hillhead40
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Hillhead40';
$string['region-side-pre'] = 'Right';
$string['choosereadme'] = 'University of Glasgow Theme - 4.0 (Hillhead) by Greg Pedder';
$string['configtitle'] = 'Hillhead 4.0 settings';
$string['guidreport'] = 'GUID Report';
// Name of the first settings tab.
$string['generalsettings'] = 'Appearance';
$string['preset'] = 'Colour Scheme';
$string['preset_desc'] = 'This lets you change the colour scheme in order to distinguish development and testing sites.';
$string['loginbackgroundimage'] = 'Login page background image';
$string['loginbackgroundimage_desc'] = 'An image that will be stretched to fill the background of the login page.';
$string['notificationsettings'] = 'Notifications and Alerts';
$string['hillhead_student_course_alert'] = 'Student Course Alert';
$string['hillhead_student_course_alert_desc'] = 'If enabled, students will see a "can\'t see your course, speak to your lecturer" message. Should hopefully cut down on helpdesk calls.';
$string['hillhead_student_course_alert_on'] = 'Enabled';
$string['hillhead_student_course_alert_off'] = 'Disabled';
$string['hillhead_student_course_alert_text'] = 'Student Course Alert Text';
$string['hillhead_student_course_alert_text_desc'] = 'This is the text that will show in the "can\'t see your course" warning.';
$string['hillhead_old_browser_alerts'] = 'Old Browser Alerts Alerts';
$string['hillhead_old_browser_alerts_desc'] = 'Shows a notification if the user has an old browser that is no longer supported.';
$string['hillhead_old_browser_alerts_on'] = 'Enabled';
$string['hillhead_old_browser_alerts_off'] = 'Disabled';
$string['hillhead_notification_type'] = 'Systemwide Notification Type';
$string['hillhead_notification_type_desc'] = 'Use this to choose the type of notification banner that appears - red for errors, blue for information, yellow for warnings etc. Green alerts should only be used for "success" notifications, e.g. saving settings. Don\'t use these for regular notifications.';
$string['hillhead_notification_none'] = 'None - Don\'t display';
$string['hillhead_notification_danger'] = 'Red - Errors and problem acknowledgements';
$string['hillhead_notification_warning'] = 'Yellow - Planned downtime warnings';
$string['hillhead_notification_success'] = 'Green - Success notifications';
$string['hillhead_notification_info'] = 'Blue - Information and advisory';
$string['hillhead_notification'] = 'Systemwide Notification Text';
$string['hillhead_notification_desc'] = 'Use this to have a notification banner appear at the top of every page. Keep it short - one line works best.';
$string['advancedsettings'] = 'Advanced settings';
$string['rawscsspre'] = 'Raw initial SCSS';
$string['rawscsspre_desc'] = 'In this field you can provide initialising SCSS code, it will be injected before everything else. Most of the time you will use this setting to define variables.';
$string['rawscss'] = 'Raw SCSS';
$string['rawscss_desc'] = 'Use this field to provide SCSS or CSS code which will be injected at the end of the style sheet.';
$string['login_intro'] = 'Login Page Introduction';
$string['login_intro_desc'] = 'Anything you enter here will be shown on the Moodle login page. We recommend a one line &lt;h2&gt; tag followed by a couple of &lt;p&gt; tags.';
$string['cachedef_fontawesomeiconmapping'] = 'Hillhead 4.0 Icon Cache';
