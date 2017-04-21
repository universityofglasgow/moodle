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
 * Contains the formular-class for the user-category creation/editing.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/formslib.php');

class edit_category_form extends moodleform {

    function definition() {
        $mform = $this->_form;
        $custom = $this->_customdata;
        // Get form custom data
        $id = $custom['id'];
        $userid = $custom['userid'];
        // Add hidden fields
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT); 
        $mform->addElement('hidden', 'visible', 1);
        $mform->setType('visible', PARAM_INT); 
        $mform->addElement('hidden', 'action', 'savesettings');
        $mform->setType('action', PARAM_TEXT);
        // Add form elements
        $mform->addElement('header', 'newcategory', 
                           get_string('createoreditcategory', 
                                      'block_course_quick_access'));
        
        $mform->addElement('text', 'newcategoryname', 
                           get_string('newcategory', 
                                      'block_course_quick_access'), 
                           'maxlength="25" size="25"');
        $mform->setType('newcategoryname', PARAM_TEXT);
        //$mform->addElement('advcheckbox', 'visible', 
        //                   get_string('categoriyisvisible', 
        //                              'block_course_quick_access'), 
        //                    null, null, array(0, 1));
        //$mform->setDefault('visible', 1);
        // Add validation: Not empty, max size: 25 & no special characters
        $mform->addRule('newcategoryname', get_string('err_required', 'form'), 
                        'required', null, 'client');
        //$mform->addRule('newcategoryname', 
        //                get_string('nospecialchars', 
        //                           'block_course_quick_access'), 
        //                'regex', '/^[a-zA-Z0-9äöüÄÖÜ\-\/]*$/', 'client');
        $mform->addRule('newcategoryname', get_string('err_maxlength', 'form'), 
                        'maxlength', 25, 'client');
        // Add form action buttons
        $buttons = array();
        $buttons[] =& $mform->createElement('submit', 'submitbutton', 
                                            get_string('savechanges'));
        $buttons[] =& $mform->createElement('cancel');
        $mform->addGroup($buttons, 'actionbuttons', '', array(' '), false);
        $mform->closeHeaderBefore('actionbuttons');
    }

}
