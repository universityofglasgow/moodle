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
 * Class containing helper methods for Grade Capture page.
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
use grade_item;
use grade_grade;
use local_gugcat;
use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once('gcat_item.php');

 /**
  * Class containing helper methods for Grade Aggregation page.
  */

class grade_capture{

     /**
      * Returns rows for grade capture table
      *
      * @param mixed $course
      * @param mixed $module
      * @param mixed $students
      */
    public static function get_rows($course, $module, $students) {
        $captureitems = array();
        global $gradeitems, $firstgradeid;
        $gradeitems = array();
        $gt = null; // Gradetype.
        $isconverted = false;
        // Error to display.
        $error = null;
        if (isset($module)) {
            $gt = $module->gradeitem->gradetype;
            $gbgrades = grade_get_grades($course->id, 'mod', $module->modname, $module->instance, array_keys($students));
            $gbgradeitem = array_values(array_filter($gbgrades->items, function($item) use($module){
                return $item->itemnumber == $module->gradeitem->itemnumber; // Filter grades with specific itemnumber.
            }));
            $releasedgrades = isset($gbgradeitem[0]) ? $gbgradeitem[0]->grades : null;
            if ($firstgradeid = local_gugcat::get_grade_item_id($course->id, $module->gradeitemid,
             get_string('moodlegrade', 'local_gugcat'))) {
                $gradeitems = local_gugcat::get_grade_grade_items($course, $module);
                $convertedgrades = array();
                $isconverted = $module->is_converted;
                if ($isconverted) {
                    $conversion = grade_converter::retrieve_grade_conversion($module->gradeitemid);
                    // Get converted grade item and remove it from the gradeitems array.
                    foreach ($gradeitems as $i => $gi) {
                        if ($gi->itemname == get_string('convertedgrade', 'local_gugcat')) {
                            $convertedgrades = $gi->grades;
                            unset($gradeitems[$i]);
                            break;
                        }
                    }
                }
                // Ids needed for grade discrepancy.
                $agreedgradeid = local_gugcat::get_grade_item_id($course->id, $module->gradeitemid,
                 get_string('gi_agreedgrade', 'local_gugcat'));
                $secondgradeid = local_gugcat::get_grade_item_id($course->id, $module->gradeitemid,
                 get_string('gi_secondgrade', 'local_gugcat'));
                $thirdgradeid = local_gugcat::get_grade_item_id($course->id, $module->gradeitemid,
                 get_string('gi_thirdgrade', 'local_gugcat'));
            }
        }
        $i = 1;
        foreach ($students as $student) {
            $gradecaptureitem = new gcat_item();
            $gradecaptureitem->cnum = $i;
            $gradecaptureitem->studentno = $student->id;
            $gradecaptureitem->surname = $student->lastname;
            $gradecaptureitem->forename = $student->firstname;
            $gradecaptureitem->idnumber = $student->idnumber;
            $gradecaptureitem->discrepancy = false;
            $gradecaptureitem->grades = array();
            $gradecaptureitem->firstgrade = get_string('nogradeimport', 'local_gugcat');
            $gradecaptureitem->hidden = null;
            if ($firstgradeid) {
                // Get released grade.
                if (count($releasedgrades) > 0) {
                    $gbg = isset($releasedgrades[$student->id]) ? $releasedgrades[$student->id] : null;
                    if ($module->modname == 'assign') {
                        $assign = new assign(context_module::instance($module->id), $module, $course->id);
                        $assigngrd = $assign->get_user_grade($student->id, false);
                        $gbg = local_gugcat::get_gb_assign_grade($assigngrd, $gbg);
                    }
                    // Normalize grades.
                    $gbg = local_gugcat::normalize_gcat_grades($gbg);
                    $gradescaleoffset = local_gugcat::is_grademax22($module->gradeitem->gradetype,
                     $module->gradeitem->grademax) ? 1 : 0;
                    $grade = self::check_gb_grade($gbg, $gradescaleoffset);
                    $gradecaptureitem->releasedgrade = is_null($grade) ? null : local_gugcat::convert_grade($grade, $gt);
                }
                // Get converted grade.
                if ($isconverted && count($convertedgrades) > 0) {
                    $cg = isset($convertedgrades[$student->id]) ? $convertedgrades[$student->id] : null;
                    $cgg = (is_null($cg) || is_null($cg->grade)) ? null : grade_converter::convert($conversion, $cg->grade);
                    $gradecaptureitem->convertedgrade = is_null($cgg) ? get_string('nograde', 'local_gugcat') 
                    : local_gugcat::convert_grade($cgg, null, $module->is_converted);
                }
                // Get first grade and provisional grade.
                $gifg = $gradeitems[$firstgradeid]->grades;
                $gipg = $gradeitems[intval(local_gugcat::$prvgradeid)]->grades;
                $fg = (isset($gifg[$student->id])) ? $gifg[$student->id]->grade : null;
                $pg = (isset($gipg[$student->id])) ? $gipg[$student->id]->grade : null;
                // Create provisional grade for newly added student.
                if (is_null($pg)) {
                    local_gugcat::add_update_grades($student->id, local_gugcat::$prvgradeid, null, null, false);
                }
                $gradecaptureitem->firstgrade = is_null($fg) ? get_string('nograde', 'local_gugcat') 
                : local_gugcat::convert_grade($fg, $gt);

                $gradecaptureitem->provisionalgrade = is_null($pg) ? get_string('nograde', 'local_gugcat') :
                ($isconverted ? local_gugcat::convert_grade($pg, null, $module->is_converted)
                : local_gugcat::convert_grade($pg, $gt));
                $agreedgrade = (!$agreedgradeid) ? null : (isset($gradeitems[$agreedgradeid]->grades[$student->id])
                ? $gradeitems[$agreedgradeid]->grades[$student->id]->grade : null);
                $sndgrade = (!$secondgradeid) ? null : (isset($gradeitems[$secondgradeid]->grades[$student->id])
                ? $gradeitems[$secondgradeid]->grades[$student->id]->grade : null);
                $trdgrade = (!$thirdgradeid) ? null : (isset($gradeitems[$thirdgradeid]->grades[$student->id])
                ? $gradeitems[$thirdgradeid]->grades[$student->id]->grade : null);

                foreach ($gradeitems as $item) {
                    if (isset($item->grades[$student->id]->hidden) && $item->grades[$student->id]->hidden == 1) {
                        $gradecaptureitem->hidden = true;
                    }
                    if ($item->id != local_gugcat::$prvgradeid && $item->id != $firstgradeid) {
                        $rawgrade = (isset($item->grades[$student->id])) ? $item->grades[$student->id]->grade : null;
                        $grdobj = new stdClass();
                        $grade = is_null($rawgrade) ? 'N/A' : local_gugcat::convert_grade($rawgrade, $gt);
                        $grdobj->grade = $grade;
                        $grdobj->discrepancy = false;

                        // Check grade discrepancy, compare to first grade and agreed grade.
                        if (is_null($agreedgrade) && $fg) {
                            if ($item->id === $secondgradeid || $item->id === $thirdgradeid) {
                                $grdobj->discrepancy = is_null($rawgrade) ? false
                                : (($rawgrade != $fg) ? true // Compare to first grade.
                                : ((!is_null($sndgrade) && $rawgrade != $sndgrade) ? true // Compare to 2nd grade.
                                : ((!is_null($trdgrade) && $rawgrade != $trdgrade) ? true : false))); // Compare to 3rd grade.
                            }
                        }
                        if ($grdobj->discrepancy) {
                            $gradecaptureitem->discrepancy = true;
                        }
                        array_push($gradecaptureitem->grades, $grdobj);
                    }
                }
                // Display error when grade from grade book is different from gcat moodle grade.
                if (!is_null($gradecaptureitem->releasedgrade)) {
                    // If gradebook grade is not null and different from moodle grade.
                    if ($gradecaptureitem->firstgrade != $gradecaptureitem->releasedgrade) {
                        $error = get_string('warningreimport', 'local_gugcat');
                    }
                } else {
                    // If gradebook grade is null but moodle grade is not null.
                    if ($gradecaptureitem->firstgrade != get_string('nograde', 'local_gugcat')) {
                        $error = get_string('warningreimport', 'local_gugcat');
                    }
                }
            }
            array_push($captureitems, $gradecaptureitem);
            $i++;
        }
        is_null($error) ? null : local_gugcat::notify_error(null, $error);
        return $captureitems;
    }

