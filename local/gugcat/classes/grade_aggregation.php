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
 * Class containing helper methods for Grade Aggregation page.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_gugcat;

use ArrayObject;
use assign;
use context_course;
use context_module;
use local_gugcat;
use stdClass;
use grade_grade;
use grade_category;
use grade_item;

defined('MOODLE_INTERNAL') || die();
require_once('gcat_item.php');

// Grade form settings.
define('ADJUST_WEIGHT_FORM', 0);
define('OVERRIDE_GRADE_FORM', 1);

 /**
  * Class containing helper methods for Grade Aggregation page.
  */

class grade_aggregation{

    /**
     * An array of course level administrative grades.
     * @var array $aggrade
     */
    public static $aggrade = array(
        CREDIT_WITHHELD => CREDIT_WITHHELD_AC,
        CREDIT_REFUSED => CREDIT_REFUSED_AC,
        CA => CA_AC,
        UNDER_INVESTIGATION => UNDER_INVESTIGATION_AC,
        AU => AU_AC,
        FC => FC_AC
    );

     /**
      * Returns rows for grade aggregation table.
      *
      * @param mixed $course
      * @param array $modules
      * @param array $students
      * @param boolean $showerrors
      */
    public static function get_rows($course, $modules, $students, $showerrors = false) {
        global $DB, $aggradeid;
        $aggradeid = null;
        $categoryid = optional_param('categoryid', '0', PARAM_INT);
        $activityid = optional_param('activityid', null, PARAM_INT);
        if (!empty($modules) && count(array_filter(array_column($modules, 'provisionalid'))) > 0) {
            // Get or create grade item id for aggregated grade.
            $aggradeid = local_gugcat::add_grade_item($course->id, get_string('aggregatedgrade', 'local_gugcat'), null, $students);
        }

        // Check for alternative course grades.
        $meritgi = local_gugcat::get_grade_item_id($course->id, $categoryid, get_string('meritgrade', 'local_gugcat'));
        if ($meritgi) {
            $meritsettings = $DB->get_records('gcat_acg_settings', array('acgid' => $meritgi));
        }
        $gpagi = local_gugcat::get_grade_item_id($course->id, $categoryid, get_string('gpagrade', 'local_gugcat'));
        if ($gpagi) {
            $gpasettings = $DB->get_records('gcat_acg_settings', array('acgid' => $gpagi));
        }

        $rows = array();
        $assessments = array();
        foreach ($modules as $mod) {
            $weightcoef1 = $mod->gradeitem->aggregationcoef; // Aggregation coeficient used for weighted averages or extra credit.
            $weightcoef2 = $mod->gradeitem->aggregationcoef2; // Aggregation coeficient used for weighted averages only.
            $mod->weight = ((float)$weightcoef1 > 0) ? (float)$weightcoef1 : (float)$weightcoef2;
            $mod->scaleid = $mod->gradeitem->scaleid;
            $mod->gradeitemid = $mod->gradeitem->id;
            $mod->is_resit = false;
            $grades = new stdClass();
            $prvgrdid = null;
            $moodlestr = get_string('moodlegrade', 'local_gugcat');
            // Get provisional gradeitem id and grades from gradebook for assessments and sub category.
            if ($mod->modname == 'category') {
                // Check if components are imported.
                if (count($mod->children) != 0) {
                    $componentsql = null;
                    foreach ($mod->children as $id) {
                        $componentsql .= "iteminfo = $id OR ";
                    }
                    // Remove last OR.
                    $componentsql = chop($componentsql, ' OR ');
                    // If atleast 1 component has moodle grade item, subcategory is imported, hence, get the.
                    // provisional grade (subcategory) grade item of the subcategory.
                    if ($DB->record_exists_select('grade_items', "itemname = '$moodlestr' AND ($componentsql)")) {
                        // Module id ($mod->id) - sub category id.
                        $prvgrdid = $mod->provisionalid;
                        $subcataggscale = $DB->get_record('grade_items', array('id' => $prvgrdid), 'calculation, outcomeid');
                        $mod->aggregation_type = !is_null($subcataggscale) && !empty($subcataggscale)
                        ? $subcataggscale->calculation : null;
                        $mod->automaticscale = !is_null($subcataggscale) && !empty($subcataggscale)
                        ? $subcataggscale->outcomeid : null;
                    }
                }
                $gbgrades = grade_get_grades($course->id, 'category', null, $mod->instance, array_keys($students));
            } else {
                $mod->is_resit = self::is_resit($mod);
                // Check if activity is imported or not by checking its moodle grade item.
                if ($DB->record_exists_select('grade_items', "itemname = '$moodlestr' AND iteminfo = $mod->gradeitemid")) {
                    $prvgrdid = $mod->provisionalid;
                }
                $gbgrades = grade_get_grades($course->id, 'mod', $mod->modname, $mod->instance, array_keys($students));
            }

            $fields = 'userid, itemid, id, rawgrade, finalgrade, information, timemodified, overridden, feedback';
            // Get provisional grades.
            $grades->provisional = is_null($prvgrdid) ? array()
            : $DB->get_records('grade_grades', array('itemid' => $prvgrdid), 'id', $fields);
            if ($mod->is_converted) {
                $mod->conversion = grade_converter::retrieve_grade_conversion($mod->gradeitemid);
                $cvtgrdid = local_gugcat::get_grade_item_id($course->id, $mod->gradeitemid,
                     get_string('convertedgrade', 'local_gugcat'));
                $grades->converted = $DB->get_records('grade_grades', array('itemid' => $cvtgrdid), 'id', $fields);
            }
            // Filter grades from gradebook with specific itemnumber.
            $gbgradeitem = array_values(array_filter($gbgrades->items, function($item) use($mod){
                return $item->itemnumber == $mod->gradeitem->itemnumber;
            }));
            $gradebook = isset($gbgradeitem[0]) ? $gbgradeitem[0]->grades : null;
            // Get assign grades/gradebook grades and normalize it.
            if (empty($grades->provisional) && $mod->modname == 'assign' && $mod->gradeitem->gradetype == GRADE_TYPE_SCALE) {
                $assign = new assign(context_module::instance($mod->id), $mod, $course->id);
                local_gugcat::set_grade_scale($mod->gradeitem->scaleid);
                foreach ($students as $student) {
                    $gb = isset($gradebook[$student->id]) ? $gradebook[$student->id] : null;
                    $assigngrd = $assign->get_user_grade($student->id, false);
                    $gb = local_gugcat::get_gb_assign_grade($assigngrd, $gb);
                    $gradebook[$student->id] = local_gugcat::normalize_gcat_grades($gb);
                }
            }
            $grades->gradebook = $gradebook;
            $mod->grades = $grades;
            array_push($assessments, $mod);
        }
        // Errors to display.
        $errors = array();
        // Candidate no. = $i - Multiply it by the page number.
        $page = optional_param('page', 0, PARAM_INT);
        $i = $page * GCAT_MAX_USERS_PER_PAGE + 1;
        $aggrdscaletype = null;
        foreach ($students as $student) {
            $schedaweights = 0;
            $schedbweights = 0;
            $hasmvgrade = false;
            $gradecaptureitem = new gcat_item();
            $gradecaptureitem->cnum = $i;
            $gradecaptureitem->studentno = $student->id;
            $gradecaptureitem->surname = $student->lastname;
            $gradecaptureitem->forename = $student->firstname;
            $gradecaptureitem->idnumber = $student->idnumber;
            $gradecaptureitem->grades = array();
            $gradecaptureitem->highlightMV = false;
            $floatweight = 0;
            $sumaggregated = 0;
            $calculatedweight = 0;
            $aggrdobj = new stdClass();
            $aggrdobj->display = get_string('missinggrade', 'local_gugcat');
            if (count($assessments) > 0) {
                $gradetypes = array();
                $feedback = ",_weights: ";
                foreach ($assessments as $item) {
                    $grdobj = new stdClass();
                    $grades = $item->grades;
                    $pg = isset($grades->provisional[$student->id]) ? $grades->provisional[$student->id] : null;
                    $gb = isset($grades->gradebook[$student->id]) ? $grades->gradebook[$student->id] : null;
                    $ncg = isset($grades->converted[$student->id]) ? $grades->converted[$student->id] : null;
                    $grd = (isset($pg) && !is_null($pg->finalgrade)) ? $pg->finalgrade
                    : (isset($pg) && !is_null($pg->rawgrade) ? $pg->rawgrade
                    : ((isset($gb) && !is_null($gb->grade)) ? $gb->grade : null));
                    $processed = false; // Used for subcat grades.
                    $autoconvertb = false; // Used for subcat grades auto converted to schedule B.
                    if ($item->modname == 'category') {
                        // Get aggregation type.
                        list($subcatgrd, $processed, $error) = self::get_aggregated_grade($student->id, $item, $assessments);
                        $grd = $processed ? (is_null($subcatgrd) ? null : $subcatgrd->grade)
                        : (is_null($subcatgrd) ? $grd : $subcatgrd->grade);
                        if (!is_null($subcatgrd)) {
                            // Set the grade type and scale id of the sub category column.
                            $item->scaleid = $subcatgrd->scaleid;
                            $item->gradeitem->gradetype = $subcatgrd->gradetype;
                            $item->gradeitem->grademax = $subcatgrd->grademax;
                            // Check auto convert to B flag for subcat grades.
                            if (isset($subcatgrd->autoconvertb) && $subcatgrd->autoconvertb) {
                                $autoconvertb = true;
                            }
                        }
                        if ($error) {
                            $errors[$item->gradeitemid] = $error;
                        }
                    }
                    $gt = $item->gradeitem->gradetype;
                    $gm = $item->gradeitem->grademax;
                    $scaleid = is_null($item->scaleid) ? null : $item->scaleid;
                    // If subcat grades are auto converted to schedule B, set scale to B.
                    local_gugcat::set_grade_scale($scaleid, ($autoconvertb ? SCHEDULE_B : SCHEDULE_A));
                    if ($item->modname == 'category' && is_null($grd)) {
                        $item->gradetypename = get_string('nogradeweight', 'local_gugcat');
                    } else {
                        if ($gt == GRADE_TYPE_SCALE || $item->is_converted) {
                            $item->gradetypename = reset(local_gugcat::$grades) == 'A0' || $item->is_converted == SCHEDULE_B
                            ? get_string('scheduleb', 'local_gugcat')
                            : get_string('schedulea', 'local_gugcat');
                        } else {
                            $a = new stdClass();
                            $a->max = ($item->modname == 'category') ? 100 : intval($gm);
                            $item->gradetypename = get_string('points', 'local_gugcat', $a);
                        }
                    }
                    local_gugcat::is_child_activity($item) || $item->is_converted ? null : $gradetypes[] = intval($gt);
                    $grade = is_null($grd) ? ( $item->is_resit ? get_string('nogradeweight', 'local_gugcat')
                    : ( $processed ? get_string('missinggrade', 'local_gugcat')
                    : get_string('nograderecorded', 'local_gugcat')))
                    : local_gugcat::convert_grade($grd, $gt, ($autoconvertb ? SCHEDULE_B : SCHEDULE_A));
                    // If subcat grades are auto converted to schedule B, set scale to B.

                    $grdvalue = get_string('nograderecorded', 'local_gugcat');
                    $grade = ($grade === (float)0) ? number_format(0, 3) : $grade;
                    if ($item->is_converted && !is_null($grd)) {
                        $grade = local_gugcat::convert_grade($grd, null, $item->is_converted);
                    }

                    // Normalize sched B grades.
                    $grd = local_gugcat::normalize_gcat_grades($grade) ? local_gugcat::normalize_gcat_grades($grade) : $grd;

                    // Pass final $grd to altgrades.
                    $grades->altgrades[$student->id] = $grd;

                    // Get weight from gradebook if adjusted weight in $pg->information is null.
                    $weight = local_gugcat::is_child_activity($item) ? 0
                    : (!is_null($pg) && !is_null($pg->information) ? (float)$pg->information : $item->weight);
                    // Only aggregate grades that are:
                    // Not null.
                    // Not MEDICAL_EXEMPTION_AC (MV, -1).
                    // In 22 pt scale.
                    if (!is_null($grd) && $grade !== MEDICAL_EXEMPTION_AC) {
                        if ($grade === NON_SUBMISSION_AC || local_gugcat::is_child_activity($item)) {
                            $newweight = 0;
                        } else {
                            $newweight = $weight;
                            $feedback .= "$item->gradeitemid-$weight, ";
                        }
                        // Add the weights for schedule A or B, to be used in converting aggregated grade.
                        if (!empty($item->is_converted)) {
                            ($item->is_converted == SCHEDULE_A) ? $schedaweights += $newweight : $schedbweights += $newweight;
                        } else {
                            preg_match('/^[Aa1]{2}/', reset(local_gugcat::$grades))
                            ? $schedaweights += $newweight : $schedbweights += $newweight;
                        }

                        // Normalize to actual grade value (-1) for computation if its grade type is scale.
                        $grdvalue = is_numeric($grade) ? $grd : (($grade === NON_SUBMISSION_AC) ? 0 : (float)$grd - (float)1);
                        $floatweight += $newweight;
                        $sumaggregated += (float)$grdvalue * $newweight;
                        $calculatedweight += local_gugcat::is_child_activity($item) ? 0 : (float)$weight;
                    } else if (!is_null($grd) && $grade == MEDICAL_EXEMPTION_AC && ($meritgi || $gpagi)) {
                        $hasmvgrade = true;
                    }
                    $getcategory = ($item->modname != 'category'
                        && $category = local_gugcat::is_child_activity($item)) ? $category : false;

                    $grdobj->activityid = $item->gradeitemid;
                    $grdobj->activityinstance = $item->instance;
                    $grdobj->activity = $item->name;
                    $grdobj->category = $getcategory;
                    $grdobj->is_subcat = ($item->modname == 'category') ? true : false;
                    $grdobj->is_imported = !is_null($pg) ? true : false;
                    $grdobj->is_child = local_gugcat::is_child_activity($item) ? true : false;
                    $grdobj->grade = $grade;
                    $grdobj->nonconvertedgrade = (isset($ncg) && !is_null($ncg->finalgrade))
                    ? $ncg->finalgrade : (isset($ncg) && !is_null($ncg->rawgrade) ? $ncg->rawgrade : null);
                    $grdobj->rawgrade = $grdvalue;
                    $grdobj->originalweight = round((float)$item->weight * 100);
                    $grdobj->weight = round((float)$weight * 100 );

                    // Check the next grade to check if scale is sched B when grade is H.
                    $gradescale = ($grade == 'H') ? local_gugcat::convert_grade($grd+1) : $grade;
                    $grdobj->scale = in_array($gradescale, local_gugcat::$schedulea) ? SCHEDULE_A
                    : (in_array($gradescale, local_gugcat::$scheduleb) ? SCHEDULE_B : null);

                    $gradecaptureitem->grades[$grdobj->activityid] = $grdobj;
                }
                $sumaggregated = $sumaggregated != 0 ? $sumaggregated / $calculatedweight : $sumaggregated;
                $totalweight = round((float)$floatweight * 100 );
                $gradecaptureitem->completed = $totalweight . '%';
                local_gugcat::set_grade_scale(null);
                $aggrdscaletype = ($schedaweights >= $schedbweights) ? SCHEDULE_A : SCHEDULE_B;
                $aggrdobj->scale = $aggrdscaletype;

                $gbaggregatedgrade = !is_null($aggradeid) ? $DB->get_record('grade_grades', array('itemid' => $aggradeid,
                 'userid' => $student->id)) : null;
                // Aggregated grade condition boolean.
                $defaultaggregated = !$gbaggregatedgrade || ($gbaggregatedgrade && $gbaggregatedgrade->overridden == 0);
                $gradecaptureitem->resit = ($gbaggregatedgrade && preg_match('/\b'.$categoryid.'/i',
                 $gbaggregatedgrade->information) ? $gbaggregatedgrade->information : null);
                $rawaggrade = ($defaultaggregated)
                ? $sumaggregated : (!is_null($gbaggregatedgrade->finalgrade)
                ? $gbaggregatedgrade->finalgrade : $gbaggregatedgrade->rawgrade);
                // Convert back to moodle scale.
                $aggrade = $defaultaggregated ? ($aggrdscaletype == SCHEDULE_B
                ? floor($rawaggrade) + 1 : round($rawaggrade) + 1) : $rawaggrade;
                $aggrdobj->grade = local_gugcat::convert_grade($aggrade, null, $aggrdscaletype);
                $aggrdobj->rawgrade = $rawaggrade;
                $numberformat = number_format($rawaggrade, 3);
                // Only get main activities and categories.
                $filtered = array_filter($gradecaptureitem->grades, function($item) {
                     return !$item->is_child;
                });
                $filtergrade = array_column($filtered, 'grade');
                $aggrdobj->display = in_array(get_string('nograderecorded', 'local_gugcat'), $filtergrade, true)
                || in_array(get_string('missinggrade', 'local_gugcat'), $filtergrade, true)
                ? get_string('missinggrade', 'local_gugcat')
                : ($defaultaggregated ? ($totalweight < 75 ? $numberformat
                : local_gugcat::convert_grade($aggrade, null, $aggrdscaletype) .' ('.$numberformat.')')
                : local_gugcat::convert_grade($aggrade, null, $aggrdscaletype));
                // Check if assessments gradetypes has point grade type, if yes, display error and missing grade.
                if (in_array(GRADE_TYPE_VALUE, $gradetypes)) {
                    $aggrdobj->grade = null;
                    $aggrdobj->rawgrade = null;
                    $aggrdobj->display = get_string('missinggrade', 'local_gugcat');
                    $errors[0] = get_string('aggregationwarningcourse', 'local_gugcat');
                }
                if ($gbaggregatedgrade) {
                    $aggradegb = (!is_null($gbaggregatedgrade->finalgrade)
                    ? $gbaggregatedgrade->finalgrade : $gbaggregatedgrade->rawgrade);
                    $feedback .= ",_grade: $aggrdobj->display ,_$gbaggregatedgrade->feedback";
                    ($gbaggregatedgrade->overridden == 0 && round((float)$sumaggregated, 5) != round((float)$aggradegb, 5)
                    && $aggrdobj->display != get_string('missinggrade', 'local_gugcat')
                    && is_null($activityid)) ? local_gugcat::update_grade($student->id, $aggradeid, $sumaggregated, $feedback)
                    : null;
                    $DB->set_field('grade_grades', 'feedback', '', array('id' => $gbaggregatedgrade->id));
                }

                // Calculate alternative grade.

                // Merit grade.
                if ($meritgi && $meritsettings) {
                    $meritgradeweights = array();
                    $ids = array_column($meritsettings, 'itemid');
                    $weights = array_column($meritsettings, 'weight', 'itemid');

                    $selectedmerits = array_filter($assessments, function($item) use($ids){
                        return in_array($item->gradeitemid, $ids, true);
                    });
                    foreach ($selectedmerits as $item) {
                        $item->meritweight = $weights[$item->gradeitemid];
                        $gradeweight = $gradecaptureitem->grades[$item->gradeitemid];
                        $displayweight = round((float)$weights[$item->gradeitemid] * 100);
                        $gradeweight->weight = $displayweight;
                        array_push($meritgradeweights, $gradeweight);
                    }
                    $gradecaptureitem->meritgrade = self::get_alt_grade(true, $meritgi, $selectedmerits, $student->id);
                    $gradecaptureitem->meritgrade->grades = $meritgradeweights;
                    $gradecaptureitem->highlightMV = ($gradecaptureitem->meritgrade->overridden) ? false : $hasmvgrade;
                }

                // GPA grade.
                if ($gpagi && $gpasettings) {
                    $gpagrade = array();
                    $ids = array_column($gpasettings, 'itemid');
                    $gpacap = array_column($gpasettings, 'cap', 'itemid');

                    $selectedgpa = array_filter($assessments, function($item) use($ids){
                        return in_array($item->gradeitemid, $ids, true);
                    });
                    foreach ($selectedgpa as $item) {
                        $item->gpacap = $gpacap[$item->gradeitemid];
                        array_push($gpagrade, $gradecaptureitem->grades[$item->gradeitemid]);
                    }
                    $gradecaptureitem->gpagrade = self::get_alt_grade(false, $gpagi, $selectedgpa, $student->id, $aggrdobj);
                    $gradecaptureitem->gpagrade->gpacap = reset($gpacap);
                    $gradecaptureitem->gpagrade->grades = $gpagrade;
                    $gradecaptureitem->highlightMV = ($gradecaptureitem->gpagrade->overridden) ? false : $hasmvgrade;
                }
            }
            $gradecaptureitem->aggregatedgrade = $aggrdobj;
            array_push($rows, $gradecaptureitem);
            $i++;
        }
        // Display error for highlighted MV grades.
        if (in_array(true, array_column($rows, 'highlightMV'), true)) {
            $errors[-1] = get_string('highlightedmv', 'local_gugcat');
        }

        // Save aggregated grade scale type for course grade history.
        if ($aggradeid && !is_null($aggrdscaletype)) {
            $DB->set_field('grade_items', 'idnumber', $aggrdscaletype, array('id' => $aggradeid));
        }
        // Display the errors from aggregation.
        if ($showerrors) {
            \core\notification::fetch();
            foreach ($errors as $e) {
                local_gugcat::notify_error(null, $e);
            }
        }
        return $rows;
    }

