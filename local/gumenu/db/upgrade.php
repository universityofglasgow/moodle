<?php
/*
 * @param int $oldversion
 * @return bool
 */

function xmldb_local_gumenu_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020052801) {

        // If block_course_overview is no longer present, replace with block_myoverview.
        if (!file_exists($CFG->dirroot . '/blocks/course_overview/block_course_overview.php')) {
            $DB->set_field('block_instances', 'blockname', 'myoverview', array('blockname' => 'course_overview'));
        }

        upgrade_plugin_savepoint(true, 2020052801, 'local', 'gumenu');
    }

    return true;
}
