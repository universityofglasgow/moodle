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
require_once($CFG->dirroot.'/mod/assign/locallib.php');

class local_gugcat {
     
    /**
     * Database tables.
     */
    const TBL_ASSIGN_GRADES = 'assign_grades';
    const TBL_GRADE_ITEMS  = 'grade_items';
    const TBL_GRADE_CATEGORIES  = 'grade_categories';
    const TBL_GRADE_GRADES = 'grade_grades';
    const TBL_SCALE = 'scale';

    public static $GRADES = array();
    public static $PRVGRADEID = null;
    public static $STUDENTS = array();

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
    public static function get_activities($courseid, $activityid = null){
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

    public static function set_prv_grade_id($courseid, $mod, $scaleid){
        $pgrd_str = get_string('provisionalgrd', 'local_gugcat');
        self::$PRVGRADEID = self::add_grade_item($courseid, $pgrd_str, $mod, $scaleid);
        return self::$PRVGRADEID;
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

    public static function add_grade_item($courseid, $reason, $mod, $scaleid){
        global $DB;

        //get scale size for max grade
        $scalesize = sizeof(self::$GRADES);
        // check if gradeitem already exists using $reason, $courseid, $activityid
        if(!$gradeitemid = self::get_grade_item_id($courseid, $mod->id, $reason)){
            // create new gradeitem
             $gradeitem = new grade_item(array('id'=>0, 'courseid'=>$courseid));
            
             // get category id
             $categoryid = $DB->get_field(self::TBL_GRADE_CATEGORIES, 'id', array('courseid' => $courseid, 'parent' => null), MUST_EXIST);

             $gradeitem->weightoverride = 0;
             $gradeitem->gradepass = 0;
             $gradeitem->grademin = 1;
             $gradeitem->grademax = $scalesize;
             $gradeitem->gradetype = 2;
             $gradeitem->display =0;
             $gradeitem->hidden = 1;
             $gradeitem->outcomeid = null;
             $gradeitem->categoryid = $categoryid;
             $gradeitem->iteminfo = $mod->id;
             $gradeitem->itemname = $reason;
             $gradeitem->iteminstance= null;
             $gradeitem->timemodified = null;
             $gradeitem->itemmodule=null;
             $gradeitem->scaleid = $scaleid;
             $gradeitem->itemtype = 'manual'; // All new items to be manual only. 
             $gradeitemid = $gradeitem->insert();
             grade_get_grades($courseid, 'mod', $mod->modname, $mod->instance, array_keys(self::$STUDENTS));
             return $gradeitemid;
        }else {
            return $gradeitemid;
        }
    }
    
    public static function add_update_grades($userid, $itemid, $grade){
        global $USER;

        $params = array(
            'userid' => $userid,
            'itemid' => $itemid,
            'rawgrade' => null,
            'finalgrade' => null
        );

        $grade_ = new grade_grade($params, true);
        $grade_->itemid = $itemid;
        $grade_->userid = $userid;
        $grade_->rawgrade = $grade;
        $grade_->usermodified = $USER->id;
        $grade_->finalgrade = $grade;
        $grade_->hidden = 0;
      
        if(empty($grade_->id)){
            //creates grade objects for other users in DB 
            $grade_->timecreated = time();
            $grade_->timemodified = time();
            //if insert successful - update provisional grade
            return ($grade_->insert()) ? self::update_grade($userid, self::$PRVGRADEID, $grade) : false;
            
        }else{
            //updates empty grade objects in database
            $grade_->timemodified = time();
            //if update successful - update provisional grade
            return ($grade_->update()) ? self::update_grade($userid, self::$PRVGRADEID, $grade) : false;
        }
    }

    public static function update_grade($userid, $itemid, $grade){
        global $USER;

        //get grade grade, true
        $grade_ = new grade_grade(array('userid' => $userid, 'itemid' => $itemid), true);
        $grade_->rawgrade = $grade;
        $grade_->usermodified = $USER->id;
        $grade_->finalgrade = $grade;
        $grade_->itemid = $itemid;
        $grade_->userid = $userid;
        $grade_->timemodified = time();
        //update existing grade
        return $grade_->update();
    }

    public static function convert_grade($grade){
        $scale = self::$GRADES;
        $final_grade = intval($grade);
        if ($final_grade >= key(array_slice($scale, -1, 1, true)) && $final_grade <= key($scale)){
            return $scale[$final_grade];
        }
        else {
            return $grade; 
        }
    }

    public static function filter_grade_version($gradeitems, $studentid){
        foreach($gradeitems as $gradeitem){
            if(is_null($gradeitem->grades[$studentid]->finalgrade)) {
                unset($gradeitems[$gradeitem->id]);
            }
        }
        unset($gradeitems[self::$PRVGRADEID]);
        
        return $gradeitems;
    }

    public static function set_grade_scale($scaleid){
        global $DB;

        $scalegrades = array();
        if($scale = $DB->get_record('scale', array('id'=>$scaleid), '*')){
        $scalegrades = make_menu_from_list($scale->scale); 
        }
        self::$GRADES = $scalegrades;
    }

    public static function get_scaleid($module){
        global $DB;
        $params = array(
            'iteminstance'  => $module->instance,
            'itemmodule'    => $module->modname
        );
        $scaleid = $DB->get_field(self::TBL_GRADE_ITEMS, 'scaleid', $params, IGNORE_MISSING);
        return $scaleid;
    }

    public static function notify_success($stridentifier){
        $message = get_string($stridentifier, 'local_gugcat');
        \core\notification::add($message, \core\output\notification::NOTIFY_SUCCESS);
    }
        
    public static function update_workflow_state($assign, $userid, $statetype){
        //update workflow state to in review
        $assign_user_flags = $assign->get_user_flags($userid, true);
        $assign_user_flags->workflowstate = $statetype;
        $assign->update_user_flags($assign_user_flags);
    }

}
