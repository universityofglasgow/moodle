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

class local_gugcat {

    /**
     * Returns all activities/modules for specific course
     *
     * @param int $courseid
     * @param int $activityid
     */
    public static function get_activities($courseid, $activityid){
        global $modules;
        $modinfo = get_fast_modinfo($courseid);
        $mods = $modinfo->get_cms();
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
        $courseiddb = $DB->sql_compare_text('courseid') . ' = ' . $DB->sql_compare_text(':courseid');
        $iteminfodb = $DB->sql_compare_text('iteminfo') . ' = '  . $DB->sql_compare_text(':iteminfo');
        $gradeitems = $DB->get_records_select('grade_items', $courseiddb . ' AND ' . $iteminfodb, [
            'courseid' => $course->id,
            'iteminfo' => $module->id,
        ]);
        $sort = 'id';
        $fields = 'userid, id, finalgrade, timemodified';
        foreach($gradeitems as $item) {
            $item->grades = $DB->get_records('grade_grades', array('itemid' => $item->id), $sort, $fields);
        }
        
        return $gradeitems;
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
        global $gradeitems;
        $grading_info = grade_get_grades($course->id, 'mod', $module->modname, $module->instance, array_keys($students));
        $gradeitems = self::get_grade_items($course, $module);
        $i = 1;
        foreach ($students as $student) {
            $firstgrade = $grading_info->items[0]->grades[$student->id]->grade;
            $gradecaptureitem = new grade_capture_item();
            $gradecaptureitem->cnum = $i;
            $gradecaptureitem->studentno = $student->id;
            $gradecaptureitem->surname = $student->lastname;
            $gradecaptureitem->forename = $student->firstname;
            $gradecaptureitem->firstgrade = $firstgrade;
            $gradecaptureitem->provisionalgrade = $firstgrade;
    
            if(!empty($gradeitems)){
                $gradecaptureitem->grades = array();
                foreach ($gradeitems as $item) {
                    $rawgrade = ( $item->grades[$student->id]->finalgrade);
                    $grade = is_null($rawgrade) ? 'N/A' : $rawgrade;
                    array_push($gradecaptureitem->grades, (object)['grade' => $grade]);
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
     * @param mixed $course
     * @param mixed $module
     * @param mixed $students
     */
    public static function get_columns(){
        $columns = array();
        $columns = [
             '1st Grade'
        ];
        global $gradeitems;
        foreach ($gradeitems as $item) {
            array_push($columns, $item->itemname);        
        }
        return $columns;
    }

}
