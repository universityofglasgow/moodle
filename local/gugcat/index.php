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
require_once('grade_capture_item.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/gugcat/'));
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('navname', 'local_gugcat'), new moodle_url('/local/gugcat'));
$PAGE->requires->css('/local/gugcat/styles/gcsa.css');

//testing course id = 2
$courseid = optional_param('courseid', 2, PARAM_INT); //change to required
$activityid = optional_param('activityid', null, PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);

require_login($course);
$coursecontext = context_course::instance($course->id);
$students = get_enrolled_users($coursecontext, 'mod/coursework:submit');

echo $OUTPUT->header();

$activities = get_activities($courseid, $activityid);
$selectedmodule = is_null($activityid) ? array_pop(array_reverse($modules)) : $modules[$activityid];
$rows = get_rows($course, $selectedmodule , $students);
$columns = get_columns();

$templatecontext = (object)[
    'title' =>get_string('title', 'local_gugcat'),
    'assessmenttabstr' =>get_string('assessmentlvlscore', 'local_gugcat'),
    'overviewtabstr' =>get_string('overviewaggregrade', 'local_gugcat'),
    'saveallbtnstr' =>get_string('saveallnewgrade', 'local_gugcat'),
    'approvebtnstr' =>get_string('approvegrades', 'local_gugcat'),
    'addallgrdstr' =>get_string('addallnewgrade', 'local_gugcat'),
    'reasonnewgrdstr' =>get_string('reasonnewgrade', 'local_gugcat'),
    'rows' => $rows,
    'columns' => $columns,
    'activities' => $activities
];

echo $OUTPUT->render_from_template('local_gugcat/index', $templatecontext);
echo $OUTPUT->footer();
