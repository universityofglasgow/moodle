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
 * Concrete implementation for mod_quiz.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

use cache;

/**
 * Implementation for a quiz activity.
 */
class quiz_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $quiz
     */
    private $quiz;

    /**
     * @var constant CACHE_KEY
     */
    const CACHE_KEY = 'studentid_quizduesoon:';

    /**
     * Constructor, set grade itemid.
     *
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        parent::__construct($gradeitemid, $courseid, $groupid);

        // Get the assignment object.
        $this->cm = \local_gugrades\users::get_cm_from_grade_item($gradeitemid, $courseid);
        $this->quiz = $this->get_quiz($gradeitemid, $this->cm);
    }

    /**
     * Local get quiz object method.
     * There is a get_quiz method on a quiz object also.
     *
     * @param int $gradeitemid
     * @param object $cm course module
     * @return object
     */
    private function get_quiz(int $gradeitemid, object $cm) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
        require_once($CFG->dirroot . '/mod/quiz/lib.php');
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        $course = $DB->get_record('course', ['id' => $this->courseid], '*', MUST_EXIST);
        $coursemodulecontext = \context_module::instance($cm->id);
        $gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $quizrecord = $DB->get_record('quiz', ['id' => $gradeitem->iteminstance], '*', MUST_EXIST);
        $quiz = new \quiz($quizrecord, $cm, $course, $coursemodulecontext);

        return $quiz;
    }

    /**
     * Return the grade directly from Gradebook or the quiz activity.
     * We also refer to the "Review Options" from the settings page here.
     * As part of MGU-631 "hidden" can either be 0, 1 OR in the case of a
     * quiz, it is the closing date and time.
     *
     * @param int $userid
     * @return mixed object|bool
     */
    public function get_grade(int $userid): object|bool {
        global $CFG, $DB, $USER;

        $quizinstance = $this->quiz->get_quiz();
        $gradeitemhiddenval = $this->gradeitem->hidden;
        // Has the gradeitem record been "hidden"
        if ($gradeitemhiddenval == 1) {
            return false;
        }

        // Is the grade only available once the quiz has closed.
        if ($gradeitemhiddenval > 1 && ($gradeitemhiddenval < $quizinstance->timeclose)) {
            return false;
        }

        $activitygrade = new \stdClass();
        $activitygrade->finalgrade = null;
        $activitygrade->rawgrade = null;
        $activitygrade->gradedate = null;
        $activitygrade->gradecolumn = false;
        $activitygrade->feedbackcolumn = false;

        // If the grade is overridden in the Gradebook then we can
        // revert to the base - i.e., get the grade from the Gradebook.
        // We're only wanting grades that are deemed as 'released', i.e.
        // not 'hidden' or 'locked'.
        if ($grade = $DB->get_record('grade_grades', ['itemid' => $this->gradeitemid, 'hidden' => 0, 'userid' => $userid])) {
            if ($grade->overridden) {
                return parent::get_first_grade($userid);
            }
        }

        // Before returning a grade - check to make sure there isn't a quiz in progress.
        // Depending on the setup of the quiz - more than one attempt could be allowed,
        // skewing our grade here.
        $unfinishedattempts = quiz_get_user_attempts($quizinstance->id, $USER->id, 'unfinished', true);
        if ($unfinishedattempts) {
            foreach ($unfinishedattempts as $unfinishedattempt) {
                if ($unfinishedattempt->state == 'inprogress' || $unfinishedattempt->state == 'overdue') {
                    return $activitygrade;
                }
            } 
        }

        // Quiz setup has a feature which controls the visibility of grades.
        // We need to check what settings have been made and if the student
        // is able to see any of the results from the quiz once completed.
        $finishedattempts = quiz_get_user_attempts($quizinstance->id, $USER->id, 'finished', true);

        if ($finishedattempts) {
            // Given that there can be 1 to multiple attempts for a given quiz, select
            // the grade based on the Grading Method from the quiz settings page.
            $finishedattempt = null;
            switch ($quizinstance->grademethod) {
                case QUIZ_ATTEMPTFIRST:
                    $finishedattempt = reset($finishedattempts);
                    break;
        
                case QUIZ_ATTEMPTLAST:
                case QUIZ_GRADEAVERAGE:
                    $finishedattempt = end($finishedattempts);
                    break;
        
                case QUIZ_GRADEHIGHEST:
                    $maxmark = 0;
                    foreach ($finishedattempts as $at) {
                        // Operator >=, since we want to most recent relevant attempt.
                        if ((float) $at->sumgrades >= $maxmark) {
                            $maxmark = $at->sumgrades;
                            $finishedattempt = $at;
                        }
                    }
                    break;
            }
            
            if ($finishedattempt->state == 'finished') {
                // Work out if we can display the grade, taking account what data is available in each attempt.
                require_once($CFG->dirroot . '/mod/quiz/locallib.php');
                list($someoptions, $alloptions) = quiz_get_combined_reviewoptions($quizinstance, $finishedattempt);
                $activitygrade->gradecolumn = $someoptions->marks >= \question_display_options::MARK_AND_MAX &&
                quiz_has_grades($quizinstance);
                $activitygrade->feedbackcolumn = quiz_has_feedback($quizinstance) && $alloptions->overallfeedback;

                // If the user is able to view the grade...
                if ($activitygrade->gradecolumn) {
                    // We've established earlier if the grade was overridden, no need to repeat that here.
                    if ($grade = $DB->get_record('grade_grades',
                    ['itemid' => $this->gradeitemid, 'hidden' => 0, 'userid' => $userid])) {

                        // We want access to other properties, hence the returns...
                        if ($grade->finalgrade != null && $grade->finalgrade >= 0) {
                            $activitygrade->finalgrade = $grade->finalgrade;
                            $activitygrade->gradedate = $grade->timemodified;
                            return $activitygrade;
                        }

                        if ($grade->rawgrade != null && $grade->rawgrade > 0) {
                            $activitygrade->rawgrade = $grade->rawgrade;
                            return $activitygrade;
                        }
                    }
                }

                return $activitygrade;
            }
        }

        // This is basically an object with pertinent field members set to null. 
        return $activitygrade;
    }

    /**
     * Return the Moodle URL to the item.
     *
     * @return string
     */
    public function get_assessmenturl(): string {
        return $this->get_itemurl() . $this->cm->id;
    }

    /**
     * Return the due date as the unix timestamp.
     *
     * @return int
     */
    public function get_rawduedate(): int {
        $dateinstance = $this->quiz->get_quiz();
        $rawdate = $dateinstance->timeclose;

        return $rawdate;
    }

    /**
     * Return a formatted date.
     *
     * @param int $unformatteddate
     * @return string
     */
    public function get_formattedduedate(int $unformatteddate = null): string {
        $quizinstance = $this->quiz->get_quiz();
        $rawdate = $quizinstance->timeclose;
        if ($unformatteddate) {
            $rawdate = $unformatteddate;
        }

        if ($rawdate > 0) {
            $dateobj = \DateTime::createFromFormat('U', $rawdate);
            $duedate = $dateobj->format('jS F Y');
        } else {
            $duedate = 'N/A';
        }

        return $duedate;
    }

    /**
     * Method to return the current status of the assessment item.
     *
     * @param int $userid
     * @return object
     */
    public function get_status(int $userid): object {

        global $DB;

        $statusobj = new \stdClass();
        $statusobj->assessment_url = $this->get_assessmenturl();
        $quizinstance = $this->quiz->get_quiz();
        $allowsubmissionsfromdate = $quizinstance->timeopen;
        $statusobj->grade_status = '';
        $statusobj->status_text = '';
        $statusobj->status_class = '';
        $statusobj->status_link = '';
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->due_date = $this->get_formattedduedate($quizinstance->timeclose);
        $statusobj->raw_due_date = $quizinstance->timeclose;
        $statusobj->gradecolumn = false;
        $statusobj->grade_class = false;
        $statusobj->feedbackcolumn = false;
        $statusobj->grade_date = '';
        $quizcloses = $quizinstance->timeclose;
        $attemptsallowed = $quizinstance->attempts;
        // This is measured in seconds.
        $graceperiod = $quizinstance->graceperiod;

        // Check if any individual overrides have been set up first of all...
        $overrides = $DB->get_record('quiz_overrides', ['quiz' => $quizinstance->id, 'userid' => $userid]);
        if (!empty($overrides)) {
            // If any of these fields are NULL, the override is using the default activity settings.
            if ($overrides->timeopen != null) {
                $allowsubmissionsfromdate = $overrides->timeopen;
            }
            if ($overrides->timeclose != null) {
                $statusobj->due_date = $this->get_formattedduedate($overrides->timeclose);
                $statusobj->raw_due_date = $overrides->timeclose;
                $quizcloses = $overrides->timeclose;
            }
            if ($overrides->attempts != null) {
                $attemptsallowed = $overrides->attempts;
            }
        }

        // We also need to check if any group overrides exist for this quiz.
        $lastmonth = mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y'));
        $now = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
        $groupselect = 'quiz = :quiz AND groupid IS NOT NULL AND userid IS NULL AND timeopen BETWEEN :lastmonth AND :now AND
        timeclose > :tnow';
        $groupparams = ['quiz' => $quizinstance->id, 'lastmonth' => $lastmonth, 'now' => $now, 'tnow' => $now];
        $groupoverrides = $DB->get_records_select('quiz_overrides', $groupselect, $groupparams, '',
        'groupid, timeopen, timeclose');
        if (!empty($groupoverrides)) {
            foreach ($groupoverrides as $groupoverride) {
                // An override for this quiz exists - is our user a member of the group?
                if ($groupmembers = $DB->record_exists('groups_members', ['groupid' => $groupoverride->groupid,
                    'userid' => $userid])) {
                    // If any of these fields are NULL, the override is using the default activity settings.
                    if ($groupoverride->timeopen != null) {
                        $allowsubmissionsfromdate = $groupoverride->timeopen;
                    }
                    if ($groupoverride->timeclose != null) {
                        $statusobj->due_date = $this->get_formattedduedate($groupoverride->timeclose);
                        $statusobj->raw_due_date = $groupoverride->timeclose;
                        $quizcloses = $groupoverride->timeclose;
                    }
                    if ($groupoverride->attempts != null) {
                        $attemptsallowed = $groupoverride->attempts;
                    }
                }
            }
        }

        // To begin with - check if the quiz is open.
        if ($allowsubmissionsfromdate > $now) {
            $statusobj->grade_status = get_string('status_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        }

        if ($statusobj->grade_status == '') {
            // Start by saying the student can submit this quiz.
            $statusobj->grade_status = get_string('status_submit', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submit', 'block_newgu_spdetails');
            $statusobj->status_class = get_string('status_class_submit', 'block_newgu_spdetails');
            $statusobj->status_link = $statusobj->assessment_url;
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');

            $unfinishedattempts = quiz_get_user_attempts($quizinstance->id, $userid, 'unfinished', true);

            if ($unfinishedattempts) {
                if ($quizcloses != 0 && ($quizcloses + $graceperiod) > $now) {
                    foreach ($unfinishedattempts as $unfinishedattempt) {
                        // With this activity still in progress, we should class it as still submissible.
                        if ($unfinishedattempt->state == 'inprogress') {
                            return $statusobj;
                        }
                        // With this activity overdue, we should class it as still submissible.
                        if ($unfinishedattempt->state == 'overdue') {
                            $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                            $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                            $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                            return $statusobj;
                        }
                    }

                    if ($now > ($quizcloses + $graceperiod)) {
                        $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
                        $statusobj->status_class = '';
                        $statusobj->status_link = '';
                        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                        $statusobj->grade_class = false;
                        return $statusobj;
                    }
                }
                if ($quizcloses != 0 && $now > ($quizcloses + $graceperiod)) {
                    $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
                    $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
                    $statusobj->status_class = '';
                    $statusobj->status_link = '';
                    $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                    $statusobj->grade_class = false;
                    return $statusobj;
                }
            }

            $finishedattempts = quiz_get_user_attempts($quizinstance->id, $userid, 'finished', true);

            if ($finishedattempts) {
                // Given that there can be 1 to multiple attempts for a given quiz, pick off the last one
                // here to see whether it's been abandoned or has since received a grade if its finished.
                $finishedattempt = array_pop($finishedattempts);
                
                if ($finishedattempt->state == 'abandoned') {
                    if ($attemptsallowed > 0 && ($finishedattempt->attempt >= $attemptsallowed)) {
                        $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
                        $statusobj->status_class = '';
                        $statusobj->status_link = '';
                        return $statusobj;
                    }
                }
                if ($finishedattempt->state == 'finished') {
                    
                    $finishedattempt = null;
                    switch ($quizinstance->grademethod) {
                        case QUIZ_ATTEMPTFIRST:
                            $finishedattempt = reset($finishedattempts);
                            break;
                
                        case QUIZ_ATTEMPTLAST:
                        case QUIZ_GRADEAVERAGE:
                            $finishedattempt = end($finishedattempts);
                            break;
                
                        case QUIZ_GRADEHIGHEST:
                            $maxmark = 0;
                            foreach ($finishedattempts as $at) {
                                // Operator >=, since we want to most recent relevant attempt.
                                if ((float) $at->sumgrades >= $maxmark) {
                                    $maxmark = $at->sumgrades;
                                    $finishedattempt = $at;
                                }
                            }
                            break;
                    }
                    
                    // Quiz setup has a feature which controls the visibility of grades.
                    // We need to check this here also.
                    // Work out if we can display the grade, taking account what data is available in each attempt.
                    list($someoptions, $alloptions) = quiz_get_combined_reviewoptions($quizinstance, $finishedattempt);
                    $statusobj->gradecolumn = $someoptions->marks >= \question_display_options::MARK_AND_MAX &&
                    quiz_has_grades($quizinstance);
                    $statusobj->feedbackcolumn = quiz_has_feedback($quizinstance) && $alloptions->overallfeedback;

                    $statusobj->grade_status = get_string('status_submitted', 'block_newgu_spdetails');
                    $statusobj->status_text = get_string('status_text_submitted', 'block_newgu_spdetails');
                    $statusobj->status_class = get_string('status_class_submitted', 'block_newgu_spdetails');

                    // There ^should^ be just one record. Using IGNORE_MISSING now as it's possible
                    // that a record may not exist - if the quiz has been set up not to autosubmit for example.
                    $quizgrade = $DB->get_record('quiz_grades', ['quiz' => $quizinstance->id, 'userid' => $userid], '*',
                    IGNORE_MISSING);
                    if ($quizgrade) {
                        $statusobj->grade_status = get_string('status_graded', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_graded', 'block_newgu_spdetails');
                        $statusobj->status_class = get_string('status_class_graded', 'block_newgu_spdetails');
                        $statusobj->status_link = '';
                        // If the user is able to view the grade...
                        if ($statusobj->gradecolumn) {
                            $statusobj->grade_class = true;
                            $statusobj->grade_to_display = $quizgrade->grade;
                        }

                        $statusobj->grade_date = $quizgrade->timemodified;

                        if ($statusobj->feedbackcolumn) {
                            $statusobj->feedbackcolumn = true;
                        }
                    }
                    return $statusobj;
                }
            }

            // If no finished or unfinished attempts were found, for a final check, lets see if this quiz is still available.
            if ($now > ($quizcloses + $graceperiod)) {
                $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
                $statusobj->status_class = '';
                $statusobj->status_link = '';
                $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                $statusobj->grade_class = false;
            }
        }

        return $statusobj;
    }

    /**
     * Return an array of quizzes that haven't exceeded any closing date,
     * time limit, grace period or attempts.
     *
     * This data feeds the charts for "Assessments due in the next...".
     * For a quiz, they can be set up with optional open/closing dates along
     * with optional time limits for a quiz, and a grace period before the
     * final submission is made. Along with individual and group overrides,
     * these permutations make up all the ways a quiz can be set up. We also
     * need to take into consideration the state of a quiz once it has begun,
     * if has been abandoned and once it has reached a 'finished' state.
     *
     * @return array
     */
    public function get_assessmentsdue(): array {
        global $USER, $DB;

        // Cache this query as it's going to get called for each assessment in the course otherwise.
        $cache = cache::make('block_newgu_spdetails', 'quizduequery');
        $now = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
        $lastmonth = mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y'));
        $currenttime = time();
        $fiveminutes = $currenttime - 300;
        $cachekey = self::CACHE_KEY . $USER->id;
        $cachedata = $cache->get_many([$cachekey]);
        $quizdata = [];

        // Find any quiz attempts that have been started but not finished.
        if (!$cachedata[$cachekey] || $cachedata[$cachekey][0]['updated'] < $fiveminutes) {
            $select = 'userid = :userid AND timestart BETWEEN :lastmonth AND :now AND state IN (:inprogress, :overdue)';
            $params = ['userid' => $USER->id, 'lastmonth' => $lastmonth, 'now' => $now, 'inprogress' => 'inprogress',
            'overdue' => 'overdue'];
            $quizattempts = $DB->get_records_select('quiz_attempts', $select, $params, '', 'quiz, state, attempt, timecheckstate');

            $submissionsdata = [
                'updated' => time(),
                'quizattempts' => $quizattempts,
            ];

            $cachedata = [
                $cachekey => [
                    $submissionsdata,
                ],
            ];
            $cache->set_many($cachedata);
        } else {
            $cachedata = $cache->get_many([$cachekey]);
            $quizattempts = $cachedata[$cachekey][0]['quizattempts'];
        }

        // We are calling the quiz object's get_quiz method here, not our local method.
        $quizobj = $this->quiz->get_quiz();

        // Begin by using the main quiz settings
        $quizopens = $quizobj->timeopen;
        $quizcloses = $quizobj->timeclose;
        $attemptsallowed = $quizobj->attempts;
        // This is measured in seconds. If set, we add it to the 'due date' value
        $graceperiod = $quizobj->graceperiod;

        // Check if any individual overrides have been set up for this user.
        $overrides = $DB->get_record('quiz_overrides', ['quiz' => $quizobj->id, 'userid' => $USER->id]);
        if (!empty($overrides)) {
            // If any of these fields are NULL, the override is using the default activity settings.
            if ($overrides->timeopen != null) {
                $quizopens = $overrides->timeopen;
            }
            if ($overrides->timeclose != null) {
                $quizcloses = $overrides->timeclose;
            }
            if ($overrides->attempts != null) {
                $attemptsallowed = $overrides->attempts;
            }
        }

        // Check if any group overrides exist for this quiz.
        $groupselect = 'quiz = :quiz AND groupid IS NOT NULL AND userid IS NULL AND timeopen BETWEEN :lastmonth AND :now AND
        timeclose > :tnow';
        $groupparams = ['quiz' => $quizobj->id, 'lastmonth' => $lastmonth, 'now' => $now, 'tnow' => $now];
        $groupoverrides = $DB->get_records_select('quiz_overrides', $groupselect, $groupparams, '',
        'groupid, timeopen, timeclose');
        if (!empty($groupoverrides)) {
            foreach ($groupoverrides as $groupoverride) {
                // An override for this quiz exists - is our user a member of the group?
                if ($groupmembers = $DB->record_exists('groups_members', ['groupid' => $groupoverride->groupid,
                    'userid' => $USER->id])) {
                    // If any of these fields are NULL, the override is using the default activity settings.
                    if ($groupoverride->timeopen != null) {
                        $quizopens = $groupoverride->timeopen;
                    }
                    if ($groupoverride->timeclose != null) {
                        $quizcloses = $groupoverride->timeclose;
                    }
                    if ($groupoverride->attempts != null) {
                        $attemptsallowed = $groupoverride->attempts;
                    }
                }
            }
        }

        // Check if an attempt for this quiz has been finished or abandoned on the first go instead.
        if (!array_key_exists($quizobj->id, $quizattempts)) {
            $finishedattempts = quiz_get_user_attempts($quizobj->id, $USER->id, 'finished', true);
            if ($finishedattempts) {
                foreach ($finishedattempts as $finishedattempt) {
                    if ($finishedattempt->state == 'finished') {
                        return $quizdata;
                    }
                    if ($finishedattempt->state == 'abandoned') {
                        $quizgrades = $DB->get_record('quiz_grades', ['quiz' => $quizobj->id, 'userid' => $USER->id]);
                        // If we ^do^ find a grade at this point, it would suggest that there is an
                        // attempt that was 'abandoned' but graded automatically, therefore is no longer due.
                        if (!empty($quizgrades)) {
                            return $quizdata;
                        }
                        // However, if a quiz has multiple attempts, all of which have been abandoned, a grade entry may
                        // not get created if, for example, the quiz page is exited during the attempt, i.e. browser crash,
                        // navigating away from the quiz for whatever reason. Moodle's cron script only seems to update the
                        // quiz_attempts table in this situation.
                        if (empty($quizgrades) && ($finishedattempt->attempt >= $attemptsallowed)) {
                            return $quizdata;
                        }
                    }
                }
            }

            // So, either no attempts have been made, no finished attempts have been found, or we have abandoned attempts
            // that have been graded already. With nothing found initially - check if the activity is still considered due.
            if ($quizcloses != 0 && ($quizcloses + $graceperiod) > $now) {
                $obj = new \stdClass();
                $obj->name = $quizobj->name;
                $obj->duedate = $quizcloses + $graceperiod;
                $quizdata[] = $obj;
            }
        }

        // Now deal with if an attempt has been made - what state is that attempt in.
        if ((array_key_exists($quizobj->id, $quizattempts) && (is_object($quizattempts[$quizobj->id]) &&
        property_exists($quizattempts[$quizobj->id], 'state')))) {
            $obj = new \stdClass();
            $obj->name = $quizobj->name;

            if ($quizattempts[$quizobj->id]->state == 'inprogress') {
                $obj->duedate = $quizcloses + $graceperiod;
                $quizdata[] = $obj;
                return $quizdata;
            }

            if ($quizattempts[$quizobj->id]->state == 'overdue' || $quizattempts[$quizobj->id]->state == 'abandoned') {
                // Open and closing dates, time limits, grace period, overrides in quizzes are all optional.
                // In lieu of anything formal to say how this should function, I'm going with a quiz must
                // have an opening and closing date to be considered for something that could be included
                // in the "Assements due in the next..." charts.
                if (($quizopens != 0 && $quizopens < $now) && ($quizcloses != 0 && $quizcloses + $graceperiod > $now)) {
                    $obj->duedate = $quizcloses + $graceperiod;
                    // Lets check this only for quizzes that have begun or are in an overdue state
                    if (is_object($quizattempts[$quizobj->id]) && 
                        ($quizattempts[$quizobj->id]->state == 'inprogress' || 
                        $quizattempts[$quizobj->id]->state == 'overdue') &&
                        property_exists($quizattempts[$quizobj->id], 'timecheckstate') &&
                        $quizattempts[$quizobj->id]->timecheckstate != 0) {
                        // A quiz can also have a time limit set.
                        if ($quizattempts[$quizobj->id]->timecheckstate > $now) {
                            $obj->duedate = $quizattempts[$quizobj->id]->timecheckstate;
                        }
                    } 

                    if ($quizattempts[$quizobj->id]->state == 'abandoned') {
                        // A quiz can have 1 to unlimited attempts. Quizzes with only 1 attempt
                        // recorded, that end up as 'abandoned' here, are meant to be automatically 
                        // graded. Quizzes with 2 or more 'abandoned' attempts, won't get this 
                        // entry, but won't allow the student to submit any more attempts - and
                        // should therefore no longer be considered as something that is due. 
                        $quizgrades = $DB->get_record('quiz_grades', ['quiz' => $quizobj->id, 'userid' => $USER->id]);
                        // If we ^do^ find a grade at this point, it would suggest that there is an
                        // attempt that has 'finished' and wasn't picked up by the earlier search.
                        if (empty($quizgrades)) {
                            if ($quizattempts[$quizobj->id]->attempt < $attemptsallowed) {
                                $obj->duedate = $quizcloses + $graceperiod;
                            }
                            if ($quizattempts[$quizobj->id]->attempt >= $attemptsallowed) {
                                unset($obj->duedate);
                            }
                        } else {
                            return $quizdata;
                        }
                    }
                    $quizdata[] = $obj;
                    return $quizdata;
                }
            }
        }

        return $quizdata;
    }

}
