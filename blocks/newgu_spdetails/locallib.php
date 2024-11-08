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
 * Returns somthing.
 *
 * @param string $coursetype
 * @param string $tdr
 * @param int $userid
 */
function get_assessmenttypeorder($coursetype, $tdr, $userid) {

    global $DB;

    $courses = \block_newgu_spdetails\course::return_enrolledcourses($userid, $coursetype);
    $strcourses = implode(",", $courses);
    $stritemsnotvisibletouser = \block_newgu_spdetails\api::fetch_itemsnotvisibletouser($userid, $strcourses);
    $sqlcc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('
      . $strcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$stritemsnotvisibletouser
      . ') && gi.courseid=c.id';
    $arrcc = $DB->get_records_sql($sqlcc);

    $arrorder = [];

    foreach ($arrcc as $keycc) {
        $modulename = $keycc->itemmodule;
        $courseid = $keycc->courseid;
        $itemid = $keycc->id;
        $categoryid = $keycc->categoryid;
        $assessmenttype = "";

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
