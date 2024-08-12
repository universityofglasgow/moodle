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
 * upgrade this recompletion
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

    return true;
}