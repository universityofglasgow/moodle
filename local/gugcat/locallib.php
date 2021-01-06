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

//tables used in db
define('GRADE_GRADES', 'grade_grades');
define('GRADE_ITEMS', 'grade_items');
define('GRADE_CATEGORIES', 'grade_categories');
define('SCALE', 'scale');

//Administrative grades at Assessment Level
define('NON_SUBMISSION_AC', 'NS');
define('MEDICAL_EXEMPTION_AC', 'MV');
define('NON_SUBMISSION', -1);
define('MEDICAL_EXEMPTION', -2);

define('GCAT_SCALE', 'UofG 22-Point Scale (Do NOT use if you are grading in Feedback Studio)');
define('GCAT_GRADE_CATEGORY', 'DO NOT USE');

require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->libdir.'/grade/grade_item.php');
require_once($CFG->libdir.'/grade/grade_grade.php');
require_once($CFG->dirroot.'/mod/assign/locallib.php');

class local_gugcat {
     
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
    public static function get_activities($courseid){
        $activityid = optional_param('activityid', null, PARAM_INT);
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $mods = grade_get_gradable_activities($courseid);
        $activities = array();
        foreach($mods as $cm) {
            $activities[$cm->id] = $cm;
            $activities[$cm->id]->selected = (strval($activityid) === $cm->id)? 'selected' : '';
            $activities[$cm->id]->gradeitem = grade_item::fetch(array('itemtype'=>'mod', 'itemmodule'=>$cm->modname, 'iteminstance'=>$cm->instance, 'courseid'=>$courseid, 'itemnumber'=>0));
        }
        if(!is_null($categoryid) && $categoryid !== 0){
            foreach ($activities as $key=>$activity) {
                if ( $activity->gradeitem->categoryid !== strval($categoryid)) {
                    unset($activities[$key]);
                }
            }
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
        $gradeitems = $DB->get_records_select(GRADE_ITEMS, $select, ['iteminfo' => $module->id]);
        $sort = 'id';
        $fields = 'userid, itemid, id, rawgrade, finalgrade, timemodified, hidden';
        foreach($gradeitems as $item) {
            $grades_arr = $DB->get_records(GRADE_GRADES, array('itemid' => $item->id), $sort, $fields);
            foreach($grades_arr as $grditem) {
                $grditem->grade = is_null($grditem->finalgrade) ? $grditem->rawgrade : $grditem->finalgrade;
            }
            $item->grades = $grades_arr;
        }
        return $gradeitems;
    }

    /**
     * Returns the scale id used for GCAT
     */
    public static function get_gcat_scaleid(){
        global $DB;
        return $DB->get_field(SCALE, 'id', array('name' => GCAT_SCALE, 'courseid' => '0'));
    }

    public static function compare_iteminfo(){
        global $DB;
        return $DB->sql_compare_text('iteminfo') . ' = :iteminfo';
    }

    public static function set_prv_grade_id($courseid, $mod){
        if(is_null($mod)) return;
        $pgrd_str = get_string('provisionalgrd', 'local_gugcat');
        self::$PRVGRADEID = self::add_grade_item($courseid, $pgrd_str, $mod);
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
        return $DB->get_field_select(GRADE_ITEMS, 'id', $select, $params);
    }

    public static function is_grademax22($gradetype, $grademax){
        if ($gradetype == GRADE_TYPE_VALUE && $grademax == 22){
            return true;
        }
        return false;
    }

    public static function get_gcat_grade_category_id($courseid){
        global $DB;
        $categoryid = $DB->get_field(GRADE_CATEGORIES, 'id', array('fullname' => GCAT_GRADE_CATEGORY, 'courseid' => $courseid));
        if (empty($categoryid)){
            $grade_category = new grade_category(array('courseid'=>$courseid), false);
            $grade_category->apply_default_settings();
            $grade_category->apply_forced_settings();
            $grade_category->fullname = GCAT_GRADE_CATEGORY;
            $grade_category->hidden = 1;
            grade_category::set_properties($grade_category, $grade_category->get_record_data());
            $grade_category->insert();
            $categoryid = $grade_category->id;
        }
        return $categoryid;
    }

    public static function add_grade_item($courseid, $reason, $mod){
        //get scale size for max grade
        $scalesize = sizeof(self::$GRADES);
        // check if gradeitem already exists using $reason, $courseid, $activityid
        if(!$gradeitemid = self::get_grade_item_id($courseid, $mod->id, $reason)){
            //get GCAT grade category id
            $categoryid = self::get_gcat_grade_category_id($courseid);
            $scaleid = $mod->gradeitem->scaleid;
            if (self::is_grademax22($mod->gradeitem->gradetype, $mod->gradeitem->grademax)){
                $scaleid = self::get_gcat_scaleid();
            }
            // create new gradeitem
            $gradeitem = new grade_item(array('id'=>0, 'courseid'=>$courseid));
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
            $gradeitem->itemname = $itemname;
            $gradeitem->iteminstance= null;
            $gradeitem->timemodified = null;
            $gradeitem->itemmodule=null;
            $gradeitem->scaleid = $scaleid;
            $gradeitem->itemtype = 'manual'; // All new items to be manual only. 
            $gradeitemid = $gradeitem->insert();
            foreach(self::$STUDENTS as $student){
                self::add_update_grades($student->id, $gradeitemid, null);
            }
            return $gradeitemid;
        }else {
            return $gradeitemid;
        }
    }
    
    public static function add_update_grades($userid, $itemid, $grade, $notes = null, $gradedocs = null){
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
        $grade_->finalgrade = self::is_admin_grade($grade) ? null : $grade;
        $grade_->feedback = $notes;
        $grade_->information = $gradedocs;
        $grade_->hidden = 0;
        $grade_->excluded = 1;
      
        if(empty($grade_->id)){
            //creates grade objects for other users in DB 
            $grade_->timecreated = time();
            $grade_->timemodified = time();
            //if insert successful - update provisional grade
            return (!$grade_->insert()) ? false : 
            ((self::$PRVGRADEID && !is_null($grade))
            ? self::update_grade($userid, self::$PRVGRADEID, $grade) 
            : false);
            
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
        $grade_->finalgrade = self::is_admin_grade($grade) ? null : $grade;
        $grade_->itemid = $itemid;
        $grade_->userid = $userid;
        $grade_->excluded = 1;
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
            $finalgrade = (isset($gradeitem->grades[$studentid]) ? $gradeitem->grades[$studentid]->grade : null); 
            if(is_null($finalgrade)) {
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
        $scalegrades[NON_SUBMISSION] = NON_SUBMISSION_AC;
        $scalegrades[MEDICAL_EXEMPTION] = MEDICAL_EXEMPTION_AC;
        self::$GRADES = $scalegrades;
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

    public static function get_grade_categories($courseid){
        $raw = grade_get_categories_menu($courseid);
        $gcat_category_id = self::get_gcat_grade_category_id($courseid);
        unset($raw[$gcat_category_id]);
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $grd_ctgs = array();
        foreach($raw as $key=>$value) {
            $cat = new stdClass();
            $cat->key = ($value === get_string('uncategorised', 'grades')) ? 'null' : $key;
            $cat->value = $value;
            $cat->selected = ($categoryid === $key)? 'selected' : '';
            $grd_ctgs[$key] = $cat;
        }
        return $grd_ctgs;
    }

    public static function is_admin_grade($grade){
        switch ($grade) {
            case NON_SUBMISSION:
                return true;
            case MEDICAL_EXEMPTION:
                return true;
            default:
                return false;
        }
    }
}
