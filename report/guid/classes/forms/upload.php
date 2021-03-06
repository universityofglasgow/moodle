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

class upload extends moodleform {

    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        // File upload.
        $mform->addElement('header', 'guidupload', get_string('uploadheader', 'report_guid' ) );
        $mform->addElement('html', '<div class="alert">'.get_string('uploadinstructions', 'report_guid' ).'</div>' );
        $mform->addElement('filepicker', 'csvfile', get_string('csvfile', 'report_guid' ) );

        // Action buttons.
        $this->add_action_buttons(false, get_string('submitfile', 'report_guid'));
    }

}
