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
    int|null $subcategory = null): array {
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
    public static function retrieve_gradable_activities(string $activetab, int $userid, string|null $sortby = null,
    string|null $sortorder = null, int|null $subcategory = null): array {
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
            $gradableactivities = \block_newgu_spdetails\activity::get_activityitems($subcategory, $userid, $activetab);
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
     * Method to return the value of the notional 'due' date column of the activity.
     *
     * The customdata property is an array of keys that we need to search and match.
     * Gak - I thought there might have been an easier way to match and return said
     * key from a source array of keys - looks like we've got to go the old skool way.
     *
     * @param object $cm - The course module object. Our key 'customdata' is an array.
     * @return int
     */
    public static function get_activity_end_date_name(object $cm): int {
        $activitydate = 0;
        $keys = [
            'duedate',
            'timeclose',
            'sessdate',
            'timeavailableto',
            'timedue',
            'deadline',
            'submissionend',
        ];
        $key = '';
        if ($cm->customdata) {
            foreach ($cm->customdata as $k => $v) {
                if (in_array($k, $keys)) {
                    $key = $k;
                    break;
                }
            }
            if ($key != null) {
                $activitydate = $cm->customdata[$key];
            }
        } else {
            // If there is no customdata we try one more time from scratch.
            // Get the activity based on its type.
            $courseid = 0;
            if (isset($cm->course)) {
                $courseid = $cm->course;
            }
            $gradinginfo = grade_get_grades($courseid, 'mod', $cm->modname, $cm->instance);
            $activity = \block_newgu_spdetails\activity::activity_factory($gradinginfo->items[0]->id, $courseid, 0);
            if ($records = $activity->get_assessmentsdue()) {
                $activitydate = $records[0]->duedate;
            }
        }

        return $activitydate;
    }

}