    /**
     * Returns columns for grade capture table
     *
     */
    public static function get_columns() {
        // Global $gradeitems from get rows function.
        global $gradeitems, $firstgradeid;
        $columns = array();
        if (!$firstgradeid) {
            $firstgrade = get_string('moodlegrade', 'local_gugcat').'<br>[Date]';
            $columns = array($firstgrade);
        }
        $firstcolumn = null;
        foreach ($gradeitems as $item) {
            if ($item->itemname == get_string('moodlegrade', 'local_gugcat')) {
                // Add the date of the moodle grade item.
                $firstcolumn = $item->itemname.'<br>'.date("[j/n/Y]", strtotime(userdate($item->timemodified)));
            } else {
                $columns[$item->id] = $item->itemname;
            }
        }
        // Remove provisional column.
        if (local_gugcat::$prvgradeid) {
            unset($columns[local_gugcat::$prvgradeid]);
        }
        !is_null($firstcolumn) ? array_unshift($columns, $firstcolumn) : null; // Always put moodle grade first.
        return $columns;
    }

     /**
      * Release provisional grades for all the students on a specific module
      *
      * @param int $courseid
      * @param mixed $cm Selected course module
      */
    public static function release_prv_grade($courseid, $cm) {
        global $USER, $CFG, $DB;
        $gradeitemid = $cm->gradeitem->id;

        // Retrieve enrolled students' ids only.
        $students = get_enrolled_users(context_course::instance($courseid), 'local/gugcat:gradable', 0, 'u.id');

        // Get grade item.
        $gradeitem = $cm->gradeitem;
        if ($cm->modname === 'assign') {
            require_once($CFG->dirroot . '/mod/assign/locallib.php');
            $assign = new assign(context_module::instance($cm->id), $cm, $courseid);
            $isworkflowenabled = $assign->get_instance()->markingworkflow == 1;
        }
        foreach ($students as $student) {
            // Get provisional grade_grade by user id.
            $fields = 'rawgrade, finalgrade, hidden';
            $itemid = $cm->is_converted ? local_gugcat::get_grade_item_id($courseid, $gradeitemid,
             get_string('convertedgrade', 'local_gugcat')) : local_gugcat::$prvgradeid;
            if ($prvgrd = $DB->get_record('grade_grades', array('itemid' => $itemid, 'userid' => $student->id), $fields)) {
                $grd = is_null($prvgrd->finalgrade) ? $prvgrd->rawgrade : $prvgrd->finalgrade;
                $hidden = $prvgrd->hidden;

                $select = "itemid = $gradeitemid AND userid = $student->id";
                // Update hidden status.
                $DB->set_field_select('grade_grades', 'hidden', $hidden, $select);
                if (!is_null($grd) && !empty($grd) && $hidden == 0) {
                    $rawgrade = intval($grd);
                    switch ($rawgrade) {
                        case NON_SUBMISSION:
                            $feedback = NON_SUBMISSION_AC;
                            $isadmingrade = true;
                            $rawgrade = null;
                            $excluded = 0;
                            break;
                        case MEDICAL_EXEMPTION:
                            $feedback = MEDICAL_EXEMPTION_AC;
                            $isadmingrade = true;
                            $rawgrade = null;
                            $excluded = 1; // Excluded from aggregation.
                            break;
                        default:
                            $isadmingrade = false;
                            $feedback = null;
                            $excluded = 0;
                            break;
                    }

                    // Update feedback and excluded field.
                    $DB->set_field_select('grade_grades', 'feedback', $feedback, $select);
                    $DB->set_field_select('grade_grades', 'excluded', $excluded, $select);
                    if ($cm->modname === 'assign') {
                        // Update assign grade.
                        if ($grade = $assign->get_user_grade($student->id, true)) {
                            // Update workflow state marking worklow is enabled.
                            if ($isworkflowenabled) {
                                local_gugcat::update_workflow_state($assign, $student->id, ASSIGN_MARKING_WORKFLOW_STATE_RELEASED);
                            }
                            $DB->set_field_select('grade_grades', 'overridden', 0, $select);
                            $grade->grade = $isadmingrade ? 0 : $rawgrade;
                            $grade->grader = $USER->id;
                            // Always set blindmarking = false to update grades to gradebook.
                            $assign->get_instance()->blindmarking = false;
                            $assign->update_grade($grade);
                        }
                        if ($isadmingrade) {
                            $DB->set_field_select('grade_grades', 'finalgrade', $rawgrade, $select);
                        }
                    } else {
                        // Update grade from gradebook.
                        $gradeitem->update_final_grade($student->id, $rawgrade, null, false, FORMAT_MOODLE, $USER->id);
                    }
                    $DB->set_field_select('grade_grades', 'overridden', time(), $select);
                }
            } else {
                local_gugcat::add_update_grades($student->id, local_gugcat::$prvgradeid, null);
            }
        }
        // Unhide gradeitem.
        $gradeitem->hidden = 0;
        $gradeitem->update();
    }

