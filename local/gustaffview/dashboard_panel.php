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
 * This file uses Moodle's Table API to output student assessment data for
 * Staff/Managers(tbc)
 *
 * The view of the Student Dashboard for Staff/Managers. Read only data.
 * Called as part of a fetch() request - this will need to update
 * the content block only when paginating or sorting by columns.
 *
 * @package    local_gustaffview
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';

defined('MOODLE_INTERNAL') || die();

global $CFG, $USER, $DB;

require_once 'sduserdetails_table.php';

$courseid = optional_param('courseid', '', PARAM_INT);
$studentid = optional_param('studentid', '', PARAM_INT);

$url = new moodle_url('/local/gustaffview/dashboard_panel.php', [
    'courseid' => $courseid,
    'studentid' => $studentid
]);
$PAGE->set_url($url);

if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new \moodle_exception('invalidcourseid');
}

// As this is a separate 'panel' script, prevent any inadvertent access
require_login($course);

// Make sure the student is indeed enrolled on this course.
if (!\block_newgu_spdetails\api::return_isstudent($courseid, $studentid)) {
    throw new \moodle_exception('notenroled');
}

$context = context_course::instance($courseid);
$PAGE->set_context($context);

// Don't include activities that are essentially LTI configured.
$ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
$str_ltiinstancenottoinclude = implode(',',$ltiactivities);

// Looks like when using the Student MyGrades Staff View, generated objects 
// were the same, table headings became un-sortable and broke things, hence...
$bytes = random_bytes(5);
$tableid = bin2hex($bytes);
$table = new sduserdetailscurrent_table($tableid);

$whereclause = 'gi.courseid = ' . $courseid . ' AND (gi.itemtype IN ("mod", "manual") AND (gi.itemmodule IS NULL OR '
. 'gi.itemmodule NOT IN ("attendance","game", "lti"))) AND gi.courseid = c.id AND gc.courseid = c.id AND gi.display = 0 AND '
. 'cm.course = c.id AND (gi.iteminstance = cm.instance OR gi.iteminstance IS NULL) GROUP BY gi.id ORDER BY gi.itemname ASC';

$table->set_sql('gi.id,gi.courseid,gi.categoryid,CASE WHEN cm.visible = 0 THEN CONCAT("<i class=\'icon fa '
    . 'fa-eye-slash fa-fw\' title=\'This activity is currently hidden on the course page.\' alt=\'This activity is currently '
    . 'hidden on the course page.\' aria-hidden=\'true\' role=\'img\' aria-label=\'This activity is currently hidden on the '
    . 'course page.\'></i>", gi.itemname) ELSE gi.itemname END AS itemname, gi.itemtype, gi.itemmodule, gi.iteminstance, '
    . 'gi.gradetype, gi.grademax, gi.scaleid, gi.aggregationcoef, gi.display, gi.hidden, gi.locked, '
    . 'c.shortname as coursename, cm.visible, cm.visibleoncoursepage, ' . $studentid . ' AS '
    . 'userid, gc.aggregation', '{grade_items} gi, {course} c, '
    . '{grade_categories} gc, mdl_course_modules cm', $whereclause);

$table->no_sorting('assessment');
$table->no_sorting('assessmenttype');
$table->no_sorting('weight');
$table->no_sorting('itemmodule');
$table->no_sorting('duedate');
$table->no_sorting('source');
$table->no_sorting('status');
$table->no_sorting('source');
$table->no_sorting('grade');
$table->no_sorting('feedback');

$table->define_baseurl($CFG->wwwroot . '/local/gustaffview/sduserdetails.php?courseid=' . $courseid);
$table->out(20, true);
