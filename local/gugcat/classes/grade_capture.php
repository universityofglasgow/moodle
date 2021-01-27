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

use assign;
use context_module;
use grade_item;
use grade_grade;
use local_gugcat;
use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once('gcat_item.php');

 /**
 * Grade capture class.
 */

class grade_capture{

     /**
     * Returns rows for grade capture table
     *
     * @param mixed $course
     * @param mixed $module
     * @param mixed $students
     */
    public static function get_rows($course, $module, $students){
        $captureitems = array();
        global $gradeitems, $firstgradeid;
        $gradeitems = array();
        if(isset($module)){
            $gbgrades = grade_get_grades($course->id, 'mod', $module->modname, $module->instance, array_keys($students));
            $gbgradeitem = array_values(array_filter($gbgrades->items, function($item) use($module){
                return $item->itemnumber == $module->gradeitem->itemnumber;//filter grades with specific itemnumber
            }));
            $releasedgrades = isset($gbgradeitem[0]) ? $gbgradeitem[0]->grades : null;
            if($firstgradeid = local_gugcat::get_grade_item_id($course->id, $module->gradeitemid, get_string('moodlegrade', 'local_gugcat'))){
                $gradeitems = local_gugcat::get_grade_grade_items($course, $module);
                //---------ids needed for grade discrepancy
                $agreedgradeid = local_gugcat::get_grade_item_id($course->id, $module->gradeitemid, get_string('gi_agreedgrade', 'local_gugcat'));
                $secondgradeid = local_gugcat::get_grade_item_id($course->id, $module->gradeitemid, get_string('gi_secondgrade', 'local_gugcat'));
                $thirdgradeid = local_gugcat::get_grade_item_id($course->id, $module->gradeitemid, get_string('gi_thirdgrade', 'local_gugcat'));    
                
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
            if($firstgradeid){
                //get released grade
                if(count($releasedgrades) > 0){
                    $gbg = isset($releasedgrades[$student->id]) ? $releasedgrades[$student->id] : null;
                    $gradescaleoffset = local_gugcat::is_grademax22($module->gradeitem->gradetype, $module->gradeitem->grademax) ? 1 : 0;
                    $grade = self::check_gb_grade($gbg, $gradescaleoffset);
                    $gradecaptureitem->releasedgrade = is_null($grade) ? null : local_gugcat::convert_grade($grade);
                }
                //get first grade and provisional grade
                $gifg = $gradeitems[$firstgradeid]->grades;
                $gipg = $gradeitems[intval(local_gugcat::$PRVGRADEID)]->grades;
                $fg = (isset($gifg[$student->id])) ? $gifg[$student->id]->grade : null;
                $pg = (isset($gipg[$student->id])) ? $gipg[$student->id]->grade : null;
                $gradecaptureitem->firstgrade = is_null($fg) ? get_string('nograde', 'local_gugcat') : local_gugcat::convert_grade($fg);
                $gradecaptureitem->provisionalgrade = is_null($pg) ? get_string('nograde', 'local_gugcat') : local_gugcat::convert_grade($pg);
                $agreedgrade = (!$agreedgradeid) ? null : (isset($gradeitems[$agreedgradeid]->grades[$student->id]) ? $gradeitems[$agreedgradeid]->grades[$student->id]->grade : null);
                $sndgrade = (!$secondgradeid) ? null : (isset($gradeitems[$secondgradeid]->grades[$student->id]) ? $gradeitems[$secondgradeid]->grades[$student->id]->grade : null);
                $trdgrade = (!$thirdgradeid) ? null : (isset($gradeitems[$thirdgradeid]->grades[$student->id]) ? $gradeitems[$thirdgradeid]->grades[$student->id]->grade : null);

                foreach ($gradeitems as $item) {
                    if($item->grades[$student->id]->hidden == 1)
                        $gradecaptureitem->hidden = true;
                    if($item->id != local_gugcat::$PRVGRADEID && $item->id != $firstgradeid){
                        $rawgrade = (isset($item->grades[$student->id])) ? $item->grades[$student->id]->grade : null; 
                        $grdobj = new stdClass();
                        $grade = is_null($rawgrade) ? 'N/A' : local_gugcat::convert_grade($rawgrade);
                        $grdobj->grade = $grade;
                        $grdobj->discrepancy = false;

                        //check grade discrepancy, compare to first grade and agreed grade
                        if(is_null($agreedgrade) && $fg){
                            if($item->id === $secondgradeid || $item->id === $thirdgradeid){
                                $grdobj->discrepancy = is_null($rawgrade) ? false 
                                : (($rawgrade != $fg) ? true //compare to first grade
                                : ((!is_null($sndgrade) && $rawgrade != $sndgrade) ? true //compare to 2nd grade
                                : ((!is_null($trdgrade) && $rawgrade != $trdgrade) ? true : false))); //compare to 3rd grade
                            }
                        }
                        if($grdobj->discrepancy){
                            $gradecaptureitem->discrepancy = true;
                        }
                        array_push($gradecaptureitem->grades, $grdobj);
                    }                        
                }    
            }
            array_push($captureitems, $gradecaptureitem);
            $i++;
        }
        return $captureitems;
    }

    /**
     * Returns columns for grade capture table
     *
     */
    public static function get_columns(){
        //global $gradeitems from get rows function
        global $gradeitems, $firstgradeid;
        $columns = array();
        if(!$firstgradeid){
            $firstgrade = get_string('moodlegrade', 'local_gugcat').'<br>[Date]';
            $columns = array($firstgrade);
        }
        $firstcolumn = null;
        foreach ($gradeitems as $item) {
            if($item->itemname == get_string('moodlegrade', 'local_gugcat')){
                $firstcolumn = $item->itemname.'<br>'.date("[j/n/Y]", strtotime(userdate($item->timemodified)));
            }else{
                $columns[$item->id] = $item->itemname;
            }
        }
        //remove provisional column
        unset($columns[local_gugcat::$PRVGRADEID]);
        !is_null($firstcolumn) ? array_unshift($columns, $firstcolumn) : null; //always put moodle grade first
        return $columns;
    }

    /**
     * Function in releasing provisional grades
     *
     */
    public static function release_prv_grade($courseid, $cm, $grades){
        global $USER, $CFG, $DB;
        $data = new stdClass();
        $data->courseid = $courseid;
        $data->itemmodule = $cm->modname;
        $data->itemname = $cm->name;
        $data->iteminstance = $cm->instance;
        $gradeitemid = $cm->gradeitem->id;

        //set offset value for max 22 points grade
        $gradescaleoffset = 0;
        if (local_gugcat::is_grademax22($cm->gradeitem->gradetype, $cm->gradeitem->grademax)){
            $gradescaleoffset = 1;
        }

        //get grade item
        $gradeitem = new grade_item($data, true);
        if($cm->modname === 'assign'){
            require_once($CFG->dirroot . '/mod/assign/locallib.php');
            $assign = new assign(context_module::instance($cm->id), $cm, $courseid);
            $is_workflow_enabled = $assign->get_instance()->markingworkflow == 1;
        }
        foreach ($grades as $userid=>$grd)  {
            $hidden = $DB->get_field('grade_grades', 'hidden', array('itemid'=>local_gugcat::$PRVGRADEID, 'userid' => $userid));
            $select = "itemid = $gradeitemid AND userid = $userid";
            //update hidden status
            $DB->set_field_select('grade_grades', 'hidden', $hidden, $select);
            if(!empty($grd) && $hidden == 0){
                $rawgrade = array_search($grd, local_gugcat::$GRADES);
                $rawgrade = $rawgrade ? $rawgrade : $grd;
                switch ($rawgrade) {
                    case NON_SUBMISSION:
                        $feedback = NON_SUBMISSION_AC;
                        $is_admingrade = true;
                        $rawgrade = 0;
                        $excluded = 0;                        
                        break;
                    case MEDICAL_EXEMPTION:
                        $feedback = MEDICAL_EXEMPTION_AC;
                        $is_admingrade = true;
                        $rawgrade = null;
                        $excluded = 1; //excluded from aggregation
                        break;
                    default:
                        $is_admingrade = false;
                        $feedback = null;
                        $excluded = 0;
                        $rawgrade = !is_null($rawgrade) ? ($rawgrade - $gradescaleoffset) : $rawgrade;
                        break;
                }
                //update feedback and excluded field
                $DB->set_field_select('grade_grades', 'feedback', $feedback, $select);
                $DB->set_field_select('grade_grades', 'excluded', $excluded, $select);
                if($cm->modname === 'assign'){
                    // update assign grade
                    if ($grade = $assign->get_user_grade($userid, true)) {
                        //update workflow state marking worklow is enabled
                        if($is_workflow_enabled){
                            local_gugcat::update_workflow_state($assign, $userid, ASSIGN_MARKING_WORKFLOW_STATE_RELEASED);
                        }
                        $DB->set_field_select('grade_grades', 'overridden', 0, $select);
                        $grade->grade = $is_admingrade ? 0 : $rawgrade;
                        $grade->grader = $USER->id;
                        $assign->update_grade($grade); 
                    }
                    if($is_admingrade){
                        $DB->set_field_select('grade_grades', 'finalgrade', $rawgrade, $select);
                    }
                }else{         
                    //update grade from gradebook
                    $gradeitem->update_final_grade($userid, $rawgrade, null, false, FORMAT_MOODLE, $USER->id);
                }
                $DB->set_field_select('grade_grades', 'overridden', time(), $select);
            }
        }
        //unhide gradeitem 
        $gradeitem->hidden = 0;
        $gradeitem->update();
    }

    public static function import_from_gradebook($courseid, $module, $students, $activities){
        $mggradeitemid = local_gugcat::add_grade_item($courseid, get_string('moodlegrade', 'local_gugcat'), $module);

        $gradeitem_ = new grade_item(array('id'=>$mggradeitemid), true);
        $gradeitem_->timemodified = time();
        //update timemodified gradeitem
        $gradeitem_->update();
        $grade = null;
        
        $gbgrades = grade_get_grades($courseid, 'mod', $module->modname, $module->instance, array_keys($students));
        $gbgradeitem = array_values(array_filter($gbgrades->items, function($item) use($module){
            return $item->itemnumber == $module->gradeitem->itemnumber;//filter grades with specific itemnumber
        }));
        $gradescaleoffset = local_gugcat::is_grademax22($module->gradeitem->gradetype, $module->gradeitem->grademax) ? 1 : 0;

        foreach($students as $student){
            $gbg = isset($gbgradeitem[0]) ? $gbgradeitem[0]->grades[$student->id] : null;//gradebook grade record
            //check if assignment
            if(strcmp($module->modname, 'assign') == 0){
                $assign = new assign(context_module::instance($module->id), $module, $courseid);
                $asgrd = $assign->get_user_grade($student->id, false);
                if($gbg->overridden == 0 && isset($asgrd->grade)){                    
                    $grade = ($asgrd->grader >=0) ? ($asgrd->grade + $gradescaleoffset) : null;
                }else {
                    $grade = self::check_gb_grade($gbg, $gradescaleoffset);
                }
                local_gugcat::update_workflow_state($assign, $student->id, ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW);
            }else{
                $grade = self::check_gb_grade($gbg, $gradescaleoffset);
            }
            local_gugcat::update_grade($student->id, $mggradeitemid, $grade);
            local_gugcat::update_grade($student->id, local_gugcat::$PRVGRADEID, $grade);
        } 
        //every time import is clicked, weights from the main activity will be copied to provisional grade items
        self::set_provisional_weights($courseid, $activities, $students);
    }

    public static function hideshowgrade($userid){
        global $USER;
        
        $grade_ = new grade_grade(array('userid' => $userid, 'itemid' => local_gugcat::$PRVGRADEID), true);
        $grade_->usermodified = $USER->id;
        $grade_->itemid = local_gugcat::$PRVGRADEID;
        $grade_->userid = $userid;
        $grade_->timemodified = time();
        if($grade_->hidden == 0){
            $grade_->hidden = 1; 
            $message = 'hiddengrademsg';
        }  
        else {
            $grade_->hidden = 0;
            $message = 'showgrademsg';
        }
        local_gugcat::notify_success($message);
        return $grade_->update();        
    }

    /**
     * Copy weights from main activity grade item to provisional grade item
     *
     */
    public static function set_provisional_weights($courseid, $activities, $students){
        global $DB;
        foreach ($activities as $mod) {
            $prvgrdid = local_gugcat::get_grade_item_id($courseid, $mod->gradeitemid, get_string('provisionalgrd', 'local_gugcat'));
            if(!$prvgrdid){//create provisional grade item for modules that has no prv gi yet
                $prvgrdid = local_gugcat::add_grade_item($courseid, get_string('provisionalgrd', 'local_gugcat'), $mod);
            }
            //Work around when setting including no grades in aggregation is not accepted. 
            $weightcoef1 = $mod->gradeitem->aggregationcoef; //Aggregation coeficient used for weighted averages or extra credit
            $weightcoef2 = $mod->gradeitem->aggregationcoef2; //Aggregation coeficient used for weighted averages only
            $weight = ((float)$weightcoef1 > 0) ? (float)$weightcoef1 : (float)$weightcoef2;
            foreach ($students as $student) {
                $DB->set_field('grade_grades', 'information', $weight, array('itemid' => $prvgrdid, 'userid' => $student->id));          
            }
        }
    }

    /**
     * Returns gradebook grade, admin grade or null 
     *
     * @param mixed $gbgobj - gradebook grade object per student
     * @param mixed $gradescaleoffset - added to grade
     */
    public static function check_gb_grade($gbgobj, $gradescaleoffset){
        if(is_null($gbgobj)) return null;
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

}