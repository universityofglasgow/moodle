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
 * Index file.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
require_once($CFG->dirroot. '/local/gugcat/classes/form/alternativegradeform.php');

$courseid = required_param('id', PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);  

require_login($courseid);
$urlparams = array('id' => $courseid, 'page' => $page);
$URL = new moodle_url('/local/gugcat/alternative/index.php', $urlparams);
(!is_null($categoryid) && $categoryid != 0) ? $URL->param('categoryid', $categoryid) : null;
$indexurl = new moodle_url('/local/gugcat/index.php', $urlparams);

$PAGE->navbar->add(get_string('navname', 'local_gugcat'), $indexurl);
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));

$PAGE->requires->css('/local/gugcat/styles/gugcat.css');
$PAGE->requires->js_call_amd('local_gugcat/main', 'init');

$course = get_course($courseid);
$coursecontext = context_course::instance($courseid);
require_capability('local/gugcat:view', $coursecontext);

$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);
$PAGE->set_url($URL);

// Retrieve the activity
$modules = local_gugcat::get_activities($courseid, $categoryid);
$renderer = $PAGE->get_renderer('local_gugcat');

// Set up the alternative form.
$mform = new alternativegradeform(null, array('activities' => $modules));
// If the upload form has been submitted.
if ($mform->is_cancelled()) {
    $overviewurl = new moodle_url('/local/gugcat/overview/index.php', $urlparams);
    redirect($overviewurl);
} else if ($formdata = $mform->get_data()) {
} else {
    // Display the create alternative grade form.
    echo $OUTPUT->header();
    echo $renderer->display_empty_form(get_string('createaltcoursegrade', 'local_gugcat'));
    echo $mform->display();
    echo $OUTPUT->footer();
    die();
}
