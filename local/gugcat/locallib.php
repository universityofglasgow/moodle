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
 * Local library containing helper methods for GCAT.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_gugcat\grade_aggregation;

require_once($CFG->libdir . '/adminlib.php');

defined('MOODLE_INTERNAL') || die();

//Scale type 
define('SCHEDULE_A', 1);
define('SCHEDULE_B', 2);
//Administrative grades
define('NON_SUBMISSION_AC', 'NS');
define('MEDICAL_EXEMPTION_AC', 'MV');
define('CREDIT_WITHHELD_AC', 'CW');
define('CREDIT_REFUSED_AC','CR');
define('CA_AC', 'CA');
define('UNDER_INVESTIGATION_AC', '07');
define('AU_AC', 'AU');
define('FC_AC', 'FC');
define('NON_SUBMISSION', -1);
define('MEDICAL_EXEMPTION', -2);
define('CREDIT_WITHHELD', -3);
define('CREDIT_REFUSED', -4);
define('CA', -5);
define('UNDER_INVESTIGATION', -6);
define('AU', -7);
define('FC', -8);

define('GCAT_MAX_USERS_PER_PAGE', 50);

require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->libdir.'/grade/grade_item.php');
require_once($CFG->libdir.'/grade/grade_grade.php');
require_once($CFG->dirroot.'/mod/assign/locallib.php');
require_once($CFG->libdir.'/dataformatlib.php');

class local_gugcat {
     
    public static $GRADES = array();
    public static $SCHEDULE_A = array();
    public static $SCHEDULE_B = array();
    public static $PRVGRADEID = null;
    public static $STUDENTS = array();

     /**
     * Returns reasons/grade versions in array
     *
     */
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
     * @param mixed $categoryid_ids Either a single category id, an array of category IDs or null. If category ID or IDs are not supplied, if gets category id from url or course grade category
     * @param grade_item $gradeitem Use for specific activity id to get specific details.
     */
    public static function get_activities($courseid, $categoryid_ids = null, $gradeitem = null){
        $activityid = optional_param('activityid', null, PARAM_INT);
        $cids = array();
        if (empty($categoryid_ids)) {
            $categoryid = optional_param('categoryid', null, PARAM_INT);
            if((is_null($categoryid) || $categoryid == 0) 
            && $gc = grade_category::fetch_course_category($courseid)){
                $categoryid = $gc->id;
            }
            $cids = array($categoryid);
        } else if (is_array($categoryid_ids)) {
            $cids = $categoryid_ids;
        } else {
            $cids = array($categoryid_ids);
        }
        $wholegradingforums = array();
        $assessmentworkshops = array();
        // if is not $gradeitem, get the specific activity with gradeitem details
        if(isset($gradeitem)){
            $mods = self::grade_get_gradable_activities($courseid, $cids, $gradeitem->itemmodule, $gradeitem->itemnumber, $gradeitem->id);
        }else{
            $mods = self::grade_get_gradable_activities($courseid, $cids);
    
            //get whole grading forums and workshop assessment | itemnumber = 1
            $wholegradingforums = self::grade_get_gradable_activities($courseid, $cids, 'forum', 1);
            $assessmentworkshops = self::grade_get_gradable_activities($courseid, $cids, 'workshop', 1);
        }

        $activities = array();
        if(count($mods) > 0 || count($wholegradingforums) > 0 || count($assessmentworkshops) > 0){
            $mods = array_merge(array_values($mods), $wholegradingforums + $assessmentworkshops);
            foreach($mods as $cm) {
                $activities[$cm->gradeitemid]= $cm;
                $activities[$cm->gradeitemid]->selected = (strval($activityid) === $cm->gradeitemid)? 'selected' : '';
                $gi = isset($gradeitem) ? $gradeitem : new grade_item(array('id'=>$cm->gradeitemid), true);
                $gi->load_parent_category();
                $activities[$cm->gradeitemid]->gradeitem = $gi;
            }

            // Remove gradeitems which do not fall within 22-point scale.
            // Deletes gcat items for deletion in progress == 1 activities 
            foreach($activities as $key=>$activity){   
                if($activity->deletioninprogress == 1){
                    unset($activities[$key]);
                    self::delete_gcat_items($courseid, $activity);
                    continue;
                }
            }
        }
        return $activities;
    }

    /**
     * Returns activity details for assessments or sub categories
     *
     * @param int $courseid
     * @param int $gradeitemid Id of the grade item
     * @retirun int $gradeitemid Id of the grade item
     */
    public static function get_activity($courseid, $gradeitemid){
        $gi = new grade_item(array('id'=> $gradeitemid), true);
        if($gi->itemtype == 'category'){
            $gc = $gi->get_item_category();
            return local_gugcat::get_category_gradeitem($courseid, $gc);
        }else{
            $act = self::get_activities($courseid, $gi->categoryid, $gi);
            return reset($act);
        }
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
        //Retrieve grade items
        $gradeitems = $DB->get_records_select('grade_items', $select, ['iteminfo' => $module->gradeitemid], 'timemodified');
        $sort = 'id';
        $fields = 'userid, itemid, id, rawgrade, finalgrade, timemodified, hidden';
        foreach($gradeitems as $item) {
            //Retrieve grade_grades of each grade item
            $grades_arr = $DB->get_records('grade_grades', array('itemid' => $item->id), $sort, $fields);
            foreach($grades_arr as $grditem) {
                $grditem->grade = is_null($grditem->finalgrade) ? $grditem->rawgrade : $grditem->finalgrade;
            }
            $item->grades = $grades_arr;
        }
        return $gradeitems;
    }

