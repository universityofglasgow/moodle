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
 * Participation report
 *
 * @package    report
 * @subpackage guenrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Some screens show 'live' progress.
define('NO_OUTPUT_BUFFERING', true);

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/lib/tablelib.php');

// Parameters.
$id = required_param('id', PARAM_INT); // Course id.
$codeid = optional_param('codeid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

$url = new moodle_url('/report/guenrol/index.php', array(
    'id' => $id,
    'codeid' => $codeid,
    'action' => $action
));

// Page setup.
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourse');
}

// Security.
require_login($course);
$context = context_course::instance($course->id);
require_capability('report/guenrol:view', $context);
$output = $PAGE->get_renderer('report_guenrol');

// Library.
$locallib = new report_guenrol\locallib($id);

$PAGE->set_title($course->shortname .': '. get_string('pluginname', 'report_guenrol'));
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading( get_string('title', 'report_guenrol') . ' : ' . $course->fullname );

// get the last time the course was synced
$lastupdate = $locallib->lastupdate();

// Get the codes for this course.
list($codes, $simplecodes) = $locallib->getcodes();

// Actions...
if ($action == 'sync') {
    $locallib->sync($output, $course);

} else if (($action == 'remove') || ($action == 'doremove')) {
    $users = $locallib->get_remove_users($simplecodes, $id);
    if ($action == 'doremove') {
        $locallib->remove_users($users);
        echo $output->continue_button(new \moodle_url('/report/guenrol/index.php', ['id' => $course->id]));
    } else {
        $userlist = new report_guenrol\output\userlist($course, $users, get_string('removelist', 'report_guenrol'));
        echo $output->render_userlist($userlist);
        if ($users) {
            $remove = new \single_button(new \moodle_url('/report/guenrol/index.php', ['id' => $course->id, 'action' => 'doremove']), get_string('remove', 'report_guenrol'));
            $cancel = new \single_button(new \moodle_url('/report/guenrol/index.php', ['id' => $course->id]), get_string('cancel'));
            echo $output->confirm(get_string('oktoremove', 'report_guenrol'), $remove, $cancel);
        } else {
            echo $output->continue_button(new \moodle_url('/report/guenrol/index.php', ['id' => $course->id]));
        }
    }

} else if (($action == 'revert') || ($action == 'dorevert')) {
    if ($action == 'dorevert') {
        $users = $locallib->get_code_users($simplecodes, $id);
        $locallib->get_instances($users);
        $count = $locallib->remove_users($users);
        $locallib->disable_enrolment($codes);
        echo $output->continue_button(new \moodle_url('/report/guenrol/index.php', ['id' => $course->id]));
    } else {
            $revert = new \single_button(new \moodle_url('/report/guenrol/index.php', ['id' => $course->id, 'action' => 'dorevert']), get_string('revert', 'report_guenrol'));
            $cancel = new \single_button(new \moodle_url('/report/guenrol/index.php', ['id' => $course->id]), get_string('cancel'));
            echo $output->confirm(get_string('oktorevert', 'report_guenrol'), $revert, $cancel);
    }
    
} else if (empty($codeid) && empty($action)) {
    $menu = new report_guenrol\output\menu(
        $course,
        $codes,
        $lastupdate
    );
    echo $output->render_menu($menu);
} else {

    if ($codeid) {
        $simplecodes = [$simplecodes[$codeid]];
    }

    // Get list of users
    $users = $locallib->get_code_users($simplecodes, $id);
    $userlist = new report_guenrol\output\userlist($course, $users, get_string('userlist', 'report_guenrol'));
    echo $output->render_userlist($userlist);
    echo $output->continue_button(new \moodle_url('/report/guenrol/index.php', ['id' => $course->id]));
}

echo $OUTPUT->footer();

