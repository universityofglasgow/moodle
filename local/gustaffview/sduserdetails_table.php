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
 * This class implements Moodle's Table API in order to provide assessment
 * data for a given student. Called via the standard web service approach.
 *
 * @package    local_gustaffview
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_newgu_spdetails\activities\assign_activity;
use block_newgu_spdetails\activities\forum_activity;
use block_newgu_spdetails\activities\h5pactivity_activity;
use block_newgu_spdetails\activities\hvp_activity;
use block_newgu_spdetails\activities\kalvidassign_activity;
use block_newgu_spdetails\activities\lesson_activity;
use block_newgu_spdetails\activities\lti_activity;
use block_newgu_spdetails\activities\peerwork_activity;
use block_newgu_spdetails\activities\questionnaire_activity;
use block_newgu_spdetails\activities\quiz_activity;
use block_newgu_spdetails\activities\scorm_activity;
use block_newgu_spdetails\activities\workshop_activity;

require_once(dirname(dirname(__FILE__)).'../../config.php');
global $CFG;

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/grade/constants.php');
require_once($CFG->libdir . '/grade/grade_category.php');
require_once($CFG->libdir . '/grade/grade_item.php');

class sduserdetailscurrent_table extends table_sql
{
    /**
     * Constructor
     * @param int $unequeid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($unequeid)
    {
        parent::__construct($unequeid);

        // Define the list of columns to show.
        $columns = [
            'assessment',
            'assessmenttype',
            'weight',
            'itemmodule',
            'duedate',
            'source',
            'status',
            'grade',
            'feedback'
        ];

        $this->collapsible(false);
        $this->define_columns($columns);

        $headers = [
            get_string('assessment'),
            get_string('assessmenttype','block_newgu_spdetails'),
            get_string('weight', 'block_newgu_spdetails'),
            get_string('activity'),
            get_string('duedate', 'block_newgu_spdetails'),
            get_string('source', 'block_newgu_spdetails'),
            get_string('status'),
            get_string('grade', 'local_gustaffview'),
            get_string('feedback')
        ];
        $this->define_headers($headers);
    }

    /**
     * @param $values
     * @return void
     */
    function col_assessment($values){
        global $CFG;
        $link = '';
        if ($values->itemtype == 'manual') {
            $userid = $values->userid;
            $manualitem = \block_newgu_spdetails\activity::process_manual_grade_item($values, 'current', '', $userid);
            $link = true;
            $itemname = $manualitem->item_name;
        } else {
            $itemname = $values->itemname;
            $modulename = $values->itemmodule;
            $iteminstance = $values->iteminstance;
            $courseid = $values->courseid;
            $cmid = \block_newgu_spdetails\course::get_cmid($modulename, $courseid, $iteminstance);
            $link = $CFG->wwwroot . '/mod/' . $modulename . '/view.php?id=' . $cmid;
        }

        if (!empty($link)) {
            return $itemname;
        }

        return false;
    }

    /**
     * @param $values
     * @return mixed
     */
    function col_assessmenttype($values){
        $courseid = $values->courseid;
        $categoryid = $values->categoryid;
        $gradecategory = grade_category::fetch(['id' => $categoryid]);

        // The assessment type is derived from the parent - which works only
        // as long as the parent name contains 'Formative' or 'Summative'.
        $item = grade_item::fetch(['courseid' => $courseid, 'iteminstance' => $categoryid, 'itemtype' => 'category']);
        if (!$item) {
            $item = grade_item::fetch(['courseid' => $courseid, 'iteminstance' => $categoryid, 'itemtype' => 'course']);
        }
        $assessmenttype = \block_newgu_spdetails\course::return_assessmenttype($gradecategory->fullname, $item->aggregationcoef);

        return $assessmenttype;
    }

