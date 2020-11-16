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

class qtype_mtf_question extends question_graded_automatically_with_countback {

    public $rows;

    public $columns;

    public $weights;

    public $scoringmethod;

    public $shuffleanswers;

    public $numberofrows;

    public $numberofcols;

    public $order = null;

    public $editedquestion;

    // All the methods needed for option shuffling.
    /**
     * (non-PHPdoc).
     *
     * @see question_definition::start_attempt()
     */
    public function start_attempt(question_attempt_step $step, $variant) {
        $this->order = array_keys($this->rows);
        if ($this->shuffleanswers) {
            shuffle($this->order);
        }
        $step->set_qt_var('_order', implode(',', $this->order));
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_definition::apply_attempt_state()
     */
    public function apply_attempt_state(question_attempt_step $step) {
        $this->order = explode(',', $step->get_qt_var('_order'));

        // Add any missing answers. Sometimes people edit questions after they
        // have been attempted which breaks things.
        // Retrieve the question rows (mtf options).
        for ($i = 0; $i < count($this->order); $i++) {
            if (isset($this->rows[$this->order[$i]])) {
                continue;
            }

            $a = new stdClass();
            $a->id = 0;
            $a->questionid = $this->id;
            $a->number = -1;
            $a->optiontext = html_writer::span(get_string('deletedchoice', 'qtype_mtf'), 'notifyproblem');
            $a->optiontextformat = FORMAT_HTML;
            $a->optionfeedback = "";
            $a->optionfeedbackformat = FORMAT_HTML;
            $this->rows[$this->order[$i]] = $a;
            $this->editedquestion = 1;
        }
    }

    /**
     *
     * @param question_attempt $qa
     *
     * @return multitype:
     */
    public function get_order(question_attempt $qa) {
        $this->init_order($qa);

        return $this->order;
    }

    /**
     * Initialises the order (if it is not set yet) by decoding
     * the question attempt variable '_order'.
     *
     * @param question_attempt $qa
     */
    protected function init_order(question_attempt $qa) {
        if (is_null($this->order)) {
            $this->order = explode(',', $qa->get_step(0)->get_qt_var('_order'));
        }
    }

    /**
     * Returns the name field name for input cells in the questiondisplay.
     * The column parameter is ignored for now since we don't use multiple answers.
     *
     * @param mixed $row
     * @param mixed $col
     *
     * @return type
     */
    public function field($key) {
        return 'option' . $key;
    }

    /**
     * Checks whether an row is answered by a given response.
     *
     * @param type $response
     * @param type $row
     * @param type $col
     *
     * @return bool
     */
    public function is_answered($response, $rownumber) {
        $field = $this->field($rownumber);
        // Get the value of the radiobutton array, if it exists in the response.
        return isset($response[$field]) && !empty($response[$field]);
    }

    /**
     * Checks whether a given column (response) is the correct answer for a given row (option).
     *
     * @param string $row The row number.
     * @param string $col The column number
     *
     * @return bool
     */
    public function is_correct($row, $col) {
        $weight = $this->weight($row, $col);

        if ($weight > 0.0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Returns the weight for the given row and column.
     *
     * @param mixed $row A row object or a row number.
     * @param mixed $col A column object or a column number.
     *
     * @return float
     */
    public function weight($row = null, $col = null) {
        $rownumber = is_object($row) ? $row->number : $row;
        $colnumber = is_object($col) ? $col->number : $col;
        if (isset($this->weights[$rownumber][$colnumber])) {
            $weight = (float) $this->weights[$rownumber][$colnumber]->weight;
        } else {
            $weight = 0;
        }

        return $weight;
    }

    public function is_row_selected($response, $rownumber) {
        return isset($response[$this->field($rownumber)]);
    }

    public function get_response(question_attempt $qa) {
        return $qa->get_last_qt_data();
    }

    /**
     * Used by many of the behaviours, to work out whether the student's
     * response to the question is complete.
     * That is, whether the question attempt
     * should move to the COMPLETE or INCOMPLETE state.
     *
     * @param array $response responses, as returned by
     *        {@link question_attempt_step::get_qt_data()}.
     *
     * @return bool whether this response is a complete answer to this question.
     */
    public function is_complete_response(array $response) {
        if (count($response) == count($this->rows)) {
            return true;
        } else {
            return false;
        }
    }

    public function is_gradable_response(array $response) {
        unset($response['_order']);
        if ($this->scoringmethod == 'subpoints') {
            if (count($response) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return $this->is_complete_response($response);
        }
    }

    /**
     * In situations where is_gradable_response() returns false, this method
     * should generate a description of what the problem is.
     *
     * @return string the message.
     */
    public function get_validation_error(array $response) {
        $isgradable = $this->is_gradable_response($response);
        if ($isgradable) {
            return '';
        }
        return get_string('oneanswerperrow', 'qtype_mtf');
    }

    /**
     *
     * @param array $response responses, as returned by
     *        {@link question_attempt_step::get_qt_data()}.
     * @return int the number of choices that were selected. in this response.
     */
    public function get_num_selected_choices(array $response) {
        $numselected = 0;
        foreach ($response as $key => $value) {
            // Response keys starting with _ are internal values like _order, so ignore them.
            if (!empty($value) && $key[0] != '_') {
                $numselected += 1;
            }
        }
        return $numselected;
    }

    /**
     * Produce a plain text summary of a response.
     *
     * @param $response a response, as might be passed to {@link grade_response()}.
     *
     * @return string a plain text summary of that response, that could be used in reports.
     */
    public function summarise_response(array $response) {
        $result = array();

        foreach ($this->order as $key => $rowid) {
            $field = $this->field($key);
            $row = $this->rows[$rowid];

            if (isset($response[$field])) {
                foreach ($this->columns as $column) {
                    if ($column->number == $response[$field]) {
                        $result[] = $this->html_to_text($row->optiontext, $row->optiontextformat) .
                                 ': ' . $this->html_to_text($column->responsetext,
                                        $column->responsetextformat);
                    }
                }
            }
        }
        return implode('; ', $result);
    }
    /**
     * (non-PHPdoc).
     *
     * @see question_with_responses::classify_response()
     */
    public function classify_response(array $response) {
        // See which column numbers have been selected.
        $selectedcolumns = array();
        $weights = $this->weights;
        foreach ($this->order as $key => $rowid) {
            $field = $this->field($key);
            $row = $this->rows[$rowid];

            if (array_key_exists($field, $response) && $response[$field]) {
                $selectedcolumns[$rowid] = $response[$field];
            } else {
                $selectedcolumns[$rowid] = 0;
            }
        }

        $parts = array();
        // Now calculate the classification for MTF.
        foreach ($this->rows as $rowid => $row) {
            $field = $this->field($key);
            if (empty($selectedcolumns[$rowid])) {
                $parts[$rowid] = question_classified_response::no_response();
                continue;
            }
            // Find the chosen column by columnnumber.
            $column = null;
            foreach ($this->columns as $colid => $col) {
                if ($col->number == $selectedcolumns[$rowid]) {
                    $column = $col;
                    break;
                }
            }
            if (empty($column)) {
                $parts[$rowid] = question_classified_response::no_response();
                continue;
            }
            // Calculate the partial credit.
            if ($this->scoringmethod == 'subpoints') {
                $partialcredit = 0.0;
            } else {
                $partialcredit = -0.999; // Due to non-linear math.
            }
            if ($this->scoringmethod == 'subpoints' &&
                     $this->weights[$row->number][$column->number]->weight > 0) {
                $partialcredit = 1 / count($this->rows);
            }
            $parts[$rowid] = new question_classified_response($column->id, $column->responsetext,
                    $partialcredit);
        }

        return $parts;
    }

    /**
     * Use by many of the behaviours to determine whether the student's
     * response has changed.
     * This is normally used to determine that a new set
     * of responses can safely be discarded.
     *
     * @param array $prevresponse the responses previously recorded for this question,
     *        as returned by {@link question_attempt_step::get_qt_data()}
     * @param array $newresponse the new responses, in the same format.
     *
     * @return bool whether the two sets of responses are the same - that is
     *         whether the new set of responses can safely be discarded.
     */
    public function is_same_response(array $prevresponse, array $newresponse) {
        if (count($prevresponse) != count($newresponse)) {
            return false;
        }
        foreach ($prevresponse as $field => $previousvalue) {
            if (!isset($newresponse[$field])) {
                return false;
            }
            $newvalue = $newresponse[$field];
            if ($newvalue != $previousvalue) {
                return false;
            }
        }

        return true;
    }

    /**
     * What data would need to be submitted to get this question correct.
     * If there is more than one correct answer, this method should just
     * return one possibility.
     *
     * @return array parameter name => value.
     */
    public function get_correct_response($rowidindex = false) {
        $result = array();
        foreach ($this->order as $key => $rowid) {
            $row = $this->rows[$rowid];
            $field = $this->field($key);

            foreach ($this->columns as $column) {
                $weight = $this->weight($row, $column);
                if ($weight > 0) {
                    if ($rowidindex) {
                        $result[$rowid] = $column->id;
                    } else {
                        $result[$field] = $column->number;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns an instance of the grading class according to the scoringmethod of the question.
     *
     * @return The grading object.
     */
    public function grading() {
        global $CFG;
        $type = $this->scoringmethod;
        $gradingclass = 'qtype_mtf_grading_' . $type;
        require_once($CFG->dirroot . '/question/type/mtf/grading/' . $gradingclass . '.class.php');

        return new $gradingclass();
    }

    /**
     * Grade a response to the question, returning a fraction between
     * get_min_fraction() and 1.0, and the corresponding {@link question_state}
     * right, partial or wrong.
     *
     * @param array $response responses, as returned by
     *        {@link question_attempt_step::get_qt_data()}.
     *
     * @return array (number, integer) the fraction, and the state.
     */
    public function grade_response(array $response) {
        $grade = $this->grading()->grade_question($this, $response);
        $state = question_state::graded_state_for_fraction($grade);

        return array($grade, $state
        );
    }

    /**
     * What data may be included in the form submission when a student submits
     * this question in its current state?
     *
     * This information is used in calls to optional_param. The parameter name
     * has {@link question_attempt::get_field_prefix()} automatically prepended.
     *
     * @return array|string variable name => PARAM_... constant, or, as a special case
     *         that should only be used in unavoidable, the constant question_attempt::USE_RAW_DATA
     *         meaning take all the raw submitted data belonging to this question.
     */
    public function get_expected_data() {
        $result = array();
        foreach ($this->order as $key => $notused) {
            $field = $this->field($key);
            $result[$field] = PARAM_INT;
        }

        return $result;
    }

    /**
     * Returns an array where keys are the cell names and the values
     * are the weights.
     *
     * @return array
     */
    public function cells() {
        $result = array();
        foreach ($this->order as $key => $rowid) {
            $row = $this->rows[$rowid];
            $field = $this->field($key);
            foreach ($this->columns as $column) {
                $result[$field] = $this->weight($row->number, $column->number);
            }
        }

        return $result;
    }

    /**
     * Makes HTML text (e.g.
     * option or feedback texts) suitable for inline presentation in renderer.php.
     *
     * @param string html The HTML code.
     *
     * @return string the purified HTML code without paragraph elements and line breaks.
     */
    public function make_html_inline($html) {
        $html = preg_replace('~\s*<p>\s*~u', '', $html);
        $html = preg_replace('~\s*</p>\s*~u', '<br />', $html);
        $html = preg_replace('~(<br\s*/?>)+$~u', '', $html);

        return trim($html);
    }

    /**
     * Convert some part of the question text to plain text.
     * This might be used,
     * for example, by get_response_summary().
     *
     * @param string $text The HTML to reduce to plain text.
     * @param int $format the FORMAT_... constant.
     *
     * @return string the equivalent plain text.
     */
    public function html_to_text($text, $format) {
        return question_utils::to_plain_text($text, $format);
    }

    /**
     * Computes the final grade when "Multiple Attempts" or "Hints" are enabled
     *
     * @param array $responses Contains the user responses. 1st dimension = attempt, 2nd dimension = answers
     * @param int $totaltries Not needed
     */
    public function compute_final_grade($responses, $totaltries) {
        $lastresponse = count($responses) - 1;
        $numpoints = isset($responses[$lastresponse]) ? $this->grading()->grade_question($this, $responses[$lastresponse]) : 0;
        return max(0, $numpoints - max(0, $lastresponse) * $this->penalty);
    }

    /**
     * Disable those hint settings that we don't want when the student has selected
     * more choices than the number of right choices.
     * This avoids giving the game away.
     *
     * @param question_hint_with_parts $hint a hint.
     */
    protected function disable_hint_settings_when_too_many_selected(question_hint_with_parts $hint) {
        $hint->clearwrong = false;
    }

    public function get_hint($hintnumber, question_attempt $qa) {
        $hint = parent::get_hint($hintnumber, $qa);
        if (is_null($hint)) {
            return $hint;
        }
        return $hint;
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_definition::check_file_access()
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'qtype_mtf' && $filearea == 'optiontext') {
            return true;
        } else if ($component == 'qtype_mtf' && $filearea == 'feedbacktext') {
            return true;
        } else if ($component == 'question' && in_array($filearea,
                array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'
                ))) {
            if ($this->editedquestion == 1) {
                return true;
            } else {
                return $this->check_combined_feedback_file_access($qa, $options, $filearea);
            }
        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);
        } else {
            return parent::check_file_access($qa, $options, $component, $filearea, $args,
                    $forcedownload);
        }
    }
}
