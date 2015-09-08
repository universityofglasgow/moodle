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
 * Group self selection
 *
 * @package    mod
 * @subpackage groupselect
 * @copyright  2008-2012 Petr Skoda (http://skodak.org)
 * @copyright  2014 Tampere University of Technology, P. Pyykkönen (pirkka.pyykkonen ÄT tut.fi)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2015032500; // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2014041100; // Requires this Moodle version
$plugin->cron      = 0;          // Period for cron to check this module (secs)
$plugin->component = 'mod_groupselect'; // Full name of the plugin (used for diagnostics)

$plugin->maturity  = MATURITY_BETA;
$plugin->release   = "3.05 beta"; // User-friendly version number
