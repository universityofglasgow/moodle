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
 * This file defines question and grading strategy classes for music theory
 * question subtypes related to harmonic functions.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * The music theory harmonic function writing question subtype.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_harmonicfunction_write extends qtype_musictheory_question implements qtype_musictheory_subtype {

    public function get_supported_grading_strategies() {
        return array(
            'qtype_musictheory_strategy_harmonicfunctionwrite_allornothing'
        );
    }

    public function get_expected_data() {
        return array('answer' => PARAM_CLEANHTML);
    }

    public function grade_response(array $response) {
        $correctresponse = $this->get_correct_response();
        return $this->gradingstrategy->grade($response, $correctresponse);
    }

    public function get_correct_response() {
        $hfprimary = $this->musictheory_hfprimary;
        $hfinvext = ($this->musictheory_hfinvext === 'r') ? '' : $this->musictheory_hfinvext;
        $hfsecondary = ($this->musictheory_hfsecondary === 'none') ? '' : $this->musictheory_hfsecondary;
        $tonicltr = substr($this->musictheory_keymode, 0, 1);
        $tonicacc = substr($this->musictheory_keymode, 1, 1);
        $tonic = new Note($tonicltr, $tonicacc, 4);
        $ismajor = (substr($this->musictheory_keymode, 2, 1) === 'M');
        $tonality = new Tonality($tonic, $ismajor);
        switch ($this->musictheory_clef) {
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
        $hf = new HarmonicFunction($tonality, $hfprimary . $hfinvext . $hfsecondary, $reg);
        return array('answer' => (string) $hf);
    }

    public function is_complete_response(array $response) {
        if (!isset($response['answer'])) {
            return false;
        }

        $regex = '/^([A-G](n|\#|b|x|bb)[1-6]){1}(,[A-G](n|\#|b|x|bb)[1-6])*$/';
        return preg_match($regex, $response['answer']);
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'answer');
    }

    public function summarise_response(array $response) {
        if (!array_key_exists('answer', $response)) {
            return null;
        } else {
            return str_replace(' ', '', $response['answer']);
        }
    }

    public function get_validation_error(array $response) {
        if (empty($response['answer'])) {
            return get_string('validationerror_empty', 'qtype_musictheory');
        } else if (preg_match('/\s/', $response['answer'])) {
            return get_string('validationerror_whitespace', 'qtype_musictheory');
        }

        global $OUTPUT;
        $help = $OUTPUT->help_icon('harmonicfunction_write_questionastext', 'qtype_musictheory', true);
        return get_string('validationerror_invalidsyntax', 'qtype_musictheory') . $help;
    }

    public function get_question_text() {
        $qtext = get_string('questiontext_harmonicfunction_write', 'qtype_musictheory');
        $keyindex = str_replace('#', 'sharp', $this->musictheory_keymode);
        if (strpos($keyindex, 'M') !== false) {
            $keyindex = str_replace('M', 'major', $keyindex);
        } else {
            $keyindex = str_replace('m', 'minor', $keyindex);
        }
        $key = get_string(strtolower($keyindex), 'qtype_musictheory');
        $invext = ($this->musictheory_hfinvext === 'r') ? '' : $this->musictheory_hfinvext;
        $hfsec = ($this->musictheory_hfsecondary === 'none') ? '' : $this->musictheory_hfsecondary;
        $harmonicfunction = $this->musictheory_hfprimary . $invext . $hfsec;
        if ($harmonicfunction === 'Gr' || $harmonicfunction === 'It' || $harmonicfunction === 'Fr') {
            $harmonicfunction = get_string('aug6th' . strtolower($harmonicfunction), 'qtype_musictheory');
        }
        $harmonicfunction = str_replace('-o', '&#248;', $harmonicfunction);
        return $qtext . ': <b>' . $key . ', ' . $harmonicfunction . '</b>';
    }

    /**
     * Returns the number of notes in the correct answer for this question.
     *
     * @return integer The number of notes in the correct answer.
     */
    public function get_answer_num_notes() {
        $correctresponsearray = $this->get_correct_response();
        $correctresponse = $correctresponsearray['answer'];
        $maxnotes = count(explode(',', $correctresponse));
        return $maxnotes;
    }

}

