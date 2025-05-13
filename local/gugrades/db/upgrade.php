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
 * Upgrade code for local_gugrades
 *
 * @package    local_gugrades
 * @author     Howard Miller
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * upgrade local_gugrades
 * @param int $oldversion The old version of the assign module
 * @return bool
 */
function xmldb_local_gugrades_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024080500) {

        // Define index logugradesgigtuiic (not unique) to be added to local_gugrades_grade.
        $table = new xmldb_table('local_gugrades_grade');
        $index = new xmldb_index('logugradesgigtuiic', XMLDB_INDEX_NOTUNIQUE, ['gradeitemid', 'gradetype', 'userid', 'iscurrent']);

        // Conditionally launch add index logugradesgigtuiic.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index localgugrade_cigiuici (not unique) to be added to local_gugrades_grade.
        $table = new xmldb_table('local_gugrades_grade');
        $index = new xmldb_index('localgugrade_cigiuici', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'gradeitemid', 'userid', 'columnid']);

        // Conditionally launch add index localgugrade_cigiuici.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index local_gugrades_giuiic (not unique) to be added to local_gugrades_grade.
        $table = new xmldb_table('local_gugrades_grade');
        $index = new xmldb_index('local_gugrades_giuiic', XMLDB_INDEX_NOTUNIQUE, ['gradeitemid', 'userid', 'iscurrent']);

        // Conditionally launch add index local_gugrades_giuiic.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index column_gigt (not unique) to be added to local_gugrades_column.
        $table = new xmldb_table('local_gugrades_column');
        $index = new xmldb_index('column_gigt', XMLDB_INDEX_NOTUNIQUE, ['gradeitemid', 'gradetype']);

        // Conditionally launch add index column_gigt.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index column_si (not unique) to be added to local_gugrades_scalevalue.
        $table = new xmldb_table('local_gugrades_scalevalue');
        $index = new xmldb_index('column_si', XMLDB_INDEX_NOTUNIQUE, ['scaleid']);

        // Conditionally launch add index column_si.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024080500, 'local', 'gugrades');
    }

    if ($oldversion < 2024081900) {

        // Define table local_gugrades_agg_conversion to be created.
        $table = new xmldb_table('local_gugrades_agg_conversion');

        // Adding fields to table local_gugrades_agg_conversion.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('gradecategoryid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mapid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_gugrades_agg_conversion.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_gugrades_agg_conversion.
        $table->add_index('logu_gugid', XMLDB_INDEX_UNIQUE, ['gradecategoryid']);

        // Conditionally launch create table for local_gugrades_agg_conversion.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024081900, 'local', 'gugrades');
    }

    if ($oldversion < 2024082600) {

        // Define field dropped to be added to local_gugrades_grade.
        $table = new xmldb_table('local_gugrades_grade');
        $field = new xmldb_field('dropped', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'auditcomment');

        // Conditionally launch add field dropped.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024082600, 'local', 'gugrades');
    }

    if ($oldversion < 2024082800) {

        // Define field catoverride to be added to local_gugrades_grade.
        $table = new xmldb_table('local_gugrades_grade');
        $field = new xmldb_field('catoverride', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'dropped');

        // Conditionally launch add field catoverride.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024082800, 'local', 'gugrades');
    }

    if ($oldversion < 2024092400) {

        // Define field normalisedweight to be added to local_gugrades_grade.
        $table = new xmldb_table('local_gugrades_grade');
        $field = new xmldb_field('normalisedweight', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, 'catoverride');

        // Conditionally launch add field normalisedweight.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024092400, 'local', 'gugrades');
    }

    if ($oldversion < 2024101000) {

        // Define table local_gugrades_altered_weight to be created.
        $table = new xmldb_table('local_gugrades_altered_weight');

        // Adding fields to table local_gugrades_altered_weight.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('gradeitemid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('weight', XMLDB_TYPE_NUMBER, '11, 5', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timealtered', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_gugrades_altered_weight.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_gugrades_altered_weight.
        $table->add_index('gradeitemid-userid', XMLDB_INDEX_UNIQUE, ['gradeitemid', 'userid']);

        // Conditionally launch create table for local_gugrades_altered_weight.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024101000, 'local', 'gugrades');
    }

    if ($oldversion < 2024101501) {

        // Define field courseid to be added to local_gugrades_altered_weight.
        $table = new xmldb_table('local_gugrades_altered_weight');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null, 'id');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024101501, 'local', 'gugrades');
    }

    if ($oldversion < 2024101600) {

        // Define field categoryid to be added to local_gugrades_altered_weight.
        $table = new xmldb_table('local_gugrades_altered_weight');
        $field = new xmldb_field('categoryid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null, 'courseid');

        // Conditionally launch add field categoryid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024101600, 'local', 'gugrades');
    }

    if ($oldversion < 2024111100) {

        // Define index local_gugrades_giuiic (not unique) to be dropped form local_gugrades_grade.
        $table = new xmldb_table('local_gugrades_grade');
        $index = new xmldb_index('local_gugrades_giuiic', XMLDB_INDEX_NOTUNIQUE, ['gradeitemid', 'userid', 'iscurrent']);

        // Conditionally launch drop index local_gugrades_giuiic.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define index local_gugrades_giuiic (not unique) to be added to local_gugrades_grade.
        $table = new xmldb_table('local_gugrades_grade');
        $index = new xmldb_index('local_gugrades_giuiic', XMLDB_INDEX_NOTUNIQUE, ['id', 'gradeitemid', 'userid', 'iscurrent']);

        // Conditionally launch add index local_gugrades_giuiic.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024111100, 'local', 'gugrades');
    }

    if ($oldversion < 2024111101) {

        // Define index local_gugrades_prov (not unique) to be added to local_gugrades_grade.
        $table = new xmldb_table('local_gugrades_grade');
        $index = new xmldb_index('local_gugrades_prov', XMLDB_INDEX_NOTUNIQUE, ['gradeitemid', 'userid', 'iscurrent']);

        // Conditionally launch add index local_gugrades_prov.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024111101, 'local', 'gugrades');
    }

    if ($oldversion < 2024111200) {

        // Define index gugrades_giid (not unique) to be added to local_gugrades_map_item.
        $table = new xmldb_table('local_gugrades_map_item');
        $index = new xmldb_index('gugrades_giid', XMLDB_INDEX_NOTUNIQUE, ['gradeitemid']);

        // Conditionally launch add index gugrades_giid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2024111200, 'local', 'gugrades');
    }

    if ($oldversion < 2025051301) {

        // Changing precision of field admingrade on table local_gugrades_grade to (30).
        $table = new xmldb_table('local_gugrades_grade');
        $field = new xmldb_field('admingrade', XMLDB_TYPE_CHAR, '30', null, null, null, null, 'convertedgrade');

        // Launch change of precision for field admingrade.
        $dbman->change_field_precision($table, $field);

        // Update setting defaults
        \local_gugrades\admingrades::setting_defaults();

        // Map old admin codes (in db) to new names.
        // Do this again because some may have been missed due to an error.
        $maps = \local_gugrades\admingrades::get_upgrade_map();
        foreach ($maps as $oldcode => $newname) {
            $sql = 'UPDATE {local_gugrades_grade} SET admingrade = :newname WHERE admingrade = :oldcode';
            $DB->execute($sql, [
                'newname' => $newname,
                'oldcode' => $oldcode,
            ]);
        }

        // Gugrades savepoint reached.
        upgrade_plugin_savepoint(true, 2025051301, 'local', 'gugrades');
    }

    return true;
}