    /**
     * Import grades from assign grades/gradebook grades to GCAT moodle grade item
     *
     * @param int $courseid
     * @param mixed $importactivities Selected course module, or modules to be imported
     * @param array $allactivities Use to update all weights
     */
    public static function import_from_gradebook($courseid, $importactivities, $allactivities) {
        global $DB;
        // Retrieve all enrolled students' ids only.
        $students = get_enrolled_users(context_course::instance($courseid), 'local/gugcat:gradable', 0, 'u.id');
        $modules = array();
        if (is_array($importactivities)) {
            $modules = $importactivities;
        } else {
            $modules = array($importactivities);
        }

        foreach ($modules as $module) {
            // Create Provisional Grade grade item and grade_grades to all students, then assign it to static PRVID.
            local_gugcat::$prvgradeid = is_null($module->provisionalid)
            ? local_gugcat::add_grade_item($courseid, get_string('provisionalgrd', 'local_gugcat'), $module, $students)
            : $module->provisionalid;

            // Get provisional gradeitem id for weights reset.
            // Create subcategory gradeitem for components.
            $prvidreset = null;
            if (local_gugcat::is_child_activity($module)) {
                // Get parent category object from array $allactivities.

                // Get sub categories.
                $categories = array_filter($allactivities, function ($act) use ($module) {
                    return $act->modname == 'category' && $act->instance == $module->gradeitem->categoryid;
                });
                // Get parent subcat activity object.
                $parent = reset($categories);

                // Create provisional gradeitem of the $parent subcategory if its null.
                if (is_null($parent->provisionalid)) {
                    // Clone category object so original obj will not be updated.
                    $x = clone($parent);
                    $x->gradeitemid = $module->gradeitem->categoryid;
                    $prvidreset = local_gugcat::add_grade_item($courseid, get_string('subcategorygrade', 'local_gugcat'), $x);
                } else {
                    $prvidreset = $parent->provisionalid;
                }
            } else {
                $prvidreset = local_gugcat::$prvgradeid;
            }

            // Check if module is converted.
            if ($module->is_converted) {
                // If conversion is enabled, get grade conversion and converted grade item id.
                $conversion = grade_converter::retrieve_grade_conversion($module->gradeitemid);
                $convertedgi = local_gugcat::get_grade_item_id($courseid, $module->gradeitemid,
                 get_string('convertedgrade', 'local_gugcat'));
            }

            // Create Aggregated Grade grade item to all students.
            $aggradeid = local_gugcat::add_grade_item($courseid, get_string('aggregatedgrade', 'local_gugcat'), null, $students);

            // Create Moodle Grade grade item and grade_grades to all students.
            $mggradeitemid = local_gugcat::add_grade_item($courseid, get_string('moodlegrade', 'local_gugcat'), $module, $students);
            // Update Moodle Grade timemodified.
            $DB->set_field('grade_items', 'timemodified', time(), array('id' => $mggradeitemid));

            $grade = null;

            $gbgrades = grade_get_grades($courseid, 'mod', $module->modname, $module->instance, array_keys($students));
            $gbgradeitem = array_values(array_filter($gbgrades->items, function($item) use($module){
                return $item->itemnumber == $module->gradeitem->itemnumber; // Filter grades with specific itemnumber.
            }));
            foreach ($students as $student) {
                $gbg = isset($gbgradeitem[0]) ? $gbgradeitem[0]->grades[$student->id] : null;
                // Check if assignment.
                if ($module->modname == 'assign') {
                    $assign = new assign(context_module::instance($module->id), $module, $courseid);
                    $assigngrd = $assign->get_user_grade($student->id, false);
                    $gbg = local_gugcat::get_gb_assign_grade($assigngrd, $gbg);
                    local_gugcat::update_workflow_state($assign, $student->id, ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW);
                }
                $gbg = local_gugcat::normalize_gcat_grades($gbg);
                $grade = self::check_gb_grade($gbg);

                // For assessment grade history.
                $notes = ',_gradeitem: '. get_string('moodlegrade', 'local_gugcat');
                if ($module->is_converted) {
                    // Update moodle grade only.
                    local_gugcat::add_update_grades($student->id, $mggradeitemid, $grade, null, false);
                    // Convert grade.
                    $cg = grade_converter::convert($conversion, $grade);
                    // Update provisional grade with converted grade.
                    local_gugcat::add_update_grades($student->id, local_gugcat::$prvgradeid, $cg, $notes, false);
                    // Update converted grade item.
                    local_gugcat::add_update_grades($student->id, $convertedgi, $grade, null, false);
                } else {
                    // Update moodle and provisional grade.
                    local_gugcat::add_update_grades($student->id, $mggradeitemid, $grade, $notes);
                }
            }

            $studentids = implode(',', array_keys($students));
            $selectag = "itemid = $aggradeid AND userid in ( $studentids )";
            $DB->set_field_select('grade_grades', 'overridden', 0, $selectag);
            /* Every time import is clicked, reset weights in provisional grade > grade_grades information
            to null, and overridden subcategory grades.*/
            $selectpg = "itemid = $prvidreset AND userid in ( $studentids )";
            $DB->set_field_select('grade_grades', 'information', null, $selectpg);

            if (local_gugcat::is_child_activity($module)) {
                $DB->set_field_select('grade_grades', 'overridden', 0, $selectpg);
            }
        }
    }

