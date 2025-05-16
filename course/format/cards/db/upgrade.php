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
 * Plugin upgrade manager.
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Perform an upgrade
 *
 * @param int $oldversion Old plugin version to upgrade from
 * @return bool
 * @throws ddl_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_format_cards_upgrade($oldversion = 0): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023052500) {
        // Create the section break table.
        $table = new xmldb_table('format_cards_break');

        // Add table fields.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('section', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, '');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false, 0);

        // Add keys.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, [ 'id' ]);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, [ 'courseid' ], 'course', [ 'id' ]);

        $dbman->create_table($table);
        upgrade_plugin_savepoint(true, 2023052500, 'format', 'cards');
    }

    return true;
}
