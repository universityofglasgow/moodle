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
 * gugcat functions
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->libdir.'/grade/grade_item.php');
require_once($CFG->libdir.'/grade/grade_grade.php');
require_once('grade_capture_item.php');

class local_gugcat {
     
    /**
     * Database tables.
     */
    const TBL_GRADE_ITEMS  = 'grade_items';
    const TBL_GRADE_CATEGORIES  = 'grade_categories';
    const TBL_GRADE_GRADES = 'grade_grades';

    public static $GRADES = array(
        22=>"A1",
        21=>"A2",
        20=>"A3",
        19=>"A4",
        18=>"A5",
        17=>"B1",
        16=>"B2",
        15=>"B3",
        14=>"C1",
        13=>"C2",
        12=>"C3",
        11=>"D1",
        10=>"D2",
        9 =>"D3",
        8 =>"E1",
        7 =>"E2",
        6 =>"E3",
        5 =>"F1",
        4 =>"F2",
        3 =>"F3",
        2 =>"G1",
        1 =>"G2",
        0 =>"H"
    );

    public static function get_reasons(){
        return array(
            0=>get_string('gi_goodcause', 'local_gugcat'),
            1=>get_string('gi_latepenalty', 'local_gugcat'),
            2=>get_string('gi_cappedgrade', 'local_gugcat'),
            3=>get_string('gi_secondgrade', 'local_gugcat'),
            4=>get_string('gi_thirdgrade', 'local_gugcat'),
            5=>get_string('gi_agreedgrade', 'local_gugcat'),
            6=>get_string('gi_moderatedgrade', 'local_gugcat'),
            7=>get_string('gi_conductpenalty', 'local_gugcat'),
            8=>get_string('reasonother', 'local_gugcat')
        );
    }

    /**
     * Returns all activities/modules for specific course
     *
     * @param int $courseid
     * @param int $activityid
     */
    public static function get_activities($courseid, $activityid){
        $mods = grade_get_gradable_activities($courseid);
        $activities = array();
        foreach($mods as $value) {
            $activities[$value->id] = $value;
            $activities[$value->id]->selected = (strval($activityid) === $value->id)? 'selected' : '';
        }
        return $activities;
    }

    /**
     * Returns all grade items for specific course and module
     *
     * @param mixed $course
     * @param mixed $module
     */
    public static function get_grade_grade_items($course, $module){
        global $DB;
        $select = 'courseid = '.$course->id.' AND '.self::compare_iteminfo();
        $gradeitems = $DB->get_records_select(self::TBL_GRADE_ITEMS, $select, ['iteminfo' => $module->id]);
        $sort = 'id';
        $fields = 'userid, itemid, id, finalgrade, timemodified';
        foreach($gradeitems as $item) {
            $item->grades = $DB->get_records('grade_grades', array('itemid' => $item->id), $sort, $fields);
        }
        
        return $gradeitems;
    }

    public static function compare_iteminfo(){
        global $DB;
        return $DB->sql_compare_text('iteminfo') . ' = :iteminfo';
    }

