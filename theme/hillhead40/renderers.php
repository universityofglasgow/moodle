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
 * Renderer for UofG Hillhead 4.0 theme features
 *
 * @package
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_hillhead40_core_renderer extends core_renderer {

    /**
     * @param custom_menu $menu
     * @return mixed
     */
    protected function render_custom_menu(custom_menu $menu) {
        if (isloggedin()) {
            $usesaccessibilitytools = get_user_preferences('theme_hillhead40_accessibility', false);
            $varg = 'clear';
            $spantext = 'Hide';
            if ($usesaccessibilitytools === false) {
                $varg = 'on';
                $spantext = 'Show';
            }
            $branchlabel = $spantext . ' Accessibility Tools';
            $script = '/theme/hillhead40/accessibility.php';
            $args = '?o=theme_hillhead_accessibility&v=' . $varg;
            $branchurl = new moodle_url($CFG->wwwroot . $script . $args);
            $branchtitle = $branchlabel;
            $branchsort  = 10000;
            $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
        }

        return parent::render_custom_menu($menu);
    }

    /**
     * @return bool
     */
    public function firstview_fakeblocks() {
    }

}
