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
 * wiforms
 *
 * @package   block
 * @subpackage wiforms
 * @copyright 2013 Howard Miller
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$settings->add(new admin_setting_configtext('block_wiforms_email', get_string('email', 'block_wiforms'),
                   get_string('configemail', 'block_wiforms'), '', PARAM_CLEAN));

$settings->add(new admin_setting_configtext('block_wiforms_subject', get_string('subject', 'block_wiforms'),
                   get_string('configsubject', 'block_wiforms'), get_string('defaultsubject', 'block_wiforms'), PARAM_CLEAN));