    /**
     * Toggles the requires resit button in grade aggregation
     *
     * @param int $studentno as the student's user id
     * @return boolean
     */
    public static function require_resit($studentno) {
        global $aggradeid, $USER, $DB;
        if (!$aggradeid) {
            return false;
        }
        $categoryid = optional_param('categoryid', '0', PARAM_INT);
        $aggrade = $DB->get_record('grade_grades', array('itemid' => $aggradeid, 'userid' => $studentno), 'id, information');
        $gradeobj = new stdClass();
        $gradeobj->id = $aggrade->id;
        $gradeobj->usermodified = $USER->id;
        $gradeobj->timemodified = time();
        $gradeobj->information = null;
        if (preg_match('/\b'.$categoryid.'/i', $aggrade->information)) {
            $gradeobj->information = null;
            $status = "disable";
        } else {
            $gradeobj->information .= $categoryid.' ';
            $status = "enable";
        }
        $DB->update_record('grade_grades', $gradeobj);
        return $status;
    }

    /**
     * Get the aggregated grade for the sub category total
     *
     * @param int $userid The student's user id
     * @param mixed $subcatobj The sub category gradeitem with grades' obj
     * @param array $gradeitems Array of the gradeitems/activities with grades' obj
     * @return mixed array list($grade, $processed, $error)
     * @return @param mixed $grade Grade object - grade, gradetype, scaleid
     * @return @param boolean $processed - Not necessary aggregated, but checked if the provisional grade can be aggregated
     * @return @param string $error
     */
    public static function get_aggregated_grade($userid, $subcatobj, $gradeitems) {
        global $DB;
        $pgobj = isset($subcatobj->grades->provisional[$userid]) ? $subcatobj->grades->provisional[$userid] : null;

        $categoryid = $subcatobj->id;
        $grdobj = new stdClass();
        // Get the provisional grade of the sub cat total.
        $grd = (isset($pgobj) && !is_null($pgobj->finalgrade)) ? $pgobj->finalgrade
            : (isset($pgobj) && !is_null($pgobj->rawgrade) ? $pgobj->rawgrade : null);

        // Get child grade items.
        $filtered = array_filter($gradeitems, function ($gi) use ($categoryid) {
            return $gi->gradeitem->categoryid == $categoryid;
        });

        // Return grade = null if there are no children.
        if (count($filtered) == 0) {
            return array(null, false, null);
        }

        // Filter child grade items object to grades.
        $actgrds = array_column($filtered, 'grades', 'gradeitemid');

        $studentgrades = array();
        foreach ($actgrds as $id => $grades) {
            // Get provisional grades $pg from child assessments.
            $pg = isset($grades->provisional[$userid]) ? $grades->provisional[$userid] : null;
            // Get gradebook grades $gb from child assessments.
            $gb = isset($grades->gradebook[$userid]) ? $grades->gradebook[$userid] : null;
            $grdca = (isset($pg) && !is_null($pg->finalgrade)) ? $pg->finalgrade
            : (isset($pg) && !is_null($pg->rawgrade) ? $pg->rawgrade
            : ((isset($gb) && !is_null($gb->grade)) ? $gb->grade : null));
            $studentgrades[$id] = is_null($grdca) ? null : intval($grdca);
        }
        $ishighestgrade = $subcatobj->aggregation == GRADE_AGGREGATE_MAX;

        $droplow = $subcatobj->droplow;
        $totalchildren = count($subcatobj->children) - $droplow;
        // If drop lowest is not empty, remove the n number of lowest grades, including -1, -2.
        if ($droplow > 0) {
            asort($studentgrades, SORT_NUMERIC);
            $studentgrades = array_slice($studentgrades, $subcatobj->droplow, count($studentgrades), true);
        }

        // Return grade = null, processed = true if all components are not graded for weighted/mean/mode/median/natural.
        if (!$ishighestgrade && count(array_filter($studentgrades, 'is_numeric')) != $totalchildren) {
            return array(null, true, null);
        } else if ($ishighestgrade && count(array_filter($studentgrades, 'is_numeric')) == 0) {
            return array(null, true, null);
        }

        // Overall gradetype, grademax and scaleid to be used in subcat grade.
        $gradetype = null;
        $grademax = null;
        $scaleid = null;
        // Array of components' grade items to be used in the calculation.
        $grditems = array_column($filtered, 'gradeitem', 'gradeitemid');

        $errstr = get_string('aggregationwarningcomponents', 'local_gugcat');
        $isschedulea = false;
        $firstgi = null;
        // Change gradetype and grademax of gradeitems that are converted.
        foreach ($filtered as $item) {
            if (is_null($firstgi)) {
                $firstgi = $item->gradeitem;
            }
            if ($firstgi->scaleid != $item->gradeitem->scaleid) {
                $isschedulea = true;
            }
            if ($item->gradeitem->gradetype == GRADE_TYPE_VALUE && $item->is_converted) {
                $item->gradeitem->gradetype = GRADE_TYPE_SCALE;
                $item->gradeitem->grademax = '23.00000';
            }
        }

        $gradetypes = array_column($grditems, 'gradetype', 'id');
        $grademaxs = array_column($grditems, 'grademax', 'id');

        // Check if components are converted to schedule B, if yes, add flag on subcat grade.
        $converted = array_column($filtered, 'is_converted');
        $autoconverttob = !empty($converted) && count(array_unique($converted)) == 1 && $converted[0] == SCHEDULE_B;
        if ($autoconverttob) {
            $grdobj->autoconvertb = true;
        }

        // If all components are converted, override subcat->is_converted flag.
        if (count($converted) == count(array_filter($converted))) {
            $subcatobj->is_converted = false;
        }

        // Check if components grade types are the same.
        if (count(array_unique($gradetypes)) == 1) {
            // Get first grade item.
            $gi = $grditems[key($grditems)];
            if ($gi->gradetype == GRADE_TYPE_VALUE) {
                $subcatobj->gradeitem->gradetype = GRADE_TYPE_VALUE;
                $gradetype = GRADE_TYPE_VALUE;
                $grademax = $subcatobj->gradeitem->grademax;
            } else if (count(array_unique($grademaxs)) == 1 && local_gugcat::is_scheduleAscale($gi->gradetype, $gi->grademax)) {
                $subcatobj->gradeitem->gradetype = GRADE_TYPE_SCALE;
                $gradetype = GRADE_TYPE_SCALE;
                $grademax = $gi->grademax;
                $scaleid = $isschedulea ? null : $gi->scaleid;
            } else {
                if ($pgobj && !is_null($grd)) {
                    local_gugcat::update_grade($userid, $pgobj->itemid, null, '');
                }
                return array(null, true, $errstr);
            }
        } else {
            if ($pgobj && !is_null($grd)) {
                local_gugcat::update_grade($userid, $pgobj->itemid, null, '');
            }
            return array(null, true, $errstr);
        }

        // Return provisional grade if overridden.
        if ($pgobj && $pgobj->overridden != 0) {
            $grdobj->grade = $grd;
            $grdobj->gradetype = $gradetype;
            $grdobj->grademax = $grademax;
            $grdobj->scaleid = $scaleid;
            return array($grdobj, false, null);
        }

        // Check if $studentgrades still have admingrades, if yes, return admin grades instead.
        if (in_array(NON_SUBMISSION, $studentgrades) && !$ishighestgrade) {
            $calculatedgrd = NON_SUBMISSION;
        } else if (in_array(MEDICAL_EXEMPTION, $studentgrades) && !$ishighestgrade) {
            $calculatedgrd = MEDICAL_EXEMPTION;
        } else {
            // If $studentgrades dont have admin grades, proceed to calculation.
            $calculatedgrd = self::calculate_grade($subcatobj, $studentgrades, $grditems);
        }

        // If subcategory is converted, convert the calculated grade.
        if ($subcatobj->is_converted) {
            $calculatedgrd = grade_converter::convert($subcatobj->conversion, $calculatedgrd);
        }

        // If calculation field is empty, then update it with aggregation type.
        if ($pgobj) {
            $updated = false;
            $autoscale = !is_null($scaleid) ? $scaleid : ($autoconverttob ? SCHEDULE_B
            : ($gradetype == GRADE_TYPE_SCALE ? SCHEDULE_A : null));
            is_null($subcatobj->aggregation_type) ? $DB->set_field('grade_items', 'calculation', $subcatobj->aggregation,
             array('id' => $pgobj->itemid)) : null;
            is_null($subcatobj->automaticscale) ? $DB->set_field('grade_items', 'outcomeid', $autoscale,
             array('id' => $pgobj->itemid)) : null;
            $scale = $subcatobj->is_converted ? $subcatobj->is_converted : (!is_null($autoscale) ? $autoscale : null);
            if (!is_null($subcatobj->aggregation_type) && $subcatobj->aggregation_type != $subcatobj->aggregation) {
                $notes = !is_null($scale) && !empty($scale) ? 'aggregation -'.$scale : 'aggregation';
                // Update calculation field with aggregation type.
                $DB->set_field('grade_items', 'calculation', $subcatobj->aggregation, array('id' => $pgobj->itemid));
            }
            if (!is_null($scale) && !$subcatobj->is_converted && $subcatobj->automaticscale != $autoscale) {
                $notes = "grade -$scale";
                $componentnotes = 'grade';
                local_gugcat::update_grade($userid, $pgobj->itemid, $calculatedgrd, $notes);
                foreach ($filtered as $id => $childact) {
                    if (isset($childact->grades->provisional[$userid]) && $pg = $childact->grades->provisional[$userid]) {
                        // Get idnumber of provisional grades for scales.
                        $scale = $childact->is_converted;
                        $notes = $scale ? $componentnotes . " -" . $scale : $componentnotes;
                        // Only get provisional grades $pg from child assessments.
                        local_gugcat::update_components_notes($userid, $pg->itemid, $notes);
                    }
                }
                $updated = true;
                $DB->set_field('grade_items', 'outcomeid', $autoscale, array('id' => $pgobj->itemid));
            }
            $DB->set_field('grade_grades', 'feedback', '', array('id' => $pgobj->id));
        }

        if ($pgobj && isset($calculatedgrd) && round((float)$grd, 5) != round((float)$calculatedgrd, 5) && !$updated) {
            // If subcategory is new then update grade with "import" notes for grade history.
            if (is_null($grd) && !$subcatobj->is_converted) {
                $notes = 'import';
                local_gugcat::update_grade($userid, $pgobj->itemid, $calculatedgrd, $notes);
                foreach ($actgrds as $id => $grades) {
                    if (isset($grades->provisional[$userid]) && $pg = $grades->provisional[$userid]) {
                        // Only get provisional grades $pg from child assessments.
                        local_gugcat::update_components_notes($userid, $pg->itemid, $notes);
                    }
                    $DB->set_field('grade_grades', 'feedback', '', array('id' => $pgobj->id));
                }
            } else if ((is_null($pgobj->feedback) || empty($pgobj->feedback)) && (!isset($notes)
            || is_null($notes) || empty($notes))) {
                // If subcategory feedback is null or empty, and calculated grade is different from grdbook grade.
                $notes = !is_null($scale) ? "systemupdatecourse -$scale" : "systemupdatecourse";
                $componentnotes = 'systemupdatecourse';
                local_gugcat::update_grade($userid, $pgobj->itemid, $calculatedgrd, $notes);
                foreach ($filtered as $id => $childact) {
                    if (isset($childact->grades->provisional[$userid]) && $pg = $childact->grades->provisional[$userid]) {
                        // Get idnumber of provisional grades for scales.
                        $scale = $childact->is_converted;
                        $notes = $scale ? $componentnotes . " -$scale" : $componentnotes;
                        // Only get provisional grades $pg from child assessments.
                        local_gugcat::update_components_notes($userid, $pg->itemid, $notes);
                    }
                }
                $DB->set_field('grade_grades', 'feedback', '', array('id' => $pgobj->id));
            } else if (preg_match('/grade/i', $pgobj->feedback) || preg_match('/import/i', $pgobj->feedback)
            || (isset($notes) && preg_match('/aggregation/i', $notes))) {
                // If subcategory feedback is equal to grade, import or $notes is equal to aggregation.
                // If calculated grade is different from grdbook grade.
                $feedback = isset($notes) && preg_match('/aggregation/i', $notes) ? $notes : $pgobj->feedback;
                local_gugcat::update_grade($userid, $pgobj->itemid, $calculatedgrd, $feedback);
                foreach ($filtered as $id => $childact) {
                    if (isset($childact->grades->provisional[$userid]) && $pg = $childact->grades->provisional[$userid]) {
                        // Get idnumber of provisional grades for scales.
                        $scale = $childact->is_converted;
                        $componentnotes = preg_replace('/ \-./i', '', $feedback);
                        $notes = $scale ? $componentnotes . " -$scale" : $componentnotes;
                        // Only get provisional grades $pg from child assessments.
                        local_gugcat::update_components_notes($userid, $pg->itemid, $notes);
                    }
                }
                $DB->set_field('grade_grades', 'feedback', '', array('id' => $pgobj->id));
            } else {
                local_gugcat::update_grade($userid, $pgobj->itemid, $calculatedgrd, '');
            }
        } else if ($pgobj && isset($calculatedgrd) && !is_null($grd)
        && round((float)$grd, 5) == round((float)$calculatedgrd, 5)) {
            // If subcategory feedback is equal to grade, import or $notes is equal to aggregated.
            // If gradebook grade is not null and equal to calculated grade.
            if (preg_match('/grade/i', $pgobj->feedback) || preg_match('/import/i', $pgobj->feedback)
            || (isset($notes) && preg_match('/aggregation/i', $notes))) {
                $feedback = isset($notes) && preg_match('/aggregation/i', $notes) ? $notes : $pgobj->feedback;
                local_gugcat::update_grade($userid, $pgobj->itemid, $calculatedgrd, $feedback);
                foreach ($filtered as $id => $childact) {
                    if (isset($childact->grades->provisional[$userid]) && $pg = $childact->grades->provisional[$userid]) {
                        // Get idnumber of provisional grades for scales.
                        $scale = $childact->is_converted;
                        $componentnotes = preg_replace('/ \-./i', '', $feedback);
                        $notes = $scale ? $componentnotes . " -$scale" : $componentnotes;
                        // Only get provisional grades $pg from child assessments.
                        local_gugcat::update_components_notes($userid, $pg->itemid, $notes);
                    }
                }
                $DB->set_field('grade_grades', 'feedback', '', array('id' => $pgobj->id));
            }
        }
        $grdobj->grade = $calculatedgrd;
        $grdobj->gradetype = $gradetype;
        $grdobj->grademax = $grademax;
        $grdobj->scaleid = $scaleid;
        return array($grdobj, true, null);
    }

