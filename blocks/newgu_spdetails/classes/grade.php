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
            SELECT finalgrade, hidden FROM {grade_grades} WHERE itemid = :itemid AND userid = :userid',
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
                    $gradestatus->grade_status = get_string('status_graded', 'block_newgu_spdetails');
                    $gradestatus->status_text = get_string('status_text_graded', 'block_newgu_spdetails');
                    $gradestatus->status_class = get_string('status_class_graded', 'block_newgu_spdetails');
                    $gradestatus->grade_feedback = get_string('status_text_viewfeedback', 'block_newgu_spdetails');
                    $gradestatus->grade_feedback_link = $CFG->wwwroot . '/grade/report/index.php?id=' . $courseid;
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
                $scale = new \grade_scale($scaleparams, false);
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
     * For a given userid, return the current grading status for this assessment item.
     *
     * @param string $modulename
     * @param int $iteminstance
     * @param int $courseid
     * @param int $itemid
     * @param int $userid
     * @return array
     */
    public static function return_gradestatus(string $modulename, int $iteminstance, int $courseid, int $itemid,
    int $userid): array {
        global $DB, $CFG;

        $status = "";
        $statusclass = "";
        $statustext = "";
        $assessmenturl = "";
        $link = "";
        $duedate = 0;
        $allowsubmissionsfromdate = 0;
        $cutoffdate = 0;
        $gradingduedate = 0;
        $provisionalgrade = 0;
        $convertedgrade = 0;
        $provisional22grademaxpoint = 0;
        $converted22grademaxpoint = 0;
        $rawgrade = null;
        $finalgrade = null;

        $arrgrade = $DB->get_record_sql(
            "SELECT rawgrade,finalgrade FROM {grade_grades} WHERE itemid = :itemid AND userid = :userid",
            [
                'itemid' => $itemid,
                'userid' => $userid,
            ]
        );

        if (!empty($arrgrade)) {
            $rawgrade = (!empty($arrgrade->rawgrade) ? floor($arrgrade->rawgrade) : null);
            $finalgrade = (!empty($arrgrade->finalgrade) ? floor($arrgrade->finalgrade) : null);

            if (is_null($rawgrade) && !is_null($finalgrade)) {
                $provisionalgrade = $finalgrade;
            }
            if (!is_null($rawgrade) && is_null($finalgrade)) {
                $provisionalgrade = $rawgrade;
            }
        }

        $cmid = \block_newgu_spdetails\course::get_cmid($modulename, $courseid, $iteminstance);

        // Refactor this to allow any activity type to be parsed...
        switch ($modulename) {
            case "assign":
                $arrassign = $DB->get_record("assign", ["id" => $iteminstance]);
                $assessmenturl = $CFG->wwwroot . "/mod/assign/view.php?id=" . $cmid;

                if (!empty($arrassign)) {
                    $allowsubmissionsfromdate = $arrassign->allowsubmissionsfromdate;
                    $duedate = $arrassign->duedate;
                    $cutoffdate = $arrassign->cutoffdate;
                    $gradingduedate = $arrassign->gradingduedate;
                }

                if ($allowsubmissionsfromdate > time()) {
                    $status = get_string("status_submissionnotopen", "block_newgu_spdetails");
                    $statustext = get_string("status_text_submissionnotopen", "block_newgu_spdetails");
                }

                if ($status == "") {
                    $arrassignsubmission = $DB->get_record("assign_submission", [
                        "assignment" => $iteminstance,
                        "userid" => $userid,
                    ]);
                    $link = $CFG->wwwroot . "/mod/assign/view.php?id=" . $cmid;

                    if (!empty($arrassignsubmission)) {
                        $status = $arrassignsubmission->status;

                        if ($status == "new") {
                            $status = get_string("status_notsubmitted", "block_newgu_spdetails");
                            $statustext = get_string("status_text_notsubmitted", "block_newgu_spdetails");
                            $statusclass = get_string("status_class_notsubmitted", "block_newgu_spdetails");

                            if (time() > $duedate + (86400 * 30) && $duedate != 0) {
                                $status = get_string("status_overdue", "block_newgu_spdetails");
                                $statusclass = get_string("status_class_overdue", "block_newgu_spdetails");
                                $statustext = get_string("status_text_overdue", "block_newgu_spdetails");
                            }
                        }

                        if ($status == get_string("status_submitted", "block_newgu_spdetails")) {
                            $status = get_string("status_submitted", "block_newgu_spdetails");
                            $statusclass = get_string("status_class_submitted", "block_newgu_spdetails");
                            $statustext = get_string("status_text_submitted", "block_newgu_spdetails");
                            $link = '';

                            if ($finalgrade != null) {
                                $status = get_string("status_graded", "block_newgu_spdetails");
                                $statusclass = get_string("status_class_graded", "block_newgu_spdetails");
                                $statustext = get_string("status_text_graded", "block_newgu_spdetails");
                            }
                        }

                    } else {
                        $status = get_string("status_submit", "block_newgu_spdetails");
                        $statustext = get_string("status_text_submit", "block_newgu_spdetails");

                        if (time() > $duedate && $duedate != 0) {
                            $status = get_string("status_notsubmitted", "block_newgu_spdetails");
                            $statustext = get_string("status_text_notsubmitted", "block_newgu_spdetails");
                        }

                        if (time() > $duedate + (86400 * 30) && $duedate != 0) {
                            $status = get_string("status_overdue", "block_newgu_spdetails");;
                            $statusclass = get_string("status_class_overdue", "block_newgu_spdetails");
                            $statustext = get_string("status_text_overdue", "block_newgu_spdetails");
                        }
                    }
                }
                break;

            case "forum":
                $forumsubmissions = $DB->count_records("forum_discussion_subs", ["forum" => $iteminstance, "userid" => $userid]);
                $assessmenturl = $CFG->wwwroot . "/mod/forum/view.php?id=" . $cmid;

                if ($forumsubmissions > 0) {
                    $status = get_string("status_submitted", "block_newgu_spdetails");
                    $statusclass = get_string("status_class_submitted", "block_newgu_spdetails");
                    $statustext = get_string("status_text_submitted", "block_newgu_spdetails");
                } else {
                    $status = get_string("status_submit", "block_newgu_spdetails");
                    $statusclass = get_string("status_class_submit", "block_newgu_spdetails");
                    $statustext = get_string("status_text_submit", "block_newgu_spdetails");
                    $link = $CFG->wwwroot . "/mod/forum/view.php?id=" . $cmid;
                }
                break;

            case "quiz":
                $assessmenturl = $CFG->wwwroot . "/mod/quiz/view.php?id=" . $cmid;

                $quizattempts = $DB->count_records("quiz_attempts", [
                    "quiz" => $iteminstance,
                    "userid" => $userid,
                    "state" => "finished",
                ]);
                if ($quizattempts > 0) {
                    $status = get_string("status_submitted", "block_newgu_spdetails");
                    $statusclass = get_string("status_class_submitted", "block_newgu_spdetails");
                    $statustext = get_string("status_text_submitted", "block_newgu_spdetails");
                } else {
                    $status = get_string("status_submit", "block_newgu_spdetails");
                    $statusclass = get_string("status_class_submit", "block_newgu_spdetails");
                    $statustext = get_string("status_text_submit", "block_newgu_spdetails");
                    $link = $CFG->wwwroot . "/mod/quiz/view.php?id=" . $cmid;
                }
                break;

            case "workshop":
                $arrworkshop = $DB->get_record("workshop", [
                    "id" => $iteminstance,
                ]);
                $assessmenturl = $CFG->wwwroot . "/mod/workshop/view.php?id=" . $cmid;

                $workshopsubmissions = $DB->count_records("workshop_submissions", [
                    "workshopid" => $iteminstance,
                    "authorid" => $userid,
                ]);
                if ($workshopsubmissions > 0) {
                    $status = get_string("status_submitted", "block_newgu_spdetails");
                    $statusclass = get_string("status_class_submitted", "block_newgu_spdetails");
                    $statustext = get_string("status_text_submitted", "block_newgu_spdetails");
                } else {
                    $status = get_string("status_submit", "block_newgu_spdetails");
                    $statusclass = get_string("status_class_submit", "block_newgu_spdetails");
                    $statustext = get_string("status_text_submit", "block_newgu_spdetails");
                    if ($arrworkshop->submissionstart == 0) {
                        $status = get_string("status_submissionnotopen", "block_newgu_spdetails");
                        $statusclass = "";
                        $statustext = get_string("status_text_submissionnotopen", "block_newgu_spdetails");
                    }
                    $link = $CFG->wwwroot . "/mod/workshop/view.php?id=" . $cmid;
                }
                break;

            default :
            break;
        }

        if ($rawgrade > 0 && ($finalgrade == null || $finalgrade == 0)) {
            $provisional22grademaxpoint = self::return_22grademaxpoint($rawgrade - 1, 1);
        }

        if ($finalgrade > 0) {
            $converted22grademaxpoint = self::return_22grademaxpoint($finalgrade - 1, 1);
        }

        $gradestatus = [
            "status" => $status,
            "status_class" => $statusclass,
            "status_text" => $statustext,
            "assessmenturl" => $assessmenturl,
            "link" => $link,
            "allowsubmissionsfromdate" => $allowsubmissionsfromdate,
            "duedate" => $duedate,
            "cutoffdate" => $cutoffdate,
            "rawgrade" => $rawgrade,
            "finalgrade" => $finalgrade,
            "gradingduedate" => $gradingduedate,
            "provisionalgrade" => $provisionalgrade,
            "convertedgrade" => $convertedgrade,
            "provisional_22grademaxpoint" => $provisional22grademaxpoint,
            "converted_22grademaxpoint" => $converted22grademaxpoint,
        ];

        return $gradestatus;
    }

    /**
     * Returns a corresponding value for grades with gradetype = "value" and grademax = "22"
     *
     * @param int $grade
     * @param int $idnumber = 1 - Schedule A, 2 - Schedule B
     * @return string 22-grade max point value
     */
    public static function return_22grademaxpoint($grade, $idnumber) {
        $values = ['H', 'G2', 'G1', 'F3', 'F2', 'F1', 'E3', 'E2', 'E1', 'D3', 'D2', 'D1',
            'C3', 'C2', 'C1', 'B3', 'B2', 'B1', 'A5', 'A4', 'A3', 'A2', 'A1'];
        if ($grade <= 22) {
            $value = $values[$grade];
            if ($idnumber == 2) {
                $stringarray = str_split($value);
                if ($stringarray[0] != 'H') {
                    $value = $stringarray[0] . '0';
                }
            }
            return $value;
        } else {
            return "";
        }
    }

    /**
     * Method to return grading feedback.
     *
     * @param string $modulename
     * @param int $iteminstance
     * @param int $courseid
     * @param int $itemid
     * @param int $userid
     * @param int $grademax
     * @param string $gradetype
     * @return array
     */
    public static function get_gradefeedback(string $modulename, int $iteminstance, int $courseid, int $itemid, int $userid,
    int $grademax, string $gradetype) {
        global $CFG;

        $link = "";
        $gradetodisplay = "";
        $gradestatus = self::return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $userid);
        $status = $gradestatus["status"];
        $link = $gradestatus["link"];
        $allowsubmissionsfromdate = $gradestatus["allowsubmissionsfromdate"];
        $duedate = $gradestatus["duedate"];
        $cutoffdate = $gradestatus["cutoffdate"];
        $gradingduedate = $gradestatus["gradingduedate"];
        $rawgrade = $gradestatus["rawgrade"];
        $finalgrade = $gradestatus["finalgrade"];
        $provisional22grademaxpoint = $gradestatus["provisional_22grademaxpoint"];
        $converted22grademaxpoint = $gradestatus["converted_22grademaxpoint"];

        $cmid = \block_newgu_spdetails\course::get_cmid($modulename, $courseid, $iteminstance);

        if ($finalgrade != null) {

            // I think this is meant to have been 'scale' type and not
            // 'grade' type. This code seems to be trying to determine
            // whether to use the 22 point 'scale' from the grade 'type'
            // i.e. scale, point or none.
            // It should really use the grade 'type' to determine if it
            // is scale/point or none. If it's set to 'point', return just
            // the point 'value' of the grade (20, out of 100 for example).
            // If it's been set to scale, use the scaleid to derive the scale
            // values from mdl_scale and *then* map the final grade to the
            // scale value.
            switch ($gradetype) {
                case 1:
                    $gradetodisplay = number_format((float)$finalgrade) . " / " . number_format((float)$grademax);
                    break;

                case 2:
                    $gradetodisplay = $converted22grademaxpoint;
                    break;
            }

            $link = $CFG->wwwroot . '/mod/'.$modulename.'/view.php?id=' . $cmid . '#page-footer';
        }

        if ($finalgrade == null  && $duedate < time()) {
            if ($status == "notopen" || $status == "notsubmitted") {
                $gradetodisplay = get_string("feedback_tobeconfirmed", "block_newgu_spdetails");
                $link = "";
            }
            if ($status == "overdue") {
                $gradetodisplay = get_string("status_text_overdue", "block_newgu_spdetails");
                $link = "";
            }
            if ($status == "notsubmitted") {
                $gradetodisplay = get_string("status_text_notsubmitted", "block_newgu_spdetails");
                if ($gradingduedate > time()) {
                    $gradetodisplay = "Due " . date("d/m/Y", $gradingduedate);
                }
            }
        }

        if ($status == "submit") {
            $gradetodisplay = get_string("feedback_tobeconfirmed", "block_newgu_spdetails");
            $link = "";
        }

        return [
            "gradetodisplay" => $gradetodisplay,
            "link" => $link,
            "provisional_22grademaxpoint" => $provisional22grademaxpoint,
            "converted_22grademaxpoint" => $converted22grademaxpoint,
            "finalgrade" => $finalgrade,
            "rawgrade" => $rawgrade,
        ];
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
