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
 * This file contains local methods for the course format Tiles (not included in lib.php as that's widely called)
 * @since     Moodle 2.7
 * @package   format_tiles
 * @copyright 2018 David Watson {@link http://evolutioncode.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Which course modules is the site administrator allowing to be displayed in a modal?
 * @return array the permitted modules including resource types e.g. page, pdf, HTML
 * @throws dml_exception
 */
function format_tiles_allowed_modal_modules() {
    $devicetype = \core_useragent::get_device_type();
    if ($devicetype != \core_useragent::DEVICETYPE_TABLET && $devicetype != \core_useragent::DEVICETYPE_MOBILE
        && !(\core_useragent::is_ie())) {
        // JS navigation and modals in Internet Explorer are not supported by this plugin so we disable modals here.
        return array(
            'resources' => explode(",", get_config('format_tiles', 'modalresources')),
            'modules' => explode(",", get_config('format_tiles', 'modalmodules'))
        );
    } else {
        return array('resources' => [], 'modules' => []);
    }
}

/**
 * If we are not on a mobile device we may want to ensure that tiles are nicely fitted depending on our screen width.
 * E.g. avoid a row with one tile, centre the tiles on screen.  JS will handle this post page load.
 * However we want to handle it pre-page load if we can to avoid tiles moving around once page is loaded.
 * So we have JS send the width via AJAX on first load, and we remember the value and apply it next time using inline CSS.
 * This function gets the data to enable us to add the inline CSS.
 * This will hide the main tiles window on page load and display a loading icon instead.
 * Then post page load, JS will get the screen width, re-arrange the tiles, then hide the loading icon and show the tiles.
 * If session width var has already been set (because JS already ran), we set that width initially.
 * Then we can load the page immediately at that width without hiding anything.
 * The skipcheck URL param is there in case anyone gets stuck at loading icon and clicks it - they escape it for session.
 * @param int $courseid the course ID we are in.
 * @see format_tiles_external::set_session_width() for where the session vars are set from JS.
 * @return array the data to add to our mustache templates.
 * @throws coding_exception
 * @throws dml_exception
 */
function format_tiles_width_template_data($courseid) {
    global $SESSION, $PAGE;
    $data = [];
    if (get_config('format_tiles', 'fittilestowidth')) {
        if (optional_param('skipcheck', 0, PARAM_INT) || isset($SESSION->format_tiles_skip_width_check)) {
            $SESSION->format_tiles_skip_width_check = 1;
            return array('hidetilesinitially' => 0);
        } else if ($PAGE->user_is_editing()
            || !get_config('format_tiles', 'usejavascriptnav')) {
            // Here we may don't tiles initially or restrict screen width.
                return array('hidetilesinitially' => 0);
        } else {
            // If session screen width has been set, send it to template so we can include in inline CSS.
            $sessionvar = 'format_tiles_width_' . $courseid;
            $sessionvarvalue = isset($SESSION->$sessionvar) ? $SESSION->$sessionvar : 0;
            $data['defaultscreenwidthsession'] = $sessionvarvalue;

            // If no session screen width has yet been set, we hide the tiles initally so we can calculate correct width.
            $data['hidetilesinitially'] = $sessionvarvalue == 0 ? 1 : 0;
        }
        return $data;
    } else {
        // Feature is disabled by site admin.
        return array('hidetilesinitially' => 0);
    }
}
