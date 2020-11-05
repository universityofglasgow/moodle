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
 * This file defines the report class for STACK questions.
 *
 * @copyright  2012 the University of Birmingham
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport.php');
require_once($CFG->dirroot . '/mod/quiz/report/statistics/report.php');
require_once($CFG->dirroot . '/question/type/stack/locallib.php');


/**
 * Report subclass for the responses report to individual stack questions.
 *
 * @copyright 2012 the University of Birmingham
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_stack_report extends quiz_attempts_report {

    /** @var The quiz context. */
    protected $context;

    /** @var qubaid_condition used to select the attempts to include in SQL queries. */
    protected $qubaids;

    /** @array The names of all inputs for this question.*/
    protected $inputs;

    /** @array The names of all prts for this question.*/
    protected $prts;

    /** @array The deployed questionnotes for this question.*/
    protected $qnotes;

    /** @array The attempts at this question.*/
    protected $attempts;

    public function display($quiz, $cm, $course) {
        global $CFG, $DB, $OUTPUT;

        // Initialise the required data.
        $this->mode = 'stack';
        $this->context = context_module::instance($cm->id);

        list($currentgroup, $students, $groupstudents, $allowed) =
                $this->load_relevant_students($cm, $course);

        $this->qubaids = quiz_statistics_qubaids_condition($quiz->id, $currentgroup, $groupstudents, true);

        $questionsused = $this->get_stack_questions_used_in_attempt($this->qubaids);

        $questionid = optional_param('questionid', 0, PARAM_INT);

        // Display the appropriate page.
        $this->print_header_and_tabs($cm, $course, $quiz);
        if (!$questionsused) {
            $this->display_no_stack_questions();

        } else if (!$questionid) {
            $this->display_index($questionsused);

        } else if (array_key_exists($questionid, $questionsused)) {
            $this->display_analysis($questionsused[$questionid]);

        } else {
            $this->display_unknown_question();
        }
    }

    /**
     * Get all the STACK questions used in all the attempts at a quiz. (Note that
     * Moodle random questions may be being used.)
     * @param qubaid_condition $qubaids the attempts of interest.
     * @return array of rows from the question table.
     */
    protected function get_stack_questions_used_in_attempt(qubaid_condition $qubaids) {
        global $DB;

        return $DB->get_records_sql("
                SELECT q.*
                  FROM {question} q
                  JOIN (
                        SELECT qa.questionid, MIN(qa.slot) AS firstslot
                          FROM {$qubaids->from_question_attempts('qa')}
                         WHERE {$qubaids->where()}
                      GROUP BY qa.questionid
                       ) usedquestionids ON q.id = usedquestionids.questionid
                 WHERE q.qtype = 'stack'
              ORDER BY usedquestionids.firstslot
                ", $qubaids->from_where_params());
    }

    /**
     * Display a message saying there are no STACK questions in this quiz.
     */
    public function display_no_stack_questions() {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('nostackquestions', 'quiz_stack'));
    }

    /**
     * Display an error if the question id is unrecognised.
     */
    public function display_unknown_question() {
        print_error('questiondoesnotexist', 'question');
    }

    /**
     * Display an index page listing all the STACK questions in the quiz,
     * with a link to get a detailed analysis of each one.
     * @param array $questionsused the STACK questions used in this quiz.
     */
    public function display_index($questionsused) {
        global $OUTPUT;

        $baseurl = $this->get_base_url();
        echo $OUTPUT->heading(get_string('stackquestionsinthisquiz', 'quiz_stack'));
        echo html_writer::tag('p', get_string('stackquestionsinthisquiz_descript', 'quiz_stack'));

        echo html_writer::start_tag('ul');
        foreach ($questionsused as $question) {
            echo html_writer::tag('li', html_writer::link(
                    new moodle_url($baseurl, array('questionid' => $question->id)),
                    format_string($question->name)));
        }
        echo html_writer::end_tag('ul');
    }

    /**
     * Display analysis of a particular question in this quiz.
     * @param object $question the row from the question table for the question to analyse.
     */
    public function display_analysis($question) {
        get_question_options($question);
        $this->display_question_information($question);

        $dm = new question_engine_data_mapper();
        $this->attempts = $dm->load_attempts_at_question($question->id, $this->qubaids);

        // Setup useful internal arrays for report generation.
        $this->inputs = array_keys($question->inputs);
        $this->prts = array_keys($question->prts);

        // TODO: change this to be a list of all *deployed* notes, not just those *used*.
        $qnotes = array();
        foreach ($this->attempts as $qa) {
            $q = $qa->get_question();
            $qnotes[$q->get_question_summary()] = true;
        }
        $this->qnotes = array_keys($qnotes);

        // Compute results.
        list ($results, $answernoteresults, $answernoteresultsraw) = $this->input_report();
        list ($validresults, $invalidresults) = $this->input_report_separate();

        // Display the results.

        // Overall results.
        $i = 0;
        $list = '';
        $tablehead = array();
        foreach ($this->qnotes as $qnote) {
            $list .= html_writer::tag('li', stack_ouput_castext($qnote));
            $i++;
            $tablehead[] = $i;
        }
        $tablehead[] = format_string(get_string('questionreportingtotal', 'quiz_stack'));
        $tablehead = array_merge(array(''), $tablehead, $tablehead);

        echo html_writer::tag('p', get_string('notesused', 'quiz_stack'));
        echo html_writer::tag('ol', $list);

        // Complete anwernotes.
        $inputstable = new html_table();
        $inputstable->head = $tablehead;
        $data = array();
        foreach ($answernoteresults as $prt => $anotedata) {
            if (count($answernoteresults) > 1) {
                $inputstable->data[] = array(html_writer::tag('b', $this->prts[$prt]));
            }
            $cstats = $this->column_stats($anotedata);
            foreach ($anotedata as $anote => $a) {
                $inputstable->data[] = array_merge(array($anote), $a, array(array_sum($a)), $cstats[$anote]);
            }
        }
        echo html_writer::tag('p', get_string('completenotes', 'quiz_stack'));
        echo html_writer::table($inputstable);

        // Split anwernotes.
        $inputstable = new html_table();
        $inputstable->head = $tablehead;
        foreach ($answernoteresultsraw as $prt => $anotedata) {
            if (count($answernoteresultsraw) > 1) {
                $inputstable->data[] = array(html_writer::tag('b', $this->prts[$prt]));
            }
            $cstats = $this->column_stats($anotedata);
            foreach ($anotedata as $anote => $a) {
                $inputstable->data[] = array_merge(array($anote), $a, array(array_sum($a)), $cstats[$anote]);
            }
        }
        echo html_writer::tag('p', get_string('splitnotes', 'quiz_stack'));
        echo html_writer::table($inputstable);

        // Maxima analysis.
        $maxheader = array();
        $maxheader[] = "STACK input data for the question '". $question->name."'";
        $maxheader[] = new moodle_url($this->get_base_url(), array('questionid' => $question->id));
        $maxheader[] = "Data generated: ".date("Y-m-d H:i:s");
        $maximacode = $this->maxima_comment($maxheader);
        $maximacode .= "\ndisplay2d:true$\nload(\"stackreporting\")$\n";
        $maximacode .= "stackdata:[]$\n";
        $variants = array();
        foreach ($this->qnotes as $qnote) {
            $variants[] = '"'.$qnote.'"';
        }
        $inputs = array();
        foreach ($this->inputs as $input) {
            $inputs[] = $input;
        }
        $anymaximadata = false;

        // Results for each question note.
        foreach ($this->qnotes as $qnote) {
            echo html_writer::tag('h2', get_string('variantx', 'quiz_stack').stack_ouput_castext($qnote));

            $inputstable = new html_table();
            $inputstable->attributes['class'] = 'generaltable stacktestsuite';
            $inputstable->head = array_merge(
                    array(
                        get_string('questionreportingsummary', 'quiz_stack'),
                        '',
                        get_string('questionreportingscore', 'quiz_stack')
                    ), $this->prts);
            foreach ($results[$qnote] as $dsummary => $summary) {
                foreach ($summary as $key => $res) {
                    $inputstable->data[] = array_merge(array($dsummary, $res['count'], $res['fraction']), $res['answernotes']);
                }
            }
            echo html_writer::table($inputstable);

            // Separate out inputs and look at validity.
            $validresultsdata = array();
            foreach ($this->inputs as $input) {
                $inputstable = new html_table();
                $inputstable->attributes['class'] = 'generaltable stacktestsuite';
                $inputstable->head = array($input, '', '', '');
                foreach ($validresults[$qnote][$input] as $key => $res) {
                    $validresultsdata[$input][] = $key;
                    $inputstable->data[] = array($key, $res, get_string('inputstatusnamevalid', 'qtype_stack'), '');
                    $inputstable->rowclasses[] = 'pass';
                }
                foreach ($invalidresults[$qnote][$input] as $key => $res) {
                    $inputstable->data[] = array($key, $res[0], get_string('inputstatusnameinvalid', 'qtype_stack'), $res[1]);
                    $inputstable->rowclasses[] = 'fail';
                }
                echo html_writer::table($inputstable);
            }

            // Maxima analysis.
            $maximacode .= "\n/* ".$qnote.' */ '."\n";
            foreach ($this->inputs as $input) {
                if (array_key_exists($input, $validresultsdata)) {
                    $maximacode .= $this->maxima_list_create($validresultsdata[$input], $input);
                    $anymaximadata = true;
                }
            }

            $maximacode .= "stackdata:append(stackdata,[[" . implode(',', $inputs) . "]])$\n";
        }

        // Maxima analysis at the end.
        if ($anymaximadata) {
            $maximacode .= "\n/* Reset input names */\nkill(" . implode(',', $inputs) . ")$\n";
            $maximacode .= $this->maxima_list_create($variants, 'variants');
            $maximacode .= $this->maxima_list_create($inputs, 'inputs');
            $maximacode .= "\n/* Perform the analysis. */\nstack_analysis(stackdata)$\n";
            echo html_writer::tag('h3', get_string('maximacode', 'quiz_stack'));
            echo html_writer::tag('p', get_string('offlineanalysis', 'quiz_stack'));
            $rows = count(explode("\n", $maximacode)) + 2;
            echo html_writer::tag('textarea', $maximacode,
                    array('readonly' => 'readonly', 'wrap' => 'virtual', 'rows' => $rows, 'cols' => '160'));
        }
    }

    /**
     * This function counts the number of response summaries per question note.
     */
    protected function input_report() {

        // The array $results holds the by question note analysis.
        $results = array();
        foreach ($this->qnotes as $qnote) {
            $results[$qnote] = array();
        }
        // Splits up the results to look for which answernotes occur most often.
        $answernoteresults = array();
        $answernoteresultsraw = array();
        foreach ($this->prts as $prtname => $prt) {
            $answernoteresults[$prtname] = array();
            $answernoteresultsraw[$prtname] = array();
        }
        $answernoteemptyrow = array();
        foreach ($this->qnotes as $qnote) {
            $answernoteemptyrow[$qnote] = '';
        }

        foreach ($this->attempts as $qattempt) {
            $question = $qattempt->get_question();
            $qnote = $question->get_question_summary();
            for ($i = 0; $i < $qattempt->get_num_steps(); $i++) {
                $step = $qattempt->get_step($i);
                $response = $step->get_submitted_data();
                if ($data = $this->nontrivial_response_step($qattempt, $i)) {
                    $fraction = trim((string) round($step->get_fraction(), 3));
                    $summary = $question->summarise_response($response);

                    $answernotes = array();
                    foreach ($this->prts as $prtname => $prt) {
                        $prtobject = $question->get_prt_result($prt, $response, true);
                        $rawanswernotes = $prtobject->__get('answernotes');

                        foreach ($rawanswernotes as $anote) {
                            if (!array_key_exists($anote, $answernoteresultsraw[$prtname])) {
                                $answernoteresultsraw[$prtname][$anote] = $answernoteemptyrow;
                            }
                            $answernoteresultsraw[$prtname][$anote][$qnote] += 1;
                        }

                        $answernotes[$prt] = implode(' | ', $rawanswernotes);
                        if (!array_key_exists($answernotes[$prt], $answernoteresults[$prtname])) {
                            $answernoteresults[$prtname][$answernotes[$prt]] = $answernoteemptyrow;
                        }
                        $answernoteresults[$prtname][$answernotes[$prt]][$qnote] += 1;
                    }

                    $answernotekey = implode(' # ', $answernotes);

                    if (array_key_exists($summary, $results[$qnote])) {
                        if (array_key_exists($answernotekey, $results[$qnote][$summary])) {
                            $results[$qnote][$summary][$answernotekey]['count'] += 1;
                            if ('' != $fraction) {
                                $results[$qnote][$summary][$answernotekey]['fraction'] = $fraction;
                            }
                        } else {
                            $results[$qnote][$summary][$answernotekey]['count'] = 1;
                            $results[$qnote][$summary][$answernotekey]['answernotes'] = $answernotes;
                            $results[$qnote][$summary][$answernotekey]['fraction'] = $fraction;
                        }
                    } else {
                        $results[$qnote][$summary][$answernotekey]['count'] = 1;
                        $results[$qnote][$summary][$answernotekey]['answernotes'] = $answernotes;
                        $results[$qnote][$summary][$answernotekey]['fraction'] = $fraction;
                    }
                }
            }
        }

        return array($results, $answernoteresults, $answernoteresultsraw);
    }

    /**
     * Counts the number of response to each input and records their validity.
     */
    protected function input_report_separate() {

        $results = array();
        $validity = array();
        foreach ($this->qnotes as $qnote) {
            foreach ($this->inputs as $input) {
                $results[$qnote][$input] = array();
            }
        }

        foreach ($this->attempts as $qattempt) {
            $question = $qattempt->get_question();
            $qnote = $question->get_question_summary();

            for ($i = 0; $i < $qattempt->get_num_steps(); $i++) {
                $step = $qattempt->get_step($i);
                $response = $step->get_submitted_data();
                if ($data = $this->nontrivial_response_step($qattempt, $i)) {
                    $summary = $question->summarise_response_data($response);
                    foreach ($this->inputs as $input) {
                        if (array_key_exists($input, $summary)) {
                            if ('' != $data[$input]->contentsmodified) {
                                if (array_key_exists($data[$input]->contentsmodified,  $results[$qnote][$input])) {
                                    $results[$qnote][$input][$data[$input]->contentsmodified] += 1;
                                } else {
                                    $results[$qnote][$input][$data[$input]->contentsmodified] = 1;
                                }
                            }
                            $validity[$qnote][$input][$data[$input]->contentsmodified] =
                                    array($data[$input]->status, $data[$input]->note);
                        }
                    }
                }
            }
        }

        foreach ($this->qnotes as $qnote) {
            foreach ($this->inputs as $input) {
                arsort($results[$qnote][$input]);
            }
        }

        // Split into valid and invalid responses.
        $validresults = array();
        $invalidresults = array();
        foreach ($this->qnotes as $qnote) {
            foreach ($this->inputs as $input) {
                $validresults[$qnote][$input] = array();
                $invalidresults[$qnote][$input] = array();
                foreach ($results[$qnote][$input] as $key => $res) {
                    if ('valid' == $validity[$qnote][$input][$key][0] or 'score' == $validity[$qnote][$input][$key][0]) {
                        $validresults[$qnote][$input][$key] = $res;
                    } else {
                        $invalidresults[$qnote][$input][$key] = array($res, $validity[$qnote][$input][$key][1]);
                    }
                }
            }
        }

        return array($validresults, $invalidresults);
    }

    /**
     * From an individual attempt, we need to establish that step $i for this
     * attempt is non-trivial, and return the non-trivial responses;
     * otherwise we return boolean false.
     */
    protected function nontrivial_response_step($qa, $i) {
        $anydata = false;
        $rdata = array();
        $question = $qa->get_question();

        // TODO: work out which states need to be reported.
        // if ('question_state_todo' == get_class($step->get_state())) {
        $step = $qa->get_step($i);
        $response = $step->get_submitted_data();

        foreach ($question->inputs as $iname => $input) {
            $inputstate = $question->get_input_state($iname, $response);
            if ('' != trim($inputstate->status)) {
                $anydata = true;
            }
            // Ensure every input name has an entry in the $rdata array, even if it is empty.
            $rdata[$iname] = $inputstate;

        }
        if ($anydata) {
            return $rdata;
        }
        // }
        return false;
    }

    /*
     * This function simply prints out some useful information about the question.
     */
    private function display_question_information($question) {
        global $OUTPUT;
        $opts = $question->options;

        echo $OUTPUT->heading($question->name, 3);

        // Display the question variables.
        echo $OUTPUT->heading(stack_string('questionvariables'), 3);
        echo html_writer::start_tag('div', array('class' => 'questionvariables'));
        echo  html_writer::tag('pre', htmlspecialchars($opts->questionvariables));
        echo html_writer::end_tag('div');

        echo $OUTPUT->heading(stack_string('questiontext'), 3);
        echo html_writer::tag('div', html_writer::tag('div', stack_ouput_castext($question->questiontext),
        array('class' => 'outcome generalfeedback')), array('class' => 'que'));

        echo $OUTPUT->heading(stack_string('generalfeedback'), 3);
        echo html_writer::tag('div', html_writer::tag('div', stack_ouput_castext($question->generalfeedback),
        array('class' => 'outcome generalfeedback')), array('class' => 'que'));

        echo $OUTPUT->heading(stack_string('questionnote'), 3);
        echo html_writer::tag('div', html_writer::tag('div', stack_ouput_castext($opts->questionnote),
        array('class' => 'outcome generalfeedback')), array('class' => 'que'));

        echo $OUTPUT->heading(get_string('pluginname', 'quiz_stack'), 3);
    }

    /*
     * Take an array of numbers and create an array containing %s for each column.
     */
    private function column_stats($data) {
        $rdata = array();
        foreach ($data as $anote => $a) {
            $rdata[$anote] = array_merge(array_values($a), array(array_sum($a)));
        }
        reset($data);
        $coltotal = array_fill(0, count(current($data)) + 1, 0);
        foreach ($rdata as $anote => $row) {
            foreach ($row as $key => $col) {
                $coltotal[$key] += $col;
            }
        }
        foreach ($rdata as $anote => $row) {
            foreach ($row as $key => $col) {
                if (0 != $coltotal[$key]) {
                    $rdata[$anote][$key] = round(100 * $col / $coltotal[$key], 1);
                }
            }
        }
        return $rdata;
    }


    /**
     * Takes an array of $data and a $listname and creates maxima code for a list assigned to the name $listname.
     * This splits up very long lists into reasonable size lists so as not to overflow maxima input.
     */
    private function maxima_list_create($data, $listname) {
        if (empty($data)) {
            return '';
        }

        $concatarray = array();
        $toolong = false;
        $maximacode = '';
        foreach ($data as $val) {
            $concatarray[] = $val;
            $cct = implode($concatarray, ',');
            // This ensures we don't have one entry for each differenet input, leading to impossibly long sessions.
            if (strlen($cct) > 100) {
                $toolong = true;
                $maximacode .= $listname.':append('.$listname.',['.$cct."])$\n";
                $concatarray = array();
            }
        }
        if ($toolong) {
            if (empty($concatarray)) {
                $maximacode = $listname.":[]$\n".$maximacode;
            } else {
                $maximacode = $listname.":[]$\n".$maximacode.$listname.':append('.$listname.',['.$cct."])$\n";
            }
        } else {
            $maximacode = $listname.':['.$cct."]$\n";
        }
        return $maximacode;
    }

    /**
     * Takes an array of strings and generates a formatted Maxima comment block.
     */
    private function maxima_comment($data) {
        if (empty($data)) {
            return '';
        }

        $l = 0;
        foreach ($data as $k => $h) {
            $l = max(strlen($h), $l);
        }
        $comment = str_pad('/**', $l + 3, '*') . "**/\n";
        $maximacode = $comment;
        foreach ($data as $k => $h) {
            // Warning: pad_str doesn't work here.
            $offset = substr_count($h, '&') * 4;
            $maximacode .= '/* '.$h.str_repeat(' ', $l - strlen($h) + $offset)." */\n";
        }
        $maximacode .= $comment;
        return $maximacode;
    }
}
