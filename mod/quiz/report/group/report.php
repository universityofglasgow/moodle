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
 * This file defines the quiz group.
 *
 * @package   quiz_group
 * @copyright 2017 Camille Tardy, University of Geneva
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/group/groupsettings_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/group/groupdispatchgrade_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/group/locallib.php');


/**
 * Quiz group to enable group evaluation for Quiz.
 *
 *
 *
 * @copyright 2017 Camille Tardy, University of Geneva
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_group_report extends quiz_default_report {

    protected $viewoptions = array();
    protected $questions;
    protected $cm;
    protected $quiz;
    protected $course;

    /**
     * @param $cm the course-module for this quiz.
     * @param $course the coures we are in.
     * @param $quiz this quiz.
     */
    public function display($quiz, $cm, $course) {
        global $DB;

        $this->quiz = $quiz;
        $this->cm = $cm;
        $this->course = $course;

        $pageoptions = array();
        $pageoptions['id'] = $cm->id;
        $pageoptions['quizid'] = $quiz->id;

        // Retrieve current grouping value for the given quizid return false if not found.
        $groupingrecord = $DB->get_record('quiz_group', array('quizid' => $quiz->id), 'id, groupingid', 'IGNORE_MISSING');

        $groupingid = 0;
        // If grouping id exist use the ID else set to 0 (-> no grouping selected).
        if ($groupingrecord !== false) {
            $groupingid = $groupingrecord->groupingid;
        }

        $boolhasattempts = quiz_has_attempts($quiz->id);

         // Params for both Forms.
        $formparams = array('quizid' => $quiz->id, 'id' => $cm->id,  'hasattempts' => $boolhasattempts, 'mode' => 'group');
        $toform = array("sel_groupingid" => $groupingid);

        // Create quiz group setting form.
        $mform = new quiz_group_settings_form(null, $formparams, 'get');

        // If cancel do nothing.
        if ($mform->is_cancelled()) {
            // Return to view quiz page.
            redirect(new moodle_url('/mod/quiz/view.php', array('id' => $cm->id)), get_string('canceledit', 'quiz_group'));

            // If edited get edited info.
        } else if ($fromform = $mform->get_data()) {
            // Should retrieve sel_groupingid value here.
            $groupingidupdated = $fromform->sel_groupingid;

            if ($groupingrecord == false) {
                // No existing record, create one.
                $record = new stdClass();
                $record->groupingid = $groupingidupdated;
                $record->quizid = $quiz->id;

                $DB->insert_record('quiz_group', $record, false);

            } else {
                // Existing record, update it.
                $groupingobj = array('id' => $groupingrecord->id , 'groupingid' => $groupingidupdated);

                $DB->update_record('quiz_group', $groupingobj, $bulk = false);
            }

            $finalgroupingname = get_string('no_group_string', 'quiz_group');
            if ($groupingidupdated > 0) {
                $dbgrouping = $DB->get_record('groupings', array('id' => $groupingidupdated), 'name', 'IGNORE_MISSING');
                $finalgroupingname = $dbgrouping->name;
            }

            // Return to view quiz page with validation message.
            redirect(
                new moodle_url('/mod/quiz/view.php', array('id' => $cm->id)),
                get_string('settings_edited', 'quiz_group', $finalgroupingname)
            );
        }

        $mform->set_data($toform);
        $this->print_header_and_tabs($cm, $course, $quiz, 'editquizsettings');
        $mform->display();

        // Create Dispatch grades to other group members button.

        $pageoptions['mode'] = "group";

        $formdispatch = new quiz_group_dispatchgrade_form(null, $formparams);

        if ($fromformdispatch = $formdispatch->get_data()) {
            dispatch_grade($quiz, $groupingid);
        }

        $formdispatch->set_data($toform);
        $formdispatch->display();

    }

}
