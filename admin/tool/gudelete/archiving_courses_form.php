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
 * TODO describe file archiving_courses_form
 *
 * @package    tool_gudelete
 * @copyright  2024 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden');
}

require_once($CFG->libdir.'/formslib.php');

class archiving_courses_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        $config = get_config('tool_gudelete');
        $archivement = new tool_gudelete\course_archiving_helper();
        $result = $archivement->check_courses($config);

        $a = new stdClass();
        $a->archived = '';
        $a->deleted = '';

        if (!empty($result->archive)) {
            $archive_list = '';
            foreach ($result->archive as $course) {
                $archive_list .= $course->fullname."<br />";
            }
            $a->archived = $archive_list;
        }
        if (!empty($result->delete)) {
            $delete_list = '';
            foreach ($result->delete as $course) {
                $delete_list .= $course->fullname."<br />";
                $a->deleted = $delete_list;
            }
        }
        
        $mform->addElement('header', '', get_string('confirm_header', 'tool_gudelete'));
        if (empty($result->archive) && empty($result->delete)) {
            $mform->addElement('static', '', '', get_string('nothing_to_archive', 'tool_gudelete'));
        } else {
            $mform->addElement('static', '', '', get_string('confirm_archiving', 'tool_gudelete', $a));
        }

        $mform->addElement('submit', 'submitbutton', get_string('archive', 'tool_gudelete'));
        $mform->addElement('cancel', 'cancelbutton');
    }

}