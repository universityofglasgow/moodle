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
require_once("$CFG->dirroot/enrol/locallib.php");

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
    case 'night':
        $extraclasses[]='hillhead-night';
        break;
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
        $extraclasses[]='hillhead-stripstyles';
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
            $notiftext = '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-info-circle"></i><span>'.$hillheadNotificationText.'</span></div>';
            break;
        default:
            $notiftext = '';
    }
} else {
    $notiftext = '';
}

$hillheadsmartalerts = get_config('theme_hillhead', 'hillhead_smart_alerts');

if((substr($PAGE->pagetype, 0, 11) == 'course-view') && ($hillheadsmartalerts == 'enabled')) {

    $courseDetails = $PAGE->course;
    
    $automaticEnrolmentsDisabled = false;
    
    if((!empty($courseDetails->id)) && $courseDetails->id != 1) {
        if($courseDetails->visible=='0') {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseinvisible'), $_SESSION['SESSION']->hillhead_notifications)) {
                $notiftext .= '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseinvisible').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-eye-slash"></i><span><strong>This course is currently hidden.</strong> You can see it, but students can\'t. You can unhide this course <a href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span></div>';
                $automaticEnrolmentsDisabled = true;
                $automaticEnrolmentsReason = 'this course has been hidden from students.';
            }
        }
            
        $context = context_course::instance($courseDetails->id);
        $studentyUsers = count_role_users(5, $PAGE->context);
                
        if($studentyUsers === 0) {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'coursenostudents'), $_SESSION['SESSION']->hillhead_notifications)) {
                $notiftext .= '<div class="alert alert-warning"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'coursenostudents').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-users"></i><span><strong>There are no students on this course.</strong> <a href="https://www.gla.ac.uk/myglasgow/moodle/universityofglasgowmoodleguides/enrollingstudentsonmoodlecourses/" target="_blank">How do I add students to my course?</a></span></div>';
            }
        }
        
        if(!empty($courseDetails->enddate) && ($courseDetails->enddate) < time()) {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseenddate'), $_SESSION['SESSION']->hillhead_notifications)) {
                $notiftext .= '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseenddate').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-clock"></i><span><strong>This course\'s end date is in the past.</strong> MyCampus enrolments are frozen, and won\'t be updated. If you\'re still using this course, you can change the end date <a href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span></div>';
                $automaticEnrolmentsDisabled = true;
                $automaticEnrolmentsReason = 'the course\'s end date is in the past. Any old MyCampus enrolments have been frozen and won\'t be removed.';
            }
        }
        
        if(!empty($courseDetails->startdate) && ($courseDetails->startdate) > time()) {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseenddate'), $_SESSION['SESSION']->hillhead_notifications)) {
                $notiftext .= '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseenddate').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-clock"></i><span><strong>This course\'s start date is in the future.</strong> If you\'re using this course right now, you can change the start date <a href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span></div>';
                $automaticEnrolmentsDisabled = true;
                $automaticEnrolmentsReason = 'the course\'s start date is in the future.';
            }
        }
        
        $enman = new course_enrolment_manager($PAGE, $courseDetails);
        $enrolmentInstances = $enman->get_enrolment_instances();
    
        $usesMyCampus = false;
        $usesSelfEnrolment = false;
        foreach($enrolmentInstances as $enrolmentInstance) {
            switch($enrolmentInstance->enrol) {
                case 'gudatabase':
                    if ($enrolmentInstance->status==0) {
                        $usesMyCampus = true;
                    }
                    break;
                case 'self':
                    if ($enrolmentInstance->status==0) {
                        if(empty($enrolmentInstance->password)) {
                            $usesSelfEnrolment = true;
                        }
                    }
                    break;
            }
        }
        
        if ($usesMyCampus == false) {
            $automaticEnrolmentsDisabled = true;
            $automaticEnrolmentsReason = 'the plugin has been disabled.';
        }

        
        if ($automaticEnrolmentsDisabled) {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'nomycampus'), $_SESSION['SESSION']->hillhead_notifications)) {
                $notiftext .= '<div class="alert alert-danger"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'nomycampus').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-refresh"></i><span><strong>MyCampus enrolments are disabled for this course.</strong> This is because '.$automaticEnrolmentsReason.' <a href="'.$CFG->wwwroot.'/report/guenrol/index.php?id='.$courseDetails->id.'">More Information</a></span></div>';
            }
        }
        
        if ($usesSelfEnrolment) {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'selfenabled'), $_SESSION['SESSION']->hillhead_notifications)) {
                $notiftext .= '<div class="alert alert-warning"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'selfenabled').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-user-plus"></i><span><strong>Self-enrolment is enabled for this course, with no enrolment key.</strong> This means anybody can add themselves to this course. <a href="'.$CFG->wwwroot.'/enrol/instances.php?id='.$courseDetails->id.'">Manage Enrolments</a></span></div>';
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
                'i'=>'fa-universal-access'
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
                'i'=>'fa-universal-access'
            ),
        ),
    );
}

