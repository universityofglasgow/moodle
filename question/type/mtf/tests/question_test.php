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
 * @package     qtype_mtf
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * @group qtype_mtf
 */
class qtype_mtf_question_test extends advanced_testcase {

    public function make_a_mtf_question() {
        question_bank::load_question_definition_classes('mtf');
        $mtf = new qtype_mtf_question();
        test_question_maker::initialise_a_question($mtf);
        $mtf->name = "MTF Question";
        $mtf->idnumber = 1;
        $mtf->questiontext = 'the right choices are option 1 and option 2';
        $mtf->generalfeedback = 'You should do this and that';
        $mtf->qtype = question_bank::get_qtype('mtf');
        $mtf->answernumbering = 'abc';
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
        $mtf->hints = array(
            0 => (object) array(
                "questionid" => 5,
                "id" => 3,
                "hint" => "This is the 1st hint",
                "hintformat" => 1,
                "options" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0
            ),
            1 => (object) array(
                "questionid" => 5,
                "id" => 4,
                "hint" => "This is the 2nd hint",
                "hintformat" => 1,
                "options" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0
            )
        );
        return $mtf;
    }

    public function test_get_expected_data() {
        $question = $this->make_a_mtf_question();
        $question->order = array_keys($question->rows);
        $this->assertEquals(array('option0' => PARAM_INT, 'option1' => PARAM_INT), $question->get_expected_data());
    }

    public function test_is_complete_response() {
        $question = $this->make_a_mtf_question();
        $this->assertFalse($question->is_complete_response(array()));
        $this->assertFalse($question->is_complete_response(array('option0' => '1')));
        $this->assertTrue($question->is_complete_response(array('option0' => '1', 'option1' => '1')));
    }

    public function test_is_gradable_response() {
        $question = $this->make_a_mtf_question();
        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1')));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1', 'option1' => '1')));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1', 'option1' => '2')));
    }

    public function test_get_order() {
        $question = $this->make_a_mtf_question();
        $question->shuffleanswers = 1;
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals( $question->order, $question->get_order(test_question_maker::get_a_qa($question)));
        unset($question);
        $question = $this->make_a_mtf_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals( array(0 => 5, 1 => 6), $question->get_order(test_question_maker::get_a_qa($question)));
    }

    public function test_is_correct() {
        $question = $this->make_a_mtf_question();
        $this->assertEquals($question->is_correct(1, 1), 1);
        $this->assertEquals($question->is_correct(1, 2), 0);
        $this->assertEquals($question->is_correct(2, 1), 0);
        $this->assertEquals($question->is_correct(2, 2), 1);
    }

    public function test_is_same_response() {
        $question = $this->make_a_mtf_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertTrue($question->is_same_response(
            array(),
            array()));
        $this->assertFalse($question->is_same_response(
            array(),
            array('option0' => '1')));
        $this->assertTrue($question->is_same_response(
            array('option0' => '1'),
            array('option0' => '1')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1'),
            array('option1' => '1')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1'),
            array('option0' => '2')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1'),
            array('option1' => '1', 'option2' => '1')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1', 'option2' => '1'),
            array('option1' => '1', 'option2' => '2')));
    }

    public function test_grading() {
        $question = $this->make_a_mtf_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(array('option0' => '1', 'option1' => '2'),
        $question->get_correct_response());
    }

    public function test_summarise_response() {
        $question = $this->make_a_mtf_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $summary = $question->summarise_response(array('option0' => '1', 'option1' => '2'),
        test_question_maker::get_a_qa($question));
        $this->assertEquals('option text 1: True; option text 2: False', $summary);
    }

    public function test_classify_response() {
        $question = $this->make_a_mtf_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(array(
            '5' => new question_classified_response(3, 'True', 0.5),
            '6' => new question_classified_response(4, 'False', 0.5)),
            $question->classify_response(array('option0' => '1', 'option1' => '2')));
        $this->assertEquals(
            array('5' => question_classified_response::no_response(),
            '6' => question_classified_response::no_response()),
            $question->classify_response(array()));
    }

    public function test_make_html_inline() {
        $question = $this->make_a_mtf_question();
        $this->assertEquals('Frog', $question->make_html_inline('<p>Frog</p>'));
        $this->assertEquals('Frog<br />Toad', $question->make_html_inline("<p>Frog</p>\n<p>Toad</p>"));
        $this->assertEquals('<img src="http://example.com/pic.png" alt="Graph" />',
        $question->make_html_inline('<p><img src="http://example.com/pic.png" alt="Graph" /></p>'));
        $this->assertEquals("Frog<br />XXX <img src='http://example.com/pic.png' alt='Graph' />",
        $question->make_html_inline(" <p> Frog </p> \n\r<p> XXX <img src='http://example.com/pic.png' alt='Graph' /> </p> "));
        $this->assertEquals('Frog', $question->make_html_inline('<p>Frog</p><p></p>'));
        $this->assertEquals('Frog<br />†', $question->make_html_inline('<p>Frog</p><p>†</p>'));
    }

    public function test_get_hint() {
        $question = $this->make_a_mtf_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals('This is the 1st hint', $question->get_hint(0, test_question_maker::get_a_qa($question))->hint);
        $this->assertEquals('This is the 2nd hint', $question->get_hint(1, test_question_maker::get_a_qa($question))->hint);
    }

    public function test_compute_final_grade_subpoints() {
        $question = $this->make_a_mtf_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '2')),
            1));
        $this->assertEquals('0.5', $question->compute_final_grade(array(
            0 => array('option0' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '2', 'option1' => '1')),
            1));
        $this->assertEquals('0.6666667', $question->compute_final_grade(array(
            0 => array(),
            1 => array('option0' => '1', 'option1' => '2')),
            1));
        $this->assertEquals('0.3333334', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array('option0' => '1', 'option1' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array('option0' => '1', 'option1' => '2')),
            1));
    }

    public function test_compute_final_grade_mtfonezero() {
        $question = $this->make_a_mtf_question();
        $question->scoringmethod = 'mtfonezero';
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option1' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option1' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array()),
            1));
        $this->assertEquals('0.6666667', $question->compute_final_grade(array(
            0 => array(),
            1 => array('option0' => '1', 'option1' => '2')),
            1));
        $this->assertEquals('0.3333334', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array('option0' => '1', 'option1' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array('option0' => '1', 'option1' => '2')),
            1));
    }

    public function test_grade_response_subpoints() {
        $question = $this->make_a_mtf_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(array('option0' => '1', 'option1' => '2'))[0]);
        $this->assertEquals(
            "0.5", $question->grade_response(array('option0' => '1', 'option1' => '1'))[0]);
        $this->assertEquals(
            "0.5", $question->grade_response(array('option0' => '1'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '2', 'option1' => '1'))[0]);
    }

    public function test_grade_response_mtfonezero() {
        $question = $this->make_a_mtf_question();
        $question->scoringmethod = 'mtfonezero';
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(array('option0' => '1', 'option1' => '2'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '1', 'option1' => '1'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '1'))[0]);
    }
}