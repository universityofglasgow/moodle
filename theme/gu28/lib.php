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

define('INSTAGRAM_API', 'https://api.instagram.com/v1/');

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
        $theme = theme_config::load('cerulean');
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
 * Populate array (and cache) with Instagram links
 */
function theme_gu28_populate_instagram($theme) {

    // Settings
    $instagramuser = $theme->settings->instagramuser;
    $instagramclientid = $theme->settings->instagramclientid; 

    // Get user id
    $c = new curl(array('proxy' => true));
    if (!$struserdata = $c->get(INSTAGRAM_API . "/users/search?q=$instagramuser&client_id=$instagramclientid")) {
        return false;
    }

    // Probably returned more than one match
    $userdata = json_decode($struserdata);
    $users = $userdata->data;
    $userid = null;
    foreach ($users as $user) {
        if ($user->username == $instagramuser) {
            $userid = $user->id;
        }
    }
    if (!$userid) {
        return false;
    }

    // Now have enough to get some links
    $nexturl = INSTAGRAM_API . "/users/$userid/media/recent?client_id=$instagramclientid";
    $images = array();
    do {
        if (!$strmedia = $c->get($nexturl)) {
            return $images;
        }
        $mediadata = json_decode($strmedia);
        $items = $mediadata->data;
        if (isset($mediadata->pagination->next_url)) {
            $nexturl = $mediadata->pagination->next_url;
        } else {
            $nexturl = null;
        }
        foreach ($items as $item) {
            $type = $item->type;
            if ($type != 'image') {
                continue;
            }
            $url = $item->images->standard_resolution->url;
            $images[] = $url;
        }
    } while ($nexturl);

    return $images;
}

/**
 * Get the instagram links
 */
function theme_gu28_instagram_images($theme) {

    // Image links stored in application cache
    $cache = cache::make('theme_gu28', 'instagram');

    // Images are just in a single array with key 1
    if (!$images = $cache->get(1)) {
        $images = theme_gu28_populate_instagram($theme);
        if (!empty($images)) {
            $cache->set(1, $images);
        }
    }

    // Did we get anything
    if (empty($images)) {
        return '';
    }
    if (count($images) < 7) {
        return '';
    }

    // Randomise the images (only return first few).
    shuffle($images);

    return array_slice($images, 0, 7);
}
