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
 * Shortcodes definitions.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// At present we cannot use the shortcodes_definition_maker interface in the
// dependency injection container, because the shortcodes plugin enforces the
// 'component' the shortcodes belongs to, and thus does not associate the description
// string with 'local_xp', which leads to 'block_xp' lacking the required strings.
// When the filter will allow this form of subclassing, we will update this file.
$shortcodes = [
    'xpdrop' => [
        'callback' => 'local_xp\local\shortcode\handler::xpdrop',
        'description' => 'shortcode:xpdrop'
    ],
    'xpteamladder' => [
        'callback' => 'local_xp\local\shortcode\handler::xpteamladder',
        'description' => 'shortcode:xpteamladder'
    ],
];
