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
 * Concrete implementation for mod_peerwork.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

use cache;

/**
 * Implementation for a peerwork activity.
 */
class peerwork_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $peerwork
     */
    private $peerwork;

    /**
     * @var constant CACHE_KEY
     */
    const CACHE_KEY = 'studentid_peerworkduesoon:';

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
        $this->peerwork = $this->get_peerwork($this->cm);
    }

    /**
     * Get peerwork object.
     *
     * @param object $cm course module
     * @return object
     */
    public function get_peerwork(object $cm): object {
        global $DB;

        $coursemodulecontext = \context_module::instance($cm->id);
        $peerwork = $DB->get_record('peerwork', ['id' => $this->gradeitem->iteminstance], '*', MUST_EXIST);
        $peerwork->coursemodulecontext = $coursemodulecontext;

        return $peerwork;
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
        $dateinstance = $this->peerwork;
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
        $dateinstance = $this->peerwork;
        $rawdate = $dateinstance->duedate;
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
        $peerworkinstance = $this->peerwork;
        $allowsubmissionsfromdate = $peerworkinstance->fromdate;
        $statusobj->grade_status = '';
        $statusobj->status_text = '';
        $statusobj->status_class = '';
        $statusobj->status_link = '';
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->due_date = $peerworkinstance->duedate;
        $statusobj->raw_due_date = $peerworkinstance->duedate;
        $statusobj->grade_date = '';
        $statusobj->grade_class = false;

        if ($allowsubmissionsfromdate > time()) {
            $statusobj->grade_status = get_string('status_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        }

        if ($statusobj->grade_status == '') {
            $peerworksubmission = $DB->get_record('peerwork_submission', ['peerworkid' => $peerworkinstance->id,
            'userid' => $userid]);

            $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
            $statusobj->status_class = get_string('status_class_notsubmitted', 'block_newgu_spdetails');

            if (!empty($peerworksubmission)) {
                $statusobj->grade_status = $peerworksubmission->releasedby;

                if ($statusobj->grade_status == null) {
                    $statusobj->grade_status = get_string('status_submitted', 'block_newgu_spdetails');
                    $statusobj->status_class = get_string('status_class_submitted', 'block_newgu_spdetails');
                    $statusobj->status_text = get_string('status_text_submitted', 'block_newgu_spdetails');
                }

            } else {
                $statusobj->grade_status = get_string('status_submit', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_submit', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_submit', 'block_newgu_spdetails');
                $statusobj->status_link = $statusobj->assessment_url;

                if ($statusobj->due_date != 0 && time() > $statusobj->due_date) {
                    $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
                    $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
                    $statusobj->status_class = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
                    $statusobj->status_link = '';

                    if ($peerworkinstance->allowlatesubmissions) {
                        $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                        $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                        $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                        $statusobj->status_link = $statusobj->assessment_url;
                        $statusobj->grade_to_display = get_string('status_text_overdue', 'block_newgu_spdetails');
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
            $statusobj->raw_due_date = '';
        }

        return $statusobj;
    }

    /**
     * Return the due date of the peerwork assignment if it hasn't been submitted.
     *
     * @return array
     */
    public function get_assessmentsdue(): array {
        global $USER, $DB;

        // Cache this query as it's going to get called for each assessment in the course otherwise.
        $cache = cache::make('block_newgu_spdetails', 'peerworkduequery');
        $now = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
        $currenttime = time();
        $fiveminutes = $currenttime - 300;
        $cachekey = self::CACHE_KEY . $USER->id;
        $cachedata = $cache->get_many([$cachekey]);
        $peerworkdata = [];

        if (!$cachedata[$cachekey] || $cachedata[$cachekey][0]['updated'] < $fiveminutes) {
            $lastmonth = mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y'));
            $select = 'userid = :userid AND ((timecreated BETWEEN :lastmonth AND :now) OR (timemodified BETWEEN :tlastmonth AND
            :tnow))';
            $params = [
                'userid' => $USER->id,
                'lastmonth' => $lastmonth,
                'now' => $now,
                'tlastmonth' => $lastmonth,
                'tnow' => $now,
            ];
            $peerworksubmissions = $DB->get_fieldset_select('peerwork_submission', 'peerworkid', $select, $params);

            $submissionsdata = [
                'updated' => time(),
                'peerworksubmissions' => $peerworksubmissions,
            ];

            $cachedata = [
                $cachekey => [
                    $submissionsdata,
                ],
            ];
            $cache->set_many($cachedata);

        } else {
            $cachedata = $cache->get_many([$cachekey]);
            $peerworksubmissions = $cachedata[$cachekey][0]['peerworksubmissions'];
        }

        $peerworkassignment = $this->peerwork;

        if (!in_array($peerworkassignment->id, $peerworksubmissions)) {
            // Where allowlatesubmissions has been checked, include this in the list of things considered due.
            if ($peerworkassignment->allowlatesubmissions == 1) {
                $peerworkdata[] = $peerworkassignment;
            }

            if (($peerworkassignment->allowlatesubmissions == 0) && ($peerworkassignment->fromdate < $now)) {
                if ($peerworkassignment->duedate > $now) {
                    $peerworkdata[] = $peerworkassignment;
                }
            }
        }

        return $peerworkdata;
    }

}
