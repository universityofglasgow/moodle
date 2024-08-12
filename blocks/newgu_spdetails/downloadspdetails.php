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
 * Export Grade data from the student dashboard.
 * MGU-572 - This file accepts a course and export type, and generates
 * either a PDF or CSV file which is then becomes available to download.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk> - updated this file.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '../../config.php');
require_once("$CFG->libdir/excellib.class.php");
require_once('locallib.php');

defined('MOODLE_INTERNAL') || die();

global $PAGE, $CFG, $DB, $OUTPUT, $USER;
$PAGE->set_context(context_system::instance());
require_login();
$spdetailstype = required_param('spdetailstype', PARAM_TEXT);
$coursestype = required_param('coursestype', PARAM_TEXT);
$strcoursestype = "";
$myfirstlastname = $USER->firstname . " " . $USER->lastname;
$sortstring = 'shortname asc';

$thhd = 'border="1px" height="15" style="text-align:center;background-color: #ccc; border: 3px solid black;"';
$tdstl = 'border="1px" cellpadding="10" valign="middle" height="22" style="margin-left:10px;"';
$tdstc = 'border="1px" cellpadding="10" valign="middle" height="22" style="text-align:center;"';
$spdetailspdf = get_string('nocoursesfound', 'block_newgu_spdetails');