    /**
     * Helper function in comparing iteminfo 
     *
     */
    public static function compare_iteminfo(){
        global $DB;
        return $DB->sql_compare_text('iteminfo') . ' = :iteminfo';
    }

    /**
     * Set the static $PRVGRADEID when provisional grade item exist, creates if not yet created
     * @param int $courseid
     * @param mixed $mod Selected course module
     */
    public static function set_prv_grade_id($courseid, $mod){
        if(is_null($mod)) return;
        $pgrd_str = get_string(($mod->modname == 'category' ? 'subcategorygrade' :'provisionalgrd'), 'local_gugcat');
        $id = $mod->modname == 'category' ? $mod->id : $mod->gradeitemid;
        self::$PRVGRADEID = self::get_grade_item_id($courseid, $id, $pgrd_str);
        return self::$PRVGRADEID;
    }

    /**
     * Returns the grade item id based from the passed parameters
     * @param int $courseid
     * @param int $id Selected course module grade item ID or sub category id
     * @param string $itemname Item name can be Provisional Grade or Aggregated Grade
     */
    public static function get_grade_item_id($courseid, $id, $itemname){
        global $DB;
        $select = 'courseid = :courseid AND itemname = :itemname ';
        $params = [
            'courseid' => $courseid,
            'itemname' => $itemname
        ];
        if(!is_null($id)){
            $select .= 'AND '.self::compare_iteminfo();
            $params['iteminfo'] = $id; // id = gradeitemid or categoryid
        }
        return $DB->get_field_select('grade_items', 'id', $select, $params);
    }

    /**
     * Returns boolean if grade item max 22
     * @param int $gradetype
     * @param int $grademax 
     */
    public static function is_grademax22($gradetype, $grademax){
        if (($gradetype == GRADE_TYPE_VALUE && intval($grademax) == 22)){
            return true;
        }
        return false;
    }

    /**
     * Returns boolean if grade type is value with min = 0
     * @param int $gradetype
     * @param int $grademin 
     */
    public static function is_validgradepoint($gradetype, $grademin){
        if (($gradetype == GRADE_TYPE_VALUE && intval($grademin) == 0)){
            return true;
        }
        return false;
    }

    /**
     * Returns boolean if scale is schedule A
     * @param int $gradetype
     * @param int $grademax 
     */
    public static function is_scheduleAscale($gradetype, $grademax){
        if (($gradetype == GRADE_TYPE_SCALE && intval($grademax) == 23)){
            return true;
        }
        return false;
    }

    /**
     * Returns the gcat DO NOT USE grade category id
     * @param int $courseid
     * @param boolean $create Create gcat category if true
     */
    public static function get_gcat_grade_category_id($courseid, $create = false){
        global $DB;
        $grdcategorystr = get_string('gcat_category', 'local_gugcat');
        $categoryid = $DB->get_field('grade_categories', 'id', array('fullname' => $grdcategorystr, 'courseid' => $courseid));
        if (empty($categoryid) && $create){
            $grade_category = new grade_category(array('courseid'=>$courseid), false);
            $grade_category->apply_default_settings();
            $grade_category->apply_forced_settings();
            $grade_category->fullname = $grdcategorystr;
            $grade_category->hidden = 1;
            grade_category::set_properties($grade_category, $grade_category->get_record_data());
            $grade_category->insert();
            $categoryid = $grade_category->id;
        }
        return $categoryid;
    }

    /**
     * Creates grade item and returns grade item id
     * @param int $courseid
     * @param mixed $mod Selected course module
     * @param string $itemname 
     */
    public static function add_grade_item($courseid, $itemname, $mod, $students_ = null){
        // Get all students ids if students_ param is null
        $students = is_null($students_) 
        ? get_enrolled_users(context_course ::instance($courseid), 'local/gugcat:gradable', 0, 'u.id') 
        : $students_;

        $params = [
            'courseid' => $courseid,
            'itemtype' => 'manual',
            'hidden' => 1,
            'weightoverride' => 1,
            'categoryid' => self::get_gcat_grade_category_id($courseid, true),
        ];
        if(is_null($mod)){
            $params['itemname'] = $itemname;
            //creates grade item that has no module
            $gradeitem = new grade_item($params, true);
            if($gradeitem->id){
                return $gradeitem->id;
            }else{
                $gradeitemid = $gradeitem->insert();
                foreach($students as $student){
                    local_gugcat::add_update_grades($student->id, $gradeitemid, null);
                }
                return $gradeitemid;
            }
        }else{
            // check if gradeitem already exists using $itemname, $courseid, $activityid (gradeitemid)
            if(!$gradeitemid = self::get_grade_item_id($courseid, $mod->gradeitemid, $itemname)){
                $params_mod = [
                    'scaleid' => $mod->gradeitem->scaleid,
                    'grademin' => 1,
                    'grademax' => sizeof(self::$GRADES),
                    'gradetype' => 2,
                    'iteminfo' => $mod->gradeitemid,
                    'itemname' => $itemname
                ];
                // create new gradeitem
                $gradeitem = new grade_item(array_merge($params, $params_mod));
                $gradeitemid = $gradeitem->insert();
                foreach($students as $student){
                    self::add_update_grades($student->id, $gradeitemid, null);
                }
                return $gradeitemid;
            }else {
                return $gradeitemid;
            }
        }
    }
    