    /**
     * Checks aggregation type and returns the calculated grade, only calculates highest,
     *  lowest, average and weighted mean of grades
     *
     * @param mixed $subcatobj The subcategory activity object
     * @param array $gradevalues An array of values to be aggregated
     * @param array $items The array of grade_items
     * @return int $grade The new calculated grade
     */
    public static function calculate_grade($subcatobj, $gradevalues, $items) {
        if (empty($gradevalues)) {
            return null;
        }
        $aggregationtype = $subcatobj->aggregation;
        $subcatgt = $subcatobj->gradeitem->gradetype;
        $subcatgmax = 100;
        $subcatgmin = 0;
        $aggrade = null; // Aggregated grade.
        $grades = self::normalize_grades($gradevalues, $items);
        switch ($aggregationtype) {
            case GRADE_AGGREGATE_MIN:
                $aggrade = min($grades);
                break;
            case GRADE_AGGREGATE_MAX:
                $aggrade = max($grades);
                break;
            case GRADE_AGGREGATE_WEIGHTED_MEAN:// Weighted average of all existing final grades, weight specified in coef.
                $weightsum = 0;
                $sum = 0;
                foreach ($grades as $itemid => $gradevalue) {
                    if (!isset($items[$itemid]) || $items[$itemid]->aggregationcoef <= 0) {
                        continue;
                    }
                    $weightsum += $items[$itemid]->aggregationcoef;
                    $sum += $items[$itemid]->aggregationcoef * $gradevalue;
                }
                $aggrade = ($weightsum == 0) ? null : $sum / $weightsum;
                break;
            case GRADE_AGGREGATE_MEDIAN:
                sort($grades);
                $count = count($grades);
                $middleval = floor(($count - 1) / 2);
                if ($count % 2) {
                    $aggrade = $grades[$middleval];
                } else {
                    $low = $grades[$middleval];
                    $high = $grades[$middleval + 1];
                    $aggrade = (($low + $high) / 2);
                }
                break;
            case GRADE_AGGREGATE_MODE:
                // The most common value.
                // Function array_count_values only counts INT and STRING, so if grades are floats we must convert them to string.
                $convertedgradevalues = array();
                foreach ($grades as $k => $gv) {
                    if (!is_int($gv) && !is_string($gv)) {
                        $convertedgradevalues[$k] = (string) $gv;
                    } else {
                        $convertedgradevalues[$k] = $gv;
                    }
                }

                $freq = array_count_values($convertedgradevalues);
                arsort($freq);                     // Sort by frequency keeping keys.
                $top = reset($freq);               // Highest frequency count.
                $modes = array_keys($freq, $top);  // Search for all modes (have the same highest count).
                rsort($modes, SORT_NUMERIC);       // Get highest mode.
                $aggrade = reset($modes);
                break;
            case GRADE_AGGREGATE_SUM:
                if (reset($items)->gradetype == GRADE_TYPE_VALUE) {
                    $sum = array_sum($gradevalues);
                    $grademax = 0;
                    foreach ($gradevalues as $itemid => $value) {
                        if (!isset($items[$itemid])) {
                            continue;
                        }
                        $grademax += intval($items[$itemid]->grademax);
                    }
                    $aggrade = ($sum / $grademax) * 100;
                } else {
                    $num = count($gradevalues);
                    $sum = array_sum($grades);
                    $aggrade = $sum / $num;
                }
                break;
            case GRADE_AGGREGATE_MEAN:
            default:
                $num = count($gradevalues);
                $sum = array_sum($grades);
                $aggrade = $sum / $num;
        }
        $aggrade = round($aggrade);

        return ($subcatgt == GRADE_TYPE_VALUE)
            ? grade_grade::standardise_score($aggrade, $subcatgmin, $subcatgmax, 0, 100)
            : $aggrade;
    }