/**
 * The music theory random harmonic function writing question subtype.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_harmonicfunction_write_random extends qtype_musictheory_harmonicfunction_write {

    public function start_attempt(question_attempt_step $step, $variant) {
        $this->musictheory_clef = qtype_musictheory_randomiser::get_random_field($this->musictheory_clef_random);
        $this->musictheory_keymode = qtype_musictheory_randomiser::get_random_key($this->musictheory_mode_random, false);
        $hftype = qtype_musictheory_randomiser::get_random_field($this->musictheory_harmonicfunctiontype_random);
        $harmonicfunction = qtype_musictheory_randomiser::get_random_harmonicfunction($hftype, $this->musictheory_keymode);
        $this->musictheory_hfprimary = $harmonicfunction['primary'];
        $this->musictheory_hfinvext = $harmonicfunction['invext'];
        $this->musictheory_hfsecondary = $harmonicfunction['secondary'];
        $this->musictheory_optionsxml = $this->qtype->get_options_xml($this, 'harmonicfunction-write');
        $this->questiontext = $this->get_question_text();
        $step->set_qt_var('_var_clef', $this->musictheory_clef);
        $step->set_qt_var('_var_keymode', $this->musictheory_keymode);
        $step->set_qt_var('_var_hfprimary', $this->musictheory_hfprimary);
        $step->set_qt_var('_var_hfsecondary', $this->musictheory_hfsecondary);
        $step->set_qt_var('_var_hfinvext', $this->musictheory_hfinvext);
        $step->set_qt_var('_var_optionsxml', $this->musictheory_optionsxml);
        $step->set_qt_var('_var_questiontext', $this->questiontext);
        parent::start_attempt($step, $variant);
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->musictheory_clef = $step->get_qt_var('_var_clef');
        $this->musictheory_keymode = $step->get_qt_var('_var_keymode');
        $this->musictheory_hfprimary = $step->get_qt_var('_var_hfprimary');
        $this->musictheory_hfsecondary = $step->get_qt_var('_var_hfsecondary');
        $this->musictheory_hfinvext = $step->get_qt_var('_var_hfinvext');
        $this->musictheory_optionsxml = $step->get_qt_var('_var_optionsxml');
        $this->questiontext = $step->get_qt_var('_var_questiontext');
        parent::apply_attempt_state($step);
    }

}

/**
 * The music theory harmonic function identification question subtype.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_harmonicfunction_identify extends qtype_musictheory_question implements qtype_musictheory_subtype {

    public function get_supported_grading_strategies() {
        return array(
            'qtype_musictheory_strategy_harmonicfunctionid_allornothing'
        );
    }

    public function grade_response(array $response) {

        $correctresponse = $this->get_correct_response();
        return $this->gradingstrategy->grade($response, $correctresponse, $this->musictheory_keymode);
    }

    public function get_correct_response() {
        $corrresp = array(
            'musictheory_answer_hfprimary' => $this->musictheory_hfprimary,
            'musictheory_answer_hfinvext'  => $this->musictheory_hfinvext
        );
        if ($this->hftypesinresponsehavesectonic()) {
            $corrresp['musictheory_answer_hfsecondary'] = $this->musictheory_hfsecondary;
        }
        return $corrresp;
    }

    public function get_expected_data() {
        $expdata = array(
            'musictheory_answer_hfprimary' => PARAM_TEXT,
            'musictheory_answer_hfinvext'  => PARAM_TEXT
        );
        if ($this->hftypesinresponsehavesectonic()) {
            $expdata['musictheory_answer_hfsecondary'] = PARAM_TEXT;
        }
        return $expdata;
    }

    public function is_complete_response(array $response) {

        if ($this->hftypesinresponsehavesectonic()) {
            if (!isset($response['musictheory_answer_hfprimary']) ||
                    !isset($response['musictheory_answer_hfinvext']) ||
                    !isset($response['musictheory_answer_hfsecondary'])) {
                return false;
            }
        } else {
            if (!isset($response['musictheory_answer_hfprimary']) ||
                    !isset($response['musictheory_answer_hfinvext'])) {
                return false;
            }
        }

        if ($this->hftypesinresponsehavesectonic()) {
            return (!empty($response['musictheory_answer_hfprimary']) &&
                    !empty($response['musictheory_answer_hfinvext']) &&
                    !empty($response['musictheory_answer_hfsecondary']));
        } else {
            return (!empty($response['musictheory_answer_hfprimary']) &&
                    !empty($response['musictheory_answer_hfinvext']));
        }
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        $samehfprimary = question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'musictheory_answer_hfprimary');
        $samehfinvext = question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'musictheory_answer_hfinvext');
        if ($this->hftypesinresponsehavesectonic()) {
            $samehfsecondary = question_utils::arrays_same_at_key_missing_is_blank(
                            $prevresponse, $newresponse, 'musictheory_answer_hfsecondary');
            return ($samehfprimary && $samehfinvext && $samehfsecondary);
        } else {
            return ($samehfprimary && $samehfinvext);
        }
    }

    public function get_validation_error(array $response) {
        return get_string('validationerror_harmonicfunction_identify', 'qtype_musictheory');
    }

    public function summarise_response(array $response) {
        if ($this->hftypesinresponsehavesectonic()) {
            if (!isset($response['musictheory_answer_hfprimary']) ||
                    !isset($response['musictheory_answer_hfinvext']) ||
                    !isset($response['musictheory_answer_hfsecondary']) ||
                    empty($response['musictheory_answer_hfprimary']) ||
                    empty($response['musictheory_answer_hfinvext']) ||
                    empty($response['musictheory_answer_hfsecondary'])) {
                return '';
            }
        } else {
            if (!isset($response['musictheory_answer_hfprimary']) ||
                    !isset($response['musictheory_answer_hfinvext']) ||
                    empty($response['musictheory_answer_hfprimary']) ||
                    empty($response['musictheory_answer_hfinvext'])) {
                return '';
            }
        }

        $invext = ($response['musictheory_answer_hfinvext'] === 'r') ? '' :
                $response['musictheory_answer_hfinvext'];
        if ($this->hftypesinresponsehavesectonic()) {
            $hfsecondary = ($response['musictheory_answer_hfsecondary'] === 'none') ? '' :
                    $response['musictheory_answer_hfsecondary'];
        } else {
            $hfsecondary = '';
        }
        $harmonicfunction = $response['musictheory_answer_hfprimary'] . $invext . $hfsecondary;
        $harmonicfunction = str_replace('-o', '&#248;', $harmonicfunction);
        return $harmonicfunction;
    }

    public function get_question_text() {
        $qtext = get_string('questiontext_harmonicfunction_identify', 'qtype_musictheory');
        return $qtext . ':';
    }

    /**
     * Determines whether the harmonic function types to be available as answers
     * include a secondary tonic.
     *
     * @return boolean Returns true if at least one harmonic function type includes
     * a secondary tonic.
     */
    public function hftypesinresponsehavesectonic() {
        $ret = false;
        foreach ($this->musictheory_hfidentifyresponsetypes as $hftype) {
            if ($hftype === 'secdomtriad' ||
                    $hftype === 'secdom7th' ||
                    $hftype === 'secnondomtriad' ||
                    $hftype === 'seclttriad' ||
                    $hftype === 'secnondom7th' ||
                    $hftype === 'seclthalfdim' ||
                    $hftype === 'secltfullydim') {
                $ret = true;
            }
        }
        return $ret;
    }

}

