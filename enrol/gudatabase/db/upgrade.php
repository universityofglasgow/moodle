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
 * @copyright  2012 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_gudatabase_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Create new table for groups.
    if ($oldversion < 2014080604) {

        // Define table enrol_gudatabase_groups to be created.
        $table = new xmldb_table('enrol_gudatabase_groups');

        // Adding fields to table enrol_gudatabase_groups.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('originalname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table enrol_gudatabase_groups.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table enrol_gudatabase_groups.
        $table->add_index('enrol_gudatabase_coursename_idx', XMLDB_INDEX_UNIQUE, array('originalname', 'courseid'));

        // Conditionally launch create table for enrol_gudatabase_groups.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gudatabase savepoint reached.
        upgrade_plugin_savepoint(true, 2014080604, 'enrol', 'gudatabase');
    }

    // Add a new key to enrol_gudatabase_users.
    if ($oldversion < 2016092200) {

        // Define key enrol_gudatabase_users_cu (primary) to be added to enrol_gudatabase_users.
        $table = new xmldb_table('enrol_gudatabase_users');
        $index = new xmldb_index('enrol_gudatabase_users_cu', XMLDB_INDEX_NOTUNIQUE, array('userid', 'courseid'));

        // Conditionally launch add index enrol_gudatabase_users_cu.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Gudatabase savepoint reached.
        upgrade_plugin_savepoint(true, 2016092200, 'enrol', 'gudatabase');
    }

    // Add fields for code location and plugin instanceid
    if ($oldversion < 2018070500) {

        // Define field location to be added to enrol_gudatabase_codes.
        $table = new xmldb_table('enrol_gudatabase_codes');
        $field = new xmldb_field('location', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, null, 'subjectnumber');

        // Conditionally launch add field location.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field instanceid to be added to enrol_gudatabase_codes.
        $field = new xmldb_field('instanceid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'location');

        // Conditionally launch add field instanceid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field instanceid to be added to enrol_gudatabase_groups.
        $table = new xmldb_table('enrol_gudatabase_groups');
        $field = new xmldb_field('instanceid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'courseid');

        // Conditionally launch add field instanceid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Gudatabase savepoint reached.
        upgrade_plugin_savepoint(true, 2018070500, 'enrol', 'gudatabase');
    }

    return true;
}