    /**
     * Normalize grades from scale or points
     *
     * @param array $gradevalues An array of values to be aggregated
     * @param array $items The array of grade_items
     * @return array $normalizegrades An array of normalized grades
     */
    public static function normalize_grades($gradevalues, $items) {
        $normalizegrades = array();
        foreach ($gradevalues as $itemid => $gradevalue) {
            if (!isset($items[$itemid])) {
                continue;
            }
            $gradetype = $items[$itemid]->gradetype;
            $grademax = $items[$itemid]->grademax;
            $grademin = $items[$itemid]->grademin;
            if ($gradetype == GRADE_TYPE_VALUE) {
                $normalizegrades[$itemid] = grade_grade::standardise_score($gradevalue, $grademin, $grademax, 0, 100);
            } else {
                $normalizegrades[$itemid] = $gradevalue;
            }
        }
        return $normalizegrades;
    }

    /**
     * Adjust the provisional weights of a specific student
     *
     * @param array $weights
     * @param int $courseid
     * @param int $studentid
     */
    public static function adjust_course_weight($weights, $courseid, $studentid) {
        // Iterate the weights, $key = gradeitem id, $value = weight.
        foreach ($weights as $key => $value) {
            $weight = number_format(($value / 100), 5);
            $gradeitem = grade_item::fetch(array('courseid' => $courseid, 'id' => $key));
            $id = ($gradeitem->itemtype == 'category') ? $gradeitem->iteminstance : $key;
            $itemname = get_string( ($gradeitem->itemtype == 'category') ? 'subcategorygrade' : 'provisionalgrd', 'local_gugcat');
            $prvgrdid = local_gugcat::get_grade_item_id($courseid, $id, $itemname);
            $grade = new grade_grade(array('userid' => $studentid, 'itemid' => $prvgrdid), true);
            $grade->information = $weight;
            $grade->timemodified = time();
            $grade->update();
        }
        local_gugcat::notify_success('successadjustweight');
    }

