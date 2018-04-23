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

$extraScripts = '';

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
        case 'mono':
        $extraclasses[]='hillhead-font-mono';
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
    case '180':
        $extraclasses[]='hillhead-size-180';
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
    case 'bw':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-bw';
        break;
}

$theme_hillhead_bold = get_user_preferences('theme_hillhead_bold');

switch($theme_hillhead_bold) {
    case 'on':
        $extraclasses[]='hillhead-bold';
        break;
}

$theme_hillhead_spacing = get_user_preferences('theme_hillhead_spacing');

switch($theme_hillhead_spacing) {
    case 'on':
        $extraclasses[]='hillhead-spacing';
        break;
}

$theme_hillhead_read_highlight = get_user_preferences('theme_hillhead_readtome');

switch($theme_hillhead_read_highlight) {
    case 'on':
        $extraScripts .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/theme/hillhead/js/readtome.js"></script>';
        break;
}

$theme_hillhead_read_alert = get_user_preferences('theme_hillhead_readalert');

switch($theme_hillhead_read_alert) {
    case 'on':
        $extraclasses[]='hillhead-readalert';
        break;
}

$theme_hillhead_stripstyles = get_user_preferences('theme_hillhead_stripstyles');

switch($theme_hillhead_stripstyles) {
    case 'on':
        $extraScripts .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/theme/hillhead/js/stripstyles.js"></script>';
        break;
}

$hillheadnotificationtype = get_config('theme_hillhead', 'hillhead_notification_type');
$hillheadNotificationText =  get_config('theme_hillhead', 'hillhead_notification');

if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($hillheadNotificationText), $_SESSION['SESSION']->hillhead_notifications)) {
    switch($hillheadnotificationtype) {
        case 'alert-danger':
            $notiftext = '<div class="alert alert-danger"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-warning"></i><span>'.$hillheadNotificationText.'</span></div>';
            break;
        case 'alert-warning':
            $notiftext = '<div class="alert alert-warning"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-warning"></i><span>'.$hillheadNotificationText.'</span></div>';
            break;
        case 'alert-success':
            $notiftext = '<div class="alert alert-success"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-info-circle"></i><span>'.$hillheadNotificationText.'</span></div>';
            break;
        case 'alert-info':
            $notiftext = '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-info-circle"></i><span>'.$$hillheadNotificationText.'</span></div>';
            break;
        default:
            $notiftext = '';
    }
} else {
    $notiftext = '';
}

$hillheadsmartalerts = get_config('theme_hillhead', 'hillhead_smart_alerts');

if($hillheadsmartalerts == 'enabled') {

    $courseDetails = $PAGE->course;
    
    if((!empty($courseDetails->id)) && $courseDetails->id != 1) {
        if($courseDetails->visible=='0') {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseinvisible'), $_SESSION['SESSION']->hillhead_notifications)) {
                $notiftext .= '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseinvisible').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-info-circle"></i><span><strong>This course is currently hidden.</strong> You can see it, but students can\'t. You can unhide this course <a href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span></div>';
            }
        }
            
        $context = context_course::instance($courseDetails->id);
        $studentyUsers = count_role_users(3, $PAGE->context);
        
        if($studentyUsers === 0) {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'coursenostudents'), $_SESSION['SESSION']->hillhead_notifications)) {
                $notiftext .= '<div class="alert alert-warning"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'coursenostudents').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-info-circle"></i><span><strong>There are no students on this course.</strong> <a href="https://www.gla.ac.uk/myglasgow/moodle/universityofglasgowmoodleguides/enrollingstudentsonmoodlecourses/" target="_blank">How do I add students to my course?</a></span></div>';
            }
        }
        
        if(($courseDetails->enddate) < time()) {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseenddate'), $_SESSION['SESSION']->hillhead_notifications)) {
                $notiftext .= '<div class="alert alert-danger"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseenddate').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-info-circle"></i><span><strong>This course\'s end date is in the past.</strong> If you\'re still using this course, you should update the end date so Automatic Rollover works. You can change your course\'s start and end dates <a href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span></div>';
            }
        }
        
    }
    
}

$usesAccessibilityTools=get_user_preferences('theme_hillhead_accessibility', false);

