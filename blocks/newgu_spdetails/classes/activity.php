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
 * Provides generic activity related methods.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails;

use local_gugrades\api;
use block_newgu_spdetails\activities\default_activity;
use block_newgu_spdetails\course;
use block_newgu_spdetails\grade;
use grade_category;
use grade_item;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/grade/constants.php');
require_once($CFG->libdir . '/grade/grade_category.php');
require_once($CFG->libdir . '/grade/grade_item.php');

define('ITEM_URL', $CFG->wwwroot . '/');
define('ITEM_SCRIPT', '/view.php?id=');

/**
 * This class processes activities for MyGrades, and Gradebook course types.
 *
 * It provides a factory method for instantiating the relevant activity which can
 * then be used to provide further functionality.
 */
class activity {

    /**
     * Main method called from the API.
     *
     * @param int $subcategory
     * @param int $userid
     * @param string $activetab
     * @param string $sortby
     * @param string $sortorder
     * @return array
     */
    public static function get_activityitems(int $subcategory, int $userid, string $activetab, string $sortby,
    string $sortorder): array {
        $activitydata = [];
        $coursedata = [];

        // What's my parent?
        // I need the parent of the parent in order to be able to always
        // step 'up' a level. \local_gugrades\grades::get_activitytree only
        // gives me the parent id, which breaks our mechanism.
        $subcat = grade_category::fetch(['id' => $subcategory]);
        $parent = grade_category::fetch(['id' => $subcat->parent]);
        if ($parent->parent == null) {
            $parentid = 0;
        } else {
            $parentid = $parent->id;
        }
        $activitydata['parent'] = $parentid;

        $courseid = $subcat->courseid;

        $course = get_course($courseid);
        $coursedata['coursename'] = $course->shortname;
        $coursedata['subcatfullname'] = ($subcat->fullname != '?' ? $subcat->fullname : '');

        // The assessment type is derived from the parent - which works only
        // as long as the parent name contains 'Formative' or 'Summative'.
        if (!$item = grade_item::fetch(['courseid' => $course->id, 'iteminstance' => $subcategory, 'itemtype' => 'category'])) {
            $item = grade_item::fetch(['courseid' => $course->id, 'iteminstance' => $subcategory, 'itemtype' => 'course']);
        }
        $assessmenttype = course::return_assessmenttype($subcat->fullname, $item->aggregationcoef);

        // The weight for this grade (sub)category is derived from the aggregation
        // coefficient value of the grade item, only if it's been set in the gradebook however.
        $weight = course::return_weight($item->aggregationcoef);
        $coursedata['weight'] = $weight . '%';

        // We don't need the status column for past courses.
        $coursedata['hidestatuscol'] = (($activetab == 'past') ? true : false);

        // We'll need to merge these next two arrays at some point, to allow the sorting to
        // to work on all items, rather than just by category/activity item as it currently does.
        $activities = api::get_activities($course->id, $subcategory);
        $activitiesdata = self::process_get_activities($activities, $course->id, $subcategory, $userid, $activetab,
        $assessmenttype, $sortby, $sortorder);
        $coursedata['subcategories'] = ((array_key_exists('subcategories', $activitiesdata)) ?
        $activitiesdata['subcategories'] : '');
        $coursedata['assessmentitems'] = ((array_key_exists('assessmentitems', $activitiesdata)) ?
        $activitiesdata['assessmentitems'] : '');
        $coursedata['hasdata'] = ((!empty($coursedata['assessmentitems']) || !empty($coursedata['subcategories']) ? true : false));
        $activitydata['coursedata'] = $coursedata;

        return $activitydata;
    }