    /**
     * Release final assessment grades for all the students
     *
     * @param int $courseid
     */
    public static function release_final_grades($courseid) {
        global $USER, $DB;
        // Retrieve modules and enrolled students per grade category.
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $modules = (is_null($categoryid)) ? local_gugcat::get_activities($courseid)
        : self::get_parent_child_activities($courseid, $categoryid);
        $students = get_enrolled_users(context_course::instance($courseid), 'local/gugcat:gradable');
        foreach ($modules as $mod) {
            $issubcat = ($mod->modname == 'category') ? true : false;
            // If mod is subcat then continue.
            if ($issubcat) {
                continue;
            }
            // Get/create provisional/converted grade id of the module.
            $grditemid = ($mod->is_converted) ? local_gugcat::get_grade_item_id($courseid, $mod->gradeitem->id,
            get_string('convertedgrade', 'local_gugcat')) : local_gugcat::add_grade_item($courseid,
            get_string('provisionalgrd', 'local_gugcat'), $mod);

            $gradeitem = new grade_item($mod->gradeitem);

            foreach ($students as $student) {
                // Get the provisional/converted grade of the student.
                $prvcnvgrd = $DB->get_record('grade_grades', array('itemid' => $grditemid,
                'userid' => $student->id), 'rawgrade, finalgrade');
                $grd = is_null($prvcnvgrd->finalgrade) ? $prvcnvgrd->rawgrade : $prvcnvgrd->finalgrade;

                // Check if grade is admin grade.
                $grade = intval($grd);
                $grade = ($grade == NON_SUBMISSION || $grade == MEDICAL_EXEMPTION) ? null : $grade;

                // Update gradebook grade if provisional grade is not null.
                if (!is_null($grd)) {
                    $gradeitem->update_final_grade($student->id, $grade, null, null, FORMAT_MOODLE, $USER->id);
                }
                // Update gradebook information field to final.
                $DB->set_field_select('grade_grades', 'information', 'final', "itemid = $gradeitem->id AND userid = $student->id");

            }
        }
        local_gugcat::notify_success('successfinalrelease');
    }

