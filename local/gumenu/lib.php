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
 * GU Custom Course Menu
 *
 * @package    local_gumenu
 * @copyright  2014 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Extend navigation menu
 */
function local_gumenu_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $DB;

    // check that the user has *any* capability needed here. This is so
    // we don't display the category if not needed
    $caps = array(
        'report/assign:view',
        'report/guid:courseupload',
        'report/guenrol:view',
        'moodle/course:enrolconfig',
        'moodle/course:enrolreview',
        'local/corehr:config',
    );
    if (!has_any_capability($caps, $context)) {
        return;
    }

    // Make sure we can grab the course admin node
    if (!$courseadminnode = $nav->get('courseadmin')) {
        return;
    }

    // only display in course context
    if ($context->contextlevel != CONTEXT_COURSE) {
        return;
    }

    // deduce course
    $courseid = $context->instanceid;
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST); 

    // Add a category
    // (See constructor for navigation_node class in navigationlib.php)
    $props = array(
        'text' => get_string('uofg', 'local_gumenu'),
        'shorttext' => 'uofg',
        'type' => navigation_node::TYPE_CATEGORY,
        'key' => 'uofg',
    );
    $uofgnode = $courseadminnode->add_node(new navigation_node($props));

    // Add a link to anonymous report
    if (has_capability('report/assign:view', $context)) {
        $props = array(
            'text' => get_string('pluginname', 'report_assign'),
            'shorttext' => 'uofgassign',
            'type' => navigation_node::TYPE_CUSTOM,
            'action' => new moodle_url('/report/assign/index.php', array('id' => $courseid)),
            'key' => 'uofgassign',
        );
        $uofgnode->add_node(new navigation_node($props));
    }

    // Add a link to anonymous report
    if (has_capability('report/guenrol:view', $context)) {
        $props = array(
            'text' => get_string('pluginname', 'report_guenrol'),
            'shorttext' => 'uofgenrol',
            'type' => navigation_node::TYPE_CUSTOM,
            'action' => new moodle_url('/report/guenrol/index.php', array('id' => $courseid)),
            'key' => 'uofgenrol',
        );
        $uofgnode->add_node(new navigation_node($props));
    }

    // Add a link to guid courseupload
    if (has_capability('report/guid:courseupload', $context)) {
        $props = array(
            'text' => get_string('courseupload', 'report_guid'),
            'shorttext' => 'uofgcourseupload',
            'type' => navigation_node::TYPE_CUSTOM,
            'action' => new moodle_url('/report/guid/courseupload.php', array('id' => $courseid)),
            'key' => 'uofcourseupload',
        );
        $uofgnode->add_node(new navigation_node($props));
    }

    // Add a link to control enrolments
    if (has_capability('moodle/course:enrolconfig', $context) or has_capability('moodle/course:enrolreview', $context)) {
        $props = array(
            'text' => get_string('enrolmentinstances', 'enrol'),
            'shorttext' => 'uofgenrolment',
            'type' => navigation_node::TYPE_CUSTOM,
            'action' => new moodle_url('/enrol/instances.php', array('id'=>$courseid)),
            'key' => 'uofgenrolment',
        );
        $uofgnode->add_node(new navigation_node($props));

        // and to sync...
        $props = array(
            'text' => get_string('enrolsync', 'local_gumenu'),
            'shorttext' => 'uofgsync',
            'type' => navigation_node::TYPE_CUSTOM,
            'action' => new moodle_url('/enrol/gudatabase/sync.php', array('courseid' => $courseid, 'sync' => 1)),
            'key' => 'uofgsync',
        );
        $uofgnode->add_node(new navigation_node($props));
    }

    // Add a link for CoreHR thing
    if (has_capability('local/corehr:config', $context)) {
        $props = array(
            'text' => get_string('pluginname', 'local_corehr'),
            'shorttext' => 'corehr',
            'type' => navigation_node::TYPE_CUSTOM,
            'action' => new moodle_url('/local/corehr/config.php', array('id' => $courseid)),
            'key' => 'corehr',
        );
        $uofgnode->add_node(new navigation_node($props));
    }

}



