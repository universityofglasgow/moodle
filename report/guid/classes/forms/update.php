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
 * Update form
 *
 * @package    report
 * @subpackage guid
 * @copyright  2017 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_guid\forms;

defined('MOODLE_INTERNAL') || die;

use \moodleform;

class update extends moodleform {

    public function definition() {
        global $CFG;

        $mform =& $this->_form;
        $user = $this->_customdata['user'];

        // Main part.
        $mform->addElement('html', '<div class="alert alert-info">' .
            get_string('changeuserdesc', 'report_guid', fullname($user) ) . '</div>' );
        $mform->addElement('text', 'currentusername',
            get_string('currentusername', 'report_guid'), array('disabled' => 'disabled'));
        $mform->setType('currentusername', PARAM_TEXT);
        $mform->setDefault('currentusername', $user->username);

        $mform->addElement('text', 'newusername', get_string('newusername', 'report_guid'));
        $mform->setType('newusername', PARAM_TEXT);

        $mform->addElement('hidden', 'userid', $user->id);
        $mform->setType('userid', PARAM_INT);

        $this->add_action_buttons();
    }
}
