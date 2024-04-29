<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Tiny Cloze Editor plugin for Moodle.
 *
 * @package     tiny_cloze
 * @copyright   2023 MoodleDACH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_cloze;

use editor_tiny\plugin;
use editor_tiny\plugin_with_buttons;
use editor_tiny\plugin_with_menuitems;

/**
 * The capabilities of the plugin, in this case there is one toolbar button and one menu item.
 */
class plugininfo extends plugin implements plugin_with_buttons, plugin_with_menuitems {

    /**
     * Get the internal name of the toolbar button.
     * @return string[]
     */
    public static function get_available_buttons(): array {
        return [
            'tiny_cloze',
        ];
    }

    /**
     * Get the internal name of the menu item.
     * @return string[]
     */
    public static function get_available_menuitems(): array {
        return [
            'tiny_cloze',
        ];
    }

}
