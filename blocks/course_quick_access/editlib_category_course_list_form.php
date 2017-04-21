<?php
// This file is part of course-quick-access plugin for Moodle
// http://moodle.org/
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
 * Contains the formular-class for the course-to-category assignments. 
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/formslib.php');

class edit_category_course_list_form extends moodleform {

    function definition() {
        $mform = $this->_form;
        $custom = $this->_customdata;
        // Get form custom data
        $userid = $custom['userid'];
        $courses = $custom['courses'];
        $categories = $custom['categories'];
        $catcourses = $custom['cat_courses'];
        // Get user-category names
        $catnames = array();
        $catnames[0] = get_string('coursenotcategorized', 
                                  'block_course_quick_access');
        foreach ($categories as $cat) {
            $catnames[$cat->id] = $cat->name;
        }
        // Add hidden form elements
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT); 
        $mform->addElement('hidden', 'action', 'savesettings');
        $mform->setType('action', PARAM_TEXT);
        // Add form elements
        $mform->addElement('header', 'courselistheadline', 
                           get_string('courselistheadline', 
                           'block_course_quick_access'));
        $mform->addElement('html', '<br>');
        // User not enrolled in any courses
        if(!$courses) {
            $mform->addElement('html', '<p>'.
                    '<img src="'.MOODLE_ICON_INFO_SRC.
                    '" class="icon" alt="" /> '.
                    get_string('nocourses', 'block_course_quick_access').
                    '</p>');
        } else { // Print info-message and course-to-category assignments
            $mform->addElement('html', '<p>'.
                    '<img src="'.MOODLE_ICON_INFO_SRC.
                    '" class="icon" alt="" /> '.
                    get_string('courselistdesc', 'block_course_quick_access').
                    '</p>');
            $mform->addElement('static', 'courselistdesc', 
                               get_string('courselistcoursehead', 
                                          'block_course_quick_access'), 
                               get_string('courselistselecthead', 
                                          'block_course_quick_access'));
            $i = 0; // TODO: Better loop
            foreach ($courses as $course) {
            //for($i = 0; $i < sizeof($courses); $i++) {
                $mform->addElement('select', 'categoryselect'.$course->id, 
                                   $course->fullname, 
                                   $catnames, null);

                // Course was already assigned before -> set assigned category
                if (!empty($catcourses[$i])) {
                    $default = $catcourses[$i]->catid;
                    $mform->setDefault('categoryselect'.$course->id, $default);
                }
                $i++;
            }
            // Add action buttons
            $buttons = array();
            $buttons[] =& $mform->createElement('submit', 'submitbutton', 
                                                get_string('savechanges'));
            $mform->addGroup($buttons, 'actionbuttons', '', array(' '), false);
            $mform->closeHeaderBefore('actionbuttons');
        }
    }
    
}
