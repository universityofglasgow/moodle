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
 * Concrete implementation for mod_assign
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Howard Miller/Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

use cache;

/**
 * Specific implementation for assignment activity.
 */
class assign_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $assign
     */
    private $assign;

    /**
     * @var constant CACHE_KEY
     */
    const CACHE_KEY = 'studentid_assessmentsduesoon:';

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
        $this->assign = $this->get_assign($this->cm);
    }

    /**
     * Get assignment object.
     *
     * @param object $cm course module
     * @return object
     */
    public function get_assign($cm): object {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        $course = $DB->get_record('course', ['id' => $this->courseid], '*', MUST_EXIST);
        $coursemodulecontext = \context_module::instance($cm->id);
        $assign = new \assign($coursemodulecontext, $cm, $course);

        return $assign;
    }

    /**
     * Return the grade either from the assignment or
     * directly from Gradebook otherwise.
     *
     * @param int $userid
     * @return mixed object|bool
     */
    public function get_grade(int $userid): object|bool {
        global $DB;

        $activitygrade = new \stdClass();
        $activitygrade->finalgrade = null;
        $activitygrade->rawgrade = null;
        $activitygrade->grade = null;
        $activitygrade->gradedate = null;

        // If the grade is overridden in the Gradebook then we can
        // revert to the base - i.e., get the grade from the Gradebook.
        // We're only wanting grades that are deemed as 'released', i.e.
        // not 'hidden'.
        if ($grade = $DB->get_record('grade_grades', ['itemid' => $this->gradeitemid, 'hidden' => 0, 'userid' => $userid])) {
            if ($grade->overridden) {
                return parent::get_first_grade($userid);
            }

            // We want access to other properties, hence the returns...
            if ($grade->finalgrade != null && $grade->finalgrade > 0) {
                $activitygrade->finalgrade = $grade->finalgrade;
                $activitygrade->gradedate = $grade->timemodified;
                return $activitygrade;
            }

            if ($grade->rawgrade != null && $grade->rawgrade > 0) {
                $activitygrade->rawgrade = $grade->rawgrade;
                return $activitygrade;
            }
        }

        // This just pulls the grade from assign. Not sure it's that simple False, means do not create grade if it does not exist.
        // This is the grade object from mdl_assign_grades (check negative values).
        // Added the last parameter as w/o it, a mdl_assign_submission entry is created - a side effect I don't think we want here.
        $assigngrade = $this->assign->get_user_grade($userid, false, 0);

        if ($assigngrade !== false) {
            $activitygrade->grade = $assigngrade->grade;
            $activitygrade->gradedate = $assigngrade->timemodified;
            return $activitygrade;
        }

        return false;
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
        $dateinstance = $this->assign->get_instance();
        $rawdate = $dateinstance->duedate;

        return $rawdate;
    }

    /**
     * Return a formatted date.
     *
     * @param int $unformatteddate
     * @return string
     */
    public function get_formattedduedate(int $unformatteddate = null): string {
        $dateinstance = $this->assign->get_instance();
        $rawdate = $dateinstance->duedate;
        if ($unformatteddate) {
            $rawdate = $unformatteddate;
        }

        if ($rawdate > 0) {
             $duedate = userdate($rawdate, get_string('strftimedate', 'core_langconfig'));
        } else {
            $duedate = 'N/A';
        }

        return $duedate;
    }

    /**
     * Method to return the current status of the assessment item.
     *
     * With regards dates - a date value of 0 in the settings page indicates
     * there is no exclusion - e.g. an assignment is open for submission anytime.
     * For overrides however, NULL values signal that the main activity settings
     * should be used instead.
     *
     * @param int $userid
     * @return object
     */
    public function get_status(int $userid): object {

        global $DB;

        $statusobj = new \stdClass();
        $statusobj->assessment_url = $this->get_assessmenturl();
        $assigninstance = $this->assign->get_instance();
        $allowsubmissionsfromdate = $assigninstance->allowsubmissionsfromdate;
        $statusobj->grade_status = '';
        $statusobj->status_text = '';
        $statusobj->status_class = '';
        $statusobj->status_link = '';
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->grade_class = false;
        $statusobj->due_date = $assigninstance->duedate;
        $statusobj->raw_due_date = $assigninstance->duedate;
        $statusobj->cutoff_date = $assigninstance->cutoffdate;
        $statusobj->markingworkflow = $assigninstance->markingworkflow;
        $statusobj->grade_date = '';

        // We first check if any group overrides have been created for this assignment.
        $groupselect = 'assignid = :assignid AND groupid IS NOT NULL AND userid IS NULL';
        $groupparams = ['assignid' => $assigninstance->id];
        $groupoverrides = $DB->get_records_select('assign_overrides', $groupselect, $groupparams, '',
        'groupid, allowsubmissionsfromdate, duedate, cutoffdate');
        if (!empty($groupoverrides)) {
            foreach ($groupoverrides as $groupoverride) {
                // An override for this assignment exists - is our user a member of the group?
                if ($groupmembers = $DB->record_exists('groups_members', ['groupid' => $groupoverride->groupid,
                    'userid' => $userid])) {
                    // If any of these fields are NULL, the override is using the default activity settings.
                    if ($groupoverride->allowsubmissionsfromdate != null) {
                        $allowsubmissionsfromdate = $groupoverride->allowsubmissionsfromdate;
                    }
                    if ($groupoverride->duedate != null) {
                        $statusobj->due_date = $groupoverride->duedate;
                        $statusobj->raw_due_date = $groupoverride->duedate;
                    }
                    if ($groupoverride->cutoffdate != null) {
                        $statusobj->cutoff_date = $groupoverride->cutoffdate;
                    }
                }
            }
        }

        // Individual overrides however, take precedence - based on how Moodle does things.
        $overrides = $DB->get_record('assign_overrides', ['assignid' => $assigninstance->id, 'userid' => $userid]);
        if (!empty($overrides)) {
            // If any of these fields are NULL, the override is using the default activity settings.
            if ($overrides->allowsubmissionsfromdate != null) {
                $allowsubmissionsfromdate = $overrides->allowsubmissionsfromdate;
            }

            if ($overrides->duedate != null) {
                $statusobj->due_date = $overrides->duedate;
                $statusobj->raw_due_date = $overrides->duedate;
            }

            if ($overrides->cutoffdate != null) {
                $statusobj->cutoff_date = $overrides->cutoffdate;
            }
        }

        $now = usertime(time());
        // Easy one first. The "Allow submissions from..." date has been set and is in the future.
        if ($allowsubmissionsfromdate != 0 && ($allowsubmissionsfromdate > $now)) {
            $statusobj->grade_status = get_string('status_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        }

        // Check if this assessment requires any submissions.
        if ($assigninstance->nosubmissions == 1) {
            $statusobj->grade_status = get_string('status_unavailable', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submissionunavailable', 'block_newgu_spdetails');
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        }

        // If our grade_status hasn't changed at this point, continue on.
        if ($statusobj->grade_status == '') {

            // This table is used for extensions to the due date. But it also contain entries for when
            // Marking Workflow is enabled - but these only appear when marking has begun however.
            // Point of interest - extension due date trumps the settings/override "cut-off date".
            // It makes sense therefore to make $statusobj's cut-off date at this point, the same as the
            // extension due date, in order to avoid some messy code later on.
            $userflags = $DB->get_record('assign_user_flags', ['assignment' => $assigninstance->id, 'userid' => $userid]);
            if (!empty($userflags)) {
                if ($userflags->extensionduedate > 0) {
                    $statusobj->due_date = $userflags->extensionduedate;
                    $statusobj->raw_due_date = $userflags->extensionduedate;
                    $statusobj->cutoff_date = $userflags->extensionduedate;
                } else {
                    $workflowstate = $userflags->workflowstate;
                }
            }

            $assignsubmission = $DB->get_record('assign_submission', ['assignment' => $assigninstance->id, 'userid' => $userid]);

            // Begin with the easy step. If the student has not made a submission yet.
            if (empty($assignsubmission)) {
                $this->set_displaystate($statusobj);
            } else {
                $statusobj->grade_status = $assignsubmission->status;

                // There is a bug in class assign->get_user_grade() where get_user_submission() is called
                // and an assignment entry is created regardless -i.e. "true" is passed instead of an arg.
                // This will always result in a mdl_assign_submission entry with a status of "new" created.
                // We also have to cater for status 'draft' here as essay 'submissions' begin life in that state.
                if ($statusobj->grade_status == get_string('status_new', 'block_newgu_spdetails') ||
                    $statusobj->grade_status == get_string('status_draft', 'block_newgu_spdetails')) {
                    $this->set_displaystate($statusobj);
                }

                if ($statusobj->grade_status == get_string('status_submitted', 'block_newgu_spdetails')) {
                    $statusobj->status_text = get_string('status_text_submitted', 'block_newgu_spdetails');
                    $statusobj->status_class = get_string('status_class_submitted', 'block_newgu_spdetails');
                    $statusobj->status_link = '';

                    // If Marking Workflow has been enabled.
                    if ($assigninstance->markingworkflow) {
                        $gtd = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');

                        // And marking has begun, what stage are we at.
                        if ($workflowstate) {
                            switch($workflowstate) {
                                case "notmarked":
                                    $gtd = get_string('notmarked', 'block_newgu_spdetails');
                                    break;

                                case "inmarking":
                                    $gtd = get_string('inmarking', 'block_newgu_spdetails');
                                    break;

                                case "inreview":
                                    $gtd = get_string('inreview', 'block_newgu_spdetails');
                                    break;

                                case "readyforreview":
                                    $gtd = get_string('readyforreview', 'block_newgu_spdetails');
                                    break;

                                case "readyforrelease":
                                    $gtd = get_string('readyforrelease', 'block_newgu_spdetails');
                                    break;

                                case "released":
                                    $gtd = get_string('released', 'block_newgu_spdetails');
                                    $statusobj->workflowstate = $workflowstate;
                                    break;
                            }
                        }
                        $statusobj->grade_to_display = $gtd;
                    }
                }

            }
        }

        // Formatting this here as the integer format for the date is no longer needed for testing against.
        if ($statusobj->due_date != 0) {
            $statusobj->due_date = $this->get_formattedduedate($statusobj->due_date);
            $statusobj->raw_due_date = $this->get_rawduedate();
        } else {
            $statusobj->due_date = 'N/A';
            $statusobj->raw_due_date = 0;
        }

        return $statusobj;
    }

    /**
     * This method takes the $statusobj object and sets the display values for the grade status.
     *
     * @param object $statusobj
     * @return object
     */
    private function set_displaystate(object $statusobj): object {

        // Start by saying the student is still able to make a submission.
        $statusobj->grade_status = get_string('status_submit', 'block_newgu_spdetails');
        $statusobj->status_text = get_string('status_text_submit', 'block_newgu_spdetails');
        $statusobj->status_class = get_string('status_class_submit', 'block_newgu_spdetails');
        $statusobj->status_link = $statusobj->assessment_url;
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $now = usertime(time());
        // Cut-off date is the more 'finite' state - exceed this and you're not allowed to submit at all.
        if ($statusobj->cutoff_date > 0) {
            // The student can still submit if they have exceeded the due date at this point.
            if ($statusobj->due_date != 0 && $now > $statusobj->due_date) {
                $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                $statusobj->status_link = $statusobj->assessment_url;
            }
            // If the student has exceeded the cut-off date then we can no longer submit anything.
            if ($now > $statusobj->cutoff_date) {
                $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
                $statusobj->status_link = '';
            }
        } else {
            // The student can still submit if they have exceeded only the due date at this point.
            if ($statusobj->due_date != 0 && $now > $statusobj->due_date) {
                $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                $statusobj->status_link = $statusobj->assessment_url;
            }
        }

        return $statusobj;
    }

    /**
     * Return the due date of the assignment if it hasn't been submitted.
     *
     * @return array
     */
    public function get_assessmentsdue(): array {
        global $USER, $DB;

        // Cache this query as it's going to get called for each assessment in the course otherwise.
        $cache = cache::make('block_newgu_spdetails', 'assignmentsduequery');
        $now = usertime(time());
        $currenttime = usertime(time());
        $fiveminutes = $currenttime - 300;
        $cachekey = self::CACHE_KEY . $USER->id;
        $cachedata = $cache->get_many([$cachekey]);
        $assignmentdata = [];

        if (!$cachedata[$cachekey] || $cachedata[$cachekey][0]['updated'] < $fiveminutes) {
            $lastmonth = usertime(mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y')));
            $select = 'userid = :userid AND ((timecreated BETWEEN :lastmonth AND :now) OR (timemodified BETWEEN :tlastmonth AND
            :tnow))';
            $params = [
                'userid' => $USER->id,
                'lastmonth' => $lastmonth,
                'now' => $now,
                'tlastmonth' => $lastmonth,
                'tnow' => $now,
            ];
            $assignmentsubmissions = $DB->get_records_select('assign_submission', $select, $params, '', 'assignment, status');

            $submissionsdata = [
                'updated' => $currenttime,
                'assignmentsubmissions' => $assignmentsubmissions,
            ];

            $cachedata = [
                $cachekey => [
                    $submissionsdata,
                ],
            ];
            $cache->set_many($cachedata);
        } else {
            $cachedata = $cache->get_many([$cachekey]);
            $assignmentsubmissions = $cachedata[$cachekey][0]['assignmentsubmissions'];
        }

        $assignment = $this->assign->get_instance();
        $allowsubmissionsfromdate = $assignment->allowsubmissionsfromdate;
        $duedate = $assignment->duedate;

        // Check if any individual overrides have been set up first of all...
        $overrides = $DB->get_record('assign_overrides', ['assignid' => $assignment->id, 'userid' => $USER->id]);
        if (!empty($overrides)) {
            $allowsubmissionsfromdate = $overrides->allowsubmissionsfromdate;
            $duedate = $overrides->duedate;
        }

        $userflags = $DB->get_record('assign_user_flags', ['assignment' => $assignment->id, 'userid' => $USER->id]);
        if (!empty($userflags)) {
            if ($userflags->extensionduedate > 0) {
                $duedate = $userflags->extensionduedate;
            }
        }

        // Looks like when visiting an activity, you end up with a submission entry by default.
        if (!array_key_exists($assignment->id, $assignmentsubmissions) ||
            (array_key_exists($assignment->id, $assignmentsubmissions) &&
            (is_object($assignmentsubmissions[$assignment->id]) &&
            property_exists($assignmentsubmissions[$assignment->id], 'status') &&
            $assignmentsubmissions[$assignment->id]->status == 'new'))) {
            if ($allowsubmissionsfromdate < $now) {
                if ($duedate > $now) {
                    $assignmentdata[] = $assignment;
                }
            }
        }

        return $assignmentdata;
    }

}
