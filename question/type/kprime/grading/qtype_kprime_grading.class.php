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
 * qtype_kprime question grading class.
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

/**
 * Provides grading functionality
 *
 * @package     qtype_kprime
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_kprime_grading {

    /**
     * Returns the scoringmethod name.
     * @return string
     */
    abstract public function get_name();

    /**
     * Returns the gradingmethod title..
     * @return string
     */
    abstract public function get_title();

    /**
     * Returns the question's grade for a given response..
     * @param qtype_kprime_question $question
     * @param array $answers
     * @return int
     */
    abstract public function grade_question($question, $answers);

    /**
     * Grade a specific row.
     * This is the same for all grading methods.
     * Either the student chose the correct response or not (single choice).
     * @param qtype_kprime_question $question The question object.
     * @param unknown $key The field key of the row.
     * @param object $row The row object.
     * @param array $answers The answers array.
     * @return float
     */
    public function grade_row(qtype_kprime_question $question, $key, $row, $answers) {
        $rowcorrect = true;
        if (!$question->is_answered($answers, $key)) {
            return 0;
        }
        $field = $question->field($key);
        $answercolumn = $answers[$field];
        if ($question->is_correct($row, $answercolumn)) {
            return 1;
        } else {
            return 0;
        }
    }
}
