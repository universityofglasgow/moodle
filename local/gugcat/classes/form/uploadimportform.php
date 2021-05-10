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
        $mform->addElement('html', '<div class="mform-container">');

        if (isset($this->_customdata)) {  // hardcoding plugin names here is hacky
            $features = $this->_customdata;
        } else {
            $features = array();
        }

        $gradetypestr = array(
            GRADE_TYPE_TEXT => get_string('modgradetypenone', 'grades'),
            GRADE_TYPE_SCALE => get_string('modgradetypescale', 'grades'),
            GRADE_TYPE_VALUE => get_string('modgradetypepoint', 'grades'),
        );

        // course id and act id need to be passed for auth purposes
        $mform->addElement('hidden', 'id', required_param('id', PARAM_INT));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'activityid', required_param('activityid', PARAM_INT));
        $mform->setType('activityid', PARAM_INT);
        $mform->addElement('hidden', 'childactivityid', optional_param('childactivityid', null, PARAM_INT));
        $mform->setType('childactivityid', PARAM_INT);
        $mform->addElement('hidden', 'categoryid', optional_param('categoryid', null, PARAM_INT));
        $mform->setType('categoryid', PARAM_INT);

        // Restrict the possible upload file types.
        if (!empty($features['acceptedtypes'])) {
            $acceptedtypes = $features['acceptedtypes'];
        } else {
            $acceptedtypes = '*';
        }

        $activity = $features['activity'];
        $mform->addElement('static', 'assessment', get_string('assessment'), $activity->name); 
        $mform->setType('assessment', PARAM_NOTAGS); 
        $mform->addElement('static', 'gradetype', get_string('gradetype', 'grades'), $gradetypestr[$activity->gradeitem->gradetype]); 
        $mform->setType('gradetype', PARAM_NOTAGS); 
        if($activity->gradeitem->gradetype == GRADE_TYPE_VALUE){
            $mform->addElement('static', 'maximumgrade', get_string('grademax', 'grades'), intval($activity->gradeitem->grademax)); 
            $mform->setType('maximumgrade', PARAM_NOTAGS); 
        }
        $mform->addElement('static', 'step', get_string('step', 'local_gugcat', $a=1), get_string('downloadtempcsv', 'local_gugcat')); 
        // Download template button row
        global $PAGE;
        $dlnote = get_string($activity->modname == 'assign' ? 'downloadtempnoteA': 'downloadtempnoteB', 'local_gugcat');
        $dlurl = 'index.php?'.parse_url($PAGE->url, PHP_URL_QUERY).'&download=1';
        $dlurl = str_replace('&amp;', '&', $dlurl);
        $dlbtn = html_writer::tag('div', html_writer::tag('a', html_writer::tag('button', get_string('downloadcsv', 'local_gugcat'), 
            array('class' => 'btn btn-form-default', 'type' => 'button')), array('href' => $dlurl)), array('class' => 'col-sm-3'));
        $download = html_writer::tag('div', 
            $dlbtn.
            html_writer::tag('span', $dlnote, array('class' => 'col-sm-7 small font-italic'))
            , array('class' => 'row'));
        $mform->addGroup(array($mform->createElement('html', $download)));
        
        $mform->addElement('static', 'step', get_string('step', 'local_gugcat', $a=2), get_string('uploadfile', 'local_gugcat')); 
        $mform->setType('step', PARAM_NOTAGS); 

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
        $mform->addElement('html', '</div>');

        $mform->addElement('submit', 'submit', get_string('uploadgrades', 'grades'), ['class' => 'btn-blue']);
    }
}

class importform extends moodleform {
    function definition (){

        $mform =& $this->_form;
        $mform->addElement('html', '<div class="mform-container">');
        // course id and act id need to be passed for auth purposes
        $mform->addElement('hidden', 'id', required_param('id', PARAM_INT));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'activityid', required_param('activityid', PARAM_INT));
        $mform->setType('activityid', PARAM_INT);
        $mform->addElement('hidden', 'iid', $this->_customdata['iid']);
        $mform->setType('iid', PARAM_INT);
        $mform->setConstant('iid', $this->_customdata['iid']);
        $mform->addElement('hidden', 'childactivityid', optional_param('childactivityid', null, PARAM_INT));
        $mform->setType('childactivityid', PARAM_INT);
        $mform->addElement('hidden', 'categoryid', optional_param('categoryid', null, PARAM_INT));
        $mform->setType('categoryid', PARAM_INT);
        $mform->addElement('hidden', 'page', optional_param('page', 0, PARAM_INT));
        $mform->setType('page', PARAM_INT);

        $reasons = local_gugcat::get_reasons();
        $reasons[0] = get_string('selectreason', 'local_gugcat');
        $mform->addElement('select', 'reasons', get_string('reasonaddgrade', 'local_gugcat'), $reasons,['class' => 'mform-custom-select']); 
        $mform->setType('reasons', PARAM_NOTAGS); 
        $mform->setDefault('reasons', 0);   
        $mform->addRule('reasons', get_string('required'), 'nonzero', null, 'client');     
        $mform->addRule('reasons', null, 'required', null, 'client');    

        $mform->addElement('text', 'otherreason', get_string('reasonother', 'local_gugcat'), array('size' => 16, 'placeholder' => get_string('pleasespecify', 'local_gugcat')));
        $mform->setType('otherreason', PARAM_NOTAGS); 
        $mform->hideIf('otherreason', 'reasons', 'neq', 9); 
        $mform->addElement('html', '</div>');
        
        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('back'));
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('importfile', 'local_gugcat'));
        $mform->addGroup($buttonarray, 'buttonarr', '', array(''), false);
        
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['reasons'] == 9 && empty($data['otherreason'])) {
            $errors['otherreason'] = get_string('required');
        }  
        return $errors;
    }
}
