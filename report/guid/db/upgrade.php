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
 * Database enrolment plugin upgrade.
 *
 * @package    enrol
 * @subpackage gudatabase
 * @copyright  2022 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_report_guid_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022060100) {
        report_guid\lib::check_create_userprofile();

        // Gudatabase savepoint reached.
        upgrade_plugin_savepoint(true, 2022060100, 'report', 'guid');
    }

    if ($oldversion < 2022060601) {
        report_guid\lib::check_create_userprofile();

        // Gudatabase savepoint reached.
        upgrade_plugin_savepoint(true, 2022060601, 'report', 'guid');
    }

    return true;
}