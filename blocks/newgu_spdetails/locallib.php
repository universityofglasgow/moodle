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
 * Contains the DB query methods for UofG Assessments Details block.
 *
 * @package    block_newgu_spdetails
 * @copyright  2023
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return an array of graded items.
 *
 * @param string $modulename
 * @param int $iteminstance
 * @param int $courseid
 * @param int $itemid
 * @param int $userid
 * @param float $grademax
 * @param int $gradetype
 * @return array
 */
function get_gradefeedback(string $modulename, int $iteminstance, int $courseid,
    int $itemid, int $userid, float $grademax, int $gradetype) {
    global $CFG, $DB, $USER;

    $link = "";
    $gradetodisplay = "";

    $gradestatus = \block_newgu_spdetails\grade::return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $userid);

    $status = $gradestatus["status"];
    $link = $gradestatus["link"];
    $allowsubmissionsfromdate = $gradestatus["allowsubmissionsfromdate"];
    $duedate = $gradestatus["duedate"];
    $cutoffdate = $gradestatus["cutoffdate"];
    $gradingduedate = $gradestatus["gradingduedate"];

    $rawgrade = $gradestatus["rawgrade"];
    $finalgrade = $gradestatus["finalgrade"];

    $provisional22grademaxpoint = $gradestatus["provisional_22grademaxpoint"];
    $converted22grademaxpoint = $gradestatus["converted_22grademaxpoint"];

    $cmid = \block_newgu_spdetails\course::get_cmid($modulename, $courseid, $iteminstance);

    if ($finalgrade != null) {
        if ($gradetype == 1) {
            $gradetodisplay = '<span class="graded">' . number_format((float)$finalgrade) . " / " . number_format((float)$grademax)
            . '</span>' . ' (Provisional)';
        }
        if ($gradetype == 2) {
            $gradetodisplay = '<span class="graded">' . $converted22grademaxpoint . '</span>' . ' (Provisional)';
        }
        $link = $CFG->wwwroot . '/mod/'.$modulename.'/view.php?id=' . $cmid . '#page-footer';
    }

    if ($finalgrade == null  && $duedate < time()) {
        if ($status == "notopen" || $status == "notsubmitted") {
            $gradetodisplay = 'To be confirmed';
            $link = "";
        }
        if ($status == "overdue") {
            $gradetodisplay = 'Overdue';
            $link = "";
        }
        if ($status == "notsubmitted") {
            $gradetodisplay = 'Not submitted';
            if ($gradingduedate > time()) {
                $gradetodisplay = "Due " . date("d/m/Y", $gradingduedate);
            }
        }
    }

    if ($status == "tosubmit") {
        $gradetodisplay = 'To be confirmed';
        $link = "";
    }

    return [
        "gradetodisplay" => $gradetodisplay,
        "link" => $link,
        "provisional_22grademaxpoint" => $provisional22grademaxpoint,
        "converted_22grademaxpoint" => $converted22grademaxpoint,
        "finalgrade" => $finalgrade,
        "rawgrade"  => $rawgrade,
    ];
}

/**
 * Return the weight.
 *
 * @param int $courseid
 * @param int $categoryid
 * @param int $aggregationcoef
 * @param int $aggregationcoef2
 */
function get_weight($courseid, $categoryid, $aggregationcoef, $aggregationcoef2) {
    global $DB;

    $arrgradecategory = $DB->get_record('grade_categories', ['courseid' => $courseid, 'id' => $categoryid]);

    if (!empty($arrgradecategory)) {
        $gradecategoryname = $arrgradecategory->fullname;
        $aggregation = $arrgradecategory->aggregation;
    }

    $finalweight = "—";
    $assessmenttype = \block_newgu_spdetails\course::return_assessmenttype($gradecategoryname, $aggregationcoef);
    $summative = get_string('summative', 'block_newgu_spdetails');
    $weight = ($aggregation == '10') ?
                (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100) :
                (($assessmenttype === $summative) ?
                    $aggregationcoef2 * 100 : 0);
    $finalweight = ($weight > 0) ? round($weight, 2).'%' : get_string('emptyvalue', 'block_newgu_spdetails');

    return $finalweight;
}

/**
 * Returns somthing.
 *
 * @param string $coursetype
 * @param string $tdr
 * @param int $userid
 */
