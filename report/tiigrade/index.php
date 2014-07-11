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
 * Anonymous report
 *
 * @package    report
 * @subpackage tiigrade
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

// Parameters.
$id = required_param('id', PARAM_INT);
$assignid = optional_param('assign', 0, PARAM_INT);
$reveal = optional_param('reveal', 0, PARAM_INT);
$export = optional_param('export', 0, PARAM_INT);

$url = new moodle_url('/report/tiigrade/index.php', array('id' => $id));
$fullurl = new moodle_url('/report/tiigrade/index.php', array(
    'id' => $id,
    'assign' => $assignid,
    'reveal' => $reveal,
));

// Page setup.
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourse');
}

// Security.
require_login($course);

// is tt2 installed
if (!file_exists($CFG->dirroot . '/mod/turnitintooltwo/version.php')) {
    notice(get_string('noturnitintooltwo', 'report_tiigrade'));
}

$output = $PAGE->get_renderer('report_tiigrade');
$context = context_course::instance($course->id);
$captt = has_capability('mod/turnitintool:grade', $context);
if (!$captt || !has_capability('report/tiigrade:view', $context)) {
    notice(get_string('nocapability', 'report_tiigrade'));
}

// Paranoia.
if (!has_capability('report/tiigrade:shownames', $context)) {
    $reveal = 0;
}

if (!$export) {
    $PAGE->set_title($course->shortname .': '. get_string('pluginname', 'report_tiigrade'));
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
}

// Get turnitintool submissions.
$assignments = report_tiigrade::get_assignments($id);

// Has a link been submitted?
if ($assignid) {
    if (!report_tiigrade::allowed_to_view($assignid, $assignments)) {
        notice(get_string('notallowed', 'report_tiigrade'), $url);
    }

    // Get assignment
    $assignment = $assignments[$assignid];

    // Can we view user data.
    $anonymous = $assignment->blindmarking && (time() < $assignment->cutoffdate);
    $showuserdata = !$anonymous;
    if (has_capability('report/tiigrade:shownames', $context) && $anonymous) {
        $showuserdata = $reveal;
    }

    // find the coursemodule for this assignment
    $module = $DB->get_record('modules', array('name' => 'assign'), '*', MUST_EXIST);
    $cm = $DB->get_record('course_modules', array('module' => $module->id, 'instance' => $assignment->id), '*', MUST_EXIST);

    $submissions = report_tiigrade::get_submissions($cm->id);
    if (!$submissions) {
        $output->nosubmissions();
        $output->back_button($url);
    } else {
        if ($export) {
            $filename = "tiigrade_{$assignment->name}.xls";
            report_tiigrade::export($submissions, $reveal, $filename);
            die;
        }
        $output->actions($context, $fullurl, $anonymous, $reveal);
        $output->report($id, $assignment, $submissions, $showuserdata);
        $output->back_button($url);
    }
} else {

    // List of assignments to select.
    $output->list_assignments($fullurl, $assignments);
}

echo $OUTPUT->footer();

