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

require('../../config.php');
require_once('edit_form.php');

$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/gudatabase:config', $context);

$PAGE->set_url('/enrol/gudatabase/edit.php', array('courseid'=>$course->id));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
if (!enrol_is_enabled('gudatabase')) {
    redirect($return);
}

$plugin = enrol_get_plugin('gudatabase');

if ($instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'gudatabase'), 'id ASC')) {
    $instance = array_shift($instances);
    if ($instances) {
        // Oh - we allow only one instance per course!!
        foreach ($instances as $del) {
            $plugin->delete_instance($del);
        }
    }
    // Merge these two settings to one value for the single selection element.
    if ($instance->notifyall and $instance->expirynotify) {
        $instance->expirynotify = 2;
    }
    unset($instance->notifyall);

} else {
    require_capability('moodle/course:enrolconfig', $context);
    // No instance yet, we have to add new instance.
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
    $instance = new stdClass();
    $instance->id              = null;
    $instance->courseid        = $course->id;
}

$mform = new enrol_gudatabase_edit_form(null, array($instance, $plugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) {
    if ($instance->id) {
        $instance->roleid          = $data->roleid;
        $instance->enrolperiod     = $data->enrolperiod;
        $instance->expiryroleid    = $data->expiryroleid;
        $instance->timemodified    = time();

        $DB->update_record('enrol', $instance);

        // Use standard API to update instance status.
        if ($instance->status != $data->status) {
            $instance = $DB->get_record('enrol', array('id'=>$instance->id));
            $plugin->update_status($instance, $data->status);
            $context->mark_dirty();
        }

    } else {
        $fields = array(
            'status'          => $data->status,
            'roleid'          => $data->roleid,
            'enrolperiod'     => $data->enrolperiod,
            'expireroleid'    => $data->expireroleid,
            'expirythreshold' => $data->expirythreshold);
        $plugin->add_instance($course, $fields);
    }

    redirect($return);
}

$PAGE->set_title(get_string('pluginname', 'enrol_manual'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_gudatabase'));
$mform->display();
echo $OUTPUT->footer();
