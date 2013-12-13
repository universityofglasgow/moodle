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

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/lib/tablelib.php');

// Parameters.
$id = required_param('id', PARAM_INT); // Course id.
$codeid = optional_param('codeid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

$url = new moodle_url('/report/guenrol/index.php', array('id' => $id));

// Page setup.
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourse');
}

// Security.
require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('report/guenrol:view', $context);

// Log.
add_to_log($course->id, "course", "report guenrol", "report/guenrol/index.php?id=$course->id", $course->id);

$PAGE->set_title($course->shortname .': '. get_string('pluginname', 'report_guenrol'));
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading( get_string('title', 'report_guenrol') . ' : ' . $course->fullname );

// Re-sync the codes
if ($action=='sync') {
    $gudatabase = enrol_get_plugin('gudatabase');
    $gudatabase->course_updated(false, $course, null);
}

// Get the codes for this course.
$codes = $DB->get_records( 'enrol_gudatabase_codes', array('courseid' => $id));

// If codeid=0 we will just show the list of possible codes.
if (empty($codeid)) {

    // Sync link.
    $synclink = new moodle_url('/report/guenrol/index.php', array('id' => $id, 'action' => 'sync'));
    echo "<div><a class=\"btn\" href=\"$synclink\">" . get_string('synccourse', 'report_guenrol') . "</a></div>";

    if (empty($codes)) {
        echo '<div class="alert">' . get_string('nocodes', 'report_guenrol') . '</div>';
    } else {
        echo "<p>" . get_string('listofcodes', 'report_guenrol') . "</p>";
        echo '<ul id="guenrol_codes">';
        foreach ($codes as $code) {

            // Establish link for detailed display.
            $link = new moodle_url('/report/guenrol/index.php', array('id' => $id, 'codeid' => $code->id));
            echo "<li><a href=\"$link\">";
            echo "<strong>{$code->code}</strong></a> ";
            echo "\"{$code->coursename}\" ";
            echo "({$code->subjectname}) ";
            echo "</li>";
        }

        // If there is more than 1 show aggregated.
        if (count($codes) > 1) {
            $link = new moodle_url('/report/guenrol/index.php', array('id' => $id, 'codeid' => -1));
            echo "<li><a href=\"$link\">";
            echo "<strong>" . get_string('showall', 'report_guenrol') . "</strong></a> ";
            echo "</li>";
        }
        echo '</ul>';

        // Dropdown to get sort order.
    }
} else {

    // Get enrolment info.
    if ($codeid > -1) {
        $selectedcode = $codes[ $codeid ];
    } else {
        $selectedcode = null;
    }

    // Get users.
    if ($codeid > -1) {
        $codename = $codes[$codeid]->code;
        $coursename = $codes[$codeid]->coursename;
        $subjectname = $codes[$codeid]->subjectname;
        $users = $DB->get_records('enrol_gudatabase_users', array('courseid' => $id, 'code' => $codename));
    } else {
        $users = array();
        foreach ($codes as $code) {
            $codeusers = $DB->get_records('enrol_gudatabase_users', array('courseid' => $id, 'code' => $code->code));
            $users = array_merge($users, $codeusers);
        }
    }

    // Convert to unique userid table based on code.
    $uniqueusers = array();
    foreach ($users as $user) {
        if (empty($uniqueusers[ $user->userid ])) {
            $moodleuser = $DB->get_record('user', array('id' => $user->userid));
            $uniqueusers[ $user->userid ] = $user;
            $uniqueusers[ $user->userid ]->firstname = $moodleuser->firstname;
            $uniqueusers[ $user->userid ]->lastname = $moodleuser->lastname;
            $uniqueusers[ $user->userid ]->fullname = fullname( $moodleuser );
            $uniqueusers[ $user->userid ]->deleted = $moodleuser->deleted;
            $uniqueusers[ $user->userid ]->username = $moodleuser->username;
        } else {
            $uniqueusers[ $user->userid]->code .= ", {$user->code}";
        }
    }

    // Sort.
    usort( $uniqueusers, 'report_guenrol_sort' );

    // Some information.
    if ($codeid > -1) {
        echo "<p>" . get_string('enrolmentscode', 'report_guenrol', $codename) . ' ';
        echo get_string('coursename', 'report_guenrol', $coursename) . ' ';
        echo get_string('subjectname', 'report_guenrol', $subjectname) .'</p>';

    } else {
        echo "<p>" . get_string('usercodes', 'report_guenrol') . "<p>";
        echo "<ul>";
        foreach ($codes as $code) {
            echo "<li><strong>{$code->code}</strong> ";
            echo get_string('coursename', 'report_guenrol', $code->coursename) . ' ';
            echo get_string('subjectname', 'report_guenrol', $code->subjectname) . '</li>';
        }
        echo "</ul>";
    }

    // List users.
    echo "<ul id=\"guenrol_users\">";
    foreach ($uniqueusers as $user) {

        // Be sure not to show deleted accounts.
        if ($user->deleted) {
            continue;
        }

        // Display user (profile) link and data.
        $link = new moodle_url( '/user/profile.php', array('id' => $user->userid));
        echo "<li>";
        echo "<a href=\"$link\"><strong>{$user->username}</strong></a> ";
        echo $user->fullname;
        echo " <small>({$user->code})</small>";
        echo "</li>";
    }
    echo "</ul>";
    echo "<p>" . get_string('totalcodeusers', 'report_guenrol', count($users)) . "</p>";
}

echo $OUTPUT->footer();

// Callback function for sort.
function report_guenrol_sort( $a, $b ) {
    $afirstname = strtolower( $a->firstname );
    $alastname = strtolower( $a->lastname );
    $bfirstname = strtolower( $b->firstname );
    $blastname = strtolower( $b->lastname );

    if ($alastname == $blastname) {
        if ($afirstname == $bfirstname) {
            return 0;
        } else {
            return ($afirstname < $bfirstname) ? -1 : 1;
        }
    } else {
        return ($alastname < $blastname) ? -1 : 1;
    }
}
