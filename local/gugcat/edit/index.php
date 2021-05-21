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
 * Index file for edit grade.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_gugcat\grade_converter;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
require_once($CFG->dirroot.'/local/gugcat/classes/form/addeditgradeform.php');
require_once($CFG->libdir.'/filelib.php');

$courseid = required_param('id', PARAM_INT);
$activityid = required_param('activityid', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$overview = required_param('overview', PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$childactivityid = optional_param('childactivityid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);  

require_login($courseid);
$urlparams = array('id' => $courseid, 'activityid' => $activityid, 'studentid' => $studentid, 'overview' => $overview, 'page' => $page);
$URL = new moodle_url('/local/gugcat/edit/index.php', $urlparams);
(!is_null($categoryid) && $categoryid != 0) ? $URL->param('categoryid', $categoryid) : null;
$indexurl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));

$modid = $activityid;
if(!is_null($childactivityid) && $childactivityid != 0){
    $URL->param('childactivityid', $childactivityid);
    $indexurl->param('childactivityid', $childactivityid);
    $modid = $childactivityid;
}

$PAGE->set_url($URL);
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));
$PAGE->navbar->add(get_string('navname', 'local_gugcat'), $indexurl);

$PAGE->requires->css('/local/gugcat/styles/gugcat.css');
$PAGE->requires->js_call_amd('local_gugcat/main', 'init');

$course = get_course($courseid);

$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);
require_capability('local/gugcat:view', $coursecontext);

$student = $DB->get_record('user', array('id'=>$studentid, 'deleted'=>0), '*', MUST_EXIST);
$module = local_gugcat::get_activity($courseid, $modid);
$scaleid = $module->gradeitem->scaleid;

if (is_null($scaleid) && local_gugcat::is_grademax22($module->gradeitem->gradetype, $module->gradeitem->grademax)){
    $scaleid = null;
}
if($is_converted = $module->is_converted){
    local_gugcat::set_grade_scale(null, $is_converted);
}else{
    local_gugcat::set_grade_scale($scaleid);
}
local_gugcat::set_prv_grade_id($courseid, $module);
$grading_info = grade_get_grades($courseid, 'mod', $module->modname, $module->instance, $studentid);
$gradeitems = local_gugcat::get_grade_grade_items($course, $module);
// Get converted grade item and remove it from the gradeitems array
foreach($gradeitems as $i=>$gi){
    if($gi->itemname == get_string('convertedgrade', 'local_gugcat')){
        unset($gradeitems[$i]);
        break;
    }
}
$gradeversions = local_gugcat::filter_grade_version($gradeitems, $studentid);

$mform = new addeditgradeform(null, array('id'=>$courseid, 'activity'=>$module, 'studentid'=>$studentid, 'overview' => $overview));
if ($fromform = $mform->get_data()) {
    $gradereason = ($fromform->reasons == 9) ? $fromform->otherreason : local_gugcat::get_reasons()[$fromform->reasons];
    $grade = !is_numeric($fromform->grade) ? array_search(strtoupper($fromform->grade), local_gugcat::$grades) : $fromform->grade; 
    $gradeitemid = local_gugcat::add_grade_item($courseid, $gradereason, $module);
    $notes = ",_gradeitem: $gradereason ,_notes: $fromform->notes";
    $grades = local_gugcat::add_update_grades($studentid, $gradeitemid, $grade, (!$is_converted ? $notes : ''));
    if($is_converted){
        $notes .= " ,_scale: $is_converted";
        // If conversion is enabled, save the converted grade to provisional grade and original grade to converted grade.
        $conversion = grade_converter::retrieve_grade_conversion($modid);
        $cg = grade_converter::convert($conversion, $grade);
        local_gugcat::update_grade($studentid, local_gugcat::$prvgradeid, $cg, $notes);
        $convertedgi = local_gugcat::get_grade_item_id($COURSE->id, $modid, get_string('convertedgrade', 'local_gugcat'));
        local_gugcat::update_grade($studentid, $convertedgi, $grade);
    }
    $url = null;

    //log of ammend grades
    $params = array(
        'context' => \context_module::instance($module->id),
        'other' => array(
            'courseid' => $courseid,
            'activityid' => $modid,
            'categoryid' => $categoryid,
            'studentno' => $studentid,
            'idnumber' => $student->idnumber,
            'grade' => local_gugcat::convert_grade($fromform->grade),
            'gradeitem' => $gradereason,
            'page'=> $page,
            'overview' => $overview
        )
    );
    $event = \local_gugcat\event\ammend_grade::create($params);
    $event->trigger();
    //check if activity is a subcat component.'
    if($module->gradeitem->parent_category->parent === strval($categoryid)){
        // Get Subcategory prv grade item id and idnumber
        $prvgrd = local_gugcat::get_gradeitem_converted_flag($module->gradeitem->categoryid, true);
        $subcatid = $prvgrd->id;
        $scale = $prvgrd->idnumber ? $prvgrd->idnumber : $DB->get_field('grade_items', 'outcomeid', array('id'=>$subcatid));
        // Get provisional grades
        $fields = 'itemid, id, rawgrade, finalgrade, overridden';
        $grade = $DB->get_record('grade_grades', array('itemid' => $subcatid, 'userid'=>$studentid), $fields);
        $notes = $scale && !empty($scale) ? 'grade -'.$scale : 'grade';
        $componentnotes = 'grade';
        $grd = !is_null($grade->finalgrade) ? $grade->finalgrade 
        : (!is_null($grade->rawgrade) ? $grade->rawgrade 
        : null);  
        //check if subcategory has an existing grade.
        if(!is_null($grd) && $grade->overridden == 0){
            $DB->set_field('grade_grades', 'feedback', $notes, array('id'=>$grade->id));
        }
    }
    if((integer)$fromform->overview == 1){
        $url = new moodle_url('/local/gugcat/overview/index.php', array('id' => $courseid, 'page'=> $page));
        (!is_null($categoryid) && $categoryid != 0) ? $url->param('categoryid', $categoryid) : null;
        redirect($url);
    }else{
        $url = new moodle_url('/local/gugcat/index.php', array('id' => $courseid, 'activityid' => $activityid, 'page'=> $page));
        (!is_null($categoryid) && $categoryid != 0) ? $url->param('categoryid', $categoryid) : null;
        (!is_null($childactivityid) && $childactivityid != 0) ? $url->param('childactivityid', $childactivityid) : null;
        redirect($url);
    }
    exit;
}   

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_add_edit_grade_form($course, $student, $gradeversions, $module, false);
$mform->display();
echo $OUTPUT->footer();


