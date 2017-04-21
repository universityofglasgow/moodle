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

if (isloggedin() && !behat_is_test_site()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}

$theme_hillhead_font = get_user_preferences('theme_hillhead_font');

switch($theme_hillhead_font) {
    case 'modern':
        $extraclasses[]='hillhead-font-modern';
        break;
    case 'classic':
        $extraclasses[]='hillhead-font-classic';
        break;
    case 'comic':
        $extraclasses[]='hillhead-font-comic';
        break;
}

$theme_hillhead_size = get_user_preferences('theme_hillhead_size');

switch($theme_hillhead_size) {
    case '120':
        $extraclasses[]='hillhead-size-120';
        break;
    case '140':
        $extraclasses[]='hillhead-size-140';
        break;
    case '160':
        $extraclasses[]='hillhead-size-160';
        break;
}

$theme_hillhead_contrast = get_user_preferences('theme_hillhead_contrast');

switch($theme_hillhead_contrast) {
    case 'by':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-by';
        break;
    case 'yb':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-yb';
        break;
    case 'wg':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-wg';
        break;
    case 'bb':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-bb';
        break;
    case 'br':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-br';
        break;
}

$theme_hillhead_bold = get_user_preferences('theme_hillhead_bold');

switch($theme_hillhead_bold) {
    case 'on':
        $extraclasses[]='hillhead-bold';
        break;
}

$hillheadnotificationtype = get_config('theme_hillhead', 'hillhead_notification_type');

switch($hillheadnotificationtype) {
    case 'alert-danger':
        $notiftext = '<div class="alert alert-danger"><i class="fa fa-warning"></i>&emsp;'.get_config('theme_hillhead', 'hillhead_notification').'</div>';
        break;
    case 'alert-warning':
        $notiftext = '<div class="alert alert-warning"><i class="fa fa-warning"></i>&emsp;'.get_config('theme_hillhead', 'hillhead_notification').'</div>';
        break;
    case 'alert-success':
        $notiftext = '<div class="alert alert-success"><i class="fa fa-info-circle"></i>&emsp;'.get_config('theme_hillhead', 'hillhead_notification').'</div>';
        break;
    case 'alert-info':
        $notiftext = '<div class="alert alert-info"><i class="fa fa-info-circle"></i>&emsp;'.get_config('theme_hillhead', 'hillhead_notification').'</div>';
        break;
    default:
        $notiftext = '';
}

$usesAccessibilityTools=get_user_preferences('theme_hillhead_accessibility', false);

if($usesAccessibilityTools === false) {
    $accessibilityTools = Array(
        Array(
            Array(
                'o'=>'theme_hillhead_accessibility',
                'v'=>'on',
                'c'=>'hh-acc-ac-on',
                't'=>'Show Accessibility Tools',
            ),
        ),
    );
} else {
    $accessibilityTools = Array(
        Array(
            Array(
                'o'=>'theme_hillhead_accessibility',
                'v'=>'clear',
                'c'=>'hh-acc-ac-of',
                't'=>'Hide Accessibility Tools',
            ),
        ),
        Array(
            Array(
                'o'=>'theme_hillhead_font',
                'v'=>'clear',
                'c'=>'hh-acc-ft-de',
                't'=>'Default Font',
            ),
            Array(
                'o'=>'theme_hillhead_font',
                'v'=>'modern',
                'c'=>'hh-acc-ft-mo',
                't'=>'Modern Font',
            ),
            Array(
                'o'=>'theme_hillhead_font',
                'v'=>'classic',
                'c'=>'hh-acc-ft-cl',
                't'=>'Classic Font',
            ),
            Array(
                'o'=>'theme_hillhead_font',
                'v'=>'comic',
                'c'=>'hh-acc-ft-co',
                't'=>'Comic Font',
            ),
        ),
        Array(
            Array(
                'o'=>'theme_hillhead_size',
                'v'=>'clear',
                'c'=>'hh-acc-fs-10',
                't'=>'Default Text Size',
            ),
            Array(
                'o'=>'theme_hillhead_size',
                'v'=>'120',
                'c'=>'hh-acc-fs-12',
                't'=>'Large Text Size',
            ),
            Array(
                'o'=>'theme_hillhead_size',
                'v'=>'140',
                'c'=>'hh-acc-fs-14',
                't'=>'Huge Text Size',
            ),
        ),
        Array(
            Array(
                'o'=>'theme_hillhead_bold',
                'v'=>'clear',
                'c'=>'hh-acc-fb-of',
                't'=>'Use Normal Fonts',
            ),
            Array(
                'o'=>'theme_hillhead_bold',
                'v'=>'on',
                'c'=>'hh-acc-fb-on',
                't'=>'Use Bold Fonts',
            ),
        ),
        Array(
            Array(
                'o'=>'theme_hillhead_contrast',
                'v'=>'clear',
                'c'=>'hh-acc-th-de',
                't'=>'Default Moodle Theme',
            ),
            Array(
                'o'=>'theme_hillhead_contrast',
                'v'=>'yb',
                'c'=>'hh-acc-th-yb',
                't'=>'Yellow on Black Theme',
            ),
            Array(
                'o'=>'theme_hillhead_contrast',
                'v'=>'by',
                'c'=>'hh-acc-th-by',
                't'=>'Black on Yellow Theme',
            ),
            Array(
                'o'=>'theme_hillhead_contrast',
                'v'=>'wg',
                'c'=>'hh-acc-th-wg',
                't'=>'White on Grey Theme',
            ),
            Array(
                'o'=>'theme_hillhead_contrast',
                'v'=>'br',
                'c'=>'hh-acc-th-br',
                't'=>'Black on Red Theme',
            ),
            Array(
                'o'=>'theme_hillhead_contrast',
                'v'=>'bb',
                'c'=>'hh-acc-th-bb',
                't'=>'Black on Blue Theme',
            ),
        ),
    );
}

$acc = '';

foreach($accessibilityTools as $accessibilityGroup) {
    $acc .= '<nav class="list-group m-t-1">';
    foreach($accessibilityGroup as $accessibilityItem) {
        $acc .= '<a class="list-group-item hh-acc '.$accessibilityItem['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$accessibilityItem['o'].'&v='.$accessibilityItem['v'].'">'.$accessibilityItem['t'].'</a>';
    }
    $acc .= '</nav>';
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
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'hillheadnotification' => $notiftext,
    'accessibilityText' => $acc
];

$templatecontext['flatnavigation'] = $PAGE->flatnav;

echo $OUTPUT->render_from_template('theme_hillhead/columns2', $templatecontext);