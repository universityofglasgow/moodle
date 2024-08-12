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

require_once $CFG->dirroot . '/blocks/newgu_spdetails/locallib.php';
require_once "sduserdetails_table.php";

$courseid = optional_param('courseid', "", PARAM_INT);
$studentid = optional_param('studentid', "", PARAM_INT);

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

$context = context_course::instance($courseid);
$PAGE->set_context($context);

$currentcourses = \block_newgu_spdetails\course::return_enrolledcourses($studentid, "current", "student");
$str_currentcourses = implode(",", $currentcourses);

// FETCH LTI IDs TO BE INCLUDED
$str_ltiinstancenottoinclude = get_ltiinstancenottoinclude();

$ts = optional_param('ts', "", PARAM_ALPHA);
$tdr = optional_param('tdr', 1, PARAM_INT);

$addsort = "";
$assessmenttypeorder = "";
if ($ts == "assessmenttype") {
    $assessmenttypeorder = get_assessmenttypeorder("current", $tdr, $studentid);
    if ($assessmenttypeorder != "") {
        $addsort = " ORDER BY FIELD(gi.id, $assessmenttypeorder)";
    }
}

// This saves us having to hook into the other plugin's code, as the
// above and below code needs to do.
if ($ts == 'itemmodule') {
    $sortdirection = (($tdr == 3) ? "ASC" : "DESC");
    $addsort = " ORDER BY gi.itemmodule " . $sortdirection;
}

$duedateorder = "";
if ($ts == "duedate") {
    $duedateorder = get_duedateorder($tdr, $studentid);

    if ($duedateorder != "") {
        $addsort = " ORDER BY FIELD(gi.id, $duedateorder)";
    }
}

// Looks like when using the Staff View of the Student Dashboard,
// the generated objects were the same, table headings became un-sortable
// and broke things, hence...
$bytes = random_bytes(5);
$tableid = bin2hex($bytes);
$table = new sduserdetailscurrent_table($tableid);

$str_itemsnotvisibletouser = \block_newgu_spdetails\api::fetch_itemsnotvisibletouser($studentid, $courseid);

if ($str_currentcourses == "") {
    $str_currentcourses = "0";
}

if ($str_itemsnotvisibletouser != "") {
    $table->set_sql('gi.*, c.shortname as coursename,' . $studentid . ' as userid', "{grade_items} gi, {course} c", "gi.courseid in ("
        . $str_currentcourses . ") && gi.courseid=" . $courseid . " && ((gi.iteminstance IN ("
        . $str_ltiinstancenottoinclude . ") && gi.itemmodule='lti') OR gi.itemmodule!='lti') && gi.itemtype='mod' && gi.id not in ("
        . $str_itemsnotvisibletouser . ") && gi.courseid=c.id $addsort");
} else {
    $table->set_sql('gi.*, c.shortname as coursename,' . $studentid . ' as userid', "{grade_items} gi, {course} c", "gi.courseid in ("
        . $str_currentcourses . ") && gi.courseid=" . $courseid . " && ((gi.iteminstance IN ("
        . $str_ltiinstancenottoinclude . ") && gi.itemmodule='lti') OR gi.itemmodule!='lti') && gi.itemtype='mod' && gi.courseid=c.id"
        . $addsort);
}

$table->no_sorting('assessment');
$table->no_sorting('assessmenttype');
$table->no_sorting('weight');
$table->no_sorting('itemmodule');
$table->no_sorting('duedate');
$table->no_sorting('source');
$table->no_sorting('status');
$table->no_sorting('includedingcat');
$table->no_sorting('grade');
$table->no_sorting('feedback');

$table->define_baseurl("$CFG->wwwroot/local/gustaffview/sduserdetails.php?courseid=" . $courseid);
$table->out(20, true);
