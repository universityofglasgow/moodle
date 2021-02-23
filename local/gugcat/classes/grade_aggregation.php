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
use local_gugcat;
use stdClass;
use grade_grade;
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
     */
    public static function get_rows($course, $modules, $students){
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

            //get provisional grades
            $prvgrdid = local_gugcat::set_prv_grade_id($course->id, $mod);
            $sort = 'id';
            $fields = 'userid, itemid, id, rawgrade, finalgrade, information, timemodified';
            $grades->provisional = $DB->get_records('grade_grades', array('itemid' => $prvgrdid), $sort, $fields);
            //get grades from gradebook
            $gbgrades = grade_get_grades($course->id, 'mod', $mod->modname, $mod->instance, array_keys($students));
            $gbgradeitem = array_values(array_filter($gbgrades->items, function($item) use($mod){
                return $item->itemnumber == $mod->gradeitem->itemnumber;//filter grades with specific itemnumber
            }));
            $grades->gradebook = isset($gbgradeitem[0]) ? $gbgradeitem[0]->grades : null;
            $mod->grades = $grades;
            array_push($gradebook, $mod);
        }
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
                foreach ($gradebook as $item) {
                    $grditemresit = self::is_resit($item);
                    $grdobj = new stdClass();
                    $grades = $item->grades;
                    $pg = isset($grades->provisional[$student->id]) ? $grades->provisional[$student->id] : null;
                    $gb = isset($grades->gradebook[$student->id]) ? $grades->gradebook[$student->id] : null;
                    $grd = (isset($pg) && !is_null($pg->finalgrade)) ? $pg->finalgrade 
                    : (isset($pg) && !is_null($pg->rawgrade) ? $pg->rawgrade 
                    : ((isset($gb) && !is_null($gb->grade)) ? $gb->grade : null));                
                    $scaleid = is_null($item->scaleid) && local_gugcat::is_grademax22($item->gradeitem->gradetype, $item->gradeitem->grademax) ? null : $item->scaleid;
                    $invalid22scale = is_null($scaleid) && local_gugcat::is_grademax22($item->gradeitem->gradetype, $item->gradeitem->grademax)  && !isset($pg);
                    local_gugcat::set_grade_scale($scaleid);
                    $grade = is_null($grd) ? ( $grditemresit ? get_string('nogradeweight', 'local_gugcat') : get_string('nograderecorded', 'local_gugcat')) 
                    : ($invalid22scale ? local_gugcat::convert_grade(intval($grd) + 1) : local_gugcat::convert_grade($grd));
                    $grdvalue = get_string('nograderecorded', 'local_gugcat');
                    $weight = !is_null($pg) ? (float)$pg->information : 0; //get weight from information column of provisional grades
                    if(!is_null($grd) && $grade !== MEDICAL_EXEMPTION_AC){
                        $grdvalue = $invalid22scale ? $grd : (($grade === NON_SUBMISSION_AC) ? 0 : (float)$grd - (float)1); //normalize to actual grade value for computation
                        $floatweight += ($grade === NON_SUBMISSION_AC) ? 0 : $weight;
                        $sumaggregated += ($grade === NON_SUBMISSION_AC) ?( 0 * (float)$grdvalue) : ((float)$grdvalue * $weight);
                    }
                    $grdobj->activityid = $item->gradeitemid;
                    $grdobj->activityinstance = $item->instance;
                    $grdobj->activity = $item->name;
                    $grdobj->grade = $grade;
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
                    $aggrdobj->display = in_array(get_string('nograderecorded', 'local_gugcat'), array_column($gradecaptureitem->grades, 'grade'))
                    ? get_string('missinggrade', 'local_gugcat') 
                    : ($gbaggregatedgrade->overridden == 0 ? ($totalweight < 75 ? $numberformat 
                    : local_gugcat::convert_grade($aggrade) .' ('.$numberformat.')') 
                    : local_gugcat::convert_grade($aggrade));
                    }
            }
            $gradecaptureitem->aggregatedgrade = $aggrdobj;
            array_push($rows, $gradecaptureitem);
            $i++;
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
            $prvgrdid = local_gugcat::get_grade_item_id($courseid, $key, get_string('provisionalgrd', 'local_gugcat'));
            $grade_ = new grade_grade(array('userid' => $studentid, 'itemid' => $prvgrdid), true);
            $grade_->information = $weight;
            $grade_->feedback = $notes;
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
    public static function export_aggregation_tool($course){
        $table = get_string('aggregationtool', 'local_gugcat');
        $filename = "export_$table"."_".date('Y-m-d_His');    
        $columns = ['candidate_number', 'student_number'];
        $is_blind_marking = local_gugcat::is_blind_marking();
        $is_blind_marking ? null : array_push($columns, ...array('surname', 'forename'));
        $modules = local_gugcat::get_activities($course->id);
        $groupingids = array_column($modules, 'groupingid');
        $students = self::get_students_per_groups($groupingids, $course->id);
        //Process the activity names
        $activities = array();
        foreach($modules as $cm) {
            $weight = preg_replace('!\s+!', '_', $cm->name).'_weighting';
            $alpha = preg_replace('!\s+!', '_', $cm->name).'_alphanumeric_grade';
            $numeric = preg_replace('!\s+!', '_', $cm->name).'_numeric_grade';
            array_push($activities, array($weight, $alpha, $numeric));
            array_push($columns, ...array($weight, $alpha, $numeric));
        }
        //add the remaining columns after the activities
        array_push($columns, ...['%_complete', 'aggregated_grade', 'aggregated_grade_numeric', 'resit_required']);
        //Process the data to be iterated
        $data = self::get_rows($course, $modules, $students);
        $array = array();
        foreach($data as $row) {
            $student = new stdClass();
            $student->candidate_number = $row->cnum;
            $student->student_number = $row->idnumber;
            if(!$is_blind_marking){
                $student->surname = $row->surname;
                $student->forename = $row->forename;
            }
            foreach($activities as $key=>$act) {
                $student->{$act[0]} = $row->grades[$key]->weight.'%';//weight
                $student->{$act[1]} = $row->grades[$key]->grade; //alphanumeric
                $student->{$act[2]} = local_gugcat::is_admin_grade(array_search($row->grades[$key]->grade, local_gugcat::$GRADES)) ? get_string('nogradeweight', 'local_gugcat') : $row->grades[$key]->rawgrade;//numeric
            }
            $student->{'%_complete'} = $row->completed;
            //check if grade is aggregated 
            $isaggregated = ($row->aggregatedgrade->display != get_string('missinggrade', 'local_gugcat')) ? true : false;
            $student->aggregated_grade = $isaggregated ? $row->aggregatedgrade->grade : null;
            $student->aggregated_grade_numeric = $isaggregated ?  (local_gugcat::is_admin_grade($row->aggregatedgrade->rawgrade) ? get_string('nogradeweight', 'local_gugcat') : $row->aggregatedgrade->rawgrade) : null;
            $student->resit_required = is_null($row->resit) ? 'N' : 'Y';
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
     * @return array
     */
    public static function get_students_per_groups($groupingids, $courseid) {
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
                    $students += get_enrolled_users($coursecontext, 'local/gugcat:gradable', $group->id);
                }
            }
        }else{
            $students = get_enrolled_users($coursecontext, 'local/gugcat:gradable');
        }
        return $students;
    }
}