    function col_weight($values){  
        $userid = $values->userid;
        $courseid = $values->courseid;
        $itemid = $values->id;
        $itemweight = $values->aggregationcoef;
        $gradecategory = new stdClass();
        $gradecategory->aggregationcoef = $itemweight;
        $gradecategory->aggregation = $values->aggregation;
        $finalweight = '-';
        $mygradesenabled = \block_newgu_spdetails\course::is_type_mygrades($courseid);

        if ($mygradesenabled) {
            $isgradereleased = \local_gugrades\grades::is_grades_released($courseid, $itemid);
            if ($isgradereleased) {
                $releasedgrade = \local_gugrades\grades::get_released_grade($courseid, $itemid, $userid);
                if ($releasedgrade) {
                    // If we're using a weighted strategy with a drop the lowest [n] configuration, don't display the weight.
                    if (!$releasedgrade->dropped) {
                        $rawassessmentweight = (
                            ($releasedgrade->normalisedweight > 0) ? \block_newgu_spdetails\course::return_weight(
                                $releasedgrade->normalisedweight)
                            : (($releasedgrade->weightedgrade > 0) ? \block_newgu_spdetails\course::return_weight(
                                $releasedgrade->weightedgrade) : (($itemweight > 0) ? \block_newgu_spdetails\course::return_weight(
                                    $itemweight) : '-')));
                        $finalweight = (($rawassessmentweight > 0) ? $rawassessmentweight . "%" : "-");
                    }
                }
            } else {
                // Fallback to whatever is in gradebook.
                $item = \grade_item::fetch(['courseid' => $courseid, 'id' => $itemid]);
                if ($item) {
                    $weighttowardscourse = \block_newgu_spdetails\course::get_grade_category_weight($item, $gradecategory);
                    $displayweights = \block_newgu_spdetails\activity::get_display_activity_item_weights($weighttowardscourse,
                        $gradecategory);
                    if ($displayweights) {
                        $rawassessmentweight = \block_newgu_spdetails\course::return_weight($itemweight);
                        $finalweight = (($rawassessmentweight > 0) ? $rawassessmentweight . "%" : "-");
                    }
                }
            }
        } elseif (!$mygradesenabled) {
            $item = \grade_item::fetch(['courseid' => $courseid, 'id' => $itemid]);
            if ($item) {
                $weighttowardscourse = \block_newgu_spdetails\course::get_grade_category_weight($item, $gradecategory);
                $displayweights = \block_newgu_spdetails\activity::get_display_activity_item_weights($weighttowardscourse,
                    $gradecategory);
                if ($displayweights) {
                    $rawassessmentweight = \block_newgu_spdetails\course::return_weight($itemweight);
                    $finalweight = (($rawassessmentweight > 0) ? $rawassessmentweight . "%" : "-");
                }
            }
        }

        return $finalweight;
      }

    /**
     * @param $values
     * @return mixed
     */
    function col_itemmodule($values){
        if ($values->itemtype == 'manual') {
            return get_string('manualitem', 'local_gustaffview');
        } else {
            return ucfirst($values->itemmodule);
        }

        return '';
    }

    /**
     * @param $values
     * @return string
     */
    function col_duedate($values){
        $userid = $values->userid;
        $modulename = $values->itemmodule;
        $gradeitemid = $values->id;
        $courseid = $values->courseid;
        $duedate = 'N/A';

        if ($modulename != '') {
            switch($modulename) {
                case 'assign':
                    $activity = new assign_activity($gradeitemid, $courseid, 0);
                    break;
                case 'forum':
                    $activity = new forum_activity($gradeitemid, $courseid, 0);
                    break;

                case 'h5pactivity':
                    $activity = new h5pactivity_activity($gradeitemid, $courseid, 0);
                    break;
                case 'hvp':
                    $activity = new hvp_activity($gradeitemid, $courseid, 0);
                    break;
                case 'kalvidassign':
                    $activity = new kalvidassign_activity($gradeitemid, $courseid, 0);
                    break;
                case 'lesson':
                    $activity = new lesson_activity($gradeitemid, $courseid, 0);
                    break;
                case 'lti':
                    $activity = new lti_activity($gradeitemid, $courseid, 0);
                    break;
                case 'peerwork':
                    $activity = new peerwork_activity($gradeitemid, $courseid, 0);
                    break;
                case 'quiz':
                    $activity = new quiz_activity($gradeitemid, $courseid, 0);
                    break;
                case 'questionnaire':
                    $activity = new questionnaire_activity($gradeitemid, $courseid, 0);
                    break;
                case 'scorm':
                    $activity = new scorm_activity($gradeitemid, $courseid, 0);
                    break;
                case 'workshop':
                    $activity = new workshop_activity($gradeitemid, $courseid, 0);
                    break;
                default:
                    $duedate = get_string('noduedate', 'block_newgu_spdetails');
                    break;
            }

            if ($activity) {
                $status = $activity->get_status($userid);
                $duedate = $status->due_date;
            }
        }

        return $duedate;
    }

