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
 * @subpackage anonymous
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

// Parameters.
$id = required_param('id', PARAM_INT);
$assignid = optional_param('assign', 0, PARAM_INT);
$reveal = optional_param('reveal', 0, PARAM_INT);
$export = optional_param('export', 0, PARAM_INT);

$url = new moodle_url('/report/anonymous/index.php', array('id' => $id));
$fullurl = new moodle_url('/report/anonymous/index.php', array(
    'id' => $id,
    'assign' => $assignid,
    'reveal' => $reveal,
));

// Page setup.
$PAGE->set_url($fullurl);
$PAGE->set_pagelayout('admin');

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

// Security.
require_login($course);
$output = $PAGE->get_renderer('report_anonymous');
$context = context_course::instance($course->id);
require_capability('mod/assign:grade', $context);
require_capability('report/anonymous:view', $context);

if (!$export) {
    $PAGE->set_title($course->shortname .': '. get_string('pluginname', 'report_anonymous'));
    $PAGE->set_heading($course->fullname);

    // Jquery stuff.
    $PAGE->requires->jquery();
    $PAGE->requires->js('/report/anonymous/jquery/tablesorter/jquery.tablesorter.js');
    $PAGE->requires->js('/report/anonymous/jquery/tablesorter/jquery.tablesorter.widgets.js');
    $PAGE->requires->js('/report/anonymous/jquery/pager/jquery.tablesorter.pager.js');
    $PAGE->requires->js('/report/anonymous/jquery/anonymous.js');

    echo $OUTPUT->header();
}

// Get assignments
$assignments = report_anonymous::get_assignments($id);

// Has a link been submitted?
if ($assignid) {
    if (!report_anonymous::allowed_to_view($assignid, $assignments)) {
        notice(get_string('notallowed', 'report_anonymous'), $url);
    }

    $assignment = $DB->get_record('assign', array('id' => $assignid), '*', MUST_EXIST);
    $urkund = report_anonymous::urkund_enabled($assignid);

    // allocate ids if required
    if ($assignment->blindmarking) {
        assign::allocate_unique_ids($assignid);
    }
    $users = report_anonymous::get_assign_users($context);
    $submissions = report_anonymous::get_submissions($assignid, $users);
    $submissions = report_anonymous::sort_submissions($submissions, $reveal || (!$assignment->blindmarking));
    if ($export) {
        $filename = "anonymous_{$assignment->name}.xls";
        report_anonymous::export($assignment, $submissions, $reveal, $filename, $urkund);
        die;
    }
    $output->actions($context, $fullurl, $reveal, $assignment);
    $output->report($id, $assignment, $submissions, $reveal, $urkund);
    $output->back_button($url);
} else {

    // List of activities to select.
    $output->list_assign($fullurl, $assignments);
}

echo $OUTPUT->footer();

