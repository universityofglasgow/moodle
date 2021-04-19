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

class qtype_kprime_test_helper extends question_test_helper {

    public function get_test_questions() {
        return array('question_one', 'question_two', 'question_three', 'question_four');
    }

    public static function get_kprime_question_data_question_one() {
        global $USER;
        $qdata = new stdClass();
        $qdata->idnumber = 1;
        $qdata->category = 1;
        $qdata->contextid = 1;
        $qdata->parent = 0;
        $qdata->name = "KPrim-Question-1";
        $qdata->qtype = 'kprime';
        $qdata->questiontext = array("text" => 'Questiontext for Question 1');
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = array("text" => 'This feedback is general');
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
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
        $qdata->hints = (object) array (
            24 => (object) array (
                "id" => 24,
                "questionid" => 1,
                "hint" => "This is the 1st hint",
                "hintformat" => 1,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
                "options" => 0
            ),
            25 => (object) array (
                "id" => 25,
                "questionid" => 1,
                "hint" => "This is the 2nd hint",
                "hintformat" => 1,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
                "options" => 0
            )
        );
        return $qdata;
    }

    public static function get_kprime_question_form_data_question_one() {
        // KPrime Question.
        // Question 1: true.
        // Question 2: true.
        // Question 3: false.
        // Question 4: false.
        global $USER;
        $qdata = new stdClass();
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'kprime';
        $qdata->name = 'KPrim-Question-1';
        $qdata->questiontext = array(
            "text" => 'Questiontext for Question 1',
            'format' => FORMAT_HTML
        );
        $qdata->generalfeedback = array(
            "text" => 'This feedback is general',
            'format' => FORMAT_HTML
        );
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->hidden = 0;
        $qdata->scoringmethod = 'subpoints';
        $qdata->shuffleanswers = 0;
        $qdata->numberofrows = 4;
        $qdata->numberofcolumns = 2;
        $qdata->answernumbering = 123;
        $qdata->option_1 = array(
            "text" => "option text 1",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_2 = array(
            "text" => "option text 2",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_3 = array(
            "text" => "option text 3",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_4 = array(
            "text" => "option text 4",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_1 = array(
            "text" => "feedback to option 1",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_2 = array(
            "text" => "feedback to option 2",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_3 = array(
            "text" => "feedback to option 3",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_4 = array(
            "text" => "feedback to option 4",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->weightbutton_1 = "1";
        $qdata->weightbutton_2 = "1";
        $qdata->weightbutton_3 = "2";
        $qdata->weightbutton_4 = "2";
        $qdata->responsetext_1 = "True";
        $qdata->responsetext_2 = "False";
        $qdata->hint = array(
            0 => array(
                'text' => 'This is the 1st hint',
                'format' => FORMAT_HTML,
                'hintshownumcorrect' => 1,
                'hintclearwrong' => 0,
                'options' => 0,
            ),
            1  => array(
                'text' => 'This is the 2nd hint',
                'format' => FORMAT_HTML,
                'hintshownumcorrect' => 1,
                'hintclearwrong' => 1,
                'options' => 1,
            )
        );
        return $qdata;
    }

    public static function get_kprime_question_data_question_two() {
        global $USER;
        $qdata = new stdClass();
        $qdata->idnumber = 2;
        $qdata->category = 1;
        $qdata->contextid = 1;
        $qdata->parent = 0;
        $qdata->name = "KPrime-Question-2";
        $qdata->qtype = 'kprime';
        $qdata->questiontext = array("text" => 'Questiontext for Question 1');
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = array("text" => 'This feedback is general');
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
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
                "questionid" => 2,
                "number" => 1,
                "optiontext" => "option text 1",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 1",
                "optionfeedbackformat" => 1
            ),
            2 => (object) array(
                "id" => 2,
                "questionid" => 2,
                "number" => 2,
                "optiontext" => "option text 2",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 2",
                "optionfeedbackformat" => 1
            ),
            3 => (object) array(
                "id" => 3,
                "questionid" => 2,
                "number" => 3,
                "optiontext" => "option text 3",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 3",
                "optionfeedbackformat" => 1
            ),
            4 => (object) array(
                "id" => 4,
                "questionid" => 2,
                "number" => 4,
                "optiontext" => "option text 4",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 4",
                "optionfeedbackformat" => 1
            )
        );
        $qdata->options->columns = array(
            1 => (object) array("id" => 1, "questionid" => 2, "number" => 1, "responsetext" => "True", "responsetextformat" => 0),
            2 => (object) array("id" => 2, "questionid" => 2, "number" => 2, "responsetext" => "False", "responsetextformat" => 0)
        );
        $qdata->options->weights = array(
            1 => array(
                1 => (object) array ("id" => 1, "questionid" => 2, "rownumber" => 1, "columnnumber" => 1, "weight" => 1.000),
                2 => (object) array ("id" => 2, "questionid" => 2, "rownumber" => 1, "columnnumber" => 2, "weight" => 0.000)
            ),
            2 => array(
                1 => (object) array ("id" => 3, "questionid" => 2, "rownumber" => 2, "columnnumber" => 1, "weight" => 1.000),
                2 => (object) array ("id" => 4, "questionid" => 2, "rownumber" => 2, "columnnumber" => 2, "weight" => 0.000)
            ),
            3 => array(
                1 => (object) array ("id" => 5, "questionid" => 2, "rownumber" => 3, "columnnumber" => 1, "weight" => 0.000),
                2 => (object) array ("id" => 6, "questionid" => 2, "rownumber" => 3, "columnnumber" => 2, "weight" => 1.000)
            ),
            4 => array(
                1 => (object) array ("id" => 7, "questionid" => 2, "rownumber" => 4, "columnnumber" => 1, "weight" => 0.000),
                2 => (object) array ("id" => 8, "questionid" => 2, "rownumber" => 4, "columnnumber" => 2, "weight" => 1.000)
            )
        );
        $qdata->hints = (object) array (
            26 => (object) array (
                "id" => 26,
                "questionid" => 2,
                "hint" => "This is the 1st hint",
                "hintformat" => 1,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
                "options" => 0
            ),
            27 => (object) array (
                "id" => 27,
                "questionid" => 2,
                "hint" => "This is the 2nd hint",
                "hintformat" => 1,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
                "options" => 0
            )
        );
        return $qdata;
    }

    public static function get_kprime_question_form_data_question_two() {
        // KPrime Question.
        // Shuffle: No.
        // Question 1: true.
        // Question 2: true.
        // Question 3: false.
        // Question 4: false.
        global $USER;
        $qdata = new stdClass();
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'kprime';
        $qdata->name = 'KPrime-Question-2';
        $qdata->questiontext = array(
            "text" => 'Questiontext for Question 1',
            'format' => FORMAT_HTML
        );
        $qdata->generalfeedback = array(
            "text" => 'This feedback is general',
            'format' => FORMAT_HTML
        );
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->hidden = 0;
        $qdata->scoringmethod = 'subpoints';
        $qdata->shuffleanswers = 0;
        $qdata->numberofrows = 4;
        $qdata->numberofcolumns = 2;
        $qdata->answernumbering = 123;
        $qdata->option_1 = array(
            "text" => "option text 1",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_2 = array(
            "text" => "option text 2",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_3 = array(
            "text" => "option text 3",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_4 = array(
            "text" => "option text 4",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_1 = array(
            "text" => "feedback to option 1",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_2 = array(
            "text" => "feedback to option 2",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_3 = array(
            "text" => "feedback to option 3",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_4 = array(
            "text" => "feedback to option 4",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->weightbutton_1 = "1";
        $qdata->weightbutton_2 = "1";
        $qdata->weightbutton_3 = "2";
        $qdata->weightbutton_4 = "2";
        $qdata->responsetext_1 = "True";
        $qdata->responsetext_2 = "False";
        $qdata->hint = array(
            0 => array(
                'text' => 'This is the 1st hint',
                'format' => FORMAT_HTML,
                'hintshownumcorrect' => 1,
                'hintclearwrong' => 0,
                'options' => 0,
            ),
            1  => array(
                'text' => 'This is the 2nd hint',
                'format' => FORMAT_HTML,
                'hintshownumcorrect' => 1,
                'hintclearwrong' => 1,
                'options' => 1,
            )
        );
        return $qdata;
    }

    public static function get_kprime_question_form_data_question_three() {
        // KPrime Question.
        // Shuffle: Yes.
        // Question 1: true.
        // Question 2: true.
        // Question 3: false.
        // Question 4: false.
        global $USER;
        $qdata = new stdClass();
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'kprime';
        $qdata->name = 'KPrime-Question-3';
        $qdata->questiontext = array(
            "text" => 'Questiontext for Question 1',
            'format' => FORMAT_HTML
        );
        $qdata->generalfeedback = array(
            "text" => 'This feedback is general',
            'format' => FORMAT_HTML
        );
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->hidden = 0;
        $qdata->scoringmethod = 'subpoints';
        $qdata->shuffleanswers = 1;
        $qdata->numberofrows = 4;
        $qdata->numberofcolumns = 2;
        $qdata->answernumbering = 123;
        $qdata->option_1 = array(
            "text" => "option text 1",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_2 = array(
            "text" => "option text 2",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_3 = array(
            "text" => "option text 3",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_4 = array(
            "text" => "option text 4",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_1 = array(
            "text" => "feedback to option 1",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_2 = array(
            "text" => "feedback to option 2",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_3 = array(
            "text" => "feedback to option 3",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_4 = array(
            "text" => "feedback to option 4",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->weightbutton_1 = "1";
        $qdata->weightbutton_2 = "1";
        $qdata->weightbutton_3 = "2";
        $qdata->weightbutton_4 = "2";
        $qdata->responsetext_1 = "True";
        $qdata->responsetext_2 = "False";
        $qdata->hint = array(
            0 => array(
                'text' => 'This is the 1st hint',
                'format' => FORMAT_HTML,
                'hintshownumcorrect' => 1,
                'hintclearwrong' => 0,
                'options' => 0,
            ),
            1  => array(
                'text' => 'This is the 2nd hint',
                'format' => FORMAT_HTML,
                'hintshownumcorrect' => 1,
                'hintclearwrong' => 1,
                'options' => 1,
            ),
        );
        return $qdata;
    }

    public static function get_kprime_question_form_data_question_four() {
        global $USER;
        $qdata = new stdClass();
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'kprime';
        $qdata->name = 'KPrime-Question-4';
        $qdata->questiontext = array(
            "text" => 'Questiontext for Question 1',
            'format' => FORMAT_HTML
        );
        $qdata->generalfeedback = array(
            "text" => 'This feedback is general',
            'format' => FORMAT_HTML
        );
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->hidden = 0;
        $qdata->scoringmethod = 'subpoints';
        $qdata->shuffleanswers = 0;
        $qdata->numberofrows = 4;
        $qdata->numberofcolumns = 2;
        $qdata->answernumbering = 123;
        $qdata->option_1 = array(
            "text" => "option text 1",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_2 = array(
            "text" => "option text 2",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_3 = array(
            "text" => "option text 3",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->option_4 = array(
            "text" => "option text 4",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_1 = array(
            "text" => "feedback to option 1",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_2 = array(
            "text" => "feedback to option 2",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_3 = array(
            "text" => "feedback to option 3",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->feedback_4 = array(
            "text" => "feedback to option 4",
            "format" => 1,
            "itemid" => 1
        );
        $qdata->weightbutton_1 = "1";
        $qdata->weightbutton_2 = "1";
        $qdata->weightbutton_3 = "2";
        $qdata->weightbutton_4 = "2";
        $qdata->responsetext_1 = "True";
        $qdata->responsetext_2 = "False";
        $qdata->hint = array(
            0 => array(
                'text' => 'This is the 1st hint',
                'format' => FORMAT_HTML,
                'hintshownumcorrect' => 1,
                'hintclearwrong' => 0,
                'options' => 0,
            ),
            1  => array(
                'text' => 'This is the 2nd hint',
                'format' => FORMAT_HTML,
                'hintshownumcorrect' => 1,
                'hintclearwrong' => 1,
                'options' => 1,
        ));
        return $qdata;
    }
}