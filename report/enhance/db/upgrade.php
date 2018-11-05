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

    return true; //have to be in else get an unknown error
}
