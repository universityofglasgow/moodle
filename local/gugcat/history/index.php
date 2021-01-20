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
$activityid = required_param('activityid', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);

require_login($courseid);
$urlparams = array('id' => $courseid, 'activityid' => $activityid, 'studentid' => $studentid);
$URL = new moodle_url('/local/gugcat/history/index.php', $urlparams);
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

$student = $DB->get_record('user', array('id'=>$studentid, 'deleted'=>0), '*', MUST_EXIST);
$module = local_gugcat::get_activities($courseid)[$activityid];

$scaleid = $module->gradeitem->scaleid;
if (is_null($scaleid) && local_gugcat::is_grademax22($module->gradeitem->gradetype, $module->gradeitem->grademax)){
    $scaleid = null;
}
local_gugcat::set_grade_scale($scaleid);
local_gugcat::set_prv_grade_id($courseid, $module);

$history = local_gugcat::get_grade_history($courseid, $module, $studentid);
echo $OUTPUT->header();
$PAGE->set_cm($module);
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_grade_history($student, $module->name, $history);
echo $OUTPUT->footer();
