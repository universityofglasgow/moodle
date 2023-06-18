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
// FETCH LTI IDs TO BE INCLUDED
$str_ltiinstancenottoinclude = get_ltiinstancenottoinclude();


$spdetailstype = required_param('spdetailstype', PARAM_TEXT);
$coursestype = required_param('coursestype', PARAM_TEXT);

$str_coursestype = "";

$myfirstlastname = $USER->firstname . " " . $USER->lastname;

$currentcourses = newassessments_statistics::return_enrolledcourses($USER->id, "current");
$str_currentcourses = implode(",", $currentcourses);

$pastcourses = newassessments_statistics::return_enrolledcourses($USER->id, "past");
$str_pastcourses = implode(",", $pastcourses);

$itemmodules = "'assign','forum','quiz','workshop'";

$thhd = 'border="1px" height="15" style="text-align:center;background-color: #ccc; border: 3px solid black;"';

$tdstl = 'border="1px" cellpadding="10" valign="middle" height="22" style="margin-left:10px;"';
$tdstc = 'border="1px" cellpadding="10" valign="middle" height="22" style="text-align:center;"';

$spdetailspdf = "No Courses found.";



if ($str_currentcourses!="" && $coursestype=="current") {

    $str_coursestype = "Current Courses";

    $str_itemsnotvisibletouser = newassessments_statistics::fetch_itemsnotvisibletouser($USER->id, $str_currentcourses);

//    $sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_currentcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id';

    $sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_currentcourses.') && gi.courseid>1 && gi.itemtype="mod" && (gi.iteminstance IN (' . $str_ltiinstancenottoinclude . ') && gi.itemmodule="lti") && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id';
//&& (gi.iteminstance IN ($str_ltiinstancenottoinclude) && gi.itemmodule='lti') &&

    $arr_cc = $DB->get_records_sql($sql_cc);

    $spdetailspdf = "<table width=100%>";
    $spdetailspdf .= '<tr style="font-weight: bold;">';

    $spdetailspdf .= '<th width="15%"' . $thhd . '>' . get_string('course') . '</th>';
    $spdetailspdf .= '<th width="15%"' . $thhd . '>' . get_string('assessment') . '</th>';
    $spdetailspdf .= '<th width="8%" ' . $thhd . '>' . get_string('activity') . ' type' . "</th>";
    $spdetailspdf .= '<th width="8%" ' . $thhd . '>' . get_string('assessmenttype', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="6%" ' . $thhd . '>' . get_string('source', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="5%" ' . $thhd . '>' . get_string('weight', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="7%" ' . $thhd . '>' . get_string('duedate','block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="10%" ' . $thhd . '>' . get_string('status') . "</th>";
    $spdetailspdf .= '<th width="11%" ' . $thhd . '>' . get_string('yourgrade', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="15%" ' . $thhd . '>' . get_string('feedback') . "</th>";

    $spdetailspdf .= "</tr>";

    $row = 6;

    foreach ($arr_cc as $key_cc) {
        $coursename = $key_cc->coursename;
        $assessment = $key_cc->itemname;
        $activitytype = $key_cc->itemmodule;

        $cmid = $key_cc->id;
        $modulename = $key_cc->itemmodule;
        $iteminstance = $key_cc->iteminstance;
        $courseid = $key_cc->courseid;
        $categoryid = $key_cc->categoryid;
        $itemid = $key_cc->id;
        $itemname = $key_cc->itemname;
        $aggregationcoef = $key_cc->aggregationcoef;
        $aggregationcoef2 = $key_cc->aggregationcoef2;
        $gradetype = $key_cc->gradetype;

        // FETCH ASSESSMENT TYPE
        $arr_gradecategory = $DB->get_record('grade_categories',array('courseid'=>$courseid, 'id'=>$categoryid));
        if (!empty($arr_gradecategory)) {
          $gradecategoryname = $arr_gradecategory->fullname;
        }

        $assessmenttype = newassessments_statistics::return_assessmenttype($gradecategoryname, $aggregationcoef);


        // FETCH INCLUDED IN GCAT
        $cfdvalue = 0;
        $inclgcat = "";
        $arr_customfield = $DB->get_record('customfield_field', array('shortname'=>'show_on_studentdashboard'));
        $cffid = $arr_customfield->id;

        $arr_customfielddata = $DB->get_record('customfield_data', array('fieldid'=>$cffid, 'instanceid'=>$courseid));

        if (!empty($arr_customfielddata)) {
              $cfdvalue = $arr_customfielddata->value;
        }

        if ($cfdvalue==1) {
            $inclgcat = "Old";
        }

        // FETCH WEIGHT
        $finalweight = get_weight($courseid,$categoryid,$aggregationcoef,$aggregationcoef2);


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

        // FETCH STATUS
        $gradestatus = newassessments_statistics::return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $USER->id);

        $status = $gradestatus["status"];
        $link = $gradestatus["link"];
        $allowsubmissionsfromdate = $gradestatus["allowsubmissionsfromdate"];
        $duedate = $gradestatus["duedate"];
        $cutoffdate = $gradestatus["cutoffdate"];

        $finalgrade = $gradestatus["finalgrade"];

        $statustodisplay = "";

        if($status == 'tosubmit'){
          $statustodisplay = '<a href="' . $link . '"><span class="status-item status-submit">'.get_string('submit').'</span></a> ';
        }
        if($status == 'notsubmitted'){
          $statustodisplay = '<span class="status-item">'.get_string('notsubmitted', 'block_newgu_spdetails').'</span> ';
        }
        if($status == 'submitted'){
          $statustodisplay = '<span class="status-item status-submitted">'. ucwords(trim(get_string('submitted', 'block_newgu_spdetails'))) . '</span> ';
          if ($finalgrade!=Null) {
            $statustodisplay = '<span class="status-item status-item status-graded">'.get_string('graded', 'block_newgu_spdetails').'</span>';
          }
        }
        if($status == "notopen"){
          $statustodisplay = '<span class="status-item">' . get_string('submissionnotopen', 'block_newgu_spdetails') . '</span> ';
        }
        if($status == "TO_BE_ASKED"){
          $statustodisplay = '<span class="status-item status-graded">' . get_string('individualcomponents', 'block_newgu_spdetails') . '</span> ';
        }
        if($status == "overdue"){
          $statustodisplay = '<span class="status-item status-overdue">' . get_string('overdue', 'block_newgu_spdetails') . '</span> ';
        }

        // FETCH YOUR Grade
        $arr_gradetodisplay = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $key_cc->grademax, $gradetype);
        $gradetodisplay = $arr_gradetodisplay["gradetodisplay"];


        // FETCH Feedback
        $link = "";

        $feedback = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $key_cc->grademax, $gradetype);
        $link = $feedback["link"];
        $gradetodisplay = $feedback["gradetodisplay"];

        if ($link!="") {
          $str_gradetodisplay = '<a href="' . $link . '">' . get_string('readfeedback', 'block_newgu_spdetails') . '</a>';
        } else {
          if ($modulename!="quiz") {
            $str_gradetodisplay = $gradetodisplay;
          }
        }


        $spdetailspdf .= "<tr>";

        $spdetailspdf .= "<td $tdstl>" . $coursename . "</td>";
        $spdetailspdf .= "<td $tdstl>" . $assessment . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $activitytype . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $assessmenttype . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $inclgcat . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $finalweight . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $str_duedate . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $statustodisplay . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $gradetodisplay . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $str_gradetodisplay . "</td>";

        $spdetailspdf .= "</tr>";

        $row++;
        $col = 0;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$coursename);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$assessment);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$activitytype);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$assessmenttype);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$inclgcat);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$finalweight);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$str_duedate);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>strip_tags($gradetodisplay));
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>strip_tags($gradetodisplay));
        $col++;

    }
    $spdetailspdf .= "</table>";

    //$spdetailspdf = "TESTING 1- TESTING 1- HELLO - HELLO";
}
//exit;


