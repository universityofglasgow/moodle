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
        $services = $customdata['services'];
        $audiences = $customdata['audiences'];

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

        // Service
        $mform->addElement('select', 'service', get_string('service', 'report_enhance'), $services);
        $mform->setType('service', PARAM_INT);
        $mform->addHelpButton('service', 'service', 'report_enhance');

        // College or School
        $department = $mform->addElement('text', 'department', get_string('department', 'report_enhance'), ['size' => 55, 'maxlength' => 50]);
        $mform->setType('department', PARAM_TEXT);
        $mform->addRule('department', null, 'required', null, 'client');
        if ($request) {
            $department->setValue($request->department);
        }

        // Audience
        $mform->addElement('select', 'audience', get_string('audience', 'report_enhance'), $audiences);
        $mform->setType('audience', PARAM_INT);
        $mform->addHelpButton('audience', 'audience', 'report_enhance');

        // description
        $editor = $mform->addElement('editor', 'description', get_string('description', 'report_enhance'));
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', null, 'required', null, 'client');
        if ($request) {
            $editor->setValue(array('text' => $request->description));
        }

        // transferability
        $editor = $mform->addElement('editor', 'transferability', get_string('transferability', 'report_enhance'));
        $mform->setType('transferability', PARAM_RAW);
        $mform->addRule('transferability', null, 'required', null, 'client');
        $mform->addHelpButton('transferability', 'transferability', 'report_enhance');
        if ($request) {
            $editor->setValue(array('text' => $request->transferability));
        }
        
        // benefits
        $benefits = $mform->addElement('editor', 'benefits', get_string('benefits', 'report_enhance'));
        $mform->setType('benefits', PARAM_RAW);
        $mform->addRule('benefits', null, 'required', null, 'client');
        if ($request) {
            $benefits->setValue(array('text' => $request->benefits));
        }


        // Files
        $filemanager = $mform->addElement('filemanager', 'attachments_filemanager', get_string('attachments', 'report_enhance'), null, ['subdirs' => 0]);
        $filemanager->setValue($entry->attachments_filemanager);
       
        $this->add_action_buttons(); 
    }
}

