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
use mod_questionnaire\responsetype\boolean;

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
     * @see MGU-975 - we're now restricting a number of activity types that no longer
     * need to appear on Student MyGrades
     * @var array $excludedactivities
     */

     public static $excludedactivities = [
        'attendance',
        'board',
        'book',
        'chat',
        'checklist',
        'choice',
        'choicegroup',
        'customcert',
        'feedback',
        'file',
        'folder',
        'game',
        'glossary',
        'hsuforum',
        'imscp',
        'kalvidres',
        'label',
        'lti',
        'oublog',
        'page',
        'pdfannotator',
        'reengagement',
        'scheduler',
        'survey',
        'url',
        'wiki',
        'zoom'
     ];

    /**
     * Main method called from the API.
     *
     * @param int $subcategoryid
     * @param int $userid
     * @param string $activetab
     * @return array
     */
    public static function get_activityitems(int $subcategoryid, int $userid, string $activetab): array {
        $activitydata = [];
        $coursedata = [];

        // What's my parent?
        // I need the parent of the parent in order to be able to always
        // step 'up' a level. \local_gugrades\grades::get_activitytree only
        // gives me the parent id, which breaks our mechanism.
        $gradecategory = grade_category::fetch(['id' => $subcategoryid]);
        $parent = grade_category::fetch(['id' => $gradecategory->parent]);
        if ($parent->parent == null) {
            $parentid = 0;
        } else {
            $parentid = $parent->id;
        }
        $activitydata['parent'] = $parentid;

        $courseid = $gradecategory->courseid;

        $course = get_course($courseid);
        $coursedata['coursename'] = $course->shortname;
        $coursedata['subcatfullname'] = ($gradecategory->fullname != '?' ? $gradecategory->fullname : '');

        // The assessment type is derived from the parent - which works only
        // as long as the parent name contains 'Formative' or 'Summative'.
        if (!$item = grade_item::fetch(['courseid' => $course->id, 'iteminstance' => $subcategoryid, 'itemtype' => 'category'])) {
            $item = grade_item::fetch(['courseid' => $course->id, 'iteminstance' => $subcategoryid, 'itemtype' => 'course']);
        }
        $assessmenttype = course::return_assessmenttype($gradecategory->fullname, $item->aggregationcoef);

        // We don't need the status column for past courses.
        $coursedata['hidestatuscol'] = (($activetab == 'past') ? true : false);

        $activities = api::get_activities($course->id, $subcategoryid);
        $activitiesdata = self::process_get_activities($activities, $course->id, $subcategoryid, $userid, $activetab, $assessmenttype);
        $coursedata['courseitems'] = ((array_key_exists('courseitems', $activitiesdata)) ? $activitiesdata['courseitems'] : '');
        $coursedata['hasdata'] = ((!empty($activitiesdata['courseitems']) ? true : false));
        $coursedata['mygradesenabled'] = ((!empty($activitiesdata['mygradesenabled']) ? true : false));
        $coursedata['hasgradecategory'] = ((array_key_exists('hasgradecategory', $activitiesdata)) ? true : false);
        $coursedata['hascourseitems'] = ((array_key_exists('hascourseitems', $activitiesdata)) ? true : false);
        $coursedata['weighttowardscourse'] = ((array_key_exists('weighttowardscourse', $activitiesdata)) ? $activitiesdata['weighttowardscourse'] : '-');
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
     * @return array
     */
    public static function process_get_activities(object $activityitems, int $courseid, int $subcategory, int $userid,
    string $activetab, string $assessmenttype): array {
        $data = [];
        // We've lost all knowledge at this point of the course type - fetch it again.
        $mygradesenabled = course::is_type_mygrades($courseid);

        if ($mygradesenabled) {
            // This call should return grade data that has been processed through the MyGrades tool.
            // This includes grade category data as well as individual grade item data.
            $gradedata = api::get_aggregation_dashboard_user($courseid, $subcategory, $userid);
            $tmpitems = $gradedata->fields;

            $gradecategories = [];
            $gradeitems = [];
            foreach($tmpitems as $tmpitem) {
                if ($tmpitem['iscategory'] == true) {
                    $gradecategories[] = $tmpitem;
                } elseif ($tmpitem['iscategory'] == false) {
                    $gradeitems[] = $tmpitem;
                }
            }
            $data['mygradesenabled'] = true;

            // MGU-1048/MGU-1065/MGU-1066 - We need to get the 'normalised' weight for the parent grade category
            $weighttowardscourse = 0;
            if ($item = \grade_item::fetch(['courseid' => $courseid, 'id' => $gradedata->parent->gradeitemid])) {
                $weighttowardscourse = course::get_grade_category_weight($item, $activityitems->category);
            }
            $data['weighttowardscourse'] = $weighttowardscourse->grade_category_weight;

            if ($gradecategories) {
                $categorydata = [];
                $categorydata = course::process_mygrades_subcategories($courseid, $gradecategories, $activityitems->categories, $assessmenttype);
                $data['courseitems'] = $categorydata;
                $data['hasgradecategory'] = true;
            }
            if ($gradeitems) {
                $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
                $activitydata = [];
                $activitydata = self::process_mygrades_items($gradeitems, $activityitems->items, $activetab, $ltiactivities,
                $assessmenttype);
                $data['courseitems'] = array_merge((array) $data['courseitems'], (array) $activitydata);
                $data['hascourseitems'] = true;
            }
        } elseif (!$mygradesenabled) {
            $data['mygradesenabled'] = false;
            
            // The weight for this grade category can be derived from the aggregation coefficient
            // value of the grade item, but only if it's been set in the gradebook however.
            $weighttowardscourse = 0;
            if ($item = \grade_item::fetch(['courseid' => $courseid, 'iteminstance' => $activityitems->category->id,
            'itemtype' => 'category'])) {
                // MGU-1066
                $weighttowardscourse = course::get_grade_category_weight($item, $activityitems->category);
            }
            $data['weighttowardscourse'] = $weighttowardscourse->grade_category_weight;

            if ($activityitems->categories) {
                $categorydata = [];
                $categorydata = course::process_default_subcategories($courseid, $activityitems->categories, $assessmenttype);
                $data['courseitems'] = $categorydata;
                $data['hasgradecategory'] = true;
            }
    
            if ($activityitems->items) {
                $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
                $activitydata = [];

                // MGU-1066 - Only display activity item weights if a weighted strategy is being used.
                // However, if using a weighted strategy with 'drop the lowest' and the value is greater 
                // than 0, then don't display any weights.
                $displayweights = false;
                if (($activityitems->category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN ||
                    $activityitems->category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2)) {
                    // If we're using a weighted strategy and the weighting has inadvertantly been entered/changed to > 1,
                    // Then don't show the weighting for the category ^and^ items.
                    if ((int) $weighttowardscourse->grade_category_weight > 0) {
                        $displayweights = true;
                    }
                    if ($activityitems->category->droplow > 0) {
                        $displayweights = false;
                    }
                }
                $activitydata = self::process_default_items($activityitems->items, $activetab, $ltiactivities, $assessmenttype,
                $displayweights);
                $data['courseitems'] = array_merge((array) $data['courseitems'], (array) $activitydata);
                $data['hascourseitems'] = true;
            }
        }

        if (!empty($data['courseitems'])) {
            $tmpcourseitems = self::sort_course_items($data['courseitems']);
            // An array of objects is returned, keyed by the sort order.
            // We need to reindex the array w/o losing the order of the items, this is needed by Mustache when iterating items.
            $data['courseitems'] = array_values($tmpcourseitems);
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
     * @param array $tmpgradeitems
     * @param string $activetab
     * @param array $ltiactivities
     * @param string $assessmenttype
     * @return array
     */
    public static function process_mygrades_items(array $mygradesitems, array $tmpgradeitems, string $activetab,
    array $ltiactivities, string $assessmenttype): array {

        global $CFG;
        $mygradesdata = [];

        if ($mygradesitems && count($mygradesitems) > 0) {

            // While processing each item, we will need to 'key into' $tmpgradeitems for help with things along the way.
            $index = 0;
            foreach ($mygradesitems as $mygradesitem) {
                $cm = get_coursemodule_from_instance($tmpgradeitems[$index]->itemmodule, $tmpgradeitems[$index]->iteminstance,
                $tmpgradeitems[$index]->courseid);
                $modinfo = get_fast_modinfo($tmpgradeitems[$index]->courseid);
                $cms = $modinfo->get_cms();

                // Deal with the easy state first.
                if ($mygradesitem['released'] == true) {
                    // We will assume here that as this item has been released, it is therefore not in a list of things to be
                    // excluded, is not subject to being in a list of course module id's, is not a manual item and is not an
                    // LTI activity that needs to be excluded.
                    $itemicon = '';
                    $iconalt = '';
                    $iconrestricted = false;
                    $assessmenturl = '';
                    if (array_key_exists($cm->id, $cms)) {
                        $cm = $modinfo->get_cm($cm->id);
                        $assessmenturl = $cm->url->out();
                        if ($activityicon = self::get_activity_icon($cm, $tmpgradeitems[$index]->itemmodule)) {
                            $itemicon = $activityicon->iconurl;
                            $iconalt = $activityicon->iconalt;
                        }
                    }
                    
                    $statusclass = get_string('status_class_graded', 'block_newgu_spdetails');
                    $statustext = get_string('status_text_graded', 'block_newgu_spdetails');
                    // MGU-631 - Honour hidden grades and hidden activities.
                    $isgradehidden = $mygradesitem['hidden'];
                    $gradestatus = get_string('status_graded', 'block_newgu_spdetails');
                    // Each activity has it's own notion of a 'due' date - so, until there's a better way...do this.
                    $activityduedate = \block_newgu_spdetails\api::get_activity_end_date_name($cm);
                    // @see MGU-1025.
                    if ($activityduedate > 0) {
                        $duedate = userdate($activityduedate, get_string('strftimedate', 'core_langconfig'));
                    } else {
                        $duedate = 'N/A';
                    }
                    $rawduedate = $activityduedate;
                    // If we're using a weighted strategy with a drop the lowest [n] configuration, don't display the weight.
                    if (!$mygradesitem['dropped']) {
                        $rawassessmentweight = course::return_weight($mygradesitem['weight']);
                        $assessmentweight = (($rawassessmentweight > 0) ? $rawassessmentweight . "%" : "-");
                    } else {
                        $rawassessmentweight = 0;
                        $assessmentweight = '-';
                    }

                    $mygradesactivityitem = new \stdClass();
                    $mygradesactivityitem->id = $mygradesitem['id'];
                    $mygradesactivityitem->sortorder = $tmpgradeitems[$index]->sortorder;
                    $mygradesactivityitem->is_gradecategory = false;
                    $mygradesactivityitem->assessment_url = $assessmenturl;
                    $mygradesactivityitem->item_icon = $itemicon;
                    $mygradesactivityitem->icon_alt = $iconalt;
                    $mygradesactivityitem->icon_restricted = $iconrestricted;
                    $mygradesactivityitem->item_name = $tmpgradeitems[$index]->itemname;
                    $mygradesactivityitem->assessment_type = $assessmenttype;
                    $mygradesactivityitem->assessment_weight = $assessmentweight;
                    $mygradesactivityitem->raw_assessment_weight = $rawassessmentweight;
                    $mygradesactivityitem->due_date = $duedate;
                    $mygradesactivityitem->raw_due_date = $rawduedate;
                    $mygradesactivityitem->grade_status = $gradestatus;
                    $mygradesactivityitem->status_link = '';
                    $mygradesactivityitem->status_class = $statusclass;
                    $mygradesactivityitem->status_text = $statustext;
                    $mygradesactivityitem->grade = get_string('status_text_tobeconfirmed',
                    'block_newgu_spdetails');
                    $mygradesactivityitem->grade_class = false;
                    $mygradesactivityitem->grade_provisional = false;
                    $mygradesactivityitem->grade_feedback = '';
                    $mygradesactivityitem->grade_feedback_link = '';
                    $mygradesactivityitem->mygradesenabled = true;

                    if (!$isgradehidden) {
                        $mygradesactivityitem->grade_class = true;
                        // MGU-1004 - Account for whether this is an Admin grade or just a regular grade.
                        $mygradesactivityitem->grade = grade::is_admin_or_generic_grade($mygradesitem['admingrade'], $mygradesitem['display']);
                        $mygradesactivityitem->grade_feedback = get_string('status_text_viewfeedback', 'block_newgu_spdetails');
                        $mygradesactivityitem->grade_feedback_link = $CFG->wwwroot . '/grade/report/index.php?id=' . $tmpgradeitems[$index]->courseid;
                    }

                    if ($activetab == 'past') {
                        unset($mygradesactivityitem->grade_status);
                    }

                    $mygradesdata[] = $mygradesactivityitem;
                } elseif ($mygradesitem['released'] == false) {
                    // Fallback to processing this as a regular Gradebook grade item if nothing has been released.
                    $tmpgradeitem = $tmpgradeitems[$index];

                    // The weight for this grade category can be derived from the aggregation coefficient
                    // value of the grade item, but only if it's been set in the gradebook however.
                    $weighttowardscourse = 0;
                    if ($item = \grade_item::fetch(['courseid' => $tmpgradeitem->courseid, 'iteminstance' => $tmpgradeitem->category->id,
                    'itemtype' => 'category'])) {
                        // MGU-1066
                        $weighttowardscourse = course::get_grade_category_weight($item, $tmpgradeitem->category);
                    }

                    // MGU-1065 - Only display activity item weights if a weighted strategy is being used.
                    // However, if using a weighted strategy with 'drop the lowest' and the value is greater 
                    // than 0, then don't display any weights.
                    $displayweights = false;
                    if (($tmpgradeitem->category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN ||
                    $tmpgradeitem->category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2)) {
                        // If we're using a weighted strategy and the weighting has inadvertantly been entered/changed to > 1,
                        // Then don't show the weighting for the category ^and^ items.
                        if ((int) $weighttowardscourse->grade_category_weight > 0) {
                            $displayweights = true;
                        }
                        if ($tmpgradeitem->category->droplow > 0) {
                            $displayweights = false;
                        }
                    }

                    $tmp = self::process_default_items([$tmpgradeitem], $activetab, $ltiactivities, $assessmenttype, $displayweights);
                    $mygradesdata[] = array_shift($tmp);
                    // $gradessubcatdata[] = array_shift($tmp);
                    
                    // if (!in_array($tmpgradeitems[$index]->itemmodule, self::$excludedactivities)) {
                    //     // Cater for manual grade items that may have been added.
                    //     if ($tmpgradeitems[$index]->itemtype == 'manual') {
                    //         $mygradesdata[] = self::process_manual_grade_item($tmpgradeitems[$index], $assessmenttype, 'mygradesenabled');
                    //     } else {
                    //         if (array_key_exists($cm->id, $cms)) {
                    //             $cm = $modinfo->get_cm($cm->id); 
                                    
                    //             // MGU-576/MGU-802 - Only include LTI activities if they have been selected.
                    //             // Note that LTI activities only become a "gradable" activity when they have been set to accept grades!
                    //             if ($tmpgradeitems[$index]->itemmodule == 'lti') {
                    //                 if (is_array($ltiactivities) && !in_array($tmpgradeitems[$index]->iteminstance, $ltiactivities)) {
                    //                     continue;
                    //                 }
                    //             }

                    //             $assessmenturl = $cm->url->out();
                    //             $itemicon = '';
                    //             $iconalt = '';
                    //             $iconrestricted = false;
                    //             if ($activityicon = self::get_activity_icon($cm, $tmpgradeitems[$index]->itemmodule)) {
                    //                 $itemicon = $activityicon->iconurl;
                    //                 $iconalt = $activityicon->iconalt;
                    //             }
                    //             $rawassessmentweight = course::return_weight($tmpgradeitems[$index]->aggregationcoef);
                    //             $assessmentweight = (($rawassessmentweight > 0) ? $rawassessmentweight . "%" : "-");
                    //             $duedate = '';
                    //             $rawduedate = '';
                    //             $gradestatus = get_string('status_tobeconfirmed', 'block_newgu_spdetails');
                    //             $statuslink = '';
                    //             $statusclass = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
                    //             $statustext = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                    //             $grade = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                    //             $gradeclass = false;
                    //             $gradeprovisional = false;
                    //             $gradefeedback = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
                    //             $gradefeedbacklink = '';

                    //             // By default, items that have been graded (in Gradebook) will appear here - unless Marking Workflow
                    //             // has been enabled. The display of the grade will then be decided based on the marking workflow state.
                    //             $gradestatobj = grade::get_grade_status_and_feedback($tmpgradeitems[$index]->courseid,
                    //             $tmpgradeitems[$index]->id,
                    //                 $USER->id,
                    //                 $tmpgradeitems[$index]->gradetype,
                    //                 $tmpgradeitems[$index]->scaleid,
                    //                 $tmpgradeitems[$index]->grademax,
                    //                 'mygradesenabled'
                    //             );

                    //             // MGU-631/MGU-1027 - Restrict Access wasn't being taken into account when checking visibility.
                    //             if ($cm->uservisible) {
                    //                 $duedate = $gradestatobj->due_date;
                    //                 $rawduedate = $gradestatobj->raw_due_date;
                    //                 $gradestatus = $gradestatobj->grade_status;
                    //                 $statuslink = $gradestatobj->status_link;
                    //                 $statusclass = $gradestatobj->status_class;
                    //                 $statustext = $gradestatobj->status_text;
                    //                 // MGU-631 - Honour hidden grades and hidden activities.
                    //                 $grade = $gradestatobj->grade_to_display;
                    //                 $gradeclass = $gradestatobj->grade_class;
                    //                 $gradeprovisional = $gradestatobj->grade_provisional;
                    //                 if (!$tmpgradeitems[$index]->hidden) {
                    //                     $gradefeedback = $gradestatobj->grade_feedback;
                    //                     $gradefeedbacklink = $gradestatobj->grade_feedback_link;
                    //                 }
                    //             } elseif ($cm->availableinfo) {
                    //                 $iconalt = substr($activityicon->iconalt, 8);
                    //                 $assessmenturl = '';
                    //                 $duedate = $gradestatobj->due_date;
                    //                 $rawduedate = $gradestatobj->raw_due_date;
                    //                 $statustext = get_string('status_text_restricted', 'block_newgu_spdetails');
                    //                 $iconrestricted = true;
                    //             } else {
                    //                 // User cannot access this activity - they simply will not see it at all.
                    //                 continue;
                    //             }

                    //             $mygradesactivityitem = new \stdClass();
                    //             $mygradesactivityitem->id = $tmpgradeitems[$index]->id;
                    //             $mygradesactivityitem->sortorder = $tmpgradeitems[$index]->sortorder;
                    //             $mygradesactivityitem->is_gradecategory = false;
                    //             $mygradesactivityitem->assessment_url = $assessmenturl;
                    //             $mygradesactivityitem->item_icon = $itemicon;
                    //             $mygradesactivityitem->icon_alt = $iconalt;
                    //             $mygradesactivityitem->icon_restricted = $iconrestricted;
                    //             $mygradesactivityitem->item_name = $tmpgradeitems[$index]->itemname;
                    //             $mygradesactivityitem->assessment_type = $assessmenttype;
                    //             $mygradesactivityitem->assessment_weight = $assessmentweight;
                    //             $mygradesactivityitem->raw_assessment_weight = $rawassessmentweight;
                    //             $mygradesactivityitem->due_date = $duedate;
                    //             $mygradesactivityitem->raw_due_date = $rawduedate;
                    //             $mygradesactivityitem->grade_status = $gradestatus;
                    //             $mygradesactivityitem->status_link = $statuslink;
                    //             $mygradesactivityitem->status_class = $statusclass;
                    //             $mygradesactivityitem->status_text = $statustext;
                    //             $mygradesactivityitem->grade = $grade;
                    //             $mygradesactivityitem->grade_class = $gradeclass;
                    //             $mygradesactivityitem->grade_provisional = $gradeprovisional;
                    //             $mygradesactivityitem->grade_feedback = $gradefeedback;
                    //             $mygradesactivityitem->grade_feedback_link = $gradefeedbacklink;
                    //             $mygradesactivityitem->mygradesenabled = true;

                    //             if ($activetab == 'past') {
                    //                 unset($mygradesactivityitem->grade_status);
                    //             }

                    //             $mygradesdata[] = $mygradesactivityitem;
                    //         }
                    //     }
                    // }
                }
                $index++;
            }
        }

        return $mygradesdata;
    }

    /**
     * Process and prepare for display default gradable items.
     *
     * Agreement between HM/TW/GP that we're only displaying items that
     * are visible - so if an assessment has been graded, and then the item
     * hidden - this will not display. No further checks for hidden grades
     * are being done - based on how Moodle currenly does things.
     *
     * @param array $defaultitems
     * @param string $activetab
     * @param array $ltiactivities
     * @param string $assessmenttype
     * @param bool $displayweights
     * @return array
     */
    public static function process_default_items(array $defaultitems, string $activetab, array $ltiactivities,
    string $assessmenttype, bool $displayweights): array {

        global $USER;
        $defaultdata = [];

        if ($defaultitems && count($defaultitems) > 0) {

            foreach ($defaultitems as $defaultitem) {
                if (!in_array($defaultitem->itemmodule, self::$excludedactivities)) {
                    // Cater for manual grade items that may have been added.
                    if ($defaultitem->itemtype == 'manual') {
                        $defaultdata[] = self::process_manual_grade_item($defaultitem, $assessmenttype, 'gradebookenabled');
                    } else {
                        $cm = get_coursemodule_from_instance($defaultitem->itemmodule, $defaultitem->iteminstance,
                        $defaultitem->courseid);
                        $modinfo = get_fast_modinfo($defaultitem->courseid);
                        $cms = $modinfo->get_cms();
                        if (array_key_exists($cm->id, $cms)) {
                            // MGU-576/MGU-802 - Only include LTI activities if they have been selected.
                            // Note that LTI activities only become a "gradable" activity when they have been set to accept grades!
                            if ($defaultitem->itemmodule == 'lti') {
                                if (is_array($ltiactivities) && !in_array($defaultitem->iteminstance, $ltiactivities)) {
                                    continue;
                                }
                            }

                            $cm = $modinfo->get_cm($cm->id);
                            $itemicon = '';
                            $iconalt = '';
                            if ($activityicon = self::get_activity_icon($cm, $defaultitem->itemmodule)) {
                                $itemicon = $activityicon->iconurl;
                                $iconalt = $activityicon->iconalt;
                            }

                            $assessmenturl = '';
                            $rawassessmentweight = 0;
                            $assessmentweight = '-';
                            if ($displayweights) {
                                $rawassessmentweight = course::return_weight($defaultitem->aggregationcoef);
                                $assessmentweight = (($rawassessmentweight > 0) ? $rawassessmentweight . "%" : "-");
                            }
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
                            $grade = $gradestatobj->grade_to_display;
                            $gradeclass = $gradestatobj->grade_class;
                            $gradeprovisional = $gradestatobj->grade_provisional;
                            $gradefeedback = $gradestatobj->grade_feedback;
                            $gradefeedbacklink = $gradestatobj->grade_feedback_link;

                            // MGU-631/MGU-1027 - Restrict Access wasn't being taken into account when checking visibility.
                            if ($cm->uservisible) {
                                $defaultactivityitem = new \stdClass();
                                $defaultactivityitem->id = $defaultitem->id;
                                $defaultactivityitem->sortorder = $defaultitem->sortorder;
                                $defaultactivityitem->is_gradecategory = false;
                                $defaultactivityitem->assessment_url = $assessmenturl;
                                $defaultactivityitem->item_icon = $itemicon;
                                $defaultactivityitem->icon_alt = $iconalt;
                                $defaultactivityitem->item_name = $defaultitem->itemname;
                                $defaultactivityitem->assessment_type = $assessmenttype;
                                $defaultactivityitem->assessment_weight = $assessmentweight;
                                $defaultactivityitem->raw_assessment_weight = $rawassessmentweight;
                                $defaultactivityitem->due_date = $duedate;
                                $defaultactivityitem->raw_due_date = $rawduedate;
                                $defaultactivityitem->grade_status = $gradestatus;
                                $defaultactivityitem->status_link = $statuslink;
                                $defaultactivityitem->status_class = $statusclass;
                                $defaultactivityitem->status_text = $statustext;
                                $defaultactivityitem->grade = $grade;
                                $defaultactivityitem->grade_class = $gradeclass;
                                $defaultactivityitem->grade_provisional = $gradeprovisional;
                                $defaultactivityitem->grade_feedback = $gradefeedback;
                                $defaultactivityitem->grade_feedback_link = $gradefeedbacklink;
                                $defaultactivityitem->gradebookenabled = 'true';
                            } elseif ($cm->availableinfo) {
                                $iconalt = substr($activityicon->iconalt, 8);

                                $defaultactivityitem = new \stdClass();
                                $defaultactivityitem->id = $defaultitem->id;
                                $defaultactivityitem->sortorder = $defaultitem->sortorder;
                                $defaultactivityitem->is_gradecategory = false;
                                $defaultactivityitem->assessment_url = '';
                                $defaultactivityitem->item_icon = $itemicon;
                                $defaultactivityitem->icon_alt = $iconalt;
                                $defaultactivityitem->icon_restricted = true;
                                $defaultactivityitem->item_name = $defaultitem->itemname;
                                $defaultactivityitem->assessment_type = $assessmenttype;
                                $defaultactivityitem->assessment_weight = $assessmentweight;
                                $defaultactivityitem->raw_assessment_weight = $rawassessmentweight;
                                $defaultactivityitem->due_date = '';
                                $defaultactivityitem->raw_due_date = 0;
                                $defaultactivityitem->grade_status = get_string('status_text_restricted', 'block_newgu_spdetails');
                                $defaultactivityitem->status_link = '';
                                $defaultactivityitem->status_class = get_string('status_class_restricted', 'block_newgu_spdetails');
                                $defaultactivityitem->status_text = get_string('status_text_restricted', 'block_newgu_spdetails');
                                $defaultactivityitem->grade = $grade;
                                $defaultactivityitem->grade_class = $gradeclass;
                                $defaultactivityitem->grade_provisional = $gradeprovisional;
                                $defaultactivityitem->grade_feedback = $gradefeedback;
                                $defaultactivityitem->grade_feedback_link = $gradefeedbacklink;
                                $defaultactivityitem->gradebookenabled = 'true';
                            } else {
                                // User cannot access this activity - they simply will not see it at all.
                            }

                            if (is_object($defaultactivityitem)) {
                                $defaultdata[] = $defaultactivityitem;
                            }
                        }
                    }
                    if ($activetab == 'past') {
                        unset($defaultactivityitem->grade_status);
                    }
                }
            }
        }

        return $defaultdata;
    }

    /**
     * @param object $manualgradeitem
     * @param string $assessmenttype
     * @param string $coursetype - this is more to satisfy the unit tests - for now at least.
     * @return object
     */
    public static function process_manual_grade_item(object $manualgradeitem, string $assessmenttype, string $coursetype): object {

        global $USER;
        $processedmanualgradeitem = new \stdClass();
        $now = usertime(mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        if ($manualgradeitem->hidden == 0 || ($manualgradeitem->hidden > 1 && $manualgradeitem->hidden < $now)) {
            $rawassessmentweight = course::return_weight($manualgradeitem->aggregationcoef);
            $assessmentweight = (($rawassessmentweight > 0) ? $rawassessmentweight . "%" : "-");
            $grade = '';
            $gradeclass = false;
            $gradeprovisional = false;
            $gradestatus = '';
            $statusclass = '';
            $statustext = '';
            $statuslink = '';
            $gradefeedback = '';
            $gradefeedbacklink = '';

            $gradestatobj = grade::get_manual_grade_item_grade_status_and_feedback($manualgradeitem->courseid,
                $manualgradeitem->id,
                $USER->id,
                $manualgradeitem->gradetype,
                $manualgradeitem->scaleid,
                $manualgradeitem->grademax
            );

            // The manual item can be hidden both via Gradebook Setup and from within the Grader report.
            if ($gradestatobj->hidden == 0) {
                $assessmenturl = $gradestatobj->assessment_url;
                $duedate = 'N/A';
                $rawduedate = '0';
                $gradestatus = $gradestatobj->grade_status;
                $statuslink = $gradestatobj->status_link;
                $statusclass = $gradestatobj->status_class;
                $statustext = $gradestatobj->status_text;
                // MGU-631 - Honour hidden grades and hidden activities.
                $grade = $gradestatobj->grade_to_display;
                $gradeclass = $gradestatobj->grade_class;
                $gradeprovisional = $gradestatobj->grade_provisional;
                $gradefeedback = $gradestatobj->grade_feedback;
                $gradefeedbacklink = $gradestatobj->grade_feedback_link;

                $processedmanualgradeitem->id = $manualgradeitem->id;
                $processedmanualgradeitem->sortorder = $manualgradeitem->sortorder;
                $processedmanualgradeitem->assessment_url = $assessmenturl;
                $processedmanualgradeitem->item_icon = '';
                $processedmanualgradeitem->icon_alt = get_string('manualitem', 'grades');
                $processedmanualgradeitem->item_name = $manualgradeitem->itemname;
                $processedmanualgradeitem->assessment_type = $assessmenttype;
                $processedmanualgradeitem->assessment_weight = $assessmentweight;
                $processedmanualgradeitem->raw_assessment_weight = $rawassessmentweight;
                $processedmanualgradeitem->due_date = $duedate;
                $processedmanualgradeitem->raw_due_date = $rawduedate;
                $processedmanualgradeitem->grade_status = $gradestatus;
                $processedmanualgradeitem->status_link = $statuslink;
                $processedmanualgradeitem->status_class = $statusclass;
                $processedmanualgradeitem->status_text = $statustext;
                $processedmanualgradeitem->grade = $grade;
                $processedmanualgradeitem->grade_class = $gradeclass;
                $processedmanualgradeitem->grade_provisional = $gradeprovisional;
                $processedmanualgradeitem->grade_feedback = $gradefeedback;
                $processedmanualgradeitem->grade_feedback_link = $gradefeedbacklink;
                $processedmanualgradeitem->$coursetype = true;
            }
        }

        return $processedmanualgradeitem;
    }

    /**
     * Generate an icon image path.
     *
     * @param object $cm
     * @param string $itemmodule
     * @return object|boolean
     */
    public static function get_activity_icon($cm, $itemmodule): mixed {
        if ($iconurl = $cm->get_icon_url()->out(false)) {
            $a = new \stdClass();
            $a->modulename = get_string('modulename', $itemmodule);
            $a->activityname = $cm->name;
            $iconalt = get_string('icon_alt_text', 'block_newgu_spdetails', $a);
            $a->iconurl = $iconurl;
            $a->iconalt = $iconalt;

            return $a;
        }

        return false;
    }

    /**
     * @param array $courseitems
     * @return array
     */
    public static function sort_course_items($courseitems) {
        uasort($courseitems, function($a, $b) {
            return strnatcmp($a->sortorder, $b->sortorder);
        });

        return $courseitems;
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
