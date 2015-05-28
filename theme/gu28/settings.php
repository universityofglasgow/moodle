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
 * Theme gu28 version file.
 *
 * @package    theme_gu28
 * @copyright  2015 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Twitter
    $settings->add(
        new admin_setting_configtext(
            'theme_gu28/twitter',
            get_string('twitter', 'theme_gu28'),
            get_string('twitterdesc', 'theme_gu28'),
            ''
        )
    );

    // Facebook
    $settings->add(
        new admin_setting_configtext(
            'theme_gu28/facebook',
            get_string('facebook', 'theme_gu28'),
            get_string('facebookdesc', 'theme_gu28'),
            ''
        )
    );

    // Instagram
    $settings->add(
        new admin_setting_configtext(
            'theme_gu28/instagram',
            get_string('instagram', 'theme_gu28'),
            get_string('instagramdesc', 'theme_gu28'),
            ''
        )
    );

    // Breadcrumb replacement strings
    $settings->add(
        new admin_setting_configtextarea(
            'theme_gu28/breadcrumbreplace',
            get_string('breadcrumbreplace', 'theme_gu28'),
            get_string('breadcrumbreplacedesc', 'theme_gu28'),
            ''
        )
    );

    // Custom Less file.
    $name = 'theme_gu28/customless';
    $title = get_string('customless', 'theme_gu28');
    $description = get_string('customlessdesc', 'theme_gu28');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

}
