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
 * to key signatures.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/renderer.php');

/**
 * Renders music theory key signature writing questions.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_keysignature_write_renderer extends qtype_musictheory_renderer {

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
            'size'  => 30
        );

        if ($options->readonly) {
            $inputattributes['readonly'] = 'readonly';
        }

        $questiontext = $question->format_questiontext($qa);
        $input = '<b>' . get_string($question->musictheory_clef . 'clef', 'qtype_musictheory') . ':</b> ';
        $input .= html_writer::empty_tag('input', $inputattributes);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $nonjavascriptdivattr = array(
            'id'    => 'musictheory_div_replacedbycanvas_' . $inputname,
            'class' => 'ablock'
        );

        $result .= html_writer::start_tag('div', $nonjavascriptdivattr);
        $result .= $input;
        if (!$options->readonly) {
            $result .= $OUTPUT->help_icon('keysignature_write_questionastext', 'qtype_musictheory', '');
        }
        $result .= html_writer::end_tag('div');

        $javascriptdivattr = array(
            'id'    => 'musictheory_div_canvas_' . $inputname,
            'class' => 'ablock',
            'style' => 'display:none'
        );
        $result .= html_writer::start_tag('div', $javascriptdivattr);
        if (!$options->readonly) {
            $result .= $OUTPUT->help_icon('keysignature_write_questionasui', 'qtype_musictheory', '');
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
 * Renders music theory key signature identification questions.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_keysignature_identify_renderer extends qtype_musictheory_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        global $PAGE;

        $PAGE->requires->yui_module('moodle-qtype_musictheory-musictheoryui', 'M.qtype_musictheory.musictheoryui.init');

        $inputname = $qa->get_qt_field_name('answer');
        $question = $qa->get_question();

        $tonic = new Note(substr($question->musictheory_keymode, 0, 1), substr($question->musictheory_keymode, 1, 1), 4);

        $ismajor = (substr($question->musictheory_keymode, 2, 1) == "M");
        $keysign = new KeySignature($tonic, $ismajor, $question->musictheory_clef);
        $correctanswer = (string) $keysign;

        $currans = $qa->get_last_qt_var('answer');

        $moduleparams = array(
            array(
                'inputname'        => $inputname,
                'optionsxml'       => $question->musictheory_optionsxml,
                'readonly'         => true,
                'initialinput'     => $correctanswer,
                'correctresponse'  => null,
                'correctrespstr'   => get_string('correctansweris', 'qtype_musictheory'),
                'additionalparams' => array(
                )
            )
        );

        $qtypemod = 'moodle-qtype_musictheory-musictheoryqtype';
        $rendernamespace = 'M.qtype_musictheory.musictheoryqtype.initQuestionRender';
        $PAGE->requires->yui_module($qtypemod, $rendernamespace, $moduleparams);

        $selectattributes = array(
            'name' => $inputname,
            'id'   => $inputname,
        );

        if ($options->readonly) {
            $selectattributes['disabled'] = 'true';
        }

        if ($ismajor) {
            $selectoptionskeymode = array(
                ''    => get_string('selectakey', 'qtype_musictheory'),
                'CnM' => get_string('cnmajor', 'qtype_musictheory'),
                'GnM' => get_string('gnmajor', 'qtype_musictheory'),
                'DnM' => get_string('dnmajor', 'qtype_musictheory'),
                'AnM' => get_string('anmajor', 'qtype_musictheory'),
                'EnM' => get_string('enmajor', 'qtype_musictheory'),
                'BnM' => get_string('bnmajor', 'qtype_musictheory'),
                'F#M' => get_string('fsharpmajor', 'qtype_musictheory'),
                'C#M' => get_string('csharpmajor', 'qtype_musictheory'),
                'FnM' => get_string('fnmajor', 'qtype_musictheory'),
                'BbM' => get_string('bbmajor', 'qtype_musictheory'),
                'EbM' => get_string('ebmajor', 'qtype_musictheory'),
                'AbM' => get_string('abmajor', 'qtype_musictheory'),
                'DbM' => get_string('dbmajor', 'qtype_musictheory'),
                'GbM' => get_string('gbmajor', 'qtype_musictheory'),
                'CbM' => get_string('cbmajor', 'qtype_musictheory')
            );
        } else {
            $selectoptionskeymode = array(
                ''    => get_string('selectakey', 'qtype_musictheory'),
                'Anm' => get_string('anminor', 'qtype_musictheory'),
                'Enm' => get_string('enminor', 'qtype_musictheory'),
                'Bnm' => get_string('bnminor', 'qtype_musictheory'),
                'F#m' => get_string('fsharpminor', 'qtype_musictheory'),
                'C#m' => get_string('csharpminor', 'qtype_musictheory'),
                'G#m' => get_string('gsharpminor', 'qtype_musictheory'),
                'D#m' => get_string('dsharpminor', 'qtype_musictheory'),
                'A#m' => get_string('asharpminor', 'qtype_musictheory'),
                'Dnm' => get_string('dnminor', 'qtype_musictheory'),
                'Gnm' => get_string('gnminor', 'qtype_musictheory'),
                'Cnm' => get_string('cnminor', 'qtype_musictheory'),
                'Fnm' => get_string('fnminor', 'qtype_musictheory'),
                'Bbm' => get_string('bbminor', 'qtype_musictheory'),
                'Ebm' => get_string('ebminor', 'qtype_musictheory'),
                'Abm' => get_string('abminor', 'qtype_musictheory')
            );
        }

        if ($options->correctness) {
            $corrresp = $question->get_correct_response();
            if (!is_null($currans)) {
                if ($currans === $corrresp['answer']) {
                    $selectattributes['class'] = $this->feedback_class(1);
                } else {
                    $selectattributes['class'] = $this->feedback_class(0);
                }
            }
        }

        $questiontext = $question->format_questiontext($qa);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $nonjavascriptdivattr = array(
            'id' => 'musictheory_div_replacedbycanvas_' . $inputname
        );
        $correctanswertext = ($correctanswer == '') ?
                get_string('emptykeysignature', 'qtype_musictheory') :
                $correctanswer;
        $correctanswertext = str_replace('#', '&#9839;', $correctanswertext);
        $correctanswertext = str_replace('b', '&#9837;', $correctanswertext);
        $clef = get_string($question->musictheory_clef . 'clef', 'qtype_musictheory');
        $musicquestionastext = '<b>' . $clef . ': ' . $correctanswertext . '</b>';
        $result .= html_writer::tag('div', $musicquestionastext, $nonjavascriptdivattr);

        $javascriptdivattr = array(
            'id'    => 'musictheory_div_canvas_' . $inputname,
            'class' => 'ablock',
            'style' => 'display:none'
        );
        $result .= html_writer::tag('div', '', $javascriptdivattr);

        $input = html_writer::select($selectoptionskeymode, $inputname, $currans, true, $selectattributes);
        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= $input;
        $result .= html_writer::end_tag('div');

        if ($qa->get_state() == question_state::$invalid) {
            $currentanswer = $qa->get_last_qt_var('answer');
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error(array('answer' => $currentanswer)),
                                                                                        array('class' => 'validationerror'));
        }

        return $result;
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $correctresponsearray = $question->get_correct_response();
        $keyindex = str_replace('#', 'sharp', $correctresponsearray['answer']);
        if (strpos($keyindex, 'M') !== false) {
            $keyindex = str_replace('M', 'major', $keyindex);
        } else {
            $keyindex = str_replace('m', 'minor', $keyindex);
        }
        $correctresponse = get_string(strtolower($keyindex), 'qtype_musictheory');
        return get_string('correctansweris', 'qtype_musictheory') . ' <b>' . $correctresponse . '</b>';
    }

}
