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
 * Concrete implementation for mod_h5pactivity.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

use cache;

/**
 * Implementation for a h5p activity.
 */
class h5pactivity_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $h5pactivity
     */
    private $h5pactivity;

    /**
     * @var constant CACHE_KEY
     */
    const CACHE_KEY = 'studentid_h5pduesoon:';

    /**
     * For this activity, get just the basic h5p and course module info.
     *
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        parent::__construct($gradeitemid, $courseid, $groupid);

        // Get the course module object.
        $this->cm = \local_gugrades\users::get_cm_from_grade_item($gradeitemid, $courseid);
        $this->h5pactivity = $this->get_h5pactivity();
    }

    /**
     * Return a h5p object.
     *
     * @return object
     */
    public function get_h5pactivity() {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/lib/datalib.php');
        $course = $DB->get_record('course', ['id' => $this->courseid], '*', MUST_EXIST);
        $h5pactivities = get_all_instances_in_course('h5pactivity', $course);
        $instance = null;
        foreach ($h5pactivities as $h5pactivity) {
            if ($this->gradeitem->iteminstance == $h5pactivity->id) {
                $instance = $h5pactivity;
                break;
            }
        }

        return $instance;
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
     * Return 0 as this activity doesn't have any kind of due date.
     *
     * @return int
     */
    public function get_rawduedate(): int {
        return 0;
    }

    /**
     * Return N/A as this activity doesn't have any kind of due date.
     *
     * @return string
     */
    public function get_formattedduedate(): string {
        return 'N/A';
    }

    /**
     * This activity only needs to return mostly empty data, as there aren't too many restrictions, e.g. due date etc.
     *
     * @param int $userid
     * @return object
     */
    public function get_status(int $userid): object {
        global $DB;

        $statusobj = new \stdClass();
        $statusobj->assessment_url = $this->get_assessmenturl();
        $statusobj->grade_status = get_string('status_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->status_text = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->status_class = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
        $statusobj->status_link = '';
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->grade_class = false;
        $statusobj->due_date = 'N/A';
        $statusobj->raw_due_date = '';
        $statusobj->grade_date = '';

        $h5psubmission = $DB->get_record('h5pactivity_attempts', [
            'h5pactivityid' => $this->h5pactivity->id,
            'userid' => $userid,
        ]);

        $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
        $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
        $statusobj->status_class = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');

        if (!empty($h5psubmission)) {
            $statusobj->grade_status = $h5psubmission->completion;

            if ($statusobj->grade_status == 1) {
                $statusobj->status_class = get_string('status_class_submitted', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_submitted', 'block_newgu_spdetails');
                $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                $statusobj->status_link = '';
            }

        } else {
            $statusobj->grade_status = get_string('status_submit', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submit', 'block_newgu_spdetails');
            $statusobj->status_class = get_string('status_class_submit', 'block_newgu_spdetails');
            $statusobj->status_link = $statusobj->assessment_url;
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        }

        return $statusobj;
    }

    /**
     * Returns an empty array here as this activity type has no due dates, therefore it
     * can't feed the "Assessments due in..." chart.
     *
     * @return array
     */
    public function get_assessmentsdue(): array {
        return [];
    }

}