    /**
     * Toggles hide and show grades in grade capture tab
     *
     * @param int $userid
     * @return boolean
     */
    public static function hideshowgrade($userid) {
        global $USER;

        $gradeobj = new grade_grade(array('userid' => $userid, 'itemid' => local_gugcat::$prvgradeid), true);
        $gradeobj->usermodified = $USER->id;
        $gradeobj->itemid = local_gugcat::$prvgradeid;
        $gradeobj->userid = $userid;
        $gradeobj->timemodified = time();
        if ($gradeobj->hidden == 0) {
            $gradeobj->hidden = 1;
            $message = 'hiddengrademsg';
            $status = 'hidden';
        } else {
            $gradeobj->hidden = 0;
            $message = 'showgrademsg';
            $status = 'shown';
        }
        local_gugcat::notify_success($message);
        $gradeobj->update();
        return $status;
    }

    /**
     * Returns gradebook grade, admin grade or null
     *
     * @param mixed $gbgobj - gradebook grade object per student
     * @param mixed $gradescaleoffset - added to grade
     */
    public static function check_gb_grade($gbgobj, $gradescaleoffset = 0) {
        if (is_null($gbgobj)) {
            return null;
        }
        $gbgrade = $gbgobj->grade;
        $feedback = $gbgobj->feedback;
        switch ($feedback) {
            case NON_SUBMISSION_AC:
                $admingrade = NON_SUBMISSION;
                break;
            case MEDICAL_EXEMPTION_AC:
                $admingrade = MEDICAL_EXEMPTION;
                break;
            default:
                $admingrade = null;
                break;
        }
        return !is_null($admingrade) ? $admingrade : ((isset($gbgrade)) ? ($gbgrade + $gradescaleoffset) : null);
    }

