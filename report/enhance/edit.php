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
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/report/enhance/classes/status.php');

// params
$courseid = required_param('courseid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);

// Page setup.
$url = new moodle_url('/report/enhance/index.php', ['courseid' => $courseid, 'id' => $id]);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

// Find course
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// If editing existing
if ($id) {
    $request = $DB->get_record('report_enhance', array('id' => $id), '*', MUST_EXIST);
} else {
    $request = null;
}

// Security.
require_login($course);
$context = context_course::instance($course->id);
if(isset($request)) {
    if(!($request->userid == $USER->id && ($request->status == ENHANCE_STATUS_NEW || $request->status == ENHANCE_STATUS_MOREINFORMATION))) {
        require_capability('report/enhance:editall', $context);
    }
}
$output = $PAGE->get_renderer('report_enhance');

// Set up files area
$entry = new stdClass();
$entry->id = $id;
$options = ['subdirs' => 0];
file_prepare_standard_filemanager($entry, 'attachments', $options, $context, 'report_enhance', 'attachments', $id);

// Form stuff
$form = new \report_enhance\forms\enhance_form(null, array('course' => $course, 'request' => $request, 'entry' => $entry));
if ($form->is_cancelled()) {
    redirect(new moodle_url('/report/enhance/index.php', array('courseid' => $courseid)));
} else if ($data = $form->get_data()) {

    if (!$id) {
        $request = new stdClass();
        $request->timecreated = time();
        $request->status = ENHANCE_STATUS_NEW;
        $request->userid = $USER->id;
    }
    $request->headline = $data->headline;
    $request->description = $data->description['text'];
    $request->benefits = $data->benefits['text'];
    $request->department = substr($data->department, 0, 50);
    $request->timemodified = time();
    if ($id) {
        $DB->update_record('report_enhance', $request);

            // Log event
            $event = \report_enhance\event\enhancement_edited::create([
                'context' => $context,
                'objectid' => $id,
            ]);
            $event->trigger();
    } else {
        $id = $DB->insert_record('report_enhance', $request);
        $request->id = $id;
        \report_enhance\email::newrequest($USER, $request);

        // Log event
        $event = \report_enhance\event\enhancement_logged::create([
            'context' => $context,
            'objectid' => $id,
        ]);
        $event->trigger();
    }

    // Attachments
    file_postupdate_standard_filemanager($data, 'attachments', $options, $context, 'report_enhance', 'attachments', $id);

    redirect(new moodle_url('/report/enhance/index.php', array('courseid' => $courseid)));
}

$PAGE->set_title(get_string('pluginname', 'report_enhance'));
$PAGE->set_heading($course->fullname);
\report_enhance\lib::fixnavigation(get_string('edit', 'report_enhance'), $url);
echo $OUTPUT->header();

$edit = new report_enhance\output\edit($form->render());
echo $output->render($edit);

echo $OUTPUT->footer();