function get_assessmenttypeorder($coursetype, $tdr, $userid) {

    global $DB, $CFG;

    $courses = \block_newgu_spdetails\course::return_enrolledcourses($userid, $coursetype);
    $strcourses = implode(",", $courses);
    $stritemsnotvisibletouser = \block_newgu_spdetails\api::fetch_itemsnotvisibletouser($userid, $strcourses);
    $sqlcc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('
      . $strcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$stritemsnotvisibletouser
      . ') && gi.courseid=c.id';
    $arrcc = $DB->get_records_sql($sqlcc);

    $arrorder = [];

    foreach ($arrcc as $keycc) {
        $cmid = $keycc->id;
        $modulename = $keycc->itemmodule;
        $iteminstance = $keycc->iteminstance;
        $courseid = $keycc->courseid;
        $itemid = $keycc->id;
        $categoryid = $keycc->categoryid;

        // DUE DATE.
        $assessmenttype = "";
        $strassessmenttype = "—";

        // READ individual TABLE OF ACTIVITY (MODULE).
        if ($modulename != "") {

            $arrgradecategory = $DB->get_record('grade_categories', ['courseid' => $courseid, 'id' => $categoryid]);
            if (!empty($arrgradecategory)) {
                  $gradecategoryname = $arrgradecategory->fullname;
            }

            $aggregationcoef = $keycc->aggregationcoef;
            $assessmenttype = \block_newgu_spdetails\course::return_assessmenttype($gradecategoryname, $aggregationcoef);
        }

        $arrorder[$itemid] = $assessmenttype;
    }

    if ($tdr == 3) {
        asort($arrorder);
    }
    if ($tdr == 4) {
        arsort($arrorder);
    }

    $strorder = "";
    foreach ($arrorder as $keyorder => $value) {
        $strorder .= $keyorder . ",";
    }
    $strorder = rtrim($strorder, ",");

    return $strorder;
}

/**
 * Does something.
 *
 * @param string $tdr
 * @param int $userid
 */
function get_duedateorder($tdr, $userid) {

    global $DB, $CFG;

    $currentcourses = \block_newgu_spdetails\course::return_enrolledcourses($userid, "current");
    $strcurrentcourses = implode(",", $currentcourses);
    $currentxl = [];
    $stritemsnotvisibletouser = \block_newgu_spdetails\api::fetch_itemsnotvisibletouser($userid, $strcurrentcourses);
    $sqlcc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in (' . $strcurrentcourses
    . ') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in (' . $stritemsnotvisibletouser . ') && gi.courseid=c.id';
    $arrcc = $DB->get_records_sql($sqlcc);
    $arrorder = [];

    foreach ($arrcc as $keycc) {
        $cmid = $keycc->id;
        $modulename = $keycc->itemmodule;
        $iteminstance = $keycc->iteminstance;
        $courseid = $keycc->courseid;
        $itemid = $keycc->id;

        // DUE DATE.
        $duedate = 0;
        $extspan = "";
        $extensionduedate = 0;
        $strduedate = "—";

        // READ individual TABLE OF ACTIVITY (MODULE).
        if ($modulename != "") {
            $arrduedate = $DB->get_record($modulename, ['course' => $courseid, 'id' => $iteminstance]);

            if (!empty($arrduedate)) {
                if ($modulename == "assign") {
                    $duedate = $arrduedate->duedate;

                    $arruserflags = $DB->get_record('assign_user_flags', ['userid' => $userid, 'assignment' => $iteminstance]);

                    if ($arruserflags) {
                        $extensionduedate = $arruserflags->extensionduedate;
                        if ($extensionduedate > 0) {
                            $extspan = '<a href="javascript:void(0)" title="' . get_string('extended', 'block_newgu_spdetails')
                            . '" class="extended">*</a>';
                        }
                    }
                }

                if ($modulename == "forum") {
                    $duedate = $arrduedate->duedate;
                }

                if ($modulename == "quiz") {
                    $duedate = $arrduedate->timeclose;
                }

                if ($modulename == "workshop") {
                    $duedate = $arrduedate->submissionend;
                }
            }
        }

        if ($duedate != 0) {
            $strduedate = date("d/m/Y", $duedate) . $extspan;
        }

        $arrorder[$itemid] = $duedate;
    }

    if ($tdr == 3) {
        asort($arrorder);
    }
    if ($tdr == 4) {
        arsort($arrorder);
    }

    $strorder = "";
    foreach ($arrorder as $keyorder => $value) {
        $strorder .= $keyorder . ",";
    }
    $strorder = rtrim($strorder, ",");

    return $strorder;
}

/**
 * Does something.
 *
 * @param string $tdr
 */
function get_startenddateorder($tdr) {

    global $USER, $DB, $CFG;

    $pastcourses = \block_newgu_spdetails\course::return_enrolledcourses($USER->id, "past");
    $strpastcourses = implode(",", $pastcourses);
    $pastxl = [];

    if ($strpastcourses != "") {

        $stritemsnotvisibletouser = \block_newgu_spdetails\api::fetch_itemsnotvisibletouser($USER->id, $strpastcourses);
        $sqlcc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in (' .
        $strpastcourses . ') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in (' .
        $stritemsnotvisibletouser . ') && gi.courseid=c.id';
        $arrcc = $DB->get_records_sql($sqlcc);
        $arrsdorder = [];
        $arredorder = [];

        foreach ($arrcc as $keycc) {
            $cmid = $keycc->id;
            $modulename = $keycc->itemmodule;
            $iteminstance = $keycc->iteminstance;
            $courseid = $keycc->courseid;
            $categoryid = $keycc->categoryid;
            $itemid = $keycc->id;
            $aggregationcoef = $keycc->aggregationcoef;
            $aggregationcoef2 = $keycc->aggregationcoef2;

            // FETCH ASSESSMENT TYPE.
            $arrgradecategory = $DB->get_record('grade_categories', ['courseid' => $courseid, 'id' => $categoryid]);
            if (!empty($arrgradecategory)) {
                $gradecategoryname = $arrgradecategory->fullname;
            }

            $assessmenttype = \block_newgu_spdetails\course::return_assessmenttype($gradecategoryname, $aggregationcoef);

            // START DATE.
            $submissionstartdate = 0;
            $startdate = "";
            $duedate = 0;
            $enddate = "";

            // READ individual TABLE OF ACTIVITY (MODULE).
            if ($modulename != "") {
                $arrsubmissionstartdate = $DB->get_record($modulename, ['course' => $courseid, 'id' => $iteminstance]);

                if (!empty($arrsubmissionstartdate)) {
                    if ($modulename == "assign") {
                        $submissionstartdate = $arrsubmissionstartdate->allowsubmissionsfromdate;
                        $duedate = $arrsubmissionstartdate->duedate;
                    }
                    if ($modulename == "forum") {
                        $submissionstartdate = $arrsubmissionstartdate->assesstimestart;
                        $duedate = $arrsubmissionstartdate->duedate;
                    }
                    if ($modulename == "quiz") {
                        $submissionstartdate = $arrsubmissionstartdate->timeopen;
                        $duedate = $arrsubmissionstartdate->timeclose;
                    }
                    if ($modulename == "workshop") {
                        $submissionstartdate = $arrsubmissionstartdate->submissionstart;
                        $duedate = $arrsubmissionstartdate->submissionend;
                    }
                }
            }

            $startdate = date("d/m/Y", $submissionstartdate);
            $arrsdorder[$itemid] = $submissionstartdate;
            $enddate = date("d/m/Y", $duedate);
            $arredorder[$itemid] = $duedate;
        }
    }

    if ($tdr == 3) {
        asort($arrsdorder);
    }
    if ($tdr == 4) {
        arsort($arrsdorder);
    }
    $strsdorder = "";
    foreach ($arrsdorder as $keyorder => $value) {
        $strsdorder .= $keyorder . ",";
    }
    $strsdorder = rtrim($strsdorder, ",");

    if ($tdr == 3) {
        asort($arredorder);
    }
    if ($tdr == 4) {
        arsort($arredorder);
    }
    $stredorder = "";
    foreach ($arredorder as $keyorder => $value) {
        $stredorder .= $keyorder . ",";
    }
    $stredorder = rtrim($stredorder, ",");
    $arrayorder = ["startdateorder" => $strsdorder, "enddateorder" => $stredorder];

    return $arrayorder;
}

/**
 * Does something.
 */
function get_ltiinstancenottoinclude() {
    // FETCH LTI IDs TO BE INCLUDED.
    global $DB;

    $strltitoinclude = "99999";
    $strltinottoinclude = "99999";
    $sqlltitoinclude = "SELECT * FROM {config} WHERE name like '%block_newgu_spdetails_include_%' AND value=1";
    $arrltitoinclude = $DB->get_records_sql($sqlltitoinclude);
    $arrayltitoinclude = [];

    foreach ($arrltitoinclude as $keyltitoinclude) {
        $name = $keyltitoinclude->name;
        $namepieces = explode("block_newgu_spdetails_include_", $name);
        $ltitype = $namepieces[1];
        $arrayltitoinclude[] = $ltitype;
    }
    $strltitoinclude = implode(",", $arrayltitoinclude);

    if ($strltitoinclude == "") {
        $strltitoinclude = "99999";
    }

    $sqlltitypenottoinclude = "SELECT id FROM {lti_types} WHERE id not in (".$strltitoinclude.")";
    $arrltitypenottoinclude = $DB->get_records_sql($sqlltitypenottoinclude);

    $arrayltitypenottoinclude = [];
    $arrayltitypenottoinclude[] = 0;
    foreach ($arrltitypenottoinclude as $keyltitypenottoinclude) {
        $arrayltitypenottoinclude[] = $keyltitypenottoinclude->id;
    }
    $strltitypenottoinclude = implode(",", $arrayltitypenottoinclude);

    $sqlltiinstancenottoinclude = "SELECT * FROM {lti} WHERE typeid NOT IN (".$strltitypenottoinclude.")";
    $arrltiinstancenottoinclude = $DB->get_records_sql($sqlltiinstancenottoinclude);

    $arrayltiinstancenottoinclude = [];
    foreach ($arrltiinstancenottoinclude as $keyltiinstancenottoinclude) {
        $arrayltiinstancenottoinclude[] = $keyltiinstancenottoinclude->id;
    }
    $strltiinstancenottoinclude = implode(",", $arrayltiinstancenottoinclude);

    if ($strltiinstancenottoinclude == "") {
        $strltiinstancenottoinclude = 99999;
    }

    return $strltiinstancenottoinclude;
}
