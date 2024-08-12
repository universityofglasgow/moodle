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
 * Displays the Staff View of the Student Dashboard.
 *
 * This page accepts a course id and creates a (drop down) list of
 * students to choose from. A dedicated container will serve to
 * display the assessments of the student. The results are to be
 * both sortable, and paged if necessary.
 *
 * @package    local_gustaffview
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $CFG, $USER, $DB;
$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);

$url = new moodle_url('/local/gustaffview/sduserdetails.php', ['courseid' => $courseid]);
$PAGE->set_url($url);

if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new \moodle_exception('invalidcourseid');
}

require_login($course);

require_once($CFG->dirroot . '/blocks/newgu_spdetails/locallib.php');

$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$title = get_string('staffview', 'local_gustaffview');
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->requires->jquery();

$studentid = optional_param('studentid', '', PARAM_TEXT);
if ($studentid == 0) {
    redirect("$CFG->wwwroot/local/gustaffview/sduserdetails.php?courseid=" . $courseid);
}

echo $OUTPUT->header();

$html = '';
$html .= html_writer::div(html_writer::tag('h1',$title),'',['id' => 'staffview']);
$html .= html_writer::div(get_string('staffview_summary', 'local_gustaffview'),'',['id' => 'staffview_summary']);
$html .= html_writer::start_tag('div', ['id' => 'student_block', 'class' => 'row m-4']);
$html .= html_writer::tag('label', 'Student: ', array('class' => 'col-md-2', 'for' => 'selectstudent'));

$enrolledstudents = get_enrolled_users($context, 'moodle/grade:view', 0, 'u.id, u.firstname, u.lastname',  null, 0, 0, true);

$studentoptions = [];
$studentoptions[0] = "Select";

if (!empty($enrolledstudents)) {
    foreach ($enrolledstudents as $enrolledstudent) {
        $studentid = $enrolledstudent->id;
        $studentname = $enrolledstudent->firstname . " " . $enrolledstudent->lastname;
        $studentoptions[$studentid] = $studentname;
    }
}

$html .= html_writer::select($studentoptions, 'selectstudent', 0, '', array('id' => 'selectstudent', 'class' => 'col-md-3 ml-4'));
$html .= html_writer::end_tag('div');
$html .= html_writer::tag('div', '',['id' => 'tmpContainer']);

echo $html;

$PAGE->requires->js_call_amd('local_gustaffview/dashboard', 'init');

echo $OUTPUT->footer();
