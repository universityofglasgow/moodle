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
 * @package     qtype_mtf
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the mtf question type.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_mtf_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $table = new xmldb_table('qtype_mtf_options');
    $oldfield = new xmldb_field('shuffleoptions', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1');

    if ($dbman->field_exists($table, $oldfield)) {
        $dbman->rename_field($table, $oldfield, 'shuffleanswers');
    }

    return true;
}
