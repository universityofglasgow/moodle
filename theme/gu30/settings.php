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
 * Theme gu30 version file.
 *
 * @package    theme_gu30
 * @copyright  2015 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Twitter
    $settings->add(
        new admin_setting_configtext(
            'theme_gu30/twitter',
            get_string('twitter', 'theme_gu30'),
            get_string('twitterdesc', 'theme_gu30'),
            ''
        )
    );

    // Facebook
    $settings->add(
        new admin_setting_configtext(
            'theme_gu30/facebook',
            get_string('facebook', 'theme_gu30'),
            get_string('facebookdesc', 'theme_gu30'),
            ''
        )
    );

    // Instagram
    $settings->add(
        new admin_setting_configtext(
            'theme_gu30/instagram',
            get_string('instagram', 'theme_gu30'),
            get_string('instagramdesc', 'theme_gu30'),
            ''
        )
    );

    // Breadcrumb replacement strings
    $settings->add(
        new admin_setting_configtextarea(
            'theme_gu30/breadcrumbreplace',
            get_string('breadcrumbreplace', 'theme_gu30'),
            get_string('breadcrumbreplacedesc', 'theme_gu30'),
            ''
        )
    );

    // Login page slogan.
    $settings->add(
        new admin_setting_configtextarea(
            'theme_gu30/loginslogan',
            get_string('loginslogan', 'theme_gu30'),
            get_string('loginslogandesc', 'theme_gu30'),
            ''
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'theme_gu30/logindescription',
            get_string('logindescription', 'theme_gu30'),
            get_string('logindescriptiondesc', 'theme_gu30'),
            ''
        )
    );

    // Custom Less file.
    $name = 'theme_gu30/customless';
    $title = get_string('customless', 'theme_gu30');
    $description = get_string('customlessdesc', 'theme_gu30');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

}
