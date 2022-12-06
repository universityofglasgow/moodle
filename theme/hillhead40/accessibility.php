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
 * Sets or unsets an accessible theme based on System Admin settings.
 *
 * Accepts a theme style and colour scheme to apply before redirecting.
 *
 * @package    theme_hillhead40
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$setting = required_param('o', PARAM_RAW);
$value = required_param('v', PARAM_RAW);

require_login();

$allowedpreferences = [
    'theme_hillhead40_accessibility'  => true,
    'theme_hillhead40_contrast'       => true,
    'theme_hillhead40_font'           => true,
    'theme_hillhead40_bold'           => true,
    'theme_hillhead40_spacing'        => true,
    'theme_hillhead40_stripstyles'    => true,
    'theme_hillhead40_size'           => true,
    'theme_hillhead40_readtome'       => true,
    'theme_hillhead40_readalert'      => true
];

if (array_key_exists($setting, $allowedpreferences)) {
    if ($value == 'clear') {
        unset_user_preference($setting);
    } else {
        set_user_preference($setting, $value);
    }
} else {
    if ($setting == 'theme_hillhead40_reset_accessibility') {
        foreach ($allowedpreferences as $unset => $pointlesstrue) {
            if ($unset != 'theme_hillhead40_accessibility') {
                unset_user_preference($unset);
            }
        }
    }
}

header('Location: '.$_SERVER['HTTP_REFERER']);