    /**
     * Method to determine which course type API needs to be used in
     * order to process the returned grade category and course items.
     *
     * @param object $activityitems
     * @param int $courseid
     * @param int $subcategory
     * @param int $userid
     * @param string $activetab
     * @param string $assessmenttype
     * @param string $sortby
     * @param string $sortorder
     * @return array
     */
    public static function process_get_activities(object $activityitems, int $courseid, int $subcategory, int $userid,
    string $activetab, string $assessmenttype, string $sortby, string $sortorder): array {
        $data = [];

        // We've lost all knowledge at this point of the course type - fetch it again.
        $mygradesenabled = course::is_type_mygrades($courseid);

        if ($activityitems->categories) {
            $categorydata = [];
            if ($mygradesenabled) {
                $categorydata = course::process_mygrades_subcategories($courseid,
                $activityitems->categories,
                $assessmenttype, $sortorder);
            }

            if (!$mygradesenabled) {
                $categorydata = course::process_default_subcategories($courseid, $activityitems->categories,
                $assessmenttype, $sortorder);
            }

            $data['subcategories'] = $categorydata;
        }

        if ($activityitems->items) {
            $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();

            $activitydata = [];
            if ($mygradesenabled) {
                $activitydata = self::process_mygrades_items($activityitems->items, $activetab, $ltiactivities,
                $assessmenttype, $sortby, $sortorder);
            }

            if (!$mygradesenabled) {
                $activitydata = self::process_default_items($activityitems->items, $activetab, $ltiactivities,
                $assessmenttype, $sortby, $sortorder);
            }

            $data['assessmentitems'] = $activitydata;
        }

        return $data;
    }