    /**
     * Returns rows for grade capture table
     *
     * @param mixed $course
     * @param mixed $module
     * @param mixed $students
     */
    public static function grade_capture_get_rows($course, $module, $students){
        $captureitems = array();
        global $gradeitems, $prvgradeid;
        $grading_info = grade_get_grades($course->id, 'mod', $module->modname, $module->instance, array_keys($students));
        $gradeitems = self::get_grade_grade_items($course, $module);

        //---------ids needed for grade discrepancy
        if(!$agreedgradeid = self::get_grade_item_id($course->id, $module->id, get_string('gi_agreedgrade', 'local_gugcat'))){
            $secondgradeid = self::get_grade_item_id($course->id, $module->id, get_string('gi_secondgrade', 'local_gugcat'));
            $thirdgradeid = self::get_grade_item_id($course->id, $module->id, get_string('gi_thirdgrade', 'local_gugcat'));    
        };

        $i = 1;
        foreach ($students as $student) {
            $gbgrade = $grading_info->items[0]->grades[$student->id]->grade;
            $firstgrade = is_null($gbgrade) ? get_string('nograde', 'local_gugcat') : self::convert_grade($gbgrade);
            $gradecaptureitem = new grade_capture_item();
            $gradecaptureitem->cnum = $i;
            $gradecaptureitem->studentno = $student->id;
            $gradecaptureitem->surname = $student->lastname;
            $gradecaptureitem->forename = $student->firstname;
            $gradecaptureitem->firstgrade = $firstgrade;
            $gradecaptureitem->grades = array();
            foreach ($gradeitems as $item) {
                if(isset($item->grades[$student->id])){
                    $rawgrade = $item->grades[$student->id]->finalgrade;
                    if($item->id === $prvgradeid){
                        $grade = is_null($rawgrade) ? $firstgrade : self::convert_grade($rawgrade);
                        $gradecaptureitem->provisionalgrade = $grade;
                    }else{
                        $grdobj = new stdClass();
                        $grade = is_null($rawgrade) ? 'N/A' : self::convert_grade($rawgrade);
                        $grdobj->grade = $grade;
                        $grdobj->discrepancy = false;
                        //check grade discrepancy
                        if(!$agreedgradeid && $gbgrade){
                            if($item->id === $secondgradeid){
                                $grdobj->discrepancy = is_null($rawgrade) ? false : (($rawgrade != $gbgrade) ? true : false);
                            }else if($item->id === $thirdgradeid){
                                $grdobj->discrepancy = is_null($rawgrade) ? false : (($rawgrade != $gbgrade) ? true : false);
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
            // echo '<pre>';
            // var_dump($captureitems);
            // echo '</pre>';
        return $captureitems;
    }

    /**
     * Returns columns for grade capture table
     *
     */
    public static function grade_capture_get_columns($module){
        //global $gradeitems from get rows function
        global $gradeitems, $prvgradeid;
        $date = date("(j/n/Y)", strtotime(userdate($module->added)));
        $firstgrade = get_string('gradebookgrade', 'local_gugcat').'<br>'.$date;
        $columns = array($firstgrade);
        foreach ($gradeitems as $item) {
            $columns[$item->id] = $item->itemname;
        }
        //remove provisional column
        unset($columns[$prvgradeid]);
        return $columns;
    }

    public static function get_prv_grade_id($courseid, $modid){
        $pgrd_str = get_string('provisionalgrd', 'local_gugcat');
        $prvgrdid = self::add_grade_item($courseid, $pgrd_str, $modid);
        return $prvgrdid;
    }

    public static function get_grade_item_id($courseid, $modid, $itemname){
        global $DB;
        $select = 'courseid = :courseid AND '.self::compare_iteminfo(). ' AND itemname = :itemname ';
        $params = [
            'courseid' => $courseid,
            'itemname' => $itemname,
            'iteminfo' => $modid
        ];
        return $DB->get_field_select(self::TBL_GRADE_ITEMS, 'id', $select, $params);
    }

    public static function add_grade_item($courseid, $reason, $modid){
        global $DB;
    
        // check if gradeitem already exists using $reason, $courseid, $activityid
        if(!$gradeitemid = self::get_grade_item_id($courseid, $modid, $reason)){
            // create new gradeitem
             $gradeitem = new grade_item(array('id'=>0, 'courseid'=>$courseid));
            
             // get category id
             $categoryid = $DB->get_field(self::TBL_GRADE_CATEGORIES, 'id', array('courseid' => $courseid, 'parent' => null), MUST_EXIST);

             $gradeitem->weightoverride = 0;
             $gradeitem->gradepass = 0;
             $gradeitem->grademin = 0;
             $gradeitem->gradetype = 1;
             $gradeitem->display =0;
             $gradeitem->outcomeid = null;
             $gradeitem->categoryid = $categoryid;
             $gradeitem->iteminfo = $modid;
             $gradeitem->itemname = $reason;
             $gradeitem->iteminstance= null;
             $gradeitem->itemmodule=null;
             $gradeitem->itemtype = 'manual'; // All new items to be manual only.
     
             return $gradeitem->insert();
        }
        
        else {
            return $gradeitemid;
        }
    }
    
    public static function add_update_grades($userid, $itemid, $grades){
        global $DB;
        global $USER;

        $params = array(
            'userid' => $userid,
            'itemid' => $itemid,
            'rawgrade' => null,
            'finalgrade' => null
        );

        $grade = new grade_grade();
        $grade->itemid = $itemid;
        $grade->userid = $userid;
        $grade->rawgrade = $grades;
        $grade->rawgrademax = "100.000";
        $grade->rawgrademin = "0.00000";
        $grade->usermodified = $USER->id;
        $grade->finalgrade = $grades;
        $grade->hidden = "0";
        $grade->locked = "0";
        $grade->locktime = "0";
        $grade->exported = "0";
        $grade->overridden = "0";
        $grade->excluded = "0";
        $grade->feedbackformat = "0";
        $grade->informationformat = "0";
        $grade->timecreated = time();
        $grade->timemodified = time();
        $grade->aggregationstatus = "used";
        $grade->aggregationweight = "100.000"; 

        if(!$gradeid = $DB->get_field(self::TBL_GRADE_GRADES, 'id', $params)){
        //creates grade objects for other users in DB 
        return $grade->insert();
        }
        else{
        //updates empty grade objects in database
        $grade->id = $gradeid;
        return $grade->update();
        }
    }

    public static function update_grade($userid, $itemid, $grades){
        global $DB;
        global $USER;
        
        $params = array(
            'userid'=>$userid,
            'itemid'=>$itemid
        );
        //gets id for existing grade
        $gradeid = $DB->get_field(self::TBL_GRADE_GRADES, 'id', $params);

        $grade = new grade_grade();
        $grade->id = $gradeid;
        $grade->itemid = $itemid;
        $grade->userid = $userid;
        $grade->rawgrade = $grades;
        $grade->rawgrademax = "100.000";
        $grade->rawgrademin = "0.00000";
        $grade->usermodified = $USER->id;
        $grade->finalgrade = $grades;
        $grade->hidden = "0";
        $grade->locked = "0";
        $grade->locktime = "0";
        $grade->exported = "0";
        $grade->overridden = "0";
        $grade->excluded = "0";
        $grade->feedbackformat = "0";
        $grade->informationformat = "0";
        $grade->timecreated = time();
        $grade->timemodified = time();
        $grade->aggregationstatus = "used";
        $grade->aggregationweight = "100.000"; 
        //updates existing grade
        return $grade->update();
    }

    public static function convert_grade($grade){
        $final_grade = intval($grade);
        if (!($final_grade >= 22 || $final_grade < 0)){
            return self::$GRADES[$final_grade];
        }
        else {
            return self::$GRADES[0]; 
        }
    }

    public static function filter_grade_version($gradeitems, $studentid, $prvgradeid){
        foreach($gradeitems as $gradeitem){
            if(is_null($gradeitem->grades[$studentid]->finalgrade)) {
                unset($gradeitems[$gradeitem->id]);
            }
        }
        unset($gradeitems[$prvgradeid]);
        
        return $gradeitems;
    }

}
