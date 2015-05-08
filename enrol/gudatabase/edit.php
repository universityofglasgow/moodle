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
 * Adds new instance of enrol_manual to specified course
 * or edits current instance.
 *
 * @package    enrol_gudatabase
 * @copyright  2014 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/edit_form.php');
require_once(dirname(__FILE__) . '/codes_form.php');
require_once(dirname(__FILE__) . '/groups_form.php');

$courseid = required_param('courseid', PARAM_INT);
$sync = optional_param('sync', 0, PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT);
$tab = optional_param('tab', 'config', PARAM_ALPHA);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/gudatabase:config', $context);

// Get the enrolment plugin.
$plugin = enrol_get_plugin('gudatabase');

// If sync, just do that and get out. This has to work for all instances.
if ($sync) {
    if ($instances = $DB->get_records('enrol', array('courseid' => $courseid, 'enrol' => 'gudatabase'))) {
        foreach( $instances as $instance) {
            $plugin->enrol_course_users($course, $instance);
            $plugin->sync_groups($course, $instance);
        }
    }
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    die;
}

$PAGE->set_url('/enrol/gudatabase/edit.php', array('courseid' => $course->id, 'id' => $instanceid, 'tab' => $tab));
$PAGE->set_pagelayout('admin');
$output = $PAGE->get_renderer('enrol_gudatabase');

$return = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
if (!enrol_is_enabled('gudatabase')) {
    redirect($return);
}

if ($instanceid) {
    $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'gudatabase', 'id'=>$instanceid), '*', MUST_EXIST);

    // Merge these two settings to one value for the single selection element.
    if ($instance->notifyall and $instance->expirynotify) {
        $instance->expirynotify = 2;
    }
    unset($instance->notifyall); 

    // just to make form (code) more readable
    $instance->expireroleid = $instance->customint1;
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // No instance yet, we have to add new instance.
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
    $instance = new stdClass();
    $instance->id              = null;
    $instance->courseid        = $course->id;
    $instance->roleid          = $plugin->get_config('defaultrole');
    $instance->customtext1     = '';
    $instance->customtext2     = '';
    $instance->customint1      = 0;
    $instance->customint2      = 0;
    $instance->customint3      = 0;
}

// check which tab is active and what action to take
// only show config tab if new instance
if (($tab=='config') || !$instance->id) {
    $mform = new enrol_gudatabase_edit_form(null, array($instance, $plugin, $context));

    if ($mform->is_cancelled()) {
        redirect($return);

    } else if ($data = $mform->get_data()) {
        if ($instanceid) {
            $instance->name            = $data->name;
            $instance->status          = $data->status;
            $instance->roleid          = $data->roleid;
            $instance->enrolperiod     = $data->enrolperiod;
            $instance->enrolenddate    = $data->enrolenddate;
            $instance->customint1      = $data->expireroleid;
            $instance->customint3      = $data->customint3;
            $instance->timemodified    = time();

            $DB->update_record('enrol', $instance);

            // Use standard API to update instance status.
            if ($instance->status != $data->status) {
                $instance = $DB->get_record('enrol', array('id' => $instance->id));
                $plugin->update_status($instance, $data->status);
                $context->mark_dirty();
            }

        } else {
            $fields = array(
                'name'            => $data->name,
                'status'          => $data->status,
                'roleid'          => $data->roleid,
                'enrolperiod'     => $data->enrolperiod,
                'enrolenddate'    => $data->enrolenddate,
                'customint1'      => $data->expireroleid,
                'customint3'      => $data->customint3,
                'timemodified'    => time(),
            );
            $newid = $plugin->add_instance($course, $fields);
            $instance = $DB->get_record('enrol', array('id' => $newid));
        }

        $plugin->enrol_course_users( $course, $instance );
        $plugin->sync_groups($course, $instance);

        redirect($return);
    }
} else if ($tab=='codes') {

    // get list of codes
    $codes = $plugin->get_codes($course, $instance);

    // create form
    if (empty($instance->customtext1)) {
        $instance->customtext1 = '';
    }
    $instance->tab = $tab;
    $cform = new enrol_gudatabase_codes_form(null, $instance);

    // process codes form
    if ($cform->is_cancelled()) {
        redirect($return);
    } else if ($data = $cform->get_data()) {
        $instance->customtext1 = strtoupper($data->customtext1);
        $DB->update_record('enrol', $instance);

        // Update enrolments and groups (slow).
        $plugin->enrol_course_users( $course, $instance );
        $plugin->sync_groups($course, $instance);
    }

    // reflect any changes we just did
    $codes = $plugin->get_codes($course, $instance);

} else if ($tab=='groups') {

    // get (serialised) group configuration
    if (empty($instance->customtext2)) {
        $instance->customtext2 = '';
    }
    if (!$groups = unserialize($instance->customtext2)) {
        $groups = array();
    }

    // get current codes
    $codes = $plugin->get_codes($course, $instance);

    // loop through to get current classes
    $codeclasses = array();
    $coursedescriptions = array();
    foreach ($codes as $code) {
        $classes = $plugin->external_classes($code);
        $codeclasses[$code] = $classes;
        $coursedescriptions[$code] = $output->courseinfo($course->id, $code);
    }

    // form stuff
    $instance->tab = $tab;
    $gform = new enrol_gudatabase_groups_form(null, array($instance, $codeclasses, $coursedescriptions, $groups));

    // process form
    if ($gform->is_cancelled()) {
        redirect($return);
    } else if ($data = $gform->get_data()) {
        $groups = array();
        foreach ($codeclasses as $code => $codeclass) {
            $groups[$code] = array();
            foreach ($codeclass as $class) {
                $selector = "{$code}_{$class}";
                $groups[$code][$class] = $data->$selector == 1;
            }
        }
        $instance->customtext2 = serialize($groups);

        // Set creating course groups
        $instance->customint2 = $data->coursegroups;
        $DB->update_record('enrol', $instance);        

        // Update enrolments and groups
        $plugin->enrol_course_users( $course, $instance );
        $plugin->sync_groups($course, $instance);
    }
}

$PAGE->set_title(get_string('pluginname', 'enrol_manual'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_gudatabase'));
if ($instance->id) {
    echo $output->print_tabs($courseid, $instance->id, $tab);
}
if (($tab=='config') || !$instance->id) {
    $mform->display();
} else if ($tab=='codes') {
    echo $output->print_codes($course->id, $codes, $instance->customint3);
    $cform->display();
} else if ($tab=='groups') {
    $gform->display();
}
echo $OUTPUT->footer();