    /**
     * Process and prepare for display MyGrades specific gradable items.
     *
     * Agreement between HM/TW/GP that we're only displaying items that
     * are visible - so if an assessment has been graded and then the item
     * hidden - this will not display. No further checks for hidden grades
     * are being done - based on how Moodle currenly does things.
     *
     * @param array $mygradesitems
     * @param string $activetab
     * @param array $ltiactivities
     * @param string $assessmenttype
     * @param string $sortby
     * @param string $sortorder
     * @return array
     */
    public static function process_mygrades_items(array $mygradesitems, string $activetab, array $ltiactivities,
    string $assessmenttype, string $sortby, string $sortorder): array {

        global $DB, $USER, $CFG;
        $mygradesdata = [];
        $now = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

        if ($mygradesitems && count($mygradesitems) > 0) {

            foreach ($mygradesitems as $mygradesitem) {
                $tmp = null;
                // Cater for manual grade items that may have been added.
                if ($mygradesitem->itemtype == 'manual') {
                    $assessmentweight = course::return_weight($mygradesitem->aggregationcoef);
                    $statuslink = '';
                    // Has this item been processed by MyGrades yet.
                    $params = [
                        'courseid' => $mygradesitem->courseid,
                        'gradeitemid' => $mygradesitem->id,
                        'userid' => $USER->id,
                        'gradetype' => 'RELEASED',
                        'iscurrent' => 1,
                    ];
                    if ($usergrades = $DB->get_records('local_gugrades_grade', $params)) {
                        // Swap all of this for the relevant mygrades API calls - if/when one exists.
                        foreach ($usergrades as $usergrade) {
                            $statusclass = get_string('status_class_graded', 'block_newgu_spdetails');
                            $statustext = get_string('status_text_graded', 'block_newgu_spdetails');
                            // MGU-631 - Honour hidden grades and hidden activities.
                            $isgradehidden = \local_gugrades\api::is_grade_hidden($mygradesitem->id, $USER->id);
                            $grade = (($isgradehidden) ? get_string('status_text_tobeconfirmed',
                            'block_newgu_spdetails') : $usergrade->displaygrade);
                            $gradestatus = get_string('status_graded', 'block_newgu_spdetails');
                            if (!$isgradehidden) {
                                $gradeclass = true;
                                $gradefeedback = get_string('status_text_viewfeedback', 'block_newgu_spdetails');
                                $gradefeedbacklink = $CFG->wwwroot . '/grade/report/index.php?id=' . $mygradesitem->courseid;

                                $tmp = [
                                    'id' => $mygradesitem->id,
                                    'assessment_url' => '',
                                    'item_icon' => '',
                                    'icon_alt' => '',
                                    'item_name' => $mygradesitem->itemname,
                                    'assessment_type' => $assessmenttype,
                                    'assessment_weight' => $assessmentweight . '%',
                                    'raw_assessment_weight' => $assessmentweight,
                                    'due_date' => '',
                                    'raw_due_date' => '',
                                    'grade_status' => $gradestatus,
                                    'status_link' => $statuslink,
                                    'status_class' => $statusclass,
                                    'status_text' => $statustext,
                                    'grade' => $grade,
                                    'grade_class' => $gradeclass,
                                    'grade_provisional' => false,
                                    'grade_feedback' => $gradefeedback,
                                    'grade_feedback_link' => $gradefeedbacklink,
                                    'mygradesenabled' => 'true',
                                ];

                                $mygradesdata[] = $tmp;
                            }
                        }
                    } else {
                        if ($mygradesitem->hidden == 0 || ($mygradesitem->hidden > 1 && $mygradesitem->hidden < $now)) {

                            // MyGrades data hasn't been imported OR released yet, revert to getting the data from Gradebook.
                            // By default, items that have been graded will appear - however, if Marking Workflow has been
                            // enabled - we need to consider the grade display options as dictated by those settings.
                            $gradestatobj = grade::get_manual_grade_item_grade_status_and_feedback($mygradesitem->courseid,
                                $mygradesitem->id,
                                $USER->id,
                                $mygradesitem->gradetype,
                                $mygradesitem->scaleid,
                                $mygradesitem->grademax,
                            );

                            // The manual item can be hidden both via Gradebook Setup and from within the Grader report.
                            if ($gradestatobj->hidden == 0) {
                                $assessmenturl = $gradestatobj->assessment_url;
                                $duedate = '';
                                $rawduedate = '';
                                $gradestatus = $gradestatobj->grade_status;
                                $statuslink = $gradestatobj->status_link;
                                $statusclass = $gradestatobj->status_class;
                                $statustext = $gradestatobj->status_text;
                                $grade = $gradestatobj->grade_to_display;
                                $gradeclass = $gradestatobj->grade_class;
                                $gradeprovisional = $gradestatobj->grade_provisional;
                                $gradefeedback = $gradestatobj->grade_feedback;
                                $gradefeedbacklink = $gradestatobj->grade_feedback_link;

                                $tmp = [
                                    'id' => $mygradesitem->id,
                                    'assessment_url' => '',
                                    'item_icon' => '',
                                    'icon_alt' => '',
                                    'item_name' => $mygradesitem->itemname,
                                    'assessment_type' => $assessmenttype,
                                    'assessment_weight' => $assessmentweight . '%',
                                    'raw_assessment_weight' => $assessmentweight,
                                    'due_date' => '',
                                    'raw_due_date' => '',
                                    'grade_status' => $gradestatus,
                                    'status_link' => $statuslink,
                                    'status_class' => $statusclass,
                                    'status_text' => $statustext,
                                    'grade' => $grade,
                                    'grade_class' => $gradeclass,
                                    'grade_provisional' => $gradeprovisional,
                                    'grade_feedback' => $gradefeedback,
                                    'grade_feedback_link' => $gradefeedbacklink,
                                    'mygradesenabled' => 'true',
                                ];

                                $mygradesdata[] = $tmp;
                            }
                        }
                    }

                } else {

                    $cm = get_coursemodule_from_instance($mygradesitem->itemmodule, $mygradesitem->iteminstance,
                    $mygradesitem->courseid);
                    $modinfo = get_fast_modinfo($mygradesitem->courseid);
                    if (!empty($cm->id)) {
                        $cm = $modinfo->get_cm($cm->id);
                    }

                    // MGU-631 - Honour hidden grades and hidden activities. Having discussed with HM, if the activity
                    // is hidden, don't show it full stop. This code may not be correct -if- it should only hide the
                    // grade if either condition is true.
                    if ($cm->uservisible) {
                        // MGU-576/MGU-802 - Only include LTI activities if they have been selected.
                        // Note that LTI activities only become a "gradable" activity when they have been set to accept grades!
                        if ($mygradesitem->itemmodule == 'lti') {
                            if (is_array($ltiactivities) && !in_array($mygradesitem->iteminstance, $ltiactivities)) {
                                continue;
                            }
                        }

                        $assessmenturl = $cm->url->out();
                        $itemicon = '';
                        $iconalt = '';
                        if ($iconurl = $cm->get_icon_url()->out(false)) {
                            $itemicon = $iconurl;
                            $a = new \stdClass();
                            $a->modulename = get_string('modulename', $mygradesitem->itemmodule);
                            $a->activityname = $cm->name;
                            $iconalt = get_string('icon_alt_text', 'block_newgu_spdetails', $a);
                        }
                        $assessmentweight = course::return_weight($mygradesitem->aggregationcoef);
                        $duedate = '';
                        $rawduedate = '';
                        $gradestatus = get_string('status_tobeconfirmed', 'block_newgu_spdetails');
                        $statuslink = '';
                        $statusclass = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
                        $statustext = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                        $grade = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                        $gradeclass = false;
                        $gradeprovisional = false;
                        $gradefeedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                        $gradefeedbacklink = '';

                        $params = [
                            'courseid' => $mygradesitem->courseid,
                            'gradeitemid' => $mygradesitem->id,
                            'userid' => $USER->id,
                            'gradetype' => 'RELEASED',
                            'iscurrent' => 1,
                        ];
                        if ($usergrades = $DB->get_records('local_gugrades_grade', $params)) {
                            // Swap all of this for the relevant mygrades API calls - if/when one exists.
                            foreach ($usergrades as $usergrade) {
                                // Each activity has it's own notion of a 'due' date - so, until there's a better way...do this.
                                $activityduedate = \block_newgu_spdetails\api::get_activity_end_date_name($cm);
                                $dateobj = \DateTime::createFromFormat('U', $activityduedate);
                                $duedate = $dateobj->format('jS F Y');
                                $rawduedate = $activityduedate;
                                $statusclass = get_string('status_class_graded', 'block_newgu_spdetails');
                                $statustext = get_string('status_text_graded', 'block_newgu_spdetails');
                                // MGU-631 - Honour hidden grades and hidden activities.
                                $isgradehidden = \local_gugrades\api::is_grade_hidden($mygradesitem->id, $USER->id);
                                $grade = (($isgradehidden) ? get_string('status_text_tobeconfirmed',
                                'block_newgu_spdetails') : $usergrade->displaygrade);
                                $gradestatus = get_string('status_graded', 'block_newgu_spdetails');
                                if (!$isgradehidden) {
                                    $gradeclass = true;
                                    $gradefeedback = get_string('status_text_viewfeedback', 'block_newgu_spdetails');
                                    $gradefeedbacklink = $assessmenturl . '#page-footer';
                                }
                                break;
                            }
                        } else {
                            // MyGrades data either hasn't been imported, OR hasn't been released yet. Revert to getting
                            // this data from the Gradebook instead.
                            // By default, items that have been graded (in Gradebook) will appear here - unless Marking Workflow
                            // has been enabled. The display of the grade will then be decided based on the marking workflow state.
                            $gradestatobj = grade::get_grade_status_and_feedback($mygradesitem->courseid,
                                $mygradesitem->id,
                                $USER->id,
                                $mygradesitem->gradetype,
                                $mygradesitem->scaleid,
                                $mygradesitem->grademax,
                                'mygradesenabled'
                            );

                            $duedate = $gradestatobj->due_date;
                            $rawduedate = $gradestatobj->raw_due_date;
                            $gradestatus = $gradestatobj->grade_status;
                            $statuslink = $gradestatobj->status_link;
                            $statusclass = $gradestatobj->status_class;
                            $statustext = $gradestatobj->status_text;
                            // MGU-631 - Honour hidden grades and hidden activities.
                            //$grade = (($mygradesitem->hidden) ? get_string('status_text_tobeconfirmed', 'block_newgu_spdetails') :
                            //$gradestatobj->grade_to_display);
                            $grade = $gradestatobj->grade_to_display;
                            $gradeclass = $gradestatobj->grade_class;
                            $gradeprovisional = $gradestatobj->grade_provisional;
                            if (!$mygradesitem->hidden) {
                                $gradeclass = false;
                                $gradefeedback = $gradestatobj->grade_feedback;
                                $gradefeedbacklink = $gradestatobj->grade_feedback_link;
                            }
                        }

                        $tmp = [
                            'id' => $mygradesitem->id,
                            'assessment_url' => $assessmenturl,
                            'item_icon' => $itemicon,
                            'icon_alt' => $iconalt,
                            'item_name' => $mygradesitem->itemname,
                            'assessment_type' => $assessmenttype,
                            'assessment_weight' => $assessmentweight . '%',
                            'raw_assessment_weight' => $assessmentweight,
                            'due_date' => $duedate,
                            'raw_due_date' => $rawduedate,
                            'grade_status' => $gradestatus,
                            'status_link' => $statuslink,
                            'status_class' => $statusclass,
                            'status_text' => $statustext,
                            'grade' => $grade,
                            'grade_class' => $gradeclass,
                            'grade_provisional' => $gradeprovisional,
                            'grade_feedback' => $gradefeedback,
                            'grade_feedback_link' => $gradefeedbacklink,
                            'mygradesenabled' => 'true',
                        ];

                        $mygradesdata[] = $tmp;
                    }
                }

                if ($activetab == 'past') {
                    unset($tmp['grade_status']);
                }
            }
        }

        return $mygradesdata;
    }

