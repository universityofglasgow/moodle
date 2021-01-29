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
 * Index file.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_gugcat\grade_capture;

require_once(__DIR__ . '/../../config.php');
require_once('locallib.php');

$courseid = required_param('id', PARAM_INT);
$activityid = optional_param('activityid', null, PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);  

$URL = new moodle_url('/local/gugcat/index.php', array('id' => $courseid, 'page' => $page));
is_null($activityid) ? null : $URL->param('activityid', $activityid);
is_null($categoryid) ? null : $URL->param('categoryid', $categoryid);
require_login($courseid);
$PAGE->set_url($URL);
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));

$PAGE->requires->css('/local/gugcat/styles/gugcat.css');
$PAGE->requires->js_call_amd('local_gugcat/main', 'init');

$course = get_course($courseid);

$coursecontext = context_course::instance($courseid);
require_capability('local/gugcat:view', $coursecontext);

$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);

//Retrieve activities
$activities = local_gugcat::get_activities($courseid);
$selectedmodule = null;
$groupingid = 0;
$valid_22point_scale = false;

if(!empty($activities)){
    $mods = array_reverse($activities);
    $selectedmodule = is_null($activityid) ? array_pop($mods) : $activities[$activityid];
    $groupingid = $selectedmodule->groupingid;

    $scaleid = $selectedmodule->gradeitem->scaleid;
    $gradetype = $selectedmodule->gradeitem->gradetype;
    $grademax = $selectedmodule->gradeitem->grademax;

    $valid_22point_scale = is_null($scaleid) ? local_gugcat::is_grademax22($gradetype, $grademax) : local_gugcat::is_scheduleAscale($gradetype, $grademax);

    //populate $GRADES with scales
    local_gugcat::set_grade_scale($scaleid);
}
//Retrieve groups
$groups = groups_get_all_groups($course->id, 0, $groupingid);

//Retrieve students
$limitfrom = $page * GCAT_MAX_USERS_PER_PAGE;
$limitnum  = GCAT_MAX_USERS_PER_PAGE;
$totalenrolled = count_enrolled_users($coursecontext, 'moodle/competency:coursecompetencygradable');

if(!empty($groups)){
    $students = Array();
    foreach ($groups as $group) {
        $groupstudents = get_enrolled_users($coursecontext, 'moodle/competency:coursecompetencygradable', $group->id, 'u.*', null, $limitfrom, $limitnum);
        $students += $groupstudents;
    }
}else{
    $students = get_enrolled_users($coursecontext, 'moodle/competency:coursecompetencygradable', 0, 'u.*', null, $limitfrom, $limitnum);
}

if(!is_null($courseid) && !is_null($categoryid)){
    $PAGE->navbar->add(get_string('navname', 'local_gugcat'), $URL);
}

//populate $STUDENTS
local_gugcat::$STUDENTS = $students;
//populate provisional grade id and set it to static
local_gugcat::set_prv_grade_id($courseid, $selectedmodule);

//---------submit grade capture table
$release = optional_param('release', null, PARAM_NOTAGS);
$multiadd = optional_param('multiadd', null, PARAM_NOTAGS);
$gradeitem = optional_param('reason', null, PARAM_NOTAGS);
$importgrades = optional_param('importgrades', null, PARAM_NOTAGS);
$showhidegrade = optional_param('showhidegrade', null, PARAM_NOTAGS);
$rowstudentid = optional_param('rowstudentno', null, PARAM_NOTAGS);
$newgrades = optional_param_array('newgrades', null, PARAM_NOTAGS);
if (isset($release)){
    grade_capture::release_prv_grade($courseid, $selectedmodule);
    local_gugcat::notify_success('successrelease');
    unset($release);
    redirect($URL);
    exit;
}else if (isset($multiadd)){
    if(isset($newgrades) && !empty($gradeitem)){
        $gradeitemid = local_gugcat::add_grade_item($courseid, $gradeitem, $selectedmodule);
        foreach ($newgrades as $id=>$item) {
            if(isset($item)){
                $grade = array_search($item, local_gugcat::$GRADES);
                local_gugcat::add_update_grades($id, $gradeitemid, $grade);
            }
        }
        local_gugcat::notify_success('successaddall');
    }else{
        local_gugcat::notify_error('errorrequired');
    }
    unset($multiadd);
    unset($gradeitem);
    unset($newgrades);
    redirect($URL);
    exit;
}else if(isset($importgrades)){
    if ($valid_22point_scale){
        grade_capture::import_from_gradebook($courseid, $selectedmodule, $activities);
        local_gugcat::notify_success('successimport');
    }else{
        local_gugcat::notify_error('importerror');
    }
    unset($importgrades);
    redirect($URL);
    exit;
}else if(isset($showhidegrade) && !empty($rowstudentid)){
    grade_capture::hideshowgrade($rowstudentid);
    unset($showhidegrade);
    unset($rowstudentid);
    redirect($URL);
    exit;
}
$rows = grade_capture::get_rows($course, $selectedmodule, $students);
$columns = grade_capture::get_columns();

echo $OUTPUT->header();
if(!empty($activities))
    $PAGE->set_cm($selectedmodule);
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_grade_capture($selectedmodule, $activities, $rows, $columns);
echo $OUTPUT->paging_bar($totalenrolled, $page, $limitnum, $PAGE->url);
echo $OUTPUT->footer();
