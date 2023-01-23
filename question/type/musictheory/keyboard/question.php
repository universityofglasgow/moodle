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
 * question subtypes related to keyboard input.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * The music theory keyboard input question subtype.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_keyboard_input extends qtype_musictheory_question implements qtype_musictheory_subtype {

    public function get_supported_grading_strategies() {
        return array(
            'qtype_musictheory_strategy_keyboard_allornothing'
        );
    }

    public function get_expected_data() {
        return array('answer' => PARAM_CLEANHTML);
    }

    public function grade_response(array $response) {
        $correctresponse = $this->get_correct_response();
        $params = array();
        $params['considerregister'] = $this->musictheory_considerregister;

        return $this->gradingstrategy->grade($response, $correctresponse, $params);
    }

    public function get_correct_response() {
        $ltr = $this->musictheory_givennoteletter;

        $acc = $this->musictheory_givennoteaccidental;

        if ($this->musictheory_considerregister) {
            $reg = $this->musictheory_givennoteregister;
        } else {
            $reg = 4;
        }

        $note = new Note($ltr, $acc, $reg);
        $pcreg = $note->getPitchClassKeyboardRegister();
        $pitchclass = $pcreg['pitchclass'];
        $reg = $pcreg['register'];

        return array('answer' => $pitchclass . '-' . $reg);
    }

    public function is_complete_response(array $response) {
        if (!isset($response['answer'])) {
            return false;
        }
        if ($response['answer'] === '') {
            return false;
        }

        return true;
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
            $ans = str_replace(' ', '', $response['answer']);
            $respexp = explode('-', $response['answer']);
            $resp = $respexp[0];
            $register = $respexp[1];
            $resp .= ($this->musictheory_considerregister) ? '-' . $register : '';
            return $resp;
        }
    }

    public function get_validation_error(array $response) {
        if (empty($response['answer'])) {
            return get_string('validationerror_empty', 'qtype_musictheory');
        }
        return '';
    }

    public function get_question_text() {
        $qtext = get_string('questiontext_keyboard_input', 'qtype_musictheory');
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

        $note = get_string('note' . strtolower($this->musictheory_givennoteletter), 'qtype_musictheory');

        $note .= $acc;

        if ($this->musictheory_considerregister) {
            $note .= $this->musictheory_givennoteregister;
        }

        return $qtext . ': <b>' . $note . '</b>';
    }

}

/**
 * A grading strategy that applies the all-or-nothing approach for keyboard
 * input questions, considering the register as appropriate.
 *
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_strategy_keyboard_allornothing implements qtype_musictheory_grading_strategy {

    public function grade($response, $correctresponse, $params = null) {

        $fraction = 1;
        foreach ($response as $key => $answer) {
            if (strpos($key, '_var_') === false) {
                if (is_null($answer) || $answer === '' || !isset($answer)) {
                    $fraction = 0;
                } else {
                    $anssplit = explode('-', $answer);
                    $anspitchclass = $anssplit[0];
                    $ansregister = $anssplit[1];
                    $respsplit = explode('-', $correctresponse[$key]);
                    $resppitchclass = $respsplit[0];
                    $respregister = $respsplit[1];

                    if ($anspitchclass !== $resppitchclass) {
                        $fraction = 0;
                    }
                    if ($params['considerregister'] &&
                            $ansregister !== $respregister) {
                        $fraction = 0;
                    }
                }
            }
        }
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

}
