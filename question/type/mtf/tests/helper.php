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

class qtype_mtf_test_helper extends question_test_helper {

    public function get_test_questions() {
        return array('question_one', 'question_two', 'question_three', 'question_four');
    }

    public static function get_mtf_question_data_question_one() {
        // Option text 1 : true.
        // Option text 2 : false.
        global $USER;
        $qdata = new stdClass();
        $qdata->qtype = 'mtf';
        $qdata->name = 'MTF-Question-001';
        $qdata->idnumber = 5;
        $qdata->category = 1;
        $qdata->contextid = 1;
        $qdata->parent = 0;
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->length = 1;
        $qdata->hidden = 0;
        $qdata->timecreated = "1552376610";
        $qdata->timemodified = "1552376610";
        $qdata->stamp = "127.0.0.1+1552376610+76EZEc";
        $qdata->version = "127.0.0.1+155237661076EZEc";
        $qdata->defaultmark = 1;
        $qdata->penalty = 0.3333333;
        $qdata->questiontext = "Questiontext for Question 1";
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = "This feedback is general";
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->options = new stdClass();
        $qdata->options->scoringmethod = "subpoints";
        $qdata->options->answernumbering = 123;
        $qdata->options->shuffleanswers = 0;
        $qdata->options->numberofrows = 2;
        $qdata->options->numberofcolumns = 2;
        $qdata->options->rows = array(
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
        $qdata->options->columns = array(
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
        $qdata->options->weights = array(
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
        $qdata->hints = array(
            0 => (object) array(
                "questionid" => 5,
                "id" => 3,
                "hint" => "This is the 1st hint",
                "hintformat" => 1,
                "options" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
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
        return $qdata;
    }

    public static function get_mtf_question_form_data_question_one() {
        // Option text 1 : true.
        // Option text 2 : false.
        global $USER;
        $qdata = new stdClass();
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'mtf';
        $qdata->name = 'MTF-Question-001';
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
        $qdata->numberofrows = 2;
        $qdata->numberofcolumns = 2;
        $qdata->answernumbering = 123;
        $qdata->option = array(
            0 => array(
                "text" => "option text 1",
                "format" => 1,
                "itemid" => 1
            ),
            1 => array(
                "text" => "option text 2",
                "format" => 1,
                "itemid" => 2
            )
        );
        $qdata->feedback = array(
            0 => array(
                "text" => "feedback to option 1",
                "format" => 1,
                "itemid" => 1
            ),
            1 => array(
                "text" => "feedback to option 2",
                "format" => 1,
                "itemid" => 2
            )
        );
        $qdata->weightbutton = array(
            0 => 1,
            1 => 2
        );
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
                'options' => 1
            )
        );
        return $qdata;
    }

    public static function get_mtf_question_data_question_two() {
        // Option text 1 : true.
        // Option text 2 : false.
        global $USER;
        $qdata = new stdClass();
        $qdata->qtype = 'mtf';
        $qdata->name = 'MTF-Question-001';
        $qdata->idnumber = 5;
        $qdata->category = 1;
        $qdata->contextid = 1;
        $qdata->parent = 0;
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->length = 1;
        $qdata->hidden = 0;
        $qdata->timecreated = "1552376610";
        $qdata->timemodified = "1552376610";
        $qdata->stamp = "127.0.0.1+1552376610+76EZEc";
        $qdata->version = "127.0.0.1+155237661076EZEc";
        $qdata->defaultmark = 1;
        $qdata->penalty = 0.3333333;
        $qdata->questiontext = "Questiontext for Question 1";
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = "This feedback is general";
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->options = new stdClass();
        $qdata->options->scoringmethod = "subpoints";
        $qdata->options->answernumbering = 123;
        $qdata->options->shuffleanswers = 1;
        $qdata->options->numberofrows = 8;
        $qdata->options->numberofcolumns = 2;
        $qdata->options->rows = array(
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
            ),
            7 => (object) array(
                "id" => 7,
                "questionid" => 5,
                "number" => 3,
                "optiontext" => "option text 3",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 3",
                "optionfeedbackformat" => 1
            ),
            8 => (object) array(
                "id" => 8,
                "questionid" => 5,
                "number" => 4,
                "optiontext" => "option text 4",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 4",
                "optionfeedbackformat" => 1
            ),
            9 => (object) array(
                "id" => 9,
                "questionid" => 5,
                "number" => 5,
                "optiontext" => "option text 5",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 5",
                "optionfeedbackformat" => 1
            ),
            10 => (object) array(
                "id" => 10,
                "questionid" => 5,
                "number" => 6,
                "optiontext" => "option text 6",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 6",
                "optionfeedbackformat" => 1
            ),
            11 => (object) array(
                "id" => 11,
                "questionid" => 5,
                "number" => 7,
                "optiontext" => "option text 7",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 7",
                "optionfeedbackformat" => 1
            ),
            12 => (object) array(
                "id" => 12,
                "questionid" => 5,
                "number" => 8,
                "optiontext" => "option text 8",
                "optiontextformat" => 1,
                "optionfeedback" => "feedback to option 8",
                "optionfeedbackformat" => 1
            )
        );
        $qdata->options->columns = array(
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
        $qdata->options->weights = array(
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
                    "weight" => 1.000
                ),
                2 => (object) array (
                    "id" => 17,
                    "questionid" => 5,
                    "rownumber" => 2,
                    "columnnumber" => 2,
                    "weight" => 0.000
                )
            ),
            3 => array(
                1 => (object) array (
                    "id" => 18,
                    "questionid" => 5,
                    "rownumber" => 3,
                    "columnnumber" => 1,
                    "weight" => 1.000
                ),
                2 => (object) array (
                    "id" => 19,
                    "questionid" => 5,
                    "rownumber" => 3,
                    "columnnumber" => 2,
                    "weight" => 0.000
                )
            ),
            4 => array(
                1 => (object) array (
                    "id" => 20,
                    "questionid" => 5,
                    "rownumber" => 4,
                    "columnnumber" => 1,
                    "weight" => 1.000
                ),
                2 => (object) array (
                    "id" => 21,
                    "questionid" => 5,
                    "rownumber" => 4,
                    "columnnumber" => 2,
                    "weight" => 0.000
                )
            ),
            5 => array(
                1 => (object) array (
                    "id" => 22,
                    "questionid" => 5,
                    "rownumber" => 5,
                    "columnnumber" => 1,
                    "weight" => 0.000
                ),
                2 => (object) array (
                    "id" => 23,
                    "questionid" => 5,
                    "rownumber" => 5,
                    "columnnumber" => 2,
                    "weight" => 1.000
                )
            ),
            6 => array(
                1 => (object) array (
                    "id" => 24,
                    "questionid" => 5,
                    "rownumber" => 6,
                    "columnnumber" => 1,
                    "weight" => 0.000
                ),
                2 => (object) array (
                    "id" => 25,
                    "questionid" => 5,
                    "rownumber" => 6,
                    "columnnumber" => 2,
                    "weight" => 1.000
                )
            ),
            7 => array(
                1 => (object) array (
                    "id" => 26,
                    "questionid" => 5,
                    "rownumber" => 7,
                    "columnnumber" => 1,
                    "weight" => 0.000
                ),
                2 => (object) array (
                    "id" => 27,
                    "questionid" => 5,
                    "rownumber" => 7,
                    "columnnumber" => 2,
                    "weight" => 1.000
                )
            ),
            8 => array(
                1 => (object) array (
                    "id" => 28,
                    "questionid" => 5,
                    "rownumber" => 8,
                    "columnnumber" => 1,
                    "weight" => 0.000
                ),
                2 => (object) array (
                    "id" => 29,
                    "questionid" => 5,
                    "rownumber" => 8,
                    "columnnumber" => 2,
                    "weight" => 1.000
                )
            )
        );
        $qdata->hints = array(
            0 => (object) array(
                "questionid" => 5,
                "id" => 3,
                "hint" => "This is the 1st hint",
                "hintformat" => 1,
                "options" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
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
        return $qdata;
    }

    public static function get_mtf_question_form_data_question_two() {
        // Option text 1 : True.
        // Option text 2 : True.
        // Option text 3 : True.
        // Option text 4 : True.
        // Option text 5 : False.
        // Option text 6 : False.
        // Option text 7 : False.
        // Option text 8 : False.
        global $USER;
        $qdata = new stdClass();
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'mtf';
        $qdata->name = 'MTF-Question-002';
        $qdata->questiontext = array(
            "text" => 'Questiontext for Question 2',
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
        $qdata->numberofrows = 8;
        $qdata->numberofcolumns = 2;
        $qdata->answernumbering = 123;
        $qdata->option = array(
            0 => array(
                "text" => "option text 1",
                "format" => 1,
                "itemid" => 1
            ),
            1 => array(
                "text" => "option text 2",
                "format" => 1,
                "itemid" => 2
            ),
            2 => array(
                "text" => "option text 3",
                "format" => 1,
                "itemid" => 3
            ),
            3 => array(
                "text" => "option text 4",
                "format" => 1,
                "itemid" => 4
            ),
            4 => array(
                "text" => "option text 5",
                "format" => 1,
                "itemid" => 5
            ),
            5 => array(
                "text" => "option text 6",
                "format" => 1,
                "itemid" => 6
            ),
            6 => array(
                "text" => "option text 7",
                "format" => 1,
                "itemid" => 7
            ),
            7 => array(
                "text" => "option text 8",
                "format" => 1,
                "itemid" => 8
            )
        );
        $qdata->feedback = array(
            0 => array(
                "text" => "feedback to option 1",
                "format" => 1,
                "itemid" => 1
            ),
            1 => array(
                "text" => "feedback to option 2",
                "format" => 1,
                "itemid" => 2
            ),
            2 => array(
                "text" => "feedback to option 3",
                "format" => 1,
                "itemid" => 3
            ),
            3 => array(
                "text" => "feedback to option 4",
                "format" => 1,
                "itemid" => 4
            ),
            4 => array(
                "text" => "feedback to option 5",
                "format" => 1,
                "itemid" => 5
            ),
            5 => array(
                "text" => "feedback to option 6",
                "format" => 1,
                "itemid" => 6
            ),
            6 => array(
                "text" => "feedback to option 7",
                "format" => 1,
                "itemid" => 7
            ),
            7 => array(
                "text" => "feedback to option 8",
                "format" => 1,
                "itemid" => 8
            )
        );
        $qdata->weightbutton = array(
            0 => 1,
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 2,
            5 => 2,
            6 => 2,
            7 => 2
        );
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

    public static function get_mtf_question_form_data_question_three() {
        global $USER;
        $qdata = new stdClass();
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'mtf';
        $qdata->name = 'MTF-Question-003';
        $qdata->questiontext = array(
            "text" => 'Questiontext for Question 3',
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
        $qdata->numberofrows = 8;
        $qdata->numberofcolumns = 2;
        $qdata->answernumbering = 123;
        $qdata->option = array(
            0 => array(
                "text" => "option text 1",
                "format" => 1,
                "itemid" => 1
            ),
            1 => array(
                "text" => "option text 2",
                "format" => 1,
                "itemid" => 2
            ),
            2 => array(
                "text" => "option text 3",
                "format" => 1,
                "itemid" => 3
            ),
            3 => array(
                "text" => "option text 4",
                "format" => 1,
                "itemid" => 4
            ),
            4 => array(
                "text" => "option text 5",
                "format" => 1,
                "itemid" => 5
            ),
            5 => array(
                "text" => "option text 6",
                "format" => 1,
                "itemid" => 6
            ),
            6 => array(
                "text" => "option text 7",
                "format" => 1,
                "itemid" => 7
            ),
            7 => array(
                "text" => "option text 8",
                "format" => 1,
                "itemid" => 8
            )
        );
        $qdata->feedback = array(
            0 => array(
                "text" => "feedback to option 1",
                "format" => 1,
                "itemid" => 1
            ),
            1 => array(
                "text" => "feedback to option 2",
                "format" => 1,
                "itemid" => 2
            ),
            2 => array(
                "text" => "feedback to option 3",
                "format" => 1,
                "itemid" => 3
            ),
            3 => array(
                "text" => "feedback to option 4",
                "format" => 1,
                "itemid" => 4
            ),
            4 => array(
                "text" => "feedback to option 5",
                "format" => 1,
                "itemid" => 5
            ),
            5 => array(
                "text" => "feedback to option 6",
                "format" => 1,
                "itemid" => 6
            ),
            6 => array(
                "text" => "feedback to option 7",
                "format" => 1,
                "itemid" => 7
            ),
            7 => array(
                "text" => "feedback to option 8",
                "format" => 1,
                "itemid" => 8
            )
        );
        $qdata->weightbutton = array(
            0 => 1,
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 2,
            5 => 2,
            6 => 2,
            7 => 2
        );
        $qdata->responsetext_1 = "True";
        $qdata->responsetext_2 = "False";
        return $qdata;
    }

    public static function get_mtf_question_form_data_question_four() {
        global $USER;
        $qdata = new stdClass();
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'mtf';
        $qdata->name = 'MTF-Question-004';
        $qdata->questiontext = array(
            "text" => 'Questiontext for Question 4',
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
        $qdata->numberofrows = 8;
        $qdata->numberofcolumns = 2;
        $qdata->answernumbering = 123;
        $qdata->option = array(
            0 => array(
                "text" => "option text 1",
                "format" => 1,
                "itemid" => 1
            ),
            1 => array(
                "text" => "option text 2",
                "format" => 1,
                "itemid" => 2
            ),
            2 => array(
                "text" => "option text 3",
                "format" => 1,
                "itemid" => 3
            ),
            3 => array(
                "text" => "option text 4",
                "format" => 1,
                "itemid" => 4
            ),
            4 => array(
                "text" => "option text 5",
                "format" => 1,
                "itemid" => 5
            ),
            5 => array(
                "text" => "option text 6",
                "format" => 1,
                "itemid" => 6
            ),
            6 => array(
                "text" => "option text 7",
                "format" => 1,
                "itemid" => 7
            ),
            7 => array(
                "text" => "option text 8",
                "format" => 1,
                "itemid" => 8
            )
        );
        $qdata->feedback = array(
            0 => array(
                "text" => "feedback to option 1",
                "format" => 1,
                "itemid" => 1
            ),
            1 => array(
                "text" => "feedback to option 2",
                "format" => 1,
                "itemid" => 2
            ),
            2 => array(
                "text" => "feedback to option 3",
                "format" => 1,
                "itemid" => 3
            ),
            3 => array(
                "text" => "feedback to option 4",
                "format" => 1,
                "itemid" => 4
            ),
            4 => array(
                "text" => "feedback to option 5",
                "format" => 1,
                "itemid" => 5
            ),
            5 => array(
                "text" => "feedback to option 6",
                "format" => 1,
                "itemid" => 6
            ),
            6 => array(
                "text" => "feedback to option 7",
                "format" => 1,
                "itemid" => 7
            ),
            7 => array(
                "text" => "feedback to option 8",
                "format" => 1,
                "itemid" => 8
            )
        );
        $qdata->weightbutton = array(
            0 => 1,
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 2,
            5 => 2,
            6 => 2,
            7 => 2
        );
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
}
