<?php
    
$siteContext = context_system::instance();
$isAdmin = has_capability('moodle/site:config', $siteContext);
$canSeeGUIDReport = has_capability('report/guid:view', $siteContext);

$enhanceLink = new moodle_url('/report/enhance/index.php?id=1');
$enhanveNav = navigation_node::create('VLE Enhancement Requests', $enhanceLink);
$enhanveNavFlat = new flat_navigation_node($enhanveNav, 0);
$enhanveNavFlat->key = 'vleenhancements';
$enhanveNavFlat->icon = new pix_icon('hillhead/vleenhancements', 'VLE Enhancement Requests', 'moodle');
$PAGE->flatnav->add($enhanveNavFlat);

if($canSeeGUIDReport) {
    $guidReportLink = new moodle_url('/report/guid/index.php');
    $guidReportNav = navigation_node::create('GUID Report', $guidReportLink);
    $guidFlat = new flat_navigation_node($guidReportNav, 0);
    $guidFlat->key = 'guidreport';
    $guidFlat->icon = new pix_icon('a/search', 'GUID Report', 'moodle');
    $PAGE->flatnav->add($guidFlat);
}

require_once('starredcourses.php');

if($isAdmin && debugging('', DEBUG_DEVELOPER)) {
    $purgeLink = new moodle_url('/admin/purgecaches.php?confirm=1&sesskey='.sesskey().'&returnurl='.$PAGE->url->out_as_local_url(false));
    $purgeNav = navigation_node::create('Purge All Caches', $purgeLink);
    $purgeFlat = new flat_navigation_node($purgeNav, 0);
    $purgeFlat->key = 'purgecaches';
    $purgeFlat->icon = new pix_icon('t/delete', 'Purge All Caches', 'moodle');
    $PAGE->flatnav->add($purgeFlat);
}
    
$courseDirLink = new moodle_url('/course');
$courseDirNav = navigation_node::create('Browse Courses', $courseDirLink);
$courseDirFlat = new flat_navigation_node($courseDirNav, 0);
$courseDirFlat->key = 'allcourses';
$courseDirFlat->icon = new pix_icon('hillhead/allcourses', 'Browse Courses', 'moodle');
$PAGE->flatnav->add($courseDirFlat);

$hillheadHelpLink = get_config('theme_hillhead', 'hillhead_helpcentre');
if(!empty($hillheadHelpLink)) { 
    $helpLink = new moodle_url($hillheadHelpLink);
    $helpLinkHav = navigation_node::create('Help', $helpLink);
    $helpLinkFlat = new flat_navigation_node($helpLinkHav, 0);
    $helpLinkFlat->key = 'helplink';
    $helpLinkFlat->icon = new pix_icon('e/help', 'Help', 'moodle');
    $PAGE->flatnav->add($helpLinkFlat);
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

$starredCourses = Array();
$starredCoursesExists = false;

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
        case 69:
            $starredCourses[] = $navitem;
            $starredCoursesExists = true;
            break;
        default:
            switch($navitem->key) {
                case 'coursehome':
                    $thiscoursenav[] = $navitem;
                    $thiscoursenavexists = true;
                    break;
                case 'participants':
                case 'badgesview':
                case 'competencies':
                case 'grades':
                    $settingsnav[] = $navitem;
                    $settingsnavexists = true;
                    break;
                case 'calendar':
                case 'privatefiles';
                case 'home':
                case 'helplink':
                    $sitenav[] = $navitem;
                    $sitenavexists = true;
                    break;
                case 'sitesettings':
                case 'purgecaches':
                case 'guidreport':
                case 'addblock':
                case 'vleenhancements':
                    $adminnav[] = $navitem;
                    $adminnavexists = true;
                    break;
                case 'mycourses':
                    break;
                case 'allcourses':
                    $coursenav[] = $navitem;
                    $starredCourses[] = $navitem;
                    $coursenavexists = true;
                    break;
                default:
                    $othernav[] = $navitem;
                    $othernavexists = true;
                    break;
            }
            
    }
}
    
?>