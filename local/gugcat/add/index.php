  
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
require_once($CFG->dirroot.'/local/gugcat/classes/form/addgradeform.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/gugcat/add/index.php'));
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('navname', 'local_gugcat'), new moodle_url('/local/gugcat'));

$PAGE->requires->css('/local/gugcat/styles/gugcat.css');
$PAGE->requires->js_call_amd('local_gugcat/main', 'init');

$courseid = required_param('id', PARAM_INT);
$activityid = required_param('activityid', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$student = $DB->get_record('user', array('id'=>$studentid, 'deleted'=>0), '*', MUST_EXIST);
require_login($course);

$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);

$modinfo = get_fast_modinfo($courseid);
$module = $modinfo->get_cm($activityid);

// get 1st grade
$grading_info = grade_get_grades($courseid, 'mod', $module->modname, $module->instance, $studentid);
$gbgrade = $grading_info->items[0]->grades[$studentid]->grade;
$convertedgrade = local_gugcat::convert_grade($gbgrade);
$prvgradeid = local_gugcat::get_prv_grade_id($courseid, $module->id);

$mform = new addgradeform(null, array('id'=>$courseid, 'activityid'=>$activityid, 'studentid'=>$studentid));
if ($fromform = $mform->get_data()) {

    if($fromform->reasons == 8) {
        $gradereason = $fromform->otherreason;
    }
    else{
        $gradereason = local_gugcat::$REASONS[$fromform->reasons];
    }

    $gradeitemid = local_gugcat::add_grade_item($courseid, $gradereason, $module->id);
    $grades = local_gugcat::add_update_grades($studentid, $gradeitemid, $fromform->grade);
    $provisionalgrade = local_gugcat::add_update_grades($studentid, $prvgradeid, $fromform->grade);

    redirect($CFG->wwwroot . '/local/gugcat/index.php?id='.$courseid.'&amp;activityid='.$activityid);
}

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_add_grade_form($course, $module->name, $convertedgrade, $student);
$mform->display();
echo $OUTPUT->footer();


