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

namespace format_tiles\local\hooks\output;

/**
 * Allows plugins to add any elements to the page <head> html tag
 *
 * @package   format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class standard_head_html_prepend {

    /**
     * Callback to add head elements.  Used to add dynamic CSS used by Tiles format.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     */
    public static function callback(\core\hook\output\before_standard_head_html_generation $hook): void {
        global $PAGE;
        try {
            $courseid = optional_param('id', 0, PARAM_INT);

            $istilescoursefrontpage = $PAGE->pagetype == 'course-view-tiles' && $courseid
                && $PAGE->url->compare(new \moodle_url('/course/view.php'), URL_MATCH_BASE);
            if (!$istilescoursefrontpage || !$courseid) {
                // We have to be careful in this function as it's called on every page (not just tiles course pages).
                return;
            }
            $dynamiccss = \format_tiles\dynamic_styles::get_tiles_dynamic_css($courseid);
            if ($dynamiccss) {
                $hook->add_html("<style id=\"format-tiles-dynamic-css\">$dynamiccss</style>");
            }
        } catch (\Exception $e) {
            debugging("Could not prepare format_tiles head data: " . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
