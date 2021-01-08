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
        $student = $this->_customdata['student'];
        foreach($student->grades as $grdobj){
            if($this->_customdata['setting'] == '1'){
                $mform->addElement('static', $grdobj->activity, $grdobj->activity.' Weighting', '20%'); 
                $mform->setType($grdobj->activity, PARAM_NOTAGS); 
            }elseif($this->_customdata['setting'] == '0'){
                $attributes = array(
                    'class' => 'input-percent',
                    'type' => 'number',
                    'maxlength' => '3',
                    'minlength' => '1',
                    'size' => '4'
                );
                $mform->addElement('text', $grdobj->activity, $grdobj->activity.' Weighting', $attributes);
                $mform->setType($grdobj->activity, PARAM_INT);
                $mform->addRule($grdobj->activity, 'Numeric', 'numeric', null, 'server');
                $mform->setDefault($grdobj->activity, '20');
            }
        }
        if($this->_customdata['setting'] == '0'){
            $mform->addElement('static', 'totalweight', 'Total Weighting', '100%'); 
            $mform->setType('totalweight', PARAM_NOTAGS); 
        }
        $mform->addElement('html', '<div class="mform-grades">');
            foreach($student->grades as $grdobj){
                $mform->addElement('static', $grdobj->activity.'grade', $grdobj->activity .' Grade',  $grdobj->grade); 
                $mform->setType($grdobj->activity.'grade', PARAM_NOTAGS);
            }
            if($this->_customdata['setting'] == '0'){
                $mform->addElement('static', 'aggregatedgrade', 'Aggregated Grade', $student->aggregatedgrade->grade); 
                $mform->setType('aggregatedgrade', PARAM_NOTAGS); 
            }
        $mform->addElement('html', '</div>');

        if($this->_customdata['setting'] == '1'){
            $mform->addElement('static', 'aggregatedgrade', 'Aggregated Grade', $student->aggregatedgrade->grade); 
            $mform->setType('aggregatedgrade', PARAM_NOTAGS); 
            $mform->addElement('select', 'override', "Override Code", local_gugcat::$GRADES,['class' => 'mform-custom']); 
            $mform->setType('reasons', PARAM_NOTAGS); 
            $mform->setDefault('reasons', "Select Reason");
        }
        
        $mform->addElement('textarea', 'notes', get_string('notes', 'local_gugcat'));
        $mform->setType('notes', PARAM_NOTAGS);

        $mform->addElement('html', '</div>');
        $this->add_action_buttons(false, get_string('savechanges', 'local_gugcat'), ['class' => 'float-right']);

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
