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

require_once($CFG->libdir . '/outputcomponents.php');

/**
 * Subclass for generating the bits of output specific to mtf questions.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_mtf_renderer extends qtype_renderer {

    /**
     *
     * @return string
     */
    protected function get_input_type() {
        return 'radio';
    }

    /**
     *
     * @param question_attempt $qa
     * @param unknown $value
     *
     * @return string
     */
    protected function get_input_name(question_attempt $qa, $value) {
        return $qa->get_qt_field_name('option');
    }

    /**
     *
     * @param unknown $value
     *
     * @return unknown
     */
    protected function get_input_value($value) {
        return $value;
    }

    /**
     *
     * @param question_attempt $qa
     * @param unknown $value
     *
     * @return string
     */
    protected function get_input_id(question_attempt $qa, $value) {
        return $qa->get_qt_field_name('option' . $value);
    }

    /**
     * Generate the display of the formulation part of the question.
     * This is the
     * area that contains the question text (stem), and the controls for students to
     * input their answers.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     *
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $displayoptions) {
        global $CFG;
        $question = $qa->get_question();
        $response = $question->get_response($qa);

        $inputname = $qa->get_qt_field_name('option');
        $inputattributes = array('type' => $this->get_input_type(), 'name' => $inputname
        );

        if ($displayoptions->readonly) {
            $inputattributes['disabled'] = 'disabled';
        }

        $this->page->requires->js(
                new moodle_url($CFG->wwwroot . '/question/type/mtf/js/attempt.js'));

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'
                ));

        $table = new html_table();
        $table->attributes['class'] = 'generaltable';

        $table->head = array();
        // Add empty header for option texts.
        // Add the response texts as table headers.
        foreach ($question->columns as $column) {
            $cell = new html_table_cell(
                    $question->make_html_inline(
                            $question->format_text($column->responsetext,
                                    $column->responsetextformat, $qa, 'question', 'response',
                                    $column->id)));
            $table->head[] = $cell;
        }

        // Add empty header for correctness if needed.
        if ($displayoptions->correctness) {
            $table->head[] = '';
        }
        // Add empty header for feedback if needed.
        if ($displayoptions->feedback) {
            $table->head[] = '';
        }

        $rowcount = 0;
        $isreadonly = $displayoptions->readonly;

        foreach ($question->get_order($qa) as $key => $rowid) {
            $field = $question->field($key);
            $row = $question->rows[$rowid];
            // Holds the data for one table row.
            $rowdata = array();

            // Add the response radio buttons to the table.
            foreach ($question->columns as $column) {
                $buttonname = $qa->get_field_prefix() . $field;
                $buttonid = 'qtype_mtf_' . $qa->get_field_prefix() . $field;
                $qtypemtfid = 'qtype_mtf_' . $question->id;
                $datacol = 'data-mtf="' . $qtypemtfid . '"';
                $ischecked = false;
                if (array_key_exists($field, $response) && ($response[$field] == $column->number)) {
                    $ischecked = true;
                }
                $datamulti = 'data-multimtf="1"';
                $singleormulti = 2; // Multi.

                $radio = $this->radiobutton($buttonname, $column->number, $ischecked, $isreadonly,
                        $buttonid, $datacol, $datamulti, $singleormulti, $qtypemtfid);
                // Show correctness icon with radio button if needed.
                if ($displayoptions->correctness) {
                    $weight = $question->weight($row->number, $column->number);
                    $radio .= '<span class="mtfgreyingout">' . $this->feedback_image($weight > 0.0) .
                             '</span>';
                }
                $cell = new html_table_cell($radio);
                $cell->attributes['class'] = 'mtfresponsebutton';
                $rowdata[] = $cell;
            }

            // Add the formated option text to the table.
            $rowtext = $this->number_in_style($rowcount, $question->answernumbering) . $question->make_html_inline(
                    $question->format_text($row->optiontext, $row->optiontextformat, $qa,
                            'qtype_mtf', 'optiontext', $row->id));
            $rowcount++;
            $cell = new html_table_cell(
                    '<span class="optiontext"><label>' . $rowtext .
                             '</label></span>');
            $cell->attributes['class'] = 'optiontext';
            $rowdata[] = $cell;
            // Has a selection been made for this option?
            $isselected = $question->is_answered($response, $key);
            // For correctness we have to grade the option...
            if ($displayoptions->correctness) {
                $rowgrade = $question->grading()->grade_row($question, $key, $row, $response);
                $cell = new html_table_cell($this->feedback_image($rowgrade));
                $cell->attributes['class'] = 'mtfcorrectness';
                $rowdata[] = $cell;
            }
            // Add the feedback to the table, if it is visible.
            if ($displayoptions->feedback && empty($displayoptions->suppresschoicefeedback) &&
                     $isselected && trim($row->optionfeedback)) {
                $cell = new html_table_cell(
                        html_writer::tag('div',
                                $question->make_html_inline($question->format_text($row->optionfeedback,
                                                $row->optionfeedbackformat, $qa, 'qtype_mtf',
                                                'feedbacktext', $rowid)), array('class' => 'mtfspecificfeedback')));
                $rowdata[] = $cell;
            } else {
                $cell = new html_table_cell(html_writer::tag('div', ''));
                $rowdata[] = $cell;
            }
            $rowmo = new html_table_row($rowdata);
            $rowmo->attributes['data-id'] = '2';
            $rowmo->attributes['class'] = 'qtype_mtf_row';
            $table->data[] = $rowmo;
        }

        $result .= html_writer::table($table, true);

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($qa->get_last_qt_data()),
                    array('class' => 'validationerror'));
        }

        if (!empty(get_config('qtype_mtf')->showscoringmethod)) {
            $result .= $this->showscoringmethod($question);
        }

        return $result;
    }

    /**
     * Returns a string containing the rendererd question's scoring method.
     * Appends an info icon containing information about the scoring method.
     * @param qtype_mtf_question $question
     * @return string
     */
    private function showscoringmethod($question) {
        global $OUTPUT;

        $result = '';

        if (get_string_manager()->string_exists('scoring' . $question->scoringmethod, 'qtype_mtf')) {
            $outputscoringmethod = get_string('scoring' . $question->scoringmethod, 'qtype_mtf');
        } else {
            $outputscoringmethod = $question->scoringmethod;
        }

        if (get_string_manager()->string_exists('scoring' . $question->scoringmethod . '_help', 'qtype_mtf')) {
            $label = get_string('scoringmethod', 'qtype_mtf') . ': <b>' . ucfirst($outputscoringmethod) . '</b>';
            $result .= html_writer::tag('div',
                '<br>'. $label . $OUTPUT->help_icon('scoring' . $question->scoringmethod, 'qtype_mtf'),
                array('id' => 'scoringmethodinfo_q' . $question->id,
                    'label' => $label));
        }
        return $result;
    }

    /**
     * Returns the HTML representation of a radio button with the given attributes.
     *
     * @param unknown $name
     * @param unknown $value
     * @param unknown $checked
     * @param unknown $readonly
     *
     * @return string
     */
    protected static function radiobutton($name, $value, $checked, $readonly, $id = '', $datacol = '',
            $datamulti = '', $singleormulti = 2, $qtypemtfid = '') {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';
        $checked = $checked ? 'checked="checked"' : '';
        $result = '';

        if ($id == '') {
            $id = $name;
        }
        $result .= '<label><input type="radio" id="' . $id . '" name="' . $name . '" value="' . $value .
                 '" ' . $checked . ' ' . $readonly . ' ' . $datacol . ' ' . $datamulti . '/></label>';
        return $result;
    }

    /**
     * The prompt for the user to answer a question.
     *
     * @return Ambigous <string, lang_string, unknown, mixed>
     */
    protected function prompt() {
        return get_string('selectone', 'qtype_mtf');
    }

    /**
     * (non-PHPdoc).
     *
     * @see qtype_renderer::correct_response()
     */
    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $result = array();
        $response = '';
        $correctresponse = $question->get_correct_response(true);
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];

            if (isset($correctresponse[$rowid])) {
                if (isset($question->columns[$correctresponse[$rowid]])) {
                    $correctcolumn = $question->columns[$correctresponse[$rowid]];
                }
            } else {
                $correctcolumn = new stdClass();
                $correctcolumn->responsetextformat = 1;
                $correctcolumn->responsetext = get_string('false', 'qtype_mtf');
                $correctcolumn->id = $rowid;
            }

            $result[] = ' ' .
                     $question->make_html_inline(
                            $question->format_text($row->optiontext, $row->optiontextformat, $qa,
                                    'qtype_mtf', 'optiontext', $rowid)) . ': ' . $question->make_html_inline(
                            $question->format_text($correctcolumn->responsetext,
                                    $correctcolumn->responsetextformat, $qa, 'question', 'response',
                                    $correctcolumn->id));
        }
        if (!empty($result)) {
            $response = '<ul style="list-style-type: none;"><li>';
            $response .= implode('</li><li>', $result);
            $response .= '</li></ul>';
        }

        return $response;
    }

    protected function number_html($qnum) {
        return $qnum . '. ';
    }

    /**
     *
     * @param int $num The number, starting at 0.
     * @param string $style The style to render the number in. One of the
     *        options returned by {@link qtype_mtf:;get_numbering_styles()}.
     * @return string the number $num in the requested style.
     */
    protected function number_in_style($num, $style) {
        switch ($style) {
            case 'abc':
                $number = chr(ord('a') + $num);
                break;
            case 'ABCD':
                $number = chr(ord('A') + $num);
                break;
            case '123':
                $number = $num + 1;
                break;
            case 'iii':
                $number = question_utils::int_to_roman($num + 1);
                break;
            case 'IIII':
                $number = strtoupper(question_utils::int_to_roman($num + 1));
                break;
            case 'none':
                return '';
            default:
                return 'ERR';
        }
        return $this->number_html($number);
    }
}
