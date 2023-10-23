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
 * Upgradecopy form.
 *
 * @package    tool_upgradecopy
 * @copyright  Howard Miller 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_upgradecopy;

defined('MOODLE_INTERNAL') || die();

class process {

    public static function get_paths() {
        $manager = \core_plugin_manager::instance();
        $allplugins = $manager->get_plugins();

        $paths = [];
        foreach ($allplugins as $type => $typeplugins) {
            $standard = \core_plugin_manager::standard_plugins_list($type);
            foreach ($typeplugins as $plugin => $info) {
                if (!$standard || !in_array($plugin, $standard)) {
                    if ($info->rootdir) {
                        $from = $info->rootdir;
                        $to = $info->typerootdir;   
                        $paths[] = (object)[
                            'from' => $from,
                            'to' => $to,   
                        ]; 
                    }
                }
            }
        }

        return $paths;
    }

}