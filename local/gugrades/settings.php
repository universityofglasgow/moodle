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
 * Admin settings for local_gugrades
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_gugrades_settings', new lang_string('pluginname', 'local_gugrades')));
    $settingspage = new admin_settingpage('managelocalgugrades', new lang_string('manage', 'local_gugrades'));

    if ($ADMIN->fulltree) {
        $settingspage->add(new admin_setting_heading('local_gugrades/headingscales', new lang_string('scalevalues', 'local_gugrades'), new lang_string('scalevaluesinfo', 'local_gugrades')));

        // Get current site-wide settings
        $scales = $DB->get_records('scale', ['courseid' => 0]);
        foreach ($scales as $scale) {
            $name = "local_gugrades/scalevalue_" . $scale->id;
            $items = explode(',', $scale->scale);
            $default = '';
            foreach ($items as $item) {
                $default .= $item . ' 0' . PHP_EOL;
            }
            $settingspage->add(new admin_setting_configtextarea($name, $scale->name, new lang_string('scalevalueshelp', 'local_gugrades'), $default, PARAM_RAW, 30, count($items)+1));
        }
    }

    $ADMIN->add('localplugins', $settingspage);
}