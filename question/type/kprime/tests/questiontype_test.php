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
 * @package     qtype_kprime
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author      Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author      Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @copyright   2014 eDaktik GmbH {@link http://www.edaktik.at}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/kprime/questiontype.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/kprime/edit_kprime_form.php');

/**
 * @group qtype_kprime
 */
class qtype_kprime_test extends advanced_testcase {

    protected $qtype;
    protected function setUp() {
        $this->qtype = new qtype_kprime();
    }

    protected function tearDown() {
        $this->qtype = null;
    }

    public function test_name() {
        $this->assertEquals($this->qtype->name(), 'kprime');
    }

    protected function get_test_question_data() {
        $qdata = new stdClass();
        $qdata->id = 1;
        $qdata->idnumber = 1;
        $qdata->category = 1;
        $qdata->contextid = 1;
        $qdata->parent = 0;
        $qdata->name = "Kprime001";
        $qdata->questiontext = array("text" => 'Questiontext for Question 1');
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = array("text" => 'This feedback is general');
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0;
        $qdata->stamp = "127.0.0.1+1552376610+76EZEc";
        $qdata->version = "127.0.0.1+155237661076EZEc";
        $qdata->hidden = 0;
        $qdata->timecreated = "1552376610";
        $qdata->timemodified = "1552376610";
        $qdata->createdby = 0;
        $qdata->modifiedby = 0;
        $qdata->options = new stdClass();
        $qdata->options->scoringmethod = "subpoints";
        $qdata->options->shuffleanswers = 0;
        $qdata->options->numberofrows = 4;
        $qdata->options->numberofcolumns = 2;
        $qdata->options->rows = array(
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
        $qdata->options->columns = array(
            1 => (object) array("id" => 1, "questionid" => 1, "number" => 1, "responsetext" => "True", "responsetextformat" => 0),
            2 => (object) array("id" => 2, "questionid" => 1, "number" => 2, "responsetext" => "False", "responsetextformat" => 0)
        );
        $qdata->options->weights = array(
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
        return $qdata;
    }

    public function test_can_analyse_responses() {
        $this->assertTrue($this->qtype->can_analyse_responses());
    }

    public function test_get_random_guess_score_kprime() {
        $question = $this->get_test_question_data();
        $question->options->scoringmethod = "kprime";
        $this->assertEquals(0.1875, $this->qtype->get_random_guess_score($question));
    }

    public function test_get_random_guess_score_kprimeonezero() {
        $question = $this->get_test_question_data();
        $question->options->scoringmethod = "kprimeonezero";
        $this->assertEquals(0.0625, $this->qtype->get_random_guess_score($question));
    }

    public function test_get_random_guess_score_subpoints() {
        $question = $this->get_test_question_data();
        $this->assertEquals(0.5, $this->qtype->get_random_guess_score($question));
    }

    public function test_get_possible_responses_subpoints() {
        $question = $this->get_test_question_data();
        $responses = $this->qtype->get_possible_responses($question);
        $this->assertEquals(array(
            1 => array(
                1 => new question_possible_response('option text 1: True (Correct Response)', 0.25),
                2 => new question_possible_response('option text 1: False', 0.0),
                null => question_possible_response::no_response()
            ),
            2 => array (
                1 => new question_possible_response('option text 2: True (Correct Response)', 0.25),
                2 => new question_possible_response('option text 2: False', 0.0),
                null => question_possible_response::no_response()
            ),
            3 => array(
                1 => new question_possible_response('option text 3: True', 0.0),
                2 => new question_possible_response('option text 3: False (Correct Response)', 0.25),
                null => question_possible_response::no_response()
            ),
            4 => array (
                1 => new question_possible_response('option text 4: True', 0.0),
                2 => new question_possible_response('option text 4: False (Correct Response)', 0.25),
                null => question_possible_response::no_response()
            )
        ), $this->qtype->get_possible_responses($question));
    }

    public function get_question_saving_which() {
        return array(array('question_one'), array('question_two'));
    }

    /**
     * @dataProvider get_question_saving_which
     */
    public function test_question_saving_question_one($which) {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $questiondata = test_question_maker::get_question_data('kprime', $which);
        $formdata = test_question_maker::get_question_form_data('kprime', $which);
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category(array());
        $formdata->category = "{$cat->id},{$cat->contextid}";
        qtype_kprime_edit_form::mock_submit((array)$formdata);
        $form = qtype_kprime_test_helper::get_question_editing_form($cat, $questiondata);
        $this->assertTrue($form->is_validated());
        $fromform = $form->get_data();
        $returnedfromsave = $this->qtype->save_question($questiondata, $fromform);
        $actualquestionsdata = question_load_questions(array($returnedfromsave->id));
        $actualquestiondata = end($actualquestionsdata);

        foreach ($questiondata as $property => $value) {
            if (!in_array($property, array('id', 'version', 'timemodified', 'timecreated', 'options', 'hints', 'stamp'))) {
                $this->assertAttributeEquals($value, $property, $actualquestiondata);
            }
        }
        foreach ($questiondata->options as $optionname => $value) {
            if ($optionname != 'rows' && $optionname != 'columns' && $optionname != 'weights') {
                $this->assertAttributeEquals($value, $optionname, $actualquestiondata->options);
            }
        }
        foreach ($questiondata->hints as $hint) {
            $actualhint = array_shift($actualquestiondata->hints);
            foreach ($hint as $property => $value) {
                if (!in_array($property, array('id', 'questionid', 'options'))) {
                    $this->assertAttributeEquals($value, $property, $actualhint);
                }
            }
        }
        foreach ($questiondata->options->rows as $row) {
            $actualrow = array_shift($actualquestiondata->options->rows);
            foreach ($row as $rowproperty => $rowvalue) {
                if (!in_array($rowproperty, array('id', 'questionid'))) {
                    $this->assertAttributeEquals($rowvalue, $rowproperty, $actualrow);
                }
            }
        }
        foreach ($questiondata->options->columns as $column) {
            $actualcolumn = array_shift($actualquestiondata->options->columns);
            foreach ($column as $columnproperty => $columnvalue) {
                if (!in_array($columnproperty, array('id', 'questionid'))) {
                    $this->assertAttributeEquals($columnvalue, $columnproperty, $actualcolumn);
                }
            }
        }
        foreach ($questiondata->options->weights as $rowkey => $row) {
            foreach ($row as $columnkey => $column) {
                $actualweight = array_shift($actualquestiondata->options->weights[$rowkey]);
                foreach ($column as $propertykey => $property) {
                    if (!in_array($propertykey, array('id', 'questionid'))) {
                        $this->assertAttributeEquals($property, $propertykey, $actualweight);
                    }
                }
            }
        }
    }

    public function test_get_question_options() {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $questiondata = test_question_maker::get_question_data('kprime', 'question_one');
        $formdata = test_question_maker::get_question_form_data('kprime', 'question_two');
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category(array());
        $formdata->category = "{$cat->id},{$cat->contextid}";
        qtype_kprime_edit_form::mock_submit((array)$formdata);
        $form = qtype_kprime_test_helper::get_question_editing_form($cat, $questiondata);
        $this->assertTrue($form->is_validated());
        $fromform = $form->get_data();
        $returnedfromsave = $this->qtype->save_question($questiondata, $fromform);
        $question = $DB->get_record('question', ['id' => $returnedfromsave->id], '*', MUST_EXIST);
        $this->qtype->get_question_options($question);
        $this->assertDebuggingNotCalled();
        $options = $question->options;
        $this->assertEquals($question->id, $options->questionid);
    }
}