    /**
     * Creates/Updates grade_grade item of the student
     * @param int $userid Student id
     * @param int $itemid Grade item id
     * @param int $grade 
     * @param mixed $notes 
     * @param mixed $gradedocs 
     */
    public static function add_update_grades($userid, $itemid, $grade, $notes = null){
        global $USER, $DB;

        $params = array(
            'userid' => $userid,
            'itemid' => $itemid
        );

        $grade_ = new grade_grade($params, true);
        $grade_->itemid = $itemid;
        $grade_->userid = $userid;
        $grade_->rawgrade = $grade;
        $grade_->usermodified = $USER->id;
        $grade_->finalgrade = self::is_admin_grade($grade) ? null : $grade;
        $grade_->feedback = $notes;
        $grade_->hidden = 0;
        $grade_->excluded = 1;
      
        if(!isset($grade_->id)){
            //creates grade objects for other users in DB 
            $grade_->timecreated = time();
            $grade_->timemodified = time();
            //if insert successful - update provisional grade
            return (!$grade_->insert()) ? false : 
            ((self::$PRVGRADEID && !is_null($grade))
            ? self::update_grade($userid, self::$PRVGRADEID, $grade, '') 
            : false);
            
        }else{
            //updates grade objects in database
            $grade_->timemodified = time();
            //if update successful - update provisional grade
            if($grade_->update()){
                //update timemodified of grade item
                $DB->set_field('grade_items', 'timemodified', $grade_->timemodified, array('id' => $itemid));
                return self::update_grade($userid, self::$PRVGRADEID, $grade, '');
            }
            return false;
        }
    }

    /**
     * Updates grade_grade item of the student
     * @param int $userid Student id
     * @param int $itemid Grade item id
     * @param int $grade 
     * @param mixed $notes 
     * @param mixed $gradedocs 
     * @param int $overridden 
     */
    public static function update_grade($userid, $itemid, $grade, $notes = null, $overridden = 0){
        global $USER;

        //get grade grade, true
        $grade_ = new grade_grade(array('userid' => $userid, 'itemid' => $itemid), true);
        $grade_->rawgrade = $grade;
        $grade_->usermodified = $USER->id;
        $grade_->finalgrade = self::is_admin_grade($grade) ? null : $grade;
        if(!is_null($notes))
            $grade_->feedback = $notes;
        $grade_->itemid = $itemid;
        $grade_->userid = $userid;
        $grade_->overridden = $overridden;
        $grade_->timemodified = time();
        //update existing grade
        return $grade_->update();
    }

    /**
     * Converts grade from the grade scale
     * @param mixed $grade 
     */
    public static function convert_grade($grade, $gradetype = GRADE_TYPE_SCALE){
        if(!local_gugcat::is_admin_grade($grade) && $gradetype == GRADE_TYPE_VALUE){
            return number_format($grade, 2);
        }
        $scale = self::$GRADES + grade_aggregation::$AGGRADE;

        //add admin grades in scale
        $scale[NON_SUBMISSION] = NON_SUBMISSION_AC;
        
        $final_grade = round($grade);
        if ($final_grade >= key(array_slice($scale, -1, 1, true)) && $final_grade <= key($scale)){
            return ($final_grade != 0) ? $scale[$final_grade] : $final_grade;
        }else {
            return $grade; 
        }
    }

    /**
     * Removes ungraded grade versions and provisional grade
     * 
     * @param array $gradeitems graded gradeversions.
     * @param int $studentid student's user id.
     */
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

    /**
     * Set the static $GRADES scale based from the scale id
     * @param int $scaleid
     * @param int $scaletype Scale type can be Schedule or Schedule B
     */
    public static function set_grade_scale($scaleid = null, $scaletype = SCHEDULE_A){
        global $DB;
        $scalegrades = array();
        if(is_null($scaleid)){
            list($scalegrades, $schedB) = self::get_gcat_scale();
            $scalegrades = ($scaletype != SCHEDULE_A) ? $schedB : $scalegrades;
            self::$SCHEDULE_A = $scalegrades;
            self::$SCHEDULE_B = $schedB;
        }else{
            if($scale = $DB->get_record('scale', array('id'=>$scaleid), '*')){
                $scalegrades = make_menu_from_list($scale->scale); 
            }
        }
        $scalegrades[NON_SUBMISSION] = NON_SUBMISSION_AC;
        $scalegrades[MEDICAL_EXEMPTION] = MEDICAL_EXEMPTION_AC;
        self::$GRADES = $scalegrades;
    }

