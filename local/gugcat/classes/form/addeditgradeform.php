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
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
class addeditgradeform extends moodleform {
    public $is_converted = false;
    //Add elements to form
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('html', '<div class="mform-container">');
        $activity = $this->_customdata['activity'];
        $mform->addElement('select', 'reasons', get_string('reasonaddgrade', 'local_gugcat'), local_gugcat::get_reasons(),['class' => 'mform-custom-select']); 
        $mform->setType('reasons', PARAM_NOTAGS); 
        $mform->setDefault('reasons', "Select Reason");   

        $mform->addElement('text', 'otherreason', get_string('reasonother', 'local_gugcat'), ['class' => 'mform-custom']); 
        $mform->setType('otherreason', PARAM_NOTAGS); 
        $mform->hideIf('otherreason', 'reasons', 'neq', 8); 
        $mform->addElement('hidden', 'gradetype', $activity->gradeitem->gradetype); 
        $mform->setType('gradetype', PARAM_NOTAGS);
        $this->is_converted = !is_null($activity->gradeitem->iteminfo);
        if($activity->gradeitem->gradetype == GRADE_TYPE_VALUE && !$this->is_converted){
            $gm = intval($activity->gradeitem->grademax);
            $mform->addElement('hidden', 'grademax', $gm); 
            $mform->setType('grademax', PARAM_NOTAGS);
            $attributes = array(
                'pattern' => '^([mM][vV]|[0-9]|[nN][sS])+$', 
                'size' => '16', 
                'placeholder' => get_string('typegrade', 'local_gugcat'),
                'data-toggle' => 'tooltip',
                'data-placement' => 'right',
                'data-html' => 'true',
                'maxlength' => strlen($gm),
                'minlength' => '1',
                'title' => get_string('gradetooltip', 'local_gugcat')
            );
            $mform->addElement('text', 'grade', get_string('gradeformgrade', 'local_gugcat'), $attributes); 
            $mform->setType('grade', PARAM_NOTAGS);
            $mform->addRule('grade', get_string('errorinputpoints', 'local_gugcat'), 'regex', '/^([mM][vV]|[0-9]|[nN][sS])+$/', 'client');
            $mform->addRule('grade', get_string('errorinputpoints', 'local_gugcat'), 'regex', '/^([mM][vV]|[0-9]|[nN][sS])+$/', 'server');
        }else{
            $mform->addElement('select', 'grade', get_string('gradeformgrade', 'local_gugcat'), local_gugcat::$GRADES, array('class' => 'mform-custom-select', 'size' => '15')); 
            $mform->setType('grade', PARAM_NOTAGS); 
            $mform->setDefault('grade', "Select Grade");
        }

        $mform->addElement('textarea', 'notes', get_string('notes', 'local_gugcat'));
        $mform->setType('notes', PARAM_NOTAGS);

        $mform->addElement('html', '</div>');
        $this->add_action_buttons(false, get_string('confirmgrade', 'local_gugcat'), ['class' => 'float-right']);
        //hidden params
        $mform->addElement('hidden', 'studentid', $this->_customdata['studentid']);
        $mform->setType('studentid', PARAM_ACTION);
        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_ACTION);
        $mform->addElement('hidden', 'activityid', required_param('activityid', PARAM_INT));
        $mform->setType('activityid', PARAM_INT);
        $mform->addElement('hidden', 'childactivityid', optional_param('childactivityid', 0, PARAM_INT));
        $mform->setType('childactivityid', PARAM_INT);
        $mform->addElement('hidden', 'categoryid', $this->_customdata['categoryid']);
        $mform->setType('categoryid', PARAM_ACTION);
        $mform->addElement('hidden', 'page', $this->_customdata['page']);
        $mform->setType('page', PARAM_ACTION);
        if(isset($this->_customdata['overview'])){
            $mform->addElement('hidden', 'overview', $this->_customdata['overview']);
            $mform->setType('overview', PARAM_ACTION);
        }
    }    
        
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $newgrade = $data['grade'];
        if ($data['gradetype'] == GRADE_TYPE_VALUE && !$this->is_converted && is_numeric($newgrade) && $newgrade > $data['grademax']) {
            $errors['grade'] = get_string('errorinputpoints', 'local_gugcat');
        }  
        return $errors;
    }
}

