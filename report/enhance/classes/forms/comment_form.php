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

class comment_form extends \moodleform {

    public function definition() {
        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 

        // custom data
        $customdata = $this->_customdata;
        $course = $customdata['course'];
        $request = $customdata['request'];
        $comment = $customdata['comment'];

        // Hidden stuff
        $mform->addElement('hidden', 'courseid', $course->id);
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'id', $request->id);
        $mform->setType('id', PARAM_INT);
        if ($comment) {
            $mform->addElement('hidden', 'commentid', $comment->id);
            $mform->setType('commentid', PARAM_INT);
        }

        // comment
        $editor = $mform->addElement('editor', 'comment', get_string('comment', 'report_enhance'));
        $mform->setType('comment', PARAM_RAW);
        $mform->addRule('comment', null, 'required', null, 'client');
        if ($comment) {
            $editor->setValue(['text' => $comment->comment]);
        }
       
        $this->add_action_buttons(); 
    }
}