    /**
     * Retrieves the custom gcat scale from json file
     */
    public static function get_gcat_scale(){
        global $CFG;
        $json = @file_get_contents($CFG->dirroot .'/local/gugcat/gcat_scale.json');
        $scale = array();
        if($json !== false){
            $obj = json_decode($json);
            $A = isset($obj) ? $obj->schedule_A : [];
            $B = isset($obj) ? $obj->schedule_B : [];
            $schedA = array_reverse(array_filter(array_merge(array(0), $A)),true);//starts 1 => H Schedule A
            $schedB = array_reverse(array_filter(array_merge(array(0), $B)),true);//starts 1 => H Schedule B
            return array($schedA, $schedB);
        }
        return $scale;
    }

    /**
     * Displays moodle success notification and gets the string from local_gugcat strings 
     * @param string $stridentifier
     */
    public static function notify_success($stridentifier){
        $message = get_string($stridentifier, 'local_gugcat');
        \core\notification::add($message, \core\output\notification::NOTIFY_SUCCESS);
    }

    /**
     * Displays moodle error notification and gets the string from local_gugcat strings 
     * @param string $stridentifier
     */
    public static function notify_error($stridentifier, $str = null){
        $message = is_null($str) ? get_string($stridentifier, 'local_gugcat') : $str;
        \core\notification::add($message, \core\output\notification::NOTIFY_ERROR);
    }
        
    /**
     * Updates workflow state of assign module
     * @param mixed $assign Instance of Assign class
     * @param int $userid 
     * @param string $statetype
     */
    public static function update_workflow_state($assign, $userid, $statetype){
        $assign_user_flags = $assign->get_user_flags($userid, true);
        $assign_user_flags->workflowstate = $statetype;
        $assign->update_user_flags($assign_user_flags);
    }

    /**
     * Retrieve all grade categories for specific course
     * @param int $courseid 
     */
    public static function get_grade_categories($courseid){
        $categories = array();
        // Retrieve course grade category
        $course_category = grade_category::fetch_course_category($courseid);
        $categories[$course_category->id] = $course_category;
        // Retrieve categories which parent is equal to the course grade category id
        $categories = $categories + grade_category::fetch_all(array('courseid' => $courseid, 'parent' => $course_category->id));
        // Remove custom gcat DO NOT USE category
        $gcat_category_id = self::get_gcat_grade_category_id($courseid);
        unset($categories[$gcat_category_id]);

        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $grd_ctgs = array();
        foreach($categories as $key=>$category) {
            $uncategorised = ($key == $course_category->id) ? true : false;
            $cat = new stdClass();
            $cat->key = $uncategorised ? 'null' : $key;
            $cat->value = $uncategorised ? get_string('uncategorised', 'grades') : $category->fullname;
            $cat->selected = ($categoryid === $key)? 'selected' : '';
            $grd_ctgs[$key] = $cat;
        }
        return $grd_ctgs;
    }

    /**
     * Returns boolean if grade is admin grade 
     * @param int $grade 
     */
    public static function is_admin_grade($grade){
        switch (intval($grade)) {
            case NON_SUBMISSION:
                return true;
            case MEDICAL_EXEMPTION:
                return true;
            case CREDIT_WITHHELD:
                return true;
            case CREDIT_REFUSED:
                return true;
            case CA:
                return true;    
            case UNDER_INVESTIGATION:
                return true;
            case AU:
                return true;
            case FC:
                return true;    
            default:
                return false;
        }
    }

    /**
     * Returns rows of grade version history
     * 
     * @param int $courseid
     * @param mixed $module select course module
     * @param int $studentid student's user id
     * 
     * 
     */
    public static function get_grade_history($courseid, $module, $studentid){
        global $DB;
        $select = 'courseid = '.$courseid.' AND '.self::compare_iteminfo();
        $gradeitems = $DB->get_records_select('grade_items', $select, ['iteminfo' => $module->gradeitemid]);
        unset($gradeitems[self::$PRVGRADEID]);
        $grades_arr = array();
        foreach($gradeitems as $gradeitem){
            $gradehistory_arr = $DB->get_records('grade_grades_history', array('userid'=>$studentid, 'itemid'=>$gradeitem->id), MUST_EXIST);
            foreach($gradehistory_arr as $grd){
                $fields = 'firstname, lastname';
                if(!is_null($grd->usermodified) && !is_null($grd->rawgrade)){
                $modby = $DB->get_record('user', array('id' => $grd->usermodified), $fields);
                $grd->modby = (isset($modby->lastname) && isset($modby->firstname)) ? $modby->lastname . ', '.$modby->firstname : null;
                $grd->notes = !is_null($grd->feedback) ? ($grd->feedback != "" ? $grd->feedback : 'N/A - '.$gradeitem->itemname) : 'N/A - '.$gradeitem->itemname;
                $grd->type = ($gradeitem->itemname == get_string('moodlegrade', 'local_gugcat')) ? 
                $gradeitem->itemname. '<br>'.date("j/n/Y", strtotime(userdate($grd->timemodified)))
                 : $gradeitem->itemname;
                $grd->date = date("j/n", strtotime(userdate($grd->timemodified))).'<br>'.date("h:i", strtotime(userdate($grd->timemodified)));
                $grd->grade = !is_null($grd->finalgrade) ? self::convert_grade($grd->finalgrade) : self::convert_grade($grd->rawgrade);
                array_push($grades_arr, $grd);
                }
            }
        }
        //sort array by timemodified
        usort($grades_arr,function($first,$second){
            return $first->timemodified < $second->timemodified;
        });

        return $grades_arr;
    }
    
