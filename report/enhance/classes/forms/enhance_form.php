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

use \stdClass;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");

class enhance_form extends \moodleform {

    public function definition() {
        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 

        // custom data
        $customdata = $this->_customdata;
        $course = $customdata['course'];
        $request = $customdata['request'];
        $entry = $customdata['entry'];

        // Course id
        $mform->addElement('hidden', 'courseid', $course->id);
        $mform->setType('courseid', PARAM_INT);

        // request id
        if ($request) {
            $mform->addElement('hidden', 'id', $request->id);
            $mform->setType('id', PARAM_INT);
        }

	if (!$request) {
            $mform->addElement('html', '<div class="alert alert-danger">' . get_string('donotuse', 'report_enhance') . '</div>');
        }

        // Headline / Summary
        $headline = $mform->addElement('text', 'headline', get_string('headline', 'report_enhance'), ['size' => 80]);
        $mform->setType('headline', PARAM_RAW);
        $mform->addRule('headline', null, 'required', null, 'client');
        if ($request) {
            $headline->setValue($request->headline);
        }

        // description
        $editor = $mform->addElement('editor', 'description', get_string('description', 'report_enhance'));
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', null, 'required', null, 'client');
        if ($request) {
            $editor->setValue(array('text' => $request->description));
        }
        
        // benefits
        $benefits = $mform->addElement('editor', 'benefits', get_string('benefits', 'report_enhance'));
        $mform->setType('benefits', PARAM_RAW);
        $mform->addRule('benefits', null, 'required', null, 'client');
        if ($request) {
            $benefits->setValue(array('text' => $request->benefits));
        }

        // College or School
        $department = $mform->addElement('text', 'department', get_string('department', 'report_enhance'), ['size' => 55, 'maxlength' => 50]);
        $mform->setType('department', PARAM_TEXT);
        $mform->addRule('department', null, 'required', null, 'client');
        if ($request) {
            $department->setValue($request->department);
        }

        // Files
        $filemanager = $mform->addElement('filemanager', 'attachments_filemanager', get_string('attachments', 'report_enhance'), null, ['subdirs' => 0]);
        $filemanager->setValue($entry->attachments_filemanager);
       
        $this->add_action_buttons(); 
    }
}

