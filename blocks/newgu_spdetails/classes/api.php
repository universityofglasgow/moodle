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
 * The API for the Student Dashboard plugin
 *
 * @package    block_newgu_spdetails
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_newgu_spdetails;

use context_course;
use context_system;
use core_external\external_api;

define('NUM_ASSESSMENTS_PER_PAGE', 12);

/**
 * This class provides the API for the plugin.
 */
class api extends external_api {

    /**
     * This method returns the processed list of gradable activities.
     *
     * @param string $activetab
     * @param int $page
     * @param string $sortby
     * @param string $sortorder
     * @param int $subcategory
     * @return array $data
     */
    public static function retrieve_assessments(string $activetab, int $page, string $sortby, string $sortorder,
    int $subcategory = null): array {
        global $USER, $OUTPUT, $PAGE;
        $PAGE->set_context(context_system::instance());

        $userid = $USER->id;
        $limit = NUM_ASSESSMENTS_PER_PAGE;
        $offset = $page * $limit;
        $params = [
            'activetab' => $activetab,
            'page' => $page,
            'sortby' => $sortby,
            'sortorder' => $sortorder,
            'subcategory' => $subcategory,
        ];
        $url = new \moodle_url('/index.php', $params);
        $totalassessments = 0;
        $data = [];

        $items = self::retrieve_gradable_activities($activetab, $userid, $sortby, $sortorder, $subcategory);

        if ($items) {
            $totalassessments = count($items);
            $paginatedassessments = array_splice($items, $offset, $limit);

            foreach ($paginatedassessments as $k => $v) {
                $data[$k] = $v;
            }

            $data['pdf_link'] = 'downloadspdetails.php?spdetailstype=pdf&coursestype=' . $activetab;
            $data['excel_link'] = 'downloadspdetails.php?spdetailstype=excel&coursestype=' . $activetab;
        }

        return $data;
    }

    /**
     * This method returns either the top level categories for a course, or the activities within that course.
     *
     * @param string $activetab
     * @param int $userid
     * @param string $sortby
     * @param string $sortorder
     * @param int $subcategory
     *
     * @return array $gradableactivities
     * @throws dml_exception
     */
    public static function retrieve_gradable_activities(string $activetab, int $userid, string $sortby = null, string $sortorder,
    int $subcategory = null): array {
        $gradableactivities = [];

        // Start with getting the top level categories for all courses.
        if (!$subcategory) {
            switch ($activetab) {
                case 'current':
                    $currentcourses = true;
                    $pastcourses = false;
                break;

                case 'past':
                    $currentcourses = false;
                    $pastcourses = true;
                break;

                default:
                    $currentcourses = false;
                    $pastcourses = false;
                break;
            }

            $courses = \local_gugrades\api::dashboard_get_courses($userid, $currentcourses, $pastcourses, $sortby . " " .
            $sortorder);
            return \block_newgu_spdetails\course::get_course_structure($courses, $currentcourses);
        } else {
            $gradableactivities = \block_newgu_spdetails\activity::get_activityitems($subcategory, $userid, $activetab, $sortby,
            $sortorder);
        }

        return $gradableactivities;
    }

    /**
     * Return the assessments that are due in the next 24 hours, week and month.
     *
     * @return array
     */
    public static function get_assessmentsduesoon(): array {

        $stats = \block_newgu_spdetails\course::get_assessmentsduesoon();

        return $stats;
    }

    /**
     * Return assessments that are due - filtered by type: 24hrs, 7days etc.
     *
     * @param int $charttype
     * @return array
     */
    public static function get_assessmentsduebytype(int $charttype): array {
        $assessmentsdue = \block_newgu_spdetails\course::get_assessmentsduebytype($charttype);

        return $assessmentsdue;
    }

    /**
     * Return a summary of current assessments for the student
     *
     * @return array
     */
    public static function get_assessmentsummary(): array {

        $summary = \block_newgu_spdetails\course::get_assessmentsummary();

        return $summary;
    }

    /**
     * Return the assessment summary - filtered by type: submitted, overdue etc.
     *
     * @param int $charttype
     * @return array
     */
    public static function get_assessmentsummarybytype(int $charttype): array {
        $assessmentsummary = \block_newgu_spdetails\course::get_assessmentsummarybytype($charttype);

        return $assessmentsummary;
    }

    /**
     * Check if the user has the capability of a student
     *
     * @param int $courseid
     * @param int $userid
     * @return boolean has_capability
     * @throws coding_exception
     */
    public static function return_isstudent($courseid, $userid) {
        $context = context_course::instance($courseid);
        return has_capability('moodle/grade:view', $context, $userid, false);
    }

