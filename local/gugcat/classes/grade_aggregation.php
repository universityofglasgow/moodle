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

use local_gugcat;
use stdClass;
use grade_grade;

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
            $grades->provisional = $DB->get_records(GRADE_GRADES, array('itemid' => $prvgrdid), $sort, $fields);
            //get grades from gradebook
            $gbgrades = grade_get_grades($course->id, 'mod', $mod->modname, $mod->instance, array_keys($students));
            $grades->gradebook = isset($gbgrades->items[0]) ? $gbgrades->items[0]->grades : null;
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
            $gbaggregatedgrade = $DB->get_record(GRADE_GRADES, array('itemid'=>$aggradeid, 'userid'=>$student->id));
            $floatweight = 0;
            $sumaggregated = 0;
            foreach ($gradebook as $item) {
                $grdobj = new stdClass();
                $grades = $item->grades;
                $pg = isset($grades->provisional[$student->id]) ? $grades->provisional[$student->id] : null;
                $gb = isset($grades->gradebook[$student->id]) ? $grades->gradebook[$student->id] : null;
                $grd = (isset($pg) && !is_null($pg->finalgrade)) ? $pg->finalgrade 
                : (isset($pg) && !is_null($pg->rawgrade) ? $pg->rawgrade 
                : ((isset($gb) && !is_null($gb->grade)) ? $gb->grade : null));                
                $scaleid = $item->scaleid;
                if (is_null($scaleid) && local_gugcat::is_grademax22($item->gradeitem->gradetype, $item->gradeitem->grademax)){
                    $scaleid = local_gugcat::get_gcat_scaleid();
                }
                local_gugcat::set_grade_scale($scaleid);
                $grade = is_null($grd) ? get_string('nograderecorded', 'local_gugcat') : local_gugcat::convert_grade($grd);
                $weight = 0;
                if(!is_null($pg) && !is_null($grd) && $grade !== MEDICAL_EXEMPTION_AC){
                    $weight = (float)$pg->information; //get weight from information column of provisional grades
                    $grdvalue = (float)$grd - (float)1; //normalize to actual grade value for computation
                    $floatweight += ($grade === NON_SUBMISSION_AC) ? 0 : $weight;
                    $sumaggregated += ($grade === NON_SUBMISSION_AC) ?( 0 * (float)$grdvalue) : ((float)$grdvalue * $weight);
                }
                $gradecaptureitem->nonsubmission = ($grade === NON_SUBMISSION_AC) ? true : false;
                $gradecaptureitem->medicalexemption = ($grade === MEDICAL_EXEMPTION_AC) ? true : false;
                $grdobj->activityid = $item->id;
                $grdobj->activity = $item->name;
                $grdobj->grade = $grade;
                $grdobj->rawgrade = $grd;
                $grdobj->weight =  round((float)$weight * 100 );
                array_push($gradecaptureitem->grades, $grdobj);
            }
            $gradecaptureitem->resit = $gbaggregatedgrade->information;
            $gradecaptureitem->completed = round((float)$floatweight * 100 ) . '%';
            $rawaggrade = ($gbaggregatedgrade->overridden == 0) ? $sumaggregated : $gbaggregatedgrade->finalgrade;
            ($gbaggregatedgrade->overridden == 0) ? local_gugcat::update_grade($student->id, $aggradeid, $sumaggregated) : null;
            $aggrade = round($rawaggrade) + 1; //convert back to moodle scale
            if(!(max(array_keys(local_gugcat::$GRADES)) >= 22)){
                $gcatscaleid = local_gugcat::get_gcat_scaleid();
                local_gugcat::set_grade_scale($gcatscaleid);
            }
            $aggrdobj = new stdClass();
            $aggrdobj->grade = local_gugcat::convert_grade($aggrade);
            $aggrdobj->rawgrade = $rawaggrade;
            $aggrdobj->display = array_search(get_string('nograderecorded', 'local_gugcat'), array_column($gradecaptureitem->grades, 'grade'))
                ? get_string('missinggrade', 'local_gugcat') 
                : (!strstr($rawaggrade, '-') ? local_gugcat::convert_grade($aggrade) .' ('.number_format($rawaggrade, 2).')' : local_gugcat::convert_grade($aggrade));
            $gradecaptureitem->aggregatedgrade = $aggrdobj;
            array_push($rows, $gradecaptureitem);
            $i++;
        }
        return $rows;
    }

    public static function require_resit($studentno){
        global $aggradeid, $USER;

        $grade_ = new grade_grade(array('userid' => $studentno, 'itemid' => $aggradeid), true);
        $grade_->usermodified = $USER->id;
        $grade_->itemid = $aggradeid;
        $grade_->userid = $studentno;
        $grade_->timemodified = time();
        $grade_->information = is_null($grade_->information) ? '1' : null;

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
}