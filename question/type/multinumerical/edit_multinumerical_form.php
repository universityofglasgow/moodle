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
 * Version information
 *
 * @package    qtype
 * @subpackage multinumerical
 * @copyright  2013 Université de Lausanne
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class qtype_multinumerical_edit_form extends question_edit_form {

    protected function definition_inner($mform) {

        $mform->addElement('header', 'qtypeoptions', get_string('qtypeoptions', 'qtype_multinumerical'));

        $mform->addElement('static', 'answersinstruct',
                get_string('help'),
                get_string('helponquestionoptions', 'qtype_multinumerical'));

        $mform->addElement('text', 'parameters', get_string('parameters', 'qtype_multinumerical'), array('size' => 80));
        $mform->setType('parameters', PARAM_TEXT);
        $mform->addElement('textarea', 'conditions', get_string('conditions', 'qtype_multinumerical'), array('rows' => 6, 'cols' => 80));
        $mform->setType('conditions', PARAM_TEXT);
        $mform->addElement('textarea', 'feedbackperconditions', get_string('feedbackperconditions', 'qtype_multinumerical'), array('rows' => 6, 'cols' => 80));
        $mform->setType('feedbackperconditions', PARAM_TEXT);

        $usecolorforfeedback_menu = array(
            get_string('no'),
            get_string('yes')
        );
        $mform->addElement('select', 'usecolorforfeedback',
                get_string('usecolorforfeedback', 'qtype_multinumerical'), $usecolorforfeedback_menu);
        $mform->setType('usecolorforfeedback', PARAM_INT);

        $displaycalc_menu = array(
            get_string('no'),
            get_string('yes'),
            get_string('onlyforcalculations', 'qtype_multinumerical')
        );
        $mform->addElement('select', 'displaycalc',
                get_string('displaycalc', 'qtype_multinumerical'), $displaycalc_menu);
        $mform->setType('displaycalc', PARAM_INT);

        $binarygrade_menu = array(
            get_string('gradefractional', 'qtype_multinumerical'),
            get_string('gradebinary', 'qtype_multinumerical')
        );
        $mform->addElement('select', 'binarygrade',
                get_string('binarygrade', 'qtype_multinumerical'), $binarygrade_menu);
        $mform->setType('binarygrade', PARAM_INT);

        $this->add_interactive_settings();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // cleanup :
        $data['conditions'] = rtrim(str_replace("\r\n", "\n", $data['conditions']), "\n");
        $data['feedbackperconditions'] = rtrim(str_replace("\r\n", "\n", $data['feedbackperconditions']), "\n");

        $conditions = explode("\n", $data['conditions']);
        $feedbackperconditions = explode("\n", $data['feedbackperconditions']);
        if (count($feedbackperconditions) > count($conditions)) {
            $errors['conditions'] = get_string('badnumfeedbackperconditions', 'qtype_multinumerical');
            $errors['feedbackperconditions'] = get_string('badnumfeedbackperconditions', 'qtype_multinumerical');
        }

        foreach ($feedbackperconditions as $feedbackpercondition) {
            if (trim($feedbackpercondition) && strpos($feedbackpercondition, '|') === false) {
                $errors['feedbackperconditions'] = get_string('badfeedbackperconditionsyntax', 'qtype_multinumerical');
            }
        }

        return $errors;
    }

    public function qtype() {
        return 'multinumerical';
    }
}
