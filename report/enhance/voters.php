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
$url = new moodle_url('/report/enhance/voters.php', ['courseid' => $courseid, 'id' => $id]);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

// Find course
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// Find request
$request = $DB->get_record('report_enhance', array('id' => $id));
if (!$request) {
    redirect(new moodle_url('/report/enhance/index.php', ['courseid' => $courseid]));
}

// Get voters
$voters = $DB->get_records('report_enhance_vote', ['enhanceid' => $id]);

// Security.
require_login($course);
$context = context_course::instance($course->id);
require_capability('report/enhance:review', $context);
$output = $PAGE->get_renderer('report_enhance');;

$PAGE->set_title(get_string('pluginname', 'report_enhance'));
$PAGE->set_heading($course->fullname);
\report_enhance\lib::fixnavigation(get_string('voters', 'report_enhance'), $url);
echo $OUTPUT->header();

$more = new report_enhance\output\voters($courseid, $request, $voters);
echo $output->render($more);

echo $OUTPUT->footer();

