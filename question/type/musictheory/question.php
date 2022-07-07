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
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib/MusicTheory/MusicTheory.php');

require_once(__DIR__ . '/randomisation.php');

require_once(__DIR__ . '/note/renderer.php');
require_once(__DIR__ . '/keyboard/renderer.php');
require_once(__DIR__ . '/keysignature/renderer.php');
require_once(__DIR__ . '/interval/renderer.php');
require_once(__DIR__ . '/scale/renderer.php');
require_once(__DIR__ . '/harmonicfunction/renderer.php');
require_once(__DIR__ . '/chordquality/renderer.php');

/**
 * Interface that a music theory question subtype must implement.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface qtype_musictheory_subtype {

    /**
     * Returns a list of {@link qtype_musictheory_question_grading_strategy}
     * that a music theory question subtype would support.
     *
     * These will be displayed in the edit form when the subtype is selected.
     *
     * @return array An array of strings provising the class name for each
     * strategy.
     */
    public function get_supported_grading_strategies();

    /**
     * Used by music theory subtypes to display automatically generated
     * question text based on the question options.
     *
     * @return string
     */
    public function get_question_text();
}

/**
 * Represents a musictheory question.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_musictheory_question extends question_graded_automatically {

    /** @var qtype_musictheory_question_grading_strategy The strategy used for
     * grading this question */
    public $gradingstrategy;

    public function __construct($gradingstrategy) {
        $this->gradingstrategy = $gradingstrategy;
        parent::__construct();
    }

    public function get_renderer(moodle_page $page) {
        switch ($this->musictheory_musicqtype) {
            case "note-write":
            case "note-write-random":
                return new qtype_musictheory_note_write_renderer($page, null);
            case 'note-identify':
            case 'note-identify-random':
                return new qtype_musictheory_note_identify_renderer($page, null);
            case 'keyboard-input':
                return new qtype_musictheory_keyboard_input_renderer($page, null);
            case "keysignature-write":
            case "keysignature-write-random":
                return new qtype_musictheory_keysignature_write_renderer($page, null);
            case "keysignature-identify":
            case "keysignature-identify-random":
                return new qtype_musictheory_keysignature_identify_renderer($page, null);
            case 'interval-write':
            case 'interval-write-random':
                return new qtype_musictheory_interval_write_renderer($page, null);
            case 'interval-identify':
            case 'interval-identify-random':
                return new qtype_musictheory_interval_identify_renderer($page, null);
            case 'scale-write':
            case 'scale-write-random':
                return new qtype_musictheory_scale_write_renderer($page, null);
            case 'scale-identify':
            case 'scale-identify-random':
                return new qtype_musictheory_scale_identify_renderer($page, null);
            case 'chordquality-write':
            case 'chordquality-write-random':
                return new qtype_musictheory_chordquality_write_renderer($page, null);
            case 'chordquality-identify':
            case 'chordquality-identify-random':
                return new qtype_musictheory_chordquality_identify_renderer($page, null);
            case 'harmonicfunction-write':
            case 'harmonicfunction-write-random':
                return new qtype_musictheory_harmonicfunction_write_renderer($page, null);
            case 'harmonicfunction-identify':
            case 'harmonicfunction-identify-random':
                return new qtype_musictheory_harmonicfunction_identify_renderer($page, null);
                return parent::get_renderer($page);
        }
    }

}

require_once(__DIR__ . '/note/question.php');
require_once(__DIR__ . '/keyboard/question.php');
require_once(__DIR__ . '/keysignature/question.php');
require_once(__DIR__ . '/interval/question.php');
require_once(__DIR__ . '/scale/question.php');
require_once(__DIR__ . '/chordquality/question.php');
require_once(__DIR__ . '/harmonicfunction/question.php');

/**
 * This interface defines the methods that a class must implement if it is to
 * be used a strategy to grade a music theory question subtype and provide
 * grade scheme information.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface qtype_musictheory_grading_strategy {

    /**
     * Returns an array that provides the grade fraction and associated
     * question state for a given response.
     *
     * @param array $response The response.
     * @param array $correctresponse The correct response.
     * @return array The fraction and question state.
     */
    public function grade($response, $correctresponse);
}

/**
 * A grading strategy that simply checks whether a given response is identical to
 * the correct response. If so, full grade is given to the response - otherwise, a grade
 * of zero is assigned.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory_strategy_all_or_nothing implements qtype_musictheory_grading_strategy {

    public function grade($response, $correctresponse) {

        $fraction = 1;
        foreach ($response as $key => $answer) {
            if (strpos($key, '_var_') === false) {
                if ($answer !== $correctresponse[$key]) {
                    $fraction = 0;
                    break;
                }
            }
        }
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

}