    /**
     * Checks and prepares grade data for updating grade items in gcat.
     *
     * @param object $csvimport csv import reader object for iterating over the imported CSV file.
     * @param mixed $activity selected course module to grade
     * @param string $itemname selected grade version for the new grades
     */
    public static function prepare_import_data($csvdata, $activity, $itemname) {
        $csvdata->init();
        global $COURSE;
        $gradebookerrors = array();
        $newgrades = array();
        $status = true;
        $enrolled = array();
        $grouped = array();
        $gradetype = $activity->gradeitem->gradetype;
        $grademax = $activity->gradeitem->grademax;
        // Get list of all student idnumbers enrolled on current course.
        $enrolled = self::get_students_per_groups(array(0), $COURSE->id, 'u.id, u.idnumber');
        // Get list of students in group.
        if ($activity->groupingid && $activity->groupingid > 0) {
            $grouped = self::get_students_per_groups(array($activity->groupingid), $COURSE->id, 'u.id, u.idnumber');
        }
        while ($line = $csvdata->next()) {
            if (count(array_filter($line)) == 0) {
                // There is no data on this line, move on.
                continue;
            }

            // Each line is a student record. First element is ID number, second is grade.
            $idnumber = $line[0];
            $grade = $activity->modname == 'assign' ? $line[2] : $line[3];
            $errorobj = new stdClass();
            $errorobj->id = $idnumber;
            $errorobj->value = $grade;

            // Check if student is not enrolled in current course.
            if (!in_array($idnumber, array_column($enrolled, 'idnumber'))) {
                $gradebookerrors[] = get_string('uploaderrornotfound', 'local_gugcat', $errorobj);
                $status = false;
                break;
            }

            // Check if student is not in the current group.
            if (count($grouped) > 0 && !in_array($idnumber, array_column($grouped, 'idnumber'))) {
                $gradebookerrors[] = get_string('uploaderrornotmember', 'local_gugcat', $errorobj);
                $status = false;
                break;
            }

            // If activity is scale, validate grades if its valid Schedule A or B.
            if ($gradetype == GRADE_TYPE_SCALE) {
                // Check if grade not alphanumeric.
                if (!preg_match('/^([mM][vV]|[Hh]|[a-zA-Z][0-9]|[nN][sS]|[a-zA-Z][0-9]:\d{1,2})$/', str_replace(' ', '', $grade))) {
                    $gradebookerrors[] = get_string('uploaderrorgradeformat', 'local_gugcat', $errorobj);
                    $status = false;
                    break;
                }

                // Check if grade is not in the scale.
                if (isset($grade) && !in_array(strtoupper($grade), local_gugcat::$grades)) {
                    $gradebookerrors[] = get_string('uploaderrorgradescale', 'local_gugcat', $errorobj);
                    $status = false;
                    break;
                }
            } else {
                // If activity is points, validate grades if its valid points or NS/MV.

                // Check if grade is greater than the max  grade of activity.
                if (is_numeric($grade) && $grade > $grademax) {
                    $gradebookerrors[] = get_string('uploaderrorgrademaxpoint', 'local_gugcat', $errorobj);
                    $status = false;
                    break;
                }

                // Check if grade is a valid grade point.
                if (!preg_match('/^([mM][vV]|[0-9]{1,3}|[nN][sS])$/', str_replace(' ', '', $grade))) {
                    $gradebookerrors[] = get_string('uploaderrorgradepoint', 'local_gugcat', $errorobj);
                    $status = false;
                    break;
                }
            }

            if ($status) {
                $userids = array_column($enrolled, 'id', 'idnumber');
                $newgrades[$userids[$idnumber]] = $grade;
            }
        }
        if ($status && count($newgrades) > 0) {
            $gradeitemid = local_gugcat::add_grade_item($COURSE->id, $itemname, $activity);
            foreach ($newgrades as $id => $item) {
                $grade = !is_numeric($item) ? array_search(strtoupper($item), local_gugcat::$grades) : $item;
                local_gugcat::add_update_grades($id, $gradeitemid, $grade, '');
                if ($activity->is_converted) {
                    /* If conversion is enabled, save the converted grade to provisional grade and
                    original grade to converted grade. */
                    $conversion = grade_converter::retrieve_grade_conversion($activity->gradeitemid);
                    $cg = grade_converter::convert($conversion, $grade);
                    local_gugcat::update_grade($id, local_gugcat::$prvgradeid, $cg, '');
                    $convertedgi = local_gugcat::get_grade_item_id($COURSE->id, $activity->gradeitemid,
                     get_string('convertedgrade', 'local_gugcat'));
                    local_gugcat::update_grade($id, $convertedgi, $grade, '');
                }
            }
        }
        return array($status, $gradebookerrors);
    }

