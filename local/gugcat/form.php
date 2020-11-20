  
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
 * My Media main viewing page.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/local/gugcat/classes/form/form.php');
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('navname', 'local_gugcat'), new moodle_url('/local/gugcat'));

$courseid = optional_param('id', 1, PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

$PAGE->set_course($course);
require_login($course);
$context = context_course::instance($course->id);
$PAGE->requires->css('/local/gugcat/styles/form.css') ;


$PAGE->set_url(new moodle_url('/local/gugcat/form.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('title', 'local_gugcat'));
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);

$mform = new form();

if ($fromform = $mform->get_data()) {
    redirect($CFG->wwwroot . '/local/gugcat/index.php');
}

$templatecontext = (object)[

    'assessmentlvlscore' =>get_string('assessmentlvlscore', 'local_gugcat'),
    'overviewaggregrade' =>get_string('overviewaggregrade', 'local_gugcat'),
    'addnewgrade' =>get_string('addnewgrade', 'local_gugcat'),
    'confirmgrade' =>get_string('confirmgrade', 'local_gugcat'),
 ];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_gugcat/form', $templatecontext);
$mform->display();
echo $OUTPUT->footer();