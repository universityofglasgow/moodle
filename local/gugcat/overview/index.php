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

use local_gugcat\grade_aggregation;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');

$courseid = required_param('id', PARAM_INT);
$categoryid = optional_param('categoryid', null, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

$url = new moodle_url('/local/gugcat/overview/index.php', array('id' => $courseid, 'page' => $page));
is_null($categoryid) ? null : $url->param('categoryid', $categoryid);
$indexurl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));
require_login($courseid);
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));
$PAGE->navbar->add(get_string('navname', 'local_gugcat'), $indexurl);


$PAGE->requires->css('/local/gugcat/styles/gugcat.css');
$PAGE->requires->js_call_amd('local_gugcat/main', 'init');
$course = get_course($courseid);

$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);
require_capability('local/gugcat:view', $coursecontext);

// Retrieve activities.
if (!is_null($categoryid)) {
    $activities = grade_aggregation::get_parent_child_activities($courseid, $categoryid);
} else {
    $activities = local_gugcat::get_activities($courseid);
}

// Retrieve groupingids from activities.
$groupingids = array_column($activities, 'groupingid');

// Retrieve students.
$limitfrom = $page * GCAT_MAX_USERS_PER_PAGE;
$limitnum  = GCAT_MAX_USERS_PER_PAGE;
$students = array();
// Params from search bar filters.
$filters = optional_param_array('filters', [], PARAM_NOTAGS);
$filters = local_gugcat::get_filters_from_url($filters);
$activesearch = isset($filters) && count($filters) > 0 && count(array_filter($filters)) > 0 ? true : false;
$activesearch ? $url->param('filter', http_build_query($filters)) : null;
$PAGE->set_url($url);

if (array_sum($groupingids) != 0) {
    $groups = array();
    foreach ($groupingids as $groupingid) {
        if ($groupingid != 0) {
            $groups += groups_get_all_groups($courseid, 0, $groupingid);
        }
    }
    $students = Array();
    $totalenrolled = 0;
    if (!empty($groups)) {
        foreach ($groups as $group) {
            if ($activesearch) {
                list($groupstudents, $count) = local_gugcat::get_filtered_students(
                    $coursecontext, $filters, $group->id, $limitfrom, $limitnum);
            } else {
                $count = count_enrolled_users($coursecontext, 'local/gugcat:gradable', $group->id);
                $groupstudents = get_enrolled_users($coursecontext, 'local/gugcat:gradable',
                $group->id, 'u.*', null, $limitfrom, $limitnum);
            }
            $totalenrolled += $count;
            $students += $groupstudents;
        }
    }
} else {
    if ($activesearch) {
        list($students, $totalenrolled) = local_gugcat::get_filtered_students($coursecontext, $filters, 0, $limitfrom, $limitnum);
    } else {
        $totalenrolled = count_enrolled_users($coursecontext, 'local/gugcat:gradable');
        $students = get_enrolled_users($coursecontext, 'local/gugcat:gradable', 0, 'u.*', null, $limitfrom, $limitnum);
    }
}

// Go back to first page when new search filters were submitted.
$filters = optional_param_array('filters', [], PARAM_NOTAGS);
if (count($filters) > 0 && $page > 0) {
    $url->remove_params('page');
    redirect($url);
}

$rows = grade_aggregation::get_rows($course, $activities, $students, true);

// Params for log.
$params = array(
    'context' => $coursecontext,
    'other' => array(
        'courseid' => $courseid,
        'categoryid' => $categoryid,
        'page' => $page
    )
);

$requireresit = optional_param('resit', null, PARAM_NOTAGS);
$finalrelease = optional_param('finalrelease', null, PARAM_NOTAGS);
$rowstudentid = optional_param('rowstudentno', null, PARAM_NOTAGS);
$downloadcsv = optional_param('downloadcsv', null, PARAM_NOTAGS);
$finalgrades = optional_param_array('finalgrades', null, PARAM_NOTAGS);
$cminstances = optional_param_array('cminstances', null, PARAM_NOTAGS);
// Process require resit on a student.
if (isset($requireresit) && !empty($rowstudentid)) {
    $status = grade_aggregation::require_resit($rowstudentid);

    // Log of require resit viewed.
    $resitparam = array(
        'context' => $coursecontext,
        'other' => array(
            'courseid' => $courseid,
            'categoryid' => $categoryid,
            'page' => $page,
            'status' => $status,
            'idnumber' => $students[$rowstudentid]->idnumber
        )
    );
    $event = \local_gugcat\event\require_resit::create($resitparam);
    $event->trigger();
    unset($requireresit);
    unset($rowstudentid);
    redirect($url);
    exit;

    // Process release final assessment grades for all students.
} else if (isset($finalrelease)) {
    grade_aggregation::release_final_grades($courseid);
    // Log of release final assessment grades.
    $event = \local_gugcat\event\release_final_assessment_grade::create($params);
    $event->trigger();
    unset($finalrelease);
    redirect($url);
    exit;

    // Process download aggregation tool.
} else if (isset($downloadcsv)) {
    // Log of release final assessment grades\.
    $event = \local_gugcat\event\export_aggregation::create($params);
    $event->trigger();
    grade_aggregation::export_aggregation_tool($course, $categoryid);
    unset($downloadcsv);
    redirect($url);
    exit;
}

// Log of aggregation tool viewed.
$event = \local_gugcat\event\aggregation_viewed::create($params);
$event->trigger();

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_aggregation_tool($rows, $activities);
echo $OUTPUT->paging_bar($totalenrolled, $page, $limitnum, $PAGE->url);
echo $OUTPUT->footer();
