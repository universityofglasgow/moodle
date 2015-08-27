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
 * Theme gu28 lib.
 *
 * @package    theme_gu28
 * @copyright  2015 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/filelib.php');

/**
 * Return any extra less rules to be added
 * @param stdClass $theme
 * @return string
 */
function theme_gu28_extra_less($theme) {
    if (!empty($theme->settings->customless)) {
        $customless = $theme->settings->customless;
    } else {
        $customless = null;
    }
    return $customless;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_gu28_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'logo')) {
        $theme = theme_config::load('gu28');
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

function theme_gu28_bootstrap3_grid() {

    $regions = array('content' => 'col-sm-8 col-md-9');
    $regions['pre'] = 'empty';
    $regions['post'] = 'col-sm-4 col-md-3';

    return $regions;
}

/**
 * Get the crappy login page images
 */
function theme_gu28_crap_image($theme) {
    global $CFG;

    $files = glob($CFG->dirroot . '/theme/gu28/pix/gucrap/*.*');
    $images = array();
    foreach ($files as $file) {
        $images[] = pathinfo($file);
    }
    shuffle($images);

    return 'gucrap/' . $images[0]['filename'];
}

/**
 * Get the slogan to go with the crappy image
 */
function theme_gu28_crap_slogan($theme) {
    $loginslogan = empty($theme->settings->loginslogan) ? '' : $theme->settings->loginslogan; 
    $sloganhighlight = empty($theme->settings->sloganhighlight) ? '' : $theme->settings->sloganhighlight;
    if (!$loginslogan) {
        return '';
    }
 
    // split up by new lines
    $slogans = explode(PHP_EOL, $loginslogan); 

    // pick random slogan
    shuffle($slogans);
    $slogan = $slogans[0];
 
    // add bold tags and split each word onto new line
    $slogan = str_replace($sloganhighlight, '<b>'.$sloganhighlight.'</b>', $slogan);
    $slogan = preg_replace('/\s+/', '<br />', $slogan);

    return $slogan;
}