    /**
     * Process the structure of the data from the aggregation tool table to be downloaded
     *
     * @param mixed $course
     */
    public static function export_aggregation_tool($course, $categoryid = null) {
        $table = get_string('aggregationtool', 'local_gugcat');
        $filename = "export_$table"."_".date('Y-m-d_His');
        $columns = ['Grade Category', 'Student Number'];
        $isblindmarking = local_gugcat::is_blind_marking();
        $isblindmarking ? null : array_push($columns, ...array('Surname', 'Forename'));
        $modules = ($categoryid == null) ? local_gugcat::get_activities($course->id) :
                                           self::get_parent_child_activities($course->id, $categoryid);
        $category = is_null($categoryid) ? null : grade_category::fetch(array('id' => $categoryid));
        $students = get_enrolled_users(context_course::instance($course->id), 'local/gugcat:gradable');
        // Add the columns before the activities.
        array_push($columns, ...['Aggregated Grade', 'Aggregated Grade Numeric', '% Complete', 'Resit Required']);
        $data = self::get_rows($course, $modules, $students);

        // Add alternative grades in columns.
        $meritstr = get_string('meritgrade', 'local_gugcat');
        $meritstrnum = "$meritstr Numeric";
        $gpastr = get_string('gpagrade', 'local_gugcat');
        $gpastrnum = "$gpastr Numeric";
        if ($data[0] && $data[0]->meritgrade) {
            array_push($columns, ...[$meritstr, $meritstrnum]);
        }
        if ($data[0] && $data[0]->gpagrade) {
            array_push($columns, ...[$gpastr, $gpastrnum]);
        }
        // Process the activity names.
        $activities = array();
        foreach ($modules as $cm) {
            $weight = "$cm->name Weighting";
            $alpha = "$cm->name Alphanumeric Grade";
            $ispoints = is_null($cm->gradeitem->scaleid) && is_null($cm->is_converted);
            $maxgrade = (int)$cm->gradeitem->grademax;
            $numeric = ($ispoints) ? "$cm->name Numeric Points Max($maxgrade)" : "$cm->name Numeric Grade";
            $activities[$cm->gradeitemid] = array($weight, $alpha, $numeric);
            array_push($columns, ...array($weight, $alpha, $numeric));
        }
        $displaymerit = false;
        $displaygpa = false;
        // Process the data to be iterated.
        $array = array();
        foreach ($data as $row) {
            $student = new stdClass();
            $student->{'Grade Category'} = is_null($categoryid) ? get_string('uncategorised', 'grades') : $category->fullname;
            $student->{'Student Number'} = $row->idnumber;
            if (!$isblindmarking) {
                $student->{'Surname'} = $row->surname;
                $student->{'Forename'} = $row->forename;
            }
            // Check if grade is aggregated.
            $isaggregated = ($row->aggregatedgrade->display != get_string('missinggrade', 'local_gugcat')) ? true : false;
            $student->{'Aggregated Grade'} = $isaggregated ? $row->aggregatedgrade->grade : null;
            $student->{'Aggregated Grade Numeric'} = $isaggregated ? (local_gugcat::is_admin_grade($row->aggregatedgrade->rawgrade)
            ? get_string('nogradeweight', 'local_gugcat') : $row->aggregatedgrade->rawgrade) : null;
            $student->{'% Complete'} = $row->completed;
            $student->{'Resit Required'} = is_null($row->resit) ? 'N' : 'Y';
            // Add student's alternative grades.
            $row->meritgrade ? $student->{$meritstr} = $row->meritgrade->grade : null;
            $row->meritgrade ? $student->{$meritstrnum} = (is_null($row->meritgrade->rawgrade)
            ? get_string('nogradeweight', 'local_gugcat')
            : $row->meritgrade->rawgrade) : null;
            $row->gpagrade ? $student->{$gpastr} = $row->gpagrade->grade : null;
            $row->gpagrade ? $student->{$gpastrnum} = (is_null($row->gpagrade->rawgrade)
            ? get_string('nogradeweight', 'local_gugcat')
            : $row->gpagrade->rawgrade) : null;
            foreach ($activities as $key => $act) {
                $isconverted = !is_null($row->grades[$key]->nonconvertedgrade);
                $student->{$act[0]} = $row->grades[$key]->originalweight.'%'; // Weight.
                $student->{$act[1]} = !is_numeric($row->grades[$key]->grade) ? $row->grades[$key]->grade
                : get_string('nogradeweight', 'local_gugcat'); // Alphanumeric.
                $isadmingrade = local_gugcat::is_admin_grade(array_search($row->grades[$key]->grade, local_gugcat::$grades));
                $student->{$act[2]} = $isadmingrade ? get_string('nogradeweight', 'local_gugcat')
                : ($isconverted ? $row->grades[$key]->nonconvertedgrade : $row->grades[$key]->rawgrade); // Numeric.
            }
            array_push($array, $student);
        }
        // Convert array to ArrayObject to get the iterator.
        $exportdata = new ArrayObject($array);
        local_gugcat::export_gcat($filename, $columns, $exportdata->getIterator());
    }


