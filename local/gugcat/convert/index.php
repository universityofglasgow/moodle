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
 * Index file for grade conversion.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_gugcat\grade_aggregation;
use local_gugcat\grade_converter;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
require_once($CFG->dirroot.'/local/gugcat/classes/form/convertgradeform.php');
require_once($CFG->libdir.'/filelib.php');

$courseid = required_param('id', PARAM_INT);
$activityid = required_param('activityid', PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);  
$childactivityid = optional_param('childactivityid', null, PARAM_INT);  

require_login($courseid);
$urlparams = array('id' => $courseid, 'activityid' => $activityid, 'page' => $page);
$URL = new moodle_url('/local/gugcat/add/index.php', $urlparams);
!is_null($categoryid) && $categoryid != 0 ? $URL->param('categoryid', $categoryid) : null;
$indexurl = new moodle_url('/local/gugcat/index.php',  $urlparams);
!is_null($categoryid && $categoryid != 0) ? $indexurl->param('categoryid', $categoryid) : null;
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

//logs for grade converter
$params = array(
    'context' => context_course::instance($courseid),
    'other' => array(
        'courseid' => $courseid,
        'activityid' => $modid,
        'categoryid' => $categoryid,
        'page'=> $page
    )
);

$module = local_gugcat::get_activity($courseid, $modid);
$maxgrade = $module->gradeitem->grademax;

$returnurl = $indexurl;
global $SESSION;
if (!empty($SESSION->wantsurl)) {
    $returnurl = $SESSION->wantsurl;
}
$mform = new convertform(null, array('activity' => $module));
if($mform->is_cancelled()) {
    unset($SESSION->wantsurl);
    redirect($returnurl);
}else if ($formdata = $mform->get_data()) {
    $ispoints = $formdata->percentpoints == 1 ? true : false;
    $grades = $ispoints ? ($formdata->scale == SCHEDULE_A ? $formdata->schedA_pt : $formdata->schedB_pt)
    : ($formdata->scale == SCHEDULE_A ? $formdata->schedA : $formdata->schedB);
    $grades = array_filter($grades, 'strlen');
    if(empty($grades)){
        local_gugcat::notify_error('errorgraderequired');
    }else if(count($grades) != count(array_unique($grades))){
        local_gugcat::notify_error('errorduplicate');
    }else if(($ispoints ? max($grades) : grade_converter::convert_point_percentage($maxgrade, max($grades), false)) > intval($maxgrade)){
        local_gugcat::notify_error('errorexceedmax');
    }else{
        $copy = $grades;
        arsort($copy);
        if(!($copy === $grades)){
            local_gugcat::notify_error('errorvaluesorder');
        }else{
            $templateid = null;
            $newtemplate = array();
            // Save new template in gcat_converter_templates table, and get the id
            if(!empty($formdata->templatename)){
                $templateid = grade_converter::save_new_template($formdata->templatename, $formdata->scale);
            }
            
            $conversion = array();
            foreach($grades as $grade=>$grd){
                if(!empty($formdata->templatename) && $templateid){
                    // save percentage as decimals
                    $newtemplate[] = array('templateid'=>$templateid, 'lowerboundary'=>$ispoints ? grade_converter::convert_point_percentage($maxgrade, $grd) : $grd, 'grade'=>$grade);
                }
                $conversion[] = array('courseid'=>$courseid, 'itemid'=>$modid, 'lowerboundary'=>$ispoints ? $grd : grade_converter::convert_point_percentage($maxgrade, $grd, false), 'grade'=>$grade);
            }
            
            // Save template conversions in gcat_grade_converter table
            if($templateid){
                grade_converter::save_grade_conversion($newtemplate);
            }

            $is_subcat = $module->modname == 'category';
            $id = $is_subcat  ? $module->instance : $modid;
            $itemname = get_string($is_subcat ? 'subcategorygrade' : 'provisionalgrd', 'local_gugcat');
            
            if($prvid = local_gugcat::get_grade_item_id($courseid, $id, $itemname)){
                grade_converter::convert_provisional_grades($conversion, $module, $prvid);
                grade_converter::delete_grade_conversion($modid);
                grade_converter::save_grade_conversion($conversion, $modid, $formdata->scale);
                // Put the scale in notes for grade conversion in grade history
                $notes = $formdata->notes." -".$formdata->scale;
                $is_subcat ? grade_aggregation::update_component_notes_for_all_students($prvid, $module->id, $notes) : null;
                unset($SESSION->wantsurl);
                redirect($returnurl);
                $event = \local_gugcat\event\add_grade_converter::create($params);
                $event->trigger();
            }
        }
    }
}

$renderer = $PAGE->get_renderer('local_gugcat');
// Display the standard convert grades form.
echo $OUTPUT->header();
echo $renderer->display_empty_form(get_string('convertformtitle', 'local_gugcat'));
echo $mform->display();
echo $OUTPUT->footer();