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
 * question subtypes related to intervals.
 *
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * The music theory interval writing question subtype.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_interval_write extends qtype_musictheory_question implements qtype_musictheory_subtype {

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

        $ltr = $this->musictheory_givennoteletter;
        $acc = $this->musictheory_givennoteaccidental;
        $reg = $this->musictheory_givennoteregister;
        $givennote = new Note($ltr, $acc, $reg);
        $direction = ($this->musictheory_direction == 'above') ? '+' : '-';
        $interval = new Interval($direction, $this->musictheory_quality, $this->musictheory_size);
        $responsenote = $givennote->getNoteFromInterval($interval);

        return array('answer' => (string) $responsenote);
    }

    public function is_complete_response(array $response) {
        if (!isset($response['answer'])) {
            return false;
        }
        $regex = '/^([A-G](n|\#|b|x|bb)[1-6]){1}$/';
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
        $help = $OUTPUT->help_icon('interval_write_questionastext', 'qtype_musictheory', true);
        return get_string('validationerror_invalidsyntax', 'qtype_musictheory') . $help;
    }

    public function get_question_text() {
        $qtext = get_string('questiontext_interval_write_' . $this->musictheory_direction, 'qtype_musictheory');
        switch ($this->musictheory_quality) {
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
        $interval = get_string($qual . $this->musictheory_size, 'qtype_musictheory');
        return $qtext . ': <b>' . $interval . '</b>';
    }

}

