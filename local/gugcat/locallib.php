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

use local_gugcat\grade_aggregation;

require_once($CFG->libdir . '/adminlib.php');

defined('MOODLE_INTERNAL') || die();

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
     * @param boolean $all - If true, retrieves all modules regardless of category
     * @param boolean $includegradeitem - If true, retrieves grade item of the module
     */
    public static function get_activities($courseid, $all = false, $includegradeitem = true){
        $activityid = optional_param('activityid', null, PARAM_INT);
        $categoryid = $all ? null : optional_param('categoryid', null, PARAM_INT);
        $mods = self::grade_get_gradable_activities($courseid);
    
        //get whole grading forums and workshop assessment | itemnumber = 1
        $wholegradingforums = self::grade_get_gradable_activities($courseid, 'forum', 1);
        $assessmentworkshops = self::grade_get_gradable_activities($courseid, 'workshop', 1);

        $activities = array();
        if(count($mods) > 0 || count($wholegradingforums) > 0 || count($assessmentworkshops) > 0){
            $mods = array_merge(array_values($mods), $wholegradingforums + $assessmentworkshops);
            foreach($mods as $cm) {
                $activities[$cm->gradeitemid]= $cm;
                $activities[$cm->gradeitemid]->selected = (strval($activityid) === $cm->gradeitemid)? 'selected' : '';
                if($includegradeitem){
                    $activities[$cm->gradeitemid]->gradeitem = new grade_item(array('id'=>$cm->gradeitemid), true);
                }
            }
            if(!is_null($categoryid) && $categoryid !== 0){
                foreach ($activities as $key=>$activity) {
                    if ( $activity->gradeitem->categoryid !== strval($categoryid)) {
                        unset($activities[$key]);
                    }
                }
            }
            else{
                if($includegradeitem && !$all){
                    $categories = self::get_grade_categories($courseid);
                    foreach ($activities as $key=>$activity){
                        if($categories[$activity->gradeitem->categoryid]->key != "null"){
                            unset($activities[$key]);
                        }
                    }
                }
            }

            //remove gradeitems which do not fall within 22-point scale.
            if($includegradeitem){
                foreach($activities as $key=>$activity){    
                    $scaleid = $activity->gradeitem->scaleid;
                    $gradetype = $activity->gradeitem->gradetype;
                    $grademax = $activity->gradeitem->grademax;

                    $valid_22point_scale = is_null($scaleid) ? local_gugcat::is_grademax22($gradetype, $grademax) : local_gugcat::is_scheduleAscale($gradetype, $grademax);
                    if(!$valid_22point_scale)
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
        $pgrd_str = get_string('provisionalgrd', 'local_gugcat');
        self::$PRVGRADEID = self::get_grade_item_id($courseid, $mod->gradeitemid, $pgrd_str);
        return self::$PRVGRADEID;
    }

    /**
     * Returns the grade item id based from the passed parameters
     * @param int $courseid
     * @param int $modid Selected course module grade item ID
     * @param string $itemname Item name can be Provisional Grade or Aggregated Grade
     */
    public static function get_grade_item_id($courseid, $modid, $itemname){
        global $DB;
        $select = 'courseid = :courseid AND itemname = :itemname ';
        $params = [
            'courseid' => $courseid,
            'itemname' => $itemname
        ];
        if(!is_null($modid)){
            $select .= 'AND '.self::compare_iteminfo();
            $params['iteminfo'] = $modid; // modid = gradeitemid
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
        $students = is_null($students_) ? self::$STUDENTS : $students_;
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
            ? self::update_grade($userid, self::$PRVGRADEID, $grade) 
            : false);
            
        }else{
            //updates grade objects in database
            $grade_->timemodified = time();
            //if update successful - update provisional grade
            if($grade_->update()){
                //update timemodified of grade item
                $DB->set_field('grade_items', 'timemodified', $grade_->timemodified, array('id' => $itemid));
                return self::update_grade($userid, self::$PRVGRADEID, $grade);
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
    public static function convert_grade($grade){
        $scale = self::$GRADES + grade_aggregation::$AGGRADE;

        //add admin grades in scale
        $scale[NON_SUBMISSION] = NON_SUBMISSION_AC;
        
        $final_grade = intval($grade);
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
     */
    public static function set_grade_scale($scaleid){
        global $DB;
        $scalegrades = array();
        if(is_null($scaleid)){
            $scalegrades = self::get_gcat_scale();
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
            $scale = isset($obj) ? $obj->schedule_A : [];
            return array_reverse(array_filter(array_merge(array(0), $scale)),true);//starts 1 => H
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
    public static function notify_error($stridentifier){
        $message = get_string($stridentifier, 'local_gugcat');
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

    /**
     * Returns boolean if grade is admin grade 
     * @param int $grade 
     */
    public static function is_admin_grade($grade){
        switch ($grade) {
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
                $grd->notes = !is_null($grd->feedback) ? $grd->feedback : 'N/A - '.$gradeitem->itemname;
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
     * @param string $modulename 
     * @param int $itemnumber 
     */
    private static function grade_get_gradable_activities($courseid, $modulename='', $itemnumber = 0) {
        global $DB;
        if (empty($modulename)) {
            $modules = array('assign', 'forum', 'quiz', 'workshop');//modules supported by gcat
            $result = array();
            foreach ($modules as $module) {
                if ($cms = self::grade_get_gradable_activities($courseid, $module, $itemnumber)) {
                    $result =  $result + $cms;
                }
            }
            return $result;
        }
        $params = array($courseid, $modulename, $itemnumber, GRADE_TYPE_NONE, $modulename);
        $sql = "SELECT cm.*, gi.itemname as name, md.name as modname, gi.id as gradeitemid
                  FROM {grade_items} gi, {course_modules} cm, {modules} md, {{$modulename}} m
                 WHERE gi.courseid = ? AND
                       gi.itemtype = 'mod' AND
                       gi.itemmodule = ? AND
                       gi.itemnumber = ? AND
                       gi.gradetype != ? AND
                       gi.iteminstance = cm.instance AND
                       cm.instance = m.id AND
                       md.name = ? AND
                       md.id = cm.module";
    
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
                $category = \core_customfield\category_controller::create($customfieldcategoryid);
                $field = \core_customfield\field_controller::create(0, (object)[
                    'type' => 'checkbox',
                    'configdata' => get_string('configdata', 'local_gugcat')
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
                return 1;
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
}
