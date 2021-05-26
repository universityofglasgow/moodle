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
    // Add elements to form.
    public function definition() {
        $act = optional_param('activityid', null, PARAM_INT);
        $acg = optional_param('alternativecg', null, PARAM_INT);
        if (!is_null($act) && $act != 0) {
            $grades = local_gugcat::$grades;
        } else {
            $grades = local_gugcat::$grades + grade_aggregation::$aggrade;
            unset($grades[NON_SUBMISSION]);
        }
        $grades[0] = get_string('selectgrade', 'local_gugcat');
        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('html', '<div class="mform-container">');
        $student = $this->_customdata['student'];
        if ($this->_customdata['setting'] == '1') {
            $mform->addElement('html', '<div class="mform-override">');
        }
        $alternativecg = optional_param('alternativecg', null, PARAM_INT);
        foreach ($student->grades as $grdobj) {
            $overridemerit = !is_null($alternativecg) && $alternativecg != 0;
            $weight = $grdobj->is_child && !$overridemerit? $grdobj->originalweight : $grdobj->weight;
            if ($this->_customdata['setting'] == '1' && $acg != GPA_GRADE || $grdobj->category) {
                $mform->addElement('html', '<div class="mform-override">');
                $mform->addElement('static', $grdobj->activity, $grdobj->activity.' Weighting', "$weight%");
                $mform->addElement('html', '</div>');
                $mform->setType($grdobj->activity, PARAM_NOTAGS);
            } else if ($this->_customdata['setting'] == '0') {
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
                $mform->addRule('weights['.$grdobj->activityid.']', get_string('errorfieldnumbers', 'local_gugcat'),
                         'regex', '/^[0-9]+$/', 'client');
                $mform->addRule('weights['.$grdobj->activityid.']', get_string('errorfieldnumbers', 'local_gugcat'),
                         'regex', '/^[0-9]+$/', 'server');
                $mform->setDefault('weights['.$grdobj->activityid.']', $weight);
            }
        }

        if ($this->_customdata['setting'] == '0') {
            $mform->addElement('static', 'totalweight', get_string('totalweight', 'local_gugcat'), '100%');
            $mform->setType('totalweight', PARAM_NOTAGS);
        }
        $mform->addElement('html', $acg != GPA_GRADE ? '<div class="mform-grades">' : '<div class="mform-override">');
        foreach ($student->grades as $grdobj) {
            if ($grdobj->category) {
                $mform->addElement('html', '<div class="mform-override">');
            }
            $mform->addElement('static', $grdobj->activity.'grade', $grdobj->activity .' Grade',  $grdobj->grade);
            if ($grdobj->category) {
                $mform->addElement('html', '</div>');
            }
            $mform->setType($grdobj->activity.'grade', PARAM_NOTAGS);
        }
        if ($this->_customdata['setting'] == '0') {
            $mform->addElement('static', 'aggregatedgrade', get_string('aggregatedgrade', 'local_gugcat'),
                     $student->aggregatedgrade->grade);
            $mform->setType('aggregatedgrade', PARAM_NOTAGS);
        }
        $mform->addElement('html', '</div>');

        if ($this->_customdata['setting'] == '1') {
            $mform->addElement('static', 'aggregatedgrade', get_string($acg == 1 ? 'meritgrade'
                    : 'aggregatedgrade', 'local_gugcat'), $student->aggregatedgrade->grade);
            $mform->setType('aggregatedgrade', PARAM_NOTAGS);
            if ($acg == GPA_GRADE) {
                $mform->addElement('static', 'capselected', get_string('capselected', 'local_gugcat'),
                         local_gugcat::convert_grade($student->gpagrade->gpacap));
                $mform->setType('capselected', PARAM_NOTAGS);
                $mform->addElement('static', 'gpagrade', get_string('gpagrade', 'local_gugcat'), $student->gpagrade->grade);
                $mform->setType('gpagrade', PARAM_NOTAGS);
            }
            $mform->addElement('html', '</div>');
            $mform->addElement('hidden', 'gradetype', $this->_customdata['gradetype']);
            $mform->setType('gradetype', PARAM_NOTAGS);
            if (!is_null($this->_customdata['gradetype']) && $this->_customdata['gradetype'] == GRADE_TYPE_VALUE) {
                $attributes = array(
                    'pattern' => '^([mM][vV]|[0-9]{1,3}|[nN][sS])$',
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
                $mform->addRule('override', get_string('errorinputpoints', 'local_gugcat'), 'regex',
                         '/^([mM][vV]|[0-9]{1,3}|[nN][sS])$/', 'client');
                $mform->addRule('override', get_string('errorinputpoints', 'local_gugcat'), 'regex',
                         '/^([mM][vV]|[0-9]{1,3}|[nN][sS])$/', 'server');
            } else {
                $mform->addElement('select', 'override', get_string('overridegrade', 'local_gugcat'),
                         array_unique($grades), ['class' => 'mform-custom-select']);
                $mform->setDefault('override', 0);
                $mform->setType('override', PARAM_NOTAGS);
                $mform->addRule('override', get_string('required'), 'nonzero', null, 'client');
            }
        }
        $mform->addElement('textarea', 'notes', get_string('notes', 'local_gugcat'),
                 array('placeholder' => get_string('specifyreason', 'local_gugcat')));
        $mform->addRule('notes', null, 'required', null, 'client');
        $mform->setType('notes', PARAM_NOTAGS);

        $mform->addElement('html', '</div>');
        if ($this->_customdata['setting'] == '1') {
            $mform->addElement('submit', 'submit', get_string('savechanges', 'local_gugcat'), ['class' => 'btn-blue']);
        } else {
            $mform->addElement('submit', 'submit', get_string('savechanges', 'local_gugcat'),
                     ['id' => 'coursegradeform-submit', 'class' => 'btn-blue']);
            $mform->addElement('button', 'adjustoverride', get_string('savechanges', 'local_gugcat'),
                     ['id' => 'btn-coursegradeform', 'class' => 'btn-blue']);
        }
        // Hidden params.
        $mform->addElement('hidden', 'studentid', required_param('studentid', PARAM_INT));
        $mform->setType('studentid', PARAM_INT);
        $mform->addElement('hidden', 'id', required_param('id', PARAM_INT));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'setting', $this->_customdata['setting']);
        $mform->setType('setting', PARAM_INT);
        $mform->addElement('hidden', 'cnum', $student->cnum);
        $mform->setType('cnum', PARAM_INT);
        $mform->addElement('hidden', 'categoryid', optional_param('categoryid', null, PARAM_INT));
        $mform->setType('categoryid', PARAM_INT);
        $mform->addElement('hidden', 'activityid', optional_param('activityid', null, PARAM_INT));
        $mform->setType('activityid', PARAM_INT);
        $mform->addElement('hidden', 'alternativecg', optional_param('alternativecg', null, PARAM_INT));
        $mform->setType('alternativecg', PARAM_INT);
        $mform->addElement('hidden', 'page', optional_param('page', 0, PARAM_INT));
        $mform->setType('page', PARAM_INT);

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['setting'] == 1) {
            $newgrade = $data['override'];
            // Grademax is always 100 for subcategory grade point.
            if ($data['gradetype'] == GRADE_TYPE_VALUE && is_numeric($newgrade) && $newgrade > 100) {
                $errors['override'] = get_string('errorinputpoints', 'local_gugcat');
            }
        }

        return $errors;
    }
}
