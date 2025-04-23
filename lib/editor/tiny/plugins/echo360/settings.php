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
 * Tiny echo360 settings file.
 *
 * @package     tiny_echo360
 * @copyright   2023 Echo360 Inc.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editortiny', new admin_category('tiny_echo360', new lang_string('pluginname', 'tiny_echo360')));

$settings = new admin_settingpage('tiny_echo360_settings', new lang_string('settings', 'tiny_echo360'));
if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_configtext(
            'tiny_echo360/consumerkey',
            get_string('consumerkey', 'tiny_echo360'), 'The Public Key provided by Echo360', '', PARAM_TEXT
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'tiny_echo360/sharedsecret',
            get_string('sharedsecret', 'tiny_echo360'), 'The Secret Key provided by Echo360', '', PARAM_TEXT
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'tiny_echo360/hosturl',
            get_string('hosturl', 'tiny_echo360'), 'The Host URL provided by Echo360', '', PARAM_TEXT
        )
    );
}
