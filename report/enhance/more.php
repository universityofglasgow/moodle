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
$courseid = required_param('courseid', PARAM_INT);
$id = required_param('id', PARAM_INT);
$context = context_course::instance($courseid);

// Page setup.
$url = new moodle_url('/report/enhance/more.php', ['courseid' => $courseid, 'id' => $id]);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

// Find course
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// Find request
$request = $DB->get_record('report_enhance', array('id' => $id));
if (!$request) {
    redirect(new moodle_url('/report/enhance/index.php', ['courseid' => $courseid]));
}

// Find any comments
$comments = $DB->get_records('report_enhance_comment', ['enhanceid' => $id]);

// Get any attachments
$fs = get_file_storage();
$dir = $fs->get_area_tree($context->id, 'report_enhance', 'attachments', $id);
$files = $dir['files'];
$attachments = [];
foreach ($files as $file) {
    $attachment = new stdClass;
    $attachment->name = $file->get_filename();
    $attachment->url = moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename(),
        true // true = force download.
    );

    $attachments[] = $attachment;
}

// Security.
require_login($course);
$context = context_course::instance($course->id);
require_capability('report/enhance:view', $context);
$output = $PAGE->get_renderer('report_enhance');;

$PAGE->set_title(get_string('pluginname', 'report_enhance'));
$PAGE->set_heading($course->fullname);
\report_enhance\lib::fixnavigation(get_string('requestno', 'report_enhance', $request->id), $url);
echo $OUTPUT->header();

$more = new report_enhance\output\more($course, $request, $context, $attachments, $comments);
echo $output->render($more);

echo $OUTPUT->footer();

// Trigger a enhancement viewed event.
$event = \report_enhance\event\enhancement_viewed::create([
    'context' => $context,
    'objectid' => $id,
]);
$event->trigger();