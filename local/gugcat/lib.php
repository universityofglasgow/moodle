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
 * Version file.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_gugcat_extend_navigation_course($parentnode, $course, $context) {
    $url = new moodle_url('/local/gugcat/index.php');
    $gugcat = get_string('navname', 'local_gugcat');
    $icon = new pix_icon('my-media', '', 'local_mymedia');
    $main_node = $parentnode->add($gugcat, $url, navigation_node::TYPE_CONTAINER, $gugcat, 'gugcat', $icon);
}

function local_gugcat_extend_navigation($navigation){
    $url = new moodle_url('/local/gugcat/index.php');
    $gugcat = get_string('navname', 'local_gugcat');
    $icon = new pix_icon('my-media', '', 'local_mymedia');
    $main_node = $navigation->add($gugcat, $url, navigation_node::TYPE_CONTAINER, $gugcat, 'gugcat', $icon);
    $main_node->showinflatnavigation = true;
}

function get_activities($courseid, $activityid){
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

function get_grade_items($course, $module){
    global $DB;
    $gradeitems = $DB->get_records('grade_items', 
    array('courseid' => $course->id, 'iteminfo' => $module->id),
    'timecreated');
    $sort = 'id';
    $fields = 'userid, id, finalgrade, timemodified';
    foreach($gradeitems as $item) {
        $item->grades = $DB->get_records('grade_grades', array('itemid' => $item->id), $sort, $fields);
    }
    
    return $gradeitems;
}

function get_rows($course, $module, $students){
    $captureitems = array();
    global $gradeitems;
    $grading_info = grade_get_grades($course->id, 'mod', $module->modname, $module->instance, array_keys($students));
    $gradeitems = get_grade_items($course, $module);
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

function get_columns(){
    $columns = array();
    $columns = [
         'Candidate no.',
         'Student no.',
         'Surname',
         'Forename',
         '1st Grade'
    ];
    global $gradeitems;
    foreach ($gradeitems as $item) {
        array_push($columns, $item->itemname);        
    }
    return $columns;
}
