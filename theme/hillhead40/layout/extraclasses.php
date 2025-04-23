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
 * Additional accessibility classes.
 *
 * The size and fonts get applied to the page based on which one is selected.
 *
 * @package   theme_hillhead40
 * @copyright 2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$extrascripts = '';
$themehillhead40stripstyles = '';
$themehillhead40font = get_user_preferences('theme_hillhead40_font');

switch($themehillhead40font) {
    case 'modern':
        $extraclasses[] = 'hillhead40-font-modern';
        break;
    case 'classic':
        $extraclasses[] = 'hillhead40-font-classic';
        break;
    case 'comic':
        $extraclasses[] = 'hillhead40-font-comic';
        break;
    case 'mono':
        $extraclasses[] = 'hillhead40-font-mono';
        break;
    case 'dyslexic':
        $extraclasses[] = 'hillhead40-font-dyslexic';
        break;
}

$themehillhead40size = get_user_preferences('theme_hillhead40_size');

switch($themehillhead40size) {
    case '120':
        $extraclasses[] = 'hillhead40-size-120';
        break;
    case '140':
        $extraclasses[] = 'hillhead40-size-140';
        break;
    case '160':
        $extraclasses[] = 'hillhead40-size-160';
        break;
    case '180':
        $extraclasses[] = 'hillhead40-size-180';
        break;
}

$themehillhead40contrast = get_user_preferences('theme_hillhead40_contrast');

switch($themehillhead40contrast) {
    case 'night':
        $extraclasses[] = 'hillhead40-night';
        break;
    case 'by':
        $extraclasses[] = 'hillhead40-contrast';
        $extraclasses[] = 'hillhead40-contrast-by';
        break;
    case 'yb':
        $extraclasses[] = 'hillhead40-contrast';
        $extraclasses[] = 'hillhead40-contrast-yb';
        break;
    case 'wg':
        $extraclasses[] = 'hillhead40-contrast';
        $extraclasses[] = 'hillhead40-contrast-wg';
        break;
    case 'bb':
        $extraclasses[] = 'hillhead40-contrast';
        $extraclasses[] = 'hillhead40-contrast-bb';
        break;
    case 'br':
        $extraclasses[] = 'hillhead40-contrast';
        $extraclasses[] = 'hillhead40-contrast-br';
        break;
    case 'bw':
        $extraclasses[] = 'hillhead40-contrast';
        $extraclasses[] = 'hillhead40-contrast-bw';
        break;
    case 'wb':
        $extraclasses[] = 'hillhead40-contrast';
        $extraclasses[] = 'hillhead40-contrast-wb';
        break;
}

$themehillhead40bold = get_user_preferences('theme_hillhead40_bold');

switch($themehillhead40bold) {
    case 'on':
        $extraclasses[] = 'hillhead40-bold';
        break;
}

$themehillhead40spacing = get_user_preferences('theme_hillhead40_spacing');

switch($themehillhead40spacing) {
    case 'on':
        $extraclasses[] = 'hillhead40-spacing';
        break;
}

$themehillhead40readhighlight = get_user_preferences('theme_hillhead40_readtome');

switch($themehillhead40readhighlight) {
    case 'on':
        $extrascripts .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/theme/hillhead40/js/readtome.js"></script>';
        break;
}

$themehillhead40readalert = get_user_preferences('theme_hillhead40_readalert');

switch ($themehillhead40readalert) {
    case 'on':
        $extraclasses[] = 'hillhead40-readalert';
        break;
}

if ($themehillhead40stripstyles != 'on') {
    $themehillhead40stripstyles = get_user_preferences('theme_hillhead40_stripstyles');
}

switch ($themehillhead40stripstyles) {
    case 'on':
        $extrascripts .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/theme/hillhead40/js/stripstyles.js"></script>';
        $extraclasses[] = 'hillhead40-stripstyles';
        break;
}
