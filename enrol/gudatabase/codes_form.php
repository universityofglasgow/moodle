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

class enrol_gudatabase_codes_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        $instance = $this->_customdata;

        $mform->addElement('textarea', 'customtext1', get_string('codelist', 'enrol_gudatabase'), 'rows="15" cols="25" style="height: auto; width:auto;"');
        $mform->addHelpButton('customtext1', 'codelist', 'enrol_gudatabase');
        $mform->setType('customtext1', PARAM_TEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'tab');
        $mform->setType('tab', PARAM_ALPHA);

        $mform->addElement('html', '<div class="alert alert-danger">' . get_string('savewarning', 'enrol_gudatabase') . '</div>');

        $this->add_action_buttons();

        $this->set_data($instance);
    }

}
