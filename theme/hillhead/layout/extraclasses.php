<?php

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

$theme_hillhead_stripstyles = '';

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
    case 'dyslexic':
        $extraclasses[]='hillhead-font-dyslexic';
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
        $theme_hillhead_stripstyles = 'on';
        break;
    case 'by':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-by';
        $theme_hillhead_stripstyles = 'on';
        break;
    case 'yb':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-yb';
        $theme_hillhead_stripstyles = 'on';
        break;
    case 'wg':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-wg';
        $theme_hillhead_stripstyles = 'on';
        break;
    case 'bb':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-bb';
        $theme_hillhead_stripstyles = 'on';
        break;
    case 'br':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-br';
        $theme_hillhead_stripstyles = 'on';
        break;
    case 'bw':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-bw';
        $theme_hillhead_stripstyles = 'on';
        break;
    case 'wb':
        $extraclasses[]='hillhead-contrast';
        $extraclasses[]='hillhead-contrast-wb';
        $theme_hillhead_stripstyles = 'on';
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

if($theme_hillhead_stripstyles != 'on') {
    $theme_hillhead_stripstyles = get_user_preferences('theme_hillhead_stripstyles');
}

switch($theme_hillhead_stripstyles) {
    case 'on':
        $extraScripts .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/theme/hillhead/js/stripstyles.js"></script>';
        $extraclasses[]='hillhead-stripstyles';
        break;
}

?>