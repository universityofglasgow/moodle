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
 * question subtypes related to key signatures.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * The music theory key signature writing question subtype.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_keysignature_write extends qtype_musictheory_question implements qtype_musictheory_subtype {

    public function get_supported_grading_strategies() {
        return array(
            'qtype_musictheory_strategy_all_or_nothing'
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
        $tonic = new Note(substr($this->musictheory_keymode, 0, 1), substr($this->musictheory_keymode, 1, 1), 4);
        $mode = (substr($this->musictheory_keymode, 2, 1) == "M");
        $keysign = new KeySignature($tonic, $mode, $this->musictheory_clef);
        return array('answer' => (string) $keysign);
    }

    public function is_complete_response(array $response) {
        if (!isset($response['answer'])) {
            return false;
        }
        $regex = '/^([A-G](\#|b)[2-5],){0,6}([A-G](\#|b)[2-5]){1}$/';
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
        $help = $OUTPUT->help_icon('keysignature_write_questionastext', 'qtype_musictheory', true);
        return get_string('validationerror_invalidsyntax', 'qtype_musictheory') . $help;
    }

    public function get_question_text() {
        $qtext = get_string('questiontext_keysignature_write', 'qtype_musictheory');
        $keyindex = str_replace('#', 'sharp', $this->musictheory_keymode);
        if (strpos($keyindex, 'M') !== false) {
            $keyindex = str_replace('M', 'major', $keyindex);
        } else {
            $keyindex = str_replace('m', 'minor', $keyindex);
        }
        $key = get_string(strtolower($keyindex), 'qtype_musictheory');
        return $qtext . ': <b>' . $key . '</b>';
    }

}

/**
 * The music theory random key signature writing question subtype.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_keysignature_write_random extends qtype_musictheory_keysignature_write {

    public function start_attempt(question_attempt_step $step, $variant) {

        $this->musictheory_clef = qtype_musictheory_randomiser::get_random_field($this->musictheory_clef_random);
        $this->musictheory_keymode = qtype_musictheory_randomiser::get_random_key($this->musictheory_mode_random, false);
        $this->musictheory_optionsxml = $this->qtype->get_options_xml($this, 'keysignature-write');
        $this->questiontext = $this->get_question_text();
        $step->set_qt_var('_var_clef', $this->musictheory_clef);
        $step->set_qt_var('_var_keymode', $this->musictheory_keymode);
        $step->set_qt_var('_var_optionsxml', $this->musictheory_optionsxml);
        $step->set_qt_var('_var_questiontext', $this->questiontext);
        parent::start_attempt($step, $variant);
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->musictheory_clef = $step->get_qt_var('_var_clef');
        $this->musictheory_keymode = $step->get_qt_var('_var_keymode');
        $this->musictheory_optionsxml = $step->get_qt_var('_var_optionsxml');
        $this->questiontext = $step->get_qt_var('_var_questiontext');
        parent::apply_attempt_state($step);
    }

}

/**
 * The music theory key signature identification question subtype.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_keysignature_identify extends qtype_musictheory_question implements qtype_musictheory_subtype {

    public function get_supported_grading_strategies() {
        return array(
            'qtype_musictheory_strategy_all_or_nothing'
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
        return array('answer' => $this->musictheory_keymode);
    }

    public function is_complete_response(array $response) {
        if (!isset($response['answer'])) {
            return false;
        }
        $regex = '/^([A-G](n|\#|b|x|bb)(M|m)){1}$/';
        return preg_match($regex, $response['answer']);
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'answer');
    }

    public function summarise_response(array $response) {
        if (!array_key_exists('answer', $response)) {
            return null;
        } else if (empty($response['answer'])) {
            return '';
        } else {
            $trimmedresponse = str_replace(' ', '', $response['answer']);
            $keyindex = str_replace('#', 'sharp', $trimmedresponse);
            if (strpos($keyindex, 'M') !== false) {
                $keyindex = str_replace('M', 'major', $keyindex);
            } else {
                $keyindex = str_replace('m', 'minor', $keyindex);
            }
            $response = get_string(strtolower($keyindex), 'qtype_musictheory');
            return $response;
        }
    }

    public function get_validation_error(array $response) {
        return get_string('validationerror_empty', 'qtype_musictheory');
    }

    public function get_question_text() {
        $qtextmode = (substr($this->musictheory_keymode, 2, 1) == 'M') ? 'major' : 'minor';
        $qtext = get_string('questiontext_keysignature_identify_' . $qtextmode, 'qtype_musictheory');
        return $qtext . ':';
    }

}

/**
 * The music theory random key signature identification question subtype.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_keysignature_identify_random extends qtype_musictheory_keysignature_identify {

    public function start_attempt(question_attempt_step $step, $variant) {

        $this->musictheory_clef = qtype_musictheory_randomiser::get_random_field($this->musictheory_clef_random);
        $this->musictheory_keymode = qtype_musictheory_randomiser::get_random_key($this->musictheory_mode_random, true);
        $this->musictheory_optionsxml = $this->qtype->get_options_xml($this, 'keysignature-identify');
        $this->questiontext = $this->get_question_text();
        $step->set_qt_var('_var_clef', $this->musictheory_clef);
        $step->set_qt_var('_var_keymode', $this->musictheory_keymode);
        $step->set_qt_var('_var_optionsxml', $this->musictheory_optionsxml);
        $step->set_qt_var('_var_questiontext', $this->questiontext);
        parent::start_attempt($step, $variant);
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->musictheory_clef = $step->get_qt_var('_var_clef');
        $this->musictheory_keymode = $step->get_qt_var('_var_keymode');
        $this->musictheory_optionsxml = $step->get_qt_var('_var_optionsxml');
        $this->questiontext = $step->get_qt_var('_var_questiontext');
        parent::apply_attempt_state($step);
    }

}
