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
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Renders music theory questions.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_musictheory_renderer extends qtype_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        // Overridden in each music theory question subtype renderer class.
    }

    public function correct_response(question_attempt $qa) {
        // Overridden in each music theory question subtype renderer class.
    }

    public function feedback(question_attempt $qa, question_display_options $options) {
        $output = '';
        $hint = null;

        if ($options->feedback) {
            $output .= html_writer::nonempty_tag('div', $this->specific_feedback($qa), array('class' => 'specificfeedback'));
            $hint = $qa->get_applicable_hint();
        }

        if ($options->numpartscorrect) {
            $output .= html_writer::nonempty_tag('div', $this->num_parts_correct($qa), array('class' => 'numpartscorrect'));
        }

        if ($hint) {
            $output .= $this->hint($qa, $hint);
        }

        if ($options->generalfeedback) {
            $output .= html_writer::nonempty_tag('div', $this->general_feedback($qa), array('class' => 'generalfeedback'));
        }

        // Adds a div that can be replaced by the music UI.
        if ($options->rightanswer) {
            $question = $qa->get_question();
            $expecteddata = $question->get_expected_data();
            $response = array();
            foreach ($expecteddata as $key => $fieldtype) {
                $response[$key] = $qa->get_last_qt_var($key);
            }
            $grade = $question->grade_response($response);
            $fraction = intval(round($grade[0], 3) * 1000);
            if ($fraction < 1000) {
                $rightanswerdivid = 'musictheory_correctanswerdiv_' .
                        $qa->get_qt_field_name($key);
                $rightanswerattributes = array(
                    'class' => 'rightanswer',
                    'name'  => $rightanswerdivid,
                    'id'    => $rightanswerdivid,
                );
                $output .= html_writer::nonempty_tag('div', $this->correct_response($qa), $rightanswerattributes);
            }
        }

        return $output;
    }

}
