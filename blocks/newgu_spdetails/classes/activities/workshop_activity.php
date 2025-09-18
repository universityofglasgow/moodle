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
 * Concrete implementation for mod_workshop.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/mod/workshop/locallib.php');

use cache;
use workshop;

/**
 * Implementation for a workshop activity.
 */
class workshop_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $workshop
     */
    private $workshop;

    /**
     * @var constant CACHE_KEY
     */
    const CACHE_KEY = 'studentid_workshopduesoon:';

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
        $this->workshop = $this->get_workshop($gradeitemid, $this->cm);
    }

    /**
     * Get workshop object.
     *
     * @param int $gradeitemid
     * @param object $cm course module
     * @return object
     */
    private function get_workshop(int $gradeitemid, object $cm) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/workshop/locallib.php');
        $course = $DB->get_record('course', ['id' => $this->courseid], '*', MUST_EXIST);
        $gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $workshoprecord = $DB->get_record('workshop', ['id' => $gradeitem->iteminstance], '*', MUST_EXIST);
        $coursemodulecontext = \context_module::instance($cm->id);
        $workshop = new \workshop($workshoprecord, $cm, $course, $coursemodulecontext);

        return $workshop;
    }

    /**
     * Return the grade directly from Gradebook.
     *
     * @param int $userid
     * @return mixed object|bool
     */
    public function get_grade(int $userid): object|bool {
        global $DB;

        $activitygrade = new \stdClass();
        $activitygrade->finalgrade = null;
        $activitygrade->rawgrade = null;
        $activitygrade->gradedate = null;

        // If the grade is overridden in the Gradebook then we can
        // revert to the base - i.e., get the grade from the Gradebook.
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
        $workshopphase = $this->gradeitem->itemnumber;
        $workshopduedate = 0;
        switch($workshopphase) {
            case 0:
                $workshopduedate = $this->workshop->submissionend;
            break;
            case 1:
                $workshopduedate = $this->workshop->assessmentend;
            break;
        }

        $rawdate = $workshopduedate;

        return $rawdate;
    }

    /**
     * Return a formatted date.
     * We need to account for both the submission and assessment items.
     * $gradeitem->itemnumber appears to denote 0 for the submission and 1 for the assessment.
     *
     * @param int $unformatteddate
     * @return string
     */
    public function get_formattedduedate(int|null $unformatteddate = null): string {
        $workshopphase = $this->gradeitem->itemnumber;
        $workshopduedate = 'N/A';
        switch($workshopphase) {
            case 0:
                $workshopduedate = $this->workshop->submissionend;
            break;
            case 1:
                $workshopduedate = $this->workshop->assessmentend;
            break;
        }

        $rawdate = $workshopduedate;
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
     * Workshop creates 2 entries in Gradebook - one for an assessment and one for
     * a submission.
     * $gradeitem->itemnumber appears to denote 0 for the submission and 1 for the assessment.
     * Treat them as two individual activities.
     *
     * @param int $userid
     * @return object
     */
    public function get_status(int $userid): object {
        global $DB;

        $statusobj = new \stdClass();
        $statusobj->assessment_url = $this->get_assessmenturl();
        $workshopinstance = $this->workshop;
        $statusobj->due_date = 'N/A';
        $statusobj->raw_due_date = 0;
        $statusobj->status_class = '';
        $statusobj->status_link = '';
        $statusobj->grade_date = '';
        $statusobj->grade_class = false;
        $workshopactivitytype = '';

        $workshop = $this->workshop;

        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->grade_status = get_string('status_submissionnotopen', 'block_newgu_spdetails');
        $statusobj->status_text = get_string('status_text_submissionnotopen', 'block_newgu_spdetails');

        switch ($this->gradeitem->itemnumber) {
            case 0:
                $allowsubmissionsfromdate = $workshopinstance->submissionstart;
                $statusobj->due_date = $workshopinstance->submissionend;
                $statusobj->raw_due_date = $workshopinstance->submissionend;
                $workshopactivitytype = 'submission';
            break;
            case 1:
                $allowsubmissionsfromdate = $workshopinstance->assessmentstart;
                $statusobj->due_date = $workshopinstance->assessmentend;
                $statusobj->raw_due_date = $workshopinstance->assessmentend;
                $workshopactivitytype = 'assessment';
            break;
        }

        $now = usertime(time());

        // Initial status based on the current phase of the workshop activity and the current time.
        switch ($workshopinstance->phase) {
            case \workshop::PHASE_SETUP:
                $statusobj->grade_status = get_string('status_submissionnotopen', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_submissionnotopen', 'block_newgu_spdetails');
            break;
            case \workshop::PHASE_SUBMISSION:
                if ($workshopactivitytype == 'submission') {
                    // The submission phase is not open yet.
                    if ($allowsubmissionsfromdate > $now) {
                        $statusobj->grade_status = get_string('status_submissionnotopen', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_submissionnotopen', 'block_newgu_spdetails');
                    } else if ($allowsubmissionsfromdate <= $now && $workshopinstance->submissionend > $now) {
                        // The submission phase is open.
                        $statusobj->grade_status = get_string('status_submit', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_submit', 'block_newgu_spdetails');
                        $statusobj->status_class = get_string('status_class_submit', 'block_newgu_spdetails');
                        $statusobj->status_link = $statusobj->assessment_url;
                    } else if ($workshopinstance->submissionend < $now) {
                        // The submission phase is closed.
                        $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                        $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                    }
                }
            break;
            case \workshop::PHASE_ASSESSMENT:
                if ($workshopactivitytype == 'assessment') {
                    // The assessment phase is not open yet.
                    if ($allowsubmissionsfromdate > $now) {
                        $statusobj->grade_status = get_string('status_submissionnotopen', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_submissionnotopen', 'block_newgu_spdetails');
                    } else if ($allowsubmissionsfromdate <= $now && $workshopinstance->assessmentend > $now) {
                        // The assessment phase is open.
                        $statusobj->grade_status = get_string('status_submissionunavailable', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_submissionunavailable', 'block_newgu_spdetails');
                        $statusobj->status_link = $statusobj->assessment_url;
                    } else if ($workshopinstance->assessmentend < $now) {
                        // The assessment phase is closed.
                        $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                        $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                    }
                }
                if ($workshopactivitytype == 'submission') {
                    // The assessment phase is open, so you probably missed the submission phase.
                    $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                    $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                    $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                }
            break;
            case \workshop::PHASE_EVALUATION:
                $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
            break;
            case \workshop::PHASE_CLOSED:
                $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
            break;
        }

        // Collect all the submissions and assessments for this user.
        $submissionbyuser = $workshop->get_submission_by_author($userid);
        $assessmentsbyuser = $workshop->get_assessments_by_reviewer($userid);
        $pendingassessments = $workshop->get_pending_assessments_by_reviewer($userid);

        // Do we have any submissions?
        if ($workshopactivitytype == 'submission' &&
            !empty($submissionbyuser)
            ) {
            $statusobj->grade_status = get_string('status_submitted', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submitted', 'block_newgu_spdetails');
            $statusobj->status_class = get_string('status_class_submitted', 'block_newgu_spdetails');
            $statusobj->status_link = '';
        }

        // Do we have any assessments?
        if ($workshopactivitytype == 'assessment' &&
            empty($pendingassessments) &&
            empty($assessmentsbyuser) &&
            $workshopinstance->phase > \workshop::PHASE_SUBMISSION &&
            ($allowsubmissionsfromdate <= $now && $workshopinstance->assessmentend > $now)
            ) {
            $statusobj->grade_status = get_string('status_unavailable', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submissionunavailable', 'block_newgu_spdetails');
            $statusobj->status_class = '';
            $statusobj->status_link = '';
        }

        // Do we need more assessments?
        if ($workshopactivitytype == 'assessment' &&
            !empty($pendingassessments) &&
            $workshopinstance->phase > \workshop::PHASE_SUBMISSION &&
            ($allowsubmissionsfromdate <= $now && $workshopinstance->assessmentend > $now)
            ) {
            $statusobj->grade_status = get_string('status_submit', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submit', 'block_newgu_spdetails');
            $statusobj->status_class = get_string('status_class_submit', 'block_newgu_spdetails');
        }

        // Do we have all assessments?
        if ($workshopactivitytype == 'assessment' &&
            empty($pendingassessments) &&
            !empty($assessmentsbyuser) &&
            $workshopinstance->phase > \workshop::PHASE_SUBMISSION
            ) {
            $statusobj->grade_status = get_string('status_submitted', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submitted', 'block_newgu_spdetails');
            $statusobj->status_class = get_string('status_class_submitted', 'block_newgu_spdetails');
            $statusobj->status_link = '';
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
     * Return the due date of the workshop activity if it hasn't been submitted.
     * This can apply to both the Submission phase and Assessment phase.
     *
     * @return array
     */
    public function get_assessmentsdue(): array {
        global $USER, $DB;

        // Cache this query as it's going to get called for each assessment in the course otherwise.
        $cache = cache::make('block_newgu_spdetails', 'workshopduequery');
        $now = usertime(time());
        $currenttime = usertime(time());
        $fiveminutes = $currenttime - 300;
        $cachekey = self::CACHE_KEY . $USER->id . '_' . $this->gradeitem->itemnumber;
        $cachedata = $cache->get_many([$cachekey]);
        $workshopdata = [];

        $workshopactivityphase = $this->gradeitem->itemnumber;

        // We're treating itemnumber 0 as the submission and 1 as the assessment.

        if (!$cachedata[$cachekey] || $cachedata[$cachekey][0]['updated'] < $fiveminutes) {
            $lastmonth = usertime(mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y')));
            if ($workshopactivityphase == 0) {
                $sql = 'authorid = :userid AND ((timecreated BETWEEN :lastmonth AND :now) OR (timemodified BETWEEN :tlastmonth AND
                :tnow))';
                $params = [
                    'userid' => $USER->id,
                    'lastmonth' => $lastmonth,
                    'now' => $now,
                    'tlastmonth' => $lastmonth,
                    'tnow' => $now,
                ];
                $workshopsubmissions = $DB->get_fieldset_select('workshop_submissions', 'workshopid', $sql, $params);

                $submissionsdata = [
                    'updated' => $currenttime,
                    'workshopsubmissions' => $workshopsubmissions,
                ];

            }
            if ($workshopactivityphase == 1) {
                // Here we'll need to join the assessments against the submission table using the submissionid.
                $tmpworkshopsubmissions = $DB->get_recordset_sql(
                    'SELECT workshopid FROM {workshop_submissions} ws INNER JOIN {workshop_assessments} wa ON
                    wa.submissionid = ws.id WHERE wa.reviewerid = :userid AND ((wa.timecreated BETWEEN :lastmonth AND :now) OR
                    (wa.timemodified BETWEEN :tlastmonth AND :tnow))',
                    [
                        'userid' => $USER->id,
                        'lastmonth' => $lastmonth,
                        'now' => $now,
                        'tlastmonth' => $lastmonth,
                        'tnow' => $now,
                    ]
                );
                // We need to turn this back into a regular array.
                $workshopsubmissions = [];
                if ($tmpworkshopsubmissions) {
                    foreach ($tmpworkshopsubmissions as $workshopsubmission) {
                        $workshopsubmissions[] = $workshopsubmission->workshopid;
                    }
                }

                $submissionsdata = [
                    'updated' => $currenttime,
                    'workshopsubmissions' => $workshopsubmissions,
                ];
            }

            $cachedata = [
                $cachekey => [
                    $submissionsdata,
                ],
            ];
            $cache->set_many($cachedata);
        } else {
            $cachedata = $cache->get_many([$cachekey]);
            $workshopsubmissions = $cachedata[$cachekey][0]['workshopsubmissions'];
        }

        $workshop = $this->workshop;

        if (!in_array($this->gradeitem->iteminstance, $workshopsubmissions)) {
            if ($workshopactivityphase == 0) {
                if ($workshop->submissionstart != 0 && $workshop->submissionstart < $now) {
                    if ($workshop->submissionend != 0 && $workshop->submissionend > $now) {
                        $obj = new \stdClass();
                        $obj->name = $this->gradeitem->itemname;
                        $obj->duedate = $workshop->submissionend;
                        $workshopdata[] = $obj;
                    }
                }
            }
            if ($workshopactivityphase == 1) {
                if ($workshop->assessmentstart != 0 && $workshop->assessmentstart < $now) {
                    if ($workshop->assessmentend != 0 && $workshop->assessmentend > $now) {
                        $obj = new \stdClass();
                        $obj->name = $this->gradeitem->itemname;
                        $obj->duedate = $workshop->assessmentend;
                        $workshopdata[] = $obj;
                    }
                }
            }

        }

        return $workshopdata;
    }

}
