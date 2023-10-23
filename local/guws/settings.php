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
 * GUID Enrolment sync
 *
 * @package    local_guws
 * @copyright  2022 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage(
            'local_guws', get_string('pluginname', 'local_guws'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading('guws_alarmbellsettings', '',
            get_string('alarmbellsettings', 'local_guws')));

    $settings->add(new admin_setting_configtextarea(
            'local_guws/allowedevents', get_string('allowedevents', 'local_guws'),
            get_string('configallowedevents', 'local_guws'), '', PARAM_RAW));

}
