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
 * Echo360 filter plugin version file.
 *
 * @package local_echo360
 * @author  Echo360
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020011401;                    // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2017010100;                    // Requires this Moodle version.
$plugin->component = 'filter_echo360';              // Full name of the plugin (used for diagnostics).
$plugin->maturity  = MATURITY_STABLE;               // Human readable version information.
$plugin->release   = '1.0.5 (Build 2020011401)';
$plugin->dependencies = array(
    'atto_echo360attoplugin' => 2019112901,         // The Echo360 atto plugin must be present.
);
