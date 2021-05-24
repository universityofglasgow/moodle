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
        // Table gcat_converter_templates starts here.

        // Define table gcat_converter_templates to be created.
        $tabletpl = new xmldb_table('gcat_converter_templates');

        // Define fields to table gcat_converter_templates.
        $tabletplid = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $tabletpltemplatename = new xmldb_field('templatename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $tabletpluserid = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $tabletplscaletype = new xmldb_field('scaletype', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Define keys to table gcat_converter_templates.
        $tabletplkey = new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding fields to table gcat_converter_templates.
        $tabletpl->addField($tabletplid);
        $tabletpl->addField($tabletpltemplatename);
        $tabletpl->addField($tabletpluserid);
        $tabletpl->addField($tabletplscaletype);

        // Adding keys to table gcat_converter_templates.
        $tabletpl->addKey($tabletplkey);

        // Conditionally launch create table for gcat_converter_templates.
        if (!$dbman->table_exists($tabletpl)) {
            $dbman->create_table($tabletpl);
        }

        // Table gcat_grade_converter starts here.

        // Define table gcat_grade_converter to be created.
        $tablecvt = new xmldb_table('gcat_grade_converter');

        // Define fields to table gcat_grade_converter.
        $tablecvtid = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $tablecvtcourseid = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $tablecvtitemid = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $tablecvttemplateid = new xmldb_field('templateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $tablecvtlowerboundary = new xmldb_field('lowerboundary', XMLDB_TYPE_NUMBER, '10,5', null, XMLDB_NOTNULL, null, '0');
        $tablecvtgrade = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Define keys to table gcat_grade_converter.
        $tablecvtkey1 = new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $tablecvtkey2 = new xmldb_key('gcat_grade_converter_fk', XMLDB_KEY_FOREIGN,
          array('templateid'), 'gcat_converter_templates', array('id'));

        // Define indexes to table gcat_grade_converter.
        $tablecvtindex1 = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        $tablecvtindex2 = new xmldb_index('itemid', XMLDB_INDEX_NOTUNIQUE, array('itemid'));

        // Adding fields to table gcat_grade_converter.
        $tablecvt->addField($tablecvtid);
        $tablecvt->addField($tablecvtcourseid);
        $tablecvt->addField($tablecvtitemid);
        $tablecvt->addField($tablecvttemplateid);
        $tablecvt->addField($tablecvtlowerboundary);
        $tablecvt->addField($tablecvtgrade);

        // Adding keys to table gcat_grade_converter.
        $tablecvt->addKey($tablecvtkey1);
        $tablecvt->addKey($tablecvtkey2);

        // Adding indexes to table gcat_grade_converter.
        $tablecvt->addIndex($tablecvtindex1);
        $tablecvt->addIndex($tablecvtindex2);

        // Conditionally launch create table for gcat_grade_converter.
        if (!$dbman->table_exists($tablecvt)) {
            $dbman->create_table($tablecvt);
        }

        // Gugcat savepoint reached.
        upgrade_plugin_savepoint(true, 2021041600, 'local', 'gugcat');
    }

    if ($oldversion < 2021041601) {
        $tablecvt = new xmldb_table('gcat_grade_converter');
        $tablecvtlowerboundary = new xmldb_field('lowerboundary', XMLDB_TYPE_NUMBER, '10,5', null, XMLDB_NOTNULL, null, '0');

        // Change field type for lowerboundary.
        $dbman->change_field_type($tablecvt, $tablecvtlowerboundary);

        // Gugcat savepoint reached.
        upgrade_plugin_savepoint(true, 2021041601, 'local', 'gugcat');
    }

    if ($oldversion < 2021051400) {

        // Table gcat_acg_settings starts here.

        // Define table gcat_acg_settings to be created.
        $tableacg = new xmldb_table('gcat_acg_settings');

        // Define fields to table gcat_acg_settings.
        $tableacgid = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $tableacgacgid = new xmldb_field('acgid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $tableacgitemid = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $tableacgweight = new xmldb_field('weight', XMLDB_TYPE_NUMBER, '10,5', null, null, null, '0');
        $tableacgcap = new xmldb_field('cap', XMLDB_TYPE_NUMBER, '10,5', null, null, null, '0');

        // Define keys to table gcat_acg_settings.
        $tableacgkey = new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Define indexes to table gcat_acg_settings.
        $tableacgindex1 = new xmldb_index('acgid', XMLDB_INDEX_NOTUNIQUE, array('acgid'));
        $tableacgindex2 = new xmldb_index('itemid', XMLDB_INDEX_NOTUNIQUE, array('itemid'));

        // Adding fields to table gcat_acg_settings.
        $tableacg->addField($tableacgid);
        $tableacg->addField($tableacgacgid);
        $tableacg->addField($tableacgitemid);
        $tableacg->addField($tableacgweight);
        $tableacg->addField($tableacgcap);

        // Adding keys to table gcat_acg_settings.
        $tableacg->addKey($tableacgkey);

        // Adding indexes to table gcat_acg_settings.
        $tableacg->addIndex($tableacgindex1);
        $tableacg->addIndex($tableacgindex2);

        // Conditionally launch create table for gcat_acg_settings.
        if (!$dbman->table_exists($tableacg)) {
            $dbman->create_table($tableacg);
        }

        // Gugcat savepoint reached.
        upgrade_plugin_savepoint(true, 2021051400, 'local', 'gugcat');
    }

    if ($oldversion < 2021051900) {
        $tableacg = new xmldb_table('gcat_acg_settings');
        $tableacgweight = new xmldb_field('weight', XMLDB_TYPE_NUMBER, '10,5', null, null, null, '0');
        $tableacgcap = new xmldb_field('cap', XMLDB_TYPE_NUMBER, '10,5', null, null, null, '0');
        // Change the default value for weight and cap fields.
        $dbman->change_field_default($tableacg, $tableacgweight);
        $dbman->change_field_default($tableacg, $tableacgcap);

        // Gugcat savepoint reached.
        upgrade_plugin_savepoint(true, 2021051900, 'local', 'gugcat');
    }

    if ($oldversion < 2021052000) {
        // Define indexes to table gcat_acg_settings.
        $tableacg = new xmldb_table('gcat_acg_settings');
        $tableacgindex1 = new xmldb_index('acgid', XMLDB_INDEX_NOTUNIQUE, array('acgid'));
        $tableacgindex2 = new xmldb_index('itemid', XMLDB_INDEX_NOTUNIQUE, array('itemid'));

        // Adding indexes to table gcat_acg_settings.
        if (!$dbman->index_exists($tableacg, $tableacgindex1)) {
            $dbman->add_index($tableacg, $tableacgindex1);
        }
        if (!$dbman->index_exists($tableacg, $tableacgindex2)) {
            $dbman->add_index($tableacg, $tableacgindex2);
        }

        // Gugcat savepoint reached.
        upgrade_plugin_savepoint(true, 2021052000, 'local', 'gugcat');
    }

    return true;
}

