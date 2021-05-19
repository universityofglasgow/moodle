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
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

/**
 * This file keeps track of upgrades to the game module
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrades database
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_local_gugcat_upgrade($oldversion) {

    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021041600) {
        // ------- Table gcat_converter_templates starts here --------

        // Define table gcat_converter_templates to be created.
        $table_tpl = new xmldb_table('gcat_converter_templates');

        // Define fields to table gcat_converter_templates.
        $table_tpl_id = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_tpl_templatename = new xmldb_field('templatename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table_tpl_userid = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_tpl_scaletype = new xmldb_field('scaletype', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Define keys to table gcat_converter_templates.
        $table_tpl_key = new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding fields to table gcat_converter_templates.
        $table_tpl->addField($table_tpl_id);
        $table_tpl->addField($table_tpl_templatename);
        $table_tpl->addField($table_tpl_userid);
        $table_tpl->addField($table_tpl_scaletype);

        // Adding keys to table gcat_converter_templates.
        $table_tpl->addKey($table_tpl_key);

        // Conditionally launch create table for gcat_converter_templates.
        if (!$dbman->table_exists($table_tpl)) {
            $dbman->create_table($table_tpl);
        }

        // ------- Table gcat_grade_converter starts here --------

        // Define table gcat_grade_converter to be created.
        $table_cvt = new xmldb_table('gcat_grade_converter');

        // Define fields to table gcat_grade_converter.
        $table_cvt_id = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_cvt_courseid = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table_cvt_itemid = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table_cvt_templateid = new xmldb_field('templateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table_cvt_lowerboundary = new xmldb_field('lowerboundary', XMLDB_TYPE_NUMBER, '10,5', null, XMLDB_NOTNULL, null, '0');
        $table_cvt_grade = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Define keys to table gcat_grade_converter.
        $table_cvt_key1 = new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table_cvt_key2 = new xmldb_key('gcat_grade_converter_fk', XMLDB_KEY_FOREIGN,  array('templateid'), 'gcat_converter_templates', array('id'));

        // Define indexes to table gcat_grade_converter.
        $table_cvt_index1 = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        $table_cvt_index2 = new xmldb_index('itemid', XMLDB_INDEX_NOTUNIQUE, array('itemid'));

        // Adding fields to table gcat_grade_converter.
        $table_cvt->addField($table_cvt_id);
        $table_cvt->addField($table_cvt_courseid);
        $table_cvt->addField($table_cvt_itemid);
        $table_cvt->addField($table_cvt_templateid);
        $table_cvt->addField($table_cvt_lowerboundary);
        $table_cvt->addField($table_cvt_grade);

        // Adding keys to table gcat_grade_converter.
        $table_cvt->addKey($table_cvt_key1);
        $table_cvt->addKey($table_cvt_key2);

        // Adding indexes to table gcat_grade_converter.
        $table_cvt->addIndex($table_cvt_index1);
        $table_cvt->addIndex($table_cvt_index2);

        // Conditionally launch create table for gcat_grade_converter.
        if(!$dbman->table_exists($table_cvt)) {
            $dbman->create_table($table_cvt);
        }

        // Gugcat savepoint reached.
        upgrade_plugin_savepoint(true, 2021041600, 'local', 'gugcat');
    }

    if ($oldversion < 2021041601) {
        $table_cvt = new xmldb_table('gcat_grade_converter');
        $table_cvt_lowerboundary = new xmldb_field('lowerboundary', XMLDB_TYPE_NUMBER, '10,5', null, XMLDB_NOTNULL, null, '0');

        // Change field type for lowerboundary
        $dbman->change_field_type($table_cvt, $table_cvt_lowerboundary);

        // Gugcat savepoint reached.
        upgrade_plugin_savepoint(true, 2021041601, 'local', 'gugcat');
    }

    if ($oldversion < 2021051400) {

        // ------- Table gcat_acg_settings starts here --------

        // Define table gcat_acg_settings to be created.
        $table_acg = new xmldb_table('gcat_acg_settings');

        // Define fields to table gcat_acg_settings.
        $table_acg_id = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_acg_acgid = new xmldb_field('acgid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table_acg_itemid = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table_acg_weight = new xmldb_field('weight', XMLDB_TYPE_NUMBER, '10,5', null, null, null, '0');
        $table_acg_cap = new xmldb_field('cap', XMLDB_TYPE_NUMBER, '10,5', null, null, null, '0');

        // Define keys to table gcat_acg_settings.
        $table_acg_key = new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Define indexes to table gcat_acg_settings.
        $table_acg_index1 = new xmldb_index('acgid', XMLDB_INDEX_NOTUNIQUE, array('acgid'));
        $table_acg_index2 = new xmldb_index('itemid', XMLDB_INDEX_NOTUNIQUE, array('itemid'));

        // Adding fields to table gcat_acg_settings.
        $table_acg->addField($table_acg_id);
        $table_acg->addField($table_acg_acgid);
        $table_acg->addField($table_acg_itemid);
        $table_acg->addField($table_acg_weight);
        $table_acg->addField($table_acg_cap);

        // Adding keys to table gcat_acg_settings.
        $table_acg->addKey($table_acg_key);

        // Adding indexes to table gcat_acg_settings.
        $table_acg->addIndex($table_acg_index1);
        $table_acg->addIndex($table_acg_index2);

        // Conditionally launch create table for gcat_acg_settings.
        if (!$dbman->table_exists($table_acg)) {
            $dbman->create_table($table_acg);
        }

        // Gugcat savepoint reached.
        upgrade_plugin_savepoint(true, 2021051400, 'local', 'gugcat');
    }

    if ($oldversion < 2021051900) {
        $table_acg = new xmldb_table('gcat_acg_settings');
        $table_acg_weight = new xmldb_field('weight', XMLDB_TYPE_NUMBER, '10,5', null, null, null, '0');
        $table_acg_cap = new xmldb_field('cap', XMLDB_TYPE_NUMBER, '10,5', null, null, null, '0');
        // Change the default value for weight and cap fields.
        $dbman->change_field_default($table_acg, $table_acg_weight);
        $dbman->change_field_default($table_acg, $table_acg_cap);

        // Gugcat savepoint reached.
        upgrade_plugin_savepoint(true, 2021051900, 'local', 'gugcat');
    }

    return true;
}