if ($coursestype=="past") {

    $pastxl = array();

    if ($str_pastcourses!="") {

    $str_coursestype = "Past Courses";

    $str_itemsnotvisibletouser = newassessments_statistics::fetch_itemsnotvisibletouser($USER->id, $str_pastcourses);

//    $sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_pastcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id';

    $sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_pastcourses.') && gi.courseid>1 && gi.itemtype="mod" && (gi.iteminstance IN (' . $str_ltiinstancenottoinclude . ') && gi.itemmodule="lti") && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id';

    $arr_cc = $DB->get_records_sql($sql_cc);

    $spdetailspdf = "<table width=100%>";
    $spdetailspdf .= '<tr style="font-weight: bold;">';

    $spdetailspdf .= '<th width="15%"' . $thhd . '>' . get_string('course') . '</th>';
    $spdetailspdf .= '<th width="15%"' . $thhd . '>' . get_string('assessment') . '</th>';
    $spdetailspdf .= '<th width="6%" ' . $thhd . '>' . get_string('activity') . ' type' . "</th>";
    $spdetailspdf .= '<th width="8%" ' . $thhd . '>' . get_string('assessmenttype', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="6%" ' . $thhd . '>' . get_string('source', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="5%" ' . $thhd . '>' . get_string('weight', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="7%" ' . $thhd . '>' . get_string('startdate','block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="7%" ' . $thhd . '>' . get_string('enddate','block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="8%" ' . $thhd . '>' . get_string('viewsubmission','block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="10%" ' . $thhd . '>' . get_string('yourgrade', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="13%" ' . $thhd . '>' . get_string('feedback') . "</th>";

    $spdetailspdf .= "</tr>";

    $row = 6;

    foreach ($arr_cc as $key_cc) {

        $col = 0;

        $coursename = $key_cc->coursename;
        $assessment = $key_cc->itemname;
        $activitytype = $key_cc->itemmodule;

        $cmid = $key_cc->id;
        $modulename = $key_cc->itemmodule;
        $iteminstance = $key_cc->iteminstance;
        $courseid = $key_cc->courseid;
        $categoryid = $key_cc->categoryid;
        $itemid = $key_cc->id;
        $itemname = $key_cc->itemname;
        $aggregationcoef = $key_cc->aggregationcoef;
        $aggregationcoef2 = $key_cc->aggregationcoef2;
        $gradetype = $key_cc->gradetype;

        // FETCH ASSESSMENT TYPE
        $arr_gradecategory = $DB->get_record('grade_categories',array('courseid'=>$courseid, 'id'=>$categoryid));
        if (!empty($arr_gradecategory)) {
          $gradecategoryname = $arr_gradecategory->fullname;
        }

        $assessmenttype = newassessments_statistics::return_assessmenttype($gradecategoryname, $aggregationcoef);


        // FETCH INCLUDED IN GCAT
        $cfdvalue = 0;
        $inclgcat = "";
        $arr_customfield = $DB->get_record('customfield_field', array('shortname'=>'show_on_studentdashboard'));
        $cffid = $arr_customfield->id;

        $arr_customfielddata = $DB->get_record('customfield_data', array('fieldid'=>$cffid, 'instanceid'=>$courseid));

        if (!empty($arr_customfielddata)) {
              $cfdvalue = $arr_customfielddata->value;
        }

        if ($cfdvalue==1) {
            $inclgcat = "Old";
        }

        // FETCH WEIGHT
        $finalweight = get_weight($courseid,$categoryid,$aggregationcoef,$aggregationcoef2);


        // START DATE
        $submissionstartdate = 0;
        $startdate = "";

        // READ individual TABLE OF ACTIVITY (MODULE)
        if ($modulename!="") {
          $arr_submissionstartdate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));

        if (!empty($arr_submissionstartdate)) {
          if ($modulename=="assign") {
            $submissionstartdate = $arr_submissionstartdate->allowsubmissionsfromdate;
          }
          if ($modulename=="forum") {
            $submissionstartdate = $arr_submissionstartdate->assesstimestart;
          }
          if ($modulename=="quiz") {
            $submissionstartdate = $arr_submissionstartdate->timeopen;
          }
          if ($modulename=="workshop") {
            $submissionstartdate = $arr_submissionstartdate->submissionstart;
          }
        }
      }

        if ($submissionstartdate!=0) {
          $startdate = date("d/m/Y", $submissionstartdate);
        }

        // END DATE
        $duedate = 0;
        $enddate = "";

        // READ individual TABLE OF ACTIVITY (MODULE)
        if ($modulename!="") {
          $arr_duedate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));


        if (!empty($arr_duedate)) {
          if ($modulename=="assign" || $modulename=="forum") {
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
          $enddate = date("d/m/Y", $duedate);
        }

        // VIEW SUBMISSIONS
        $link = "";

        $status="";

        $cmid = newassessments_statistics::get_cmid($modulename, $courseid, $iteminstance);

        $link = $CFG->wwwroot . '/mod/' . $modulename . '/view.php?id=' . $cmid;

        if (!empty($link)) {
            $viewsubmission = '<a href="' . $link . '">' . get_string('viewsubmission', 'block_newgu_spdetails') . '</a>';
            $viewsubmission_xls = '';
        }


        // FETCH YOUR Grade
        $arr_gradetodisplay = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $key_cc->grademax, $gradetype);
        $gradetodisplay = $arr_gradetodisplay["gradetodisplay"];


        // FETCH Feedback
        $link = "";

        $feedback = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $key_cc->grademax, $gradetype);
        $link = $feedback["link"];
        $gradetodisplay = $feedback["gradetodisplay"];

        if ($link!="") {
          $str_gradetodisplay = '<a href="' . $link . '">' . get_string('readfeedback', 'block_newgu_spdetails') . '</a>';
        } else {
          if ($modulename!="quiz") {
            $str_gradetodisplay = $gradetodisplay;
          }
        }


        $spdetailspdf .= "<tr>";

        $spdetailspdf .= "<td $tdstl>" . $coursename . "</td>";
        $spdetailspdf .= "<td $tdstl>" . $assessment . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $activitytype . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $assessmenttype . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $inclgcat . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $finalweight . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $startdate . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $enddate . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $viewsubmission . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $gradetodisplay . "</td>";
        $spdetailspdf .= "<td $tdstc>" . $str_gradetodisplay . "</td>";

        $spdetailspdf .= "</tr>";

        $row++;
        $col = 0;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$coursename);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$assessment);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$activitytype);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$assessmenttype);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$inclgcat);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$finalweight);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$startdate);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>$enddate);
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>strip_tags($gradetodisplay));
        $col++;
        $pastxl[$row][$col] = array("row"=>$row, "col"=>$col, "text"=>strip_tags($gradetodisplay));
        $col++;
    }
    $spdetailspdf .= "</table>";
  }
}

