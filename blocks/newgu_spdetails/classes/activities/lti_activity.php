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
 * Concrete implementation for mod_lti.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

/**
 * Implementation for a LTI activity.
 */
class lti_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $lti
     */
    private $lti;

    /**
     * Constructor, set grade itemid.
     *
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        parent::__construct($gradeitemid, $courseid, $groupid);

        // Get the lti object.
        $this->cm = \local_gugrades\users::get_cm_from_grade_item($gradeitemid, $courseid);
        $this->lti = $this->get_lti($this->cm);
    }

    /**
     * Get an lti record and return as an object.
     *
     * @param object $cm course module
     * @return object
     */
    public function get_lti($cm): object {
        global $DB;

        $coursemodulecontext = \context_module::instance($cm->id);
        $lti = $DB->get_record('lti', ['id' => $this->gradeitem->iteminstance], '*', MUST_EXIST);
        $lti->coursemodulecontext = $coursemodulecontext;

        return $lti;
    }

    /**
     * If the LTI has been setup to accept grades from the external tool,
     * we can return this from Gradebook - otherwise - return nothing.
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

        // I'm not sure this is even needed, as unless the activity has been
        // marked to update the gradebook, we will never reach this method.
        if ($this->lti->instructorchoiceacceptgrades == 0) {
            return false;
        }

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
        return 0;
    }

    /**
     * Return a formatted date.
     *
     * @param int $unformatteddate
     * @return string
     */
    public function get_formattedduedate(int $unformatteddate = null): string {

        $duedate = 'N/A';
        if ($unformatteddate > 0) {
            $duedate = userdate($unformatteddate, get_string('strftimedate', 'core_langconfig'));
        }

        return $duedate;
    }

    /**
     * Method to return the current status of the assessment item.
     *
     * Erys notes seem to be 'assessed' by students making an assignment submission.
     *
     * @param int $userid
     * @return object
     */
    public function get_status(int $userid): object {
        global $DB;

        $statusobj = new \stdClass();
        $statusobj->assessment_url = $this->get_assessmenturl();
        $ltiinstance = $this->lti;
        $statusobj->grade_status = get_string('status_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->status_text = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');;
        $statusobj->status_class = '';
        $statusobj->status_link = '';
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->due_date = $ltiinstance->duedate;
        $statusobj->raw_due_date = $ltiinstance->duedate;
        $statusobj->grade_date = '';
        $statusobj->grade_class = false;

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
     * LTI's don't appear to have due dates attached to them. For now we can only return an empty array.
     *
     * @return array
     */
    public function get_assessmentsdue(): array {
        $ltidata = [];
        return $ltidata;

    }

}
