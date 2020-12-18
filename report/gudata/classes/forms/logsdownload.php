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

class logsdownload extends \moodleform {

    function definition() {
        global $CFG;

        $mform = $this->_form;
        $data = $this->_customdata;

        // hidden
        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', $data['action']);
        $mform->setType('action', PARAM_ALPHA);

        // Heading
        $mform->addElement('html', '<div class="alert alert-primary">' . get_string('logsdownloadpreamble', 'report_gudata') . '</div>');

        // Dates.
        $mform->addElement('date_time_selector', 'logstart', get_string('from'), ['optional' => true]);
        $mform->addElement('date_time_selector', 'logend', get_string('to'), ['optional' => true]);

        // Submit and download
        $mform->addElement('submit', 'submit', get_string('downloadlogs', 'report_gudata'));
    }

}