/* *
 * The music theory harmonic function random identification question subtype.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_musictheory_harmonicfunction_identify_random extends qtype_musictheory_harmonicfunction_identify {

    public function start_attempt(question_attempt_step $step, $variant) {
        $this->musictheory_clef = qtype_musictheory_randomiser::get_random_field($this->musictheory_clef_random);
        $this->musictheory_keymode = qtype_musictheory_randomiser::get_random_key($this->musictheory_mode_random, false);
        $hftype = qtype_musictheory_randomiser::get_random_field($this->musictheory_harmonicfunctiontype_random);
        $harmonicfunction = qtype_musictheory_randomiser::get_random_harmonicfunction($hftype, $this->musictheory_keymode);
        $this->musictheory_hfidentifyresponsetypes = $this->musictheory_harmonicfunctiontype_random;
        $this->musictheory_hfprimary = $harmonicfunction['primary'];
        $this->musictheory_hfinvext = ($harmonicfunction['invext'] === '') ? 'r' : $harmonicfunction['invext'];
        $this->musictheory_hfsecondary = ($harmonicfunction['secondary'] === '') ? 'none' : $harmonicfunction['secondary'];
        $this->musictheory_optionsxml = $this->qtype->get_options_xml($this, 'harmonicfunction-identify');
        $this->questiontext = $this->get_question_text();
        $step->set_qt_var('_var_clef', $this->musictheory_clef);
        $step->set_qt_var('_var_keymode', $this->musictheory_keymode);
        $step->set_qt_var('_var_hfidentifyresponsetypes', implode(',', $this->musictheory_hfidentifyresponsetypes));
        $step->set_qt_var('_var_hfprimary', $this->musictheory_hfprimary);
        $step->set_qt_var('_var_hfsecondary', $this->musictheory_hfsecondary);
        $step->set_qt_var('_var_hfinvext', $this->musictheory_hfinvext);
        $step->set_qt_var('_var_optionsxml', $this->musictheory_optionsxml);
        $step->set_qt_var('_var_questiontext', $this->questiontext);
        parent::start_attempt($step, $variant);
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->musictheory_clef = $step->get_qt_var('_var_clef');
        $this->musictheory_keymode = $step->get_qt_var('_var_keymode');
        $this->musictheory_hfidentifyresponsetypes = explode(',', $step->get_qt_var('_var_hfidentifyresponsetypes'));
        $this->musictheory_hfprimary = $step->get_qt_var('_var_hfprimary');
        $this->musictheory_hfsecondary = $step->get_qt_var('_var_hfsecondary');
        $this->musictheory_hfinvext = $step->get_qt_var('_var_hfinvext');
        $this->musictheory_optionsxml = $step->get_qt_var('_var_optionsxml');
        $this->questiontext = $step->get_qt_var('_var_questiontext');
        parent::apply_attempt_state($step);
    }

}

/**
 * A grading strategy that applies the all-or-nothing approach for a harmonic
 * function writing question (ignoring the register)..
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_strategy_harmonicfunctionwrite_allornothing implements qtype_musictheory_grading_strategy {

    public function grade($response, $correctresponse) {

        $fraction = 1;
        $resp = explode(',', $response['answer']);
        $corrresp = explode(',', $correctresponse['answer']);

        if (count($resp) !== count($corrresp)) {
            return array(0, question_state::graded_state_for_fraction(0));
        }

        $prevnote = null;
        for ($i = 0; $i < count($resp); $i++) {
            $ansltr = substr($resp[$i], 0, 1);
            $ansacc = substr($resp[$i], 1, strlen($resp[$i]) - 2);
            $ansreg = substr($resp[$i], strlen($resp[$i]) - 1, 1);
            $respltr = substr($corrresp[$i], 0, 1);
            $respacc = substr($corrresp[$i], 1, strlen($corrresp[$i]) - 2);

            $currnote = new Note($ansltr, $ansacc, $ansreg);
            if ($prevnote !== null) {
                if ($currnote->getSizeDifferential($prevnote) > 4) {
                    $fraction = 0;
                    break;
                }
            }
            $prevnote = $currnote;

            if ($ansltr !== $respltr || $ansacc !== $respacc) {
                $fraction = 0;
                break;
            }
        }

        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

}

/**
 * A grading strategy that applies the all-or-nothing approach for a harmonic
 * function identification question.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_strategy_harmonicfunctionid_allornothing implements qtype_musictheory_grading_strategy {

    public function grade($response, $correctresponse, $tonality = null) {

        $fraction = 1;

        if ($response['musictheory_answer_hfprimary'] !==
                $correctresponse['musictheory_answer_hfprimary'] ||
                $response['musictheory_answer_hfinvext'] !==
                $correctresponse['musictheory_answer_hfinvext']) {
            $fraction = 0;
        }
        // Check optional secondary tonic if it is part of the response.
        if (isset($response['musictheory_answer_hfsecondary'])) {
            if ($response['musictheory_answer_hfsecondary'] !==
                    $correctresponse['musictheory_answer_hfsecondary']) {
                $fraction = 0;
            }
        }

        // Check if alernate answer is valid.
        if ($fraction === 0) {

            $rinvext = ($response['musictheory_answer_hfinvext'] === 'r') ? '' :
                    $response['musictheory_answer_hfinvext'];
            if (isset($response['musictheory_answer_hfsecondary'])) {
                $rhfsecondary = ($response['musictheory_answer_hfsecondary'] === 'none') ? '' :
                        $response['musictheory_answer_hfsecondary'];
            } else {
                $rhfsecondary = '';
            }
            $respfunc = $response['musictheory_answer_hfprimary'] . $rinvext . $rhfsecondary;

            $cinvext = ($correctresponse['musictheory_answer_hfinvext'] === 'r') ? '' :
                    $correctresponse['musictheory_answer_hfinvext'];
            if (isset($correctresponse['musictheory_answer_hfsecondary'])) {
                $chfsecondary = ($correctresponse['musictheory_answer_hfsecondary'] === 'none') ? '' :
                        $correctresponse['musictheory_answer_hfsecondary'];
            } else {
                $chfsecondary = '';
            }
            $correctrespfunc = $correctresponse['musictheory_answer_hfprimary'] . $cinvext . $chfsecondary;

            $tonic = new Note(substr($tonality, 0, 1), substr($tonality, 1, 1));
            $ismajor = (substr($tonality, 2, 1) === 'M') ? true : false;
            $tonal = new Tonality($tonic, $ismajor);
            $resphf = new HarmonicFunction($tonal, $respfunc);
            if ($resphf->isSupported()) {
                $correctresphf = new HarmonicFunction($tonal, $correctrespfunc);
                if ($resphf->equals($correctresphf, true)) {
                    $fraction = 1;
                }
            }
        }

        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

}
