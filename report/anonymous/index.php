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
 * @copyright  2019 Howard Miller (howardsmiller@gmail.com)
 * @package    report
 * @subpackage anonymous
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('REPORT_PAGESIZE', 20);

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

// Parameters.
$id = required_param('id', PARAM_INT);
$assignid = optional_param('assign', 0, PARAM_INT);
$export = optional_param('export', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$tsort = optional_param('tsort', 'idnumber', PARAM_ALPHA);
$tdir = optional_param('tdir', 'asc', PARAM_ALPHA);
$group = optional_param('group', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$userid = optional_param('userid', 0, PARAM_INT);

$url = new moodle_url('/report/anonymous/index.php', array('id' => $id));
$fullurl = new moodle_url('/report/anonymous/index.php', array(
    'id' => $id,
    'assign' => $assignid,
    'group' => $group,
));

// Page setup.
$PAGE->set_url($fullurl);
$PAGE->set_pagelayout('report');

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

// Security.
require_login($course);
$output = $PAGE->get_renderer('report_anonymous');
$context = context_course::instance($course->id);
require_capability('mod/assign:grade', $context);
require_capability('report/anonymous:view', $context);

// Download feedback files?
// Avoid headers and stuff
if ($assignid && ($action == 'feedback')) {
    report_anonymous::feedback_files($assignid);
    redirect($url);
}

if (!$export) {
    $PAGE->set_title($course->shortname .': '. get_string('pluginname', 'report_anonymous'));
    $PAGE->set_heading($course->fullname);

    echo $output->header();
}

// Get assignments
$assignments = report_anonymous\lib::get_assignments($id);

// Has a link been submitted?
if ($assignid) {

    // Filters
    $PAGE->requires->js_call_amd('report_anonymous/anonymous', 'init');

    // Create assignment object
    $assign = \report_anonymous\lib::get_assign($course, $assignid);

    // Participants
    $submissions = $assign->list_participants_with_filter_status_and_group($group);
    $cm = get_coursemodule_from_instance('assign', $assignid);
    $submissions = report_anonymous\lib::add_assignment_data($course->id, $assignid, $cm->id, $assign, $submissions);

    // group mode
    $groupmode = groups_get_activity_groupmode($cm, $course);

    if (!report_anonymous\lib::allowed_to_view($assignid, $assignments)) {
        notice(get_string('notallowed', 'report_anonymous'), $url);
    }

    $assignment = $DB->get_record('assign', ['id' => $assignid], '*', MUST_EXIST);
    $urkund = report_anonymous\lib::urkund_enabled($assignid);
    $turnitin = report_anonymous\lib::turnitin_enabled($assignid);

    // Reveal always if not blindmarking
    if (!$assignment->blindmarking) {
        $reveal = true;
    }

    // allocate ids if required
    if ($assignment->blindmarking) {
        assign::allocate_unique_ids($assignid);
    }

    if ($export) {
        $filename = "anonymous_{$assignment->name}.xls";
        report_anonymous::export($assignment, $displaysubs, $reveal, $filename, $urkund);
        die;
    }

    // Display report
    $reportassign = new report_anonymous\output\reportassign($course, $context, $fullurl, $submissions, $assignment);
    echo $output->render_reportassign($reportassign);

    //$output->actions($context, $fullurl, $reveal, $assignment);
    //groups_print_activity_menu($cm, $fullurl);
    //$output->report($id, $assignment, $displaysubs, $reveal, $urkund, $fullurl);
    //$output->back_button($url);
} else {

    // List of activities to select.
    $listassign = new report_anonymous\output\listassign($course, $fullurl, $assignments);
    echo $output->render_listassign($listassign);
}

echo $output->footer();

