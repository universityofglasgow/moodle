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
 * Version information
 *
 * @package    qtype
 * @subpackage multinumerical
 * @copyright  2013 Université de Lausanne
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/multinumerical/question.php');


class qtype_multinumerical extends question_type {

    public function questionid_column_name() {
        return 'question';
    }

    public function extra_question_fields() {
        return array('question_multinumerical', 'parameters', 'conditions', 'feedbackperconditions', 'binarygrade', 'displaycalc', 'usecolorforfeedback');
    }

    public function save_question_options($question) {
        global $DB;
        $result = new stdClass();

        $context = $question->context;

        $question->feedbackperconditions = rtrim(str_replace("\r\n", "\n", $question->feedbackperconditions), "\n");
        $question->conditions = rtrim(str_replace("\r\n", "\n", $question->conditions), "\n");

        $parentresult = parent::save_question_options($question);
        if ($parentresult !== null) {
            // Parent function returns null if all is OK
            return $parentresult;
        }

        $this->save_hints($question);

        return $parentresult;
    }

}
