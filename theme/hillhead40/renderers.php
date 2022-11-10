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
 * Brief Description
 *
 * More indepth description.
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
            $usesAccessibilityTools=get_user_preferences('theme_hillhead40_accessibility', false);
            $vArg = 'clear';
            $spanText = 'Hide';
            if($usesAccessibilityTools === false) {
                $vArg = 'on';
                $spanText = 'Show';
            }
            $branchlabel = $spanText . ' Accessibility Tools';
            $branchurl   = new moodle_url($CFG->wwwroot.'/theme/hillhead40/accessibility.php?o=theme_hillhead_accessibility&v=' . $vArg);
            $branchtitle = $branchlabel;
            $branchsort  = 10000;
            //$accessibilityButton = '<a href="'.$CFG->wwwroot.'/theme/hillhead40/accessibility.php?o=theme_hillhead_accessibility&v=' . $vArg . '"><span class="media-left"><i class="fa fa-universal-access"></i></span><span class="media-body">' . $spanText . ' Accessibility Tools</span></a>';

            $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
        }

        return parent::render_custom_menu($menu);
    }

    /**
     * @return bool
     */
    public function firstview_fakeblocks() {}

}