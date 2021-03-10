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
 * A moodleform allowing the editing of the grade options for an individual student
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/local/gugcat/locallib.php');

class uploadform extends moodleform {
    function definition (){

        $mform =& $this->_form;

        if (isset($this->_customdata)) {  // hardcoding plugin names here is hacky
            $features = $this->_customdata;
        } else {
            $features = array();
        }

        // course id and act id need to be passed for auth purposes
        $mform->addElement('hidden', 'id', required_param('id', PARAM_INT));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'activityid', required_param('activityid', PARAM_INT));
        $mform->setType('activityid', PARAM_INT);

        // Restrict the possible upload file types.
        if (!empty($features['acceptedtypes'])) {
            $acceptedtypes = $features['acceptedtypes'];
        } else {
            $acceptedtypes = '*';
        }

        // File upload.
        $mform->addElement('filepicker', 'userfile', get_string('selectfile', 'local_gugcat'), null, array('accepted_types' => $acceptedtypes));
        $mform->addRule('userfile', null, 'required');
        // Select delimiter
        if (!empty($features['includeseparator'])) {
            $radio = array();
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('septab', 'grades'), 'tab');
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcomma', 'grades'), 'comma');
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcolon', 'grades'), 'colon');
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepsemicolon', 'grades'), 'semicolon');
            $mform->addGroup($radio, 'separator', get_string('selectdelimiter', 'local_gugcat'), ' ', false);
            $mform->addHelpButton('separator', 'separator', 'grades');
            $mform->setDefault('separator', 'comma');
        }
        $mform->addElement('advcheckbox', 'ignorerow', get_string('ignorerow', 'local_gugcat'));
        $mform->setDefault('ignorerow', 1);

        $this->add_action_buttons(false, get_string('uploadgrades', 'grades'));
    }
}

class importform extends moodleform {
    function definition (){

        $mform =& $this->_form;

        // course id and act id need to be passed for auth purposes
        $mform->addElement('hidden', 'id', required_param('id', PARAM_INT));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'activityid', required_param('activityid', PARAM_INT));
        $mform->setType('activityid', PARAM_INT);
        $mform->addElement('hidden', 'iid', $this->_customdata['iid']);
        $mform->setType('iid', PARAM_INT);
        $mform->setConstant('iid', $this->_customdata['iid']);
        $mform->addElement('hidden', 'categoryid', optional_param('categoryid', null, PARAM_INT));
        $mform->setType('categoryid', PARAM_ACTION);

        $mform->addElement('select', 'reasons', get_string('selectreason', 'local_gugcat'), local_gugcat::get_reasons(), ['class' => 'mform-custom-select']); 
        $mform->setType('reasons', PARAM_NOTAGS); 
        $mform->setDefault('reasons', "Select Reason");

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('back'));
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('importfile', 'local_gugcat'));
        $mform->addGroup($buttonarray, 'buttonarr', '', array(''), false);
        
    }
}