if($usesAccessibilityTools === false) {
    $accessibilityButton = Array(
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
    $accessibilityButton = Array(
        Array(
            Array(
                'o'=>'theme_hillhead_accessibility',
                'v'=>'clear',
                'c'=>'hh-acc-ac-of',
                't'=>'Hide Accessibility Tools',
            ),
        ),
    );
}

$accBtn = '';
$accTxt = '';

foreach($accessibilityButton as $accessibilityGroup) {
    $accBtn .= '<nav class="list-group accessibility-toggle">';
    foreach($accessibilityGroup as $accessibilityItem) {
        $accBtn .= '<a class="list-group-item list-group-item-action hh-acc '.$accessibilityItem['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$accessibilityItem['o'].'&v='.$accessibilityItem['v'].'" data-key="accessibility"><div class="m-l-0"> '.$accessibilityItem['t'].'</div></a>';
    }
    $accBtn .= '</nav>';
}

$colourOptions = Array(
    Array(
        'o'=>'theme_hillhead_contrast',
        'v'=>'clear',
        'c'=>'hh-acc-th-de',
        't'=>'Default Moodle Theme',
        'i'=>'fa-low-vision'
    ),
    Array(
        'o'=>'theme_hillhead_contrast',
        'v'=>'yb',
        'c'=>'hh-acc-th-yb',
        't'=>'Yellow on Black Theme',
        'i'=>'fa-low-vision'
    ),
    Array(
        'o'=>'theme_hillhead_contrast',
        'v'=>'by',
        'c'=>'hh-acc-th-by',
        't'=>'Black on Yellow Theme',
        'i'=>'fa-low-vision'
    ),
    Array(
        'o'=>'theme_hillhead_contrast',
        'v'=>'wg',
        'c'=>'hh-acc-th-wg',
        't'=>'White on Grey Theme',
        'i'=>'fa-low-vision'
    ),
    Array(
        'o'=>'theme_hillhead_contrast',
        'v'=>'br',
        'c'=>'hh-acc-th-br',
        't'=>'Black on Red Theme',
        'i'=>'fa-low-vision'
    ),
    Array(
        'o'=>'theme_hillhead_contrast',
        'v'=>'bb',
        'c'=>'hh-acc-th-bb',
        't'=>'Black on Blue Theme',
        'i'=>'fa-low-vision'
    ),
    Array(
        'o'=>'theme_hillhead_contrast',
        'v'=>'bw',
        'c'=>'hh-acc-th-bw',
        't'=>'Black on White Theme',
        'i'=>'fa-low-vision'
    )
);

$fontOptions = Array(
    Array(
        'o'=>'theme_hillhead_font',
        'v'=>'clear',
        'c'=>'hh-acc-ft-de',
        't'=>'Default Font',
        'i'=>'fa-font'
    ),
    Array(
        'o'=>'theme_hillhead_font',
        'v'=>'modern',
        'c'=>'hh-acc-ft-mo',
        't'=>'Modern Font',
        'i'=>'fa-font'
    ),
    Array(
        'o'=>'theme_hillhead_font',
        'v'=>'classic',
        'c'=>'hh-acc-ft-cl',
        't'=>'Classic Font',
        'i'=>'fa-font'
    ),
    Array(
        'o'=>'theme_hillhead_font',
        'v'=>'comic',
        'c'=>'hh-acc-ft-co',
        't'=>'Comic Font',
        'i'=>'fa-font'
    ),
    Array(
        'o'=>'theme_hillhead_font',
        'v'=>'mono',
        'c'=>'hh-acc-ft-mn',
        't'=>'Monospace Font',
        'i'=>'fa-font'
    )
);

if($theme_hillhead_bold == 'on') {
    $boldOptions = Array(
        Array(
            'o'=>'theme_hillhead_bold',
            'v'=>'clear',
            'c'=>'hh-acc-fb-of',
            't'=>'Don\'t Always Use Bold Fonts',
            'i'=>'fa-bold'
        )
    );
} else {
    $boldOptions = Array(
        Array(
            'o'=>'theme_hillhead_bold',
            'v'=>'on',
            'c'=>'hh-acc-fb-on',
            't'=>'Always Use Bold Fonts',
            'i'=>'fa-bold'
        )
    );
}

if($theme_hillhead_spacing == 'on') {
    $spacingOptions = Array(
        Array(
            'o'=>'theme_hillhead_spacing',
            'v'=>'clear',
            'c'=>'hh-acc-sp-of',
            't'=>'Normal Space Between Lines',
            'i'=>'fa-align-justify'
        )
    );
} else {
    $spacingOptions = Array(
        Array(
            'o'=>'theme_hillhead_spacing',
            'v'=>'on',
            'c'=>'hh-acc-sp-on',
            't'=>'More Space Between Lines',
            'i'=>'fa-align-justify'
        )
    );
}

$sizeOptions = Array(
    Array(
        'o'=>'theme_hillhead_size',
        'v'=>'clear',
        'c'=>'hh-acc-fs-10',
        't'=>'Default Text Size',
        'i'=>'fa-text-height'
    ),
    Array(
        'o'=>'theme_hillhead_size',
        'v'=>'120',
        'c'=>'hh-acc-fs-12',
        't'=>'Large Text Size',
        'i'=>'fa-text-height'
    ),
    Array(
        'o'=>'theme_hillhead_size',
        'v'=>'140',
        'c'=>'hh-acc-fs-14',
        't'=>'Huge Text Size',
        'i'=>'fa-text-height'
    ),
    Array(
        'o'=>'theme_hillhead_size',
        'v'=>'160',
        'c'=>'hh-acc-fs-16',
        't'=>'Massive Text Size',
        'i'=>'fa-text-height'
    ),
    Array(
        'o'=>'theme_hillhead_size',
        'v'=>'180',
        'c'=>'hh-acc-fs-18',
        't'=>'Gigantic Text Size',
        'i'=>'fa-text-height'
    )
);

if($theme_hillhead_read_highlight == 'on') {
    $readHighlightOptions = Array(
        Array(
            'o'=>'theme_hillhead_readtome',
            'v'=>'clear',
            'c'=>'hh-acc-sp-of',
            't'=>'Turn Off Read-To-Me',
            'i'=>'fa-headphones'
        )
    );
} else {
    $readHighlightOptions = Array(
        Array(
            'o'=>'theme_hillhead_readtome',
            'v'=>'on',
            'c'=>'hh-acc-sp-on',
            't'=>'Turn On Read-To-Me',
            'i'=>'fa-headphones'
        )
    );
}

if($theme_hillhead_read_alert == 'on') {
    $readAlertOptions = Array(
        Array(
            'o'=>'theme_hillhead_readalert',
            'v'=>'clear',
            'c'=>'hh-acc-sp-of',
            't'=>'Don\'t Announce Notifications',
            'i'=>'fa-bullhorn'
        )
    );
} else {
    $readAlertOptions = Array(
        Array(
            'o'=>'theme_hillhead_readalert',
            'v'=>'on',
            'c'=>'hh-acc-sp-on',
            't'=>'Announce Moodle Notifications',
            'i'=>'fa-bullhorn'
        )
    );
}

if($theme_hillhead_stripstyles == 'on') {
    $stripStyleOptions = Array(
        Array(
            'o'=>'theme_hillhead_stripstyles',
            'v'=>'clear',
            'c'=>'hh-acc-ss-of',
            't'=>'Don\'t Apply to All Moodle Content',
            'i'=>'fa-minus-square'
        )
    );
} else {
    $stripStyleOptions = Array(
        Array(
            'o'=>'theme_hillhead_stripstyles',
            'v'=>'on',
            'c'=>'hh-acc-ss-of',
            't'=>'Apply to All Moodle Content',
            'i'=>'fa-plus-square'
        )
    );
}

if($usesAccessibilityTools) {
    $accTxt = '<div class="card accessibility-card"><div class="card-block"><h3>Accessibility Tools</h3>';
    $accTxt .= '<div class="row">';
    $accTxt .= '<div class="col-xs-12 col-sm-4 accessibility-group">';
    $accTxt .= '<h4>Colour Scheme</h4><ul class="accessibility-features">';
    foreach($colourOptions as $opt) {
        $accTxt .= '<li><a class="hh-acc" id="'.$opt['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$opt['o'].'&v='.$opt['v'].'"><i class="fa '.$opt['i'].'"></i>'.$opt['t'].'</a></li>';
    }
    foreach($stripStyleOptions as $opt) {
        $accTxt .= '<li><a class="hh-acc" id="'.$opt['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$opt['o'].'&v='.$opt['v'].'"><i class="fa '.$opt['i'].'"></i>'.$opt['t'].'</a></li>';
    }
    $accTxt .= '</ul>';
    $accTxt .= '</div>';
    $accTxt .= '<div class="col-xs-12 col-sm-4 accessibility-group">';
    $accTxt .= '<h4>Font Style</h4><ul class="accessibility-features">';
    
    foreach($fontOptions as $opt) {
        $accTxt .= '<li><a class="hh-acc" id="'.$opt['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$opt['o'].'&v='.$opt['v'].'"><i class="fa '.$opt['i'].'"></i>'.$opt['t'].'</a></li>';
    }
    $accTxt .= '</ul>';
    $accTxt .= '<h4>Readability</h4><ul class="accessibility-features">';
    foreach($boldOptions as $opt) {
        $accTxt .= '<li><a class="hh-acc" id="'.$opt['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$opt['o'].'&v='.$opt['v'].'"><i class="fa '.$opt['i'].'"></i>'.$opt['t'].'</a></li>';
    }
    foreach($spacingOptions as $opt) {
        $accTxt .= '<li><a class="hh-acc" id="'.$opt['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$opt['o'].'&v='.$opt['v'].'"><i class="fa '.$opt['i'].'"></i>'.$opt['t'].'</a></li>';
    }
    $accTxt .= '</ul>';
    $accTxt .= '</div>';
    $accTxt .= '<div class="col-xs-12 col-sm-4 accessibility-group">';
    $accTxt .= '<h4>Text Size and Spacing</h4><ul class="accessibility-features">';
    foreach($sizeOptions as $opt) {
        $accTxt .= '<li><a class="hh-acc" id="'.$opt['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$opt['o'].'&v='.$opt['v'].'"><i class="fa '.$opt['i'].'"></i>'.$opt['t'].'</a></li>';
    }
    $accTxt .= '</ul>';
    $accTxt .= '<h4>Read To Me</h4><ul class="accessibility-features">';
    foreach($readHighlightOptions as $opt) {
        $accTxt .= '<li><a class="hh-acc" id="'.$opt['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$opt['o'].'&v='.$opt['v'].'"><i class="fa '.$opt['i'].'"></i>'.$opt['t'].'</a></li>';
    }
    foreach($readAlertOptions as $opt) {
        $accTxt .= '<li><a class="hh-acc" id="'.$opt['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$opt['o'].'&v='.$opt['v'].'"><i class="fa '.$opt['i'].'"></i>'.$opt['t'].'</a></li>';
    }
    $accTxt .= '</ul>';
    $accTxt .= '</div>';
    $accTxt .= '</div>';
    $accTxt .= '</div></div>';
}

$footerLinks = Array(
    'middle' => Array(
        'Site Links' => Array(
            'Dashboard' => $CFG->wwwroot.'/my',
            'Course Directory' => $CFG->wwwroot.'/course'
        ),
    ),
    'right' => Array(
        'Current Students' => Array(
            'MyGlasgow Students' => 'http://www.gla.ac.uk/students/myglasgow/'
        ),
        'Current Staff' => Array(
            'MyGlasgow Staff' => 'http://www.gla.ac.uk/myglasgow/staff/'
        )
    )
);

$siteContext = context_system::instance();
$isAdmin = has_capability('moodle/site:config', $siteContext);

if($isAdmin) {
    $footerLinks['middle']['Site Links']['Purge All Caches'] = $CFG->wwwroot.'/admin/purgecaches.php?confirm=1&sesskey='.sesskey().'&returnurl='.$PAGE->url->out_as_local_url(false);
    $footerLinks['middle']['Site Links']['GUID Search'] = $CFG->wwwroot.'/report/guid/index.php';
}

$footerLinks['middle']['Site Links']['GUID Search'] = $CFG->wwwroot.'/report/guid/index.php';

$footerText = '<div class="row">
            <div class="col-sm-6">
    			<h3 class="glasgow">University <em>of</em> Glasgow</h3>
    			<p class="address">Glasgow, G12 8QQ, Scotland</p>
    			<p class="phone">Tel +44 (0) 141 330 2000</p>
    			<p class="charity">The University of Glasgow is a registered Scottish charity: Registration Number SC004401</p>
            </div>
            <div class="col-sm-3 footer-links">';
foreach($footerLinks['middle'] as $sectionHeading=>$sectionLinks) {
    $footerText .= '<h4>'.$sectionHeading.'</h4><ul>';
    foreach($sectionLinks as $linkTitle=>$linkLink) {
        $footerText .= '<li><a href="'.$linkLink.'">'.$linkTitle.'</a></li>';
    }
    if($sectionHeading=='Site Links') {
        // Nasty hack for Moodle Docs link
        $footerText .= '<li class="moodle-footer-doc-link">'.page_doc_link('Help with this page').'</li>';
        $footerText .= '<li class="logininfo"></li>';
    }
    $footerText .= '</ul>';
}
$footerText .= '</div>
    		<div class="col-sm-3 footer-links">';
foreach($footerLinks['right'] as $sectionHeading=>$sectionLinks) {
    $footerText .= '<h4>'.$sectionHeading.'</h4><ul>';
    foreach($sectionLinks as $linkTitle=>$linkLink) {
        $footerText .= '<li><a href="'.$linkLink.'">'.$linkTitle.'</a></li>';
    }
    $footerText .= '</ul>';
}
$footerText .= '</div></div>';

$hillheadHelpLink = get_config('theme_hillhead', 'hillhead_helpcentre');
if(!empty($hillheadHelpLink)) {
    $hillheadHelpLinkText = '<div class="help-centre popover-region"><div class="nav-link"><a href="'.$hillheadHelpLink.'" title="Help with Moodle"><i class="fa fa-question-circle"></i></a></div></div>';
} else {
    $hillheadHelpLinkText = '';
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
    'accessibilityText' => $accTxt,
    'accessibilityButton' => $accBtn,
    'footerText' => $footerText,
    'extraScripts' => $extraScripts,
    'hillheadhelplink' => $hillheadHelpLinkText
];

$templatecontext['flatnavigation'] = $PAGE->flatnav;

echo $OUTPUT->render_from_template('theme_hillhead/columns2', $templatecontext);