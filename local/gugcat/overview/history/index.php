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

$courseid = required_param('id', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$cnum = required_param('cnum', PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);  

require_login($courseid);
$urlparams = array('id' => $courseid, 'studentid' => $studentid, 'cnum'=>$cnum, 'page' => $page);
$URL = new moodle_url('/local/gugcat/overview/history/index.php', $urlparams);
$indexurl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));
$categoryid != 0 ? null : $URL->param('categoryid', $categoryid);

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
$modules = local_gugcat::get_activities($courseid);
if(!is_null($categoryid) && $categoryid != 0){
    // Retrieve sub categories
    $gcs = grade_category::fetch_all(array('courseid' => $courseid, 'parent' => $categoryid));

    $gradecatgi = array();
    if(!empty($gcs)){
        foreach ($gcs as $gc){
            $gi = local_gugcat::get_category_gradeitem($courseid, $gc);
            $gi->name = preg_replace('/\b total/i', '', $gi->name);
            $gradecatgi[$gi->gradeitemid] = $gi; 
        }
        //merging two arrays without changing their index.
        $modules = $modules + $gradecatgi;
    }
}

$student->cnum = $cnum;

$gradehistory = grade_aggregation::get_course_grade_history($course, $modules, $student);

//logs for course grade history viewed
$params = array(
    'context' => $coursecontext,
    'other' => array(
        'courseid' => $courseid,
        'categoryid' => $categoryid,
        'studentno' => $studentid,
        'idnumber' => $student->idnumber,
        'cnum' => $cnum,
        'page'=> $page
    )
);
$event = \local_gugcat\event\course_grade_history_viewed::create($params);
$event->trigger();

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_course_grade_history($student, $gradehistory, $modules);
echo $OUTPUT->footer();