    /**
     * Reused moodle export function
     * @param string $filename 
     * @param array $columns 
     * @param array $iterator 
     */
    public static function export_gcat($filename, $columns, $iterator){
        $dataformat = 'csv';
        // In 3.9 forward, download_as_dataformat is replaced by \core\dataformat::download_data.
        if (method_exists('\\core\\dataformat', 'download_data')) {
            \core\dataformat::download_data($filename, $dataformat, $columns, $iterator);
            exit;
        } else {
            download_as_dataformat($filename, $dataformat, $columns, $iterator);
            exit;
        } 
    }

    /**
     * Returns boolean if blind marking is enabled
     * @param mixed $module 
     */
    public static function is_blind_marking($module = null){
        global $COURSE;
        $coursecontext = context_course::instance($COURSE->id);
        if(has_capability('local/gugcat:revealidentities', $coursecontext)){
            return false;
        }else{
            if(!is_null($module)){
                if($module->modname === 'assign'){
                    $assign = new assign(context_module::instance($module->id), $module, $COURSE->id);
                    return $assign->is_blind_marking();
                }
                return false;
            }else{
                return true;//aggregation tool
            }
        }
    }

    /**
     * Custom grade_get_gradable_activities to accommodate modules with itemnumber 1
     * @param int $courseid 
     * @param array $categoryids
     * @param string $modulename 
     * @param int $itemnumber 
     */
    private static function grade_get_gradable_activities($courseid, $categoryids, $modulename='', $itemnumber = 0, $gradeitemid = null) {
        global $DB;
        if (empty($modulename)) {
            $modules = array('assign', 'forum', 'quiz', 'workshop');//modules supported by gcat
            $result = array();
            foreach ($modules as $module) {
                if ($cms = self::grade_get_gradable_activities($courseid, $categoryids, $module, $itemnumber, $gradeitemid)) {
                    $result =  $result + $cms;
                }
            }
            return $result;
        }
        $categorysql = '';
        foreach($categoryids as $id){
            $categorysql .= "gi.categoryid = $id OR ";
        }
        // Remove the last 'OR'
        $categorysql = chop($categorysql, ' OR ');
        
        $params = array($courseid, $modulename, $itemnumber, GRADE_TYPE_NONE, $modulename);
        $sql = "SELECT cm.*, gi.itemname as name, md.name as modname, gi.id as gradeitemid
                  FROM {grade_items} gi, {course_modules} cm, {modules} md, {{$modulename}} m
                 WHERE gi.courseid = ? AND
                       gi.itemmodule = ? AND
                       gi.itemnumber = ? AND
                       gi.gradetype != ? AND
                       gi.iteminstance = cm.instance AND
                       cm.instance = m.id AND
                       md.name = ? AND
                       md.id = cm.module AND
                       (gi.itemtype = 'mod' OR gi.itemtype = 'category')".
                       (is_null($gradeitemid) ? null : " AND gi.id = $gradeitemid").
                       (empty($categoryids) ? null : " AND ($categorysql)"); 
    
        return $DB->get_records_sql($sql, $params);
    }

