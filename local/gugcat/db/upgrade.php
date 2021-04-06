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
 */
function xmldb_local_gugcat_upgrade() {

    global $CFG, $DB;

    $dbman = $DB->get_manager();
    //create table gcat_grade_converter
    $table = new xmldb_table('gcat_grade_converter');
    //check if table is existing in DB, if not then create table
    if(!$dbman->table_exists($table)){
        $field1 = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10',
            XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $field2 = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10',
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $field3 = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10',
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $field4 = new xmldb_field('lowerboundary', XMLDB_TYPE_INTEGER, '10',
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $field5 = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10',
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);

        $table->addField($field1);
        $table->addField($field2);
        $table->addField($field3);
        $table->addField($field4);
        $table->addField($field5);

        $key1 = new xmldb_key('primary');
        $key1->set_attributes(XMLDB_KEY_PRIMARY, array('id'), null, null);
        $key2 = new xmldb_key('foreignkey1');
        $key2->set_attributes(XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id')); 
        $key3 = new xmldb_key('foreignkey2');
        $key3->set_attributes(XMLDB_KEY_FOREIGN, array('itemid'), 'grade_items', array('id'));
        $table->addKey($key1);
        $table->addKey($key2);
        $table->addKey($key3);
        $dbman->create_table($table);
    }

    return true;
}

