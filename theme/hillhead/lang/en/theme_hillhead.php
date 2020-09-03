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
 * @package   theme_iomadboost
 * @copyright 2017 Howard Miller
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['choosereadme'] = 'University of Glasgow Theme - 1.0 (Hillhead) by Alex Walker';
$string['iomaddashboard'] = 'Hillhead dashboard';
$string['pluginname'] = 'Hillhead';
$string['region-side-pre'] = 'Right';                                                                              
$string['advancedsettings'] = 'Advanced settings';                                                                                                                                                                                        
$string['brandcolor'] = 'Brand colour';                                                                                                                                                                                       
$string['brandcolor_desc'] = 'The accent colour.';                                                                                                                                                                                        
$string['configtitle'] = 'Hillhead settings';                                                                                                                                                                                         
$string['generalsettings'] = 'General settings';                                                                                                                                                                                                                                                                                                
$string['presetfiles'] = 'Additional theme preset files';                                                                                                                                                                                
$string['presetfiles_desc'] = 'Preset files can be used to dramatically alter the appearance of the theme. See <a href=https://docs.moodle.org/dev/Boost_Presets>Boost presets</a> for information on creating and sharing your own preset files, and see the <a href=http://moodle.net/boost>Presets repository</a> for presets that others have shared.';                                                                                                             
$string['preset'] = 'Theme preset';                                                                                                                                                                                                              
$string['preset_desc'] = 'Pick a preset to broadly change the look of the theme.';                                                                                                                                                                
$string['rawscss'] = 'Raw SCSS';                                                                                                                                                                                                        
$string['rawscss_desc'] = 'Use this field to provide SCSS or CSS code which will be injected at the end of the style sheet.';                                                                                                   
$string['rawscsspre'] = 'Raw initial SCSS';                                                                                                                                                                                      
$string['rawscsspre_desc'] = 'In this field you can provide initialising SCSS code, it will be injected before everything else. Most of the time you will use this setting to define variables.';
$string['hillhead_notification'] = 'Systemwide Notification Text';
$string['hillhead_notification_desc'] = 'Use this to have a notification banner appear at the top of every page. Keep it short - one line works best.';
$string['hillhead_notification_type'] = 'Systemwide Notification Type';
$string['hillhead_notification_type_desc'] = 'Use this to choose the type of notification banner that appears - red for errors, blue for information, yellow for warnings etc. Green alerts should only be used for "success" notifications, e.g. saving settings. Don\'t use these for regular notifications.';
$string['hillhead_notification_none'] = 'None - Don\'t display';
$string['hillhead_notification_danger'] = 'Red - Errors and problem acknowledgements';
$string['hillhead_notification_warning'] = 'Yellow - Planned downtime warnings';
$string['hillhead_notification_success'] = 'Green - Success notifications';
$string['hillhead_notification_info'] = 'Blue - Information and advisory';                                                                                   
$string['login_intro'] = 'Login Page Introduction';
$string['login_intro_desc'] = 'Anything you enter here will be shown on the Moodle login page. We recommend a one line &lt;h2&gt; tag followed by a couple of &lt;p&gt; tags.';
$string['hillhead_smart_alerts'] = 'Smart Alerts';
$string['hillhead_smart_alerts_desc'] = 'Smart Alerts let lecturers know about potential problems with their courses. For example, they will remind a lecturer if their course is hidden from students.';
$string['hillhead_smart_alerts_on'] = 'Enabled';
$string['hillhead_smart_alerts_off'] = 'Disabled';
$string['hillhead_old_browser_alerts'] = 'Old Browser Alerts Alerts';
$string['hillhead_old_browser_alerts_desc'] = 'Shows a notification if the user has an old browser that is no longer supported.';
$string['hillhead_old_browser_alerts_on'] = 'Enabled';
$string['hillhead_old_browser_alerts_off'] = 'Disabled';
$string['purgenotificationstask'] = 'Purge Dismissed Notification Preferences';
$string['notificationsettings'] = 'Notifications and Alerts';
$string['helpsettings'] = 'Help Settings';
$string['helplink'] = 'Help Link';
$string['sidebar'] = 'Sidebar';
$string['hillhead_globalpinned'] = 'Important Courses';
$string['hillhead_globalpinned_desc'] = 'This is a JSON array of courses to show on everybody\'s sidebar. [{idnumber, title}]';
$string['hillhead_student_course_alert'] = 'Student Course Alerts';
$string['hillhead_student_course_alert_desc'] = 'If enabled, students will see a "can\'t see your course, speak to your lecturer" message. Should hopefully cut down on helpdesk calls.';
$string['hillhead_student_course_alert_on'] = 'Enabled';
$string['hillhead_student_course_alert_off'] = 'Disabled';
$string['helplink_desc'] = 'If this setting isn\'t blank, there will be a "Help" link in the top right corner of the navigation bar. If you have your own help resource, you can enter the URL here. Otherwise, you could include a link to the Moodle documentation.';
$string['privacy:metadata:font'] = 'The font (default, sans serif, comic etc) a user has chosen in Hillhead\'s Accessibility Tools.';
$string['privacy:metadata:size'] = 'The font size (default, large, extra large) a user has chosen in Hillhead\'s Accessibility Tools.';
$string['privacy:metadata:contrast'] = 'The colour scheme a user has chosen in Hillhead\'s Accessibility Tools (default, high-contrast yellow-on-black etc).';
$string['privacy:metadata:bold'] = 'Whether a user has enabled the "make everything bold" option in Hillhead\'s Accessiblity Tools.';
$string['privacy:metadata:spacing'] = 'Whether a user has enabled the "increase spacing between lines of text" option in Hillhead\'s Accessibility Tools.';
$string['privacy:metadata:readtome'] = 'Whether a user has enabled the "Read-To-Me" option in Hillhead\'s Accessibility Tools.';
$string['privacy:metadata:readalert'] = 'Whether a user has enabled the "Announce alerts" option in Hillhead\'s Accessibility Tools.';
$string['privacy:metadata:stripstyles'] = 'Whether a user has enabled the "strip styles from content" option in Hillhead\'s Accessibility Tools.';
$string['privacy:metadata:accessibility'] = 'Remembers whether a user has the Accessibility Tools panel displayed or not.';