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
 * Sychronise completion data for CoreHR
 *
 * @package    local_corehr
 * @copyright  2016 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_corehr\form;

require_once("$CFG->libdir/formslib.php");

class config extends \moodleform {

    function definition() {
        global $CFG;

        $mform =& $this->_form;

        $mform->addElement('html', get_string('configintro', 'local_corehr'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'coursecode', get_string('coursecode', 'local_corehr'));
        $mform->setType('coursecode', PARAM_TEXT);
        $mform->addHelpButton('coursecode', 'coursecode', 'local_corehr');

        $mform->addElement('advcheckbox', 'enrolallstaff', get_string('enrolallstaff', 'local_corehr'));
        $mform->addHelpButton('enrolallstaff', 'enrolallstaff', 'local_corehr');

        $this->add_action_buttons();
    }

}