    /**
     * @param $values
     * @return string
     */
    function col_source($values){
        $courseid = $values->courseid;
        $itemid = $values->id;
        $userid = $values->userid;
        $mygradesenabled = \block_newgu_spdetails\course::is_type_mygrades($courseid);
        
        if ($mygradesenabled) {
            $gradesreleased = \local_gugrades\grades::is_grades_released($courseid, $itemid);
            if ($gradesreleased) {
                $releasegrade = \local_gugrades\grades::get_released_grade($courseid, $itemid, $userid);
                if ($releasegrade) {
                    return get_string('mygradesenabled', 'local_gustaffview');
                } else {
                    // Fallback to whatever is in gradebook.
                    return get_string('regulargradebook', 'local_gustaffview');
                }
            } else {
                // Fallback to whatever is in gradebook.
                return get_string('regulargradebook', 'local_gustaffview');
            }
        } elseif (!$mygradesenabled) {
            return get_string('regulargradebook', 'local_gustaffview');
        }
    }

    /**
     * @param $values
     * @return string
     */
    function col_status($values){
        $userid = $values->userid;
        $courseid = $values->courseid;
        $itemid = $values->id;
        $gradetype = $values->gradetype;
        $scaleid = $values->scaleid;
        $grademax = $values->grademax;
        $statustodisplay = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $mygradesenabled = \block_newgu_spdetails\course::is_type_mygrades($courseid);

        if ($mygradesenabled) {
            $gradesreleased = \local_gugrades\grades::is_grades_released($courseid, $itemid);
            if ($gradesreleased) {
                $statustodisplay = "<span class='status-item status-graded'>" . get_string('status_text_graded',
                    'block_newgu_spdetails') . "</span>";
            } else {
                // Fallback to whatever is in gradebook.
                if ($values->itemtype == 'manual') {
                    $gradestatus = \block_newgu_spdetails\grade::get_manual_grade_item_grade_status_and_feedback($courseid, $itemid,
                        $userid, $gradetype, $scaleid, $grademax);
                    if ($gradestatus) {
                        $statustodisplay = "<span class='status-item " . $gradestatus->status_class . "'>" . $gradestatus->status_text .
                        "</span>";
                    }
                } else {
                    $gradestatus = \block_newgu_spdetails\grade::get_grade_status_and_feedback($courseid, $itemid, $userid, $gradetype,
                        $scaleid, $grademax, '');
                    if ($gradestatus) {
                        $statustodisplay = "<span class='status-item " . $gradestatus->status_class . "'>" . $gradestatus->status_text .
                        "</span>";
                    }
                }
            }
        } elseif (!$mygradesenabled) {
            if ($values->itemtype == 'manual') {
                $gradestatus = \block_newgu_spdetails\grade::get_manual_grade_item_grade_status_and_feedback($courseid, $itemid,
                    $userid, $gradetype, $scaleid, $grademax);
                if ($gradestatus) {
                    $statustodisplay = "<span class='status-item " . $gradestatus->status_class . "'>" . $gradestatus->status_text .
                    "</span>";
                }
            } else {
                $gradestatus = \block_newgu_spdetails\grade::get_grade_status_and_feedback($courseid, $itemid, $userid, $gradetype,
                    $scaleid, $grademax, '');
                if ($gradestatus) {
                    $statustodisplay = "<span class='status-item " . $gradestatus->status_class . "'>" . $gradestatus->status_text .
                    "</span>";
                }
            }
        }

        return $statustodisplay;
    }