$accBtn = '';
$accTxt = '';

foreach($accessibilityButton as $accessibilityGroup) {
    $accBtn .= '<nav class="list-group accessibility-toggle">';
    foreach($accessibilityGroup as $accessibilityItem) {
        $accBtn .= '<a class="list-group-item list-group-item-action hh-acc '.$accessibilityItem['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$accessibilityItem['o'].'&v='.$accessibilityItem['v'].'" data-key="accessibility"><div class="m-l-0"><div class="media"><span class="media-left"><i class="fa '.$accessibilityItem['i'].'"></i></span><span class="media-body"> '.$accessibilityItem['t'].'</span></div></div></a>';
    }
    $accBtn .= '</nav>';
}

$colourOptions = Array(
    Array(
        'o'=>'theme_hillhead_contrast',
        'v'=>'clear',
        'c'=>'hh-acc-th-de',
        't'=>'Day Mode',
        'i'=>'fa-sun-o'
    ),
    Array(
        'o'=>'theme_hillhead_contrast',
        'v'=>'night',
        'c'=>'hh-acc-th-nt',
        't'=>'Night Mode',
        'i'=>'fa-moon-o'
    ),
);
$accessibleColourOptions = Array(
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
            't'=>'Show Custom Fonts &amp; Colours',
            'i'=>'fa-minus-square'
        )
    );
} else {
    $stripStyleOptions = Array(
        Array(
            'o'=>'theme_hillhead_stripstyles',
            'v'=>'on',
            'c'=>'hh-acc-ss-of',
            't'=>'Don\'t Show Custom Fonts &amp; Colours',
            'i'=>'fa-plus-square'
        )
    );
}

if($usesAccessibilityTools) {
    $accTxt = '<div class="block card m-t-1 accessibility-tools"><div class="block-heading"><h3>Accessibility Tools<a class="float-right" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o=theme_hillhead_accessibility&v=clear" data-key="accessibility"><i class="fa fa-times"></i></a></h3></div><div class="block-content">';
    $accTxt .= '<div class="row">';
    $accTxt .= '<div class="col-xs-12 col-sm-4 accessibility-group">';
    $accTxt .= '<h4>Colour Scheme</h4><ul class="accessibility-features">';
    foreach($colourOptions as $opt) {
        $accTxt .= '<li><a class="hh-acc" id="'.$opt['c'].'" href="'.$CFG->wwwroot.'/theme/hillhead/accessibility.php?o='.$opt['o'].'&v='.$opt['v'].'"><i class="fa '.$opt['i'].'"></i>'.$opt['t'].'</a></li>';
    }
    $accTxt .= '</ul>';
    $accTxt .= '<h4>Accessible Colour Schemes</h4><ul class="accessibility-features">';
    foreach($accessibleColourOptions as $opt) {
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
    foreach($stripStyleOptions as $opt) {
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
$canSeeGUIDReport = has_capability('report/guid:view', $siteContext);

if($isAdmin) {
    $footerLinks['middle']['Site Links']['Purge All Caches'] = $CFG->wwwroot.'/admin/purgecaches.php?confirm=1&sesskey='.sesskey().'&returnurl='.$PAGE->url->out_as_local_url(false);
}
if($canSeeGUIDReport) {
    $footerLinks['middle']['Site Links']['GUID Search'] = $CFG->wwwroot.'/report/guid/index.php';
}

$footerLinks['middle']['Site Links']['Moodle Mobile App'] = tool_mobile_create_app_download_url();

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