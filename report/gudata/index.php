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
 * Gudata report
 *
 * @package    report_gudata
 * @copyright  2020 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

// Parameters.
$courseid = required_param('id', PARAM_INT);
$action = optional_param('action', 'userdownload', PARAM_ALPHA);

// Security.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);
require_login($course);
require_capability('report/gudata:view', $context);

// Renderer.
$PAGE->set_context($context);
$output = $PAGE->get_renderer('report_gudata');
$url = new moodle_url('/report/gudata/index.php', ['id' => $courseid]);
$PAGE->set_url($url);

// Start the page.
$PAGE->set_title($course->shortname .': '. get_string('pluginname', 'report_gudata'));
$PAGE->set_heading($course->fullname);

// Display appropriate form
if ($action == 'userdownload') {
    $process = new \report_gudata\userdownload($course);
    $form = new \report_gudata\forms\userdownload(null, [
        'id' => $courseid,
        'action' => $action,
        'roles' => $process->get_filter_roles(),
    ]);
} else {
    $process = new report_gudata\logsdownload($course);
    $form = new \report_gudata\forms\logsdownload(null, [
        'id' => $courseid,
        'action' => $action,
    ]);   
}
if ($data = $form->get_data()) {
    $process->set_data($data);
    $process->execute();
}
$main = new \report_gudata\output\main('/report/gudata/index.php', $course, $action, $form);

echo $output->header();

echo $output->heading(get_string('pluginname', 'report_gudata'));
echo $output->render_main($main);

echo $output->footer();
