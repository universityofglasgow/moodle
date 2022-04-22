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
use local_gugcat\grade_converter;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
require_once($CFG->dirroot . '/local/gugcat/classes/form/coursegradeform.php');

$courseid = required_param('id', PARAM_INT);
$formtype = required_param('setting', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$cnum = required_param('cnum', PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$alternativecg = optional_param('alternativecg', null, PARAM_INT);
$activityid = optional_param('activityid', null, PARAM_INT);
$activityid = $activityid == 0 ? null : $activityid;

require_login($courseid);
$urlparams = array('id' => $courseid, 'setting' => $formtype, 'studentid' => $studentid, 'cnum' => $cnum, 'page' => $page);
$url = new moodle_url('/local/gugcat/overview/gradeform/index.php', $urlparams);
(!is_null($categoryid) && $categoryid != 0) ? $url->param('categoryid', $categoryid) : null;
(!is_null($activityid) && $activityid != 0) ? $url->param('activityid', $activityid) : null;
!is_null($alternativecg) && $alternativecg != 0 ? $url->param('alternativecg', $alternativecg) : null;
$indexurl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));
$courseurl = new moodle_url('/local/gugcat/overview/index.php', array('id' => $courseid, 'page' => $page));
(!is_null($categoryid) && $categoryid != 0) ? $courseurl->param('categoryid', $categoryid) : null;

$PAGE->set_url($url);
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

$studentarr = $DB->get_records('user', array('id' => $studentid, 'deleted' => 0), MUST_EXIST);
$activities = array();
$gradetype = null;
// Retrieve activities.
if (!is_null($categoryid) && $categoryid != 0) {
    if (!is_null($activityid) && $formtype == 1) {
        // Retrieve sub cat activity object.
        $subcatactivity = local_gugcat::get_activity($courseid, $activityid);
        // Retrieve sub cat child components.
        $components = local_gugcat::get_activities($courseid, $subcatactivity->instance);
        $subcatactivity->children = array_column($components, 'gradeitemid');
        $activities = array_merge($components, [$subcatactivity]);
    } else {
        $activities = grade_aggregation::get_parent_child_activities($courseid, $categoryid);
    }
} else {
    $activities = local_gugcat::get_activities($courseid);
}
$rows = grade_aggregation::get_rows($course, $activities, $studentarr);
$student = $rows[0];
$student->cnum = $cnum; // Candidate no.
$student->id = $student->studentno;
$student->lastname = $student->surname;
$student->firstname = $student->forename;
// Prepare the data displayed if subcat activity.
if (!is_null($activityid) && $formtype == OVERRIDE_GRADE_FORM) {
    $subcatgrade = null;
    foreach ($student->grades as $key => $grade) {
        // Get the calculated sub category grade.
        if ($grade->is_subcat) {
            // Assign it to $subcatgrade.
            $subcatgrade = $grade;
            // Remove it from $student->grades.
            unset($student->grades[$key]);
            break;
        }
    }
    // Change the data in aggregate grade obj with $subcatgrade.
    $aggrdobj = new stdClass();
    $aggrdobj->scale = $subcatgrade->scale;
    $aggrdobj->grade = $subcatgrade->grade;
    $aggrdobj->rawgrade = $subcatgrade->rawgrade;
    $aggrdobj->display = $subcatgrade->rawgrade;
    $student->aggregatedgrade = $aggrdobj;
}
if (!is_null($alternativecg) && $alternativecg != 0) {
    if ($alternativecg == 1) {
        $aggrdobj = new stdClass();
        $aggrdobj->grade = $student->meritgrade->grade;
        $aggrdobj->rawgrade = $student->meritgrade->rawgrade;
        $student->aggregatedgrade = $aggrdobj;
        $student->grades = $student->meritgrade->grades;
    } else {
        $student->grades = $student->gpagrade->grades;
    }
}

