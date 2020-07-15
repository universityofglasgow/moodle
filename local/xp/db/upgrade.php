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
 * Local XP upgrade.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Local XP upgrade function.
 *
 * @param int $oldversion Old version.
 * @return true
 */
function xmldb_local_xp_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    // Our first upgrade steps are responsible for creating the database tables.
    // It is possible that users installed the 'public' placeholder version of
    // the plugin first, and therefore we must create the tables here. Changes
    // to tables will be added as normal database steps.

    if ($oldversion < 2017080101) {

        // Define table local_xp_log to be created.
        $table = new xmldb_table('local_xp_log');

        // Adding fields to table local_xp_log.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('signature', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('points', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('hashkey', XMLDB_TYPE_CHAR, '40', null, null, null, null);

        // Adding keys to table local_xp_log.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_xp_log.
        $table->add_index('ctxuser', XMLDB_INDEX_NOTUNIQUE, array('contextid', 'userid'));
        $table->add_index('ctxusertime', XMLDB_INDEX_NOTUNIQUE, array('contextid', 'userid', 'time'));
        $table->add_index('ctxuserpts', XMLDB_INDEX_NOTUNIQUE, array('contextid', 'userid', 'points'));
        $table->add_index('ctxuserhash', XMLDB_INDEX_NOTUNIQUE, array('contextid', 'userid', 'hashkey'));

        // Conditionally launch create table for local_xp_log.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Xp savepoint reached.
        upgrade_plugin_savepoint(true, 2017080101, 'local', 'xp');
    }

    if ($oldversion < 2017080102) {

        // Define table local_xp_config to be created.
        $table = new xmldb_table('local_xp_config');

        // Adding fields to table local_xp_config.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('maxpointspertime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeformaxpoints', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('currencystate', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('badgetheme', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_xp_config.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_xp_config.
        $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));

        // Conditionally launch create table for local_xp_config.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Xp savepoint reached.
        upgrade_plugin_savepoint(true, 2017080102, 'local', 'xp');
    }

    if ($oldversion < 2017080103) {

        // Define table local_xp_theme to be created.
        $table = new xmldb_table('local_xp_theme');

        // Adding fields to table local_xp_theme.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('levels', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_xp_theme.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_xp_theme.
        $table->add_index('code', XMLDB_INDEX_UNIQUE, array('code'));

        // Conditionally launch create table for local_xp_theme.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Xp savepoint reached.
        upgrade_plugin_savepoint(true, 2017080103, 'local', 'xp');
    }

    if ($oldversion < 2017080104) {

        // We unset the length for which logs are kept, to force the admin to set it again.
        // The local plugin needs logs to be kept for a longer time.
        unset_config('keeplogs', 'block_xp');

        // Xp savepoint reached.
        upgrade_plugin_savepoint(true, 2017080104, 'local', 'xp');
    }

    if ($oldversion < 2018092501) {

        // Define field enablegroupladder to be added to local_xp_config.
        $table = new xmldb_table('local_xp_config');
        $field = new xmldb_field('enablegroupladder', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'badgetheme');

        // Conditionally launch add field enablegroupladder.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Xp savepoint reached.
        upgrade_plugin_savepoint(true, 2018092501, 'local', 'xp');
    }

    if ($oldversion < 2019020300) {

        // Define field progressbarmode to be added to local_xp_config.
        $table = new xmldb_table('local_xp_config');
        $field = new xmldb_field('progressbarmode', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'enablegroupladder');

        // Conditionally launch add field progressbarmode.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Xp savepoint reached.
        upgrade_plugin_savepoint(true, 2019020300, 'local', 'xp');
    }

    if ($oldversion < 2019043000) {

        // Define field groupidentitymode to be added to local_xp_config.
        $table = new xmldb_table('local_xp_config');
        $field = new xmldb_field('groupidentitymode', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'enablegroupladder');

        // Conditionally launch add field groupidentitymode.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Xp savepoint reached.
        upgrade_plugin_savepoint(true, 2019043000, 'local', 'xp');
    }

    if ($oldversion < 2019061000) {

        // Define field groupladdercols to be added to local_xp_config.
        $table = new xmldb_table('local_xp_config');
        $field = new xmldb_field('groupladdercols', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'xp', 'progressbarmode');

        // Conditionally launch add field groupladdercols.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Xp savepoint reached.
        upgrade_plugin_savepoint(true, 2019061000, 'local', 'xp');
    }

    if ($oldversion < 2019061001) {

        // Define field grouporderby to be added to local_xp_config.
        $table = new xmldb_table('local_xp_config');
        $field = new xmldb_field('grouporderby', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'groupladdercols');

        // Conditionally launch add field grouporderby.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Xp savepoint reached.
        upgrade_plugin_savepoint(true, 2019061001, 'local', 'xp');
    }

    // Force the themes upgrade.
    try {
        $themeupdater = \block_xp\di::get('theme_updater');
        $themeupdater->update_themes();
    } catch (Exception $e) {
        debugging('Exception caught during call to local_xp::theme_updater.');
    }

    // Lastly, force update of block_xp, because it won't update automatically.
    \core\task\manager::reset_scheduled_tasks_for_component('block_xp');

    return true;
}
