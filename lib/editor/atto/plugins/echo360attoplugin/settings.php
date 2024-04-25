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
 * Atto echo360attoplugin settings file.
 *
 * @package   atto_echo360attoplugin
 * @copyright 2020 Echo360 Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_echo360attoplugin', new lang_string('pluginname', 'atto_echo360attoplugin')));

$settings = new admin_settingpage('atto_echo360attoplugin_settings', new lang_string('settings', 'atto_echo360attoplugin'));
if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_configtext(
            'atto_echo360attoplugin/consumerkey',
            get_string('consumerkey', 'atto_echo360attoplugin'), 'The Public Key provided by Echo360', '', PARAM_TEXT
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'atto_echo360attoplugin/sharedsecret',
            get_string('sharedsecret', 'atto_echo360attoplugin'), 'The Secret Key provided by Echo360', '', PARAM_TEXT
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'atto_echo360attoplugin/hosturl',
            get_string('hosturl', 'atto_echo360attoplugin'), 'The Host URL provided by Echo360', '', PARAM_TEXT
        )
    );
}
