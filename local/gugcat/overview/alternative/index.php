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
use local_gugcat\grade_aggregation;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
require_once($CFG->dirroot. '/local/gugcat/classes/form/alternativegradeform.php');

$courseid = required_param('id', PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

require_login($courseid);
$urlparams = array('id' => $courseid, 'page' => $page);
$url = new moodle_url('/local/gugcat/alternative/index.php', $urlparams);
$indexurl = new moodle_url('/local/gugcat/index.php', $urlparams);
$overviewurl = new moodle_url('/local/gugcat/overview/index.php', $urlparams);

if(!is_null($categoryid) && $categoryid != 0){
    $url->param('categoryid', $categoryid);
    $overviewurl->param('categoryid', $categoryid);
}

$PAGE->navbar->add(get_string('navname', 'local_gugcat'), $indexurl);
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));

$PAGE->requires->css('/local/gugcat/styles/gugcat.css');
$PAGE->requires->js_call_amd('local_gugcat/main', 'init');

$course = get_course($courseid);
$coursecontext = context_course::instance($courseid);
require_capability('local/gugcat:view', $coursecontext);

$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);
$PAGE->set_url($url);

// Retrieve the activity
if(!is_null($categoryid) && $categoryid != 0){
    $activities = grade_aggregation::get_parent_child_activities($courseid, $categoryid);
}else{
    $activities = local_gugcat::get_activities($courseid);
}
local_gugcat::set_grade_scale(null);
$renderer = $PAGE->get_renderer('local_gugcat');

// Check for exisiting alternative course grades
$meritgi = local_gugcat::get_grade_item_id($course->id, $categoryid, get_string('meritgrade', 'local_gugcat'));
$meritsettings = null;
if($meritgi){
    $meritsettings = $DB->get_records('gcat_acg_settings', array('acgid'=>$meritgi));
}
$gpagi = local_gugcat::get_grade_item_id($course->id, $categoryid, get_string('gpagrade', 'local_gugcat'));
$gpasettings = null;
if($gpagi){
    $gpasettings = $DB->get_records('gcat_acg_settings', array('acgid'=>$gpagi));
}
// Set up the alternative form.
$mform = new alternativegradeform(null, array('activities' => $activities, 'meritsettings' => $meritsettings, 'gpasettings' => $gpasettings));
// If the upload form has been submitted.
if ($mform->is_cancelled()) {
    redirect($overviewurl);
} else if ($formdata = $mform->get_data()) {
    $is_merit = $formdata->altgradetype == MERIT_GRADE ? true : false;
    $assessments = $is_merit ? $formdata->merits : $formdata->resits;
    $weights = $is_merit ? $formdata->weights : array();
    $appliedcap = $is_merit ? null : $formdata->appliedcap;
    $appliedcap = !is_null($appliedcap) && $appliedcap == 0 ? $formdata->grade : $appliedcap;
    // Remove unselected assessments
    $assessments = array_filter($assessments);
    if(!empty($assessments)){
        grade_aggregation::create_edit_alt_grades($formdata->altgradetype, $assessments, $weights, $appliedcap);
    }
    redirect($overviewurl);
} else {
    // Display the create alternative grade form.
    echo $OUTPUT->header();
    echo $renderer->display_empty_form(get_string('createaltcoursegrade', 'local_gugcat'));
    echo $mform->display();
    echo $OUTPUT->footer();
    die();
}
