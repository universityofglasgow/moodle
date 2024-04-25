<?php
// This file is part of JSXGraph Moodle Filter.
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
 * This is a plugin to enable function plotting and dynamic geometry constructions with JSXGraph within a Moodle platform.
 *
 * JSXGraph is a cross-browser JavaScript library for interactive geometry,
 * function plotting, charting, and data visualization in the web browser.
 * JSXGraph is implemented in pure JavaScript and does not rely on any other
 * library. Special care has been taken to optimize the performance.
 *
 * @package    filter_jsxgraph
 * @copyright  2023 JSXGraph team - Center for Mobile Learning with Digital Technology – Universität Bayreuth
 *             Matthias Ehmann,
 *             Michael Gerhaeuser,
 *             Carsten Miller,
 *             Andreas Walter <andreas.walter@uni-bayreuth.de>,
 *             Alfred Wassermann <alfred.wassermann@uni-bayreuth.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023121801;          // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires = 2019111800;         // Requires this Moodle version.
$plugin->supported = [38, 403];         // Moodle versions.
$plugin->component = 'filter_jsxgraph'; // Full name of the plugin (used for diagnostics).
$plugin->maturity = MATURITY_STABLE;
$plugin->release = 'v1.6.2-patch';      // JSXGraph + filter version.
