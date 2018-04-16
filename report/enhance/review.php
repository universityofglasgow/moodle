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
require_once($CFG->dirroot . '/report/enhance/review_form.php');

// params
$courseid = required_param('courseid', PARAM_INT);
$id = required_param('id', PARAM_INT);

// Page setup.
$url = new moodle_url('/report/enhance/review.php', array('courseid' => $courseid, 'id' => $id));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

// Find course
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// Find request
$request = $DB->get_record('report_enhance', array('id' => $id), '*', MUST_EXIST);

// Security.
require_login($course);
$context = context_course::instance($course->id);
require_capability('report/enhance:view', $context);
$output = $PAGE->get_renderer('report_enhance');

// Form fields
$fields = array(
    'functionality',
    'viability',
    'policies',
    'result',
);

// Form stuff
$status = new \report_enhance\status();
$form = new \review_form(null, array('course' => $course, 'request' => $request, 'fields' => $fields, 'statuses' => $status->getStatuses()));
$form->set_data($request);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/report/enhance/index.php', array('courseid' => $courseid)));
} else if ($data = $form->get_data()) {
    $request->status = $data->status;
    foreach ($fields as $field) {
        $formdata = $data->$field;
        $request->$field = $formdata['text'];
    }
    $DB->update_record('report_enhance', $request);
    redirect(new moodle_url('/report/enhance/index.php', array('courseid' => $courseid)));
}

$PAGE->set_title(get_string('pluginname', 'report_enhance'));
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

$review = new report_enhance\output\review($form->render(), $request);
echo $output->render($review);

echo $OUTPUT->footer();

