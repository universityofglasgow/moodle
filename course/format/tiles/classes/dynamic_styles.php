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
 * Prepares CSS for Tiles dynamic styles (e.g. course specific colours).
 *
 * @package format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_tiles;

/**
 * Prepares CSS for Tiles dynamic styles (e.g. course specific colours).
 *
 * @package format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dynamic_styles {

    /**
     * Get the tiles dynamic course CSS to be added to <head>.
     * @param int $courseid
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_tiles_dynamic_css(int $courseid): string {
        global $CFG;
        require_once("$CFG->dirroot/course/format/lib.php");
        if (!$courseid) {
            debugging("Missing course ID");
            return '';
        }

        $csscontent = '';

        $format = course_get_format($courseid);
        $course = $courseid ? $format->get_course() : null;
        $basecolour = !$course ? null : self::get_tile_base_colour($course->basecolour ?? '');

        // Will be 1 or 0 for use or not use now.
        // (Legacy values could be 'standard' for not use, or a colour for use, but in that case treat as 'use').
        $shadeheadingbar = $course->courseusebarforheadings != 0 && $course->courseusebarforheadings != 'standard'
            ? 1 : 0;

        $usingtilefitter = self::using_tile_fitter();
        $tilefittermaxwidth = self::get_tile_fitter_max_width($courseid);

        // Course specific colours.
        $data = self::data_for_template($basecolour, $shadeheadingbar, $course->courseusesubtiles ?? false);
        $m = new \Mustache_Engine;
        $csscontent .= $m->render(
            file_get_contents("$CFG->dirroot/course/format/tiles/templates/dynamic_styles.mustache"),
            $data
        );

        // Tile fitter if used.
        if ($usingtilefitter) {
            $csscontent .= self::get_tilefitter_extra_css($courseid, $tilefittermaxwidth);
        }

        // Site admin may have added additional CSS via the plugin settings.
        $csscontent .= trim(get_config('format_tiles', 'customcss') ?? '');

        return $csscontent;
    }

    /**
     * Export the data for the mustache template.
     * @see \format_tiles\util::width_template_data()
     * @param string $basecolourhex The hex code for the base colour used in this course.
     * @param bool $shadeheadingbar Whether the shade heading bar is set to yes for this course.
     * @param bool $usesubtiles Whether the course uses subtiles.
     * @return array
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function data_for_template(string $basecolourhex, bool $shadeheadingbar, bool $usesubtiles) {
        $tilestyle = get_config('format_tiles', 'tilestyle') ?? \format_tiles\output\course_output::TILE_STYLE_STANDARD;
        $basecolourrgb = self::rgbcolour($basecolourhex);
        $outputdata = [
            "isstyle-$tilestyle" => true,
            'isstyle1or2' => $tilestyle == 1 || $tilestyle == 2,
            'base_colour_rgb' => $basecolourrgb,
            'usesubtiles' => $usesubtiles,
        ];

        if (get_config('format_tiles', 'allowphototiles')) {
            $outputdata['allowphototiles'] = 1;
            $outputdata['photo_tile_text_bg_opacity'] =
                1.0 - (float)get_config('format_tiles', 'phototiletitletransarency');

            // The best values here vary by theme and browser, so mostly come from admin setting.
            // If the site admin sets background opacity to solid then it doesn't matter if the lines overlap.
            $outputdata['phototilefontsize'] = 20;
            $outputdata['phototiletextpadding'] = number_format(
                (float)get_config('format_tiles', 'phototitletitlepadding') / 10, 1
            );
            $outputdata['phototiletextlineheight'] = number_format(
                (float)get_config('format_tiles', 'phototitletitlelineheight') / 10, 1
            );
        }
        $outputdata['shade_heading_bar'] = $shadeheadingbar;
        $outputdata['ismoodle42minus'] = \format_tiles\util::get_moodle_release() <= 4.2;

        return $outputdata;
    }

    /**
     * Convert hex colour from plugin settings admin page to RGB
     * so that can add transparency to it when used as background
     * @param string $hex the colour in hex form e.g. #979797
     * @return string rgb colour
     */
    private static function rgbcolour(string $hex) {
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        return "$r,$g,$b";
    }

    /**
     * Get the colour which should be used as the base course for this course
     * (Can depend on theme, plugin and/or course settings).
     * @param string $coursebasecolour the course base colour which we may use unless this overrides it.
     * @return string the hex colour
     * @throws \dml_exception
     */
    public static function get_tile_base_colour($coursebasecolour): string {
        global $PAGE;
        $result = null;

        $hexpattern = '/^#(?:[0-9a-fA-F]{3}){1,2}$/';

        if (!(get_config('format_tiles', 'followthemecolour'))) {
            if (!$coursebasecolour) {
                // If no course tile colour is set, use plugin default colour.
                $result = get_config('format_tiles', 'tilecolour1');
            } else {
                $result = $coursebasecolour;
            }
        } else {
            // We are following theme's main colour so find out what it is.
            // Many themes including boost theme and Moove use "brandcolor" so try to get that if current theme has it.
            $result = get_config('theme_' . $PAGE->theme->name, 'brandcolor');
            if (!$result) {
                // If not got a colour yet, look where essential theme stores its brand color and try that.
                $result = get_config('theme_' . $PAGE->theme->name, 'themecolor');
            }
        }

        if (!$result || !preg_match($hexpattern, $result)) {
            // If still no colour set, use a default colour.
            $result = get_config('format_tiles', 'tilecolour1') ?? '#1670CC';
        }
        return $result;
    }


    /**
     * If we are not on a mobile device we may want to ensure that tiles are nicely fitted depending on our screen width.
     * E.g. avoid a row with one tile, centre the tiles on screen.  JS will handle this post page load.
     * However, we want to handle it pre-page load if we can to avoid tiles moving around once page is loaded.
     * So we have JS send the width via AJAX on first load, and we remember the value and apply it next time using inline CSS.
     * This function gets the data to enable us to add the inline CSS.
     * This will hide the main tiles window on page load and display a loading icon instead.
     * Then post page load, JS will get the screen width, re-arrange the tiles, then hide the loading icon and show the tiles.
     * If session width var has already been set (because JS already ran), we set that width initially.
     * Then we can load the page immediately at that width without hiding anything.
     * The skipcheck URL param is there in case anyone gets stuck at loading icon and clicks it - they escape it for session.
     * @param int $courseid the course ID we are in.
     * @param int $maxwidth the max width for tiles if set.
     * @see format_tiles_external::set_session_width() for where the session vars are set from JS.
     * @return string the styles to print.
     */
    public static function get_tilefitter_extra_css(int $courseid, int $maxwidth): string {
        if ($maxwidth == 0) {
            // If no session screen width has yet been set, we hide the tiles initially, so we can calculate correct width in JS.
            // We will remove this opacity later in JS.
            return ".format-tiles.course-$courseid.jsenabled:not(.editing) ul.tiles {opacity: 0;}";
        } else {
            return ".format-tiles.course-$courseid.jsenabled ul.tiles {max-width: {$maxwidth}px;}";
        }
    }

    /**
     * Does the course main page need to show the loading icon while correct width is calculated?
     * @param int $courseid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function page_needs_loading_icon(int $courseid): bool {
        if (!self::using_tile_fitter()) {
            return false;
        }
        return !self::get_tile_fitter_max_width($courseid);
    }

    /**
     * If tile fitter has already set a max width for page, what is it?
     * @param int $courseid
     * @return int
     */
    public static function get_tile_fitter_max_width(int $courseid): int {
        global $SESSION;
        if (!$courseid) {
            return 0;
        }
        $var = 'format_tiles_width_' . $courseid;
        return $SESSION->$var ?? 0;
    }

    /**
     * Is the current user using tile fitter?
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function using_tile_fitter(): bool {
        global $SESSION;

        if (optional_param('skipcheck', 0, PARAM_INT)) {
            // The skipcheck param is for anyone stuck at loading icon who clicks it - they escape it for session.
            $SESSION->format_tiles_skip_width_check = 1;
            return false;
        }

        return \format_tiles\util::using_js_nav()
            && get_config('format_tiles', 'fittilestowidth')
            && \core_useragent::get_device_type() != \core_useragent::DEVICETYPE_MOBILE
            && ($SESSION->format_tiles_skip_width_check ?? null) != 1;
    }
}
