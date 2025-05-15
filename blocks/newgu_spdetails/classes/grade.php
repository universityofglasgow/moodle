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
 * Class to provide utility methods for grading attributes.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails;

require_once($CFG->libdir . '/grade/grade_scale.php');

use grade_scale;

/**
 * This class provides utility methods for grading attributes.
 */
class grade {
    /**
     * Reimplementation of return_gradestatus as it misses the mark on a
     * number of fundamental levels.
     *
     * @param int $courseid
     * @param int $itemid
     * @param int $userid
     * @param int $gradetype
     * @param int $scaleid
     * @param int $grademax
     * @param string $coursetype - this is needed by the unit tests.
     * @return object
     */
    public static function get_grade_status_and_feedback(int $courseid, int $itemid, int $userid, int $gradetype,
    int $scaleid = null, int $grademax, string $coursetype): object {

        $gradestatus = new \stdClass();
        $gradestatus->assessment_url = '';
        $gradestatus->due_date = '';
        $gradestatus->raw_due_date = '';
        $gradestatus->grade_date = '';
        $gradestatus->grade_status = get_string('status_tobeconfirmed', 'block_newgu_spdetails');
        $gradestatus->status_text = '';
        $gradestatus->status_class = '';
        $gradestatus->status_link = '';
        $gradestatus->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $gradestatus->grade_class = false;
        $gradestatus->grade_provisional = false;
        $gradestatus->grade_feedback = '';
        $gradestatus->grade_feedback_link = '';

        $activity = \block_newgu_spdetails\activity::activity_factory($itemid, $courseid, 0);
        $activitygrade = $activity->get_grade($userid);

        if ($activitygrade) {
            $gradestatus->assessment_url = $activity->get_assessmenturl();
            $gradestatus->raw_due_date = $activity->get_rawduedate();
            $gradestatus->due_date = $activity->get_formattedduedate();

            if (property_exists($activitygrade, 'finalgrade') && $activitygrade->finalgrade != null &&
            $activitygrade->finalgrade >= 0) {
                $grade = self::get_formatted_grade_from_grade_type($activitygrade->finalgrade, $gradetype, $scaleid, $grademax);
                $gradestatus->grade_date = $activitygrade->gradedate;
                $gradestatus->grade_status = get_string('status_graded', 'block_newgu_spdetails');
                $gradestatus->status_text = get_string('status_text_graded', 'block_newgu_spdetails');
                $gradestatus->status_class = get_string('status_class_graded', 'block_newgu_spdetails');
                $gradestatus->grade_to_display = $grade;
                $gradestatus->grade_class = true;
                $gradestatus->grade_feedback = get_string('status_text_viewfeedback', 'block_newgu_spdetails');
                $gradestatus->grade_feedback_link = $activity->get_assessmenturl() . '#page-footer';

                if (property_exists($activitygrade, 'feedbackcolumn') && !$activitygrade->feedbackcolumn) {
                    $gradestatus->grade_feedback = get_string('status_tobeconfirmed', 'block_newgu_spdetails');
                    $gradestatus->grade_feedback_link = '';
                }
                return $gradestatus;
            }

            // It's not been mentioned/specced w/regards provisional grades - do we treat rawgrades as such?
            if (property_exists($activitygrade, 'rawgrade') && $activitygrade->rawgrade != null && $activitygrade->rawgrade > 0) {
                $grade = self::get_formatted_grade_from_grade_type($activitygrade->rawgrade, $gradetype,
                $scaleid, $grademax);
                $gradestatus->grade_status = get_string('status_provisional', 'block_newgu_spdetails');
                $gradestatus->status_text = get_string('status_text_provisional', 'block_newgu_spdetails');
                $gradestatus->status_class = get_string('status_class_provisional', 'block_newgu_spdetails');
                $gradestatus->grade_to_display = $grade;
                $gradestatus->grade_provisional = true;
                $gradestatus->grade_feedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                return $gradestatus;
            }

            // For an assignment activity, if both finalgrade and rawgrade return empty,
            // we do have a grade record - do we/should we use this here?
        }

        // We either don't have a grade record, or the grade may not have been
        // released. Let's work backwards to determine the status. Without making
        // things complicated - at this stage, Grade and Feedback should only
        // need to display 'To be confirmed'.
        $statusobj = $activity->get_status($userid);
        $feedbackobj = $activity->get_feedback($statusobj);
        $gradestatus->due_date = $statusobj->due_date;
        $gradestatus->raw_due_date = $statusobj->raw_due_date;
        $gradestatus->grade_date = $statusobj->grade_date;
        $gradestatus->grade_status = $statusobj->grade_status;
        $gradestatus->status_text = $statusobj->status_text;
        $gradestatus->status_class = $statusobj->status_class;
        $gradestatus->status_link = $statusobj->status_link;
        $gradestatus->assessment_url = $statusobj->assessment_url;
        $gradestatus->grade_to_display = $statusobj->grade_to_display;
        $gradestatus->grade_class = $statusobj->grade_class;
        $gradestatus->grade_feedback = $feedbackobj->grade_feedback;
        $gradestatus->grade_feedback_link = $feedbackobj->grade_feedback_link;

        return $gradestatus;
    }

