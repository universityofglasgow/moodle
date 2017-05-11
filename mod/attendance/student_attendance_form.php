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
 * Student form class.
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

/**
 * Class mod_attendance_student_attendance_form
 *
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_attendance_student_attendance_form extends moodleform {
    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $USER;

        $mform  =& $this->_form;

        $attforsession = $this->_customdata['session'];
        $attblock = $this->_customdata['attendance'];

        $statuses = $attblock->get_statuses();

        $mform->addElement('hidden', 'sessid', null);
        $mform->setType('sessid', PARAM_INT);
        $mform->setConstant('sessid', $attforsession->id);

        $mform->addElement('hidden', 'sesskey', null);
        $mform->setType('sesskey', PARAM_INT);
        $mform->setConstant('sesskey', sesskey());

        // Set a title as the date and time of the session.
        $sesstiontitle = userdate($attforsession->sessdate, get_string('strftimedate')).' '
                .attendance_strftimehm($attforsession->sessdate);

        $mform->addElement('header', 'session', $sesstiontitle);

        // If a session description is set display it.
        if (!empty($attforsession->description)) {
            $mform->addElement('html', $attforsession->description);
        }
        if (!empty($attforsession->studentpassword)) {
            $mform->addElement('text', 'studentpassword', get_string('password', 'attendance'));
            $mform->setType('studentpassword', PARAM_TEXT);
            $mform->addRule('studentpassword', get_string('passwordrequired', 'attendance'), 'required');
        }
        // Create radio buttons for setting the attendance status.
        $radioarray = array();
        foreach ($statuses as $status) {
            $radioarray[] =& $mform->createElement('radio', 'status', '', $status->description, $status->id, array());
        }
        // Add the radio buttons as a control with the user's name in front.
        $mform->addGroup($radioarray, 'statusarray', $USER->firstname.' '.$USER->lastname.':', array(''), false);
        $mform->addRule('statusarray', get_string('attendancenotset', 'attendance'), 'required', '', 'client', false, false);

        $this->add_action_buttons();
    }
}