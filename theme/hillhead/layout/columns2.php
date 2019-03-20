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

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
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

$siteContext = context_system::instance();
$isAdmin = has_capability('moodle/site:config', $siteContext);
$canSeeGUIDReport = has_capability('report/guid:view', $siteContext);

if($canSeeGUIDReport) {
    $guidReportLink = new moodle_url('/report/guid/index.php');
    $guidReportNav = navigation_node::create('GUID Report', $guidReportLink);
    $guidFlat = new flat_navigation_node($guidReportNav, 0);
    $guidFlat->key = 'guidreport';
    $guidFlat->icon = new pix_icon('a/search', 'GUID Report', 'moodle');
    $PAGE->flatnav->add($guidFlat);
}

if($isAdmin) {
    $purgeLink = new moodle_url('/admin/purgecaches.php?confirm=1&sesskey='.sesskey().'&returnurl='.$PAGE->url->out_as_local_url(false));
    $purgeNav = navigation_node::create('Purge All Caches', $purgeLink);
    $purgeFlat = new flat_navigation_node($purgeNav, 0);
    $purgeFlat->key = 'purgecaches';
    $purgeFlat->icon = new pix_icon('t/delete', 'Purge All Caches', 'moodle');
    $PAGE->flatnav->add($purgeFlat);
}



$flatnav = $PAGE->flatnav;

$coursenav = Array();
$coursenavexists = false;

$sitenav = Array();
$sitenavexists = false;

$thiscoursenav = Array();
$thiscoursenavexists = false;

$settingsnav = Array();
$settingsnavexists = false;

$othernav = Array();
$othernavexists = false;

$adminnav = Array();
$adminnavexists = false;

foreach($flatnav as $navitem) {
    
    switch($navitem->type) {
        case 1:
            $sitenav[] = $navitem;
            $sitenavexists = true;
            break;
        case 20:
            $coursenav[] = $navitem;
            $coursenavexists = true;
            break;
        case 30:
            $thiscoursenav[] = $navitem;
            $thiscoursenavexists = true;
            break;
        default:
            switch($navitem->key) {
                case 'coursehome':
                case 'participants':
                case 'badgesview':
                case 'competencies':
                case 'grades':
                    $settingsnav[] = $navitem;
                    $settingsnavexists = true;
                    break;
                case 'calendar':
                case 'privatefiles':
                    $sitenav[] = $navitem;
                    $sitenavexists = true;
                    break;
                case 'sitesettings':
                case 'purgecaches':
                case 'guidreport':
                    $adminnav[] = $navitem;
                    $adminnavexists = true;
                    break;
                case 'home':
                case 'mycourses':
                    break;
                default:
                    $othernav[] = $navitem;
                    $othernavexists = true;
                    break;
            }
            
    }
}

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
echo $OUTPUT->render_from_template('theme_hillhead/columns2', $templatecontext);

