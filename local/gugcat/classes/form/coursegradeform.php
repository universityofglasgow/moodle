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

use local_gugcat\grade_aggregation;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
class coursegradeform extends moodleform {
    //Add elements to form
    public function definition() {
        $act = optional_param('activityid', null, PARAM_INT);
        if(!is_null($act)){
            $grade = local_gugcat::$GRADES;
        }else{
            $grade = local_gugcat::$GRADES + grade_aggregation::$AGGRADE;
            unset($grade[NON_SUBMISSION]);
        }
        
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('html', '<div class="mform-container">');
        $student = $this->_customdata['student'];
        if ($this->_customdata['setting'] == '1')
            $mform->addElement('html', '<div class="mform-override">');
        foreach($student->grades as $grdobj){

            if($this->_customdata['setting'] == '1' || $grdobj->category){
                $mform->addElement('html', '<div class="mform-override">');
                $mform->addElement('static', $grdobj->activity, $grdobj->activity.' Weighting', $grdobj->weight .'%'); 
                $mform->addElement('html', '</div>');
                $mform->setType($grdobj->activity, PARAM_NOTAGS); 
            }elseif($this->_customdata['setting'] == '0'){
                $attributes = array(
                    'class' => 'input-percent',
                    'type' => 'number',
                    'maxlength' => '3',
                    'minlength' => '1',
                    'size' => '6',
                    'pattern' => '[0-9]+'
                );
                $mform->addElement('text', 'weights['.$grdobj->activityid.']', $grdobj->activity.' Weighting', $attributes);
                $mform->setType('weights['.$grdobj->activityid.']', PARAM_INT);
                $mform->addRule('weights['.$grdobj->activityid.']', null, 'numeric', null, 'client');
                $mform->addRule('weights['.$grdobj->activityid.']', get_string('errorfieldnumbers', 'local_gugcat'), 'regex', '/^[0-9]+$/', 'client');
                $mform->addRule('weights['.$grdobj->activityid.']', get_string('errorfieldnumbers', 'local_gugcat'), 'regex', '/^[0-9]+$/', 'server');
                $mform->setDefault('weights['.$grdobj->activityid.']', $grdobj->weight);
            }
        }

        if($this->_customdata['setting'] == '0'){
            $mform->addElement('static', 'totalweight', get_string('totalweight', 'local_gugcat'), '100%'); 
            $mform->setType('totalweight', PARAM_NOTAGS); 
        }
        $mform->addElement('html', '<div class="mform-grades">');
            foreach($student->grades as $grdobj){
                if ($grdobj->category){
                    $mform->addElement('html', '<div class="mform-override">');
                }
                $mform->addElement('static', $grdobj->activity.'grade', $grdobj->activity .' Grade',  $grdobj->grade); 
                if ($grdobj->category){
                    $mform->addElement('html', '</div>');
                }
                $mform->setType($grdobj->activity.'grade', PARAM_NOTAGS);
            }
            if($this->_customdata['setting'] == '0'){
                $mform->addElement('static', 'aggregatedgrade', get_string('aggregatedgrade', 'local_gugcat'), $student->aggregatedgrade->grade); 
                $mform->setType('aggregatedgrade', PARAM_NOTAGS); 
            }
        $mform->addElement('html', '</div>');
    
        if($this->_customdata['setting'] == '1'){
            $mform->addElement('static', 'aggregatedgrade', get_string('aggregatedgrade', 'local_gugcat'), $student->aggregatedgrade->grade); 
            $mform->setType('aggregatedgrade', PARAM_NOTAGS); 
            $mform->addElement('html', '</div>');
            $mform->addElement('hidden', 'gradetype', $this->_customdata['gradetype']); 
            $mform->setType('gradetype', PARAM_NOTAGS);
            if(!is_null($this->_customdata['gradetype']) && $this->_customdata['gradetype'] == GRADE_TYPE_VALUE){
                $attributes = array(
                    'pattern' => '^([mM][vV]|[0-9]|[nN][sS])+$', 
                    'size' => '16', 
                    'placeholder' => get_string('typegrade', 'local_gugcat'),
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'right',
                    'data-html' => 'true',
                    'maxlength' => '3',
                    'minlength' => '1',
                    'title' => get_string('gradetooltip', 'local_gugcat')
                );
                $mform->addElement('text', 'override', get_string('gradeformgrade', 'local_gugcat'), $attributes); 
                $mform->setType('override', PARAM_NOTAGS);
                $mform->addRule('override', get_string('errorinputpoints', 'local_gugcat'), 'regex', '/^([mM][vV]|[0-9]|[nN][sS])+$/', 'client');
                $mform->addRule('override', get_string('errorinputpoints', 'local_gugcat'), 'regex', '/^([mM][vV]|[0-9]|[nN][sS])+$/', 'server');    
            }else{
                $mform->addElement('select', 'override', get_string('overridegrade', 'local_gugcat'), $grade, ['class' => 'mform-custom-select']); 
                $mform->setType('override', PARAM_NOTAGS); 
            }
        }
        $mform->addElement('textarea', 'notes', get_string('notes', 'local_gugcat'));
        $mform->setType('notes', PARAM_NOTAGS); 

        $mform->addElement('html', '</div>');
        if($this->_customdata['setting'] == '1'){
            $mform->addElement('submit', 'submit', get_string('savechanges', 'local_gugcat'), ['class' => 'btn-coursegradeform']);
        }else{
            $mform->addElement('submit', 'submit', get_string('savechanges', 'local_gugcat'), ['id' => 'coursegradeform-submit', 'class' => 'btn-coursegradeform']);
            $mform->addElement('button', 'adjustoverride', get_string('savechanges', 'local_gugcat'), ['id' => 'btn-coursegradeform', 'class' => 'btn-coursegradeform']);
        }
        // hidden params
        $mform->addElement('hidden', 'studentid', $this->_customdata['studentid']);
        $mform->setType('studentid', PARAM_ACTION);
        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_ACTION);
        $mform->addElement('hidden', 'setting', $this->_customdata['setting']);
        $mform->setType('setting', PARAM_ACTION);
        $mform->addElement('hidden', 'cnum', $student->cnum);
        $mform->setType('cnum', PARAM_ACTION);
        $mform->addElement('hidden', 'categoryid', $this->_customdata['categoryid']);
        $mform->setType('categoryid', PARAM_ACTION);
        $mform->addElement('hidden', 'activityid', optional_param('activityid', null, PARAM_INT));
        $mform->setType('activityid', PARAM_INT);
        
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if($data['setting'] == 1){
            $newgrade = $data['override'];
            // Grademax is always 100 for subcategory grade point
            if ($data['gradetype'] == GRADE_TYPE_VALUE && is_numeric($newgrade) && $newgrade > 100) {
                $errors['override'] = get_string('errorinputpoints', 'local_gugcat');
            } 
        }
             
        return $errors;
    }
}
