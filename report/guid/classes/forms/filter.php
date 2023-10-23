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
 * Version details.
 *
 * @package    report
 * @subpackage guid
 * @copyright  2012 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_guid\forms;

defined('MOODLE_INTERNAL') || die;

use \moodleform;

class filter extends moodleform {

    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        // Main part.
        $mform->addElement('html', '<p class="alert">'.get_string('instructions', 'report_guid' ).'</p>' );
        $mform->addElement('text', 'firstname', get_string('firstname', 'report_guid' ) );
        $mform->setType('firstname', PARAM_RAW);
        $mform->addElement('text', 'lastname', get_string('lastname', 'report_guid' ) );
        $mform->setType('lastname', PARAM_RAW);
        $mform->addElement('text', 'email', get_string('email', 'report_guid' ) );
        $mform->setType('email', PARAM_EMAIL);
        $mform->addElement('text', 'idnumber', get_string('idnumber', 'report_guid' ) );
        $mform->setType('idnumber', PARAM_ALPHANUM);
        $mform->addElement('text', 'guid', get_string('guidform', 'report_guid' ) );
        $mform->setType('guid', PARAM_ALPHANUM);

        // Action buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('search', 'report_guid'));
        $buttonarray[] = $mform->createElement('submit', 'resetbutton', get_string('reset', 'report_guid'));
        $mform->addGroup($buttonarray, 'buttonbar', '', ' ', false);
    }
}
