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
 * to harmonic functions.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/renderer.php');

/**
 * Renders music theory harmonic function writing questions.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_harmonicfunction_write_renderer extends qtype_musictheory_renderer {

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
            $result .= $OUTPUT->help_icon('harmonicfunction_write_questionastext', 'qtype_musictheory', '');
        }
        $result .= html_writer::end_tag('div');

        $javascriptdivattr = array(
            'id'    => 'musictheory_div_canvas_' . $inputname,
            'class' => 'ablock',
            'style' => 'display:none'
        );
        $result .= html_writer::start_tag('div', $javascriptdivattr);
        if (!$options->readonly) {
            $result .= $OUTPUT->help_icon('harmonicfunction_write_questionasui', 'qtype_musictheory', '');
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
 * Renders music theory harmonic function identification questions.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_harmonicfunction_identify_renderer extends qtype_musictheory_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        global $PAGE;

        $PAGE->requires->yui_module('moodle-qtype_musictheory-musictheoryui', 'M.qtype_musictheory.musictheoryui.init');
        $question = $qa->get_question();
        $hfprimaryselectid = $qa->get_qt_field_name('musictheory_answer_hfprimary');
        $hfinvextselectid = $qa->get_qt_field_name('musictheory_answer_hfinvext');

        $currhfinvext = $qa->get_last_qt_var('musictheory_answer_hfinvext');
        $currhfprimary = $qa->get_last_qt_var('musictheory_answer_hfprimary');
        $currhfsecondary = $qa->get_last_qt_var('musictheory_answer_hfsecondary');

        if ($question->hftypesinresponsehavesectonic()) {
            $hfsecondaryselectid = $qa->get_qt_field_name('musictheory_answer_hfsecondary');
        }

        $corrresp = $question->get_correct_response();
        $invext = ($corrresp['musictheory_answer_hfinvext'] === 'r') ? '' :
                $corrresp['musictheory_answer_hfinvext'];
        $corrrespstr = $corrresp['musictheory_answer_hfprimary'] . $invext;

        if ($question->hftypesinresponsehavesectonic()) {
            $hfsecondary = ($corrresp['musictheory_answer_hfsecondary'] === 'none') ? '' :
                    $corrresp['musictheory_answer_hfsecondary'];
            $corrrespstr .= $hfsecondary;
        }
        $ltr = substr($question->musictheory_keymode, 0, 1);
        $acc = substr($question->musictheory_keymode, 1, 1);
        $tonic = new Note($ltr, $acc, 4);
        $ismajor = (substr($question->musictheory_keymode, 2, 1) === 'M');
        $tonality = new Tonality($tonic, $ismajor);
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
        $hf = new HarmonicFunction($tonality, $corrrespstr, $reg);
        $initialinput = (string) $hf;

        $moduleparams = array(
            array(
                'inputname'        => $hfprimaryselectid,
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

        $selectoptions = $this->get_response_options($question->musictheory_hfidentifyresponsetypes);

        $selectoptionshfprimary = $selectoptions['hfprimary'];

        $hfprimaryselectattributes = array(
            'name' => $hfprimaryselectid,
            'id'   => $hfprimaryselectid
        );

        $selectoptionsinvext = $selectoptions['hfinvext'];

        $hfinvextselectattributes = array(
            'name' => $hfinvextselectid,
            'id'   => $hfinvextselectid
        );

        if ($question->hftypesinresponsehavesectonic()) {
            $selectoptionshfsecondary = $selectoptions['hfsecondary'];

            $hfsecondaryselectattributes = array(
                'name' => $hfsecondaryselectid,
                'id'   => $hfsecondaryselectid
            );
        }

        if ($options->readonly) {
            $hfprimaryselectattributes['disabled'] = 'true';
            $hfinvextselectattributes['disabled'] = 'true';
            if ($question->hftypesinresponsehavesectonic()) {
                $hfsecondaryselectattributes['disabled'] = 'true';
            }
        }

        $questiontext = $question->format_questiontext($qa);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        $nonjavascriptdivattr = array(
            'id' => 'musictheory_div_replacedbycanvas_' . $hfprimaryselectid
        );

        $musicquestionastext = $initialinput;
        $musicquestionastext = str_replace('#', get_string('acc_sharp', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('b', get_string('acc_b', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('n', get_string('acc_n', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('x', get_string('acc_x', 'qtype_musictheory'), $musicquestionastext);
        $musicquestionastext = str_replace('bb', get_string('acc_bb', 'qtype_musictheory'), $musicquestionastext);

        $result .= html_writer::tag('div', '<b>' . $musicquestionastext . '</b>', $nonjavascriptdivattr);

        $javascriptdivattr = array(
            'id'    => 'musictheory_div_canvas_' . $hfprimaryselectid,
            'class' => 'ablock',
            'style' => 'display:none'
        );
        $result .= html_writer::tag('div', '', $javascriptdivattr);

        $keyindex = str_replace('#', 'sharp', $question->musictheory_keymode);
        if (strpos($keyindex, 'M') !== false) {
            $keyindex = str_replace('M', 'major', $keyindex);
        } else {
            $keyindex = str_replace('m', 'minor', $keyindex);
        }
        $key = get_string(strtolower($keyindex), 'qtype_musictheory');

        $input = html_writer::start_span() . '<b>' . $key . ':</b> ' . html_writer::end_span();
        $input .= html_writer::select($selectoptionshfprimary, $hfprimaryselectid, $currhfprimary, true,
                $hfprimaryselectattributes);
        $input .= html_writer::select($selectoptionsinvext, $hfinvextselectid, $currhfinvext, true, $hfinvextselectattributes);
        if ($question->hftypesinresponsehavesectonic()) {
            $input .= html_writer::select($selectoptionshfsecondary, $hfsecondaryselectid, $currhfsecondary, true,
                                          $hfsecondaryselectattributes);
        }
        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= $input;
        $result .= html_writer::end_tag('div');

        if ($qa->get_state() == question_state::$invalid) {
            $currentinvext = $qa->get_last_qt_var('musictheory_answer_invext');
            $currenthfprimary = $qa->get_last_qt_var('musictheory_answer_hfprimary');
            $currenthfsecondary = $qa->get_last_qt_var('musictheory_answer_hfsecondary');
            $answerarray = array(
                'musictheory_answer_invext'    => $currentinvext,
                'musictheory_answer_hfprimary' => $currenthfprimary
            );
            if ($question->hftypesinresponsehavesectonic()) {
                $answerarray['musictheory_answer_hfsecondary'] = $currenthfsecondary;
            }
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($answerarray),
                                                                                        array('class' => 'validationerror'));
        }

        return $result;
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $correctresponsearray = $question->get_correct_response();
        $invext = ($correctresponsearray['musictheory_answer_hfinvext'] === 'r') ?
                '' :
                $correctresponsearray['musictheory_answer_hfinvext'];
        $harmonicfunction = $correctresponsearray['musictheory_answer_hfprimary'] . $invext;
        if ($question->hftypesinresponsehavesectonic()) {
            $hfsecondary = ($correctresponsearray['musictheory_answer_hfsecondary'] === 'none') ?
                    '' :
                    $correctresponsearray['musictheory_answer_hfsecondary'];
            $harmonicfunction .= $hfsecondary;
        }
        $harmonicfunction = str_replace('-o', '&#248;', $harmonicfunction);
        return get_string('correctansweris', 'qtype_musictheory') . ' <b>' . $harmonicfunction . '</b>';
    }

    /**
     * Returns the possible dropdown menu options given the harmonic function
     * types selected for display in the question options.
     *
     * @param array $hftypes The harmonic function types to display as possible
     * answers.
     * @return array An array with three incides ('hfprimary', 'hfinvext', 'hfsecondary'),
     * each pointing to an array of dropdown menu options.
     */
    private function get_response_options($hftypes) {

        $hfprimary = array();
        $hfinvext = array();
        $hfsecondary = array();
        foreach ($hftypes as $type) {
            switch ($type) {
                case 'diatonictriad':
                    $hfprimary['100_I'] = 'I';
                    $hfprimary['150_i'] = 'i';
                    $hfprimary['200_ii'] = 'ii';
                    $hfprimary['250_iio'] = 'iio';
                    $hfprimary['300_III'] = 'III';
                    $hfprimary['350_iii'] = 'iii';
                    $hfprimary['375_III+'] = 'III+';
                    $hfprimary['400_IV'] = 'IV';
                    $hfprimary['400_iv'] = 'iv';
                    $hfprimary['500_V'] = 'V';
                    $hfprimary['550_v'] = 'v';
                    $hfprimary['600_VI'] = 'VI';
                    $hfprimary['650_vi'] = 'vi';
                    $hfprimary['675_vio'] = 'vio';
                    $hfprimary['700_VII'] = 'VII';
                    $hfprimary['750_viio'] = 'viio';
                    $hfinvext['10_r'] = get_string('rootposition', 'qtype_musictheory');
                    $hfinvext['20_6'] = '6';
                    $hfinvext['30_64'] = '6/4';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    break;
                case 'dom7th';
                    $hfprimary['500_V'] = 'V';
                    $hfinvext['40_7'] = '7';
                    $hfinvext['50_65'] = '6/5';
                    $hfinvext['60_43'] = '4/3';
                    $hfinvext['70_42'] = '4/2';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    break;
                case 'nondom7th':
                    $hfprimary['100_I'] = 'I';
                    $hfprimary['150_i'] = 'i';
                    $hfprimary['200_ii'] = 'ii';
                    $hfprimary['275_ii-o'] = 'ii&#248;';
                    $hfprimary['300_III'] = 'III';
                    $hfprimary['350_iii'] = 'iii';
                    $hfprimary['400_IV'] = 'IV';
                    $hfprimary['400_iv'] = 'iv';
                    $hfprimary['600_VI'] = 'VI';
                    $hfprimary['650_vi'] = 'vi';
                    $hfprimary['680_vi-o'] = 'vi&#248;';
                    $hfinvext['40_7'] = '7';
                    $hfinvext['50_65'] = '6/5';
                    $hfinvext['60_43'] = '4/3';
                    $hfinvext['70_42'] = '4/2';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    break;
                case 'leadingtone7thhalfdim':
                    $hfprimary['775_vii-o'] = 'vii&#248;';
                    $hfinvext['40_7'] = '7';
                    $hfinvext['50_65'] = '6/5';
                    $hfinvext['60_43'] = '4/3';
                    $hfinvext['70_42'] = '4/2';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    break;
                case 'leadingtone7thfullydim':
                    $hfprimary['750_viio'] = 'viio';
                    $hfinvext['40_7'] = '7';
                    $hfinvext['50_65'] = '6/5';
                    $hfinvext['60_43'] = '4/3';
                    $hfinvext['70_42'] = '4/2';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    break;
                case 'secdomtriad':
                    $hfprimary['500_V'] = 'V';
                    $hfinvext['10_r'] = get_string('rootposition', 'qtype_musictheory');
                    $hfinvext['20_6'] = '6';
                    $hfinvext['30_64'] = '6/4';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    $hfsecondary['200_/ii'] = '/ii';
                    $hfsecondary['300_/III'] = '/III';
                    $hfsecondary['350_/iii'] = '/iii';
                    $hfsecondary['400_/IV'] = '/IV';
                    $hfsecondary['400_/iv'] = '/iv';
                    $hfsecondary['500_/V'] = '/V';
                    $hfsecondary['600_/VI'] = '/VI';
                    $hfsecondary['650_/vi'] = '/vi';
                    $hfsecondary['700_/VII'] = '/VII';
                    break;
                case 'secdom7th':
                    $hfprimary['500_V'] = 'V';
                    $hfinvext['40_7'] = '7';
                    $hfinvext['50_65'] = '6/5';
                    $hfinvext['60_43'] = '4/3';
                    $hfinvext['70_42'] = '4/2';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    $hfsecondary['200_/ii'] = '/ii';
                    $hfsecondary['300_/III'] = '/III';
                    $hfsecondary['350_/iii'] = '/iii';
                    $hfsecondary['400_/IV'] = '/IV';
                    $hfsecondary['400_/iv'] = '/iv';
                    $hfsecondary['500_/V'] = '/V';
                    $hfsecondary['600_/VI'] = '/VI';
                    $hfsecondary['650_/vi'] = '/vi';
                    $hfsecondary['700_/VII'] = '/VII';
                    break;
                case 'secnondomtriad':
                    $hfprimary['200_ii'] = 'ii';
                    $hfprimary['250_iio'] = 'iio';
                    $hfprimary['300_III'] = 'III';
                    $hfprimary['350_iii'] = 'iii';
                    $hfprimary['400_IV'] = 'IV';
                    $hfprimary['400_iv'] = 'iv';
                    $hfprimary['600_VI'] = 'VI';
                    $hfprimary['650_vi'] = 'vi';
                    $hfprimary['675_vio'] = 'vio';
                    $hfinvext['10_r'] = get_string('rootposition', 'qtype_musictheory');
                    $hfinvext['20_6'] = '6';
                    $hfinvext['30_64'] = '6/4';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    $hfsecondary['200_/ii'] = '/ii';
                    $hfsecondary['300_/III'] = '/III';
                    $hfsecondary['350_/iii'] = '/iii';
                    $hfsecondary['400_/IV'] = '/IV';
                    $hfsecondary['400_/iv'] = '/iv';
                    $hfsecondary['500_/V'] = '/V';
                    $hfsecondary['600_/VI'] = '/VI';
                    $hfsecondary['650_/vi'] = '/vi';
                    $hfsecondary['700_/VII'] = '/VII';
                    break;
                case 'secnondom7th':
                    $hfprimary['200_ii'] = 'ii';
                    $hfprimary['275_ii-o'] = 'ii&#248;';
                    $hfprimary['300_III'] = 'III';
                    $hfprimary['350_iii'] = 'iii';
                    $hfprimary['400_IV'] = 'IV';
                    $hfprimary['400_iv'] = 'iv';
                    $hfprimary['600_VI'] = 'VI';
                    $hfprimary['650_vi'] = 'vi';
                    $hfprimary['680_vi-o'] = 'vi&#248;';
                    $hfinvext['40_7'] = '7';
                    $hfinvext['50_65'] = '6/5';
                    $hfinvext['60_43'] = '4/3';
                    $hfinvext['70_42'] = '4/2';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    $hfsecondary['200_/ii'] = '/ii';
                    $hfsecondary['300_/III'] = '/III';
                    $hfsecondary['350_/iii'] = '/iii';
                    $hfsecondary['400_/IV'] = '/IV';
                    $hfsecondary['400_/iv'] = '/iv';
                    $hfsecondary['500_/V'] = '/V';
                    $hfsecondary['600_/VI'] = '/VI';
                    $hfsecondary['650_/vi'] = '/vi';
                    $hfsecondary['700_/VII'] = '/VII';
                    break;
                case 'seclttriad':
                    $hfprimary['750_viio'] = 'viio';
                    $hfinvext['10_r'] = get_string('rootposition', 'qtype_musictheory');
                    $hfinvext['20_6'] = '6';
                    $hfinvext['30_64'] = '6/4';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    $hfsecondary['200_/ii'] = '/ii';
                    $hfsecondary['300_/III'] = '/III';
                    $hfsecondary['350_/iii'] = '/iii';
                    $hfsecondary['400_/IV'] = '/IV';
                    $hfsecondary['400_/iv'] = '/iv';
                    $hfsecondary['500_/V'] = '/V';
                    $hfsecondary['600_/VI'] = '/VI';
                    $hfsecondary['650_/vi'] = '/vi';
                    $hfsecondary['700_/VII'] = '/VII';
                    break;
                case 'seclthalfdim':
                    $hfprimary['775_vii-o'] = 'vii&#248;';
                    $hfinvext['40_7'] = '7';
                    $hfinvext['50_65'] = '6/5';
                    $hfinvext['60_43'] = '4/3';
                    $hfinvext['70_42'] = '4/2';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    $hfsecondary['200_/ii'] = '/ii';
                    $hfsecondary['300_/III'] = '/III';
                    $hfsecondary['350_/iii'] = '/iii';
                    $hfsecondary['400_/IV'] = '/IV';
                    $hfsecondary['400_/iv'] = '/iv';
                    $hfsecondary['500_/V'] = '/V';
                    $hfsecondary['600_/VI'] = '/VI';
                    $hfsecondary['650_/vi'] = '/vi';
                    $hfsecondary['700_/VII'] = '/VII';
                    break;
                case 'secltfullydim':
                    $hfprimary['750_viio'] = 'viio';
                    $hfinvext['40_7'] = '7';
                    $hfinvext['50_65'] = '6/5';
                    $hfinvext['60_43'] = '4/3';
                    $hfinvext['70_42'] = '4/2';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    $hfsecondary['200_/ii'] = '/ii';
                    $hfsecondary['300_/III'] = '/III';
                    $hfsecondary['350_/iii'] = '/iii';
                    $hfsecondary['400_/IV'] = '/IV';
                    $hfsecondary['400_/iv'] = '/iv';
                    $hfsecondary['500_/V'] = '/V';
                    $hfsecondary['600_/VI'] = '/VI';
                    $hfsecondary['650_/vi'] = '/vi';
                    $hfsecondary['700_/VII'] = '/VII';
                    break;
                case 'neapolitan':
                    $hfprimary['280_N'] = 'N';
                    $hfinvext['10_r'] = get_string('rootposition', 'qtype_musictheory');
                    $hfinvext['20_6'] = '6';
                    $hfinvext['30_64'] = '6/4';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    break;
                case 'aug6th':
                    $hfprimary['800_Gr'] = get_string('aug6thgr', 'qtype_musictheory');
                    $hfprimary['800_It'] = get_string('aug6thit', 'qtype_musictheory');
                    $hfprimary['800_Fr'] = get_string('aug6thfr', 'qtype_musictheory');
                    $hfinvext['10_r'] = get_string('rootposition', 'qtype_musictheory');
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    break;
                case 'extendeddom';
                    $hfprimary['500_V'] = 'V';
                    $hfinvext['80_9'] = '9';
                    $hfinvext['85_11'] = '11';
                    $hfinvext['90_13'] = '13';
                    $hfsecondary['100_none'] = get_string('nosectonic', 'qtype_musictheory');
                    break;
            }
        }

        $hfprimary2 = array();
        $hfprimary2[''] = get_string('selectanoption', 'qtype_musictheory');
        $hfinvext2 = array();
        $hfinvext2[''] = get_string('selectanoption', 'qtype_musictheory');
        $hfsecondary2 = array();
        $hfsecondary2[''] = get_string('selectanoption', 'qtype_musictheory');

        ksort($hfprimary);
        foreach ($hfprimary as $key => $value) {
            $hfprimary2[substr($key, 4)] = $value;
        }

        ksort($hfinvext);
        foreach ($hfinvext as $key => $value) {
            $hfinvext2[substr($key, 3)] = $value;
        }

        ksort($hfsecondary);
        foreach ($hfsecondary as $key => $value) {
            $hfsecondary2[substr($key, 4)] = $value;
        }

        return array(
            'hfprimary'   => $hfprimary2,
            'hfinvext'    => $hfinvext2,
            'hfsecondary' => $hfsecondary2
        );
    }

}
