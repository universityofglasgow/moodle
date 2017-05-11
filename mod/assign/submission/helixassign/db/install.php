<?php

/**
 * Post-install code for the submission_helixassign module.
 *
 * @package assignsubmission_helixassign
 * @copyright Streaming LTD 2013
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


/**
 * Code run after the assignsubmission_helixassign module database tables have been created.
 * Moves the plugin to the top of the list (of 3)
 * @return bool
 */
function xmldb_assignsubmission_helixassign_install() {
    global $CFG;

    // do the install

    require_once($CFG->dirroot . '/mod/assign/adminlib.php');
    // set the correct initial order for the plugins
    $pluginmanager = new assign_plugin_manager('assignsubmission');

    $pluginmanager->move_plugin('helixassign', 'up');
    $pluginmanager->move_plugin('helixassign', 'up');

    // do the upgrades
    return true;



}