    /**
     * Process and prepare for display default gradable items.
     *
     * Agreement between HM/TW/GP that we're only displaying items that
     * are visible - so if an assessment has been graded a then the item
     * hidden - this will not display. No further checks for hidden grades
     * are being done - based on how Moodle currenly does things.
     *
     * @param array $defaultitems
     * @param string $activetab
     * @param array $ltiactivities
     * @param string $assessmenttype
     * @param string $sortby
     * @param string $sortorder
     * @return array
     */
    public static function process_default_items(array $defaultitems, string $activetab, array $ltiactivities,
    string $assessmenttype, string $sortby, string $sortorder): array {

        global $USER;
        $defaultdata = [];
        $now = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

        if ($defaultitems && count($defaultitems) > 0) {

            foreach ($defaultitems as $defaultitem) {

                // Cater for manual grade items that may have been added.
                if ($defaultitem->itemtype == 'manual') {
                    if ($defaultitem->hidden == 0 || ($defaultitem->hidden > 1 && $defaultitem->hidden < $now)) {
                        $assessmentweight = course::return_weight($defaultitem->aggregationcoef);
                        $grade = '';
                        $gradeclass = false;
                        $gradeprovisional = false;
                        $gradestatus = '';
                        $statusclass = '';
                        $statustext = '';
                        $statuslink = '';
                        $gradefeedback = '';
                        $gradefeedbacklink = '';

                        $gradestatobj = grade::get_manual_grade_item_grade_status_and_feedback($defaultitem->courseid,
                            $defaultitem->id,
                            $USER->id,
                            $defaultitem->gradetype,
                            $defaultitem->scaleid,
                            $defaultitem->grademax
                        );

                        // The manual item can be hidden both via Gradebook Setup and from within the Grader report.
                        if ($gradestatobj->hidden == 0) {
                            $assessmenturl = $gradestatobj->assessment_url;
                            $duedate = '';
                            $rawduedate = '';
                            $gradestatus = $gradestatobj->grade_status;
                            $statuslink = $gradestatobj->status_link;
                            $statusclass = $gradestatobj->status_class;
                            $statustext = $gradestatobj->status_text;
                            $grade = $gradestatobj->grade_to_display;
                            $gradeclass = $gradestatobj->grade_class;
                            $gradeprovisional = $gradestatobj->grade_provisional;
                            $gradefeedback = $gradestatobj->grade_feedback;
                            $gradefeedbacklink = $gradestatobj->grade_feedback_link;

                            $tmp = [
                                'id' => $defaultitem->id,
                                'assessment_url' => $assessmenturl,
                                'item_icon' => '',
                                'icon_alt' => '',
                                'item_name' => $defaultitem->itemname,
                                'assessment_type' => $assessmenttype,
                                'assessment_weight' => $assessmentweight . '%',
                                'raw_assessment_weight' => $assessmentweight,
                                'due_date' => $duedate,
                                'raw_due_date' => $rawduedate,
                                'grade_status' => $gradestatus,
                                'status_link' => $statuslink,
                                'status_class' => $statusclass,
                                'status_text' => $statustext,
                                'grade' => $grade,
                                'grade_class' => $gradeclass,
                                'grade_provisional' => $gradeprovisional,
                                'grade_feedback' => $gradefeedback,
                                'grade_feedback_link' => $gradefeedbacklink,
                                'gradebookenabled' => 'true',
                            ];

                            $defaultdata[] = $tmp;
                        }
                    }
                } else {

                    $cm = get_coursemodule_from_instance($defaultitem->itemmodule, $defaultitem->iteminstance,
                    $defaultitem->courseid);
                    $modinfo = get_fast_modinfo($defaultitem->courseid);
                    if (!empty($cm->id)) {
                        $cm = $modinfo->get_cm($cm->id);
                    }

                    // MGU-631 - Honour hidden grades and hidden activities.
                    // Having discussed with HM, if the activity is hidden,
                    // don't show it full stop.
                    if ($cm->uservisible) {
                        // MGU-576/MGU-802 - Only include LTI activities if they have been selected.
                        // Note that LTI activities only become a "gradable" activity when they have been set to accept grades!
                        if ($defaultitem->itemmodule == 'lti') {
                            if (is_array($ltiactivities) && !in_array($defaultitem->iteminstance, $ltiactivities)) {
                                continue;
                            }
                        }

                        $itemicon = '';
                        $iconalt = '';
                        if ($iconurl = $cm->get_icon_url()->out(false)) {
                            $itemicon = $iconurl;
                            $a = new \stdClass();
                            $a->modulename = get_string('modulename', $defaultitem->itemmodule);
                            $a->activityname = $cm->name;
                            $iconalt = get_string('icon_alt_text', 'block_newgu_spdetails', $a);
                        }
                        $assessmentweight = course::return_weight($defaultitem->aggregationcoef);
                        $grade = '';
                        $gradeclass = false;
                        $gradeprovisional = false;
                        $gradestatus = '';
                        $statusclass = '';
                        $statustext = '';
                        $statuslink = '';
                        $gradefeedback = '';
                        $gradefeedbacklink = '';

                        $gradestatobj = grade::get_grade_status_and_feedback($defaultitem->courseid,
                                $defaultitem->id,
                                $USER->id,
                                $defaultitem->gradetype,
                                $defaultitem->scaleid,
                                $defaultitem->grademax,
                                'gradebookenabled',
                            );

                        $assessmenturl = $gradestatobj->assessment_url;
                        $duedate = $gradestatobj->due_date;
                        $rawduedate = $gradestatobj->raw_due_date;
                        $gradestatus = $gradestatobj->grade_status;
                        $statuslink = $gradestatobj->status_link;
                        $statusclass = $gradestatobj->status_class;
                        $statustext = $gradestatobj->status_text;
                        // MGU-631 - Honour hidden grades and hidden activities.
                        //$grade = ((!$defaultitem->hidden) ? $gradestatobj->grade_to_display :
                        //get_string('status_text_tobeconfirmed', 'block_newgu_spdetails'));
                        $grade = $gradestatobj->grade_to_display;
                        $gradeclass = $gradestatobj->grade_class;
                        $gradeprovisional = $gradestatobj->grade_provisional;
                        $gradefeedback = $gradestatobj->grade_feedback;
                        $gradefeedbacklink = $gradestatobj->grade_feedback_link;

                        $tmp = [
                            'id' => $defaultitem->id,
                            'assessment_url' => $assessmenturl,
                            'item_icon' => $itemicon,
                            'icon_alt' => $iconalt,
                            'item_name' => $defaultitem->itemname,
                            'assessment_type' => $assessmenttype,
                            'assessment_weight' => $assessmentweight . '%',
                            'raw_assessment_weight' => $assessmentweight,
                            'due_date' => $duedate,
                            'raw_due_date' => $rawduedate,
                            'grade_status' => $gradestatus,
                            'status_link' => $statuslink,
                            'status_class' => $statusclass,
                            'status_text' => $statustext,
                            'grade' => $grade,
                            'grade_class' => $gradeclass,
                            'grade_provisional' => $gradeprovisional,
                            'grade_feedback' => $gradefeedback,
                            'grade_feedback_link' => $gradefeedbacklink,
                            'gradebookenabled' => 'true',
                        ];

                        $defaultdata[] = $tmp;
                    }
                }

                if ($activetab == 'past') {
                    unset($tmp['grade_status']);
                }
            }
        }

        return $defaultdata;
    }

    /**
     * "Borrowed" from local_gugrades...
     * Factory to get the correct class based on the assignment type.
     * These are found in blocks_newgu_spdetails/classes/activities/
     * Pick xxx_activity for activity xxx (if exists) or default_activity
     * for everything else.
     *
     * @param int $gradeitemid
     * @param int $courseid
     * @param int $groupid
     * @return object
     */
    public static function activity_factory(int $gradeitemid, int $courseid, int $groupid = 0): object {
        global $DB;

        $item = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $module = $item->itemmodule;
        $classname = '\\block_newgu_spdetails\\activities\\' . $module . '_activity';
        if (class_exists($classname)) {
            return new $classname($gradeitemid, $courseid, $groupid);
        } else {
            return new default_activity($gradeitemid, $courseid, $groupid);
        }
    }

}
