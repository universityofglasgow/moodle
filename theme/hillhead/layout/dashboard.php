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
 * A two column layout for the boost theme.
 *
 * @package   theme_boost
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/admin/tool/mobile/lib.php');

include('extraclasses.php');

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
];

include('navigation.php');
include('accessibility.php');
include('topnotifications.php');
//include('breadcrumb.php');
include('footerlinks.php');

$dashboardNotification = get_config('theme_hillhead', 'hillhead_student_course_alert');

if($dashboardNotification == 'enabled') {
    $notiftext .= '<div class="alert alert-info alert-jumbo"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5('DashboardNoCourseWarning').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><h3><i class="fa fa-info-circle"></i>Can\'t see one of your courses?</h3>'.get_config('theme_hillhead', 'hillhead_student_course_alert_text').'</div>';
}

$templatecontext['starrednav'] = $starredCourses;
$templatecontext['starrednavbottom'] = $starredCoursesBottom;
$templatecontext['starrednavexists'] = $starredCoursesExists;
$templatecontext['coursenav'] = $coursenav;
$templatecontext['coursenavexists'] = $coursenavexists;
$templatecontext['sitenav'] = $sitenav;
$templatecontext['sitenavexists'] = $sitenavexists;
$templatecontext['thiscoursenav'] = $thiscoursenav;
$templatecontext['thiscoursenavexists'] = $thiscoursenavexists;
$templatecontext['settingsnav'] = $settingsnav;
$templatecontext['settingsnavexists'] = $settingsnavexists;
$templatecontext['othernav'] = $othernav;
$templatecontext['othernavexists'] = $othernavexists;
$templatecontext['adminnav'] = $adminnav;
$templatecontext['adminnavexists'] = $adminnavexists;
$templatecontext['globalcoursenavheading'] = $globalCourseHeader;
$templatecontext['globalcoursenav'] = $globalcoursenav;
$templatecontext['globalcoursenavexists'] = $globalcoursenavexists;
$templatecontext['accessibilityText'] = $accTxt;
$templatecontext['accessibilityButton'] = $accessibilityButton;
$templatecontext['extrascripts'] = $extraScripts;
$templatecontext['notifications'] = $notiftext;
//$templatecontext['breadcrumb'] = $breadcrumbLinks;
$templatecontext['footerlinks'] = $footerLinkText;
$templatecontext['starposturl'] = $starPostURL;

$PAGE->requires->js_call_amd('theme_hillhead/starredcourses', 'init');

echo $OUTPUT->render_from_template('theme_hillhead/columns2', $templatecontext);

