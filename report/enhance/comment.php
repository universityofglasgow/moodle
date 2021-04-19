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
$id = required_param('id', PARAM_INT);
$commentid = optional_param('commentid', 0, PARAM_INT);

// Page setup.
$url = new moodle_url('/report/enhance/comment.php', ['courseid' => $courseid, 'id' => $id]);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

// Find course and request
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$request = $DB->get_record('report_enhance', ['id' => $id], '*', MUST_EXIST);

// Security.
require_login($course);
$context = context_course::instance($course->id);
require_capability('report/enhance:addcomment', $context);
$output = $PAGE->get_renderer('report_enhance');

// Comment to edit?
if ($commentid) {
    $comment = $DB->get_record('report_enhance_comment', ['id' => $commentid, 'enhanceid' => $id], '*', MUST_EXIST);

    // Do we have the right to edit this?
    if (!has_capability('report/enhance:editallcomments', $context) && ($USER->id != $comment->userid)) {
        print_error('editcommenterror', 'report_enhance');
    }
} else {
    $comment = null;
}

// Form stuff
$form = new \report_enhance\forms\comment_form(null, [
    'course' => $course,
    'request' => $request,
    'comment' => $comment,
]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/report/enhance/more.php', ['courseid' => $courseid, 'id' => $id]));
} else if ($data = $form->get_data()) {

    if ($commentid) {
        $comment->comment = $data->comment['text'];
        $comment->timeedited = time();
        $DB->update_record('report_enhance_comment', $comment);
    } else {
        $comment = new stdClass;
        $comment->enhanceid = $id;
        $comment->userid = $USER->id;
        $comment->comment = $data->comment['text'];
        $comment->timeadded = time();
        $comment->timeedited = 0; // Not edited.
        $DB->insert_record('report_enhance_comment', $comment);
    }
    redirect(new moodle_url('/report/enhance/more.php', ['courseid' => $courseid, 'id' => $id]));
}

$PAGE->set_title(get_string('pluginname', 'report_enhance'));
$PAGE->set_heading($course->fullname);
\report_enhance\lib::fixnavigation(get_string('comment', 'report_enhance'), $url);
echo $OUTPUT->header();

$edit = new report_enhance\output\comment($form->render());
echo $output->render($edit);

echo $OUTPUT->footer();