    /**
     *  Custom field method to create and update value of customfield_data
     * @param int $instanceid 
     * @param int $contextid 
     */
    public static function switch_display_of_assessment_on_student_dashboard($instanceid, $contextid){
        global $DB;

        $customfieldcategory = $DB->get_record('customfield_category', array('name' => get_string('gugcatoptions', 'local_gugcat')));
        if($customfieldcategory){
            $customfieldfield = $DB->get_record('customfield_field', array('categoryid' => $customfieldcategory->id));
            if(!empty($customfieldfield)){
                $customfielddata = $DB->get_record('customfield_data', array('fieldid' => $customfieldfield->id , 'instanceid' => $instanceid, 'contextid' => $contextid));
                if(!empty($customfielddata)){
                    $customfielddatadobj = new stdClass();
                    $customfielddatadobj->id = (int)$customfielddata->id;

                    if((int)$customfielddata->intvalue == 1){
                        $customfielddatadobj->intvalue = 0;
                        $customfielddatadobj->value = "0";
                    }
                    else{
                        $customfielddatadobj->intvalue = 1;
                        $customfielddatadobj->value = "1";
                    }

                    if($DB->update_record('customfield_data', $customfielddatadobj, $bulk=false)){
                        return $customfielddatadobj->intvalue;
                    };
                }
                else{
                    $customfieldddataobj = self::default_contextfield_data_value($customfieldfield->id, $instanceid, $contextid);
                    $DB->insert_record('customfield_data', $customfieldddataobj);
                    return $customfielddatadobj->intvalue;
                }
            }
        }
        else{
            $customfieldcategory = new stdClass();
            $customfieldcategory->name = get_string('gugcatoptions', 'local_gugcat');
            $customfieldcategory->component ="core_course";
            $customfieldcategory->area = "course";
            $customfieldcategory->timecreated = time();
            $customfieldcategory->timemodified = time();
            $customfieldcategoryid = $DB->insert_record('customfield_category', $customfieldcategory, $returnid=true, $bulk=false);
            if(!is_null($customfieldcategoryid)){
                $configdata = '{"required":"0","uniquevalues":"0","checkbydefault":"0","locked":"0","visibility":"0"}';
                $category = \core_customfield\category_controller::create($customfieldcategoryid);
                $field = \core_customfield\field_controller::create(0, (object)[
                    'type' => 'checkbox',
                    'configdata' => $configdata
                ], $category);

                $handler = $field->get_handler();
                $handler->save_field_configuration($field, (object)[
                    'name' => get_string('showassessment', 'local_gugcat'), 
                    'shortname' => get_string('showonstudentdashboard', 'local_gugcat')
                ]);
                
                $customfieldfield = $DB->get_record('customfield_field', array('categoryid' => $customfieldcategoryid));
                if(!is_null($customfieldfield->id) && !is_null($instanceid) && !is_null($contextid)){
                    $customfieldddataobj = self::default_contextfield_data_value($customfieldfield->id, $instanceid, $contextid);
                    $DB->insert_record('customfield_data', $customfieldddataobj);
                    $customfielddata = $DB->get_record('customfield_data', array('fieldid' => $customfieldfield->id , 'instanceid' => $instanceid, 'contextid' => $contextid));
                    return (int)$customfielddata->intvalue;
                }
            }
            return 1;
        }
    }

    /**
     * Custom method to get the value of the customfield data
     * @param int $instanceid 
     * @param int $contextid 
     */
    public static function get_value_of_customfield_checkbox($instanceid, $contextid){
        global $DB;

        $customfieldcategory = $DB->get_record('customfield_category', array('name'=> get_string('gugcatoptions', 'local_gugcat')));
        if($customfieldcategory){
            $customfieldfield = $DB->get_record('customfield_field', array('categoryid'=> $customfieldcategory->id));
            if(!empty($customfieldfield)){
                $customfielddata = $DB->get_record('customfield_data', array('fieldid'=> $customfieldfield->id, 'instanceid' => $instanceid, 'contextid' => $contextid));
                if(!empty($customfielddata)){
                    return (int)$customfielddata->intvalue;
                }
                return 0;
            }
        }
    }

    /**
     * Reusable customfield_data object
     * @param int $customfieldid 
     * @param int $instanceid 
     * @param int $contextid 
     */
    public static function default_contextfield_data_value($customfieldid, $instanceid, $contextid){
        $default_obj = (object) array(
            "fieldid"      => $customfieldid,
            "instanceid"   => $instanceid,
            "intvalue"     => 1,
            "value"        => "1",
            "valueformat"  => 0,
            "timecreated"  => time(),
            "timemodified" => time(),
            "contextid"    => $contextid
        );

        return $default_obj;
    }

    /**
     * Retrieve display students based from the search filters
     * @param context $coursecontext
     * @param array $filters 
     * @param int $groupid 0 means ignore groups, USERSWITHOUTGROUP without any group and any other value limits the result by group id
     * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
     * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).\
     * @return array list($data, $count) returns list of students and total count of filtered result
     */
    public static function get_filtered_students($coursecontext, $filters, $groupid = 0, $limitfrom = 0, $limitnum = 0){
        global $DB;
        list($enrolledsql, $enrolledparams) = get_enrolled_sql($coursecontext, 'local/gugcat:gradable', $groupid);
        $filtersql = '';
        foreach($filters as $key=>$value){
            if(!empty($value)){
                $filtersql .= $DB->sql_like("u.$key", ":$key", false).' AND ';
                $params[$key] = "%$value%";
            }
        }
        // Remove the last 'OR'
        $filtersql = chop($filtersql, ' AND ');
        // Sql for retrieving the data
        $sql = "SELECT u.*
                FROM {user} u
                JOIN ($enrolledsql) je ON je.id = u.id
                WHERE u.deleted = 0 AND $filtersql";
        // Sql for counting the total filtered users
        $countsql = "SELECT COUNT(DISTINCT u.id)
                FROM {user} u
                JOIN ($enrolledsql) je ON je.id = u.id
                WHERE u.deleted = 0 AND $filtersql";        
        $params = array_merge($params, $enrolledparams);
        $data = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
        $count = $DB->count_records_sql($countsql, $params);
        return array($data, $count);
    }