/**
 * The music theory random interval writing question subtype.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_interval_write_random extends qtype_musictheory_interval_write {

    public function start_attempt(question_attempt_step $step, $variant) {

        $this->musictheory_clef = qtype_musictheory_randomiser::get_random_field($this->musictheory_clef_random);
        $this->musictheory_direction = qtype_musictheory_randomiser::get_random_field($this->musictheory_direction_random);
        $randinterval = qtype_musictheory_randomiser::get_random_interval($this->musictheory_quality_random,
                                                                          $this->musictheory_size_random);
        $this->musictheory_size = $randinterval['size'];
        $this->musictheory_quality = $randinterval['quality'];
        $givennote = qtype_musictheory_randomiser::get_random_interval_givennote(
                        $this->musictheory_clef, $this->musictheory_quality, $this->musictheory_size, $this->musictheory_direction);
        $this->musictheory_givennoteletter = $givennote->getLetter();
        $this->musictheory_givennoteaccidental = $givennote->getAccidental();
        $this->musictheory_givennoteregister = $givennote->getRegister();
        $this->musictheory_optionsxml = $this->qtype->get_options_xml($this, 'interval-write');
        $this->questiontext = $this->get_question_text();
        $step->set_qt_var('_var_clef', $this->musictheory_clef);
        $step->set_qt_var('_var_direction', $this->musictheory_direction);
        $step->set_qt_var('_var_quality', $this->musictheory_quality);
        $step->set_qt_var('_var_size', $this->musictheory_size);
        $step->set_qt_var('_var_givennoteletter', $this->musictheory_givennoteletter);
        $step->set_qt_var('_var_givennoteaccidental', $this->musictheory_givennoteaccidental);
        $step->set_qt_var('_var_givennoteregister', $this->musictheory_givennoteregister);
        $step->set_qt_var('_var_optionsxml', $this->musictheory_optionsxml);
        $step->set_qt_var('_var_questiontext', $this->questiontext);
        parent::start_attempt($step, $variant);
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->musictheory_clef = $step->get_qt_var('_var_clef');
        $this->musictheory_direction = $step->get_qt_var('_var_direction');
        $this->musictheory_quality = $step->get_qt_var('_var_quality');
        $this->musictheory_size = $step->get_qt_var('_var_size');
        $this->musictheory_givennoteletter = $step->get_qt_var('_var_givennoteletter');
        $this->musictheory_givennoteaccidental = $step->get_qt_var('_var_givennoteaccidental');
        $this->musictheory_givennoteregister = $step->get_qt_var('_var_givennoteregister');
        $this->musictheory_optionsxml = $step->get_qt_var('_var_optionsxml');
        $this->questiontext = $step->get_qt_var('_var_questiontext');
        parent::apply_attempt_state($step);
    }

}

/**
 * The music theory interval identification question subtype.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_interval_identify extends qtype_musictheory_question implements qtype_musictheory_subtype {

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
        return array(
            'musictheory_answer_quality' => $this->musictheory_quality,
            'musictheory_answer_size'    => $this->musictheory_size,
        );
    }

    public function get_expected_data() {
        return array(
            'musictheory_answer_quality' => PARAM_TEXT,
            'musictheory_answer_size'    => PARAM_TEXT,
        );
    }

    public function is_complete_response(array $response) {
        if (!isset($response['musictheory_answer_quality']) ||
                !isset($response['musictheory_answer_size'])) {
            return false;
        }
        return (!empty($response['musictheory_answer_quality']) &&
                !empty($response['musictheory_answer_size']));
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        $samequality = question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'musictheory_answer_quality');
        $samesize = question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'musictheory_answer_size');
        return ($samequality && $samesize);
    }

    public function get_validation_error(array $response) {
        return get_string('validationerror_interval_identify', 'qtype_musictheory');
    }

    public function summarise_response(array $response) {
        if (!isset($response['musictheory_answer_quality']) ||
                !isset($response['musictheory_answer_size']) ||
                empty($response['musictheory_answer_quality']) ||
                empty($response['musictheory_answer_size'])) {
            return '';
        }
        switch ($response['musictheory_answer_quality']) {
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
        $quality = get_string('quality' . $qual, 'qtype_musictheory');
        $size = get_string('size' . $response['musictheory_answer_size'], 'qtype_musictheory');
        return $quality . ' ' . $size;
    }

    public function get_question_text() {
        $qtext = get_string('questiontext_interval_identify', 'qtype_musictheory');
        return $qtext . ':';
    }

}

/**
 * The music theory interval random identification question subtype.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_interval_identify_random extends qtype_musictheory_interval_identify {

    public function start_attempt(question_attempt_step $step, $variant) {

        $this->musictheory_clef = qtype_musictheory_randomiser::get_random_field($this->musictheory_clef_random);
        $this->musictheory_direction = qtype_musictheory_randomiser::get_random_field(array('above', 'below'));
        $randinterval = qtype_musictheory_randomiser::get_random_interval($this->musictheory_quality_random,
                                                                          $this->musictheory_size_random);
        $this->musictheory_size = $randinterval['size'];
        $this->musictheory_quality = $randinterval['quality'];
        $givennote = qtype_musictheory_randomiser::get_random_interval_givennote(
                        $this->musictheory_clef, $this->musictheory_quality, $this->musictheory_size, $this->musictheory_direction);
        $this->musictheory_givennoteletter = $givennote->getLetter();
        $this->musictheory_givennoteaccidental = $givennote->getAccidental();
        $this->musictheory_givennoteregister = $givennote->getRegister();
        $this->musictheory_optionsxml = $this->qtype->get_options_xml($this, 'interval-identify');
        $this->questiontext = $this->get_question_text();
        $step->set_qt_var('_var_clef', $this->musictheory_clef);
        $step->set_qt_var('_var_direction', $this->musictheory_direction);
        $step->set_qt_var('_var_quality', $this->musictheory_quality);
        $step->set_qt_var('_var_size', $this->musictheory_size);
        $step->set_qt_var('_var_givennoteletter', $this->musictheory_givennoteletter);
        $step->set_qt_var('_var_givennoteaccidental', $this->musictheory_givennoteaccidental);
        $step->set_qt_var('_var_givennoteregister', $this->musictheory_givennoteregister);
        $step->set_qt_var('_var_optionsxml', $this->musictheory_optionsxml);
        $step->set_qt_var('_var_questiontext', $this->questiontext);
        parent::start_attempt($step, $variant);
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->musictheory_clef = $step->get_qt_var('_var_clef');
        $this->musictheory_direction = $step->get_qt_var('_var_direction');
        $this->musictheory_quality = $step->get_qt_var('_var_quality');
        $this->musictheory_size = $step->get_qt_var('_var_size');
        $this->musictheory_givennoteletter = $step->get_qt_var('_var_givennoteletter');
        $this->musictheory_givennoteaccidental = $step->get_qt_var('_var_givennoteaccidental');
        $this->musictheory_givennoteregister = $step->get_qt_var('_var_givennoteregister');
        $this->musictheory_optionsxml = $step->get_qt_var('_var_optionsxml');
        $this->questiontext = $step->get_qt_var('_var_questiontext');
        parent::apply_attempt_state($step);
    }

}
