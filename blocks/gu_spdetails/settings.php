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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Adds admin settings for the plugin.
 *
 * @package     block_gu_spdetails
 * @category    admin
 * @copyright   2021 Howard Miller
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_gu_spdetails/messagedatapage', get_string('messagedatapage', 'block_gu_spdetails'),
                       get_string('configmessagedatapage', 'block_gu_spdetails'), ''));

    $settings->add(new admin_setting_configtext('block_gu_spdetails/messagenodata', get_string('messagenodata', 'block_gu_spdetails'),
                       get_string('configmessagenodata', 'block_gu_spdetails'), get_string('nodetails', 'block_gu_spdetails')));
}