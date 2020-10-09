<?php
/*
 * @param int $oldversion 
 * @return bool
 */

function xmldb_local_corehr_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2018072304) {

        // Define table local_corehr_status to be created.
        $table = new xmldb_table('local_corehr_status');

        // Adding fields to table local_corehr_status.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('personnelno', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('coursecode', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('completed', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('lasttry', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('retrycount', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_corehr_status.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_corehr_log.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Corehr savepoint reached.
        upgrade_plugin_savepoint(true, 2018072304, 'local', 'corehr');
    }

    if ($oldversion < 2019021800) {

        // Define table local_corehr_extract to be created.
        $table = new xmldb_table('local_corehr_extract');

        // Adding fields to table local_corehr_extract.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('college', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('collegedesc', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('costcentre', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('costcentredesc', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('title', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('forename', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('middlename', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('surname', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('knownas', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('orgunitno', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('orgunitdesc', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('school', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('schooldesc', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('jobtitle', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('jobtitledesc', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_corehr_extract.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_corehr_extract.
        $table->add_index('ux_uid', XMLDB_INDEX_UNIQUE, array('userid'));

        // Conditionally launch create table for local_corehr_extract.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Corehr savepoint reached.
        upgrade_plugin_savepoint(true, 2019021800, 'local', 'corehr');
    }

    if ($oldversion < 2019041100) {

        // Define field error to be added to local_corehr_status.
        $table = new xmldb_table('local_corehr_status');
        $field = new xmldb_field('error', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'status');

        // Conditionally launch add field error.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Corehr savepoint reached.
        upgrade_plugin_savepoint(true, 2019041100, 'local', 'corehr');
    }

    if ($oldversion < 2019120900) {

        // Define field enrolallstaff to be added to local_corehr.
        $table = new xmldb_table('local_corehr');
        $field = new xmldb_field('enrolallstaff', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'coursecode');

        // Conditionally launch add field enrolallstaff.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Corehr savepoint reached.
        upgrade_plugin_savepoint(true, 2019120900, 'local', 'corehr');
    }

    return true; //have to be in else get an unknown error
}
