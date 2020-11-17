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
 * question subtypes related to chordquality.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * The music theory chord quality writing question subtype.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_chordquality_write extends qtype_musictheory_question implements qtype_musictheory_subtype {

    public function get_supported_grading_strategies() {
        return array(
            'qtype_musictheory_strategy_chordqualitywrite_allornothing'
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
        switch ($this->musictheory_chordquality) {
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
        if ($this->musictheory_clef === 'treble') {
            $reg = 4;
        } else {
            $reg = 3;
        }
        $root = new Note($this->musictheory_givennoteletter, $this->musictheory_givennoteaccidental, $reg);
        $chord = new Chord($root, $quality, 0);
        return array('answer' => (string) $chord);
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
        $help = $OUTPUT->help_icon('chordquality_write_questionastext', 'qtype_musictheory', true);
        return get_string('validationerror_invalidsyntax', 'qtype_musictheory') . $help;
    }

    public function get_question_text() {
        $qtext = get_string('questiontext_chordquality_write', 'qtype_musictheory');
        switch ($this->musictheory_givennoteaccidental) {
            case 'n':
                $acc = '';
                break;
            case 'b':
            case 'x':
            case 'bb':
                $acc = get_string('acc_' . $this->musictheory_givennoteaccidental, 'qtype_musictheory');
                break;
            case '#':
                $acc = get_string('acc_sharp', 'qtype_musictheory');
                break;
        }
        $chord = get_string('note' . strtolower($this->musictheory_givennoteletter), 'qtype_musictheory');
        $quality = get_string($this->musictheory_chordquality, 'qtype_musictheory');
        $chord .= $acc . ' ' . $quality;

        return $qtext . ': <b>' . $chord . '</b>';
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
 * The music theory random chord quality writing question subtype.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_chordquality_write_random extends qtype_musictheory_chordquality_write {

    public function start_attempt(question_attempt_step $step, $variant) {
        $this->musictheory_clef = qtype_musictheory_randomiser::get_random_field($this->musictheory_clef_random);
        $this->musictheory_chordquality = qtype_musictheory_randomiser::get_random_field($this->musictheory_chordquality_random);
        $this->musictheory_givennoteletter = qtype_musictheory_randomiser::get_random_letter_name();
        $this->musictheory_givennoteaccidental = qtype_musictheory_randomiser::get_random_accidental(false);
        if ($this->musictheory_chordquality == 'augmented' && $this->musictheory_givennoteletter == 'B' &&
                $this->musictheory_givennoteaccidental == '#') {
            $this->musictheory_givennoteaccidental = 'n';
        }
        $this->musictheory_optionsxml = $this->qtype->get_options_xml($this, 'chordquality-write');
        $this->questiontext = $this->get_question_text();
        $step->set_qt_var('_var_clef', $this->musictheory_clef);
        $step->set_qt_var('_var_chordquality', $this->musictheory_chordquality);
        $step->set_qt_var('_var_givennoteletter', $this->musictheory_givennoteletter);
        $step->set_qt_var('_var_givennoteaccidental', $this->musictheory_givennoteaccidental);
        $step->set_qt_var('_var_optionsxml', $this->musictheory_optionsxml);
        $step->set_qt_var('_var_questiontext', $this->questiontext);
        parent::start_attempt($step, $variant);
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->musictheory_clef = $step->get_qt_var('_var_clef');
        $this->musictheory_chordquality = $step->get_qt_var('_var_chordquality');
        $this->musictheory_givennoteletter = $step->get_qt_var('_var_givennoteletter');
        $this->musictheory_givennoteaccidental = $step->get_qt_var('_var_givennoteaccidental');
        $this->musictheory_optionsxml = $step->get_qt_var('_var_optionsxml');
        $this->questiontext = $step->get_qt_var('_var_questiontext');
        parent::apply_attempt_state($step);
    }

}

/**
 * The music theory chord quality identification question subtype.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_chordquality_identify extends qtype_musictheory_question implements qtype_musictheory_subtype {

    public function get_supported_grading_strategies() {
        return array(
            'qtype_musictheory_strategy_all_or_nothing'
        );
    }

    public function grade_response(array $response) {

        $correctresponse = $this->get_correct_response();
        return $this->gradingstrategy->grade($response, $correctresponse);
    }

    public function get_correct_response() {
        $corrresp = array(
            'musictheory_answer_rootletter'   => $this->musictheory_givennoteletter,
            'musictheory_answer_rootacc'      => $this->musictheory_givennoteaccidental,
            'musictheory_answer_chordquality' => $this->musictheory_chordquality
        );
        return $corrresp;
    }

    public function get_expected_data() {
        $expdata = array(
            'musictheory_answer_rootletter'   => PARAM_TEXT,
            'musictheory_answer_rootacc'      => PARAM_TEXT,
            'musictheory_answer_chordquality' => PARAM_TEXT
        );
        return $expdata;
    }

    public function is_complete_response(array $response) {

        if (!isset($response['musictheory_answer_rootletter']) ||
                !isset($response['musictheory_answer_rootacc']) ||
                !isset($response['musictheory_answer_chordquality'])) {
            return false;
        }

        return (!empty($response['musictheory_answer_rootletter']) &&
                !empty($response['musictheory_answer_rootacc']) &&
                !empty($response['musictheory_answer_chordquality']));
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        $samerootletter = question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'musictheory_answer_rootletter');
        $samerootacc = question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'musictheory_answer_rootacc');
        $samechordquality = question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'musictheory_answer_chordquality');
        return ($samerootletter && $samerootacc && $samechordquality);
    }

    public function get_validation_error(array $response) {
        return get_string('validationerror_chordquality_identify', 'qtype_musictheory');
    }

    public function summarise_response(array $response) {

        if (!isset($response['musictheory_answer_rootletter']) ||
                empty($response['musictheory_answer_rootletter']) ||
                !isset($response['musictheory_answer_rootacc']) ||
                empty($response['musictheory_answer_rootacc']) ||
                !isset($response['musictheory_answer_chordquality']) ||
                empty($response['musictheory_answer_chordquality'])) {
            return '';
        }
        $root = get_string('note' . strtolower($response['musictheory_answer_rootletter']), 'qtype_musictheory');
        $acckey = 'acc_' . str_replace('#', 'sharp', $response['musictheory_answer_rootacc']);
        $acc = get_string($acckey, 'qtype_musictheory');
        $quality = get_string($response['musictheory_answer_chordquality'], 'qtype_musictheory');

        return $root . $acc . ' ' . $quality;
    }

    public function get_question_text() {
        $qtext = get_string('questiontext_chordquality_identify', 'qtype_musictheory');
        return $qtext . ':';
    }

}

/* *
 * The music theory chord quality random identification question subtype.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_musictheory_chordquality_identify_random extends qtype_musictheory_chordquality_identify {

    public function start_attempt(question_attempt_step $step, $variant) {
        $this->musictheory_clef = qtype_musictheory_randomiser::get_random_field($this->musictheory_clef_random);
        $this->musictheory_chordquality = qtype_musictheory_randomiser::get_random_field($this->musictheory_chordquality_random);
        $this->musictheory_givennoteletter = qtype_musictheory_randomiser::get_random_letter_name();
        $this->musictheory_givennoteaccidental = qtype_musictheory_randomiser::get_random_accidental(false);
        if ($this->musictheory_chordquality == 'augmented' && $this->musictheory_givennoteletter == 'B' &&
                $this->musictheory_givennoteaccidental == '#') {
            $this->musictheory_givennoteaccidental = 'n';
        }
        $this->musictheory_optionsxml = $this->qtype->get_options_xml($this, 'chordquality-identify');
        $this->questiontext = $this->get_question_text();
        $step->set_qt_var('_var_clef', $this->musictheory_clef);
        $step->set_qt_var('_var_chordquality', $this->musictheory_chordquality);
        $step->set_qt_var('_var_givennoteletter', $this->musictheory_givennoteletter);
        $step->set_qt_var('_var_givennoteaccidental', $this->musictheory_givennoteaccidental);
        $step->set_qt_var('_var_optionsxml', $this->musictheory_optionsxml);
        $step->set_qt_var('_var_questiontext', $this->questiontext);
        parent::start_attempt($step, $variant);
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->musictheory_clef = $step->get_qt_var('_var_clef');
        $this->musictheory_chordquality = $step->get_qt_var('_var_chordquality');
        $this->musictheory_givennoteletter = $step->get_qt_var('_var_givennoteletter');
        $this->musictheory_givennoteaccidental = $step->get_qt_var('_var_givennoteaccidental');
        $this->musictheory_optionsxml = $step->get_qt_var('_var_optionsxml');
        $this->questiontext = $step->get_qt_var('_var_questiontext');
        parent::apply_attempt_state($step);
    }

}

/**
 * A grading strategy that applies the all-or-nothing approach for a chord
 * quality question (ignoring the register).
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_strategy_chordqualitywrite_allornothing implements qtype_musictheory_grading_strategy {

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
            $currnote = new Note($ansltr, $ansacc, $ansreg);
            if ($prevnote !== null) {
                if ($currnote->getSizeDifferential($prevnote) > 4) {
                    $fraction = 0;
                    break;
                }
            }
            $prevnote = $currnote;
            $respltr = substr($corrresp[$i], 0, 1);
            $respacc = substr($corrresp[$i], 1, strlen($corrresp[$i]) - 2);

            if ($ansltr !== $respltr || $ansacc !== $respacc) {
                $fraction = 0;
                break;
            }
        }

        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

}
