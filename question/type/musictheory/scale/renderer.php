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
 * This defines renderer classes for music theory question subtypes related
 * to scales.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/renderer.php');

/**
 * Renders music theory scale writing questions.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_scale_write_renderer extends qtype_musictheory_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        global $PAGE, $OUTPUT;

        $PAGE->requires->yui_module('moodle-qtype_musictheory-musictheoryui', 'M.qtype_musictheory.musictheoryui.init');

        $inputname = $qa->get_qt_field_name('answer');
        $question = $qa->get_question();
        $initialinput = $qa->get_last_qt_var('answer');
        if ($options->rightanswer) {
            $correctresponsearray = $question->get_correct_response();
            $correctresponse = $correctresponsearray['answer'];
        } else {
            $correctresponse = null;
        }

        $moduleparams = array(
            array(
                'inputname' => $inputname,
                'optionsxml' => $question->musictheory_optionsxml,
                'readonly' => $options->readonly,
                'initialinput' => $initialinput,
                'correctresponse' => $correctresponse,
                'correctrespstr' => get_string('correctansweris', 'qtype_musictheory'),
                'additionalparams' => array(
                )
            )
        );

        $qtypemod = 'moodle-qtype_musictheory-musictheoryqtype';
        $rendernamespace = 'M.qtype_musictheory.musictheoryqtype.initQuestionRender';
        $PAGE->requires->yui_module($qtypemod, $rendernamespace, $moduleparams);

        $inputattributes = array(
            'type' => 'text',
            'name' => $inputname,
            'value' => $initialinput,
            'id' => $inputname,
            'size' => 70
        );

        if ($options->readonly) {
            $inputattributes['readonly'] = 'readonly';
        }

        $questiontext = $question->format_questiontext($qa);
        $input = html_writer::empty_tag('input', $inputattributes);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $nonjavascriptdivattr = array(
            'id' => 'musictheory_div_replacedbycanvas_' . $inputname,
            'class' => 'ablock'
        );

        $tonic = $question->musictheory_givennoteletter;
        $acc = $question->musictheory_givennoteaccidental;
        $tonic .= $acc . $question->musictheory_givennoteregister;
        $result .= html_writer::start_tag('div', $nonjavascriptdivattr);
        $result .= '<b>' . get_string('tonic', 'qtype_musictheory') . ' = ' . $tonic . '</b>&nbsp;' . $input;
        if (!$options->readonly) {
            $result .= $OUTPUT->help_icon('scale_write_questionastext', 'qtype_musictheory', '');
        }
        $result .= html_writer::end_tag('div');

        $javascriptdivattr = array(
            'id' => 'musictheory_div_canvas_' . $inputname,
            'class' => 'ablock',
            'style' => 'display:none'
        );
        $result .= html_writer::start_tag('div', $javascriptdivattr);
        if (!$options->readonly) {
            $result .= $OUTPUT->help_icon('scale_write_questionasui', 'qtype_musictheory', '');
        }
        $result .= html_writer::end_tag('div');

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error(array('answer' => $initialinput)),
                    array('class' => 'validationerror'));
        }

        return $result;
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $correctresponsearray = $question->get_correct_response();
        return get_string('correctansweris', 'qtype_musictheory') . ' ' .
                $correctresponsearray['answer'];
    }

}

/**
 * Renders music theory scale identification questions.
 *
 * @copyright  2015 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_scale_identify_renderer extends qtype_musictheory_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        global $PAGE;

        $PAGE->requires->yui_module('moodle-qtype_musictheory-musictheoryui', 'M.qtype_musictheory.musictheoryui.init');
        $question = $qa->get_question();
        $tonicletterselectid = $qa->get_qt_field_name('musictheory_answer_tonicletter');
        $tonicaccselectid = $qa->get_qt_field_name('musictheory_answer_tonicacc');
        $scaletypeselectid = $qa->get_qt_field_name('musictheory_answer_scaletype');

        $currtonicletter = $qa->get_last_qt_var('musictheory_answer_tonicletter');
        $currtonicacc = $qa->get_last_qt_var('musictheory_answer_tonicacc');
        $currscaletype = $qa->get_last_qt_var('musictheory_answer_scaletype');

        $corrresp = $question->get_correct_response();
        $tonic = new Note($question->musictheory_givennoteletter, $question->musictheory_givennoteaccidental,
                $question->musictheory_givennoteregister);
        $scale = null;
        switch ($question->musictheory_scaletype) {
            case 'major':
                $scale = new MajorScale($tonic);
                break;
            case 'natural':
                $scale = new NaturalMinorScale($tonic);
                break;
            case 'harmonic':
                $scale = new HarmonicMinorScale($tonic);
                break;
            case 'melodic':
                $scale = new MelodicMinorScale($tonic);
                break;
            default:
                $scale = new MajorScale($tonic);
        }

        $initialinput = (string) $scale;

        $moduleparams = array(
            array(
                'inputname' => $tonicletterselectid,
                'optionsxml' => $question->musictheory_optionsxml,
                'readonly' => true,
                'initialinput' => $initialinput,
                'correctresponse' => null,
                'correctrespstr' => get_string('correctansweris', 'qtype_musictheory'),
                'additionalparams' => array(
                )
            )
        );

        $qtypemod = 'moodle-qtype_musictheory-musictheoryqtype';
        $rendernamespace = 'M.qtype_musictheory.musictheoryqtype.initQuestionRender';
        $PAGE->requires->yui_module($qtypemod, $rendernamespace, $moduleparams);

        $selectoptionsletter = array(
            '' => get_string('selectanoption', 'qtype_musictheory'),
            'A' => get_string('notea', 'qtype_musictheory'),
            'B' => get_string('noteb', 'qtype_musictheory'),
            'C' => get_string('notec', 'qtype_musictheory'),
            'D' => get_string('noted', 'qtype_musictheory'),
            'E' => get_string('notee', 'qtype_musictheory'),
            'F' => get_string('notef', 'qtype_musictheory'),
            'G' => get_string('noteg', 'qtype_musictheory'),
        );

        $letterselectattributes = array(
            'name' => $tonicletterselectid,
            'id' => $tonicletterselectid,
        );

        $feedbackimg = $this->feedback_image(1);

        $selectoptionsacc = array(
            '' => get_string('selectanoption', 'qtype_musictheory'),
            'n' => '(' . '&#9838;' . ')',
            '#' => '&#9839;',
            'b' => '&#9837;',
        );

        $accselectattributes = array(
            'name' => $tonicaccselectid,
            'id' => $tonicaccselectid
        );

        $selectoptionsscaletype = array();
        $selectoptionsscaletype[''] = get_string('selectanoption', 'qtype_musictheory');
        foreach ($question->musictheory_possiblescalesinresponse as $scaletype) {
            $selectoptionsscaletype[$scaletype] = get_string('scaletype_' . $scaletype, 'qtype_musictheory');
        }

        $scaletypeselectattributes = array(
            'name' => $scaletypeselectid,
            'id' => $scaletypeselectid
        );

        if ($options->correctness) {
            if (!is_null($currtonicletter)) {
                if ($currtonicletter === $corrresp['musictheory_answer_tonicletter']) {
                    $letterselectattributes['class'] = $this->feedback_class(1);
                } else {
                    $letterselectattributes['class'] = $this->feedback_class(0);
                }
            }
            if (!is_null($currtonicacc)) {
                if ($currtonicacc === $corrresp['musictheory_answer_tonicacc']) {
                    $accselectattributes['class'] = $this->feedback_class(1);
                } else {
                    $accselectattributes['class'] = $this->feedback_class(0);
                }
            }
            if (!is_null($currscaletype)) {
                if ($currscaletype === $corrresp['musictheory_answer_scaletype']) {
                    $scaletypeselectattributes['class'] = $this->feedback_class(1);
                } else {
                    $scaletypeselectattributes['class'] = $this->feedback_class(0);
                }
            }
        }

        if ($options->readonly) {
            $letterselectattributes['disabled'] = 'true';
            $accselectattributes['disabled'] = 'true';
            $scaletypeselectattributes['disabled'] = 'true';
        }

        $questiontext = $question->format_questiontext($qa);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $nonjavascriptdivattr = array(
            'id' => 'musictheory_div_replacedbycanvas_' . $tonicletterselectid
        );

        $musicquestionastext = $initialinput;
        $musicquestionastext = str_replace('#', get_string('acc_sharp', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('b', get_string('acc_b', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('n', get_string('acc_n', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('x', get_string('acc_x', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('bb', get_string('acc_bb', 'qtype_musictheory'), $musicquestionastext);

        $result .= html_writer::tag('div', '<b>' . $musicquestionastext . '</b>', $nonjavascriptdivattr);

        $javascriptdivattr = array(
            'id' => 'musictheory_div_canvas_' . $tonicletterselectid,
            'class' => 'ablock',
            'style' => 'display:none'
        );
        $result .= html_writer::tag('div', '', $javascriptdivattr);

        $input = html_writer::select($selectoptionsletter, $tonicletterselectid, $currtonicletter, true, $letterselectattributes);
        $input .= html_writer::select($selectoptionsacc, $tonicaccselectid, $currtonicacc, true, $accselectattributes);
        $input .= html_writer::select($selectoptionsscaletype, $scaletypeselectid, $currscaletype, true,
                $scaletypeselectattributes);

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= $input;
        $result .= html_writer::end_tag('div');

        if ($qa->get_state() == question_state::$invalid) {
            $currenttonicletter = $qa->get_last_qt_var('musictheory_answer_tonicletter');
            $currenttonicacc = $qa->get_last_qt_var('musictheory_answer_tonicacc');
            $currentscaletype = $qa->get_last_qt_var('musictheory_answer_scaletype');
            $answerarray = array(
                'musictheory_answer_tonicletter' => $currenttonicletter,
                'musictheory_answer_tonicacc' => $currenttonicacc,
                'musictheory_answer_scaletype' => $currentscaletype,
            );
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($answerarray),
                    array('class' => 'validationerror'));
        }

        return $result;
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $correctresponsearray = $question->get_correct_response();
        $letter = get_string('note' . strtolower($correctresponsearray['musictheory_answer_tonicletter']), 'qtype_musictheory');
        $acc = ($correctresponsearray['musictheory_answer_tonicacc'] === '#') ?
                'sharp' :
                $correctresponsearray['musictheory_answer_tonicacc'];
        if ($correctresponsearray['musictheory_answer_tonicacc'] !== 'n') {
            $accstr = get_string('acc_' . $acc, 'qtype_musictheory');
        } else {
            $accstr = '';
        }
        $scaletype = get_string('scaletype_' . $correctresponsearray['musictheory_answer_scaletype'], 'qtype_musictheory');
        return get_string('correctansweris', 'qtype_musictheory') . ' ' .
                $letter . $accstr . ' ' . $scaletype;
    }

}
