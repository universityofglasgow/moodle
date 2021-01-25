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
require_once($CFG->dirroot.'/local/gugcat/classes/form/coursegradeform.php');

$courseid = required_param('id', PARAM_INT);
$formtype = required_param('setting', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$cnum = required_param('cnum', PARAM_INT);

require_login($courseid);
$urlparams = array('id' => $courseid, 'setting' => $formtype, 'studentid' => $studentid, 'cnum' => $cnum);
$URL = new moodle_url('/local/gugcat/overview/gradeform/index.php', $urlparams);
$indexurl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));

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
$studentarr = $DB->get_records('user', array('id'=>$studentid, 'deleted'=>0), MUST_EXIST);

$activities = local_gugcat::get_activities($courseid);
$rows = grade_aggregation::get_rows($course, $activities, $studentarr);
$student = $rows[0];
$student->cnum = $cnum; //candidate no.
$student->id = $student->studentno; 
$student->lastname = $student->surname; 
$student->firstname = $student->forename;
$mform = new coursegradeform(null, array('id'=>$courseid, 'studentid'=>$studentid, 'setting'=>$formtype, 'student'=>$student));
if ($fromform = $mform->get_data()) {
    if($formtype == OVERRIDE_GRADE_FORM){
        $gradeitemid = local_gugcat::add_grade_item($courseid, get_string('aggregatedgrade', 'local_gugcat'), null);
        local_gugcat::update_grade($studentid, $gradeitemid, $fromform->override, $fromform->notes, null, time());
    }else if($formtype == ADJUST_WEIGHT_FORM){
        $weights = $fromform->weights;
        if(array_sum($weights) != 100){
            local_gugcat::notify_error('errortotalweight');
            $urlparams = "?id=$courseid&setting=$formtype&studentid=$studentid&cnum=$cnum";
            redirect($URL);
            exit;
        }else{
            grade_aggregation::adjust_course_weight($weights, $courseid, $studentid, $fromform->notes);
        }
    }
    $url = new moodle_url('/local/gugcat/overview/index.php', array('id' => $courseid));
    redirect($url);
    exit;
}   

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_adjust_override_grade_form($student);
$mform->display();
echo $OUTPUT->footer();
