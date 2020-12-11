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
 * Version information
 *
 * @package    qtype
 * @subpackage multinumerical
 * @copyright  2013 Université de Lausanne
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function xmldb_qtype_multinumerical_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();

    $result = true;

    if ($result && $oldversion < 2012110100) {

        // Define key questionid (foreign) to be dropped form question_multinumerical
        $table = new xmldb_table('question_multinumerical');
        $key = new xmldb_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'questionid', array('id'));

        // Launch drop key questionid
        $dbman->drop_key($table, $key);

        // Define key question (foreign) to be added to question_multinumerical
        $table = new xmldb_table('question_multinumerical');
        $key = new xmldb_key('question', XMLDB_KEY_FOREIGN, array('question'), 'question', array('id'));

        // Launch add key questionid
        $dbman->add_key($table, $key);

        // multinumerical savepoint reached
        upgrade_plugin_savepoint(true, 2014082500, 'qtype', 'multinumerical');
    }

    return $result;
}