    /**
     * This function checks that, for a given userid, the user
     * is enrolled on a given course (passed in as courseid).
     *
     * @param int $userid
     * @param int $courseid
     * @return mixed
     * @throws dml_exception
     */
    public static function checkrole(int $userid, int $courseid) {
        global $DB;

        $sqlstaff = "SELECT count(*) as cntstaff
             FROM {user} u
             JOIN {user_enrolments} ue ON ue.userid = u.id
             JOIN {enrol} e ON e.id = ue.enrolid
             JOIN {role_assignments} ra ON ra.userid = u.id
             JOIN {context} ct ON ct.id = ra.contextid
             AND ct.contextlevel = 50
             JOIN {course} c ON c.id = ct.instanceid
             AND e.courseid = c.id
             JOIN {role} r ON r.id = ra.roleid
             AND r.shortname in ('staff', 'editingstaff')
             WHERE e.status = 0
             AND u.suspended = 0
             AND u.deleted = 0
             AND ue.status = 0 ";
        if ($courseid != 0) {
            $sqlstaff .= " AND c.id = " . $courseid;
        }
        $sqlstaff .= " AND u.id = " . $userid;

        $arrcntstaff = $DB->get_record_sql($sqlstaff);
        $cntstaff = $arrcntstaff->cntstaff;

        return $cntstaff;
    }

    /**
     * This method does something.
     *
     * @param int $userid
     * @param string $strcourses
     * @return string
     */
    public static function fetch_itemsnotvisibletouser(int $userid, string $strcourses) {

        global $DB;

        $courses = explode(",", $strcourses);
        $itemsnotvisibletouser = [];
        $itemsnotvisibletouser[] = 0;
        $stritemsnotvisibletouser = "";

        if ($strcourses != "") {
            foreach ($courses as $courseid) {

                $modinfo = get_fast_modinfo($courseid);
                $cms = $modinfo->get_cms();

                foreach ($cms as $cm) {
                    // Check if course module is visible to the user.
                    $iscmvisible = $cm->uservisible;

                    if (!$iscmvisible) {
                        $sqlmodinstance = 'SELECT cm.id, cm.instance, cm.module, m.name FROM {modules} m, {course_modules} cm
                        WHERE cm.id=' . $cm->id . ' AND cm.module=m.id';
                        $arrmodinstance = $DB->get_record_sql($sqlmodinstance);
                        $instance = $arrmodinstance->instance;
                        $modname = $arrmodinstance->name;

                        $sqlgradeitemtoexclude = "SELECT id FROM {grade_items} WHERE courseid = " . $courseid . " AND itemmodule =
                        '" . $modname . "' AND iteminstance=" . $instance;
                        $arrgradeitemtoexclude = $DB->get_record_sql($sqlgradeitemtoexclude);
                        if (!empty($arrgradeitemtoexclude)) {
                            $itemsnotvisibletouser[] = $arrgradeitemtoexclude->id;
                        }
                    }
                }
            }
            $stritemsnotvisibletouser = implode(",", $itemsnotvisibletouser);
        }

        return $stritemsnotvisibletouser;
    }

    /**
     * Method to return LTI activities selected to be included on the dashboard.
     * These are selected via the MyGrades plugin Admin Settings page.
     *
     * @return array
     */
    public static function get_lti_activities(): array {
        global $DB;

        $configvalues = $DB->get_records_sql(
            "SELECT name FROM {config} WHERE name LIKE :configname AND value = :configvalue",
            [
                "configname" => "%block_newgu_spdetails_include_%",
                "configvalue" => 1,
            ]
        );

        if (!$configvalues) {
            return [];
        }

        $configltitypes = [];
        foreach ($configvalues as $configlti) {
            $name = $configlti->name;
            $namepieces = explode("block_newgu_spdetails_include_", $name);
            $ltitype = $namepieces[1];
            $configltitypes[] = $ltitype;
        }

        if (empty($configltitypes)) {
            return [];
        }

        $ltitypesparams = implode(",", $configltitypes);
        $ltitypes = $DB->get_records_sql(
            "SELECT id FROM {lti} WHERE typeid IN ($ltitypesparams)"
        );

        if (empty($ltitypes)) {
            return [];
        }

        $ltiactivities = [];
        foreach ($ltitypes as $ltitype) {
            $ltiactivities[] = $ltitype->id;
        }

        return $ltiactivities;
    }

    /**
     * Method to return the value of the notional 'due' date column of the activity.
     *
     * The customdata property is an array of keys that we need to search and match.
     * Gak - I thought there might have been an easier way to match and return said
     * key from a source array of keys - looks like we've got to go the old skool way.
     *
     * @param object $cm - The course module object. Our key 'customdata' is an array.
     * @return int
     */
    public static function get_activity_end_date_name(object $cm):int {
        $activitydate = 0;
        $keys = [
            'duedate',
            'timeclose',
            'sessdate',
            'timeavailableto',
            'timedue',
            'deadline',
            'submissionend'
        ];
        $key = '';
        foreach ($cm->customdata as $k => $v) {
            if(in_array($k, $keys)) {
                $key = $k;
                break;
            }
        }
        if ($key != null) {
            $activitydate = $cm->customdata[$key];
        }

        return $activitydate;
    }

}
