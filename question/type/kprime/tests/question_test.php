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
 * Unit tests for qtype_kprime definition class.
 *
 * @package     qtype_kprime
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author      Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author      Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @copyright   2014 eDaktik GmbH {@link http://www.edaktik.at}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_kprime;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * Unit tests for qtype_sc question definition class.
 *
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group       qtype_kprime
 */
class question_test extends \advanced_testcase {

    /**
     * Makes a qtype_sc question.
     * @return qtype_kprime
     */
    public function make_a_kprime_question() {
        \question_bank::load_question_definition_classes('kprime');
        $kprime = new \qtype_kprime_question();
        \test_question_maker::initialise_a_question($kprime);
        $kprime->name = 'Kprime Question';
        $kprime->questiontext = 'the right choices are option 1 and option 2';
        $kprime->generalfeedback = 'You should do this and that';
        $kprime->qtype = \question_bank::get_qtype('kprime');
        $kprime->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $kprime->options = new \stdClass();
        $kprime->shuffleanswers = 0;
        $kprime->answernumbering = 'abc';
        $kprime->scoringmethod = "subpoints";
        $kprime->numberofrows = 4;
        $kprime->rows = array(
            1 => (object) array(
                "id" => 1,
                "questionid" => 1,
                "number" => 1,
                "optiontext" => "option text 1",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 1",
                "optionfeedbackformat" => 1
            ),
            2 => (object) array(
                "id" => 2,
                "questionid" => 1,
                "number" => 2,
                "optiontext" => "option text 2",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 2",
                "optionfeedbackformat" => 1
            ),
            3 => (object) array(
                "id" => 3,
                "questionid" => 1,
                "number" => 3,
                "optiontext" => "option text 3",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 3",
                "optionfeedbackformat" => 1
            ),
            4 => (object) array(
                "id" => 4,
                "questionid" => 1,
                "number" => 4,
                "optiontext" => "option text 4",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 4",
                "optionfeedbackformat" => 1
            )
        );
        $kprime->columns = array(
            1 => (object) array("id" => 1, "questionid" => 1, "number" => 1, "responsetext" => "True", "responsetextformat" => 0),
            2 => (object) array("id" => 2, "questionid" => 1, "number" => 2, "responsetext" => "False", "responsetextformat" => 0)
        );
        $kprime->weights = array(
            1 => array(
                1 => (object) array ("id" => 1, "questionid" => 1, "rownumber" => 1, "columnnumber" => 1, "weight" => 1.000),
                2 => (object) array ("id" => 2, "questionid" => 1, "rownumber" => 1, "columnnumber" => 2, "weight" => 0.000)
            ),
            2 => array(
                1 => (object) array ("id" => 3, "questionid" => 1, "rownumber" => 2, "columnnumber" => 1, "weight" => 1.000),
                2 => (object) array ("id" => 4, "questionid" => 1, "rownumber" => 2, "columnnumber" => 2, "weight" => 0.000)
            ),
            3 => array(
                1 => (object) array ("id" => 5, "questionid" => 1, "rownumber" => 3, "columnnumber" => 1, "weight" => 0.000),
                2 => (object) array ("id" => 6, "questionid" => 1, "rownumber" => 3, "columnnumber" => 2, "weight" => 1.000)
            ),
            4 => array(
                1 => (object) array ("id" => 7, "questionid" => 1, "rownumber" => 4, "columnnumber" => 1, "weight" => 0.000),
                2 => (object) array ("id" => 8, "questionid" => 1, "rownumber" => 4, "columnnumber" => 2, "weight" => 1.000)
            )
        );
        $kprime->hints = array(
            0 => (object) array (
                "statewhichincorrect" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
                "id" => 24,
                "hint" => "Hint 1"
            ),
            1 => (object) array (
                "statewhichincorrect" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
                "id" => 25,
                "hint" => "Hint 2"
            )
        );
        return $kprime;
    }

    /**
     * Test get_expected_data
     *
     * @covers ::get_expected_data
     */
    public function test_get_expected_data() {
        $question = $this->make_a_kprime_question();
        $question->order = array_keys($question->rows);
        $this->assertEquals(array('option0' => PARAM_INT, 'option1' => PARAM_INT, 'option2' => PARAM_INT, 'option3' => PARAM_INT),
            $question->get_expected_data());
    }

    /**
     * Test is_complete_response
     *
     * @covers ::is_complete_response
     */
    public function test_is_complete_response() {
        $question = $this->make_a_kprime_question();
        $this->assertFalse($question->is_complete_response(array()));
        $this->assertTrue($question->is_complete_response(array(
            'option0' => '1',
            'option1' => '1',
            'option2' => '1',
            'option3' => '1')));
        $this->assertTrue($question->is_complete_response(array(
            'option0' => '1',
            'option1' => '1',
            'option2' => '2',
            'option3' => '2')));
    }

    /**
     * Test is_gradable_response
     *
     * @covers ::is_gradable_response
     */
    public function test_is_gradable_response() {
        $question = $this->make_a_kprime_question();
        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertTrue($question->is_gradable_response(array(
            'option0' => '1')));
        $this->assertTrue($question->is_gradable_response(array(
            'option0' => '1',
            'option1' => '1')));
        $this->assertTrue($question->is_gradable_response(array(
            'option0' => '1',
            'option1' => '1',
            'option2' => '1')));
        $this->assertTrue($question->is_gradable_response(array(
            'option0' => '1',
            'option1' => '1',
            'option2' => '2',
            'option3' => '2')));

        $question->scoringmethod = 'kprimeonezero';

        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertFalse($question->is_gradable_response(array(
            'option0' => '1')));
        $this->assertFalse($question->is_gradable_response(array(
            'option0' => '1',
            'option1' => '1')));
        $this->assertFalse($question->is_gradable_response(array(
            'option0' => '1',
            'option1' => '1',
            'option2' => '1')));
        $this->assertTrue($question->is_gradable_response(array(
            'option0' => '1',
            'option1' => '1',
            'option2' => '2',
            'option3' => '2')));
    }

    /**
     * Test get_order
     *
     * @covers ::get_order
     */
    public function test_get_order() {
        $question = $this->make_a_kprime_question();
        $question->shuffleanswers = 1;
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals( $question->order, $question->get_order(\test_question_maker::get_a_qa($question)));
        unset($question);
        $question = $this->make_a_kprime_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals( array(0 => 1, 1 => 2, 2 => 3, 3 => 4),
            $question->get_order(\test_question_maker::get_a_qa($question)));
    }

    /**
     * Test is_correct
     *
     * @covers ::is_correct
     */
    public function test_is_correct() {
        $question = $this->make_a_kprime_question();
        $this->assertEquals($question->is_correct(1, 1), 1);
        $this->assertEquals($question->is_correct(1, 2), 0);
        $this->assertEquals($question->is_correct(2, 1), 1);
        $this->assertEquals($question->is_correct(2, 2), 0);
        $this->assertEquals($question->is_correct(3, 1), 0);
        $this->assertEquals($question->is_correct(3, 2), 1);
        $this->assertEquals($question->is_correct(4, 1), 0);
        $this->assertEquals($question->is_correct(4, 2), 1);
    }

    /**
     * Test is_same_response
     *
     * @covers ::is_same_response
     */
    public function test_is_same_response() {
        $question = $this->make_a_kprime_question();
        $question->start_attempt(new \question_attempt_step(), 1);
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
            array('option0' => '2')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1'),
            array('option1' => '1')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1'),
            array('option1' => '2')));
        $this->assertTrue($question->is_same_response(
            array('option0' => '1', 'option1' => '1'),
            array('option0' => '1', 'option1' => '1')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1', 'option1' => '1'),
            array('option0' => '1', 'option1' => '2')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1', 'option1' => '1'),
            array('option0' => '1', 'option2' => '2')));
    }

    /**
     * Test grading
     *
     * @covers ::get_correct_response
     */
    public function test_grading() {
        $question = $this->make_a_kprime_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals(array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2'),
        $question->get_correct_response());
    }

    /**
     * Test summarise_response
     *
     * @covers ::summarise_response
     */
    public function test_summarise_response() {
        $question = $this->make_a_kprime_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $summary = $question->summarise_response(array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2'),
        \test_question_maker::get_a_qa($question));
        $this->assertEquals('option text 1: True; option text 2: True; option text 3: False; option text 4: False', $summary);
    }

    /**
     * Test classify_response
     *
     * @covers ::classify_response
     */
    public function test_classify_response() {
        $question = $this->make_a_kprime_question();
        $question->start_attempt(new \question_attempt_step(), 1);

        $this->assertEquals(array('1' => new \question_classified_response(1, 'True', 0.25),
            '2' => new \question_classified_response(1, 'True', 0.25),
            '3' => new \question_classified_response(2, 'False', 0.25),
            '4' => new \question_classified_response(2, 'False', 0.25)),
            $question->classify_response(array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')));
        $this->assertEquals(array('1' => \question_classified_response::no_response(),
            '2' => \question_classified_response::no_response(),
            '3' => \question_classified_response::no_response(),
            '4' => \question_classified_response::no_response()),
            $question->classify_response(array()));
    }

    /**
     * Test make_html_inline
     *
     * @covers ::make_html_inline
     */
    public function test_make_html_inline() {
        $question = $this->make_a_kprime_question();
        $this->assertEquals('Frog', $question->make_html_inline('<p>Frog</p>'));
        $this->assertEquals('Frog<br />Toad', $question->make_html_inline("<p>Frog</p>\n<p>Toad</p>"));
        $this->assertEquals('<img src="http://example.com/pic.png" alt="Graph" />',
        $question->make_html_inline('<p><img src="http://example.com/pic.png" alt="Graph" /></p>'));
        $this->assertEquals("Frog<br />XXX <img src='http://example.com/pic.png' alt='Graph' />",
        $question->make_html_inline(" <p> Frog </p> \n\r<p> XXX <img src='http://example.com/pic.png' alt='Graph' /> </p> "));
        $this->assertEquals('Frog', $question->make_html_inline('<p>Frog</p><p></p>'));
        $this->assertEquals('Frog<br />†', $question->make_html_inline('<p>Frog</p><p>†</p>'));
    }

    /**
     * Test get_hint
     *
     * @covers ::get_hint
     */
    public function test_get_hint() {
        $question = $this->make_a_kprime_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals('Hint 1', $question->get_hint(0, \test_question_maker::get_a_qa($question))->hint);
        $this->assertEquals('Hint 2', $question->get_hint(1, \test_question_maker::get_a_qa($question))->hint);
    }

    /**
     * Test compute_final_grade (subpoints)
     *
     * @covers ::compute_final_grade
     */
    public function test_compute_final_grade_subpoints() {
        $question = $this->make_a_kprime_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.5', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '2', 'option1' => '2', 'option2' => '1', 'option3' => '1')),
            1));
        $this->assertEquals('0.6666667', $question->compute_final_grade(array(
            0 => array(),
            1 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.3333334', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
    }

    /**
     * Test compute_final_grade (kprime)
     *
     * @covers ::compute_final_grade
     */
    public function test_compute_final_grade_kprime() {
        $question = $this->make_a_kprime_question();
        $question->scoringmethod = 'kprime';
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.5', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '1')),
            1));
        $this->assertEquals('0.5', $question->compute_final_grade(array(
            0 => array('option0' => '2', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1', 'option2' => '1', 'option3' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '2', 'option2' => '1', 'option3' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '2', 'option1' => '2', 'option2' => '1', 'option3' => '1')),
            1));
        $this->assertEquals('0.5', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1', 'option2' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1', 'option2' => '1')),
            1));
        $this->assertEquals('0.6666667', $question->compute_final_grade(array(
            0 => array(),
            1 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.3333334', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.1666667', $question->compute_final_grade(array(
            0 => array(),
            1 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '1')),
            1));
    }

    /**
     * Test compute_final_grade (kprimeonezero)
     *
     * @covers ::compute_final_grade
     */
    public function test_compute_final_grade_kprimeonezero() {
        $question = $this->make_a_kprime_question();
        $question->scoringmethod = 'kprimeonezero';
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '1', 'option1' => '1', 'option2' => '2')),
            1));
        $this->assertEquals('0.6666667', $question->compute_final_grade(array(
            0 => array(),
            1 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.3333334', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2')),
            1));
    }

    /**
     * Test grade_response (subpoints)
     *
     * @covers ::grade_response
     */
    public function test_grade_response_subpoints() {
        $question = $this->make_a_kprime_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2'))[0]);
        $this->assertEquals(
            "0.75", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '1', 'option3' => '2'))[0]);
        $this->assertEquals(
            "0.75", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '2'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '2', 'option1' => '2', 'option2' => '1', 'option3' => '1'))[0]);
    }

    /**
     * Test grade_response (kprime)
     *
     * @covers ::grade_response
     */
    public function test_grade_response_kprime() {
        $question = $this->make_a_kprime_question();
        $question->scoringmethod = 'kprime';
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2'))[0]);
        $this->assertEquals(
            "0.5", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '1'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '1', 'option3' => '1'))[0]);
        $this->assertEquals(
            "0.5", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '2'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '1'))[0]);
    }

    /**
     * Test grade_response (kprimeonezero)
     *
     * @covers ::grade_response
     */
    public function test_grade_response_kprimeonezero() {
        $question = $this->make_a_kprime_question();
        $question->scoringmethod = 'kprimeonezero';
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '2'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '2', 'option3' => '1'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '1', 'option1' => '1', 'option2' => '2'))[0]);
    }
}
