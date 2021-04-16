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
use context_course;
use core\plugininfo\local;
use local_gugcat;
use stdClass;
use grade_grade;
use grade_category;
use grade_item;

defined('MOODLE_INTERNAL') || die();
require_once('gcat_item.php');

//grade form settings
define('ADJUST_WEIGHT_FORM', 0);
define('OVERRIDE_GRADE_FORM', 1);

 /**
 * Class containing helper methods for Grade Aggregation page.
 */

class grade_aggregation{
    
    /**
     * An array of course level administrative grades.
     * @var array $AGGRADE
     */
    public static $AGGRADE = array(
        CREDIT_WITHHELD => CREDIT_WITHHELD_AC,
        CREDIT_REFUSED => CREDIT_REFUSED_AC,
        CA => CA_AC,
        UNDER_INVESTIGATION => UNDER_INVESTIGATION_AC,
        AU => AU_AC,
        FC => FC_AC 
    );

     /**
     * Returns rows for grade aggregation table
     *
     * @param mixed $course
     * @param array $modules
     * @param array $students
     * @param boolean $showerrors
     */
    public static function get_rows($course, $modules, $students, $showerrors = false){
        global $DB, $aggradeid;
        $categoryid = optional_param('categoryid', '0', PARAM_INT);
        //get grade item id for aggregated grade
        $aggradeid = local_gugcat::get_grade_item_id($course->id, null, get_string('aggregatedgrade', 'local_gugcat'));
        $rows = array();
        $gradebook = array();
        foreach ($modules as $mod) {
            $mod->scaleid = $mod->gradeitem->scaleid;
            $mod->gradeitemid = $mod->gradeitem->id;
            $grades = new stdClass();
            $prvgrdid = null;
            // Get provisional gradeitem id and grades from gradebook for assessments and sub category
            if($mod->modname == 'category'){
                // Check if components are imported
                if(count($mod->children) != 0){
                    $componentsql = null;
                    foreach($mod->children as $id){
                        $componentsql .= "iteminfo = $id OR ";
                    }
                    $moodlestr = get_string('moodlegrade', 'local_gugcat');
                    //remove last OR
                    $componentsql = chop($componentsql, ' OR ');
                    // If atleast 1 component has moodle grade item, subcategory is imported, hence, get the 
                    // provisional grade (subcategory) grade item of the subcategory
                    if($DB->record_exists_select('grade_items', "itemname = '$moodlestr' AND ($componentsql)")){
                        // $mod->id - sub category id 
                        $prvgrdid = local_gugcat::get_grade_item_id($course->id, $mod->id, get_string('subcategorygrade', 'local_gugcat'));
                        $mod->aggregation_type = $DB->get_field('grade_items', 'calculation', array('id'=>$prvgrdid));
                    }
                }
                $gbgrades = grade_get_grades($course->id, 'category', null, $mod->instance, array_keys($students));
            }else{
                $prvgrdid = local_gugcat::set_prv_grade_id($course->id, $mod);
                $gbgrades = grade_get_grades($course->id, 'mod', $mod->modname, $mod->instance, array_keys($students));
            }

            $fields = 'userid, itemid, id, rawgrade, finalgrade, information, timemodified, overridden';
            // Get provisional grades
            $grades->provisional = is_null($prvgrdid) ? array() : $DB->get_records('grade_grades', array('itemid' => $prvgrdid), 'id', $fields);
            $mod->is_converted = !is_null($mod->gradeitem->iteminfo) && !empty($mod->gradeitem->iteminfo);
            if($mod->is_converted){
                $mod->conversion = grade_converter::retrieve_grade_conversion($mod->gradeitemid);
                $cvtgrdid = local_gugcat::get_grade_item_id($course->id, $mod->gradeitemid, get_string('convertedgrade', 'local_gugcat'));
                $grades->converted = $DB->get_records('grade_grades', array('itemid' => $cvtgrdid), 'id', $fields);
            }
            // Filter grades from gradebook with specific itemnumber
            $gbgradeitem = array_values(array_filter($gbgrades->items, function($item) use($mod){
                return $item->itemnumber == $mod->gradeitem->itemnumber;
            }));
            $grades->gradebook = isset($gbgradeitem[0]) ? $gbgradeitem[0]->grades : null;
            $mod->grades = $grades;
            array_push($gradebook, $mod);
        }
        // Errors to display
        $errors = array();
        //$i = candidate no. - Multiply it by the page number
        $page = optional_param('page', 0, PARAM_INT);  
        $i = $page * GCAT_MAX_USERS_PER_PAGE + 1;
        foreach ($students as $student) {
            $gradecaptureitem = new gcat_item();
            $gradecaptureitem->cnum = $i;
            $gradecaptureitem->studentno = $student->id;
            $gradecaptureitem->surname = $student->lastname;
            $gradecaptureitem->forename = $student->firstname;
            $gradecaptureitem->idnumber = $student->idnumber;
            $gradecaptureitem->grades = array();
            $floatweight = 0;
            $sumaggregated = 0;
            $aggrdobj = new stdClass();
            $aggrdobj->display =  get_string('missinggrade', 'local_gugcat') ;
            if(count($gradebook) > 0){
                $gradetypes = array();
                foreach ($gradebook as $item) {
                    $grditemresit = self::is_resit($item);
                    $grdobj = new stdClass();
                    $grades = $item->grades;
                    $pg = isset($grades->provisional[$student->id]) ? $grades->provisional[$student->id] : null;
                    $gb = isset($grades->gradebook[$student->id]) ? $grades->gradebook[$student->id] : null;
                    $ncg = isset($grades->converted[$student->id]) ? $grades->converted[$student->id] : null;
                    $grd = (isset($pg) && !is_null($pg->finalgrade)) ? $pg->finalgrade 
                    : (isset($pg) && !is_null($pg->rawgrade) ? $pg->rawgrade 
                    : ((isset($gb) && !is_null($gb->grade)) ? $gb->grade : null)); 
                    $processed = false; // Used for subcat grades
                    if($item->modname == 'category') {
                        //get aggregation type
                        list($subcatgrd, $processed, $error) = self::get_aggregated_grade($student->id, $item, $gradebook);
                        $grd = $processed ? (is_null($subcatgrd) ? null : $subcatgrd->grade) : (is_null($subcatgrd) ? $grd : $subcatgrd->grade);
                        if(!is_null($subcatgrd)){
                            // Set the grade type and scale id of the sub category column
                            $item->scaleid = $subcatgrd->scaleid;
                            $item->gradeitem->gradetype = $subcatgrd->gradetype;                            
                            $item->gradeitem->grademax = $subcatgrd->grademax;                            
                        }
                        if($error){
                            $errors[$item->gradeitemid] = $error;
                        }
                    }
                    $gt = $item->gradeitem->gradetype;
                    $gm = $item->gradeitem->grademax;
                    $scaleid = is_null($item->scaleid) ? null : $item->scaleid;
                    $is_scale = !is_null($scaleid) && local_gugcat::is_scheduleAscale($gt, $gm) && isset($pg);

                    local_gugcat::set_grade_scale($scaleid);
                    local_gugcat::is_child_activity($item) || $item->is_converted ? null: $gradetypes[] = intval($gt);
                    $grade = is_null($grd) ? ( $grditemresit ? get_string('nogradeweight', 'local_gugcat') :( $processed ? get_string('missinggrade', 'local_gugcat') : get_string('nograderecorded', 'local_gugcat'))) 
                    : local_gugcat::convert_grade($grd, $gt);
                    $grdvalue = get_string('nograderecorded', 'local_gugcat');
                    $grade = ($grade === (float)0) ? number_format(0, 3) : $grade;
                    if($item->is_converted && !is_null($grd)){
                        $is_scale = true;
                        $grade = local_gugcat::convert_grade($grd, null, $item->gradeitem->iteminfo);
                    }
                    $weight = local_gugcat::is_child_activity($item) ? 0 : (!is_null($pg) ? (float)$pg->information : 0); //get weight from information column of provisional grades
                    // Only aggregate grades that are:
                    // - not null
                    // - not MEDICAL_EXEMPTION_AC (MV, -1)
                    // - in 22 pt scale
                    // - grade type in points but converted to schedule A/B
                    $not_converted = $item->modname == 'category' && $item->gradeitem->gradetype == GRADE_TYPE_VALUE && !$item->is_converted;
                    if(!is_null($grd) && $grade !== MEDICAL_EXEMPTION_AC){
                        if($not_converted){
                            $weight_ = 0;
                        }else if($grade === NON_SUBMISSION_AC || local_gugcat::is_child_activity($item)){
                            $weight_ = 0;
                        }else{
                            $weight_ = $weight;
                        }
                        // Normalize to actual grade value (-1) for computation if its grade type is scale
                        $grdvalue = !$is_scale ? $grd : (($grade === NON_SUBMISSION_AC) ? 0 : (float)$grd - (float)1);
                        $floatweight += $weight_;
                        $sumaggregated += (float)$grdvalue * $weight_;
                    }
                    $get_category = ($item->modname != 'category' 
                        && $category = local_gugcat::is_child_activity($item)) ? $category : false;
                    
                    $grdobj->activityid = $item->gradeitemid;
                    $grdobj->activityinstance = $item->instance;
                    $grdobj->activity = $item->name;
                    $grdobj->category = $get_category;
                    $grdobj->is_subcat = ($item->modname == 'category') ? true : false;
                    $grdobj->is_imported = !is_null($pg) ? true : false;
                    $grdobj->is_child = local_gugcat::is_child_activity($item) ? true : false;
                    $grdobj->grade = $grade;
                    $grdobj->originalgrade = is_null($scaleid) ? $grade : $grdvalue;
                    $grdobj->nonconvertedgrade = (isset($ncg) && !is_null($ncg->finalgrade)) 
                    ? $ncg->finalgrade : (isset($ncg) && !is_null($ncg->rawgrade) ? $ncg->rawgrade : null);
                    $weightcoef1 = $item->gradeitem->aggregationcoef; //Aggregation coeficient used for weighted averages or extra credit
                    $weightcoef2 = $item->gradeitem->aggregationcoef2; //Aggregation coeficient used for weighted averages only
                    $originalweight = ((float)$weightcoef1 > 0) ? (float)$weightcoef1 : (float)$weightcoef2;
                    $grdobj->originalweight =  round((float)$originalweight * 100 );
                    $grdobj->rawgrade = $grdvalue;
                    $grdobj->weight =  round((float)$weight * 100 );
                    array_push($gradecaptureitem->grades, $grdobj);
                }
                if($gbaggregatedgrade = $DB->get_record('grade_grades', array('itemid'=>$aggradeid, 'userid'=>$student->id))){
                    $gradecaptureitem->resit = (preg_match('/\b'.$categoryid.'/i', $gbaggregatedgrade->information) ? $gbaggregatedgrade->information : null);
                    $totalweight = round((float)$floatweight * 100 );
                    $gradecaptureitem->completed = $totalweight . '%';
                    $rawaggrade = ($gbaggregatedgrade->overridden == 0) ? $sumaggregated : (!is_null($gbaggregatedgrade->finalgrade) ? $gbaggregatedgrade->finalgrade : $gbaggregatedgrade->rawgrade);
                    ($gbaggregatedgrade->overridden == 0) ? local_gugcat::update_grade($student->id, $aggradeid, $sumaggregated) : null;
                    $aggrade = ($gbaggregatedgrade->overridden == 0) ? round($rawaggrade) + 1 : $rawaggrade; //convert back to moodle scale
                    if(!(max(array_keys(local_gugcat::$GRADES)) >= 22)){
                        local_gugcat::set_grade_scale(null);
                    }
                    $aggrdobj->grade = local_gugcat::convert_grade($aggrade);
                    $aggrdobj->rawgrade = $rawaggrade;
                    $numberformat = number_format($rawaggrade, 3);
                    // Only get main activities and categories.
                    $filtered = array_filter($gradecaptureitem->grades, function($item){ return !$item->is_child; });
                    $filtergrade = array_column($filtered, 'grade');
                    $aggrdobj->display = in_array(get_string('nograderecorded', 'local_gugcat'), $filtergrade, true)
                    || in_array(get_string('missinggrade', 'local_gugcat'), $filtergrade, true)
                    ? get_string('missinggrade', 'local_gugcat') 
                    : ($gbaggregatedgrade->overridden == 0 ? ($totalweight < 75 ? $numberformat 
                    : local_gugcat::convert_grade($aggrade) .' ('.$numberformat.')') 
                    : local_gugcat::convert_grade($aggrade));
                    // Check if assessments gradetypes has point grade type, if yes, display error and missing grade
                    if(in_array(GRADE_TYPE_VALUE, $gradetypes)){
                        $aggrdobj->grade = null;
                        $aggrdobj->rawgrade = null;
                        $aggrdobj->display = get_string('missinggrade', 'local_gugcat');
                        $errors[0] = get_string('aggregationwarningcourse', 'local_gugcat');
                    }
                }

            }
            $gradecaptureitem->aggregatedgrade = $aggrdobj;
            array_push($rows, $gradecaptureitem);
            $i++;
        }
        // Display the errors from aggregation
        if($showerrors){
            foreach($errors as $e) {
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
    public static function require_resit($studentno){
        global $aggradeid, $USER;
        if(!$aggradeid) return false;
        $categoryid = optional_param('categoryid', '0', PARAM_INT);
        $grade_ = new grade_grade(array('userid' => $studentno, 'itemid' => $aggradeid), true);
        $grade_->usermodified = $USER->id;
        $grade_->itemid = $aggradeid;
        $grade_->userid = $studentno;
        $grade_->timemodified = time();
        if(preg_match('/\b'.$categoryid.'/i', $grade_->information)){
            $grade_->information = preg_replace('/\b'.$categoryid.' /i', '', $grade_->information);
            $status = "disable";
        }
        else{
            $grade_->information .= $categoryid.' ';
            $status = "enable";
        }
        $grade_->update();
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
    public static function get_aggregated_grade($userid, $subcatobj, $gradeitems){
        global $DB;
        if(isset($subcatobj->grades->provisional[$userid]) && $pgobj = $subcatobj->grades->provisional[$userid]){
            $categoryid = $subcatobj->id;
            $grdobj = new stdClass();
            // Get the provisional grade of the sub cat total
            $grd = (!is_null($pgobj->finalgrade)) ? $pgobj->finalgrade 
                : (!is_null($pgobj->rawgrade) ? $pgobj->rawgrade 
                : null);

            // Get child grade items
            $filtered = array_filter($gradeitems, function ($gi) use ($categoryid) {
                return $gi->gradeitem->categoryid == $categoryid;
            });

            // Return grade = null if there are no children
            if(count($filtered) == 0){
                return array(null, false, null);
            }

            // Filter child grade items object to grades
            $actgrds = array_column($filtered, 'grades', 'gradeitemid');
            
            $studentgrades = array();
            foreach ($actgrds as $id=>$grades) {
                // Get provisional grades $pg from child assessments
                $pg = isset($grades->provisional[$userid]) ? $grades->provisional[$userid] : null;
                // Get gradebook grades $gb from child assessments
                $gb = isset($grades->gradebook[$userid]) ? $grades->gradebook[$userid] : null;
                $grd_ = (isset($pg) && !is_null($pg->finalgrade)) ? $pg->finalgrade 
                : (isset($pg) && !is_null($pg->rawgrade) ? $pg->rawgrade 
                : ((isset($gb) && !is_null($gb->grade)) ? $gb->grade : null)); 
                $studentgrades[$id] = intval($grd_);
            }
            $is_highest_grade = $subcatobj->aggregation == GRADE_AGGREGATE_MAX;

            // If calculation field is empty, then update it with aggregation type
            is_null($subcatobj->aggregation_type) ? $DB->set_field('grade_items', 'calculation', $subcatobj->aggregation, array('id'=>$pgobj->itemid)) : null;
            $scale = $subcatobj->is_converted ? $subcatobj->gradeitem->iteminfo : null;
            if(!is_null($subcatobj->aggregation_type) && $subcatobj->aggregation_type != $subcatobj->aggregation){
                $notes = !is_null($scale) && !empty($scale) ? 'aggregation -'.$scale : 'aggregation';
                //update feedback field for subcat and child components prvgrade 
                local_gugcat::update_components_notes($userid, $pgobj->itemid, $notes);
                foreach($actgrds as $id=>$grades){
                    if(isset($grades->provisional[$userid]) && $pg = $grades->provisional[$userid]){
                        // Only get provisional grades $pg from child assessments
                        local_gugcat::update_components_notes($userid, $pg->itemid, $notes);
                    }        
                } 
                //update calculation field with aggregation type
                $DB->set_field('grade_items', 'calculation', $subcatobj->aggregation, array('id'=>$pgobj->itemid));
            }

            // Overall gradetype, grademax and scaleid to be used in subcat grade
            $gradetype = null;
            $grademax = null;
            $scaleid = null;
            // Array of components' grade items to be used in the calculation
            $grditems = array_column($filtered, 'gradeitem', 'gradeitemid');

            $errstr = get_string('aggregationwarningcomponents', 'local_gugcat');
            $is_schedule_a = false;
            $firstgi = null;
            // Change gradetype and grademax of gradeitems that are converted
            foreach($grditems as $item){
                if(is_null($firstgi)){
                    $firstgi = $item;
                }
                if ($firstgi->scaleid != $item->scaleid){
                    $is_schedule_a = true;
                }
                $is_converted = !is_null($item->iteminfo) && !empty($item->iteminfo);
                if($item->gradetype == GRADE_TYPE_VALUE && $is_converted){
                    $item->gradetype = GRADE_TYPE_SCALE;
                    $item->grademax = '23.00000';
                }
            }

            $gradetypes = array_column($grditems, 'gradetype', 'id');
            $grademaxs = array_column($grditems, 'grademax', 'id');

            // Check if components grade types are the same
            if(count(array_unique($gradetypes)) == 1){
                // Get first grade item
                $gi = $grditems[key($grditems)];
                if($gi->gradetype == GRADE_TYPE_VALUE){
                    $subcatobj->gradeitem->gradetype = GRADE_TYPE_VALUE;
                    $gradetype = GRADE_TYPE_VALUE;
                    $grademax = $subcatobj->gradeitem->grademax;
                }else if(count(array_unique($grademaxs)) == 1 && local_gugcat::is_scheduleAscale($gi->gradetype, $gi->grademax)){
                    $subcatobj->gradeitem->gradetype = GRADE_TYPE_SCALE;
                    $gradetype = GRADE_TYPE_SCALE;
                    $grademax = $gi->grademax;
                    $scaleid = $is_schedule_a ? null : $gi->scaleid;
                }else{
                    if(!is_null($grd)){
                        local_gugcat::update_grade($userid, $pgobj->itemid, null, '');
                    }
                    return array(null, true, $errstr);
                }
            }else{
                if(!is_null($grd)){
                    local_gugcat::update_grade($userid, $pgobj->itemid, null, '');
                }
                return array(null, true, $errstr);
            }

            // Return provisional grade if overridden
            if($pgobj->overridden != 0){
                $grdobj->grade = $grd;
                $grdobj->gradetype = $gradetype;
                $grdobj->grademax = $grademax;
                $grdobj->scaleid = $scaleid;
                if($subcatobj->is_converted){
                    $grdobj->grade = grade_converter::convert($subcatobj->conversion, $grd);
                }
                return array($grdobj, false, null);
            }

            // Aggregate only graded
            if($subcatobj->aggregateonlygraded == 1){
                $studentgrades = array_filter($studentgrades);
            }

            // If drop lowest is not empty, remove the n number of lowest grades, including -1, -2
            if($subcatobj->droplow > 0){
                asort($studentgrades, SORT_NUMERIC);
                $studentgrades = array_slice($studentgrades, $subcatobj->droplow, count($studentgrades), true);
            }

            // Check if $studentgrades still have admingrades, if yes, return admin grades instead
            if(in_array(NON_SUBMISSION, $studentgrades) && !$is_highest_grade){
                $calculatedgrd = NON_SUBMISSION;
            }else if(in_array(MEDICAL_EXEMPTION, $studentgrades) && !$is_highest_grade){
                $calculatedgrd = MEDICAL_EXEMPTION;
            }else{
                // If $studentgrades dont have admin grades, proceed to calculation
                $calculatedgrd = self::calculate_grade($subcatobj, $studentgrades, $grditems);
            }

            // If subcategory is converted, convert the calculated grade
            if($subcatobj->is_converted){
                $calculatedgrd = grade_converter::convert($subcatobj->conversion, $calculatedgrd);
            }
            if(isset($calculatedgrd) && $grd != $calculatedgrd){
                //if subcategory is new then update grade with "import" notes for grade history.
                if(is_null($grd) && !$subcatobj->is_converted){
                    $notes = 'import';
                    local_gugcat::update_grade($userid, $pgobj->itemid, $calculatedgrd, $notes);
                    foreach($actgrds as $id=>$grades){
                        if(isset($grades->provisional[$userid]) && $pg = $grades->provisional[$userid]){
                            // Only get provisional grades $pg from child assessments
                            local_gugcat::update_components_notes($userid, $pg->itemid, $notes);
                        }        
                    }   
                }else{
                    local_gugcat::update_grade($userid, $pgobj->itemid, $calculatedgrd, '');
                }    
            }
            $grdobj->grade = $calculatedgrd;
            $grdobj->gradetype = $gradetype;
            $grdobj->grademax = $grademax;
            $grdobj->scaleid = $scaleid;
            return array($grdobj, true, null);
        }else if((isset($subcatobj->grades->gradebook[$userid]) && $subcatobj->grades->gradebook[$userid])){
            // Calculate assessment grades from gradebook if sub category is not yet imported.
            $categoryid = $subcatobj->id;
            $grdobj = new stdClass();
            // Get child grade items
            $filtered = array_filter($gradeitems, function ($gi) use ($categoryid) {
                return $gi->gradeitem->categoryid == $categoryid;
            });

            // Return grade = null if there are no children
            if(count($filtered) == 0){
                return array(null, false, null);
            }

            // Filter child grade items object to grades
            $actgrds = array_column($filtered, 'grades', 'gradeitemid');
            $studentgrades = array();
            foreach ($actgrds as $id=>$grades) {
                // Only get grades from gradebook of child assessments
                $gb = isset($grades->gradebook[$userid]) ? $grades->gradebook[$userid] : null;
                if(is_null($gb)){
                    continue;
                }
                $studentgrades[$id] = intval($gb->grade);
            }
            $grdobj->grade = in_array(0, $studentgrades) ? null : 0;
            $grdobj->gradetype = $subcatobj->gradeitem->gradetype;
            $grdobj->grademax = $subcatobj->gradeitem->grademax;
            $grdobj->scaleid = $subcatobj->gradeitem->scaleid;
            return array($grdobj, true, null);
        }
        return array(null, false, null);
    }

    /**
     * Checks aggregation type and returns the calculated grade, only calculates highest, lowest, average and weighted mean of grades
     * 
     * @param mixed $subcatobj The subcategory activity object
     * @param array $grade_values An array of values to be aggregated
     * @param array $items The array of grade_items
     * @return int $grade The new calculated grade
     */
    public static function calculate_grade($subcatobj, $grade_values, $items){
        if(empty($grade_values)){
            return null;
        }
        $aggregationtype = $subcatobj->aggregation;
        $subcatgt = $subcatobj->gradeitem->gradetype;
        $subcatgmax = 100;
        $subcatgmin = 0;
        $agg_grade = null; //Aggregated grade
        $grades = self::normalize_grades($grade_values, $items);
        switch ($aggregationtype) {
            case GRADE_AGGREGATE_MIN:
                $agg_grade = min($grades);
                break;
            case GRADE_AGGREGATE_MAX:
                $agg_grade = max($grades);
                break;
            case GRADE_AGGREGATE_WEIGHTED_MEAN:// Weighted average of all existing final grades, weight specified in coef
                $weightsum = 0;
                $sum = 0;
                foreach ($grades as $itemid=>$grade_value) {
                    if (!isset($items[$itemid]) || $items[$itemid]->aggregationcoef <= 0) {
                        continue;
                    }
                    $weightsum += $items[$itemid]->aggregationcoef;
                    $sum       += $items[$itemid]->aggregationcoef * $grade_value;
                }
                $agg_grade = ($weightsum == 0) ? null : $sum / $weightsum;
                break;
            case GRADE_AGGREGATE_SUM:
                $agg_grade = array_sum($grade_values);
                break;
            case GRADE_AGGREGATE_MEDIAN:
                sort($grades);
                $count = count($grades);
                $middleval = floor(($count-1)/2);
                if ($count % 2) {
                    $agg_grade = $grades[$middleval];
                } else {
                    $low = $grades[$middleval];
                    $high = $grades[$middleval+1];
                    $agg_grade = (($low+$high)/2);
                }
                break;
            case GRADE_AGGREGATE_MODE:
                // the most common value
                // array_count_values only counts INT and STRING, so if grades are floats we must convert them to string
                $converted_grade_values = array();
                foreach ($grades as $k => $gv) {
                    if (!is_int($gv) && !is_string($gv)) {
                        $converted_grade_values[$k] = (string) $gv;
                    } else {
                        $converted_grade_values[$k] = $gv;
                    }
                }

                $freq = array_count_values($converted_grade_values);
                arsort($freq);                      // sort by frequency keeping keys
                $top = reset($freq);               // highest frequency count
                $modes = array_keys($freq, $top);  // search for all modes (have the same highest count)
                rsort($modes, SORT_NUMERIC);       // get highest mode
                $agg_grade = reset($modes);
                break;
            case GRADE_AGGREGATE_MEAN:
            default:
                $num = count($grade_values);
                $sum = array_sum($grades);
                $agg_grade = $sum / $num;
        }
        return ($subcatgt == GRADE_TYPE_VALUE) 
            ? grade_grade::standardise_score($agg_grade, $subcatgmin, $subcatgmax, 0, 100)
            : $agg_grade;
    }

    /**
     * Normalize grades from scale or points
     * 
     * @param array $grade_values An array of values to be aggregated
     * @param array $items The array of grade_items
     * @return array $normalizegrades An array of normalized grades
     */
    public static function normalize_grades($grade_values, $items){
        $normalizegrades = array();
        foreach ($grade_values as $itemid=>$grade_value) {
            if (!isset($items[$itemid])) {
                continue;
            }
            $gradetype = $items[$itemid]->gradetype;
            $grademax = $items[$itemid]->grademax;
            $grademin = $items[$itemid]->grademin;
            if($gradetype == GRADE_TYPE_VALUE){
                $normalizegrades[$itemid] = grade_grade::standardise_score($grade_value, $grademin, $grademax, 0, 100);
            }else{
                $normalizegrades[$itemid] = $grade_value;
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
     * @param string $notes
     */
    public static function adjust_course_weight($weights, $courseid, $studentid, $notes){
        //Iterate the weights, $key = gradeitem id, $value = weight
        foreach($weights as $key=>$value) {
            $weight = number_format(($value/100), 5);
            $gradeitem = grade_item::fetch(array('courseid' => $courseid, 'id' => $key));
            $id = ($gradeitem->itemtype == 'category') ? $gradeitem->iteminstance : $key;
            $itemname = get_string( ($gradeitem->itemtype == 'category') ? 'subcategorygrade' : 'provisionalgrd', 'local_gugcat');
            $prvgrdid = local_gugcat::get_grade_item_id($courseid, $id, $itemname);
            $grade_ = new grade_grade(array('userid' => $studentid, 'itemid' => $prvgrdid), true);
            $grade_->information = $weight;
            $grade_->feedback = ($gradeitem->itemtype == 'category') ? null : $notes;
            $grade_->timemodified = time();
            $grade_->update();  
        }
        local_gugcat::notify_success('successadjustweight');
    }

    /**
     * Release final assessment grades for all the students
     *
     * @param int $courseid
     */
    public static function release_final_grades($courseid){
        global $USER, $DB;
        //Retrieve modules and enrolled students per grade category
        $modules = local_gugcat::get_activities($courseid);
        $groupingids = array_column($modules, 'groupingid');
        $students = self::get_students_per_groups($groupingids, $courseid);
        foreach($modules as $mod) {
            // Get/create provisional grade id of the module
            $prvgrdid = local_gugcat::add_grade_item($courseid, get_string('provisionalgrd', 'local_gugcat'), $mod);
            
            $gradeitem = new grade_item($mod->gradeitem);
            //set offset value for max 22 points grade
            $gradescaleoffset = (local_gugcat::is_grademax22($gradeitem->gradetype, $gradeitem->grademax)) ? 1 : 0;

            foreach($students as $student) {
                //get the provisional grade of the student
                $prvgrd = $DB->get_record('grade_grades', array('itemid'=>$prvgrdid, 'userid' => $student->id), 'rawgrade, finalgrade');
                $grd = is_null($prvgrd->finalgrade) ? $prvgrd->rawgrade : $prvgrd->finalgrade;

                //check if grade is admin grade
                $grade = intval($grd); 
                $grade = ($grade == NON_SUBMISSION || $grade == MEDICAL_EXEMPTION) ? null : $grade - $gradescaleoffset;

                //update gradebook grade if provisional grade is not null
                if(!is_null($grd)){
                    $gradeitem->update_final_grade($student->id, $grade, null, null, FORMAT_MOODLE, $USER->id);
                }
                // Update gradebook information field to final
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
    public static function export_aggregation_tool($course, $categoryid = null){
        $table = get_string('aggregationtool', 'local_gugcat');
        $filename = "export_$table"."_".date('Y-m-d_His');    
        $columns = ['grade_category', 'student_number'];
        $is_blind_marking = local_gugcat::is_blind_marking();
        $is_blind_marking ? null : array_push($columns, ...array('surname', 'forename'));
        $modules = ($categoryid == null) ? local_gugcat::get_activities($course->id) : 
                                           self::get_parent_child_activities($course->id, $categoryid);
        $category = is_null($categoryid) ? null : grade_category::fetch(array('id' => $categoryid));
        $groupingids = array_column($modules, 'groupingid');
        $students = self::get_students_per_groups($groupingids, $course->id);
        //add the columns before the activities
        array_push($columns, ...['aggregated_grade', 'aggregated_grade_numeric', '%_complete', 'resit_required']);
        //Process the activity names
        $activities = array();
        foreach($modules as $cm) {
            $weight = preg_replace('!\s+!', '_', $cm->name).'_weighting';
            $alpha = preg_replace('!\s+!', '_', $cm->name).'_alphanumeric_grade';
            $is_points = is_null($cm->gradeitem->scaleid);
            $max_grade = (int)$cm->gradeitem->grademax;
            $numeric = preg_replace('!\s+!', '_', $cm->name). (($is_points) ? '_numeric_points_max(' . $max_grade . ')' : '_numeric_grade');
            array_push($activities, array($weight, $alpha, $numeric, $is_points));
            array_push($columns, ...array($weight, $alpha, $numeric));
        }
        //Process the data to be iterated
        $data = self::get_rows($course, $modules, $students);
        $array = array();
        foreach($data as $row) {
            $student = new stdClass();
            $student->grade_category = is_null($categoryid) ? get_string('uncategorised', 'grades') : $category->fullname;
            $student->student_number = $row->idnumber;
            if(!$is_blind_marking){
                $student->surname = $row->surname;
                $student->forename = $row->forename;
            }
            //check if grade is aggregated 
            $isaggregated = ($row->aggregatedgrade->display != get_string('missinggrade', 'local_gugcat')) ? true : false;
            $student->aggregated_grade = $isaggregated ? $row->aggregatedgrade->grade : null;
            $student->aggregated_grade_numeric = $isaggregated ?  (local_gugcat::is_admin_grade($row->aggregatedgrade->rawgrade) ? get_string('nogradeweight', 'local_gugcat') : $row->aggregatedgrade->rawgrade) : null;
            $student->{'%_complete'} = $row->completed;
            $student->resit_required = is_null($row->resit) ? 'N' : 'Y';
            foreach($activities as $key=>$act) {
                $is_converted = !is_null($row->grades[$key]->nonconvertedgrade);
                $is_points = $act[3];
                $student->{$act[0]} = $row->grades[$key]->originalweight.'%'; //weight
                $student->{$act[1]} = ($is_converted || !$is_points) ? $row->grades[$key]->grade : get_string('nogradeweight', 'local_gugcat'); //alphanumeric
                $is_admin_grade = local_gugcat::is_admin_grade(array_search($row->grades[$key]->grade,local_gugcat::$GRADES));
                $student->{$act[2]} = $is_admin_grade ? get_string('nogradeweight', 'local_gugcat')
                                                      : ($is_converted ? $row->grades[$key]->nonconvertedgrade : $row->grades[$key]->rawgrade); //numeric
            }
            array_push($array, $student);
        }
        //convert array to ArrayObject to get the iterator
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

        if($taginstances = $DB->get_records('tag_instance', array('itemid'=>$module->id), null, 'tagid')){
            foreach($taginstances as $taginstance){
                $tag = $DB->get_field('tag', 'name', array('id'=>$taginstance->tagid));

                if(!strcasecmp('resit', $tag)){
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
    public static function get_course_grade_history($course, $modules, $student){
        global $DB;

        $rows = array();
        foreach ($modules as $mod) {
            $i = 0;
            $mod->scaleid = $mod->gradeitem->scaleid;
            $scaleid = $mod->scaleid;
            if (is_null($scaleid) && local_gugcat::is_grademax22($mod->gradeitem->gradetype, $mod->gradeitem->grademax)){
                $scaleid = null;
            }
            local_gugcat::set_grade_scale($scaleid);
            //get provisional grades
            $prvgrdstr = get_string('provisionalgrd', 'local_gugcat');
            $prvgrdid = local_gugcat::get_grade_item_id($course->id, $mod->gradeitemid, $prvgrdstr);
            $sort = 'id DESC';
            $fields = 'id, itemid, rawgrade, finalgrade, feedback, timemodified, usermodified, information';
            $select = 'feedback IS NOT NULL AND rawgrade IS NOT NULL AND itemid='.$prvgrdid.' AND '.' userid="'.$student->id.'"'; 
            $gradehistory_arr = $DB->get_records_select('grade_grades_history', $select, null, $sort, $fields);
            if($gradehistory_arr > 0){
                foreach($gradehistory_arr as $gradehistory){
                    isset($rows[$i]) ? null : $rows[$i] = new stdClass();
                    isset($rows[$i]->grades) ? null : $rows[$i]->grades = array();
                    $rows[$i]->timemodified = $gradehistory->timemodified;
                    $rows[$i]->date = date("j/n", strtotime(userdate($gradehistory->timemodified))).'<br>'.date("h:i", strtotime(userdate($gradehistory->timemodified)));
                    $fields = 'firstname, lastname';
                    $modby = $DB->get_record('user', array('id' => $gradehistory->usermodified), $fields);
                    $rows[$i]->modby = (isset($modby->lastname) && isset($modby->firstname)) ? $modby->lastname . ', '.$modby->firstname : null;
                    $rows[$i]->notes = $gradehistory->feedback;
                    array_push($rows[$i]->grades, $gradehistory);
                    $i++;
                }
            }
        }
        $i = count($rows);
        foreach($modules as $mod){
            //add first course grade history
            $grditemresit = self::is_resit($mod);
            if(!$grditemresit){
                $prvgrdstr = get_string('provisionalgrd', 'local_gugcat');
                $prvgrdid = local_gugcat::get_grade_item_id($course->id, $mod->gradeitemid, $prvgrdstr);
                $sort = 'id ASC';
                $select = 'information IS NOT NULL AND rawgrade IS NOT NULL AND itemid='.$prvgrdid.' AND '.' userid="'.$student->id.'"'; 
                //if grdhistory did not get the first provisional grade, get it to gradebook
                if(!$gradehistory = $DB->get_records_select('grade_grades_history', $select, null, $sort, '*', 0, 1))
                    $grdhistoryobj = $DB->get_record('grade_grades', array('itemid'=>$prvgrdid, 'userid'=>$student->id));
                else{
                    $grdhistoryobj = $gradehistory[key($gradehistory)];
                } 
                if($grdhistoryobj){
                    isset($rows[$i]) ? null : $rows[$i] = new stdClass();
                    isset($rows[$i]->grades) ? null : $rows[$i]->grades = array();
                    $rows[$i]->timemodified = $grdhistoryobj->timemodified;
                    $rows[$i]->date = date("j/n", strtotime(userdate($grdhistoryobj->timemodified))).'<br>'.date("h:i", strtotime(userdate($grdhistoryobj->timemodified)));
                    $rows[$i]->modby = get_string('nogradeweight','local_gugcat');
                    $rows[$i]->notes = get_string('nogradeweight','local_gugcat');
                    array_push($rows[$i]->grades, $grdhistoryobj);
                }
            }
        }

        foreach($rows as $row) {
            $sumgrade = 0;
            if($row->grades > 0){
                foreach($row->grades as $grdhistory) {
                    $grd = $grdhistory->rawgrade;
                    $weight = $grdhistory->information;
                    $sumgrade += (float)$grd * $weight;
                }
            }
            $row->grade = local_gugcat::convert_grade(round((float)$sumgrade));
        }
        // Add overridden grades in rows
        $aggradeid = local_gugcat::get_grade_item_id($course->id, null, get_string('aggregatedgrade', 'local_gugcat'));
        if($aggradeid){
            $fields = 'id, itemid, rawgrade, finalgrade, feedback, timemodified, usermodified';
            $select = 'feedback IS NOT NULL AND rawgrade IS NOT NULL AND itemid='.$aggradeid.' AND '.' userid="'.$student->id.'" AND overridden <> "0"'; 
            $gradehistory_overridden = $DB->get_records_select('grade_grades_history', $select, null, $fields);
            if($gradehistory_overridden > 0){
                foreach($gradehistory_overridden as $overriddengrade){
                    $ovgrade = new stdClass();
                    $grd = (is_null($overriddengrade->finalgrade) ? (float)$overriddengrade->rawgrade : (float)$overriddengrade->finalgrade);
                    $ovgrade->grade = local_gugcat::convert_grade($grd);
                    $ovgrade->notes = $overriddengrade->feedback;
                    $ovgrade->overridden = true;
                    $fields = 'firstname, lastname';
                    $modby = $DB->get_record('user', array('id' => $overriddengrade->usermodified), $fields);
                    $ovgrade->modby = (isset($modby->lastname) && isset($modby->firstname)) ? $modby->lastname . ', '.$modby->firstname : null;
                    $ovgrade->timemodified = $overriddengrade->timemodified;
                    $ovgrade->date = date("j/n", strtotime(userdate($overriddengrade->timemodified))).'<br>'.date("h:i", strtotime(userdate($overriddengrade->timemodified)));
                    array_push($rows, $ovgrade);
                }
            }
        }
        //sort array by timemodified
        usort($rows,function($first,$second){
            return $first->timemodified < $second->timemodified;
        });
        return $rows;
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
        if(array_sum($groupingids) != 0){
            $groups = array();
            foreach ($groupingids as $groupingid) {
                if($groupingid != 0){
                    $groups += groups_get_all_groups($courseid, 0, $groupingid);
                }
            }
            if(!empty($groups)){
                foreach ($groups as $group) {
                    $students += get_enrolled_users($coursecontext, 'local/gugcat:gradable', $group->id, $userfields);
                }
            }
        }else{
            $students = get_enrolled_users($coursecontext, 'local/gugcat:gradable', 0, $userfields);
        }
        return $students;
    }

    /**
     * Return list of both child and parent activities
     * 
     * @param int $courseid
     * @param int $categoryid
     * @return array
     */
    public static function get_parent_child_activities($courseid, $categoryid){
        // Retrieve sub categories
        $gcs = grade_category::fetch_all(array('courseid' => $courseid, 'parent' => $categoryid));
        $cids = array($categoryid);

        // Combine retrieved sub categories and the main course category (ids)
        !empty($gcs) ? array_push($cids, ...array_column($gcs, 'id')) : null;

        // Retrieve activities based from categoryids
        $raw_activities = local_gugcat::get_activities($courseid, $cids);

        $mainactivities = array();
        $childactivities = array();
        // Separate the main activities and child activites into two arrays
        array_map(function($value) use (&$mainactivities, &$childactivities) {
            if (local_gugcat::is_child_activity($value)) {
                $childactivities[] = $value;
            } else {
                $mainactivities[] = $value;
            }
        }, $raw_activities);

        // Retrieve grade items of the grade categories
        $gradecatgi = array();
        if(!empty($gcs)){
            foreach ($gcs as $gc) {
                $gradecatgi[] = local_gugcat::get_category_gradeitem($courseid, $gc);
            }
        }
        // The final array to be pass to get_rows
        $activities = array();
        // Combine the main activities and grade categories grade items
        $mainactivities = array_merge($mainactivities, $gradecatgi);
        foreach ($mainactivities as $index=>$act) {
            // Check if activity = category, insert the child activities next to it.
            if($act->modname == 'category'){
                // Filter $childactivities to the children of the iterated category
                $children = array_filter($childactivities,
                    function($value) use ($act) {
                        return $value->gradeitem->categoryid == $act->id;
                    }
                );
                $act->children = array_column($children, 'gradeitemid');
                if(!empty($children)){
                    // Insert $children first before its category grade item
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
    public static function update_component_notes_for_all_students($subcatid, $categoryid, $notes){
        global $DB;

        $courseid = optional_param('id', null, PARAM_INT);
        $students = get_enrolled_users(context_course::instance($courseid), 'local/gugcat:gradable', 0, 'u.id');
        $activities = local_gugcat::get_child_activities_id($courseid, $categoryid);
        $prvgrades = local_gugcat::get_prvgrd_item_ids($courseid, $activities);
        $userids = '';
        foreach($students as $student){
            $userids .= "userid=$student->id OR ";
        }
        //remove last OR
        $userids = chop($userids, ' OR ');
        //get all subcat grds
        $select = "itemid=$subcatid AND ($userids) AND rawgrade IS NOT NULL";
        $fields = 'id, itemid, userid, rawgrade, finalgrade, overridden';
        $subcatgrds = $DB->get_records_select('grade_grades', $select, null, null, $fields);
        foreach($subcatgrds as $subcatgrd){
            if($subcatgrd->overridden == 0){
                local_gugcat::update_components_notes($subcatgrd->userid, $subcatid, $notes);
                foreach($prvgrades as $prvgrd){
                    local_gugcat::update_components_notes($subcatgrd->userid, $prvgrd->id, $notes);
                }
            }
        }
    }
}