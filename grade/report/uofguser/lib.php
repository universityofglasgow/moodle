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
 * Callback implementations for Student MyGrades Staff View
 *
 * @package   gradereport_uofguser
 * @copyright 2007 Nicolas Connault
 * @copyright 2025 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 use core_user\output\myprofile\tree;

 defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->libdir.'/tablelib.php');

define("GRADE_REPORT_UOFGUSER_HIDE_HIDDEN", 0);
define("GRADE_REPORT_UOFGUSER_HIDE_UNTIL", 1);
define("GRADE_REPORT_UOFGUSER_SHOW_HIDDEN", 2);

define("GRADE_REPORT_UOFGUSER_VIEW_SELF", 1);
define("GRADE_REPORT_UOFGUSER_VIEW_USER", 2);

/**
 * Set up the settings for the uofguser grade report.
 *
 * @param \moodleform $mform The form to add the settings to.
 * @return void
 */
function grade_report_uofguser_settings_definition(&$mform) {
    global $CFG;

    // Commom options.
    $options = [
        -1 => get_string('default', 'grades'),
        0 => get_string('hide'),
        1 => get_string('show'),
    ];

    // Show rank setting.
    if (empty($CFG->grade_report_uofguser_showrank)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showrank', get_string('showrank', 'grades'), $options);
    $mform->addHelpButton('report_uofguser_showrank', 'showrank', 'grades');

    // Show source setting - MyGrades specific.
    if (empty($CFG->grade_report_uofguser_showsource)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showsource', get_string('showsource', 'gradereport_uofguser'), $options);
    $mform->addHelpButton('report_uofguser_showsource', 'showsource', 'gradereport_uofguser');

    // Show status setting - MyGrades specific.
    if (empty($CFG->grade_report_uofguser_showstatus)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showstatus', get_string('showstatus', 'gradereport_uofguser'), $options);
    $mform->addHelpButton('report_uofguser_showstatus', 'showstatus', 'gradereport_uofguser');

    // Show percentage setting.
    if (empty($CFG->grade_report_uofguser_showpercentage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showpercentage', get_string('showpercentage', 'grades'), $options);
    $mform->addHelpButton('report_uofguser_showpercentage', 'showpercentage', 'grades');

    // Show grade setting.
    if (empty($CFG->grade_report_uofguser_showgrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showgrade', get_string('showgrade', 'grades'), $options);

    // Show feedback setting.
    if (empty($CFG->grade_report_uofguser_showfeedback)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showfeedback', get_string('showfeedback', 'grades'), $options);

    // Show weight setting.
    if (empty($CFG->grade_report_uofguser_showweight)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showweight', get_string('showweight', 'grades'), $options);

    // Show average setting.
    if (empty($CFG->grade_report_uofguser_showaverage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showaverage', get_string('showaverage', 'grades'), $options);
    $mform->addHelpButton('report_uofguser_showaverage', 'showaverage', 'grades');

    // Show letter grade setting.
    if (empty($CFG->grade_report_uofguser_showlettergrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showlettergrade', get_string('showlettergrade', 'grades'), $options);
    if (empty($CFG->grade_report_uofguser_showcontributiontocoursetotal)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_uofguser_showcontributiontocoursetotal]);
    }

    // Show contribution to course total setting.
    $mform->addElement(
        'select',
        'report_uofguser_showcontributiontocoursetotal',
        get_string('showcontributiontocoursetotal', 'grades'),
        $options);
    $mform->addHelpButton('report_uofguser_showcontributiontocoursetotal', 'showcontributiontocoursetotal', 'grades');

    // Show range setting.
    if (empty($CFG->grade_report_uofguser_showrange)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_uofguser_showrange', get_string('showrange', 'grades'), $options);

    // Range decimals setting.
    $options = [
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
    ];

    if (!empty($CFG->grade_report_uofguser_rangedecimals)) {
        $options[-1] = $options[$CFG->grade_report_uofguser_rangedecimals];
    }
    $mform->addElement('select', 'report_uofguser_rangedecimals', get_string('rangedecimals', 'grades'), $options);

    // Show hidden items setting.
    $options = [
        -1 => get_string('default', 'grades'),
        0 => get_string('shownohidden', 'grades'),
        1 => get_string('showhiddenuntilonly', 'grades'),
        2 => get_string('showallhidden', 'grades'),
    ];

    if (empty($CFG->grade_report_uofguser_showhiddenitems)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_uofguser_showhiddenitems]);
    }

    $mform->addElement('select', 'report_uofguser_showhiddenitems', get_string('showhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_uofguser_showhiddenitems', 'showhiddenitems', 'grades');

    // Show totals if contain hidden items setting.
    $options = [
        -1 => get_string('default', 'grades'),
        GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN => get_string('hide'),
        GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowexhiddenitems', 'grades'),
        GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowinchiddenitems', 'grades'),
    ];

    if (empty($CFG->grade_report_uofguser_showtotalsifcontainhidden)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_uofguser_showtotalsifcontainhidden]);
    }

    $mform->addElement(
        'select',
        'report_uofguser_showtotalsifcontainhidden',
        get_string('hidetotalifhiddenitems', 'grades'),
        $options);
    $mform->addHelpButton('report_uofguser_showtotalsifcontainhidden', 'hidetotalifhiddenitems', 'grades');

}

/**
 * Profile report callback.
 *
 * @param object $course The course.
 * @param object $user The user.
 * @param bool $viewasuser True when we are viewing this as the targetted user sees it.
 * @return void
 */
function grade_report_uofguser_profilereport(object $course, object $user, bool $viewasuser = false) {
    if (!empty($course->showgrades)) {

        $context = context_course::instance($course->id);

        // Fetch the return tracking object.
        $gpr = new grade_plugin_return(
            ['type' => 'report', 'plugin' => 'uofguser', 'courseid' => $course->id, 'userid' => $user->id]
        );
        // Create a report instance.
        $report = new gradereport_uofguser\report\uofguser($course->id, $gpr, $context, $user->id, $viewasuser);

        // Print the page.
        // A css fix to share styles with real report page.
        echo '<div class="grade-report-user">';
        if ($report->fill_table()) {
            echo $report->print_table(true);
        }
        echo '</div>';
    }
}

/**
 * Returns a map of status keys to their corresponding moodle pix_icon HTML.
 *
 * @param moodle_page $OUTPUT
 * @return array
 */
function gradereport_uofguser_status_icons($OUTPUT) {
    $statuses = [
        'status_text_submissionunavailable' => ['e/cancel', 'status_text_submissionunavailable'],
        'status_text_submissionnotopen' => ['i/lock', 'status_text_submissionnotopen'],
        'status_text_hidden' => ['i/show', 'status_text_hidden'],
        'status_text_overdue' => ['i/risk_dataloss', 'status_text_overdue'],
        'status_text_submit' => ['i/cloudupload', 'status_text_submit'],
        'status_text_submitted' => ['i/completion_self', 'status_text_submitted'],
        'status_text_notsubmitted' => ['i/excluded', 'status_text_notsubmitted'],
        'status_text_graded' => ['i/grade_correct', 'status_text_graded'],
        'status_text_tobeconfirmed' => ['i/uncheckedcircle', 'status_text_tobeconfirmed'],
    ];

    $icons = [];
    foreach ($statuses as $key => [$icon, $strkey]) {
        $icons[$key] = $OUTPUT->pix_icon(
            $icon,
            get_string($strkey, 'gradereport_uofguser'),
            'moodle'
        );
    }
    return $icons;
}

/**
 * Find the first instance of a scale of type 'schedule' in the plugin config.
 * This is used to identify scales if there is a converted grade item.
 *
 * @param string $schedule The schedule type to look for, defaults to 'schedulea'.
 * @return int|null The scale number if found, or null if not found.
 */
function gradereport_uofguser_schedulescale_map($schedule = 'schedulea') {
    $configs = get_config('local_gugrades');
    foreach ($configs as $name => $value) {
        if (strpos($name, 'scaletype_') === 0 && $value === $schedule) {
            $number = substr($name, strlen('scaletype_'));
            break;
        }
    }
    return (int) $number ?? null;
}
