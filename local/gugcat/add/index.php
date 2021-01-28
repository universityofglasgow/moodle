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
 * My Media main viewing page.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
require_once($CFG->dirroot.'/local/gugcat/classes/form/addeditgradeform.php');
require_once($CFG->libdir.'/filelib.php');

$courseid = required_param('id', PARAM_INT);
$activityid = required_param('activityid', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);  

require_login($courseid);
$urlparams = array('id' => $courseid, 'activityid' => $activityid, 'studentid' => $studentid, 'page' => $page);
$URL = new moodle_url('/local/gugcat/add/index.php', $urlparams);
is_null($categoryid) ? null : $URL->param('categoryid', $categoryid);
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
require_capability('local/gugcat:view', $coursecontext);

$student = $DB->get_record('user', array('id'=>$studentid, 'deleted'=>0), '*', MUST_EXIST);
$module = local_gugcat::get_activities($courseid)[$activityid];

$scaleid = $module->gradeitem->scaleid;
if (is_null($scaleid) && local_gugcat::is_grademax22($module->gradeitem->gradetype, $module->gradeitem->grademax)){
    $scaleid = null;
}
local_gugcat::set_grade_scale($scaleid);
local_gugcat::set_prv_grade_id($courseid, $module);
$grading_info = grade_get_grades($courseid, 'mod', $module->modname, $module->instance, $studentid);
$gradeitems = local_gugcat::get_grade_grade_items($course, $module);
$gradeversions = local_gugcat::filter_grade_version($gradeitems, $studentid);

$mform = new addeditgradeform(null, array('id'=>$courseid, 'page'=>$page, 'categoryid'=>$categoryid, 'activityid'=>$activityid, 'studentid'=>$studentid));
if ($fromform = $mform->get_data()) {

    if($fromform->reasons == 8) {
        $gradereason = $fromform->otherreason;
    }
    else{
        $gradereason = local_gugcat::get_reasons()[$fromform->reasons];
    }
    if(!empty($fromform->userfile)){
        file_save_draft_area_files($fromform->userfile, $PAGE->context->id, 'local_gugcat', 'attachment',
                            $fromform->userfile, array('subdirs' => 0));
    }
    
    $gradeitemid = local_gugcat::add_grade_item($courseid, $gradereason, $module);
    $grades = local_gugcat::add_update_grades($studentid, $gradeitemid, $fromform->grade, $fromform->notes, $fromform->userfile);
    $url = new moodle_url('/local/gugcat/index.php', array('id' => $courseid, 'activityid' => $activityid, 'page'=> $page));
    (!is_null($categoryid) && $categoryid != 0) ? $url->param('categoryid', $categoryid) : null;
    redirect($url);
    exit;
}   

echo $OUTPUT->header();
$PAGE->set_cm($module);
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_add_edit_grade_form($course, $student, $gradeversions, true);
$mform->display();
echo $OUTPUT->footer();