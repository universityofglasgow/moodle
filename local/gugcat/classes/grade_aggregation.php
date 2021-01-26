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
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_gugcat;

use ArrayObject;
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
 * Grade capture class.
 */

class grade_aggregation{

    public static $AGGRADE = array(
        CREDIT_WITHHELD => CREDIT_WITHHELD_AC,
        CREDIT_REFUSED => CREDIT_REFUSED_AC,
        CA => CA_AC,
        UNDER_INVESTIGATION => UNDER_INVESTIGATION_AC,
        AU => AU_AC,
        FC => FC_AC 
    );

     /**
     * Returns rows for grade aggreation table
     *
     * @param mixed $course
     * @param mixed $module
     * @param mixed $students
     */
    public static function get_rows($course, $modules, $students){
        global $DB, $aggradeid;
        $categoryid = optional_param('categoryid', '0', PARAM_INT);
        //get grade item id for aggregated grade
        $aggradeid = local_gugcat::add_grade_item($course->id, get_string('aggregatedgrade', 'local_gugcat'), null);
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

        $i = 1;
        foreach ($students as $student) {
            $gradecaptureitem = new gcat_item();
            $gradecaptureitem->cnum = $i;
            $gradecaptureitem->studentno = $student->id;
            $gradecaptureitem->surname = $student->lastname;
            $gradecaptureitem->forename = $student->firstname;
            $gradecaptureitem->grades = array();
            $gbaggregatedgrade = $DB->get_record('grade_grades', array('itemid'=>$aggradeid, 'userid'=>$student->id));
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
                    $scaleid = $item->scaleid;
                    if (is_null($scaleid) && local_gugcat::is_grademax22($item->gradeitem->gradetype, $item->gradeitem->grademax)){
                        $scaleid = null;
                    }
                    local_gugcat::set_grade_scale($scaleid);
                    $grade = is_null($grd) ? ( $grditemresit ? get_string('nograderesit', 'local_gugcat') : get_string('nograderecorded', 'local_gugcat')) : local_gugcat::convert_grade($grd);
                    $weight = 0;
                    $grdvalue = get_string('nograderecorded', 'local_gugcat');
                    if($grditemresit && is_null($pg) && is_null($grd) && !$gradecaptureitem->resitexist)
                        $gradecaptureitem->resitexist = false;
                    else if(!is_null($pg) && !is_null($grd) && $grade !== MEDICAL_EXEMPTION_AC){
                        $weight = (float)$pg->information; //get weight from information column of provisional grades
                        $grdvalue = ($grade === NON_SUBMISSION_AC) ? 0 : (float)$grd - (float)1; //normalize to actual grade value for computation
                        $floatweight += ($grade === NON_SUBMISSION_AC) ? 0 : $weight;
                        $sumaggregated += ($grade === NON_SUBMISSION_AC) ?( 0 * (float)$grdvalue) : ((float)$grdvalue * $weight);
                        if($grditemresit && $weight == 0)
                            $gradecaptureitem->resitexist = true;
                    }
                    $gradecaptureitem->nonsubmission = ($grade === NON_SUBMISSION_AC) ? true : false;
                    $gradecaptureitem->medicalexemption = ($grade === MEDICAL_EXEMPTION_AC) ? true : false;
                    $grdobj->activityid = $item->gradeitemid;
                    $grdobj->activityinstance = $item->instance;
                    $grdobj->activity = $item->name;
                    $grdobj->grade = $grade;
                    $grdobj->rawgrade = $grdvalue;
                    $grdobj->weight =  round((float)$weight * 100 );
                    array_push($gradecaptureitem->grades, $grdobj);
                }
                $gradecaptureitem->resit = (preg_match('/\b'.$categoryid.'/i', $gbaggregatedgrade->information) ? $gbaggregatedgrade->information : null);
                $gradecaptureitem->completed = round((float)$floatweight * 100 ) . '%';
                $rawaggrade = ($gbaggregatedgrade->overridden == 0) ? $sumaggregated : $gbaggregatedgrade->finalgrade;
                ($gbaggregatedgrade->overridden == 0) ? local_gugcat::update_grade($student->id, $aggradeid, $sumaggregated) : null;
                $aggrade = ($gbaggregatedgrade->overridden == 0) ? round($rawaggrade) + 1 : $rawaggrade; //convert back to moodle scale
                if(!(max(array_keys(local_gugcat::$GRADES)) >= 22)){
                    local_gugcat::set_grade_scale(null);
                }
                $aggrdobj->grade = local_gugcat::convert_grade($aggrade);
                $aggrdobj->rawgrade = $rawaggrade;
                $aggrdobj->display = in_array(get_string('nograderecorded', 'local_gugcat'), array_column($gradecaptureitem->grades, 'grade'))
                    ? get_string('missinggrade', 'local_gugcat') 
                    : (!strstr($rawaggrade, '-') ? local_gugcat::convert_grade($aggrade) .' ('.number_format(($gbaggregatedgrade->overridden == 0) ?
                     $rawaggrade : $rawaggrade-1, 2).')' : local_gugcat::convert_grade($aggrade));
            }
            $gradecaptureitem->aggregatedgrade = $aggrdobj;
            array_push($rows, $gradecaptureitem);
            $i++;
        }
        return $rows;
    }

    public static function require_resit($studentno){
        global $aggradeid, $USER;

        $categoryid = optional_param('categoryid', '0', PARAM_INT);
        $grade_ = new grade_grade(array('userid' => $studentno, 'itemid' => $aggradeid), true);
        $grade_->usermodified = $USER->id;
        $grade_->itemid = $aggradeid;
        $grade_->userid = $studentno;
        $grade_->timemodified = time();
        if(preg_match('/\b'.$categoryid.'/i', $grade_->information))
            $grade_->information = preg_replace('/\b'.$categoryid.' /i', '', $grade_->information);
        else
            $grade_->information .= $categoryid.' ';

        return $grade_->update();    
    }

    public static function adjust_course_weight($weights, $courseid, $studentid, $notes){
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

    public static function release_final_grades($courseid, $cms, $students){
        global $USER, $DB;
        foreach($cms as $cmid=>$cm) {
            $data = new stdClass();
            $data->courseid = $courseid;
            $data->itemtype = 'mod';
            $data->iteminstance = explode('_', $cm)[0];
            $data->itemname = explode('_', $cm)[1];
            //get gradebook grade item
            $gradeitem = new grade_item($data, true);
           //set offset value for max 22 points grade
            $gradescaleoffset = 0;
            if (local_gugcat::is_grademax22($gradeitem->gradetype, $gradeitem->grademax)){
                $gradescaleoffset = 1;
            }
            foreach($students as $id=>$student) {
                //update grade & information from gradebook
                $grade = $student[$cmid]; 
                switch ($grade) {
                    case NON_SUBMISSION:
                        $grade = 0;
                        break;
                    case MEDICAL_EXEMPTION:
                        $grade = null;
                        break;
                    default:
                        $grade = $grade - $gradescaleoffset;
                        break;
                }
                if($gradeitem->update_final_grade($id, $grade, null, null, FORMAT_MOODLE, $USER->id)){
                    $DB->set_field_select('grade_grades', 'information', 'final', "itemid = $gradeitem->id AND userid = $id");
                }
            }         
        }
        local_gugcat::notify_success('successfinalrelease');
    }

    public static function export_aggregation_tool($course, $modules, $students){
        $table = get_string('aggregationtool', 'local_gugcat');
        $filename = "export_$table"."_".date('Y-m-d_His');    
        $columns = ['candidate_number', 'student_number'];
        $is_blind_marking = local_gugcat::is_blind_marking();
        $is_blind_marking ? null : array_push($columns, ...array('surname', 'forename'));
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
            $student->student_number = $row->studentno;
            if(!$is_blind_marking){
                $student->surname = $row->surname;
                $student->forename = $row->forename;
            }
            foreach($activities as $key=>$act) {
                $student->{$act[0]} = $row->grades[$key]->weight.'%';//weight
                $student->{$act[1]} = $row->grades[$key]->grade; //alphanumeric
                $student->{$act[2]} = $row->grades[$key]->rawgrade;//numeric
            }
            $student->{'%_complete'} = $row->completed;
            //check if grade is aggregated 
            $isaggregated = ($row->aggregatedgrade->display != get_string('missinggrade', 'local_gugcat')) ? true : false;
            $student->aggregated_grade = $isaggregated ? $row->aggregatedgrade->grade : null;
            $student->aggregated_grade_numeric = $isaggregated ?  $row->aggregatedgrade->rawgrade : null;
            $student->resit_required = is_null($row->resit) ? 'N' : 'Y';
            array_push($array, $student);
        }
        //convert array to ArrayObject to get the iterator
        $exportdata = new ArrayObject($array);
        local_gugcat::export_gcat($filename, $columns, $exportdata->getIterator());
    }

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
}