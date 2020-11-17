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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kuracloud;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Course mapping search form
 *
 * @copyright 2017 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursemapping_form extends \moodleform {

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $courses = (new courses)->get_all_unmapped(required_param('course', PARAM_INT));

        $options = array();
        $options[] = get_string('remotecoursedefault', 'block_kuracloud');

        foreach ($courses as $course) {
            $id = $course->instanceId.'-'.$course->courseId;
            $options[$id] = $course->name;
        }

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);

        $mform->addElement('autocomplete', 'remotecourse', get_string('remotecourse', 'block_kuracloud'), $options);
        $mform->addRule('remotecourse', get_string('required'), 'required');

        $this->add_action_buttons();
    }
}