if ($coursestype) {
    switch ($coursestype) {
        case "current":
            $strcoursestype = get_string('currentcourses', 'block_newgu_spdetails');
            $courses = \local_gugrades\api::dashboard_get_courses($USER->id, true, false, $sortstring);
            $cellwidth = 157;
        break;
        case "past":
            $strcoursestype = get_string('pastcourses', 'block_newgu_spdetails');
            $courses = \local_gugrades\api::dashboard_get_courses($USER->id, false, true, $sortstring);
            $cellwidth = 148;
        break;
        default:
            $strcoursestype = get_string('currentcourses', 'block_newgu_spdetails');
            $courses = \local_gugrades\api::dashboard_get_courses($USER->id, true, false, $sortstring);
        break;
    }

    $spdetailspdf = "<table width=100%>";
    $spdetailspdf .= '<tr style="font-weight: bold;">';
    $spdetailspdf .= '<th width="22%"' . $thhd . '>' . get_string('course') . '</th>';
    $spdetailspdf .= '<th width="22%"' . $thhd . '>' . get_string('assessment') . '</th>';
    $spdetailspdf .= '<th width="8%" ' . $thhd . '>' . get_string('assessmenttype', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= '<th width="5%" ' . $thhd . '>' . get_string('weight', 'block_newgu_spdetails') . "</th>";
    if ($coursestype == 'current') {
        $spdetailspdf .= '<th width="15%" ' . $thhd . '>' . get_string('duedate', 'block_newgu_spdetails') . "</th>";
        $spdetailspdf .= '<th width="15%" ' . $thhd . '>' . get_string('status') . "</th>";
    } else {
        $spdetailspdf .= '<th width="15%" ' . $thhd . '>' . get_string('startdate', 'block_newgu_spdetails') . "</th>";
        $spdetailspdf .= '<th width="15%" ' . $thhd . '>' . get_string('enddate', 'block_newgu_spdetails') . "</th>";
    }
    $spdetailspdf .= '<th width="11%" ' . $thhd . '>' . get_string('yourgrade', 'block_newgu_spdetails') . "</th>";
    $spdetailspdf .= "</tr>";

    $row = 6;
    $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
    foreach ($courses as $course) {
        // Make sure we are enrolled as a student on this course.
        if (\block_newgu_spdetails\api::return_isstudent($course->id, $USER->id)) {

            $mygradesenabled = \block_newgu_spdetails\course::is_type_mygrades($course->id);
            $activitydata = [];

            if ($course->startdate) {
                $dateobj = \DateTime::createFromFormat('U', $course->startdate);
                $startdate = $dateobj->format('jS F Y');
            }

            if ($course->enddate) {
                $dateobj = \DateTime::createFromFormat('U', $course->enddate);
                $enddate = $dateobj->format('jS F Y');
            }

            // This returns an array of objects - process_[x]_items() is expecting an ordinary array. It seems to work still.
            $activities = \block_newgu_spdetails\course::get_activities($course->id);

            if ($mygradesenabled) {
                $activitydata = \block_newgu_spdetails\activity::process_mygrades_items($activities, $coursestype,
                $ltiactivities, '', 'shortname', 'ASC');
            }

            if (!$mygradesenabled) {
                $activitydata = \block_newgu_spdetails\activity::process_default_items($activities, $coursestype,
                $ltiactivities, '', 'shortname', 'ASC');
            }

            if ($activitydata) {
                foreach ($activitydata as $activityitem) {
                    $spdetailspdf .= "<tr>";
                    $spdetailspdf .= "<td $tdstl>" . $course->fullname . "</td>";
                    $spdetailspdf .= "<td $tdstl>" . $activityitem['item_name'] . "</td>";
                    // The assessment type is normally derived from the parent category - which works only
                    // as long as the parent name contains 'Formative' or 'Summative', and the item weight.
                    // As we have the original activities array, we can get the category id from there and
                    // use it to then work out the category name for this item.
                    $categoryid = $activities[$activityitem['id']]->categoryid;
                    $category = grade_category::fetch(['id' => $categoryid]);
                    $categoryname = '';
                    if ($category) {
                        $categoryname = $category->fullname;
                    }
                    $weight = (float) $activityitem['raw_assessment_weight'];
                    $assessmenttype = \block_newgu_spdetails\course::return_assessmenttype($categoryname, $weight);
                    $spdetailspdf .= "<td $tdstc>" . $assessmenttype . "</td>";
                    $spdetailspdf .= "<td $tdstc>" . $activityitem['assessment_weight'] . "</td>";
                    if ($coursestype == 'current') {
                        $spdetailspdf .= "<td $tdstc>" . $activityitem['due_date'] . "</td>";
                        $spdetailspdf .= "<td $tdstc>" . $activityitem['status_text'] . "</td>";
                    } else {
                        $spdetailspdf .= "<td $tdstc>" . $startdate . "</td>";
                        $spdetailspdf .= "<td $tdstc>" . $enddate . "</td>";
                    }
                    $spdetailspdf .= "<td $tdstc>" . $activityitem['grade'] . "</td>";
                    $spdetailspdf .= "</tr>";

                    $row++;
                    $col = 0;
                    $xldata[$row][$col] = ["row" => $row, "col" => $col, "text" => $course->fullname];
                    $col++;
                    $xldata[$row][$col] = ["row" => $row, "col" => $col, "text" => $activityitem['item_name']];
                    $col++;
                    $xldata[$row][$col] = ["row" => $row, "col" => $col, "text" => $assessmenttype];
                    $col++;
                    $xldata[$row][$col] = ["row" => $row, "col" => $col, "text" => $activityitem['assessment_weight']];
                    $col++;
                    if ($coursestype == 'current') {
                        $xldata[$row][$col] = ["row" => $row, "col" => $col, "text" => $activityitem['due_date']];
                        $col++;
                        $xldata[$row][$col] = ["row" => $row, "col" => $col, "text" => $activityitem['status_text']];
                        $col++;
                    } else {
                        $xldata[$row][$col] = ["row" => $row, "col" => $col, "text" => $startdate];
                        $col++;
                        $xldata[$row][$col] = ["row" => $row, "col" => $col, "text" => $enddate];
                        $col++;
                    }
                    $xldata[$row][$col] = ["row" => $row, "col" => $col, "text" => strip_tags($activityitem['grade'])];
                    $col++;
                }
            }
        }
    }
    $spdetailspdf .= "</table>";
}

if ($spdetailstype == "pdf" && $spdetailspdf != "" && $strcoursestype != "") {

    require_once($CFG->libdir . '/pdflib.php');

    $doc = new pdf();

    // Set document information.
    $doc->setCreator(PDF_CREATOR);
    $doc->setAuthor('University of Glasgow');
    $doc->setTitle($strcoursestype . ' Report');
    $doc->setSubject('Course Reports');

    // Set the images to be used.
    $background = 'img/uofg-background.jpg';
    $pathsw1b = 'img/uglogo03.png';

    // Set header, footer and general fonts.
    $doc->setHeaderFont(['helvetica', 'b', 18]);
    $doc->setFooterFont(['helvetica', '', 10]);
    $doc->setFont('helvetica', '', 12);

    // Set default footer data.
    $doc->setFooterData();

    // Set margins.
    $doc->setMargins(5, 20, 5);
    $doc->setHeaderMargin(50);
    $doc->setFooterMargin(10);

    // Set auto page breaks.
    $doc->setAutoPageBreak(true, 15);

    // Set image scale factor.
    $doc->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Add the opening page.
    $doc->AddPage('L', 'A4');
    $doc->Image($background, 0, 0, 1920, 1080, 'jpeg', '', '', true, 300, '', false, false, 0, false);

    // Set alpha to semi-transparency.
    $doc->setAlpha(0.5);

    // Draw the blue transparent square.
    $doc->setFillColor(0, 102, 153);
    $doc->setDrawColor(0, 0, 127);
    $doc->Rect(180, 20, 100, 140, 'F');

    // Reset alpha.
    $doc->setAlpha(1);

    // Set color for text.
    $doc->SetTextColor(255, 255, 255);

    // Add our report titles.
    $html = "<div>University of Glasgow";
    $doc->writeHTMLCell(0, 0, 115, 70, $html, '', 1, 0, true, 'C', true);
    $style1 = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255,255,255));
    $style2 = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255,255,255));
    $doc->Line(183, 76, 275, 76, $style1);
    $html = "<h1>" . $strcoursestype . " Report for</h1>";
    $doc->writeHTMLCell(0, 0, $cellwidth, 80, $html, '', 1, 0, true, 'C', true);
    $doc->Line(183, 88, 275, 88, $style2);
    $html = "<h2>" . $myfirstlastname. "</h2></div>";
    $doc->writeHTMLCell(0, 0, 170, 90, $html, '', 1, 0, true, 'C', true);
    $doc->Line(183, 98, 275, 98, $style2);

    // Set color for remaining text.
    $doc->SetTextColor(0,0,0);

    // Set the starting point for the page content.
    $doc->setPageMark();
    $doc->AddPage('L', 'A4');
    $doc->setXY(5, 2);
    $doc->setXY(215, 15);
    $doc->Cell(25, 10, $myfirstlastname, 0, $ln = 0, 'C', 0, '', 0, false, 'B', 'B');
    $doc->setFont('helvetica', '', 9);
    $doc->setXY(245, 20);
    $doc->Cell(25, 10, $strcoursestype . " Report Date : " . date("d-m-Y"), 0, $ln = 0, 'C', 0, '', 0, false, 'B', 'B');
    $doc->setMargins(5, 20, 5);
    $doc->setFont('helvetica', '', 10);
    $doc->setXY(5, 23);
    $thtml = <<<EOD