    /**
     * @param $values
     * @return mixed
     */
    function col_grade($values){
        $userid = $values->userid;
        $courseid = $values->courseid;
        $itemid = $values->id;
        $mygradesenabled = \block_newgu_spdetails\course::is_type_mygrades($courseid);
        $gradetodisplay = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $fallbacktogradebook = false;

        if ($mygradesenabled) {
            $gradesreleased = \local_gugrades\grades::is_grades_released($courseid, $itemid);
            if ($gradesreleased) {
                $hidden_or_locked = \local_gugrades\grades::is_grade_hidden_locked($itemid);
                if ($hidden_or_locked[0] != 1 && $hidden_or_locked[1] != 1) {
                    $releasedgrade = \local_gugrades\grades::get_released_grade($courseid, $itemid, $userid);
                    if ($releasedgrade) {
                        $gradetodisplay = "<span class='status-graded'><strong>";
                        $gradetodisplay .= \block_newgu_spdetails\grade::is_admin_or_generic_grade($releasedgrade->admingrade,
                            $releasedgrade->displaygrade);
                        $gradetodisplay .= "<strong></span>";
                    } else {
                        $fallbacktogradebook = true;
                    }
                } else {
                    $fallbacktogradebook = true;
                }
            } elseif (!$gradesreleased) {
                $fallbacktogradebook = true;
            }
        } elseif (!$mygradesenabled) {
            $fallbacktogradebook = true;
        }

        // MGU-1152 - Default to using whatever was added/released in Gradebook.
        if ($fallbacktogradebook) {
            $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
            if ($values->itemtype == 'manual') {
                $activitydata[] = \block_newgu_spdetails\activity::process_manual_grade_item($values, 'current', '', $userid);
            } else {
                $activitydata = \block_newgu_spdetails\activity::process_default_items([$values], 'current', $ltiactivities, '',
                false, $userid);
            }
            $opentag = '';
            $closetag = '';
            if ($activitydata[0]->grade_status == get_string('status_graded', 'block_newgu_spdetails') &&
            $activitydata[0]->grade != get_string('status_text_tobeconfirmed', 'block_newgu_spdetails')) {
                $opentag = "<span class='status-graded'><strong>";
                $closetag = "<strong></span>";
            }
            $activitygrade = $activitydata[0]->grade;
            if ($activitydata[0]->grade != get_string('status_text_tobeconfirmed', 'block_newgu_spdetails')) {
                if (is_numeric($activitydata[0]->grade)) {
                    $activitygrade = \block_newgu_spdetails\grade::get_formatted_grade_from_grade_type($activitydata[0]->grade,
                        $values->gradetype, $values->scaleid, $values->grademax);
                }
            }
            $gradetodisplay = $opentag . $activitygrade . $closetag;
        }

        return $gradetodisplay;
    }

    /**
     * @param $values
     * @return mixed
     */
    function col_feedback($values){
        $userid = $values->userid;
        $courseid = $values->courseid;
        $itemid = $values->id;
        $gradetype = $values->gradetype;
        $scaleid = $values->scaleid;
        $grademax = $values->grademax;
        $mygradesenabled = \block_newgu_spdetails\course::is_type_mygrades($courseid);
        $processmanualitemfeedback = false;
        $processgradebookfeedback = false;
        $feedback = '-';

        if ($mygradesenabled) {
            $gradesreleased = \local_gugrades\grades::is_grades_released($courseid, $itemid);
            if ($gradesreleased) {
                $releasegrade = \local_gugrades\grades::get_released_grade($courseid, $itemid, $userid);
                // Not sure what needs to be displayed here as the spec doesn't define things clearly enough.
                $feedback = $releasegrade->auditcomment;
            } else {
                // Fallback to whatever is in gradebook.
                if ($values->itemtype == 'manual') {
                    $processmanualitemfeedback = true;
                } else {
                    $processgradebookfeedback = true;
                }
            }
        } elseif (!$mygradesenabled) {
            if ($values->itemtype == 'manual') {
                $processmanualitemfeedback = true;
            } else {
                $processgradebookfeedback = true;
            }
        }

        if ($processmanualitemfeedback) {
            $manualgradefeedback = \block_newgu_spdetails\grade::get_manual_grade_item_grade_status_and_feedback($courseid,
                $itemid,
                $userid,
                $gradetype,
                $scaleid,
                $grademax
            );
            if ($manualgradefeedback) {
                $feedback = $manualgradefeedback->grade_feedback;
                $manualgradefeedbacklink = $manualgradefeedback->grade_feedback_link;
                if ($manualgradefeedbacklink) {
                    $feedback = '<a href="' . $manualgradefeedbacklink . '">' . $manualgradefeedback->grade_feedback . '</a>';
                }
            }
        }

        if ($processgradebookfeedback) {
            $gradefeedback = \block_newgu_spdetails\grade::get_grade_status_and_feedback($courseid, $itemid, $userid, $gradetype,
                $scaleid, $grademax, '');
            if ($gradefeedback) {
                $feedback = $gradefeedback->grade_feedback;
                $gradefeedbacklink = $gradefeedback->grade_feedback_link;
                if ($gradefeedbacklink) {
                    $feedback = '<a href="' . $gradefeedbacklink . '">' . $gradefeedback->grade_feedback . '</a>';
                }
            }
        }

        return $feedback;
    }
}
