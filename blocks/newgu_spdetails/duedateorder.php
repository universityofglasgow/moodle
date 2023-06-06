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
 * New GU SP Details
 * @package    block_newgu_spdetails
 * @copyright  2023 NEW GU
 * @author
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '../../config.php');
require_once("$CFG->libdir/excellib.class.php");
//require_once("excellib.class.php");

require_once('locallib.php');
require_once('assessment_table.php');

defined('MOODLE_INTERNAL') || die();
global $PAGE, $CFG, $DB, $OUTPUT,$USER;
$PAGE->set_context(context_system::instance());
require_login();
$usercontext = context_user::instance($USER->id);

$spdetailstype = required_param('spdetailstype', PARAM_TEXT);
$coursestype = required_param('coursestype', PARAM_TEXT);

$str_coursestype = "";

$myfirstlastname = $USER->firstname . " " . $USER->lastname;

$currentcourses = newassessments_statistics::return_enrolledcourses($USER->id, "current");
$str_currentcourses = implode(",", $currentcourses);

$pastcourses = newassessments_statistics::return_enrolledcourses($USER->id, "past");
$str_pastcourses = implode(",", $pastcourses);

//TEMPORARY - DELETE AFTER COMPLETION
//$str_currentcourses = $str_pastcourses;

$itemmodules = "'assign','forum','quiz','workshop'";

$thhd = 'border="1px" height="15" style="text-align:center;background-color: #ccc; border: 3px solid black;"';

$tdstl = 'border="1px" cellpadding="10" valign="middle" height="22" style="margin-left:10px;"';
$tdstc = 'border="1px" cellpadding="10" valign="middle" height="22" style="text-align:center;"';

$spdetailspdf = "No Courses found.";



if ($str_currentcourses!="" && $coursestype=="current") {

    $str_coursestype = "Current Courses";

    $str_itemsnotvisibletouser = newassessments_statistics::fetch_itemsnotvisibletouser($USER->id, $str_currentcourses);

    $sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_currentcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id';

$arr_cc = $DB->get_records_sql($sql_cc);

$arr_order = array();

foreach ($arr_cc as $key_cc) {
//    $coursename = $key_cc->coursename;
//    $assessment = $key_cc->itemname;
//    $activitytype = $key_cc->itemmodule;

    $cmid = $key_cc->id;
    $modulename = $key_cc->itemmodule;
    $iteminstance = $key_cc->iteminstance;
    $courseid = $key_cc->courseid;
//    $categoryid = $key_cc->categoryid;
    $itemid = $key_cc->id;
//    $itemname = $key_cc->itemname;
//    $aggregationcoef = $key_cc->aggregationcoef;
//    $aggregationcoef2 = $key_cc->aggregationcoef2;
//    $gradetype = $key_cc->gradetype;


    // DUE DATE
    $duedate = 0;
    $extspan = "";
    $extensionduedate = 0;
    $str_duedate = "—";

    // READ individual TABLE OF ACTIVITY (MODULE)
    if ($modulename!="") {
      $arr_duedate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));

    if (!empty($arr_duedate)) {
      if ($modulename=="assign") {
        $duedate = $arr_duedate->duedate;

        $arr_userflags = $DB->get_record('assign_user_flags', array('userid'=>$USER->id, 'assignment'=>$iteminstance));

        if ($arr_userflags) {
        $extensionduedate = $arr_userflags->extensionduedate;
        if ($extensionduedate>0) {
          $extspan = '<a href="javascript:void(0)" title="' . get_string('extended', 'block_newgu_spdetails') . '" class="extended">*</a>';
        }
        }

      }
      if ($modulename=="forum") {
        $duedate = $arr_duedate->duedate;
      }
      if ($modulename=="quiz") {
        $duedate = $arr_duedate->timeclose;
      }
      if ($modulename=="workshop") {
        $duedate = $arr_duedate->submissionend;
      }
    }
  }

    if ($duedate!=0) {
      $str_duedate = date("d/m/Y", $duedate) . $extspan;
    }

    $arr_order[$itemid] = $duedate;
//    $arr_order2[$duedate] = $itemid;
}



echo "<pre>";
print_r($arr_order);
echo "<br><br/>-----<br/><br/>";
asort($arr_order);
print_r($arr_order);
echo "</pre>";

$str_order = "";
foreach ($arr_order as $key_order=>$value) {
  $str_order .= $key_order . ",";
}
$str_order = rtrim($str_order,",");

echo $str_order;


$sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_currentcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id ORDER BY FIELD(gi.id, ' . $str_order . ')';

$arr_cc = $DB->get_records_sql($sql_cc);

echo "<pre>";
print_r($arr_cc);
echo "</pre>";

    //$spdetailspdf = "TESTING 1- TESTING 1- HELLO - HELLO";
}



if ($coursestype=="past") {

    $pastxl = array();

    if ($str_pastcourses!="") {

    $str_itemsnotvisibletouser = newassessments_statistics::fetch_itemsnotvisibletouser($USER->id, $str_pastcourses);

    $sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_pastcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id';

    $arr_cc = $DB->get_records_sql($sql_cc);


    $arr_sdorder = array();
    $arr_edorder = array();

    foreach ($arr_cc as $key_cc) {


        // $coursename = $key_cc->coursename;
        // $assessment = $key_cc->itemname;
        // $activitytype = $key_cc->itemmodule;

        $cmid = $key_cc->id;
        $modulename = $key_cc->itemmodule;
        $iteminstance = $key_cc->iteminstance;
        $courseid = $key_cc->courseid;
        $categoryid = $key_cc->categoryid;
        $itemid = $key_cc->id;
        // $itemname = $key_cc->itemname;
        $aggregationcoef = $key_cc->aggregationcoef;
        $aggregationcoef2 = $key_cc->aggregationcoef2;
        // $gradetype = $key_cc->gradetype;

        // FETCH ASSESSMENT TYPE
        $arr_gradecategory = $DB->get_record('grade_categories',array('courseid'=>$courseid, 'id'=>$categoryid));
        if (!empty($arr_gradecategory)) {
          $gradecategoryname = $arr_gradecategory->fullname;
        }

        $assessmenttype = newassessments_statistics::return_assessmenttype($gradecategoryname, $aggregationcoef);


        // START DATE
        $submissionstartdate = 0;
        $startdate = "";
        $duedate = 0;
        $enddate = "";

        // READ individual TABLE OF ACTIVITY (MODULE)
        if ($modulename!="") {
          $arr_submissionstartdate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));

        if (!empty($arr_submissionstartdate)) {
          if ($modulename=="assign") {
            $submissionstartdate = $arr_submissionstartdate->allowsubmissionsfromdate;
            $duedate = $arr_submissionstartdate->duedate;
          }
          if ($modulename=="forum") {
            $submissionstartdate = $arr_submissionstartdate->assesstimestart;
            $duedate = $arr_submissionstartdate->duedate;
          }
          if ($modulename=="quiz") {
            $submissionstartdate = $arr_submissionstartdate->timeopen;
            $duedate = $arr_submissionstartdate->timeclose;
          }
          if ($modulename=="workshop") {
            $submissionstartdate = $arr_submissionstartdate->submissionstart;
            $duedate = $arr_submissionstartdate->submissionend;
          }
        }
      }


          $startdate = date("d/m/Y", $submissionstartdate);
          $arr_sdorder[$itemid] = $submissionstartdate;



          $enddate = date("d/m/Y", $duedate);
          $arr_edorder[$itemid] = $duedate;


    }

  }
}

echo "<pre>";
echo "<br/>START DATE : <br>";
print_r($arr_sdorder);
echo "<br><br/>-----<br/><br/>";
asort($arr_sdorder);
print_r($arr_sdorder);
echo "<br><br/>+++++++++<br/><br/>";
echo "<br/>END DATE : <br>";
print_r($arr_edorder);
echo "<br><br/>-----<br/><br/>";
asort($arr_edorder);
print_r($arr_edorder);
echo "</pre>";


$str_sdorder = "";
foreach ($arr_sdorder as $key_order=>$value) {
  $str_sdorder .= $key_order . ",";
}
$str_sdorder = rtrim($str_sdorder,",");

echo $str_sdorder;


$sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_pastcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id ORDER BY FIELD(gi.id, ' . $str_sdorder . ')';

$arr_cc = $DB->get_records_sql($sql_cc);

echo "<pre>";
print_r($arr_cc);
echo "</pre>";


$str_edorder = "";
foreach ($arr_edorder as $key_order=>$value) {
  $str_edorder .= $key_order . ",";
}
$str_edorder = rtrim($str_edorder,",");

echo $str_edorder;


$sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_pastcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id ORDER BY FIELD(gi.id, ' . $str_edorder . ')';

$arr_cc = $DB->get_records_sql($sql_cc);

echo "<pre>";
print_r($arr_cc);
echo "</pre>";
