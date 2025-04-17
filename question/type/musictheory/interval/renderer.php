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
 * to intervals.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/renderer.php');

/**
 * Renders music theory interval writing questions.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_interval_write_renderer extends qtype_musictheory_renderer {

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
                'inputname'        => $inputname,
                'optionsxml'       => $question->musictheory_optionsxml,
                'readonly'         => $options->readonly,
                'initialinput'     => $initialinput,
                'correctresponse'  => $correctresponse,
                'correctrespstr'   => get_string('correctansweris', 'qtype_musictheory'),
                'additionalparams' => array(
                )
            )
        );

        $qtypemod = 'moodle-qtype_musictheory-musictheoryqtype';
        $rendernamespace = 'M.qtype_musictheory.musictheoryqtype.initQuestionRender';
        $PAGE->requires->yui_module($qtypemod, $rendernamespace, $moduleparams);

        $inputattributes = array(
            'type'  => 'text',
            'name'  => $inputname,
            'value' => $initialinput,
            'id'    => $inputname,
            'size'  => 4
        );

        if ($options->readonly) {
            $inputattributes['readonly'] = 'readonly';
        }

        $questiontext = $question->format_questiontext($qa);
        $input = '<b>' . get_string('givennoteelementgroup', 'qtype_musictheory') . ': ';
        $input .= get_string('note' . strtolower($question->musictheory_givennoteletter), 'qtype_musictheory');
        $acc = str_replace('#', 'sharp', $question->musictheory_givennoteaccidental);
        $input .= get_string('acc_' . $acc, 'qtype_musictheory') . $question->musictheory_givennoteregister . '</b> ';
        $input .= html_writer::empty_tag('input', $inputattributes);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $nonjavascriptdivattr = array(
            'id'    => 'musictheory_div_replacedbycanvas_' . $inputname,
            'class' => 'ablock'
        );

        $result .= html_writer::start_tag('div', $nonjavascriptdivattr);
        $result .= $input;
        if (!$options->readonly) {
            $result .= $OUTPUT->help_icon('interval_write_questionastext', 'qtype_musictheory', '');
        }
        $result .= html_writer::end_tag('div');

        $javascriptdivattr = array(
            'id'    => 'musictheory_div_canvas_' . $inputname,
            'class' => 'ablock',
            'style' => 'display:none'
        );
        $result .= html_writer::start_tag('div', $javascriptdivattr);
        if (!$options->readonly) {
            $result .= $OUTPUT->help_icon('interval_write_questionasui', 'qtype_musictheory', '');
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
 * Renders music theory interval identification questions.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_interval_identify_renderer extends qtype_musictheory_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        global $PAGE;

        $PAGE->requires->yui_module('moodle-qtype_musictheory-musictheoryui', 'M.qtype_musictheory.musictheoryui.init');

        $sizeselectid = $qa->get_qt_field_name('musictheory_answer_size');
        $qualityselectid = $qa->get_qt_field_name('musictheory_answer_quality');
        $question = $qa->get_question();

        $direction = ($question->musictheory_direction == 'above') ? '+' : '-';
        $interval = new Interval($direction, $question->musictheory_quality, $question->musictheory_size);
        $ltr = $question->musictheory_givennoteletter;
        $acc = $question->musictheory_givennoteaccidental;
        $reg = $question->musictheory_givennoteregister;
        $givennote = new Note($ltr, $acc, $reg);
        $notefrominterval = $givennote->getNoteFromInterval($interval);

        $currqual = $qa->get_last_qt_var('musictheory_answer_quality');
        $currsize = $qa->get_last_qt_var('musictheory_answer_size');

        $initialinput = (string) $notefrominterval;

        $moduleparams = array(
            array(
                'inputname'        => $sizeselectid,
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

        $selectoptionssize = array(
            ''   => get_string('selectasize', 'qtype_musictheory'),
            '2'  => get_string('size2', 'qtype_musictheory'),
            '3'  => get_string('size3', 'qtype_musictheory'),
            '4'  => get_string('size4', 'qtype_musictheory'),
            '5'  => get_string('size5', 'qtype_musictheory'),
            '6'  => get_string('size6', 'qtype_musictheory'),
            '7'  => get_string('size7', 'qtype_musictheory'),
            '8'  => get_string('size8', 'qtype_musictheory'),
            '9'  => get_string('size9', 'qtype_musictheory'),
            '10' => get_string('size10', 'qtype_musictheory'),
            '11' => get_string('size11', 'qtype_musictheory'),
            '12' => get_string('size12', 'qtype_musictheory'),
            '13' => get_string('size13', 'qtype_musictheory'),
        );

        $sizeselectattributes = array(
            'name' => $sizeselectid,
            'id'   => $sizeselectid
        );

        $selectoptionsquality = array(
            ''  => get_string('selectaquality', 'qtype_musictheory'),
            'D' => get_string('diminished', 'qtype_musictheory'),
            'm' => get_string('minor', 'qtype_musictheory'),
            'M' => get_string('major', 'qtype_musictheory'),
            'P' => get_string('perfect', 'qtype_musictheory'),
            'A' => get_string('augmented', 'qtype_musictheory'),
        );

        $qualityselectattributes = array(
            'name' => $qualityselectid,
            'id'   => $qualityselectid
        );

        if ($options->readonly) {
            $sizeselectattributes['disabled'] = 'true';
            $qualityselectattributes['disabled'] = 'true';
        }

        if ($options->correctness) {
            $corrresp = $question->get_correct_response();
            if (!is_null($currqual)) {
                if ($currqual === $corrresp['musictheory_answer_quality']) {
                    $qualityselectattributes['class'] = $this->feedback_class(1);
                } else {
                    $qualityselectattributes['class'] = $this->feedback_class(0);
                }
            }
            if (!is_null($currsize)) {
                if ($currsize === $corrresp['musictheory_answer_size']) {
                    $sizeselectattributes['class'] = $this->feedback_class(1);
                } else {
                    $sizeselectattributes['class'] = $this->feedback_class(0);
                }
            }
        }

        $questiontext = $question->format_questiontext($qa);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $nonjavascriptdivattr = array(
            'id' => 'musictheory_div_replacedbycanvas_' . $sizeselectid
        );

        $givennotestr = get_string('note' . strtolower($question->musictheory_givennoteletter), 'qtype_musictheory');
        $acc = str_replace('#', 'sharp', $question->musictheory_givennoteaccidental);
        $givennotestr .= get_string('acc_' . $acc, 'qtype_musictheory') . $question->musictheory_givennoteregister;

        $notefromintervalstr = get_string('note' . strtolower($notefrominterval->getLetter()), 'qtype_musictheory');
        $acc = str_replace('#', 'sharp', $notefrominterval->getAccidental());
        $notefromintervalstr .= get_string('acc_' . $acc, 'qtype_musictheory') . $notefrominterval->getRegister();

        $musicquestionastext = $givennotestr . ', ' . $notefromintervalstr;

        $result .= html_writer::tag('div', '<b>' . $musicquestionastext . '</b>', $nonjavascriptdivattr);

        $javascriptdivattr = array(
            'id'    => 'musictheory_div_canvas_' . $sizeselectid,
            'class' => 'ablock',
            'style' => 'display:none'
        );
        $result .= html_writer::tag('div', '', $javascriptdivattr);

        $input = html_writer::select($selectoptionsquality, $qualityselectid, $currqual, true, $qualityselectattributes);
        $input .= html_writer::select($selectoptionssize, $sizeselectid, $currsize, true, $sizeselectattributes);
        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= $input;
        $result .= html_writer::end_tag('div');

        if ($qa->get_state() == question_state::$invalid) {
            $currentquality = $qa->get_last_qt_var('musictheory_answer_quality');
            $currentsize = $qa->get_last_qt_var('musictheory_answer_size');
            $answerarray = array(
                'musictheory_answer_quality' => $currentquality,
                'musictheory_answer_size'    => $currentsize
            );
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($answerarray),
                                                                                        array('class' => 'validationerror'));
        }

        return $result;
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $correctresponsearray = $question->get_correct_response();
        switch ($correctresponsearray['musictheory_answer_quality']) {
            case 'M':
                $qual = 'maj';
                break;
            case 'm':
                $qual = 'min';
                break;
            case 'P':
                $qual = 'per';
                break;
            case 'A':
                $qual = 'aug';
                break;
            case 'D':
                $qual = 'dim';
                break;
            default:
                $qual = '';
        }
        $interval = get_string($qual .
                $correctresponsearray['musictheory_answer_size'], 'qtype_musictheory');
        return get_string('correctansweris', 'qtype_musictheory') . ' <b>' . $interval . '</b>';
    }

}
