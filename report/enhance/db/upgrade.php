<?php

/*
 * Upgrade this Enhance instance
 * @param int $oldversion The old version of the My feedback report
 * @return bool
 */

function xmldb_report_enhance_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2018110500) {

        // Rename field desirability on table report_enhance to NEWNAMEGOESHERE.
        $table = new xmldb_table('report_enhance');
        $field = new xmldb_field('functionality', XMLDB_TYPE_TEXT, null, null, null, null, null, 'department');

        // Launch rename field functionality to desirability.
        $dbman->rename_field($table, $field, 'desirability');

        $field = new xmldb_field('policies', XMLDB_TYPE_TEXT, null, null, null, null, null, 'viability');

        // Launch rename field policies to impact.
        $dbman->rename_field($table, $field, 'impact');

        // Enhance savepoint reached.
        upgrade_plugin_savepoint(true, 2018110500, 'report', 'enhance');
    }

    if ($oldversion < 2019013000) {

        // Define field reviewernotes to be added to report_enhance.
        $table = new xmldb_table('report_enhance');
        $field = new xmldb_field('reviewernotes', XMLDB_TYPE_TEXT, null, null, null, null, null, 'result');

        // Conditionally launch add field reviewernotes.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table report_enhance_vote to be created.
        $table = new xmldb_table('report_enhance_vote');

        // Adding fields to table report_enhance_vote.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('enhanceid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table report_enhance_vote.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table report_enhance_vote.
        $table->add_index('ix_userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch create table for report_enhance_vote.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Enhance savepoint reached.
        upgrade_plugin_savepoint(true, 2019013000, 'report', 'enhance');
    }

    if ($oldversion < 2019020700) {

        // Define field priority to be added to report_enhance.
        $table = new xmldb_table('report_enhance');
        $field = new xmldb_field('priority', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'reviewernotes');

        // Conditionally launch add field priority.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Enhance savepoint reached.
        upgrade_plugin_savepoint(true, 2019020700, 'report', 'enhance');
    }

    return true; //have to be in else get an unknown error
}