$spdetailspdf
EOD;

    $c = $thtml;
    $doc->writeHTML($c, true, false, false, false, '');
    $doc->Output($strcoursestype . " Report - " . $myfirstlastname . '_' . date("d-m-Y") . '.pdf', 'D');

    exit(0);
}

if ($spdetailstype == "excel" && $spdetailspdf != "" && $strcoursestype != "") {

    $filename = clean_filename($strcoursestype . " Report - " . $myfirstlastname . "_" . date("d-M-Y") . '.xls');
    $workbook = new MoodleExcelWorkbook("-");
    // Send HTTP headers.
    $workbook->send($filename);

    $formatsetcenter = $workbook->add_format();
    $formatsetcenter->set_align('center');
    $formatsetcenter->set_v_align('center');
    $formatsetvcenter = $workbook->add_format();
    $formatsetvcenter->set_v_align('center');

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
    $myxls->set_column(0, 1, 40);
    $myxls->set_column(2, 2, 15);
    $myxls->set_column(3, 4, 10);
    $myxls->write_string(4, 0, $strcoursestype . ' Report - ' . date("d/m/Y"));

    $rowhd = 6;
    $col = 0;
    $xldata[$row][$col] = ["row" => $rowhd, "col" => $col, "text" => get_string('course')];
    $col++;
    $xldata[$row][$col] = ["row" => $rowhd, "col" => $col, "text" => get_string("assessment")];
    $col++;
    $xldata[$row][$col] = ["row" => $rowhd, "col" => $col, "text" => get_string("assessmenttype", "block_newgu_spdetails")];
    $col++;
    $xldata[$row][$col] = ["row" => $rowhd, "col" => $col, "text" => get_string('weight', 'block_newgu_spdetails')];
    $col++;
    if ($coursestype == "current") {
        $xldata[$row][$col] = ["row" => $rowhd, "col" => $col, "text" => get_string('duedate', 'block_newgu_spdetails')];
        $col++;
        $xldata[$row][$col] = ["row" => $rowhd, "col" => $col, "text" => get_string('status')];
        $col++;
    }
    if ($coursestype == "past") {
        $xldata[$row][$col] = ["row" => $rowhd, "col" => $col, "text" => get_string('startdate', 'block_newgu_spdetails')];
        $col++;
        $xldata[$row][$col] = ["row" => $rowhd, "col" => $col, "text" => get_string('enddate', 'block_newgu_spdetails')];
        $col++;
    }
    $xldata[$row][$col] = ["row" => $rowhd, "col" => $col, "text" => get_string('yourgrade', 'block_newgu_spdetails')];
    $col++;

    if ($coursestype == "current") {
        $myxls->set_column(5, 5, 15);
        $myxls->set_column(6, 6, 20);
        $myxls->set_column(7, 8, 25);
    }

    if ($coursestype == "past") {
        $myxls->set_column(5, 6, 15);
        $myxls->set_column(7, 8, 25);
    }

    $rowheight = 22;

    foreach ($xldata as $row) {
        foreach ($row as $cell) {
            if ($cell["row"] == 6) {
                $cellformat = $formatbgcol;
            } else {
                if ($cell["col"] >= 2 && $cell["col"] <= 7) {
                    $cellformat = $formatsetcenter;
                } else {
                    $cellformat = $formatsetvcenter;
                }
            }

            $myxls->set_row($cell["row"], $rowheight, null, false);
            $myxls->write_string($cell["row"], $cell["col"], $cell["text"], $cellformat);
        }
    }

    $workbook->close();
}
