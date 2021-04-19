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
require_once($CFG->dirroot . '/question/type/mtf/grading/qtype_mtf_grading.class.php');

class qtype_mtf_grading_mtfonezero extends qtype_mtf_grading {

    const TYPE = 'mtfonezero';

    public function get_name() {
        return self::TYPE;
    }

    public function get_title() {
        return get_string('scoring' . self::TYPE, 'qtype_mtf');
    }

    /**
     * Returns the question's grade.
     *
     * (non-PHPdoc)
     *
     * @see qtype_mtf_grading::grade_question()
     */
    public function grade_question($question, $answers) {
        $correctrows = 0;
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];
            $grade = $this->grade_row($question, $key, $row, $answers);
            if ($grade > 0) {
                ++$correctrows;
            }
        }
        // Mtf1/0: if all responses are correct => all points, else 0 points.
        // I.e. points = if (correct_responses == num_options) then max_points else 0.

        if ($correctrows == $question->numberofrows) {
            return 1;
        } else {
            return 0;
        }
    }
}
