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

class alternativegradeform extends moodleform {
    public function definition () {

        $mform =& $this->_form;
        $mform->addElement('html', '<div class="mform-container">');

        if (isset($this->_customdata)) {  // Hardcoding plugin names here is hacky.
            $features = $this->_customdata;
        } else {
            $features = array();
        }
        $activities = $features['activities'];
        $altgradetypes = array(
            0 => get_string('selectaltgrdtype', 'local_gugcat'),
            MERIT_GRADE => get_string('meritgrade', 'local_gugcat'),
            GPA_GRADE => get_string('gpagrade', 'local_gugcat'),
        );
        $adjust = optional_param('alternative', null, PARAM_INT);
        $altgtattr = array(
            'id' => 'select-alt-grade',
            'class' => 'mform-custom-select'
        );
        if (!is_null($adjust) && $adjust != 0) {
            $altgtattr['disabled'] = true;
        }
        $mform->addElement('select', 'altgradetype', get_string('selectaltgrdtype', 'local_gugcat'), $altgradetypes, $altgtattr);
        $mform->setType('altgradetype', PARAM_NOTAGS);

        // Check for existing settings for merit/gpa.
        $meritsettings = $features['meritsettings'];
        $gpasettings = $features['gpasettings'];
        if ($meritsettings) {
            $mform->setDefault('altgradetype', MERIT_GRADE);
        } else if ($gpasettings) {
            $mform->setDefault('altgradetype', GPA_GRADE);
        } else {
            $mform->setDefault('altgradetype', 0);
        }

        // Merit Grade type form elements.
        $mweights = !is_null($meritsettings) ? array_column($meritsettings, 'weight', 'itemid') : array();

        $mform->addElement('html', html_writer::tag('label', get_string('selectincludeassessment', 'local_gugcat'),
                 array('class' => 'merit-lbl hidden')));
        $mform->hideif('selectassessment', 'altgradetype', 'neq', MERIT_GRADE);
        $weightattr = array(
            'class' => 'input-percent',
            'type' => 'number',
            'maxlength' => '3',
            'minlength' => '1',
            'size' => '6',
            'pattern' => '[0-9]+'
        );
        foreach ($activities as $act) {
            $selectedm = array_key_exists($act->gradeitemid, $mweights) ? $mweights[$act->gradeitemid] : false;
            $cbfield = array();
            $cbfield[] =& $mform->createElement('advcheckbox', "merits[$act->gradeitemid]", $act->name, null,
                         array('data-itemid' => $act->gradeitemid, 'class' => 'checkbox-field'));
            $cbfield[] =& $mform->createElement('text', "weights[$act->gradeitemid]", null, $weightattr);
            $mform->addGroup($cbfield, 'cbfield', '', array(''), false);
            $mform->setType("weights[$act->gradeitemid]", PARAM_INT);
            $mform->setDefault("weights[$act->gradeitemid]", ($selectedm ? floatval($selectedm * 100) : 0));
            $mform->disabledIf("weights[$act->gradeitemid]", "merits[$act->gradeitemid]", 'notchecked');
            $mform->setDefault("merits[$act->gradeitemid]", ($selectedm ? 1 : 0));
            $mform->hideif('cbfield', 'altgradetype', 'neq', MERIT_GRADE);
        }
        $totalweight = $mform->createElement('html',  html_writer::tag('span', '100%', array('class' => 'total-weight')));
        $mform->addGroup(array($totalweight), 'totalfield', get_string('totalweight', 'local_gugcat'), array(''), false);
        $mform->hideif('totalfield', 'altgradetype', 'neq', MERIT_GRADE);

        // GPA Grade type form elements.
        $gcap = !is_null($gpasettings) ? array_column($gpasettings, 'cap', 'itemid') : array();
        $mform->addElement('html', html_writer::tag('label', get_string('pleaseindicateresits', 'local_gugcat'),
                 array('class' => 'gpa-lbl hidden')));

        foreach ($activities as $act) {
            $selectedg = array_key_exists($act->gradeitemid, $gcap) ? $gcap[$act->gradeitemid] : false;
            $mform->addElement('advcheckbox', "resits[$act->gradeitemid]", $act->name);
            $mform->setDefault("resits[$act->gradeitemid]", ($selectedg ? 1 : 0));
            $mform->setType("resits[$act->gradeitemid]", PARAM_NOTAGS);
            $mform->hideif("resits[$act->gradeitemid]", 'altgradetype', 'neq', GPA_GRADE);
        }
        $mform->addElement('html', html_writer::tag('div', get_string('cappedgradenote', 'local_gugcat'),
                 array('class' => 'gpa-lbl hidden small font-italic mb-3')));

        $selectcap = array();
        $selectcap[] = $mform->createElement('radio', 'appliedcap', '', get_string('cap12', 'local_gugcat'), 13, '');
        $selectcap[] = $mform->createElement('radio', 'appliedcap', '', get_string('cap9', 'local_gugcat'), 10, '');
        $mform->addGroup($selectcap, 'selectcap', get_string('selectcap', 'local_gugcat'), array(''), false);
        $mform->hideif('selectcap', 'altgradetype', 'neq', GPA_GRADE);

        $selectcap = array();
        $grades = local_gugcat::$grades;
        unset($grades[MEDICAL_EXEMPTION]);
        foreach ($grades as $key => $value) {
            $num = $key - 1;
            $grades[$key] = $key!= NON_SUBMISSION ? "$num ($value)" : $value;
        }
        $grades[0] = get_string('selectgrade', 'local_gugcat');
        $selectcap[] = $mform->createElement('radio', 'appliedcap', '', get_string('capother', 'local_gugcat'), 0, '');
        $selectcap[] = $mform->createElement('select', 'grade', get_string('gradeformgrade', 'local_gugcat'),
                         array_unique($grades), array('class' => 'mform-custom-select-grouped', 'size' => '15'));
        $appliedcap = $selectedg ? (($selectedg != 10 && $selectedg != 13 ) ? 0 : $selectedg) : 13;
        $mform->setDefault('appliedcap', $appliedcap);
        $mform->setDefault('grade', $appliedcap == 0 ? $selectedg : 0);
        $mform->setType('grade', PARAM_NOTAGS);
        $mform->disabledIf('grade', 'appliedcap', 'neg', 0);
        $mform->addGroup($selectcap, 'selectcapgrade', null, array(''), false);
        $mform->hideif('selectcapgrade', 'altgradetype', 'neq', GPA_GRADE);

        $mform->addElement('html', '</div>');
        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $buttonarray[] =& $mform->createElement('submit', 'hiddensubmitform', get_string('savechanges', 'local_gugcat'));
        $buttonarray[] =& $mform->createElement('button', 'savealtbutton', get_string('savechanges', 'local_gugcat'));
        $mform->addGroup($buttonarray, 'buttonarr', '', array(''), false);

        $mform->hideIf('buttonarr', 'altgradetype', 'eq', 0);

        // Hidden parameters.
        $mform->addElement('hidden', 'id', required_param('id', PARAM_INT));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'categoryid', optional_param('categoryid', null, PARAM_INT));
        $mform->setType('categoryid', PARAM_INT);
        $mform->addElement('hidden', 'alternative', optional_param('alternative', null, PARAM_INT));
        $mform->setType('alternative', PARAM_INT);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['altgradetype'] == GPA_GRADE) {
            // Display error when no selected grade in cap at other.
            if ($data['appliedcap'] == 0 && $data['grade'] == 0 ) {
                $errors['selectcapgrade'] = get_string('pleaseselectgrade', 'local_gugcat');
            }
        }

        return $errors;
    }
}