if ($spdetailstype=="pdf" && $spdetailspdf!="" && $str_coursestype!="") {

    require_once($CFG->libdir . '/pdflib.php');

    $doc = new pdf();

    $pathsw1b = 'img/uglogo03.png';
    $type = pathinfo($pathsw1b, PATHINFO_EXTENSION);
    $sw1bdata = file_get_contents($pathsw1b);

    $doc->SetFont('helvetica', '', 10);

// set default footer data
    $doc->setFooterData();

// set header and footer fonts
    $doc->setHeaderFont(Array('helvetica', 'b', 18));


// set margins
    $doc->SetMargins(5, 20, 5);
    $doc->SetHeaderMargin(50);
    $doc->setFooterMargin(10);

// set auto page breaks
    $doc->SetAutoPageBreak(TRUE, 15);

// set image scale factor
    $doc->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $doc->AddPage('L', 'A4');

    $doc->SetXY(5, 2);

    $doc->SetFont('helvetica', '', 20);
    $doc->SetXY(215, 15);
    $doc->Cell(25, 10, $myfirstlastname, 0, $ln=0, 'C', 0, '', 0, false, 'B', 'B');

    $doc->SetFont('helvetica', '', 9);

    $doc->SetXY(245, 20);
    $doc->Cell(25, 10, $str_coursestype . " Report Date : " . date("d-m-Y"), 0, $ln=0, 'C', 0, '', 0, false, 'B', 'B');

    $doc->SetMargins(5, 20, 5);

    $doc->SetFont('helvetica', '', 10);
    $doc->SetXY(5, 23);

// ----------------------------------------------------------------------------- -->

    $thtml = <<<EOD
$spdetailspdf
EOD;

    $c = $thtml ;

    $doc->writeHTML($c, true, false, false, false, '');

    $doc->Output('spdetails-' . $myfirstlastname . '_' . date("d-m-Y") . '.pdf', 'D');

    exit();


}