    /**
     * Checks if the activity has a resit tag
     *
     * @param mixed $module selected course module
     * @return boolean
     */
    public static function is_resit($module) {
        global $DB;

        if ($taginstances = $DB->get_records('tag_instance', array('itemid' => $module->id), null, 'tagid')) {
            foreach ($taginstances as $taginstance) {
                $tag = $DB->get_field('tag', 'name', array('id' => $taginstance->tagid));

                if (!strcasecmp('resit', $tag)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns rows of history of adjusted weights and overridden grades
     *
     * @param mixed $course
     * @param array $modules
     * @param mixed $student
     */
    public static function get_course_grade_history($course, $modules, $student) {
        global $DB;

        $categoryid = optional_param('categoryid', 0, PARAM_INT);

        $rows = array();
        local_gugcat::set_grade_scale(null);
        $aggradeid = local_gugcat::get_grade_item_id($course->id, $categoryid, get_string('aggregatedgrade', 'local_gugcat'));
        if ($aggradeid) {
            $fields = 'id, itemid, rawgrade, finalgrade, feedback, timemodified, usermodified';
            $select = 'feedback IS NOT NULL AND rawgrade IS NOT NULL AND itemid='.$aggradeid.' AND '.' userid="'.$student->id.'"';
            $gradehistoryarr = $DB->get_records_select('grade_grades_history', $select, null, $fields);
            if ($gradehistoryarr > 0) {
                foreach ($gradehistoryarr as $grdhistory) {
                    $grdobj = new stdClass();
                    $grd = (is_null($grdhistory->finalgrade) ? (float)$grdhistory->rawgrade : (float)$grdhistory->finalgrade);
                    $grdobj->notes = null;
                    $grdobj->modby = null;
                    $pattern = "/,_/i";
                    $feedback = preg_split($pattern, $grdhistory->feedback, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($feedback as $fb) {
                        if (preg_match('/weights:/i', $fb)) {
                            $weightsitemid = preg_replace('/weights:/i', '', $fb);
                            $j = 0;
                            foreach ($modules as $mod) {
                                isset($grdobj->weights) ? null : $grdobj->weights = array();
                                if (preg_match('/'.$mod->gradeitem->id.'-[0-9\.]*,/i', $weightsitemid, $weightitemid)) {
                                    $weight = preg_replace('/'.$mod->gradeitem->id.'\-/', '', $weightitemid[0]);
                                    $weight = chop($weight, ',');
                                    $grdobj->weights[$j] = $weight;
                                }
                                $j++;
                            }
                        }
                        if (preg_match('/notes:/i', $fb)) {
                            $grdobj->notes = preg_replace('/.*notes:/i', '', $fb);
                            $modby = $DB->get_record('user', array('id' => $grdhistory->usermodified), 'firstname, lastname');
                            $grdobj->modby = (isset($modby->lastname) && isset($modby->firstname))
                            ? $modby->lastname . ', '.$modby->firstname : null;
                        }
                        if (preg_match('/grade:/i', $fb)) {
                            $grdobj->grade = preg_replace('/grade:/i', '', $fb);
                        }
                        if (preg_match('/scale:/i', $fb)) {
                            $grd = (is_null($grdhistory->finalgrade) ? (float)$grdhistory->rawgrade
                            : (float)$grdhistory->finalgrade);
                            $scale = preg_replace('/scale:/i', '', $fb);
                            $grdobj->grade = local_gugcat::convert_grade($grd, null, $scale);
                        }
                    }
                    $grdobj->timemodified = $grdhistory->timemodified;
                    $grdobj->date = date("j/n/y", strtotime(userdate($grdhistory->timemodified)))
                    .'<br>'.date("h:i", strtotime(userdate($grdhistory->timemodified)));
                    array_push($rows, $grdobj);
                }
            }
        }
        // Sort array by timemodified.
        usort($rows, function($first, $second){
            return $first->timemodified < $second->timemodified;
        });
        return $rows;
    }

    /**
     * Return list of both child and parent activities
     *
     * @param int $courseid
     * @param int $categoryid
     * @return array
     */
    public static function get_parent_child_activities($courseid, $categoryid) {
        // Retrieve sub categories.
        $gcs = grade_category::fetch_all(array('courseid' => $courseid, 'parent' => $categoryid));
        $cids = array($categoryid);

        // Combine retrieved sub categories and the main course category (ids).
        !empty($gcs) ? array_push($cids, ...array_column($gcs, 'id')) : null;

        // Retrieve activities based from categoryids.
        $rawactivities = local_gugcat::get_activities($courseid, $cids);

        $mainactivities = array();
        $childactivities = array();
        // Separate the main activities and child activites into two arrays.
        array_map(function($value) use (&$mainactivities, &$childactivities) {
            if (local_gugcat::is_child_activity($value)) {
                $childactivities[] = $value;
            } else {
                $mainactivities[] = $value;
            }
        }, $rawactivities);

        // Retrieve grade items of the grade categories.
        $gradecatgi = array();
        if (!empty($gcs)) {
            foreach ($gcs as $gc) {
                $gradecatgi[] = local_gugcat::get_category_gradeitem($courseid, $gc);
            }
        }
        // The final array to be pass to get_rows.
        $activities = array();
        // Combine the main activities and grade categories grade items.
        $mainactivities = array_merge($mainactivities, $gradecatgi);
        foreach ($mainactivities as $index => $act) {
            // Check if activity = category, insert the child activities next to it.
            if ($act->modname == 'category') {
                // Filter $childactivities to the children of the iterated category.
                $children = array_filter($childactivities,
                    function($value) use ($act) {
                        return $value->gradeitem->categoryid == $act->id;
                    }
                );
                $act->children = array_column($children, 'gradeitemid');
                if (!empty($children)) {
                    // Insert $children first before its category grade item.
                    array_push($activities, ...$children);
                }
            }
            $activities[] = $act;
        }
        return $activities;
    }

    /**
     * updates feedback field for subcategory grades and provisional grades
     * @param int $subcatid
     * @param int $categoryid
     * @param string $notes
     */
    public static function update_component_notes_for_all_students($subcatid, $categoryid, $notes) {
        global $DB;

        $courseid = optional_param('id', null, PARAM_INT);
        $students = get_enrolled_users(context_course::instance($courseid), 'local/gugcat:gradable', 0, 'u.id');
        $activities = local_gugcat::get_child_activities_id($courseid, $categoryid);
        $prvgrades = local_gugcat::get_prvgrd_item_ids($courseid, $activities);
        $userids = '';
        foreach ($students as $student) {
            $userids .= "userid=$student->id OR ";
        }
        // Remove last OR.
        $userids = chop($userids, ' OR ');
        // Get all subcat grds.
        $select = "itemid=$subcatid AND ($userids) AND rawgrade IS NOT NULL";
        $fields = 'id, itemid, userid, rawgrade, finalgrade, overridden';
        $subcatgrds = $DB->get_records_select('grade_grades', $select, null, null, $fields);
        foreach ($subcatgrds as $subcatgrd) {
            local_gugcat::update_components_notes($subcatgrd->userid, $subcatid, $notes);
            foreach ($prvgrades as $prvgrd) {
                $componentnotes = preg_replace('/ \-./i', '', $notes);
                $isscale = !is_null($prvgrd->idnumber) ? $prvgrd->idnumber : false;
                $newnotes = $isscale ? $componentnotes . " -" . $isscale : $componentnotes;
                local_gugcat::update_components_notes($subcatgrd->userid, $prvgrd->id, $newnotes);
            }
        }
    }

    /**
     * Create or edit alternative course grade settings
     * @param int $alttype Either Merit or GPA
     * @param array $assessments
     * @param array $weights
     * @param int $appliedcap
     * @return boolean true | false
     */
    public static function create_edit_alt_grades($alttype, $assessments, $weights = array(), $appliedcap = null) {
        global $DB, $COURSE;
        $ismerit = $alttype == MERIT_GRADE;
        $altstr = get_string(($ismerit ? 'meritgrade' : 'gpagrade'), 'local_gugcat');
        $altgi = local_gugcat::add_grade_item($COURSE->id, $altstr, null);
        // Delete existing settings first.
        $DB->delete_records('gcat_acg_settings', array('acgid' => $altgi));
        $acgitems = array();
        foreach ($assessments as $id => $value) {
            $weight = $ismerit && !empty($weights) ? number_format(($weights[$id] / 100), 5) : null;
            $acgitems[] = array('acgid' => $altgi, 'itemid' => $id, 'weight' => $weight, 'cap' => $appliedcap);
        }
        return $DB->insert_records('gcat_acg_settings', $acgitems);
    }

    /**
     * Calculate and return alternative course grade
     *
     * @param boolean $ismerit
     * @param int $itemid grade item id of alt course grade
     * @param array $selectedacts selected assessments from alt grade form
     * @param int $userid student id
     * @param object $aggrdobj aggregated grade object
     * @return object $altgrdobj
     */
    public static function get_alt_grade($ismerit, $itemid, $selectedacts, $userid, $aggrdobj = null) {
        global $DB;
        $meritsumgrade = 0;
        $meritsumweight = 0;
        $altgrdobj = new stdClass();
        $altgrdobj->overridden = false;
        $altgg = $DB->get_record('grade_grades', array('itemid' => $itemid, 'userid' => $userid));
        $altggrd = !$altgg ? null : (!is_null($altgg->finalgrade)
            ? $altgg->finalgrade : $altgg->rawgrade);
        local_gugcat::set_grade_scale(null);
        // If merit grade is overridden.
        if ($altgg && $altgg->overridden != 0) {
            $altgrdobj->grade = $altggrd ? local_gugcat::convert_grade($altggrd) : get_string('missinggrade', 'local_gugcat');
            $altgrdobj->rawgrade = $altggrd;
            $altgrdobj->overridden = true;
            return $altgrdobj;
        } else {
            $allgrades = array();
            $altgrade = null;
            $cap = null;
            foreach ($selectedacts as $item) {
                $grades = $item->grades;
                $cap = $ismerit ? null : $item->gpacap;
                $grd = isset($grades->altgrades[$userid]) ? $grades->altgrades[$userid] : null;
                $allgrades[] = is_null($grd) ? null : intval($grd);
                if ($ismerit) {
                    // Only aggregate grades that are:
                    // Not null.
                    // Not MEDICAL_EXEMPTION_AC (MV, -1).
                    // In 22 pt scale.
                    if (!is_null($grd) && intval($grd) !== MEDICAL_EXEMPTION) {
                        $weight = $item->meritweight;

                        // Normalize to actual grade value (-1) for computation if its grade type is scale.
                        $grdvalue = intval($grd) == NON_SUBMISSION ? 0 : (float)$grd - (float)1;
                        $meritsumweight += $weight;
                        $meritsumgrade += (float)$grdvalue * $weight;
                    }
                }
            }
            if ($ismerit) {
                // Merit grade.
                // If selected assessment grades has missing or no grade, display missing grade.
                if (in_array(null, $allgrades, true)) {
                    $altgrade = null;
                } else {
                    $altgrade = $meritsumgrade != 0 ? $meritsumgrade / $meritsumweight : 0;
                }
            } else {
                // GPA grade.
                // If all selected assessment grades has missing or no grade, display aggregated grade.
                if (in_array(null, $allgrades, true) && count(array_unique($allgrades)) == 1) {
                    $altgrade = $aggrdobj->rawgrade;
                } else {
                    $cap = (intval($cap) == NON_SUBMISSION) ? -1 : intval($cap - 1);
                    $aggrade = $aggrdobj->rawgrade;
                    $altgrade = $aggrade <= $cap ? $aggrade : $cap;
                }
            }

            if (round((float)$altgrade, 5) != round((float)$altggrd, 5)) {
                local_gugcat::update_grade($userid, $itemid, $altgrade);
            }
            $altgrdobj->grade = !is_null($altgrade)
            ? ($altgrade == 0 ? 0 : local_gugcat::convert_grade($altgrade + 1))
            : get_string('missinggrade', 'local_gugcat');
            $altgrdobj->rawgrade = $altgrade;
            return $altgrdobj;
        }
    }

    /**
     *  Returns rows of history of alternative course grades
     *
     * @param mixed $course
     * @param mixed $student
     * @return array $rows
     */
    public static function acg_grade_history($course, $student, $acg) {
        global $DB;

        $categoryid = optional_param('categoryid', 0, PARAM_INT);
        local_gugcat::set_grade_scale(null);
        $rows = array();
        $acgid = local_gugcat::get_grade_item_id($course->id, $categoryid,
         get_string($acg == 1 ? 'meritgrade' : 'gpagrade', 'local_gugcat'));
        if ($acgid) {
            $fields = 'id, itemid, rawgrade, finalgrade, feedback, timemodified, usermodified';
            $select = "rawgrade IS NOT NULL AND itemid=$acgid AND userid='$student->id'";
            $gradehistoryarr = $DB->get_records_select('grade_grades_history', $select, null, $fields);
            if ($gradehistoryarr > 0) {
                foreach ($gradehistoryarr as $gradehistory) {
                    $grdobj = new stdClass();
                    $grd = !is_null($gradehistory->finalgrade) ? $gradehistory->finalgrade : null;
                    $grdobj->grade = local_gugcat::convert_grade(($gradehistory->overridden != 0 ? $grd : $grd + 1));
                    $grdobj->notes = !is_null($gradehistory->feedback) && !empty($gradehistory->feedback)
                    ? $gradehistory->feedback : get_string('systemupdatecreateupdate', 'local_gugcat');
                    $modby = $DB->get_record('user', array('id' => $gradehistory->usermodified), 'firstname, lastname');
                    $grdobj->modby = (isset($modby->lastname) && isset($modby->firstname))
                    ? $modby->lastname . ', '.$modby->firstname : null;
                    $grdobj->date = date("j/n/y", strtotime(userdate($gradehistory->timemodified)))
                    .'<br>'.date("h:i", strtotime(userdate($gradehistory->timemodified)));
                    $grdobj->type = get_string($gradehistory->overridden != 0 ? 'gradeoverridden' : 'systemupdate', 'local_gugcat');
                    $grdobj->timemodified = $gradehistory->timemodified;
                    array_push($rows, $grdobj);

                    // Sort array by timemodified.
                    usort($rows, function($first, $second){
                        return $first->timemodified < $second->timemodified;
                    });
                }
            }
        }

        return $rows;
    }
}