    /**
     * Retrieve string filters from URL then convert it to an array
     * @param array $currentfilters returns the current filters from submit if filter from url is null
     */
    public static function get_filters_from_url($currentfilters){
        $filter = optional_param('filter', null, PARAM_NOTAGS);
        if(!is_null($filter)){
            parse_str(htmlspecialchars_decode($filter), $filters);
            return $filters;
        }else{
            return $currentfilters;
        }
    }

    /**
     * Checks if activity is a child of the current selected category
     * @param mixed $activity Course module with gradeitem property
     * @return mixed category id or false
     */
    public static function is_child_activity($activity){
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        if(!is_null($categoryid) && isset($activity->gradeitem->parent_category)){
            $parent = $activity->gradeitem->parent_category->parent;
            return ($parent == $categoryid) ? $activity->gradeitem->categoryid: false ;
        }else{
            return false;
        }
    }

    /**
     * Retrieve grade items of the grade category
     * @param int $courseid 
     * @param grade_category $gradecategory
     * @return mixed $activity 
     */
    public static function get_category_gradeitem($courseid, $gradecategory){
        $activity = new stdClass();
        $activity->id = $gradecategory->id;
        $activity->course = $courseid;
        $activity->modname = 'category';
        $activity->instance = $gradecategory->id;
        $activity->parent = $gradecategory->parent;
        $activity->aggregation = $gradecategory->aggregation;
        $activity->droplow = $gradecategory->droplow;
        $activity->aggregateonlygraded = $gradecategory->aggregateonlygraded;
        $a = new stdClass();
        $a->category = $gradecategory->get_name();
        $activity->name =get_string('categorytotalfull', 'grades', $a);
        $gi = grade_item::fetch(array('courseid' => $courseid, 'itemtype' => 'category', 'iteminstance' => $gradecategory->id));
        $gi->load_parent_category();
        $activity->gradeitem = $gi;
        $activity->gradeitemid = $gi->id;
        return $activity;
    }

    /**
     * Delete gcat items in grade_items and grade_grades table
     * @param int $courseid 
     * @param mixed $activity
     */
    public static function delete_gcat_items($courseid, $activity){
        global $DB;
        $select = "courseid = $courseid AND ".self::compare_iteminfo();
        // Retrieve grade items ids first
        if($gradeitemids = $DB->get_records_select('grade_items', $select, ['iteminfo' => $activity->gradeitemid], '', 'id')){
            // Iterate the ids to create the select sql for grade_grades deletion
            $ggsql = '';
            foreach ($gradeitemids as $id) {
                $ggsql .= "itemid = $id->id OR ";
            }
            // Remove the last 'OR'
            $ggsql = chop($ggsql, ' OR ');
            // Delete records in grade_grades
            $DB->delete_records_select('grade_grades', $ggsql);
            // Delete records in grade_items
            $DB->delete_records_select('grade_items', $select, ['iteminfo' => $activity->gradeitemid]);
        }
    }

    /**
     * Update child components notes and timemodified
     * @param int $userid
     * @param int $itemid
     * @param string $status
     */
    public static function update_components_notes($userid, $itemid, $status){
        $subcatgrade = new grade_grade(array('userid' => $userid, 'itemid' => $itemid), true);
        $subcatgrade->feedback = $status;
        $subcatgrade->timemodified = time();
        //if subcat raw grade is null then it won't update
        !is_null($subcatgrade->rawgrade) ? $subcatgrade->update() : null;
    }

    /**
     * Get provisional grade item ids for each activity ids
     * @param int $courseid
     * @param mixed $activities
     * @return mixed prvgrditemids
     */
    public static function get_prvgrd_item_ids($courseid, $activities){
        global $DB;

        $itemname = get_string('provisionalgrd', 'local_gugcat');
        $iteminfo = '';
        $field = 'id';
        foreach($activities as $act){
            $iteminfo .= $DB->sql_compare_text('iteminfo').'='.$act->gradeitemid.' OR ';
        }
        //remove last or
        $iteminfo = chop($iteminfo, ' OR ');
        $select = "courseid=$courseid AND itemname='$itemname' AND ( $iteminfo )";
        return $DB->get_records_select('grade_items', $select, null, null, $field);
    }

    /**
     * get the gradeitemids of child activities
     * @param int $courseid
     * @param int $categoryid
     * @return mixed $activities
     */
    public static function get_child_activities_id($courseid, $categoryid){
        global $DB;
        
        $activities = array();
        $modules = array('assign', 'forum', 'quiz', 'workshop');//modules supported by gcat
        foreach($modules as $mod){
            $params = array($courseid, $categoryid, GRADE_TYPE_NONE, $mod, 0);
            $sql = "SELECT gi.id as gradeitemid, gi.gradetype as gradetype, gi.grademax as grademax
                    FROM {grade_items} gi, {course_modules} cm
                    WHERE gi.courseid = ? AND
                          gi.categoryid = ? AND
                          gi.itemtype = 'mod' AND
                          gi.gradetype != ? AND
                          gi.itemmodule = ? AND
                          cm.instance = gi.iteminstance AND
                          cm.deletioninprogress = ?";

            $result = $DB->get_records_sql($sql, $params);
            (sizeof($result) > 0) ? $activities = $activities + $result : null; 
        }

        foreach($activities as $key=>$activity){
            $gradetype = $activity->gradetype;
            $grademax = $activity->grademax;
            if($gradetype != GRADE_TYPE_VALUE && !local_gugcat::is_scheduleAscale($gradetype, $grademax)){
                unset($activities[$key]);
            }
        }

        return $activities;
    }