    /**
     * Returns list of students based on grouping ids from activities
     *
     * @param array $groupingids ids from activities
     * @param int $courseid selected course id
     * @param string $userfields requested user record fields
     * @return array
     */
    public static function get_students_per_groups($groupingids, $courseid, $userfields = 'u.*') {
        $coursecontext = context_course::instance($courseid);
        $students = Array();
        if (array_sum($groupingids) != 0) {
            $groups = array();
            foreach ($groupingids as $groupingid) {
                if ($groupingid != 0) {
                    $groups += groups_get_all_groups($courseid, 0, $groupingid);
                }
            }
            if (!empty($groups)) {
                foreach ($groups as $group) {
                    $students += get_enrolled_users($coursecontext, 'local/gugcat:gradable', $group->id, $userfields);
                }
            }
        } else {
            $students = get_enrolled_users($coursecontext, 'local/gugcat:gradable', 0, $userfields);
        }
        return $students;
    }

    /**
     * Process the structure of the data for upload csv template
     *
     * @param mixed $activity
     */
    public static function download_template_csv($activity) {
        global $COURSE;
        $isassign = $activity->modname == 'assign';
        $filename = "upload_template_$activity->name"."_".date('Y-m-d_His');
        $columns = $isassign ? ['Student Number', 'Participant Number', 'Grades']
            : ['Student number', 'Last Name', 'First Name', 'Grades'];
        $fields = $isassign ? 'u.id, u.idnumber' : 'u.id, u.firstname, u.lastname, u.idnumber';
        $students = self::get_students_per_groups(array($activity->groupingid), $COURSE->id, $fields);
        $array = array();
        foreach ($students as $student) {
            $row = new stdClass();
            $row->{'Student Number'} = $student->idnumber;
            if ($isassign) {
                $row->{'Participant Number'} = assign::get_uniqueid_for_user_static($activity->instance, $student->id);
            } else {
                $row->{'Last Name'} = $student->lastname;
                $row->{'First Name'} = $student->firstname;
            }
            $row->{'Grades'} = null;
            array_push($array, $row);
        }
        // Convert array to ArrayObject to get the iterator.
        $exportdata = new ArrayObject($array);
        local_gugcat::export_gcat($filename, $columns, $exportdata->getIterator());
    }

}