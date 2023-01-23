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
 * Add navigation links
 *
 * Add the Manage Optional Plugins link under Development->Experimental.
 * Add the link to the report under Reports
 *
 * @package tool_optionalplugins
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $pluginname = get_string('pagetitle', 'tool_optionalplugins');
    $reportnname = get_string('reportname', 'tool_optionalplugins');

    $ADMIN->add('experimental', new admin_externalpage('tooloptionalplugins',
        $pluginname, "$CFG->wwwroot/$CFG->admin/tool/optionalplugins/index.php", 'moodle/site:config'));

    $ADMIN->add('reports', new admin_externalpage('reportoptionalplugins',
        $reportnname, "$CFG->wwwroot/$CFG->admin/tool/optionalplugins/pluginreport.php", 'moodle/site:config'));
}
