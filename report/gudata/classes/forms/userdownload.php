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
 * Main class for userdownload form
 *
 * @package    report_gudata
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_gudata\forms;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

class userdownload extends \moodleform {

    function definition() {
        global $CFG;

        $mform = $this->_form;
        $data = $this->_customdata;

        // hidden
        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', $data['action']);
        $mform->setType('action', PARAM_ALPHA);

        // Submit and download
        $mform->addElement('submit', 'submit', get_string('downloaduser', 'report_gudata'));
    }

}