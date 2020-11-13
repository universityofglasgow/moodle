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

require_once '../../config.php';
global $USER, $DB, $CFG;

$PAGE->set_url('/local/gugcat/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->requires->css('/local/gugcat/gcsa.css');

$PAGE->set_title($strpagetitle);
require_login();

$strpagetitle = get_string('gugcat', 'local_gugcat');
$strpageheading = get_string('gugcat', 'local_gugcat');

$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

//testing course id = 1
$courseid = optional_param('id', 1, PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}
$PAGE->set_course($course);
$context_course = context_course::instance($course->id);
$students = get_role_users(5 , $context_course);
$modinfo = get_fast_modinfo($courseid);
$mods = $modinfo->get_cms();

$templatecontext = (object)[
    'title' =>get_string('title', 'local_gugcat'),
    'assessmenttabstr' =>get_string('assessmentlvlscore', 'local_gugcat'),
    'overviewtabstr' =>get_string('overviewaggregrade', 'local_gugcat'),
    'addsavebtnstr' =>get_string('saveallgrade', 'local_gugcat'),
    'approvebtnstr' =>get_string('approvegrades', 'local_gugcat'),
    'students' => array_values($students),
    'activities' => array_values($mods)
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_gugcat/index', $templatecontext);
echo $OUTPUT->footer();