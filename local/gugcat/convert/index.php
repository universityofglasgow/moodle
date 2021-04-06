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

$module = local_gugcat::get_activity($courseid, $modid);
$scales = array(
    0 => get_string('schedulea', 'local_gugcat'),
    1 => get_string('scheduleb', 'local_gugcat')
);
$mform = new convertform(null, array('activity' => $module, 'scales' => $scales));
if ($formdata = $mform->get_data()) {

    $grades = $formdata->scale == 0 ? $formdata->schedA : $formdata->schedB;
    $i = $formdata->scale == 0 ? 23 : 8;
    $gradeconvert = array();
    foreach($grades as $grd){
        if($grd != ""){
            $grdconvert = array('courseid'=>$courseid, 'itemid'=>$modid, 'lowerboundary'=>$grd, 'grade'=>$i);
            array_push($gradeconvert, $grdconvert);
        }
        $i--;
    }
    grade_converter::save_grade_converter($modid, $formdata->scale, $gradeconvert);
}else if ($mform->is_cancelled()) {
    redirect($indexurl);
}

$renderer = $PAGE->get_renderer('local_gugcat');
// Display the standard convert grades form.
echo $OUTPUT->header();
echo $renderer->display_empty_form(get_string('convertformtitle', 'local_gugcat'));
echo $mform->display();
echo $OUTPUT->footer();