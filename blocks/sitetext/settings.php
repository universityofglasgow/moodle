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
 * Settings for the Sitetext block
 *
 * @copyright Howard Miller
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_html
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $editor = new admin_setting_confightmleditor(
        'block_sitetext/content',
        get_string('content', 'block_sitetext'),
        get_string('content_help', 'block_sitetext'),
        ''
    );
    $settings->add($editor);
}


