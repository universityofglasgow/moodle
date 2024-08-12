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
 * Concrete implementation for mod_lesson.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

use cache;

/**
 * Implementation for a lesson activity.
 */
class lesson_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $lesson
     */
    private $lesson;

    /**
     * @var constant CACHE_KEY
     */
    const CACHE_KEY = 'studentid_lessonsduesoon:';

    /**
     * Constructor, set grade itemid.
     *
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        parent::__construct($gradeitemid, $courseid, $groupid);

        // Get the lesson object.
        $this->cm = \local_gugrades\users::get_cm_from_grade_item($gradeitemid, $courseid);
        $this->lesson = $this->get_lesson();
    }

    /**
     * Get lesson object.
     *
     * @return object
     */
    public function get_lesson() {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/lesson/lib.php');
        require_once($CFG->dirroot . '/mod/lesson/locallib.php');
        $lessonid = $this->gradeitem->iteminstance;
        $lessonrecord = $DB->get_record('lesson', ['id' => $lessonid]);
        $lesson = new \lesson($lessonrecord);

        return $lesson;
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

        $dateinstance = lesson_get_user_deadline($this->courseid);
        $rawdate = $dateinstance[$this->gradeitem->iteminstance]->userdeadline;

        return $rawdate;
    }

    /**
     * Return a formatted date.
     *
     * @param int $unformatteddate
     * @return string
     */
    public function get_formattedduedate(int $unformatteddate = null): string {
        $dateinstance = $this->lesson;
        $rawdate = $dateinstance->deadline;
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
        $statusobj->due_date = $this->lesson->deadline;
        $statusobj->raw_due_date = $this->lesson->deadline;
        $statusobj->grade_status = '';
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $allowsubmissionsfromdate = $this->lesson->available;
        $statusobj->status_link = '';
        $statusobj->grade_date = '';
        $statusobj->grade_class = false;

        // Check if any overrides have been set up first of all...
        $overrides = $DB->get_record('lesson_overrides', ['lessonid' => $this->lesson->id, 'userid' => $userid]);
        if (!empty($overrides)) {
            $allowsubmissionsfromdate = $overrides->available;
            $statusobj->due_date = $overrides->deadline;
            $statusobj->raw_due_date = $overrides->deadline;
        }

        if ($allowsubmissionsfromdate > time()) {
            $statusobj->grade_status = get_string('status_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        }

        if ($statusobj->grade_status == '') {
            $lessonattempts = $DB->count_records('lesson_attempts', ['lessonid' => $this->lesson->id, 'userid' => $userid]);
            if ($lessonattempts > 0) {
                $statusobj->grade_status = get_string('status_submitted', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_submitted', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_submitted', 'block_newgu_spdetails');

                if ($lessongrades = $DB->get_record('lesson_grades', ['lessonid' => $this->lesson->id, 'userid' => $userid,
                'completed' => 1])) {
                    $statusobj->grade_status = get_string('status_graded', 'block_newgu_spdetails');
                    $statusobj->status_text = get_string('status_text_graded', 'block_newgu_spdetails');
                    $statusobj->status_class = get_string('status_class_graded', 'block_newgu_spdetails');
                    $statusobj->grade_to_display = $lessongrades->grade;
                }

            } else {
                $statusobj->grade_status = get_string('status_submit', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_submit', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_submit', 'block_newgu_spdetails');
                $statusobj->status_link = $statusobj->assessment_url;

                if (time() > $statusobj->due_date && $statusobj->due_date != 0) {
                    $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
                    $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
                    $statusobj->status_class = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
                    $statusobj->status_link = '';
                }

                // Not even sure if this is correct - this came from the Assignment activity code.
                if (time() > $statusobj->due_date + (86400 * 30) && $statusobj->due_date != 0) {
                    $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                    $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                    $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                    $statusobj->status_link = $statusobj->assessment_url;
                }
            }
        }

        // Formatting this here as the integer format for the date is no longer needed for testing against.
        if ($statusobj->due_date != 0) {
            $statusobj->due_date = $this->get_formattedduedate($statusobj->due_date);
            $statusobj->raw_due_date = $this->get_rawduedate();
        } else {
            $statusobj->due_date = '';
            $statusobj->raw_due_date = '';
        }

        return $statusobj;
    }

    /**
     * Return the due date of the lesson if it hasn't been submitted.
     * Given that a Lesson activity can have a number of permutations with regards opening/deadline dates,
     * along with a timer, this gives us a number of...tbc.
     *
     * @return array
     */
    public function get_assessmentsdue(): array {
        global $USER, $DB;

        // Cache this query as it's going to get called for each lesson in the course otherwise.
        $cache = cache::make('block_newgu_spdetails', 'lessonsduequery');
        $now = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
        $currenttime = time();
        $fiveminutes = $currenttime - 300;
        $cachekey = self::CACHE_KEY . $USER->id;
        $cachedata = $cache->get_many([$cachekey]);
        $lessondata = [];

        if (!$cachedata[$cachekey] || $cachedata[$cachekey][0]['updated'] < $fiveminutes) {
            $lastmonth = mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y'));
            $select = 'userid = :userid AND ((lessontime BETWEEN :lastmonth AND :now) OR (lessontime BETWEEN :tlastmonth AND
            :tnow))';
            $params = [
                'userid' => $USER->id,
                'lastmonth' => $lastmonth,
                'now' => $now,
                'tlastmonth' => $lastmonth,
                'tnow' => $now,
            ];
            // This table seems to be the only practical table to query for submission deadlines. lesson_attempts only stores when
            // an attempt was made.
            $timedlessonsubmissions = $DB->get_records_select('lesson_timer', $select, $params, '', 'lessonid, starttime,
            completed');

            $submissionsdata = [
                'updated' => time(),
                'timedlessonsubmissions' => $timedlessonsubmissions,
            ];

            $cachedata = [
                $cachekey => [
                    $submissionsdata,
                ],
            ];
            $cache->set_many($cachedata);
        } else {
            $cachedata = $cache->get_many([$cachekey]);
            $timedlessonsubmissions = $cachedata[$cachekey][0]['timedlessonsubmissions'];
        }

        $lesson = $this->lesson;
        $lessonavailable = $lesson->available;
        $lessondeadline = $lesson->deadline;
        $timelimit = $lesson->timelimit;

        // Check if any individual overrides have been set up first of all.
        $overrides = $DB->get_record('lesson_overrides', ['lessonid' => $lesson->id, 'userid' => $USER->id]);
        if (!empty($overrides)) {
            $lessonavailable = $overrides->available;
            $lessondeadline = $overrides->deadline;
            $timelimit = $overrides->timelimit;
        }

        // Much like activity type Assignment, we end up with a 'submission' that we now need to check if it's 'completed'.
        if (!array_key_exists($lesson->id, $timedlessonsubmissions) ||
        (array_key_exists($lesson->id, $timedlessonsubmissions) &&
        (is_object($timedlessonsubmissions[$lesson->id]) && property_exists($timedlessonsubmissions[$lesson->id], 'completed') &&
        $timedlessonsubmissions[$lesson->id]->completed == 0))) {
            // Also similar to Assignment, we can set dates for when a lesson is available from, and/or when it is due by.
            if ($lessonavailable != 0 && $lessonavailable < $now) {
                if ($lessondeadline != 0 && $now < $lessondeadline) {
                    $obj = new \stdClass();
                    $obj->name = $lesson->name;
                    $obj->duedate = $lessondeadline;
                    $lessondata[] = $obj;
                }
            }
            // As well as setting just a time limit.
            if ($lessonavailable == 0) {
                if (is_object($timedlessonsubmissions[$lesson->id]) && $timelimit > 0 &&
                (($timedlessonsubmissions[$lesson->id]->starttime + $timelimit) > $now)) {
                    $obj = new \stdClass();
                    $obj->name = $lesson->name;
                    $obj->duedate = ($timedlessonsubmissions[$lesson->id]->starttime + $timelimit);
                    $lessondata[] = $obj;
                }
            }
        }

        return $lessondata;
    }

}
