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

    if ($oldversion < 2020121701) {

        // Define table report_enhance_comment to be created.
        $table = new xmldb_table('report_enhance_comment');

        // Adding fields to table report_enhance_comment.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('enhanceid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('comment', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timeadded', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeedited', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table report_enhance_comment.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table report_enhance_comment.
        $table->add_index('ix_eid', XMLDB_INDEX_NOTUNIQUE, ['enhanceid']);

        // Conditionally launch create table for report_enhance_comment.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Enhance savepoint reached.
        upgrade_plugin_savepoint(true, 2020121701, 'report', 'enhance');
    }

    if ($oldversion < 2021070800) {

        // Define fields to be added to report_enhance.
        $table = new xmldb_table('report_enhance');

        $field = new xmldb_field('service', XMLDB_TYPE_TEXT, null, null, null, null, null, 'headline');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('audience', XMLDB_TYPE_TEXT, null, null, null, null, null, 'service');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('transferability', XMLDB_TYPE_TEXT, null, null, null, null, null, 'audience');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('assignedto', XMLDB_TYPE_TEXT, null, null, null, null, null, 'transferability');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('gdpr', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'assignedto');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('evaluation', XMLDB_TYPE_TEXT, null, null, null, null, null, 'reviewernotes');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Combine fields desireability, impact and viability into reviewernotes
        $requests = $DB->get_records('report_enhance');
        foreach ($requests as $request) {
            $new = '';
            if ($request->desirability) {
                $new .= '<h4>' . get_string('desirability', 'report_enhance') . '</h4>';
                $new .= $request->desirability;
            }
            if ($request->impact) {
                $new .= '<h4>' . get_string('impact', 'report_enhance') . '</h4>';
                $new .= $request->impact;
            }
            if ($request->viability) {
                $new .= '<h4>' . get_string('viability', 'report_enhance') . '</h4>';
                $new .= $request->viability;
            }
            if ($new) {
                $new = '<p></p><h3>' . get_string('oldfields', 'report_enhance') . '</h3>' . $new;
            }
            $new = $request->reviewernotes . $new;
            $DB->set_field('report_enhance', 'reviewernotes', $new, ['id' => $request->id]);
        }

        // Enhance savepoint reached.
        upgrade_plugin_savepoint(true, 2021070800, 'report', 'enhance');
    }

    if ($oldversion < 2021070900) {

        // Prevent truncated errors
        $DB->set_field('report_enhance', 'service', 1);
        $DB->set_field('report_enhance', 'assignedto', 1);

        // Changing type of field service on table report_enhance to int.
        $table = new xmldb_table('report_enhance');

        $field = new xmldb_field('service', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '1', 'headline');
        $dbman->change_field_type($table, $field);

        $field = new xmldb_field('assignedto', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '1', 'transferability');
        $dbman->change_field_type($table, $field);

        // Fix deprecated statuses
        $status = new \report_enhance\status();
        $requests = $DB->get_records('report_enhance');
        foreach ($requests as $request) {
            if (($request->status == ENHANCE_STATUS_MOREINFORMATION) || ($request->status == ENHANCE_STATUS_WAITINGDEVELOPMENT)) {
                $DB->set_field('report_enhance', 'status', ENHANCE_STATUS_UNDERREVIEW, ['id' => $request->id]);
            }
        }

        // Enhance savepoint reached.
        upgrade_plugin_savepoint(true, 2021070900, 'report', 'enhance');
    }


    if ($oldversion < 2021070901) {

        // Fix deprecated statuses (again)
        $status = new \report_enhance\status();
        $requests = $DB->get_records('report_enhance');
        foreach ($requests as $request) {
            if (($request->status == ENHANCE_STATUS_PENDINGREVIEW) || ($request->status == ENHANCE_STATUS_DESIRABLE)) {
                $DB->set_field('report_enhance', 'status', ENHANCE_STATUS_UNDERREVIEW, ['id' => $request->id]);
            }
        }

        // Enhance savepoint reached.
        upgrade_plugin_savepoint(true, 2021070901, 'report', 'enhance');
    }

    if ($oldversion < 2021071000) {

        $DB->set_field('report_enhance', 'audience', '1');

        // Enhance savepoint reached.
        upgrade_plugin_savepoint(true, 2021071000, 'report', 'enhance');      
    }


    return true; //have to be in else get an unknown error
}