    /**
     * Get the grade, status and feedback values for a manually added grade item.
     *
     * @param int $courseid
     * @param int $itemid
     * @param int $userid
     * @param int $gradetype
     * @param int $scaleid
     * @param int $grademax
     * @return object
     */
    public static function get_manual_grade_item_grade_status_and_feedback(int $courseid, int $itemid, int $userid, int $gradetype,
    int $scaleid = null, int $grademax): object {

        global $DB, $CFG;

        $gradestatus = new \stdClass();
        $gradestatus->hidden = 0;
        $gradestatus->assessment_url = '';
        $gradestatus->due_date = '';
        $gradestatus->raw_due_date = '';
        $gradestatus->grade_date = '';
        $gradestatus->grade_status = get_string('status_tobeconfirmed', 'block_newgu_spdetails');
        $gradestatus->status_text = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $gradestatus->status_class = '';
        $gradestatus->status_link = '';
        $gradestatus->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $gradestatus->grade_class = false;
        $gradestatus->grade_provisional = false;
        $gradestatus->grade_feedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $gradestatus->grade_feedback_link = '';
        $grade = $DB->get_record_sql('
            SELECT finalgrade, hidden, feedback FROM {grade_grades} WHERE itemid = :itemid AND userid = :userid',
            [
                'itemid' => $itemid,
                'userid' => $userid,
            ]);
        if ($grade) {
            if ($grade->hidden == 1) {
                $gradestatus->hidden = 1;
            } else {
                if ($grade->finalgrade != null && $grade->finalgrade > 0) {
                    $manualgrade = self::get_formatted_grade_from_grade_type($grade->finalgrade, $gradetype, $scaleid, $grademax);
                    $gradestatus->grade_to_display = $manualgrade;
                    $gradestatus->grade_class = true;
                    $gradestatus->grade_status = get_string('status_graded', 'block_newgu_spdetails');
                    $gradestatus->status_text = get_string('status_text_graded', 'block_newgu_spdetails');
                    $gradestatus->status_class = get_string('status_class_graded', 'block_newgu_spdetails');
                    // @see MGU-1249 - It seems prudent however that if feedback ^has^ been added, then we provide a link to it.
                    if ($grade->feedback != '') {
                        $gradestatus->grade_feedback = get_string('status_text_viewfeedback', 'block_newgu_spdetails');
                        $gradestatus->grade_feedback_link = $CFG->wwwroot . '/grade/report/index.php?id=' . $courseid;
                    } else {
                        $gradestatus->grade_feedback = '-';
                    }
                }
            }

            return $gradestatus;
        }

        return $gradestatus;
    }

    /**
     * This method returns the grade using the format that was set
     * in the Assessment settings page, i.e. Point, Scale or None.
     *
     * @param int|float $grade
     * @param int $gradetype
     * @param int $scaleid
     * @param int $grademax
     * @return string
     */
    public static function get_formatted_grade_from_grade_type(int|float $grade, int $gradetype, int $scaleid = null,
    int $grademax): string {

        $returngrade = null;
        switch ($gradetype) {
            // Point Scale.
            case GRADE_TYPE_VALUE:
                $returngrade = number_format($grade, 2) . " / " . $grademax;
                break;

            case GRADE_TYPE_SCALE:
                // Using the scaleid, derive the scale values...
                $scaleparams = [
                    'id' => $scaleid,
                ];
                $scale = new grade_scale($scaleparams, false);
                $returngrade = $scale->get_nearest_item($grade);
                break;

            // Grade Type has been set to None in the settings...
            case GRADE_TYPE_TEXT:
                $returngrade = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                break;
        }

        return $returngrade;
    }

    /**
     * MGU-1004 - For an Admin grade, we need to map the short code to the value. Otherwise just return the grade as is.
     * MGU-1202/MGU-1203 - Additional MV/NS grades added - MV0 (Good cause further opportunity) and NS0 (No Submission 0 Grade).
     * @param string $admingrade
     * @param string $displaygrade
     * @return string
     */
    public static function is_admin_or_generic_grade(string $admingrade, string $displaygrade): string {
        if ($admingrade) {
            $gradetodisplay = \local_gugrades\admingrades::get_displaygrade_from_name($admingrade);
            // We only want the description as per MGU-1004.
            return $gradetodisplay[1];
        } else {
            return $displaygrade;
        }
    }

    /**
     * Recursive routine to reduce items from all categories
     * to a flat list of items that can then be iterated over.
     *
     * @param string $category
     * @param array $gradeitems
     * @param array $items
     * @param array $gradecategories
     * @return object
     */
    public static function recurse_categorytree(string $category, array $gradeitems, array $items,
    array $gradecategories): object {
        // While this looks odd, when we call this method recursively, we are in fact
        // passing in the previously built up array of $items. We also (re)set $record
        // here since after the final iteration, when control is returned, $items will
        // contain everything bar the items from the last iteration, thereby having the
        // side effect of inadvertantly losing those last items. Setting $record to
        // null allows us us to check (after the last iteration and control is returned)
        // if the object already exist - which it will at the point of last iteration.
        $items = $items;
        $record = null;

        // First find any grade items attached to the current category.
        foreach ($gradeitems as $item) {
            if ($item->categoryid == $category) {
                $items[$item->id] = $item;
            }
        }

        // Next find any sub-categories of this category.
        $categories = [];
        foreach ($gradecategories as $gradecategory) {
            if ($gradecategory->category->parent == $category) {
                if (is_object($record)) {
                    $items = $record->items;
                }
                $record = self::recurse_categorytree($gradecategory->category->id, $gradecategory->items, $items,
                $gradecategory->categories);
                $tmp = 0;
            }
        }

        // Add this all up.
        if (!is_object($record)) {
            $record = new \stdClass();
            $record->items = $items;
        }

        return $record;
    }

}
