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

class enrol_gudatabase_groups_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        list($instance, $codeclasses, $coursedescriptions, $groups) = $this->_customdata;

        if ($coursedescriptions) {
            $mform->addElement('html', '<div class="alert alert-info">' . get_string('groupsinstruction', 'enrol_gudatabase') . '</div>');
        } else {
            $mform->addElement('html', '<div class="alert alert-warning">' . get_string('nolegacycodes', 'enrol_gudatabase') . '</div>');
        }

        if ($coursedescriptions) {
            $mform->addElement('advcheckbox', 'coursegroups', get_string('coursegroups', 'enrol_gudatabase'), '');
            $mform->setDefault('coursegroups', $instance->customint2);
            $mform->addHelpButton('coursegroups', 'coursegroups', 'enrol_gudatabase');
        }

        foreach ($codeclasses as $code => $classes) {
            $description = $coursedescriptions[$code];
            $mform->addElement('html', "<h3>$code ($description)</h3>");
            foreach ($classes as $class) {
                $selector = "{$code}_{$class}";
                $mform->addElement('advcheckbox', $selector, $class, '');
                $mform->setDefault($selector, !empty($groups[$code][$class]));
            }
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'tab');
        $mform->setType('tab', PARAM_ALPHA);

        if ($coursedescriptions) {
            $mform->addElement('html', '<div class="alert alert-danger">' . get_string('savewarning', 'enrol_gudatabase') . '</div>');
            $this->add_action_buttons();
        }


        $this->set_data($instance);
    }

}
