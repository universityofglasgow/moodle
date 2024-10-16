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
 * Unit tests for qtype_mtf question definition class.
 *
 * @package     qtype_mtf
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/mtf/tests/helper.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * Unit tests for qtype_mtf question definition class.
 *
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group       qtype_mtf
 */
class qtype_mtf_walkthrough_test extends qbehaviour_walkthrough_test_base {

    /**
     * (non-PHPdoc)
     * @param int $index
     * @param int $value
     * @param bool $enabled
     * @param bool $checked
     * @return array
     */
    public function get_contains_mtf_radio_expectation($index, $value, $enabled = null, $checked = null) {
        return $this->get_contains_radio_expectation(array(
            'name' => $this->quba->get_field_prefix($this->slot) .  "option" .  $index,
            'value' => $value
        ), $enabled, $checked);
    }

    /**
     * Makes a qtype_mtf question.
     * @return qtype_mtf
     */
    public function make_a_mtf_question() {
        question_bank::load_question_definition_classes('mtf');
        $mtf = new qtype_mtf_question();
        test_question_maker::initialise_a_question($mtf);
        $mtf->qtype = question_bank::get_qtype('mtf');
        $mtf->name = "MTF Question";
        $mtf->idnumber = 1;
        $mtf->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $mtf->questiontext = 'the right choices are option 1 and option 2';
        $mtf->generalfeedback = 'You should do this and that';
        $mtf->answernumbering = 'abc';
        $mtf->deduction = 0.0;
        $mtf->scoringmethod = "subpoints";
        $mtf->options = new stdClass();
        $mtf->shuffleanswers = 0;
        $mtf->numberofrows = 2;
        $mtf->numberofcolumns = 2;
        $mtf->rows = array(
            5 => (object) array(
                "id" => 5,
                "questionid" => 5,
                "number" => 1,
                "optiontext" => "option text 1",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 1",
                "optionfeedbackformat" => 1
            ),
            6 => (object) array(
                "id" => 6,
                "questionid" => 5,
                "number" => 2,
                "optiontext" => "option text 2",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 2",
                "optionfeedbackformat" => 1
            )
        );
        $mtf->columns = array(
            3 => (object) array(
                "id" => 3,
                "questionid" => 5,
                "number" => 1,
                "responsetext" => "True",
                "responsetextformat" => 0
            ),
            4 => (object) array(
                "id" => 4,
                "questionid" => 5,
                "number" => 2,
                "responsetext" => "False",
                "responsetextformat" => 0
            )
        );
        $mtf->weights = array(
            1 => array(
                1 => (object) array (
                    "id" => 15,
                    "questionid" => 5,
                    "rownumber" => 1,
                    "columnnumber" => 1,
                    "weight" => 1.000
                ),
                2 => (object) array (
                    "id" => 16,
                    "questionid" => 5,
                    "rownumber" => 1,
                    "columnnumber" => 2,
                    "weight" => 0.000
                )
            ),
            2 => array(
                1 => (object) array (
                    "id" => 17,
                    "questionid" => 5,
                    "rownumber" => 2,
                    "columnnumber" => 1,
                    "weight" => 0.000
                ),
                2 => (object) array (
                    "id" => 17,
                    "questionid" => 5,
                    "rownumber" => 2,
                    "columnnumber" => 2,
                    "weight" => 1.000
                )
            )
        );
        return $mtf;
    }

    /**
     * Test deferredfeedback_feedback_mtf
     */
    public function test_deferredfeedback_feedback_mtf() {
        $mtf = $this->make_a_mtf_question();
        $this->start_attempt_at_question($mtf, 'deferredfeedback', 1);
        $this->process_submission(array("option0" => 1, "option1" => 2));
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_mtf_radio_expectation(0, 1, true, true),
            $this->get_contains_mtf_radio_expectation(0, 2, true, false),
            $this->get_contains_mtf_radio_expectation(1, 1, true, false),
            $this->get_contains_mtf_radio_expectation(1, 2, true, true),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation());
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(1);
        $this->check_current_output(
            $this->get_contains_mtf_radio_expectation(0, 1, false, true),
            $this->get_contains_mtf_radio_expectation(1, 2, false, true),
            $this->get_contains_correct_expectation(),
            new question_pattern_expectation('/name=\".*1_option0\".*value=\"1\".*checked=\"checked\"/'),
            new question_pattern_expectation('/name=\".*1_option1\".*value=\"2\".*checked=\"checked\"/')
        );
    }

    /**
     * Test deduction for wrong answers
     */
    public function test_deduction_mtf() {
        $mtf = $this->make_a_mtf_question();
        $mtf->deduction = 0.5;
        $mtf->scoringmethod = 'subpointdeduction';
        $this->start_attempt_at_question($mtf, 'immediatefeedback', 1);
        $this->process_submission(array('option0' => 1, 'option1' => '1'));
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedpartial);
        // One right, one wrong should give +0.5 -0.25 = 0.25.
        $this->check_current_mark(0.25);

        $this->start_attempt_at_question($mtf, 'immediatefeedback', 1);
        $this->process_submission(array('option0' => 1, 'option1' => ''));
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedpartial);
        // One right, one empty should give +0.5 -0 = 0.5.
        $this->check_current_mark(0.5);
    }

    /**
     * Test regrading of questions with deduction
     */
    public function test_deduction_mtf_regrading() {
        $mtf = $this->make_a_mtf_question();
        $mtf->deduction = 0.5;
        $mtf->scoringmethod = 'subpointdeduction';
        $this->start_attempt_at_question($mtf, 'immediatefeedback', 1);
        // Correct answer would be 1 and 2, so we have one correct and one wrong.
        $this->process_submission(array('option0' => 1, 'option1' => 1));
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(0.25);

        // Changing to "subpoints" grading method, so there should be no deduction anymore.
        $question = $this->quba->get_question($this->slot, false);
        $question->scoringmethod = 'subpoints';
        $this->quba->regrade_all_questions();
        $this->check_current_mark(0.5);

        // Changing back to "subpoints with deduction" and changing answers to 2 and 1,
        // so still one right and one wrong. With the deduction reduced to 0.1, we should
        // get 0.5 - 0.1*0.5 = 0.45 points.
        $question = $this->quba->get_question($this->slot, false);
        $question->scoringmethod = 'subpointdeduction';
        $question->rows[5]->number = 2;
        $question->rows[6]->number = 1;
        $question->deduction = 0.1;
        $this->quba->regrade_all_questions();
        $this->check_current_mark(0.45);

        // Finally, change answers to 1 and 1, so now both answers are correct,
        // we should get full marks.
        $question = $this->quba->get_question($this->slot, false);
        $question->scoringmethod = 'subpointdeduction';
        $question->rows[5]->number = 1;
        $question->rows[6]->number = 1;
        $this->quba->regrade_all_questions();
        $this->check_current_mark(1);
    }
}
