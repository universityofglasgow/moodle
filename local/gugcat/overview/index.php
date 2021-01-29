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

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');

$courseid = required_param('id', PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);  

$URL = new moodle_url('/local/gugcat/overview/index.php', array('id' => $courseid, 'page' => $page));
is_null($categoryid) ? null : $URL->param('categoryid', $categoryid);
$indexurl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));
require_login($courseid);
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

//Retrieve groups
$groups = groups_get_all_groups($courseid);

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

//Retrieve activities
$activities = local_gugcat::get_activities($courseid);
$rows = grade_aggregation::get_rows($course, $activities, $students);

$requireresit = optional_param('resit', null, PARAM_NOTAGS);
$finalrelease = optional_param('finalrelease', null, PARAM_NOTAGS);
$rowstudentid = optional_param('rowstudentno', null, PARAM_NOTAGS);
$downloadcsv = optional_param('downloadcsv', null, PARAM_NOTAGS);
$finalgrades = optional_param_array('finalgrades', null, PARAM_NOTAGS);
$cminstances = optional_param_array('cminstances', null, PARAM_NOTAGS);
if(isset($requireresit) && !empty($rowstudentid)){
    grade_aggregation::require_resit($rowstudentid);
    unset($requireresit);
    unset($rowstudentid);
    redirect($URL);
    exit;
}else if(isset($finalrelease)){
    grade_aggregation::release_final_grades($courseid); 
    unset($finalrelease);
    redirect($URL);
    exit;
}else if(isset($downloadcsv)){
    grade_aggregation::export_aggregation_tool($course);
    unset($downloadcsv);
    redirect($URL);
    exit;
}

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_aggregation_tool($rows, $activities);
echo $OUTPUT->paging_bar($totalenrolled, $page, $limitnum, $PAGE->url);
echo $OUTPUT->footer();
