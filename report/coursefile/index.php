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
 * Anonymous report
 *
 * @package    report
 * @subpackage anonymous
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require(dirname(__FILE__).'/../../config.php');

// Parameters.
$id = required_param('id', PARAM_RAW);

$url = new moodle_url('/report/coursefile/index.php', array('id' => $id));

// Page setup.
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

// Security.
require_login($course);
$output = $PAGE->get_renderer('report_coursefile');
$context = context_course::instance($course->id);
require_capability('report/coursefile:view', $context);

$PAGE->set_title($course->shortnames .': '. get_string('pluginname', 'report_coursefile'));
$PAGE->set_heading($course->fullname);

// Get data
$calc = new report_coursefile_calc();
$files = $calc::get_filelist($context);
$filesum = $calc::get_filesum();

echo $OUTPUT->header();
echo $output->filetable($files);
echo $output->filesum($filesum);
echo $OUTPUT->footer();

