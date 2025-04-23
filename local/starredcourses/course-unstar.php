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
 * Unstar course file.
 *
 * This file accepts a course id and removes it from core_favourites.
 *
 * @package    local_starredcourses
 * @copyright  2023 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$value = required_param('id', PARAM_RAW);

require_login();

$usercontext = context_user::instance($USER->id);
$ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

$ufservice->delete_favourite('core_course', 'courses', $value, \context_course::instance($value));

header('Location: '.$_SERVER['HTTP_REFERER']);
