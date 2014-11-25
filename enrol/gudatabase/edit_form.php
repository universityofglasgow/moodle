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
 * @package    enrol_gudatabase
 * @copyright  2014 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_gudatabase_edit_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_gudatabase'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_gudatabase');
        $mform->setDefault('status', $plugin->get_config('status'));

        $options = array(
            0 => get_string('no'),
            1 => get_string('yes'),
        );
        $mform->addElement('select', 'customint3', get_string('settingscodes', 'enrol_gudatabase'), $options);
        $mform->addHelpButton('customint3', 'settingscodes', 'enrol_gudatabase');
        $mform->setDefault('customint3', 0);

        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('defaultrole', 'role'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));

        $mform->addElement('duration', 'enrolperiod', get_string('defaultperiod', 'enrol_gudatabase'), array('optional' => true, 'defaultunit' => 86400));
        $mform->setDefault('enrolperiod', $plugin->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'defaultperiod', 'enrol_gudatabase');

        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_gudatabase'), array('optional' => true));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_gudatabase');

        $roles = array(0 => get_string('unenrol', 'enrol_gudatabase')) + $roles;
        $mform->addElement('select', 'expireroleid', get_string('expirerole', 'enrol_gudatabase'), $roles);
        $mform->setDefault('expireroleid', $plugin->get_config('expireroleid'));
        $mform->addHelpButton('expireroleid', 'expirerole', 'enrol_gudatabase');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        if (enrol_accessing_via_instance($instance)) {
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), get_string('instanceeditselfwarningtext', 'core_enrol'));
        }

        if (empty($instance->id)) {
            $mform->addElement('html', '<div class="alert alert-info">' . get_string('newwarning', 'enrol_gudatabase') . '</div>');
        }

        $mform->addElement('html', '<div class="alert alert-danger">' . get_string('savewarning', 'enrol_gudatabase') . '</div>');

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        return $errors;
    }
}
