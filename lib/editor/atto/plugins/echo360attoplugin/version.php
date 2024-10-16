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
 * Atto echo360attoplugin version file.
 *
 * @package    atto_echo360attoplugin
 * @copyright  2020 Echo360 Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020081801;                    // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2017010100;                    // Requires this Moodle version.
$plugin->component = 'atto_echo360attoplugin';      // Full name of the plugin (used for diagnostics).
$plugin->maturity  = MATURITY_STABLE;               // Human readable version information.
$plugin->release   = '1.0.25 (Build 2020081801)';   // Update value on ajax.php as well.
$plugin->dependencies = array(
    'filter_echo360' => 2020060601,                 // The Echo360 filter plugin must be present.
);
