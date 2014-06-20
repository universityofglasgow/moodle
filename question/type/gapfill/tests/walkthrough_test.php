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
 * This file contains tests that walks a question through the interactive
 * behaviour.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/type/gapfill/tests/helper.php');

/**
 * Unit tests for the gapfill question type.
 * Not complete, needs more examples.
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_gapfill_walkthrough_test extends qbehaviour_walkthrough_test_base {

    public function test_deferred_feedback_unanswered() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        /* Check the initial state. */
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);

        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(), $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(), $this->get_no_hint_visible_expectation());

        // Save an  correct response.
        $this->process_submission(array('p1' => '', 'p2' => ''));
        $this->check_step_count(2);
        $this->check_current_state(question_state::$todo);

        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gaveup);
        $this->check_current_mark(null);
    }

    public function test_deferred_with_correct() {
        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);

        // Save an  correct response.
        $this->process_submission(array('p1' => 'cat', 'p2' => 'mat'));
        $this->check_step_count(2);
        $this->check_current_state(question_state::$complete);

        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(2);
        $this->quba->finish_all_questions();
    }

    public function test_deferred_with_incorrect() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);

        // Save an  correct response.
        $this->process_submission(array('p1' => 'dog', 'p2' => 'cat'));
        $this->check_step_count(2);
        $this->check_current_state(question_state::$complete);

        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gradedwrong);
        $this->check_current_mark(0);
    }

    public function test_deferred_with_partially_correct() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'deferredfeedback', $maxmark);
        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);

        // Save an  correct response.
        $this->process_submission(array('p1' => 'cat', 'p2' => 'dog'));
        $this->check_step_count(2);
        $this->check_current_state(question_state::$complete);

        $this->quba->finish_all_questions();
        $this->check_step_count(3);
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(1);
    }

    public function test_interactive_with_correct() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;
        $this->start_attempt_at_question($gapfill, 'interactive', $maxmark);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);

        $this->check_step_count(1);

        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(), $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(), $this->get_no_hint_visible_expectation());

        // Save a  correct response.
        $this->process_submission(array('p0' => 'cat', 'p1' => 'mat'));
        $this->check_step_count(2);

        $this->check_current_state(question_state::$todo);

        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(), $this->get_no_hint_visible_expectation());

        // Submit saved response.
        $this->process_submission(array('-submit' => 1, 'p1' => 'cat', 'p2' => 'mat'));
        $this->check_step_count(3);
        // Verify.
        $this->check_current_state(question_state::$gradedright);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(), $this->get_no_hint_visible_expectation());

        $this->check_current_mark(2);
        // Finish the attempt.
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedright);
    }

    public function test_interactive_wildcard_with_correct() {
        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill', array('cat|dog', 'mat'));
        $maxmark = 2;

        $this->start_attempt_at_question($gapfill, 'interactive', $maxmark);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(), $this->get_no_hint_visible_expectation());

        // Check the initial state.
        $this->check_current_state(question_state::$todo);

        $this->check_step_count(1);

        // Save a  correct response.
        $this->process_submission(array('p0' => 'cat', 'p1' => 'mat'));
        $this->check_step_count(2);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(), $this->get_no_hint_visible_expectation());

        $this->check_current_state(question_state::$todo);
        // Submit saved response.
        $this->process_submission(array('-submit' => 1, 'p1' => 'cat', 'p2' => 'mat'));
        $this->check_step_count(3);

        // Verify.
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedright);

        $this->check_current_mark(2);
        // Finish the attempt.
    }

    public function test_disableregex() {
        $questiontext = 'for([$i]=0;$<10;$i++)';
        $submission = array('+');
        $options = array();
        $options['noduplicates'] = 0;
        $options['disableregex'] = 1;
        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext, false, $options);
        $maxmark = 1;
        $this->start_attempt_at_question($gapfill, 'interactive', $maxmark);
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_step_count(1);

        $this->process_submission(array('-submit' => 1, 'p1' => '$i'));

        $this->check_step_count(2);
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(1);
        $this->quba->finish_all_questions();
    }

    public function test_interactive_discard_duplicates() {
        /* this is for the scenario where you have multiple fields
         * and each field could take any value. The marking is designed
         * to asssure that the student cannot get credited more than once
         * for each value, i.e. so if the answer is gold,silver, bronze
         * they cannot get 3 marks by entereing gold, gold and gold
         */

        /* Create a gapfill question and set noduplicates to true */
        $questiontext = '
What are the colors of the Olympic medals?

[gold|silver|bronze]
[gold|silver|bronze]
[gold|silver|bronze] arse';
        $submission = array('p0' => 'gold', 'p1' => 'gold', 'p2' => 'silver');
        $options = array();
        $options['noduplicates'] = 1;
        $options['disableregex'] = 0;
        $gapfill = qtype_gapfill_test_helper::make_question2('gapfill', $questiontext, false, $options);

        $maxmark = 3;
        $this->start_attempt_at_question($gapfill, 'interactive', $maxmark);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_step_count(1);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(), $this->get_no_hint_visible_expectation());

        // Save a  correct response.
        $this->process_submission($submission);
        $this->check_current_state(question_state::$todo);

        $this->check_current_mark(null);

        $this->check_step_count(2);

        $submission = array('-submit' => 1, 'p1' => 'gold', 'p2' => 'gold', 'p3' => 'silver');

        $this->process_submission($submission);
        $this->check_step_count(3);

        $this->check_current_output(
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_does_not_contain_validation_error_expectation(),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_no_hint_visible_expectation());

        $gapfill->grade_response($submission);
        $this->check_current_mark(2);

        $this->check_current_state(question_state::$gradedpartial);
    }

    public function test_immediatefeedback_with_correct() {

        // Create a gapfill question.
        $gapfill = qtype_gapfill_test_helper::make_question('gapfill');
        $maxmark = 2;

        $gapfill->showanswers = true;
        $this->start_attempt_at_question($gapfill, 'immediatefeedback', $maxmark);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);

        $this->check_step_count(1);

        // Save a  correct response.
        $this->process_submission(array('p0' => 'cat', 'p1' => 'cat'));
        $this->check_step_count(2);

        $this->check_current_state(question_state::$todo);
        // Submit saved response.
        $this->process_submission(array('-submit' => 1, 'p1' => 'cat', 'p2' => 'mat'));
        $this->check_step_count(3);
        // Verify.
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedright);

        $this->check_current_mark(2);
        // Finish the attempt.
    }

}