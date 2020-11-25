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


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
require_once('grade_capture_item.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/gugcat/'));
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('navname', 'local_gugcat'), new moodle_url('/local/gugcat'));

$PAGE->requires->js_call_amd('local_gugcat/main', 'init');
$PAGE->requires->css('/local/gugcat/styles/gugcat.css');

$courseid = required_param('id', PARAM_INT);
$activityid = optional_param('activityid', null, PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);

$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);

$coursecontext = context_course::instance($course->id);
$students = get_enrolled_users($coursecontext, 'mod/coursework:submit');
$activities = local_gugcat::get_activities($courseid, $activityid);
$mods = array_reverse($modules);
$selectedmodule = is_null($activityid) ? array_pop($mods) : $modules[$activityid];
$rows = local_gugcat::get_rows($course, $selectedmodule , $students);
$columns = local_gugcat::get_columns();

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_grade_capture($activities, $rows, $columns);
echo $OUTPUT->footer();