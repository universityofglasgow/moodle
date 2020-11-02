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
 * to chord quality.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/renderer.php');

/**
 * Renders music theory chord quality writing questions.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_chordquality_write_renderer extends qtype_musictheory_renderer {

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
        $maxnotes = $question->get_answer_num_notes();

        $moduleparams = array(
            array(
                'inputname'        => $inputname,
                'optionsxml'       => $question->musictheory_optionsxml,
                'readonly'         => $options->readonly,
                'initialinput'     => $initialinput,
                'correctresponse'  => $correctresponse,
                'correctrespstr'   => get_string('correctansweris', 'qtype_musictheory'),
                'additionalparams' => array(
                    'maxnotes' => $maxnotes
                )
            )
        );

        $qtypemod = 'moodle-qtype_musictheory-musictheoryqtype';
        $rendernamespace = 'M.qtype_musictheory.musictheoryqtype.initQuestionRender';
        $PAGE->requires->yui_module($qtypemod, $rendernamespace, $moduleparams);

        $inputattributes = array(
            'type'      => 'text',
            'name'      => $inputname,
            'value'     => $initialinput,
            'id'        => $inputname,
            'hfprimary' => 20
        );

        if ($options->readonly) {
            $inputattributes['readonly'] = 'readonly';
        }

        $questiontext = $question->format_questiontext($qa);
        $input = html_writer::empty_tag('input', $inputattributes);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $nonjavascriptdivattr = array(
            'id'    => 'musictheory_div_replacedbycanvas_' . $inputname,
            'class' => 'ablock'
        );

        $result .= html_writer::start_tag('div', $nonjavascriptdivattr);
        $result .= $input;
        if (!$options->readonly) {
            $result .= $OUTPUT->help_icon('chordquality_write_questionastext', 'qtype_musictheory', '');
        }
        $result .= html_writer::end_tag('div');

        $javascriptdivattr = array(
            'id'    => 'musictheory_div_canvas_' . $inputname,
            'class' => 'ablock',
            'style' => 'display:none'
        );
        $result .= html_writer::start_tag('div', $javascriptdivattr);
        if (!$options->readonly) {
            $result .= $OUTPUT->help_icon('chordquality_write_questionasui', 'qtype_musictheory', '');
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
 * Renders music theory chord quality identification questions.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_chordquality_identify_renderer extends qtype_musictheory_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        global $PAGE;

        $PAGE->requires->yui_module('moodle-qtype_musictheory-musictheoryui', 'M.qtype_musictheory.musictheoryui.init');
        $question = $qa->get_question();
        $chordqualityselectid = $qa->get_qt_field_name('musictheory_answer_chordquality');
        $rootletterselectid = $qa->get_qt_field_name('musictheory_answer_rootletter');
        $rootaccselectid = $qa->get_qt_field_name('musictheory_answer_rootacc');

        $currquality = $qa->get_last_qt_var('musictheory_answer_chordquality');
        $currrootletter = $qa->get_last_qt_var('musictheory_answer_rootletter');
        $currrootacc = $qa->get_last_qt_var('musictheory_answer_rootacc');

        switch ($question->musictheory_clef) {
            case 'treble':
                $reg = 4;
                break;
            case 'alto':
            case 'tenor':
                $reg = 3;
                break;
            case 'bass':
                $reg = 2;
                break;
        }

        $root = new Note($question->musictheory_givennoteletter, $question->musictheory_givennoteaccidental, $reg);

        switch ($question->musictheory_chordquality) {
            case 'major':
                $quality = 'M';
                break;
            case 'minor':
                $quality = 'm';
                break;
            case 'diminished':
                $quality = 'D';
                break;
            case 'augmented':
                $quality = 'A';
                break;
            default:
                $quality = 'M';
        }

        $chord = new Chord($root, $quality, 0);
        $initialinput = (string) $chord;

        $moduleparams = array(
            array(
                'inputname'        => $chordqualityselectid,
                'optionsxml'       => $question->musictheory_optionsxml,
                'readonly'         => true,
                'initialinput'     => $initialinput,
                'correctresponse'  => null,
                'correctrespstr'   => get_string('correctansweris', 'qtype_musictheory'),
                'additionalparams' => array(
                )
            )
        );

        $qtypemod = 'moodle-qtype_musictheory-musictheoryqtype';
        $rendernamespace = 'M.qtype_musictheory.musictheoryqtype.initQuestionRender';
        $PAGE->requires->yui_module($qtypemod, $rendernamespace, $moduleparams);

        $selectoptionsrootletter = array(
            ''  => get_string('selectanoption', 'qtype_musictheory'),
            'A' => get_string('notea', 'qtype_musictheory'),
            'B' => get_string('noteb', 'qtype_musictheory'),
            'C' => get_string('notec', 'qtype_musictheory'),
            'D' => get_string('noted', 'qtype_musictheory'),
            'E' => get_string('notee', 'qtype_musictheory'),
            'F' => get_string('notef', 'qtype_musictheory'),
            'G' => get_string('noteg', 'qtype_musictheory')
        );

        $rootletterselectattributes = array(
            'name'  => $rootletterselectid,
            'id'    => $rootletterselectid,
            'style' => 'margin-right:3px'
        );

        $selectoptionsrootacc = array(
            ''   => get_string('selectanoption', 'qtype_musictheory'),
            'n'  => get_string('acc_n', 'qtype_musictheory'),
            '#'  => get_string('acc_sharp', 'qtype_musictheory'),
            'b'  => get_string('acc_b', 'qtype_musictheory'),
            'x'  => get_string('acc_x', 'qtype_musictheory'),
            'bb' => get_string('acc_bb', 'qtype_musictheory'),
        );

        $rootaccselectattributes = array(
            'name'  => $rootaccselectid,
            'id'    => $rootaccselectid,
            'style' => 'margin-right:3px'
        );

        $selectoptionschordquality = array(
            ''           => get_string('selectanoption', 'qtype_musictheory'),
            'major'      => get_string('major', 'qtype_musictheory'),
            'minor'      => get_string('minor', 'qtype_musictheory'),
            'augmented'  => get_string('augmented', 'qtype_musictheory'),
            'diminished' => get_string('diminished', 'qtype_musictheory')
        );

        $chordqualityselectattributes = array(
            'name' => $chordqualityselectid,
            'id'   => $chordqualityselectid
        );

        if ($options->readonly) {
            $chordqualityselectattributes['disabled'] = 'true';
            $rootletterselectattributes['disabled'] = 'true';
            $rootaccselectattributes['disabled'] = 'true';
        }

        if ($options->correctness) {
            $corrresp = $question->get_correct_response();
            if (!is_null($currquality)) {
                if ($currquality === $corrresp['musictheory_answer_chordquality']) {
                    $chordqualityselectattributes['class'] = $this->feedback_class(1);
                } else {
                    $chordqualityselectattributes['class'] = $this->feedback_class(0);
                }
            }
            if (!is_null($currrootletter)) {
                if ($currrootletter === $corrresp['musictheory_answer_rootletter']) {
                    $rootletterselectattributes['class'] = $this->feedback_class(1);
                } else {
                    $rootletterselectattributes['class'] = $this->feedback_class(0);
                }
            }
            if (!is_null($currrootacc)) {
                if ($currrootacc === $corrresp['musictheory_answer_rootacc']) {
                    $rootaccselectattributes['class'] = $this->feedback_class(1);
                } else {
                    $rootaccselectattributes['class'] = $this->feedback_class(0);
                }
            }
        }

        $questiontext = $question->format_questiontext($qa);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $nonjavascriptdivattr = array(
            'id' => 'musictheory_div_replacedbycanvas_' . $chordqualityselectid
        );

        $musicquestionastext = $initialinput;
        $musicquestionastext = preg_replace('/[0-9]+/', '', $musicquestionastext);
        $musicquestionastext = str_replace('#', get_string('acc_sharp', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('b', get_string('acc_b', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('n', get_string('acc_n', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('x', get_string('acc_x', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('bb', get_string('acc_bb', 'qtype_musictheory'), $musicquestionastext);

        $result .= html_writer::tag('div', '<b>' . $musicquestionastext . '</b>', $nonjavascriptdivattr);

        $javascriptdivattr = array(
            'id'    => 'musictheory_div_canvas_' . $chordqualityselectid,
            'class' => 'ablock',
            'style' => 'display:none'
        );

        $result .= html_writer::tag('div', '', $javascriptdivattr);
        $input = html_writer::select($selectoptionsrootletter, $rootletterselectid, $currrootletter, true,
                                     $rootletterselectattributes);
        $input .= html_writer::select($selectoptionsrootacc, $rootaccselectid, $currrootacc, true, $rootaccselectattributes);
        $input .= html_writer::select($selectoptionschordquality, $chordqualityselectid, $currquality, true,
                                      $chordqualityselectattributes);
        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= $input;
        $result .= html_writer::end_tag('div');

        if ($qa->get_state() == question_state::$invalid) {
            $currentchordquality = $qa->get_last_qt_var('musictheory_answer_chordquality');
            $currentrootletter = $qa->get_last_qt_var('musictheory_answer_rootletter');
            $currentrootacc = $qa->get_last_qt_var('musictheory_answer_rootacc');
            $answerarray = array(
                'musictheory_answer_chordquality' => $currentchordquality,
                'musictheory_answer_rootletter'   => $currentrootletter,
                'musictheory_answer_rootacc'      => $currentrootacc
            );
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($answerarray),
                                                                                        array('class' => 'validationerror'));
        }

        return $result;
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $correctresponsearray = $question->get_correct_response();
        $ltr = get_string('note' . strtolower($correctresponsearray['musictheory_answer_rootletter']), 'qtype_musictheory');
        $acckey = str_replace('#', 'sharp', $correctresponsearray['musictheory_answer_rootacc']);
        $acc = get_string('acc_' . $acckey, 'qtype_musictheory');
        $quality = get_string($correctresponsearray['musictheory_answer_chordquality'], 'qtype_musictheory');
        return get_string('correctansweris', 'qtype_musictheory') . ' <b>' . $ltr . $acc . ' ' . $quality . '</b>';
    }

}
