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
 * A moodleform allowing the editing of the grade options for an individual grade item
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
class coursegradeform extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('html', '<div class="mform-container">');

        $i = 0;
        foreach($this->_customdata['activities'] as $activity){

            $studIndex = key($this->_customdata['rows']);

            $mform->addElement('html', '<div class="mform-grades">');
                $mform->addElement('html', '<div class="mform-activities">');
                    if($this->_customdata['setting'] == '1'){
                        $mform->addElement('static', $activity->name.'weight', $activity->name .' Weighting', '20%'); 
                        $mform->setType($activity->name.'weight', PARAM_NOTAGS); 
                    }
                    elseif($this->_customdata['setting'] == '0'){
                        $mform->addElement('text', $activity->name.'weight', $activity->name .' Weighting', ['class' => 'mform-custom']); 
                        $mform->setType($activity->name.'weight', PARAM_NOTAGS); 
                        $mform->setDefault($activity->name.'weight', '20');
                    }
                $mform->addElement('html', '</div>');
                $mform->addElement('html', '<div class="mform-activities">');
                    $mform->addElement('static', $activity->name.'grade', $activity->name .' Grade',  $this->_customdata['rows'][$studIndex]->grades[$i]); 
                    $mform->setType($activity->name.'grade', PARAM_NOTAGS); 
                $mform->addElement('html', '</div>');
            $mform->addElement('html', '</div>');
            $i++;
        }

        if($this->_customdata['setting'] == '1'){
            $mform->addElement('static', 'aggregatedgrade', 'Aggregated Grade', $this->_customdata['rows'][$studIndex]->aggregatedgrade); 
            $mform->setType('aggregatedgrade', PARAM_NOTAGS); 
            $mform->addElement('select', 'override', "Override Code", local_gugcat::$GRADES,['class' => 'mform-custom']); 
            $mform->setType('reasons', PARAM_NOTAGS); 
            $mform->setDefault('reasons', "Select Reason");
        }
        
        $mform->addElement('textarea', 'notes', get_string('notes', 'local_gugcat'));
        $mform->setType('notes', PARAM_NOTAGS);

        $mform->addElement('html', '</div>');
        $this->add_action_buttons(false, get_string('confirmgrade', 'local_gugcat'), ['class' => 'float-right']);

        // hidden params
        $mform->addElement('hidden', 'studentid', $this->_customdata['studentid']);
        $mform->setType('studentid', PARAM_ACTION);
        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_ACTION);
        $mform->addElement('hidden', 'setting', $this->_customdata['setting']);
        $mform->setType('setting', PARAM_ACTION);
        
    function validation($data, $files) {
        return array();
        }
    }
}
