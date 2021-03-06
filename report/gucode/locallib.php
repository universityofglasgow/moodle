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
 * GUID report
 *
 * @package    report_guid
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");


// Form definition for search.
class gucode_form extends moodleform {

    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        // Main part.
        $mform->addElement('html', '<div>'.get_string('instructions', 'report_gucode' ) );
        $mform->addElement('text', 'code', get_string('code', 'report_gucode') );
        $mform->setType('code', PARAM_ALPHANUM);

        // Action buttons.
        $this->add_action_buttons(true, get_string('search', 'report_guid'));
    }
}

/** 
 * Add visible to courses found
 */
function report_gucodes_visible($courses) {
    global $DB;

    foreach ($courses as $codecourse) {
        if ($course = $DB->get_record('course', array('id' => $codecourse->courseid))) {
            $codecourse->visible = $course->visible ? get_string('visible', 'report_gucode') : get_string('hidden', 'report_gucode');
            $codecourse->fullname = $course->fullname;
            $codecourse->shortname = $course->shortname;
            $codecourse->missing = 0;
        } else {
            $codecourse->visible = get_string('missing', 'report_gucode');
            $codecourse->fullname = '-';
            $codecourse->shortname = '-';
            $codecourse->missing = 1;
        }
    }

    return $courses;
}

