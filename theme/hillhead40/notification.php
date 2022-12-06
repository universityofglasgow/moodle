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
 * Controls how the Systemwide Notifications are handled.
 *
 * These notifications are configured in admin/settings.php as part of the theme
 * and display across all Moodle pages, unless dismissed by the user.
 *
 * @package    theme_hillhead40
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$notificationhash = required_param('h', PARAM_RAW);

require_login();

if (!isset($_SESSION['SESSION']->hillhead_notifications)) {
    $_SESSION['SESSION']->hillhead_notifications = [];
}
$_SESSION['SESSION']->hillhead_notifications[$notificationhash] = 1;

header('Location: '.$_SERVER['HTTP_REFERER']);
