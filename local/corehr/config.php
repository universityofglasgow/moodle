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
require_once(dirname(__FILE__) . '/locallib.php');

$courseid   = required_param('id', PARAM_INT);

$returnurl = new moodle_url('/local/corehr/config.php', array('id' => $courseid));
$PAGE->set_url($returnurl);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$coursecontext = course_context::instance($courseid);

require_login($course);
$coursecontext = context_course::instance($course->id);
$title = get_string('pluginname', 'local_corehr');
$PAGE->set_context($coursecontext);
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading();
$PAGE->set_title(get_string('config', 'local_corehr'));

require_capability('local/corehr:config', $coursecontext);



echo $OUTPUT->header();

echo $OUTPUT->footer();
