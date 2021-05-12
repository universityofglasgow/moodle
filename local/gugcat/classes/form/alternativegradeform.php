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
    function definition (){

        $mform =& $this->_form;
        $mform->addElement('html', '<div class="mform-container">');

        if (isset($this->_customdata)) {  // hardcoding plugin names here is hacky
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
        $mform->addElement('select', 'altgradetype', get_string('selectaltgrdtype', 'local_gugcat'), $altgradetypes, ['id' => 'select-alt-grade', 'class' => 'mform-custom-select']);
        $mform->setType('altgradetype', PARAM_NOTAGS);
        $mform->setDefault('altgradetype', 0);

        // Merit Grade type form elements
        $mform->addElement('html', html_writer::tag('label', get_string('selectincludeassessment', 'local_gugcat'), array('class' => 'merit-lbl hidden')));
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
            $cbfield = array();
            $cbfield[] =& $mform->createElement('advcheckbox', "cb[$act->gradeitemid]", $act->name, null,  array('class' => 'checkbox-field'));
            $cbfield[] =& $mform->createElement('text', "weights[$act->gradeitemid]", null, $weightattr);
            $mform->addGroup($cbfield, 'cbfield', '', array(''), false);
            $mform->setType("weights[$act->gradeitemid]", PARAM_INT);
            $mform->setDefault("weights[$act->gradeitemid]", 0);
            $mform->disabledIf("weights[$act->gradeitemid]", "cb[$act->gradeitemid]", 'notchecked');
            $mform->hideif('cbfield', 'altgradetype', 'neq', MERIT_GRADE);
        }
        $twnote = html_writer::tag('span', get_string('totalweightvalue', 'local_gugcat'), array('class' => 'font-weight-normal small font-italic'));
        $totalweight = $mform->createElement('html',  html_writer::tag('span', '100%', array('class' => 'total-weight')));
        $mform->addGroup(array($totalweight), 'totalfield', get_string('totalweight', 'local_gugcat').$twnote, array(''), false);
        $mform->hideif('totalfield', 'altgradetype', 'neq', MERIT_GRADE);

        // GPA Grade type form elements
        $mform->addElement('html', html_writer::tag('label', get_string('pleaseindicateresits', 'local_gugcat'), array('class' => 'gpa-lbl hidden')));

        foreach ($activities as $act) {
            $mform->addElement('advcheckbox', "resits[$act->gradeitemid]", $act->name);
            $mform->setType('advcheckbox', PARAM_NOTAGS);
            $mform->hideif("resits[$act->gradeitemid]", 'altgradetype', 'neq', GPA_GRADE);
        }
        $mform->addElement('html', html_writer::tag('div', get_string('cappedgradenote', 'local_gugcat'), array('class' => 'gpa-lbl hidden small font-italic mb-3')));

        $selectcap = array();
        $selectcap[] = $mform->createElement('radio', 'capapply', '', get_string('cap12', 'local_gugcat'), 0, '');
        $selectcap[] = $mform->createElement('radio', 'capapply', '', get_string('cap9', 'local_gugcat'), 1, '');
        $mform->addGroup($selectcap, 'selectcap', get_string('selectcap', 'local_gugcat'), array(''), false);

        $selectcap = array();
        $grades = local_gugcat::$GRADES;
        $grades[0] = get_string('selectgrade', 'local_gugcat');
        $selectcap[] = $mform->createElement('radio', 'capapply', '', get_string('capother', 'local_gugcat'), 2, '');
        $selectcap[] = $mform->createElement('select', 'grade', get_string('gradeformgrade', 'local_gugcat'), array_unique($grades), array('class' => 'mform-custom-select', 'size' => '15'));
        $mform->setDefault('capapply', 0);
        $mform->setDefault('grade', 0);
        $mform->setType('grade', PARAM_NOTAGS);
        $mform->disabledIf('grade','capapply', 'neg', 2);
        $mform->addGroup($selectcap, 'selectcap', null, array(''), false);
        $mform->hideif('selectcap', 'altgradetype', 'neq', GPA_GRADE);

        $mform->addElement('html', '</div>');
        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $buttonarray[] =& $mform->createElement('submit', 'save', get_string('savechanges', 'local_gugcat'));
        $mform->addGroup($buttonarray, 'buttonarr', '', array(''), false);

        $mform->hideIf('buttonarr', 'altgradetype', 'eq', 0);

    }
}