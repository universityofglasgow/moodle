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
 * Sychronise completion data for CoreHR
 *
 * @package    local_corehr
 * @copyright  2016 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$courseid  = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$returnurl = new moodle_url('/local/corehr/config.php', array('id' => $courseid));
$PAGE->set_url($returnurl);
require_login($course);
$coursecontext = context_course::instance($course->id);
$title = get_string('pluginname', 'local_corehr');
$PAGE->set_context($coursecontext);
$PAGE->set_pagelayout('incourse');

require_capability('local/corehr:config', $coursecontext);

// Is there an existing coursecode?
if ($corehr = $DB->get_record('local_corehr', array('courseid' => $courseid))) {
    $coursecode = $corehr->coursecode;
    $enrolallstaff = $corehr->enrolallstaff;
} else {
    $coursecode = '';
    $enrolallstaff = 0;
}

// Form stuffs
$mform = new \local_corehr\form\config($returnurl);
$mform->set_data(array(
    'id' => $courseid,
    'coursecode' => $coursecode,
    'enrolallstaff' => $enrolallstaff,
));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
} else if ($data = $mform->get_data()) {
    $coursecode = $data->coursecode;
    $enrolallstaff = $data->enrolallstaff;
    \local_corehr\api::savecoursecode($courseid, $coursecode, $enrolallstaff);

    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