if ($formtype == OVERRIDE_GRADE_FORM && $student->aggregatedgrade) {
    if (!is_null($activityid)) {
        if ($subcatactivity->is_converted || is_numeric($student->aggregatedgrade->grade)) {
            $gradetype = GRADE_TYPE_VALUE;
        } else {
            // Get scaleid of the first component.
            if (is_null($student->aggregatedgrade->scale)) {
                $scaleid = reset($components) ? reset($components)->scaleid : null;
                local_gugcat::set_grade_scale($scaleid, $student->aggregatedgrade->scale);
            } else {
                local_gugcat::set_grade_scale(null, $student->aggregatedgrade->scale);
            }
        }
    } else if (!is_null($alternativecg) && $alternativecg != 0) {
        local_gugcat::set_grade_scale(null);
    } else {
        local_gugcat::set_grade_scale(null, $student->aggregatedgrade->scale);
    }
}
// Params needed for logs.
$params = array(
    'context' => $coursecontext,
    'other' => array(
        'courseid' => $courseid,
        'categoryid' => $categoryid,
        'cnum' => $cnum,
        'idnumber' => $student->idnumber,
        'studentid' => $studentid,
        'alternativecg' => $alternativecg,
        'setting' => $formtype,
        'page' => $page
    )
);
$mform = new coursegradeform(null, array('setting' => $formtype, 'student' => $student, 'gradetype' => $gradetype));
if ($mform->is_cancelled()) {
    unset($SESSION->wantsurl);
    redirect($courseurl);
} else if ($mform->no_submit_button_pressed()) {
    $issubcat = !is_null($activityid) && isset($subcatactivity) && $subcatactivity->modname == 'category';
    $id = $issubcat ? $subcatactivity->instance : $categoryid;
    $itemname = null;
    if (!is_null($alternativecg) && $alternativecg != 0) {
        $itemname = get_string($alternativecg == GPA_GRADE ? 'gpagrade' : 'meritgrade', 'local_gugcat');
    } else {
        $itemname = get_string($issubcat ? 'subcategorygrade' : 'aggregatedgrade', 'local_gugcat');
    }
    $select = "courseid=$courseid AND itemname='$itemname' AND " . local_gugcat::compare_iteminfo();
    if ($gradeitem = $DB->get_record_select('grade_items', $select, ['iteminfo' => $id], 'id, idnumber, outcomeid')) {
        // If subcat get scaleid.
        $scale = $issubcat ? (!is_null($subcatactivity->is_converted) && !empty($subcatactivity->is_converted)
        ? $subcatactivity->is_converted : $gradeitem->outcomeid) : $gradeitem->idnumber;
        $revertstr = get_string('revertoverridden', 'local_gugcat');
        $notes = !is_null($alternativecg) && $alternativecg != 0 ? $revertstr
        : ($issubcat ? (!is_null($scale) && !empty($scale) ? "revertoverridden -$scale"
        : "revertoverridden") : "notes:$revertstr");
        $grdobj = new stdClass();
        $grdobj->id = $DB->get_field('grade_grades', 'id', array('userid' => $studentid, 'itemid' => $gradeitem->id));
        $grdobj->feedback = $notes;
        $grdobj->overridden = 0;
        $DB->update_record('grade_grades', $grdobj);
    }
    // Log of revert overriden grade.
    $event = \local_gugcat\event\revert_overridden_grade::create($params);
    $event->trigger();
    redirect($courseurl);
    exit;
} else if ($fromform = $mform->get_data()) {
    if ($formtype == OVERRIDE_GRADE_FORM) {
        $issubcat = !is_null($activityid) && isset($subcatactivity) && $subcatactivity->modname == 'category';
        $id = $issubcat ? $subcatactivity->instance : $categoryid;
        $itemname = null;
        if (!is_null($alternativecg) && $alternativecg != 0) {
            $itemname = get_string($alternativecg == GPA_GRADE ? 'gpagrade' : 'meritgrade', 'local_gugcat');
        } else {
            $itemname = get_string($issubcat ? 'subcategorygrade' : 'aggregatedgrade', 'local_gugcat');
        }
        $select = "courseid=$courseid AND itemname='$itemname' AND " . local_gugcat::compare_iteminfo();
        if ($gradeitem = $DB->get_record_select('grade_items', $select, ['iteminfo' => $id], 'id, idnumber')) {
            $grade = !is_numeric($fromform->override) ? array_search(strtoupper($fromform->override),
             local_gugcat::$grades) : $fromform->override;
            // If subcat get scaleid.
            $scale = $issubcat ? (!is_null($subcatactivity->is_converted) && !empty($subcatactivity->is_converted)
                ? $subcatactivity->is_converted : $DB->get_field('grade_items', 'outcomeid', array('id' => $gradeitem->id))) : null;
            // If scaleid is not empty or null, then add the scale to notes.
            $notes = !is_null($scale) && !empty($scale) ? $fromform->notes . " ,_scale:$scale" : $fromform->notes;
            if ($issubcat && $subcatactivity->is_converted) {
                /* If conversion is enabled, save the converted grade to
                provisional grade and original grade to converted grade.*/
                $conversion = grade_converter::retrieve_grade_conversion($subcatactivity->gradeitemid);
                $cg = grade_converter::convert($conversion, $grade);
                local_gugcat::update_grade($studentid, $gradeitem->id, $cg, $notes, time());
                $convertedgi = local_gugcat::get_grade_item_id($COURSE->id, $subcatactivity->gradeitemid,
                 get_string('convertedgrade', 'local_gugcat'));
                local_gugcat::update_grade($studentid, $convertedgi, $grade);
            } else {
                $notes = !is_null($alternativecg) && $alternativecg != 0 ? $fromform->notes
                    : (!$issubcat ? ",_scale: $gradeitem->idnumber ,_notes: $fromform->notes"
                        : (is_null($scale) ? $fromform->notes : $fromform->notes . " ,_scale:$scale"));
                local_gugcat::update_grade($studentid, $gradeitem->id, $grade, $notes, time());
            }

            // Also update notes for subcomponents.
            if ($issubcat) {
                $prvgrades = local_gugcat::get_prvgrd_item_ids($courseid, $components);
                $componentnotes = $fromform->notes;
                foreach ($prvgrades as $prvgrades) {
                    $scale = $DB->get_field('grade_items', 'idnumber', array("id" => $prvgrades->id));
                    $notes = $scale ? $componentnotes . " -" . $scale : $componentnotes;
                    local_gugcat::update_components_notes($studentid, $prvgrades->id, $notes);
                }
            }
            // Log of override grades.
            $event = \local_gugcat\event\override_course_grade::create($params);
            $event->trigger();
        } else {
            local_gugcat::notify_error('overrideerror');
        }
    } else if ($formtype == ADJUST_WEIGHT_FORM) {
        $weights = $fromform->weights;
        $notes = "notes: $fromform->notes";

        $aggradeid = local_gugcat::get_grade_item_id($courseid, $categoryid, get_string('aggregatedgrade', 'local_gugcat'));
        $aggradeobj = new stdClass();
        $aggradeobj->id = $DB->get_field('grade_grades', 'id', array('itemid' => $aggradeid, 'userid' => $studentid));
        $aggradeobj->feedback = $notes;
        $aggradeobj->overridden = 0;
        $DB->update_record('grade_grades', $aggradeobj);
        grade_aggregation::adjust_course_weight($weights, $courseid, $studentid);
        // Log of adjust course weight.
        $event = \local_gugcat\event\adjust_course_weight::create($params);
        $event->trigger();
    }
    redirect($courseurl);
    exit;
}

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_gugcat');
echo $renderer->display_adjust_override_grade_form($student);
$mform->display();
echo $OUTPUT->footer();