    /**
     * Returns rows of aggregated assessment grade history
     * @param int $courseid
     * @param int $userid
     * @param mixed $module
     * @param mixed $childacts
     */
    public static function get_aggregated_assessment_history($courseid, $userid, $module){
        global $DB;

        $i = 0;
        $rows = array();
        $notes = array('aggregation', 'grade', 'import');
        $subcatid = local_gugcat::get_grade_item_id($courseid, $module->gradeitem->iteminstance, get_string('subcategorygrade', 'local_gugcat'));
        $sort = 'id DESC';
        $fields = 'id, itemid, rawgrade, finalgrade, feedback, timemodified, usermodified';
        $select = 'feedback IS NOT NULL AND feedback <> "" AND rawgrade IS NOT NULL AND itemid='.$subcatid.' AND userid='.$userid;
        $gradehistory_arr = $DB->get_records_select('grade_grades_history', $select, null, $sort, $fields);
        if($gradehistory_arr > 0){
            foreach($gradehistory_arr as $gradehistory){
                isset($rows[$i]) ? null : $rows[$i] = new stdClass();
                isset($rows[$i]->grades) ? null : $rows[$i]->grades = array();
                $rows[$i]->timemodified = $gradehistory->timemodified;
                $rows[$i]->date = date("j/n", strtotime(userdate($gradehistory->timemodified))).'<br>'.date("h:i", strtotime(userdate($gradehistory->timemodified)));
                $rows[$i]->notes = $gradehistory->feedback;
                $fields = 'firstname, lastname';
                $modby = (!in_array($gradehistory->feedback, $notes)) ? $DB->get_record('user', array('id' => $gradehistory->usermodified), $fields) : null;
                $rows[$i]->modby = !is_null($modby) ? ((isset($modby->lastname) && isset($modby->firstname)) ? $modby->lastname . ', '.$modby->firstname : 'System Update') : 'System Update';
                $rows[$i]->notes = $gradehistory->feedback == 'aggregation' ? get_string('aggregation', 'local_gugcat') 
                : ($gradehistory->feedback == 'grade' ? get_string('grade', 'local_gugcat') 
                : ($gradehistory->feedback == 'import' ? get_string('import', 'local_gugcat') : $gradehistory->feedback));
                if($i+1 < sizeof($gradehistory_arr)){
                isset($rows[$i+1]) ? null : $rows[$i+1] = new stdClass();
                isset($rows[$i+1]->grade) ? null : $rows[$i+1]->grade = array();
                $rows[$i+1]->grade = !is_null($gradehistory->finalgrade) ? self::convert_grade($gradehistory->finalgrade) 
                : (!is_null($gradehistory->rawgrade) ? self::convert_grade($gradehistory->rawgrade) 
                : null); 
                }
                array_push($rows[$i]->grades, $gradehistory);
                $i++;
            }
        }
         //get the latest grade.
         if($grade = $DB->get_record('grade_grades', array('userid'=>$userid, 'itemid'=>$subcatid))){
            isset($rows[0]->grade) ? null : $rows[0]->grade = new stdClass();
            $rows[0]->grade = !is_null($grade->finalgrade) ? self::convert_grade($grade->finalgrade) 
            : (!is_null($grade->rawgrade) ? self::convert_grade($grade->rawgrade) 
            : null); 
        }
        $i = 0;
        $childacts = self::get_activities($courseid, $module->gradeitem->iteminstance);
        $prvgrades = local_gugcat::get_prvgrd_item_ids($courseid, $childacts);
        foreach($prvgrades as $prvgrd){
            $j = 0;
            $notes = array('aggregation', 'grade', 'import');
            $sort = 'id DESC';
            $fields = 'id, itemid, rawgrade, finalgrade, feedback';
            $select = 'feedback IS NOT NULL AND feedback <> "" AND rawgrade IS NOT NULL AND itemid='.$prvgrd->id.' AND userid='.$userid;
            $gradehistory_arr = $DB->get_records_select('grade_grades_history', $select, null, $sort, $fields);
            if(sizeof($gradehistory_arr) > 0){
                foreach($gradehistory_arr as $gradehistory){
                    if(isset($rows[$j])){
                        isset($rows[$j]->childgrades) ? null : $rows[$j]->childgrades = array();
                        $gradehistory->grade = !is_null($gradehistory->finalgrade) ? self::convert_grade($gradehistory->finalgrade) 
                        : (!is_null($gradehistory->rawgrade) ? self::convert_grade($gradehistory->rawgrade) 
                        : null); 
                        $rows[$j]->childgrades[$i] = $gradehistory;
                        $j++;
                    }
                }
            }
            $i++;
        }
        return $rows;
    }
}
