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
 * Contains the class for the UofG Assessments Details block.
 *
 * @package    block_gu_spdetails
 * @copyright  2021 University of Glasgow
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/lib.php');

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/gu_spdetails/index.php');
$PAGE->set_title(get_string('title', 'block_gu_spdetails'));
$PAGE->set_heading(get_string('heading', 'block_gu_spdetails'));
$PAGE->set_pagelayout('report');

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('navtitle', 'block_gu_spdetails'), new moodle_url('/blocks/gu_spdetails/index.php'));

$PAGE->requires->js_call_amd('block_gu_spdetails/main', 'init');

$config = get_config('block_gu_spdetails');

if ($courses = assessments_details::return_enrolledcourses($USER->id)) {
    $courseids = implode(', ', $courses);
    $count = assessments_details::return_assessments_count($USER->id, $courseids);

    $submittedstr = ($count->submitted == 1) ? get_string('assessment', 'block_gu_spdetails').
                                                get_string('submitted', 'block_gu_spdetails') :
                                                get_string('assessments', 'block_gu_spdetails').
                                                get_string('submitted', 'block_gu_spdetails');
    $markedstr = ($count->marked == 1) ? get_string('assessment', 'block_gu_spdetails').
                                          get_string('marked', 'block_gu_spdetails') :
                                          get_string('assessments', 'block_gu_spdetails').
                                          get_string('marked', 'block_gu_spdetails');

    $assessmentssubmittedicon = $OUTPUT->image_url('assessments_submitted', 'theme');
    $assessmentstosubmiticon = $OUTPUT->image_url('assessments_tosubmit', 'theme');
    $assessmentsoverdueicon = $OUTPUT->image_url('assessments_overdue', 'theme');
    $assessmentsmarkedicon = $OUTPUT->image_url('assessments_marked', 'theme');
    $templatecontext = (array)[
        'assessments_submitted'        => $count->submitted,
        'assessments_tosubmit'         => $count->tosubmit,
        'assessments_overdue'          => $count->overdue,
        'assessments_marked'           => $count->marked,
        'assessments_submitted_icon'   => $assessmentssubmittedicon,
        'assessments_tosubmit_icon'    => $assessmentstosubmiticon,
        'assessments_overdue_icon'     => $assessmentsoverdueicon,
        'assessments_marked_icon'      => $assessmentsmarkedicon,
        'assessments_submitted_str'    => $submittedstr,
        'assessments_tosubmit_str'     => get_string('tobesubmitted', 'block_gu_spdetails'),
        'assessments_overdue_str'      => get_string('overdue', 'block_gu_spdetails'),
        'assessments_marked_str'       => $markedstr,
        'tab_current'       => get_string('tab_current', 'block_gu_spdetails'),
        'tab_past'          => get_string('tab_past', 'block_gu_spdetails'),
        'tab_asessments'    => get_string('returnallassessment', 'block_gu_spdetails'),
        'label_course'      => get_string('label_course', 'block_gu_spdetails'),
        'label_assessment'  => get_string('label_assessment', 'block_gu_spdetails'),
        'label_weight'      => get_string('label_weight', 'block_gu_spdetails'),
        'label_grade'       => get_string('label_grade', 'block_gu_spdetails'),
        'message' => $config->messagedatapage,
        'showmessage' => !empty($config->messagedatapage),
        'showdetails' => true,
    ];
    
} else {
    $templatecontext = [
        'showdetails' => false,
        'message' => $config->messagenodata,
    ];
}
$content = $OUTPUT->render_from_template('block_gu_spdetails/spdetails', $templatecontext);

echo $OUTPUT->header();
echo $content;
echo $OUTPUT->footer();