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
 * VLE Enhancement Requests
 *
 * @package    report_enhance
 * @subpackage guenrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_enhance\forms;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");
 
class review_form extends \moodleform {

    public function definition() {
        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 

        // custom data
        $customdata = $this->_customdata;
        $course = $customdata['course'];
        $request = $customdata['request'];
        $fields = $customdata['fields'];
        $statuses = $customdata['statuses'];
        $entry = $customdata['entry'];

        // Course id
        $mform->addElement('hidden', 'courseid', $course->id);
        $mform->setType('courseid', PARAM_INT);

        // Request id
        $mform->addElement('hidden', 'id', $request->id);
        $mform->setType('id', PARAM_INT);

        // Status
        $mform->addElement('select', 'status', get_string('status', 'report_enhance'), $statuses)->setValue($request->status);
        $mform->setType('status', PARAM_INT);

        // Loop over fields
        foreach ($fields as $field) {
            $mform->addElement('editor', $field, get_string($field, 'report_enhance'))->setValue(array('text' => $request->$field));
            $mform->setType($field, PARAM_RAW);
            $mform->addHelpButton($field, $field, 'report_enhance');
        }

        // Priority
        $mform->addElement('select', 'priority', get_string('priority', 'report_enhance'), \report_enhance\lib::getpriorities())
            ->setValue($request->priority);
        $mform->setType('priority', PARAM_INT);

        // Files
        $filemanager = $mform->addElement('filemanager', 'attachments_filemanager', get_string('attachments', 'report_enhance'), null, ['subdirs' => 0]);
        $filemanager->setValue($entry->attachments_filemanager);
        
        $this->add_action_buttons(); 
    }
}

