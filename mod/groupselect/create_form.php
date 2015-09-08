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
 * Group self selection - group creation form
*
* @package    mod
* @subpackage groupselect
* @copyright  2014 Tampere University of Technology, P. Pyykkönen (pirkka.pyykkonen ÄT tut.fi)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');

class create_form extends moodleform {
    
    const DESCRIPTION_MAXLEN = 1024;
    const PASSWORD_MAXLEN = 254;
    
	function definition() {	    
	    
	    $mform = $this->_form;
		list($data, $this->groupselect) = $this->_customdata;
        
		$mform->addElement('hidden','id');
		$mform->setType('id', PARAM_INT);
		
                if($this->groupselect->studentcansetdesc) {
		$mform->addElement('textarea', 'description', get_string('description', 'mod_groupselect'), array('wrap'=>'virtual', 'maxlength'=>self::DESCRIPTION_MAXLEN-1, 'rows'=>'3', 'cols'=>'25', ''));
                }
                else {
                    $mform->addElement('hidden', 'description', '');
                }
                $mform->setType('description', PARAM_NOTAGS);
                
                
		$mform->addElement('passwordunmask', 'password', get_string('password'), array('maxlength'=>self::PASSWORD_MAXLEN-1, 'size'=>"24"));
		$mform->setType('password', PARAM_RAW);
		
		$this->add_action_buttons(true, get_string('creategroup', 'mod_groupselect'));
		$this->set_data($data);
	}

	function validation($data, $files) {
 		$errors = parent::validation($data, $files);
        
 		$description = $data['description'];
  		if(strlen($description) > self::DESCRIPTION_MAXLEN) {
 		    $errors['description'] = get_string('maxcharlenreached', 'mod_groupselect');
 		}
        $password = $data['password'];
        if(strlen($password) > self::PASSWORD_MAXLEN) {
            $errors['password'] = get_string('maxcharlenreached', 'mod_groupselect');
        }

		return $errors;
	}
}
