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
 * Default class for grade/activity access classes.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Howard Miller/Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

/**
 * Access data in course activities
 *
 */
abstract class base {

    /**
     * @var int $gradeitemid
     */
    protected int $gradeitemid;

    /**
     * @var object $gradeitem
     */
    protected object $gradeitem;

    /**
     * @var int $courseid
     */
    protected int $courseid;

    /**
     * @var int $groupid
     */
    protected int $groupid;

    /**
     * @var string $itemurl
     */
    protected string $itemurl;

    /**
     * @var string $itemtype
     */
    protected string $itemtype;

    /**
     * @var string $itemmodule
     */
    protected string $itemmodule;

    /**
     * @var string $itemname
     */
    protected string $itemname;

    /**
     * @var string $itemscript
     */
    protected string $itemscript;

    /**
     * @var object $feedback
     */
    protected object $feedback;

    /**
     * Constructor, set grade itemid
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        global $CFG, $DB;

        $this->gradeitemid = $gradeitemid;
        $this->courseid = $courseid;
        $this->groupid = $groupid;

        // Get grade item.
        $this->gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $this->itemtype = $this->gradeitem->itemtype;
        $this->itemname = $this->gradeitem->itemname;

        // The URL format seems to be consistent between activities.
        $this->itemurl = $CFG->wwwroot . '/';
        $this->itemmodule = '/' . $this->gradeitem->itemmodule;
        $this->itemscript = '/view.php?id=';
    }

    /**
     * Implement get_first_grade
     * This is currently just the same as a manual grade
     * (this is pulling 'finalgrade' instead of 'rawgrade'. Not sure if this is correct/complete).
     *
     * @param int $userid
     * @return mixed object|bool
     */
    public function get_first_grade(int $userid): object|bool {
        global $DB;
        $gradeobj = new \stdClass();
        $gradeobj->finalgrade = null;
        $gradeobj->rawgrade = null;
        $gradeobj->gradedate = null;

        if ($grade = $DB->get_record('grade_grades', ['itemid' => $this->gradeitemid, 'userid' => $userid])) {
            if ($grade->finalgrade != null && $grade->finalgrade > 0) {
                $gradeobj->finalgrade = $grade->finalgrade;
                // Not sure if this is correct, however, it seems plausible that the
                // date the item was graded/released could be inferred from the time
                // -modified column.
                $gradeobj->gradedate = $grade->timemodified;
                return $gradeobj;
            }

            if ($grade->rawgrade != null && $grade->rawgrade > 0) {
                $gradeobj->rawgrade = $grade->rawgrade;
                return $gradeobj;
            }
        }

        return false;
    }

    /**
     * Get item type.
     *
     * @return string
     */
    public function get_itemtype(): string {
        return $this->itemtype;
    }

    /**
     * Get item module.
     *
     * @return string
     */
    public function get_itemmodule(): string {
        return $this->itemmodule;
    }

    /**
     * Get item script.
     *
     * @return string
     */
    public function get_itemscript(): string {
        return $this->itemscript;
    }

    /**
     * Get item url.
     *
     * @return string
     */
    public function get_itemurl(): string {
        return $this->itemurl . $this->get_itemtype() . $this->get_itemmodule() . $this->get_itemscript();
    }

    /**
     * Get item name.
     *
     * @return string
     */
    public function get_itemname(): string {
        return $this->itemname;
    }

    /**
     * Allow the implementing class to decide how the status
     * should be determined.
     *
     * @param int $userid
     * @return object
     */
    abstract public function get_status(int $userid): object;

    /**
     * Return the feedback for a given graded activity.
     *
     * We need to make this part of the object - currently
     * being called as a static method.
     *
     * @param object $gradestatusobj
     * @return object
     */
    public function get_feedback(object $gradestatusobj): object {
        $feedbackobj = new \stdClass();
        $feedbackobj->grade_feedback = '';
        $feedbackobj->grade_feedback_link = '';

        switch($gradestatusobj->grade_status) {
            case get_string('status_submit', 'block_newgu_spdetails'):
            case get_string('status_notopen', 'block_newgu_spdetails'):
            case get_string('status_submissionnotopen', 'block_newgu_spdetails'):
            case get_string('status_notsubmitted', 'block_newgu_spdetails') :
            case get_string('status_draft', 'block_newgu_spdetails') :
            case get_string('status_overdue', 'block_newgu_spdetails'):
            case get_string('status_submitted', 'block_newgu_spdetails'):
                $feedbackobj->grade_feedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');

                // For activity type 'assignment', when Marking Workflow is enabled, feedback becomes available for the
                // student when the status is set to 'Released', even ^if^ Reveal Identities hasn't been triggered.
                // Honour this same behaviour here.
                if ($gradestatusobj->markingworkflow && $gradestatusobj->workflowstate == 'released') {
                    $feedbackobj->grade_feedback = get_string('status_text_viewfeedback', 'block_newgu_spdetails');
                    $feedbackobj->grade_feedback_link = $gradestatusobj->assessment_url . '#page-footer';
                }
                break;
            case get_string('status_notsubmitted', 'block_newgu_spdetails'):
                $feedbackobj->grade_feedback = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
                break;

            case get_string('status_graded', 'block_newgu_spdetails'):
                $feedbackobj->grade_feedback = get_string('status_text_viewfeedback', 'block_newgu_spdetails');
                $feedbackobj->grade_feedback_link = $gradestatusobj->assessment_url . '#page-footer';

                // For activity type 'quiz', there are settings which prevent the display of any feedback.
                if (property_exists($gradestatusobj, 'feedbackcolumn') && !$gradestatusobj->feedbackcolumn) {
                    $feedbackobj->grade_feedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                    $feedbackobj->grade_feedback_link = '';
                }
                break;

            default:
                $feedbackobj->grade_feedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                break;
        }

        return $feedbackobj;
    }

    /**
     * Allow the implementing class to determine how the due
     * date of assessment submissions are worked out.
     *
     * @return array
     */
    abstract public function get_assessmentsdue(): array;

}
