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
 * Brief Description
 *
 * More indepth description.
 *
 * @package
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->dirroot/enrol/locallib.php");

$themeType = 'hillhead40';
$hillheadnotificationtype = get_config('theme_hillhead40', 'hillhead_notification_type');
$hillheadNotificationText =  get_config('theme_hillhead40', 'hillhead_notification');

if((empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($hillheadNotificationText), $_SESSION['SESSION']->hillhead_notifications)) && $PAGE->pagetype != 'admin-search') {
    switch($hillheadnotificationtype) {
        case 'alert-danger':
            $notiftext = '<div class="alert alert-danger mb-3 d-flex align-items-center"><i class="fa fa-warning"></i><span class="d-flex-item">'.$hillheadNotificationText.'</span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/' . $themeType . '/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
            break;
        case 'alert-warning':
            $notiftext = '<div class="alert alert-warning mb-3 d-flex align-items-center"><i class="fa fa-warning"></i><span class="d-flex-item">'.$hillheadNotificationText.'</span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/' . $themeType . '/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
            break;
        case 'alert-success':
            $notiftext = '<div class="alert alert-success mb-3 d-flex align-items-center"><i class="fa fa-info-circle"></i><span class="d-flex-item">'.$hillheadNotificationText.'</span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/' . $themeType . '/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
            break;
        case 'alert-info':
            $notiftext = '<div class="alert alert-info mb-3 d-flex align-items-center"><i class="fa fa-info-circle"></i><span class="d-flex-item">'.$hillheadNotificationText.'</span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/' . $themeType . '/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
            break;
        default:
            $notiftext = '';
    }
} else {
    $notiftext = '';
}

$hillheadoldbrowseralerts = get_config('theme_hillhead40', 'hillhead_old_browser_alerts');

if ($hillheadoldbrowseralerts == 'enabled') {
    $userAgentFlags = array(
        'windows-xp' => '/(Windows NT 5.1)|(Windows XP)/',
        'firefox-1-51' => '/Firefox\/([0-9]|[1-4][0-9]|5[0-1])\b/',
        'safari-1-7' => '/(?=.*?AppleWebKit\/([0-9][0-9]|[0-5][0-9][0-9]|600)\b)(?!.*?Chrome\/).*/',
        'ie-5-10' => '/MSIE ([5-9]|10)\b/'
    );

    $friendlyNames = array(
        'windows-xp' => 'Windows XP',
        'firefox-1-51' => 'an old version of Firefox',
        'safari-1-7' => 'an old version of Safari',
        'ie-5-10' => 'an old version of Internet Explorer'
    );

    $flags = array();

    foreach ($userAgentFlags as $flag => $regex) {
        if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
            $flags[$flag] = $flag;
        }
    }

    foreach ($flags as $flag) {
        $oldBrowserText = '<strong>You\'re using ' . $friendlyNames[$flag] . '</strong>.&ensp;';
        switch ($flag) {
            case 'windows-xp':
                $oldBrowserText .= 'Windows XP is obsolete and hasn\'t received security updates since 2014.';
                break;
            default:
                $oldBrowserText .= 'This is an old browser and isn\'t supported by Moodle. Some things might be broken or might not look right.';
                //$oldBrowserText .= 'Debug Information: '.$_SERVER['HTTP_USER_AGENT'].print_r($flags, true);
                break;
        }

        $notiftext .= '<div class="alert mb-3 alert-danger d-flex align-items-center"><i class="fa fa-exclamation-triangle d-flex-item"></i><span class="d-flex-item">' . $oldBrowserText . '</span></div>';
    }

}

if ($PAGE->pagetype == 'my-index') {

    $dashboardNotification = get_config('theme_hillhead40', 'hillhead_student_course_alert');

    if ($dashboardNotification == 'enabled') {
        $notiftext .= '<div class="alert alert-info alert-jumbo"><a class="close" href="' . $CFG->wwwroot . '/theme/hillhead40/notification.php?h=' . md5('DashboardNoCourseWarning') . '" aria-label="Close"><span aria-hidden="true">&times;</span></a><span class="d-flex-item"><i class="fa fa-info-circle"></i>Can\'t see one of your courses?' . get_config('theme_hillhead40', 'hillhead_student_course_alert_text') . '</span></div>';
    }
}

$templatecontext['notifications'] = $notiftext;