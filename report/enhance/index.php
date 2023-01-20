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
 * VLE Enhancement Requests
 *
 * @package    report_enhance
 * @subpackage guenrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../config.php');

// params
$courseid = optional_param('courseid', 1, PARAM_INT);
$export = optional_param('export', 0, PARAM_INT);

// Page setup.
$url = new moodle_url('/report/enhance/index.php', ['courseid' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd('report_enhance/filter', 'init');

// Find course
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// Security.
require_login($course);
$context = context_course::instance($course->id);
require_capability('report/enhance:view', $context);
$output = $PAGE->get_renderer('report_enhance');;

// Get requests
$requests = $DB->get_records('report_enhance', null, 'id desc');

// Export
if ($export) {
    $datepart = date('Y-m-d');
    $filename = "vleenhancements-$datepart.xls";
    \report_enhance\lib::export($filename, $requests);

    // Trigger event.
    $event = \report_enhance\event\enhancement_export::create(array('context' => $context));
    $event->trigger();
    die;
}

$PAGE->set_title(get_string('pluginname', 'report_enhance'));
$PAGE->set_heading($course->fullname);
\report_enhance\lib::fixnavigation();
echo $OUTPUT->header();

$status = new \report_enhance\status();
$elist = new report_enhance\output\elist($course, $requests, $status->getStatuses());
echo $output->render($elist);


echo $OUTPUT->footer();

// Trigger a report viewed event.
$event = \report_enhance\event\report_viewed::create(array('context' => $context));
$event->trigger();