if ($spdetailstype=="excel" && $spdetailspdf!="" && $str_coursestype!="") {

  $filename = clean_filename($str_coursestype . " Report -" . $myfirstlastname . "_" . date("d-M-Y") . '.xls');

  /// Creating a workbook
  $workbook = new MoodleExcelWorkbook("-");
  /// Send HTTP headers
  $workbook->send($filename);

  $formatsetcenter = $workbook->add_format();
  $formatsetcenter->set_align('center');
  $formatsetcenter->set_v_align('center');

  $formatsetvcenter = $workbook->add_format();
  $formatsetvcenter->set_v_align('center');

  /// Creating the first worksheet
  $myxls = $workbook->add_worksheet("Sheet-1");

  $formatuname = $workbook->add_format();
  $formatuname->set_size(18);

  $formatbgcol = $workbook->add_format();
  $formatbgcol->set_align('center');
  $formatbgcol->set_v_align('center');
  $formatbgcol->set_border(0);
  $formatbgcol->set_color('white');
  $formatbgcol->set_bg_color('black');
  $formatbgcol->set_text_wrap();

  $bitmap = 'img/uglogo03.png';
  $myxls->insert_bitmap(0, 0, $bitmap, 2, 2, 1, 1);

  $myxls->merge_cells(0, 0, 3, 0);
  $myxls->write_string(2, 4, $myfirstlastname, $formatuname);

  $myxls->set_column(0, 1, 35);
  $myxls->set_column(2, 2, 10);
  $myxls->set_column(3, 3, 15);
  $myxls->set_column(4, 4, 10);
  $myxls->set_column(5, 5, 10);

  $myxls->write_string(4, 0, $str_coursestype . ' Report - ' . date("d/m/Y"));

  if ($coursestype=="current") {

    $rowhd = 6;
    $col = 0;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('course'));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string("assessment"));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string("activity") . " type");
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string("assessmenttype", "block_newgu_spdetails"));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string("source", "block_newgu_spdetails"));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('weight', 'block_newgu_spdetails'));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('duedate','block_newgu_spdetails'));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('yourgrade', 'block_newgu_spdetails'));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('feedback'));
    $col++;


      $myxls->set_column(6, 6, 10);
      $myxls->set_column(7, 7, 20);
      $myxls->set_column(8, 9, 25);
      $myxls->set_column(9, 9, 15);

  }

  if ($coursestype=="past") {
    $row++;
    $rowhd = 6;
    $col = 0;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('course'));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string("assessment"));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string("activity") . " type");
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string("assessmenttype", "block_newgu_spdetails"));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string("source", "block_newgu_spdetails"));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('weight', 'block_newgu_spdetails'));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('startdate','block_newgu_spdetails'));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('enddate','block_newgu_spdetails'));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('yourgrade', 'block_newgu_spdetails'));
    $col++;
    $pastxl[$row][$col] = array("row"=>$rowhd, "col"=>$col, "text"=>get_string('feedback'));
    $col++;

    $myxls->set_column(6, 6, 12);
    $myxls->set_column(7, 7, 12);
    $myxls->set_column(8, 9, 20);
    $myxls->set_column(9, 9, 20);
  }

  $rowheight = 22;

  foreach ($pastxl as $key_pastxl) {

    foreach($key_pastxl as $keykey_pastxl) {

      if ($keykey_pastxl["row"]==6) {
          $cellformat = $formatbgcol;
      } else {
          if ($keykey_pastxl["col"]>=2 && $keykey_pastxl["col"]<=7) {
            $cellformat = $formatsetcenter;
          } else {
            $cellformat = $formatsetvcenter;
          }
      }

      $myxls->set_row($keykey_pastxl["row"], $rowheight, null, false);
      $myxls->write_string($keykey_pastxl["row"], $keykey_pastxl["col"], $keykey_pastxl["text"], $cellformat);
    }

  }

  /// Close the workbook
  $workbook->close();
}
