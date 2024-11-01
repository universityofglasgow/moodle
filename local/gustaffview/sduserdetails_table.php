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

        $tdr = optional_param('tdr', '', PARAM_INT);
        $ts = optional_param('ts', '', PARAM_ALPHA);
        $page = optional_param('page', 0, PARAM_INT);
        $tdrnew = 4;
        $tdirdd_icon = '';
        $tdirat_icon = '';
        $tdiract_icon = '';

        switch($ts) {
            case 'assessmenttype':
                $tdirat_icon = ' <i class="fa fa-caret-';
                switch ($tdr) {
                    case 3:
                        $tdirat_icon .= 'up';
                        break;
                    case 4:
                        $tdirat_icon .= 'down';
                        $tdrnew = 3;
                        break;

                }
                $tdirat_icon .= '" data-ts="assessmenttype" data-tdr="' . $tdrnew . '"></i>';
                break;

            case 'itemmodule':
                $tdiract_icon = ' <i class="fa fa-caret-';
                switch ($tdr) {
                    case 3:
                        $tdiract_icon .= 'up';
                        break;
                    case 4:
                        $tdiract_icon .= 'down';
                        $tdrnew = 3;
                        break;

                }
                $tdiract_icon .= '" data-ts="itemmodule" data-tdr="' . $tdrnew . '"></i>';
                break;

            case 'duedate':
                $tdirdd_icon = ' <i class="fa fa-caret-';
                switch($tdr) {
                    case 3:
                        $tdirdd_icon .= 'up';
                    break;

                    case 4:
                        $tdirdd_icon .= 'down';
                        $tdrnew = 3;
                    break;
                }
                $tdirdd_icon .= '" data-ts="duedate" data-tdr="' . $tdrnew . '"></i>';
                break;
        }

        $headers = [
            get_string('assessment'),
            '<a data-page="' . $page . '" data-ts="assessmenttype" data-tdr="' . $tdrnew . '" href="#">' .
            get_string('assessmenttype','block_newgu_spdetails') . $tdirat_icon . '</a>',
            get_string('weight', 'block_newgu_spdetails'),
            '<a data-page="' . $page . '" data-ts="itemmodule" data-tdr="' . $tdrnew . '" href="#">' . get_string('activity') .
            $tdiract_icon . '</a>',
            '<a data-page="' . $page . '" data-ts="duedate" data-tdr="' . $tdrnew . '" href="#">' . get_string('duedate',
                'block_newgu_spdetails') . $tdirdd_icon . '</a>',
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

        $itemname = $values->itemname;
        $modulename = $values->itemmodule;
        $iteminstance = $values->iteminstance;
        $courseid = $values->courseid;
        $cmid = \block_newgu_spdetails\course::get_cmid($modulename, $courseid, $iteminstance);
        $link = $CFG->wwwroot . '/mod/' . $modulename . '/view.php?id=' . $cmid;

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
        if (!$item = grade_item::fetch(['courseid' => $courseid, 'iteminstance' => $categoryid, 'itemtype' => 'category'])) {
            $item = grade_item::fetch(['courseid' => $courseid, 'iteminstance' => $categoryid, 'itemtype' => 'course']);
        }
        $assessmenttype = \block_newgu_spdetails\course::return_assessmenttype($gradecategory->fullname, $item->aggregationcoef);

        return $assessmenttype;
    }

    function col_weight($values){  
        $userid = $values->userid;
        $courseid = $values->courseid;
        $itemid = $values->id;
        $categoryid = $values->categoryid;
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
                // If we're using a weighted strategy with a drop the lowest [n] configuration, don't display the weight.
                if (!$releasedgrade->dropped) {
                    $rawassessmentweight = (
                        ($releasedgrade->normalisedweight > 0) ? \block_newgu_spdetails\course::return_weight(
                            $releasedgrade->normalisedweight)
                        : (($releasedgrade->weightedgrade > 0) ? \block_newgu_spdetails\course::return_weight(
                            $releasedgrade->weightedgrade) : (($itemweight > 0) ? \block_newgu_spdetails\course::return_weight(
                                $itemweight) : '-')));
                    $finalweight = (($rawassessmentweight > 0) ? $rawassessmentweight . "%" : "-");
                } else {
                    $finalweight = '-';
                }
            } else {
                // Fallback to whatever is in gradebook.
                if ($item = \grade_item::fetch(['courseid' => $courseid, 'id' => $itemid])) {
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
            if ($item = \grade_item::fetch(['courseid' => $courseid, 'id' => $itemid])) {
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
        return ucfirst($values->itemmodule);
    }

    /**
     * @param $values
     * @return string
     */
    function col_duedate($values){
        global $DB;

        $userid = $values->userid;
        $modulename = $values->itemmodule;
        $gradeitemid = $values->id;
        $courseid = $values->courseid;
        $duedate = 0;

        if ($modulename!="") {
            switch($modulename) {
                case "assign":
                    $activity = new assign_activity($gradeitemid, $courseid, 0);
                    break;
                case "forum":
                    $activity = new forum_activity($gradeitemid, $courseid, 0);
                    break;

                case "h5pactivity":
                    $activity = new h5pactivity_activity($gradeitemid, $courseid, 0);
                    break;
                case "hvp":
                    $activity = new hvp_activity($gradeitemid, $courseid, 0);
                    break;
                case "kalvidassign":
                    $activity = new kalvidassign_activity($gradeitemid, $courseid, 0);
                    break;
                case "lesson":
                    $activity = new lesson_activity($gradeitemid, $courseid, 0);
                    break;
                case "lti":
                    $activity = new lti_activity($gradeitemid, $courseid, 0);
                    break;
                case "peerwork":
                    $activity = new peerwork_activity($gradeitemid, $courseid, 0);
                    break;
                case "quiz":
                    $activity = new quiz_activity($gradeitemid, $courseid, 0);
                    break;
                case "questionnaire":
                    $activity = new questionnaire_activity($gradeitemid, $courseid, 0);
                    break;
                case "scorm":
                    $activity = new scorm_activity($gradeitemid, $courseid, 0);
                    break;
                case "workshop":
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
                $gradestatus = \block_newgu_spdetails\grade::get_grade_status_and_feedback($courseid, $itemid, $userid, $gradetype,
                    $scaleid, $grademax, '');
                $statustodisplay = "<span class='status-item " . $gradestatus->status_class . "'>" . $gradestatus->status_text .
                "</span>";
            }
        } elseif (!$mygradesenabled) {
            $gradestatus = \block_newgu_spdetails\grade::get_grade_status_and_feedback($courseid, $itemid, $userid, $gradetype,
                $scaleid, $grademax, '');
            $statustodisplay = "<span class='status-item " . $gradestatus->status_class . "'>" . $gradestatus->status_text .
            "</span>";
        }

        return $statustodisplay;
    }

    /**
     * @param $values
     * @return mixed
     */
    function col_grade($values){
        $userid = $values->userid;
        $modulename = $values->itemmodule;
        $iteminstance = $values->iteminstance;
        $courseid = $values->courseid;
        $itemid = $values->id;
        $gradetype = $values->gradetype;
        $mygradesenabled = \block_newgu_spdetails\course::is_type_mygrades($courseid);
        $gradetodisplay = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');

        if ($mygradesenabled) {
            $gradesreleased = \local_gugrades\grades::is_grades_released($courseid, $itemid);
            if ($gradesreleased) {
                $hidden_or_locked = \local_gugrades\grades::is_grade_hidden_locked($itemid);
                if ($hidden_or_locked[0] != 1 && $hidden_or_locked[1] != 1) {
                    $releasedgrade = \local_gugrades\grades::get_released_grade($courseid, $itemid, $userid);
                    $gradetodisplay = "<span class='status-graded'><strong>";
                    $gradetodisplay .= \block_newgu_spdetails\grade::is_admin_or_generic_grade($releasedgrade->admingrade,
                        $releasedgrade->displaygrade);
                    $gradetodisplay .= "<strong></span>";
                }
            }
        } elseif (!$mygradesenabled) {
            $arr_gradetodisplay = \block_newgu_spdetails\grade::get_gradefeedback($modulename, $iteminstance, $courseid, $itemid,
                $userid, $values->grademax, $gradetype);
            $gradetodisplay = $arr_gradetodisplay["gradetodisplay"];
        }

        return $gradetodisplay;
    }

    /**
     * @param $values
     * @return mixed
     */
    function col_feedback($values){
        $userid = $values->userid;
        $modulename = $values->itemmodule;
        $iteminstance = $values->iteminstance;
        $courseid = $values->courseid;
        $itemid = $values->id;
        $gradetype = $values->gradetype;
        $scaleid = $values->scaleid;
        $grademax = $values->grademax;
        $mygradesenabled = \block_newgu_spdetails\course::is_type_mygrades($courseid);
        $feedback = '-';

        if ($mygradesenabled) {
            $gradesreleased = \local_gugrades\grades::is_grades_released($courseid, $itemid);
            if ($gradesreleased) {
                $releasegrade = \local_gugrades\grades::get_released_grade($courseid, $itemid, $userid);
                $feedback = $releasegrade->auditcomment;
            } else {
                // Fallback to whatever is in gradebook.
                $gradefeedback = \block_newgu_spdetails\grade::get_grade_status_and_feedback($courseid, $itemid, $userid,
                    $gradetype, $scaleid, $grademax, '');
                $feedback = $gradefeedback->grade_feedback;
            }
        } elseif (!$mygradesenabled) {
            $gradefeedback = \block_newgu_spdetails\grade::get_grade_status_and_feedback($courseid, $itemid, $userid, $gradetype,
                $scaleid, $grademax, '');
            $feedback = $gradefeedback->grade_feedback;
        }

        return $feedback;
    }
}
