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
 * Echo360 Tiny filter plugin version file.
 *
 * @package    filter_echo360tiny
 * @copyright  2023 Echo360 Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2023071802;                    // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2022112804.00;                 // Requires this Moodle version.
$plugin->component = 'filter_echo360tiny';          // Full name of the plugin (used for diagnostics).
$plugin->maturity  = MATURITY_STABLE;               // Human readable version information.
$plugin->release   = '1.0.0 (Build ' . $plugin->version . ')';
$plugin->dependencies = array(
    'tiny_echo360' => 2023071802,                   // The Echo360 tiny plugin must be present.
);
