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
 * A drawer based layout for the Hillhead 4.0 theme, inspired by Boost.
 *
 * @package   theme_hillhead40
 * @copyright 2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/admin/tool/mobile/lib.php');
require_once('footerlinks.php');

// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
user_preference_allow_ajax_update('drawer-open-index', PARAM_BOOL);
user_preference_allow_ajax_update('drawer-open-block', PARAM_BOOL);

if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}
$courseindex = core_course_drawer();
if (!$courseindex) {
    $courseindexopen = false;
}

// MOOD-229 - Collapse all course indexes by default.
// Pass through to the template a boolean value for whether the course indexes
// should initially be collapsed or not. Based on how the user prefs mechanism
// works, check if a record for 'coursesectionspreferences_[x]' exists or not.
// If one doesn't, then the user hasn't visited *this* course page before, and
// it is therefore safe to collapse everything. If a record does exist, then let
// the existing mechanism deal with expanding only those selected course indexes.
$courseindexcollapsed = false;
if ($COURSE->id > 1) {
    $sectionpreferences = (array)json_decode(
        get_user_preferences("coursesectionspreferences_{$COURSE->id}", '', $USER->id)
    );
    if (empty($sectionpreferences)) {
        $courseindexcollapsed = true;
    }
}

// Append any additional css classes to the $extraclasses array...
require_once('extraclasses.php');

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
}

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);

// Because the navigation hook *_extend_navigation_*() only works for local, mod
// and report plugins, we need to slot this in before Preferences, and its divider.
if (isset($primarymenu['user']['items']) && count($primarymenu['user']['items']) > 0) {
    $usesaccessibilitytools = get_user_preferences('theme_hillhead40_accessibility', false);
    $varg = 'clear';
    $spantext = 'Hide';
    if ($usesaccessibilitytools === false) {
        $varg = 'on';
        $spantext = 'Show';
    }

    $branchlabel = $spantext . ' Accessibility Tools';
    $branchtitle = str_replace(' ', '-', $branchlabel);

    $accessibilityobj = new stdClass();
    $accessibilityobj->itemtype = 'link';
    $accessibilityobj->title = $branchlabel;
    $accessibilityobj->titleidentifier = $branchtitle;
    $args = [
        'o' => 'theme_hillhead40_accessibility',
        'v' => $varg
    ];
    $accessibilityurl = new moodle_url('/theme/hillhead40/accessibility.php', $args);
    $accessibilityobj->url = $accessibilityurl;
    $accessibilityobj->divider = false;
    $accessibilityobj->link = true;

    // Here begins the slicing and dicing...
    $tmpitems = array_slice($primarymenu['user']['items'], -4, null, true);
    // ...add our Accessibility link to the beginning of this temp array...
    array_unshift($tmpitems, $accessibilityobj);
    // ...finally, replace the items at the end of the array, with the new item...
    array_splice($primarymenu['user']['items'], -4, null, $tmpitems);
}

$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'courseindexcollapsed' => $courseindexcollapsed,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'forceblockdraweropen' => $forceblockdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
    'footerlinks' => $footerlinktext,
    'extrascripts' => $extrascripts
];

// As this file seems to handle most of the layouts,
// we now need to distinguish which section we're in...
require_once('topnotifications.php');
// Integrate the link to the Accessibility tool also....
require_once('accessibility.php');

echo $OUTPUT->render_from_template('theme_hillhead40/drawers', $templatecontext);
