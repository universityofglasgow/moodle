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
 * This file keeps track of upgrades to the helixmedia module
 *
 * @package    mod
 * @subpackage helixmedia
 * @author     Tim Williams for Streaming LTD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/helixmedia/locallib.php');

/**
 * xmldb_helixmedia_upgrade is the function that upgrades
 * the helic_media module database when is needed
 *
 * This function is automaticly called when version number in
 * version.php changes.
 *
 * @param int $oldversion New old version number.
 *
 * @return boolean
 */
function xmldb_helixmedia_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014081101) {
        // Move the plugin settings to mdl_config_plugins.

        // Insert into plugins config.
        set_config("launchurl", $CFG->helixmedia_launchurl, "helixmedia");
        set_config("consumer_key", $CFG->helixmedia_consumer_key, "helixmedia");
        set_config("shared_secret", $CFG->helixmedia_shared_secret, "helixmedia");
        set_config("org_id", $CFG->helixmedia_org_id, "helixmedia");
        set_config("default_launch", $CFG->helixmedia_default_launch, "helixmedia");
        set_config("sendname", $CFG->helixmedia_sendname, "helixmedia");
        set_config("sendemailaddr", $CFG->helixmedia_sendemailaddr, "helixmedia");
        set_config("custom_params", $CFG->helixmedia_custom_params, "helixmedia");

        // Remove the old values.
        unset_config("helixmedia_launchurl");
        unset_config("helixmedia_consumer_key");
        unset_config("helixmedia_shared_secret");
        unset_config("helixmedia_org_id");
        unset_config("helixmedia_default_launch");
        unset_config("helixmedia_sendname");
        unset_config("helixmedia_sendemailaddr");
        unset_config("helixmedia_custom_params");
    }

    if ($oldversion < 2020021101) {
        // Define table helixmedia_mobile to be created.
        $table = new xmldb_table('helixmedia_mobile');

        // Adding fields to table helixmedia_mobile.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('instance', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('token', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table helixmedia_mobile.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for helixmedia_mobile.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Helixmedia savepoint reached.
        upgrade_mod_savepoint(true, 2020021101, 'helixmedia');
    }

    try {
        echo helixmedia_version_check();
    } catch (Exception $e) {
        echo "Error communicating with MEDIAL. Skipping version check";
    }

    return true;
}

