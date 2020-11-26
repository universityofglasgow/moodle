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

class local_gugcat {
     
    /**
     * Database tables.
     */
    const TBL_GRADE_ITEMS  = 'grade_items';
    const TBL_GRADE_GRADES = 'grade_grades';

    public static $REASONS = array(
        0=>"Good Cause",
        1=>"Late Penalty",
        2=>"Capped Grade",
        3=>"Second Grade",
        4=>"Third Grade",
        5=>"Agreed Grade",
        6=>"Moderated Grade",
        7=>"Conduct Penalty",
        8=>"Other"
    );

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

    
    /**
     * Returns all activities/modules for specific course
     *
     * @param int $courseid
     * @param int $activityid
     */
    public static function get_activities($courseid, $activityid){
        global $modules;
        $mods = grade_get_gradable_activities($courseid);
        $activities = array();
        $assignments = array_filter($mods, function($mod){
            return (isset($mod->modname) && ($mod->modname === 'assign')) ? true : false;
        });
        $i = 1;
        foreach($assignments as $value) {
            $modules[$value->id] = $value;
            $activity = new stdClass();
            $activity->id = $value->id;
            $activity->name = "Assignment ".$i.": ".$value->name;
            $activity->modname = $value->modname;
            $activity->instance = $value->instance;
            $activity->selected = (strval($activityid) === $value->id)? 'selected' : '';
            array_push($activities, $activity);
            $i++;
        }
    
        $quizzes = array_filter($mods, function($mod){
            return (isset($mod->modname) && ($mod->modname === 'quiz')) ? true : false;
        });
        $i = 1;
        foreach($quizzes as $value) {
            $modules[$value->id] = $value;
            $activity = new stdClass();
            $activity->name = "Quiz ".$i.": ".$value->name;
            $activity->id = $value->id;
            $activity->modname = $value->modname;
            $activity->instance = $value->instance;
            $activity->selected = (strval($activityid) === $value->id)? 'selected' : '';
            array_push($activities, $activity);
            $i++;
        }
        return $activities;
    }

    /**
     * Returns all grade items for specific course and module
     *
     * @param mixed $course
     * @param mixed $module
     */
    public static function get_grade_items($course, $module){
        global $DB;
        $select = 'courseid = '.$course->id.' AND '.self::compare_iteminfo();
        $gradeitems = $DB->get_records_select(self::TBL_GRADE_ITEMS, $select, ['iteminfo' => $module->id]);
        $sort = 'id';
        $fields = 'userid, id, finalgrade, timemodified';
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
    public static function get_rows($course, $module, $students){
        $captureitems = array();
        global $gradeitems, $prvgradeid;
        $grading_info = grade_get_grades($course->id, 'mod', $module->modname, $module->instance, array_keys($students));
        $gradeitems = self::get_grade_items($course, $module);
        $i = 1;
        foreach ($students as $student) {
            $firstgrade = self::convert_grade($grading_info->items[0]->grades[$student->id]->grade);
            $gradecaptureitem = new grade_capture_item();
            $gradecaptureitem->cnum = $i;
            $gradecaptureitem->studentno = $student->id;
            $gradecaptureitem->surname = $student->lastname;
            $gradecaptureitem->forename = $student->firstname;
            $gradecaptureitem->firstgrade = $firstgrade;
    
            if(!empty($gradeitems)){
                $gradecaptureitem->grades = array();
                foreach ($gradeitems as $item) {
                    if($item->id === $prvgradeid){
                        $gradecaptureitem->provisionalgrade = self::convert_grade(( $item->grades[$student->id]->finalgrade));
                    }else{
                        $rawgrade = ( $item->grades[$student->id]->finalgrade);
                        $grade = is_null($rawgrade) ? 'N/A' : self::convert_grade($rawgrade);
                        array_push($gradecaptureitem->grades, $grade);
                    }
                }
            } else{
                $gradecaptureitem->provisionalgrade = self::convert_grade($firstgrade);
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
        global $gradeitems, $selectedmodule, $prvgradeid;
        $date = date("(j/n/Y)", strtotime(userdate($selectedmodule->added)));
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

    public static function add_grade_item($courseid, $reason, $modid){
        global $DB;
        //get category id
        $categoryid = $DB->get_field('grade_categories', 'id', array('courseid' => $courseid, 'parent' => null), MUST_EXIST);
    
        // check if gradeitem already exists using $reason, $courseid, $activityid
        $select = 'courseid = :courseid AND '.self::compare_iteminfo(). ' AND categoryid = :categoryid AND itemname = :itemname ';
        $params = [
            'courseid' => $courseid,
            'categoryid' => $categoryid,
            'itemname' => $reason,
            'iteminfo' => $modid
        ];
        if(!$gradeitemid = $DB->get_record_select(self::TBL_GRADE_ITEMS, $select, $params, 'id')){
            //  create new gradeitem
             $gradeitem = new grade_item(array('id'=>0, 'courseid'=>$courseid));
        
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
            return $gradeitemid->id;
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
        return $grade->insert();
        }
        else{
        $grade->id = $gradeid;
        return $grade->update();
        }
    }

    public static function convert_grade($grade){
        $final_grade = intval($grade);
        if (!($final_grade > 23 || $final_grade < 0)){
            return self::$GRADES[$final_grade];
        }
        else {
            return self::$GRADES[0]; 
        }
    }

}
