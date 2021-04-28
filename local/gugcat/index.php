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
use local_gugcat\grade_converter;

require_once(__DIR__ . '/../../config.php');
require_once('locallib.php');

$courseid = required_param('id', PARAM_INT);
$activityid = optional_param('activityid', null, PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$childactivityid = optional_param('childactivityid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);  

$URL = new moodle_url('/local/gugcat/index.php', array('id' => $courseid, 'page' => $page));
is_null($activityid) ? null : $URL->param('activityid', $activityid);
is_null($categoryid) && $categoryid == 0 ? null : $URL->param('categoryid', $categoryid);
is_null($childactivityid) ? null : $URL->param('childactivityid', $childactivityid);
require_login($courseid);
$PAGE->navbar->add(get_string('navname', 'local_gugcat'));
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
$totalactivities = array();
$childactivities = array();
$selectedmodule = null;
$groupingid = 0;
$valid_import_activity = false;
$is_converted = false;
if(!is_null($categoryid) && $categoryid != 0){
    // Retrieve sub categories
    $gcs = grade_category::fetch_all(array('courseid' => $courseid, 'parent' => $categoryid));

    $gradecatgi = array();
    if(!empty($gcs)){
        foreach ($gcs as $gc){
            $gi = local_gugcat::get_category_gradeitem($courseid, $gc);
            $gi->name = preg_replace('/\b total/i', '', $gi->name);
            $gradecatgi[$gi->gradeitemid] = $gi; 
            $gradecatgi[$gi->gradeitemid]->selected = (strval($activityid) === $gi->gradeitemid)? 'selected' : '';
        }
        //merging two arrays without changing their index.
        $totalactivities = $activities + $gradecatgi;
    }
    
    //if activityid is null and there are no assessments
    if(is_null($activityid) && empty($activities) && !empty($gradecatgi)){
        $mods = array_reverse($totalactivities);
        $activity = array_pop($mods);
        $activityid = $activity->gradeitemid;
        $URL->param('activityid', $activityid);
    }

    $childactivities = (isset($totalactivities[$activityid]->modname) && $totalactivities[$activityid]->modname === 'category') ? local_gugcat::get_activities($courseid, $totalactivities[$activityid]->id) : array();
    if(!empty($childactivities)){
        foreach($childactivities as $ca){
            $ca->selected = (strval($childactivityid) === $ca->gradeitemid)? 'selected' : '';
        }
    }
}

if(!empty($totalactivities) || !empty($activities)){
    
    $mods = array_reverse($activities);
    $childmods = empty($childactivities) ?  null : array_reverse($childactivities);
    $selectedmodule = is_null($childmods) ? (is_null($activityid) ? array_pop($mods) : (!empty($activities) ? $activities[$activityid] : null)) : (is_null($childactivityid) ? array_pop($childmods) : $childactivities[$childactivityid]);

    if(isset($selectedmodule)){
        $groupingid = $selectedmodule->groupingid;

        $scaleid = $selectedmodule->gradeitem->scaleid;
        $gradetype = $selectedmodule->gradeitem->gradetype;
        $grademax = $selectedmodule->gradeitem->grademax;
        $grademin = $selectedmodule->gradeitem->grademin;
        $valid_import_activity = is_null($scaleid) ? local_gugcat::is_validgradepoint($gradetype, $grademin) : local_gugcat::is_scheduleAscale($gradetype, $grademax);
        //if $activities is empty, and activity id parameter is also null add $activityid into $selectmodule
        empty($activities) ? $selectedmodule->activityid = $activityid : null;
        //Populate static $GRADES scales
        if($is_converted = $selectedmodule->is_converted){
            local_gugcat::set_grade_scale(null, $is_converted);
        }else{
            local_gugcat::set_grade_scale($scaleid);
        }
    }
}

//Retrieve students
$limitfrom = $page * GCAT_MAX_USERS_PER_PAGE;
$limitnum  = GCAT_MAX_USERS_PER_PAGE;

// Params from search bar filters
$filters = optional_param_array('filters', [], PARAM_NOTAGS);
$filters = local_gugcat::get_filters_from_url($filters);
$activesearch = isset($filters) && count($filters) > 0 && count(array_filter($filters)) > 0 ? true : false;
$activesearch ? $URL->param('filter', http_build_query($filters)) : null;
$PAGE->set_url($URL);

if($groupingid != 0){
    $students = Array();
    $totalenrolled = 0;
    //Retrieve groups
    $groups = groups_get_all_groups($course->id, 0, $groupingid);
    if(!empty($groups)){
        foreach ($groups as $group) {
            if($activesearch){
                list($groupstudents, $count) = local_gugcat::get_filtered_students($coursecontext, $filters, $group->id, $limitfrom, $limitnum);
            }else{
                $count = count_enrolled_users($coursecontext, 'local/gugcat:gradable', $group->id);
                $groupstudents = get_enrolled_users($coursecontext, 'local/gugcat:gradable', $group->id, 'u.*', null, $limitfrom, $limitnum);
            }
            $totalenrolled += $count;
            $students += $groupstudents;
        }
    }
}else{
    if($activesearch){
        list($students, $totalenrolled) = local_gugcat::get_filtered_students($coursecontext, $filters, 0, $limitfrom, $limitnum);
    }else{
        $totalenrolled = count_enrolled_users($coursecontext, 'local/gugcat:gradable');
        $students = get_enrolled_users($coursecontext, 'local/gugcat:gradable', 0, 'u.*', null, $limitfrom, $limitnum);
    }
}

// Go back to first page when new search filters were submitted
$filters = optional_param_array('filters', [], PARAM_NOTAGS);
if(count($filters) > 0 && $page > 0){
    $URL->remove_params('page');
    redirect($URL);
}
//Populate static $STUDENTS
local_gugcat::$STUDENTS = $students;
//Populate static provisional grade id
local_gugcat::set_prv_grade_id($courseid, $selectedmodule);

//---------submit grade capture table
$release = optional_param('release', null, PARAM_NOTAGS);
$multiadd = optional_param('multiadd', null, PARAM_NOTAGS);
$gradeitem = optional_param('reason', null, PARAM_NOTAGS);
$importgrades = optional_param('importgrades', null, PARAM_NOTAGS);
$showhidegrade = optional_param('showhidegrade', null, PARAM_NOTAGS);
$rowstudentid = optional_param('studentid', null, PARAM_NOTAGS);
$newgrades = optional_param_array('newgrades', null, PARAM_NOTAGS);
$bulkimport = optional_param('bulkimport', null, PARAM_NOTAGS);

//params for logs
$eventcontext = $coursecontext;
if (!is_null($selectedmodule) && isset($selectedmodule->id)) {
    $eventcontext = \context_module::instance($selectedmodule->id);
}
$params = array(
    'context' => $eventcontext,
    'other' => array(
        'courseid' => $courseid,
        'activityid' => $activityid,
        'categoryid' => $categoryid,
        'page' => $page
    )
);

// Process release provisional grades
if (isset($release)){
    grade_capture::release_prv_grade($courseid, $selectedmodule);
    local_gugcat::notify_success('successrelease');
    //log of release grades
    $event = \local_gugcat\event\release_prv_grade::create($params);
    $event->trigger();
    unset($release);
    redirect($URL);
    exit;

// Process multiple add grades to the students
}else if (isset($multiadd)){
    if(isset($newgrades) && !empty($gradeitem)){
        $gradeitemid = local_gugcat::add_grade_item($courseid, $gradeitem, $selectedmodule);
        foreach ($newgrades as $id=>$item) {
            if(isset($item) && !empty($item)){
                $grade = !is_numeric($item) ? array_search(strtoupper($item), local_gugcat::$GRADES) : $item; 
                local_gugcat::add_update_grades($id, $gradeitemid, $grade, '');
                if($is_converted){
                    // If conversion is enabled, save the converted grade to provisional grade and original grade to converted grade.
                    $conversion = grade_converter::retrieve_grade_conversion($selectedmodule->gradeitemid);
                    $cg = grade_converter::convert($conversion, $grade);
                    local_gugcat::update_grade($id, local_gugcat::$PRVGRADEID, $cg, '');
                    $convertedgi = local_gugcat::get_grade_item_id($COURSE->id, $selectedmodule->gradeitemid, get_string('convertedgrade', 'local_gugcat'));
                    local_gugcat::update_grade($id, $convertedgi, $grade, '');
                }
                //check if child activities are existing
                if(!empty($childactivities)){
                    $subcatid = local_gugcat::get_grade_item_id($courseid, $selectedmodule->gradeitem->categoryid, get_string('subcategorygrade', 'local_gugcat'));
                    $scale = $totalactivities[$activityid]->is_converted;
                    $fields = 'itemid, id, rawgrade, finalgrade, overridden';
                    // Get provisional grades
                    $grade = $DB->get_record('grade_grades', array('itemid' => $subcatid, 'userid'=>$id), $fields);
                    $grd = !is_null($grade->finalgrade) ? $grade->finalgrade 
                    : (!is_null($grade->rawgrade) ? $grade->rawgrade 
                    : null);
                    //if subcat has a grade and it is not overridden.
                    if(isset($grd) && !is_null($grd) && $grade->overridden == 0){
                        $notes = ($scale) ? 'grade -'.$scale : 'grade';
                        local_gugcat::update_components_notes($id, $subcatid, $notes);
                        $prvgrds = local_gugcat::get_prvgrd_item_ids($courseid, $childactivities);
                        foreach($prvgrds as $prvgrd){
                            local_gugcat::update_components_notes($id, $prvgrd->id, $notes);
                        }
                    }
                }
            }
        }
        local_gugcat::notify_success('successaddall');
        //log of add multiple grades
        $event = \local_gugcat\event\add_multiple_grades::create($params);
        $event->trigger();
    }else{
        local_gugcat::notify_error('errorrequired');
    }
    unset($multiadd);
    unset($gradeitem);
    unset($newgrades);
    redirect($URL);
    exit;

// Process single import grades
}else if(isset($importgrades)){
    if ($valid_import_activity){
        if(!empty($childactivities)){
            $scale = $totalactivities[$activityid]->is_converted;
            grade_capture::import_from_gradebook($courseid, $selectedmodule, $totalactivities);
            $subcatid = local_gugcat::get_grade_item_id($courseid, $selectedmodule->gradeitem->categoryid, get_string('subcategorygrade', 'local_gugcat'));
            foreach($students as $student){
                $fields = 'itemid, id, rawgrade, finalgrade, overridden';
                // Get provisional grades
                $grade = $DB->get_record('grade_grades', array('itemid' => $subcatid, 'userid'=>$student->id), $fields);
                $grd = !is_null($grade->finalgrade) ? $grade->finalgrade 
                : (!is_null($grade->rawgrade) ? $grade->rawgrade 
                : null);
                //if subcat has a grade and it is not overridden.
                if(isset($grd) && !is_null($grd) && $grade->overridden == 0){
                    $notes = ($scale) ? 'import -'.$scale : 'import';
                    local_gugcat::update_components_notes($student->id, $subcatid, $notes);
                    $prvgrds = local_gugcat::get_prvgrd_item_ids($courseid, $childactivities);
                    foreach($prvgrds as $prvgrd){
                        local_gugcat::update_components_notes($student->id, $prvgrd->id, $notes);
                    }
                }
            }
        }else{
            grade_capture::import_from_gradebook($courseid, $selectedmodule,  empty($totalactivities) ? $activities : $totalactivities);
        }
        local_gugcat::notify_success('successimport');
        $event = \local_gugcat\event\import_grade::create($params);
        $event->trigger();
    }else{
        local_gugcat::notify_error('importerror');
    }
    unset($importgrades);
    redirect($URL);
    exit;

// Process show/hide grade from the student
}else if(isset($showhidegrade) && !empty($rowstudentid)){
    $status = grade_capture::hideshowgrade($rowstudentid);
    //log of hide show grade
    $hideshowparam = array (
        'context' => \context_module::instance($selectedmodule->id),
        'other' => array(
            'courseid' => $courseid,
            'activityid' => $activityid,
            'categoryid' => $categoryid,
            'status' => $status,
            'idnumber' => $students[$rowstudentid]->idnumber,
            'page' => $page
        )
    );
    $event = \local_gugcat\event\hide_show_grade::create($hideshowparam);
    $event->trigger();
    unset($showhidegrade);
    unset($rowstudentid);
    redirect($URL);
    exit;
// Process bulk import of components
}else if(isset($bulkimport)){
    $importerror = array();
    foreach ($childactivities as $activity) {
        $scaleid = $activity->gradeitem->scaleid;
        $gradetype = $activity->gradeitem->gradetype;
        $grademax = $activity->gradeitem->grademax;
        $grademin = $activity->gradeitem->grademin;
        $invalid_import_activity = (is_null($scaleid) ? !local_gugcat::is_validgradepoint($gradetype, $grademin)
                                                      : !local_gugcat::is_scheduleAscale($gradetype, $grademax));

        if($invalid_import_activity){
            $importerror[] = $activity->gradeitemid;
        }
        // Stop the iteration if importerror is not empty
        if(!empty($importerror)){
            break;
        }
    }
    if(!empty($importerror)){
        local_gugcat::notify_error('bulkimporterror');
    }else{
        $scale = $totalactivities[$activityid]->is_converted;
        // Proceed with bulk import
        grade_capture::import_from_gradebook($courseid, $childactivities, $totalactivities);
        $subcatid = local_gugcat::get_grade_item_id($courseid, $selectedmodule->gradeitem->categoryid, get_string('subcategorygrade', 'local_gugcat'));
        //update notes for grade history
        foreach($students as $student){
            $fields = 'itemid, id, rawgrade, finalgrade, overridden';
            // Get provisional grades
            $grade = $DB->get_record('grade_grades', array('itemid' => $subcatid, 'userid'=>$student->id), $fields);
            $grd = !is_null($grade->finalgrade) ? $grade->finalgrade 
            : (!is_null($grade->rawgrade) ? $grade->rawgrade 
            : null);
            //if subcat has a grade and it is not overridden.
            if(isset($grd) && !is_null($grd) && $grade->overridden == 0){
                $notes = ($scale) ? 'import -'.$scale : 'import';
                local_gugcat::update_components_notes($student->id, $subcatid, $notes);
                $prvgrds = local_gugcat::get_prvgrd_item_ids($courseid, $childactivities);
                foreach($prvgrds as $prvgrd){
                    local_gugcat::update_components_notes($student->id, $prvgrd->id, $notes);
                }
            }
        }
        local_gugcat::notify_success('successimport');
        //log of bulk import
        $params = array(
            'context' => context_course::instance($courseid),
            'other' => array(
                'courseid' => $courseid,
                'activityid' => $activity->id,
                'categoryid' => $categoryid,
                'categoryname' => $activity->gradeitem->itemname,
                'page'=> $page
            )
        );
        $event = \local_gugcat\event\bulk_import::create($params);
        $event->trigger();
    }
    unset($bulkimport);
    redirect($URL);
    exit;
}

$rows = grade_capture::get_rows($course, $selectedmodule, $students);
$columns = grade_capture::get_columns();

//log of grade capture view
$event = \local_gugcat\event\grade_capture_viewed::create($params);
$event->trigger();

echo $OUTPUT->header();
if(!is_null($selectedmodule)){
    $PAGE->set_cm($selectedmodule);
}
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_grade_capture($selectedmodule, empty($totalactivities) ? $activities : $totalactivities, $childactivities, $rows, $columns);
echo $OUTPUT->paging_bar($totalenrolled, $page, $limitnum, $PAGE->url);
echo $OUTPUT->footer();
