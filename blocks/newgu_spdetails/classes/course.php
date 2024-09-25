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
 * Class to describe course attributes.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace block_newgu_spdetails;

use block_newgu_spdetails;
use stdClass;

/**
 * This class provides methods for extracting course attributes.
 */
class course {

    /**
     * Given an array of 1 or more courses, return pertinent information.
     *
     * @param array $courses - an array of courses the user is enrolled in
     * @param bool $active - indicate if this is a current or past course
     * @return array
     */
    public static function get_course_structure(array $courses, bool $active): array {
        global $USER;
        $coursedata = [];
        $data = [
            'parent' => 0,
        ];

        if (!$courses) {
            return $data;
        }

        foreach ($courses as $course) {
            // Make sure we are in fact a student enrolled on this course.
            if (\block_newgu_spdetails\api::return_isstudent($course->id, $USER->id)) {
                // Fetch the categories and subcategories...
                $coursedata['coursename'] = $course->shortname;
                $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
                $coursedata['courseurl'] = $courseurl->out();
                if (!$active) {
                    $startdate = \DateTime::createFromFormat('U', $course->startdate);
                    $enddate = \DateTime::createFromFormat('U', $course->enddate);
                    $coursedata['startdate'] = $startdate->format('jS F Y');
                    $coursedata['raw_startdate'] = $course->startdate;
                    $coursedata['enddate'] = $enddate->format('jS F Y');
                    $coursedata['raw_enddate'] = $course->enddate;
                }
                $subcatdata = [];
                if (isset($course->firstlevel) && count($course->firstlevel) > 0) {
                    foreach ($course->firstlevel as $subcategory) {
                        $subcatid = 0;
                        $subcatname = '';
                        $subcatid = $subcategory['id'];
                        $subcatname = $subcategory['fullname'];
                        $item = \grade_item::fetch(['courseid' => $course->id, 'iteminstance' => $subcatid,
                        'itemtype' => 'category']);
                        // MGU-973 - Don't display the category if it doesn't contain any grade items.
                        // However, it may only contain further sub categories.
                        $items = \grade_item::fetch_all(['courseid' => $course->id, 'categoryid' => $subcatid,
                        'hidden' => 0]);
                        $subcategories = \grade_category::fetch_all(['parent' => $subcatid, 'hidden' => 0]);
                        if ($items || $subcategories) {
                            $assessmenttype = self::return_assessmenttype($subcatname, $item->aggregationcoef);
                            $subcatdata[] = [
                                'id' => $subcatid,
                                'name' => $subcatname,
                                'assessmenttype' => $assessmenttype,
                            ];
                        }
                    }
                } else {
                    // Our course appears to contain no sub categories. We want to filter out PLUGIN RELATED items.
                    $gradecat = \grade_category::fetch_all(['courseid' => $course->id, 'hidden' => 0]);
                    if ($gradecat) {
                        
                        // MGU-973 - Don't display the category if it doesn't contain any grade items.
                        // However, it may only contain further sub categories.
                        $items = \grade_item::fetch_all(['courseid' => $course->id, 'categoryid' => $course->category,
                        'hidden' => 0]);
                        $subcategories = \grade_category::fetch_all(['parent' => $course->category, 'hidden' => 0]);
                        if ($items || $subcategories) {
                        
                            $item = \grade_item::fetch(['courseid' => $course->id, 'itemtype' => 'course']);
                            $assessmenttype = self::return_assessmenttype($course->fullname, $item->aggregationcoef);
                            if (count($gradecat) > 0) {
                                foreach ($gradecat as $gradecategory) {
                                    $subcatdata[] = [
                                        'id' => $gradecategory->id,
                                        'name' => $course->fullname,
                                        'assessmenttype' => $assessmenttype,
                                    ];
                                }
                            }
                        }
                    }
                }

                $coursedata['subcategories'] = $subcatdata;
                $data['coursedata'][] = $coursedata;
            }
        }

        // This is needed by the template for 'past' courses.
        if (!$active) {
            $data['hasstartdate'] = true;
            $data['hasenddate'] = true;
        }

        return $data;
    }

    /**
     * Process and prepare for display sub categories for this course.
     * As there is nothing fundamentally different for a MyGrades course or a Gradebook course,
     * we no longer need the previous approach of having 2 methods doing effectively the same thing.
     *
     * @param int $courseid
     * @param array $gradecategories
     * @param string $assessmenttype
     * @param string $sortorder
     * @return array
     */
    public static function process_subcategories(int $courseid, array $gradecategories, string $assessmenttype,
    string $sortorder): array {
        $gradessubcatdata = [];
        $tmp = [];
        foreach ($gradecategories as $obj) {
            // We've no way of filtering out the PLUGIN RELATED DATA items by this point, so we need to do this.
            if ($obj->category->hidden) {
                continue;
            }
            
            // MGU-973 - Don't display the category if it doesn't contain any grade items.
            // However, it may only contain further sub categories.
            $items = \grade_item::fetch_all(['courseid' => $courseid, 'categoryid' => $obj->category->id,
            'hidden' => 0]);
            $subcategories = \grade_category::fetch_all(['parent' => $obj->category->id, 'hidden' => 0]);
            if ($items || $subcategories) {
                $item = \grade_item::fetch(['courseid' => $courseid, 'iteminstance' => $obj->category->id,
                'itemtype' => 'category']);
                $subcatweight = self::return_weight($item->aggregationcoef);
                // We need to work out the grade aggregate for any graded items w/in this sub category...
                // Is there an API call for this?
                $subcat = new \stdClass();
                $subcat->id = $obj->category->id;
                $subcat->name = $obj->category->fullname;
                $subcat->assessment_type = $assessmenttype;
                $subcat->subcatweight = $subcatweight . '%';
                $subcat->raw_category_weight = $subcatweight;

                $tmp[] = $subcat;
            }
        }

        // This needs redone. $mygradecategories comes in as an array of
        // objects, whose category property is also an object - making
        // sorting a tad awkward. The items property that comes in also,
        // is an array of objects containing the necessary property/key
        // which ^can^ get sorted and returned in the correct order needed
        // by the mustache engine. @todo!
        $tmp2 = self::sort_items($tmp, $sortorder);
        foreach ($tmp2 as $sortedarray) {
            $gradessubcatdata[] = $sortedarray;
        }

        return $gradessubcatdata;
    }

    /**
     * Utility function for sorting - as we're not using any fancy libraries
     * that will do this for us, we need to manually implement this feature.
     *
     * @param array $itemstosort
     * @param string $sortorder
     * @return array
     */
    public static function sort_items(array $itemstosort, string $sortorder): array {
        switch($sortorder) {
            case "asc":
                uasort($itemstosort, function($a, $b) {
                    return strcmp($a->name, $b->name);
                });
                break;

            case "desc":
                uasort($itemstosort, function($a, $b) {
                    return strcmp($b->name, $a->name);
                });
                break;
        }

        return $itemstosort;
    }

    /**
     * Reusing the code from local_gugrades/api::get_dashboard_get_courses.
     *
     * @param int $courseid
     * @return bool
     */
    public static function is_type_mygrades(int $courseid): bool {
        $mygradesenabled = \local_gugrades\api::is_mygrades_enabled_for_course($courseid);

        return $mygradesenabled;
    }

    /**
     * Returns the 'weight' in percentage
     * According to the spec, weighting is now derived only from the weight in the Gradebook set up.
     * @see https://gla.sharepoint.com/:w:/s/GCATUpgradeProjectTeam/EVDsT68UetZMn8Ug5ISb394BfYLW_MwcyMI7RF0JAC38PQ?e=BOofAS
     *
     * @param float $aggregationcoef
     * @return int Weight (as a percentage), or '—' if empty
     */
    public static function return_weight(float $aggregationcoef): int {
        $weight = (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100);
        $finalweight = ($weight > 0) ? round($weight, 2) : 0;

        return $finalweight;
    }

    /**
     * Returns the 'assessment type' for an assessment. Achieved through using the
     * assessments aggregation coefficient and category name. If the item only has
     * a weighting value - then we consider it to be a summative assessment.
     *
     * @param string $gradecategoryname
     * @param float $aggregationcoef
     * @return string 'Formative', 'Summative', or '—'
     */
    public static function return_assessmenttype(string $gradecategoryname, float $aggregationcoef): string {
        $type = strtolower($gradecategoryname);
        $hasweight = !empty((float)$aggregationcoef);

        if ($hasweight || (!$hasweight && strpos($type, 'summative') !== false)) {
            $assessmenttype = get_string('summative', 'block_newgu_spdetails');
        } else if (!$hasweight && strpos($type, 'formative') !== false) {
            $assessmenttype = get_string('formative', 'block_newgu_spdetails');
        } else if (!$hasweight && strpos($type, 'summative') === false && strpos($type, 'formative') === false) {
            $assessmenttype = get_string('emptyvalue', 'block_newgu_spdetails');
        }

        return $assessmenttype;
    }

    /**
     * Returns the course module id and relevant attributes.
     *
     * @param string $cmodule
     * @param int $courseid
     * @param int $instance
     * @return int
     */
    public static function get_cmid(string $cmodule, int $courseid, int $instance): int {
        // ...$cmodule is module name e.g. quiz, forums etc.
        global $DB;

        $arrmodule = $DB->get_record('modules', ['name' => $cmodule]);
        $moduleid = $arrmodule->id;

        $arrcoursemodule = $DB->get_record('course_modules', [
            'course' => $courseid,
            'module' => $moduleid,
            'instance' => $instance,
        ]);

        $cmid = $arrcoursemodule->id;

        return $cmid;

    }

    /**
     * This method returns all courses a user is currently enrolled in.
     * Courses can be filtered by course type and user type.
     *
     * @param int $userid
     * @param string $coursetype
     * @param string $usertype
     * @return array|void
     * @throws dml_exception
     */
    public static function return_enrolledcourses(int $userid, string $coursetype, string $usertype = "student"): array {

        $currentdate = time();
        $coursetypewhere = "";

        global $DB;

        $fields = "c.id, c.fullname as coursename";
        $fieldwhere = "c.visible = 1 AND c.visibleold = 1";

        if ($coursetype == "past") {
            $coursetypewhere = " AND ( c.enddate + (86400 * 30) <=" . $currentdate . " AND c.enddate!=0 )";
        }

        if ($coursetype == "current") {
            $coursetypewhere = " AND ( c.enddate + (86400 * 30) >" . $currentdate . " OR c.enddate=0 )";
        }

        if ($coursetype == "all") {
            $coursetypewhere = "";
        }

        $enrolmentselect = "SELECT DISTINCT e.courseid FROM {enrol} e
                            JOIN {user_enrolments} ue
                            ON (ue.enrolid = e.id AND ue.userid = ?)";

        $enrolmentjoin = "JOIN ($enrolmentselect) en ON (en.courseid = c.id)";

        $sql = "SELECT $fields FROM {course} c $enrolmentjoin
                WHERE $fieldwhere $coursetypewhere";

        $param = [$userid];

        $results = $DB->get_records_sql($sql, $param);

        if ($results) {
            $studentcourses = [];
            $staffcourses = [];
            foreach ($results as $courseid => $courseobject) {

                $coursename = $courseobject->coursename;

                if (\block_newgu_spdetails\api::return_isstudent($courseid, $userid)) {
                    array_push($studentcourses, $courseid);

                } else {
                    $cntstaff = \block_newgu_spdetails\api::checkrole($userid, $courseid);
                    if ($cntstaff != 0) {
                        array_push($staffcourses, ["courseid" => $courseid, "coursename" => $coursename]);
                    }
                }
            }

            if ($usertype == "student") {
                return $studentcourses;
            }

            if ($usertype == "staff") {
                return $staffcourses;
            }

        } else {
            return [];
        }
    }

    /**
     * Return a list of the activities for a given course id.
     *
     * @param int $courseid
     * @return object
     */
    public static function get_activities(int $courseid) {
        global $DB;

        $gradeitems = $DB->get_records('grade_items', [
            'courseid' => $courseid,
            'itemtype' => 'mod',
        ]);

        return $gradeitems;
    }

    /**
     * Return the assessments that are due in the next 24 hours, week and month.
     *
     * @return array
     */
    public static function get_assessmentsduesoon() {
        global $USER, $PAGE;

        $PAGE->set_context(\context_system::instance());

        $sortstring = 'shortname asc';
        $courses = \local_gugrades\api::dashboard_get_courses($USER->id, true, false, $sortstring);

        $stats = [
            '24hours' => 0,
            'week' => 0,
            'month' => 0,
        ];

        if (!$courses) {
            return $stats;
        }

        $assignmentdata = [];
        $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
        foreach ($courses as $course) {
            // Make sure we are enrolled as a student on this course.
            if (\block_newgu_spdetails\api::return_isstudent($course->id, $USER->id)) {
                // Return all the activities for this course.
                $activities = self::get_activities($course->id);
                if ($activities) {
                    foreach ($activities as $activityitem) {
                        if (!in_array($activityitem->itemmodule, \block_newgu_spdetails\activity::$excludedactivities)) {
                            $cm = get_coursemodule_from_instance($activityitem->itemmodule, $activityitem->iteminstance,
                            $activityitem->courseid);
                            $modinfo = get_fast_modinfo($activityitem->courseid);
                            $cms = $modinfo->get_cms();
                            if (array_key_exists($cm->id, $cms)) {
                                $cm = $modinfo->get_cm($cm->id);
                                if ($cm->uservisible) {
                                    // MGU-576/MGU-802 - Only include LTI activities if they have been selected.
                                    // Note that LTI activities only become a "gradable" activity when they have been set to accept grades!
                                    if ($activityitem->itemmodule == 'lti') {
                                        if (is_array($ltiactivities) && !in_array($activityitem->iteminstance, $ltiactivities)) {
                                            continue;
                                        }
                                    }

                                    // Get the activity based on its type...
                                    $activity = \block_newgu_spdetails\activity::activity_factory($activityitem->id,
                                    $activityitem->courseid, 0);
                                    if ($records = $activity->get_assessmentsdue()) {
                                        $assignmentdata[] = $records[0];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$assignmentdata) {
            return $stats;
        }

        $now = usertime(mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        $next24hours = usertime(mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y")));
        $next7days = usertime(mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7, date("Y")));
        $nextmonth = usertime(mktime(date("H"), date("i"), date("s"), date("m") + 1, date("d"), date("Y")));

        $duein24hours = 0;
        $duein7days = 0;
        $dueinnextmonth = 0;

        foreach ($assignmentdata as $assignment) {
            if (($assignment->duedate > $now) && ($assignment->duedate < $next24hours)) {
                $duein24hours++;
            }

            if (($assignment->duedate > $now) && (($assignment->duedate > $next24hours) && ($assignment->duedate < $next7days))) {
                $duein7days++;
            }

            if (($assignment->duedate > $now) && (($assignment->duedate > $next7days) && ($assignment->duedate < $nextmonth))) {
                $dueinnextmonth++;
            }
        }

        $stats = [
            '24hours' => $duein24hours,
            'week' => $duein7days,
            'month' => $dueinnextmonth,
        ];

        return $stats;
    }

    /**
     * Return assessments that are due - filtered by type: 24hrs, 7days etc.
     *
     * @param int $charttype
     * @return array
     */
    public static function get_assessmentsduebytype(int $charttype): array {
        global $USER, $PAGE;

        $PAGE->set_context(\context_system::instance());

        $sortstring = 'shortname asc';
        $courses = \local_gugrades\api::dashboard_get_courses($USER->id, true, false, $sortstring);

        $assessmentsdue = [];

        if (!$courses) {
            return $assessmentsdue;
        }

        $now = usertime(mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        $next24hours = usertime(mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y")));
        $next7days = usertime(mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7, date("Y")));
        $nextmonth = usertime(mktime(date("H"), date("i"), date("s"), date("m") + 1, date("d"), date("Y")));
        $option = '';
        switch($charttype) {
            case 0:
                $option = get_string('chart_24hrs', 'block_newgu_spdetails');
                break;
            case 1:
                $option = get_string('chart_7days', 'block_newgu_spdetails');
                break;
            case 2:
                $option = get_string('chart_1mth', 'block_newgu_spdetails');
                break;
        }

        $assessmentsdueheader = get_string('header_assessmentsdue', 'block_newgu_spdetails', $option);

        $assessmentdata = [];
        $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
        foreach ($courses as $course) {
            // Make sure we are enrolled as a student on this course.
            if (\block_newgu_spdetails\api::return_isstudent($course->id, $USER->id)) {
                $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
                $activities = self::get_activities($course->id);
                if ($activities) {
                    foreach ($activities as $item) {
                        if (!in_array($item->itemmodule, \block_newgu_spdetails\activity::$excludedactivities)) {
                            $cm = get_coursemodule_from_instance($item->itemmodule, $item->iteminstance, $item->courseid);
                            $modinfo = get_fast_modinfo($item->courseid);
                            $cms = $modinfo->get_cms();
                            if (array_key_exists($cm->id, $cms)) {
                                $cm = $modinfo->get_cm($cm->id);
                                if ($cm->uservisible) {
                                    // MGU-576/MGU-802 - Only include LTI activities if they have been selected.
                                    // Note that LTI activities only become a "gradable" activity when they have been set to accept grades!
                                    if ($item->itemmodule == 'lti') {
                                        if (is_array($ltiactivities) && !in_array($item->iteminstance, $ltiactivities)) {
                                            continue;
                                        }
                                    }

                                    // Get the activity based on its type...
                                    $activityitem = \block_newgu_spdetails\activity::activity_factory($item->id,
                                    $item->courseid, 0);
                                    if ($assessments = $activityitem->get_assessmentsdue()) {
                                        $assessment = $assessments[0];
                                        if ($assessment->duedate > $now) {
                                            $includeitem = false;
                                            switch($charttype) {
                                                case 0:
                                                    $when = usertime(mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y")));
                                                    $includeitem = ($assessment->duedate < $when);
                                                    break;
                                                case 1:
                                                    $when = usertime(mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7, date("Y")));
                                                    $includeitem = (($assessment->duedate > $next24hours) && ($assessment->duedate < $next7days));
                                                    break;
                                                case 2:
                                                    $when = usertime(mktime(date("H"), date("i"), date("s"), date("m") + 1, date("d"), date("Y")));
                                                    $includeitem = (($assessment->duedate > $next7days) && ($assessment->duedate < $nextmonth));
                                                    break;
                                            }
                                            if ($includeitem) {
                                                $itemicon = '';
                                                $iconalt = '';
                                                if ($iconurl = $cm->get_icon_url()->out(false)) {
                                                    $itemicon = $iconurl;
                                                    $a = new \stdClass();
                                                    $a->modulename = get_string('modulename', $item->itemmodule);
                                                    $a->activityname = $cm->name;
                                                    $iconalt = get_string('icon_alt_text', 'block_newgu_spdetails', $a);
                                                }
                                                $assessmentweight = self::return_weight($item->aggregationcoef);
                                                $assessmenttype = self::return_assessmenttype($course->fullname,
                                                $item->aggregationcoef);
                                                $status = $activityitem->get_status($USER->id);
                                                $duedate = $activityitem->get_formattedduedate($assessment->duedate);
                                                $rawduedate = $activityitem->get_rawduedate();
                                                $tmp = [
                                                    'id' => $assessment->id,
                                                    'courseurl' => $courseurl->out(),
                                                    'coursename' => $course->shortname,
                                                    'assessment_url' => $activityitem->get_assessmenturl(),
                                                    'item_icon' => $itemicon,
                                                    'icon_alt' => $iconalt,
                                                    'item_name' => $assessment->name,
                                                    'assessment_type' => $assessmenttype,
                                                    'assessment_weight' => $assessmentweight . '%',
                                                    'raw_assessment_weight' => $assessmentweight,
                                                    'due_date' => $duedate,
                                                    'raw_due_date' => $rawduedate,
                                                    'grade_status' => $status->grade_status,
                                                    'status_link' => $status->status_link,
                                                    'status_class' => $status->status_class,
                                                    'status_text' => $status->status_text,
                                                    'gradebookenabled' => '',
                                                ];

                                                $assessmentdata[] = $tmp;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $assessmentsdue['chart_header'] = $assessmentsdueheader;

        if (!$assessmentdata) {
            return $assessmentsdue;
        }

        $assessmentsdue['assessmentitems'] = $assessmentdata;

        return $assessmentsdue;
    }

    /**
     * Return the summary of assessments that have been marked, submitted, are
     * outstanding or are overdue.
     *
     * @return array
     */
    public static function get_assessmentsummary(): array {

        global $DB, $USER, $PAGE;

        $PAGE->set_context(\context_system::instance());

        $marked = 0;
        $totaloverdue = 0;
        $totalsubmissions = 0;
        $totaltosubmit = 0;
        $sortstring = 'shortname asc';
        $currentcourses = \local_gugrades\api::dashboard_get_courses($USER->id, true, false, $sortstring);

        $stats = [
            'total_tosubmit' => 0,
            'total_overdue' => 0,
            'total_submissions' => 0,
            'marked' => 0,
        ];

        if (!$currentcourses) {
            return $stats;
        }

        $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
        foreach ($currentcourses as $course) {
            // Make sure we are enrolled as a student on this course.
            if (\block_newgu_spdetails\api::return_isstudent($course->id, $USER->id)) {
                $activities = self::get_activities($course->id);
                if ($activities) {
                    foreach ($activities as $activityitem) {
                        if (!in_array($activityitem->itemmodule, \block_newgu_spdetails\activity::$excludedactivities)) {
                            $cm = get_coursemodule_from_instance($activityitem->itemmodule, $activityitem->iteminstance,
                            $activityitem->courseid);
                            $modinfo = get_fast_modinfo($activityitem->courseid);
                            $cms = $modinfo->get_cms();
                            if (array_key_exists($cm->id, $cms)) {
                                $cm = $modinfo->get_cm($cm->id);
                                if ($cm->uservisible) {
                                    // MGU-576/MGU-802 - Only include LTI activities if they have been selected.
                                    // Note that LTI activities only become a "gradable" activity when they have been set to accept grades!
                                    if ($activityitem->itemmodule == 'lti') {
                                        if (is_array($ltiactivities) && !in_array($activityitem->iteminstance, $ltiactivities)) {
                                            continue;
                                        }
                                    }

                                    // We had overlooked that we needed to check the course type when collating these numbers.
                                    // If the course that this activity belongs to is a MyGrades course, first check if we have
                                    // any 'Released' grades, for the Graded section - and then increment the total accordingly.
                                    if ($course->gugradesenabled) {
                                        $params = [
                                            'courseid' => $activityitem->courseid,
                                            'gradeitemid' => $activityitem->id,
                                            'userid' => $USER->id,
                                            'gradetype' => 'RELEASED',
                                            'iscurrent' => 1,
                                        ];
                                        if ($usergrades = $DB->get_records('local_gugrades_grade', $params)) {
                                            // Swap all of this for the relevant mygrades API calls - if/when one exists.
                                            foreach ($usergrades as $usergrade) {
                                                // MGU-631 - Honour hidden grades and hidden activities.
                                                $isgradehidden = \local_gugrades\api::is_grade_hidden($activityitem->id, $USER->id);
                                                if (!$isgradehidden) {
                                                    $marked++;
                                                }
                                                break;
                                            }
                                        } else {
                                            $gradestatus = \block_newgu_spdetails\grade::get_grade_status_and_feedback($activityitem->courseid,
                                                $activityitem->id,
                                                $USER->id,
                                                $activityitem->gradetype,
                                                $activityitem->scaleid,
                                                $activityitem->grademax,
                                                ''
                                            );
                                            $status = $gradestatus->grade_status;
                                            if ($status == get_string('status_submitted', 'block_newgu_spdetails')) {
                                                $totalsubmissions++;
                                            }

                                            if ($status == get_string('status_submit', 'block_newgu_spdetails')) {
                                                $totaltosubmit++;
                                            }

                                            if ($status == get_string('status_overdue', 'block_newgu_spdetails')) {
                                                $totaloverdue++;
                                            }

                                            if ($status == get_string('status_graded', 'block_newgu_spdetails')) {
                                                if (($gradestatus->grade_to_display != null) && ($gradestatus->grade_to_display !=
                                                get_string('status_text_tobeconfirmed', 'block_newgu_spdetails'))) {
                                                    $marked++;
                                                }
                                            }
                                        }

                                    } else {

                                        $gradestatus = \block_newgu_spdetails\grade::get_grade_status_and_feedback($activityitem->courseid,
                                            $activityitem->id,
                                            $USER->id,
                                            $activityitem->gradetype,
                                            $activityitem->scaleid,
                                            $activityitem->grademax,
                                            ''
                                        );
                                        $status = $gradestatus->grade_status;
                                        if ($status == get_string('status_submitted', 'block_newgu_spdetails')) {
                                            $totalsubmissions++;
                                        }

                                        if ($status == get_string('status_submit', 'block_newgu_spdetails')) {
                                            $totaltosubmit++;
                                        }

                                        if ($status == get_string('status_overdue', 'block_newgu_spdetails')) {
                                            $totaloverdue++;
                                        }

                                        if ($status == get_string('status_graded', 'block_newgu_spdetails')) {
                                            if (($gradestatus->grade_to_display != null) && ($gradestatus->grade_to_display !=
                                            get_string('status_text_tobeconfirmed', 'block_newgu_spdetails'))) {
                                                $marked++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $stats = [
            'total_tosubmit' => $totaltosubmit,
            'total_overdue' => $totaloverdue,
            'total_submissions' => $totalsubmissions,
            'marked' => $marked,
        ];

        return $stats;
    }

    /**
     * Return only the assessments that have been:
     * Submitted
     * Are still to be submitted
     * Overdue
     * Marked/Graded
     *
     * @param int $charttype
     * @return array
     */
    public static function get_assessmentsummarybytype(int $charttype): array {
        global $DB, $CFG, $USER, $PAGE;

        $PAGE->set_context(\context_system::instance());

        $sortstring = 'shortname asc';
        $courses = \local_gugrades\api::dashboard_get_courses($USER->id, true, false, $sortstring);

        $assessmentsdue = [];

        if (!$courses) {
            return $assessmentsdue;
        }

        $dateheader = '';
        $option = '';
        $whichstatus = '';
        $show_grade_column = false;
        switch ($charttype) {
            case 0:
                $option = get_string('status_text_tobesubmitted', 'block_newgu_spdetails');
                $dateheader = get_string('header_duedate', 'block_newgu_spdetails');
                $whichstatus = get_string('status_submit', 'block_newgu_spdetails');
                break;
            case 1:
                $option = get_string('status_text_overdue', 'block_newgu_spdetails');
                $dateheader = get_string('header_duedate', 'block_newgu_spdetails');
                $whichstatus = get_string('status_overdue', 'block_newgu_spdetails');
                break;
            case 2:
                $option = get_string('status_text_submitted', 'block_newgu_spdetails');
                $dateheader = get_string('header_datesubmitted', 'block_newgu_spdetails');
                $whichstatus = get_string('status_submitted', 'block_newgu_spdetails');
                break;
            case 3:
                $option = get_string('status_text_graded', 'block_newgu_spdetails');
                $dateheader = get_string('header_dategraded', 'block_newgu_spdetails');
                $whichstatus = get_string('status_graded', 'block_newgu_spdetails');
                $show_grade_column = true;
                break;
        }

        $assessmentsummaryheader = get_string('header_assessmentsummary', 'block_newgu_spdetails', $option);

        $assessmentdata = [];
        $ltiactivities = \block_newgu_spdetails\api::get_lti_activities();
        foreach ($courses as $course) {
            // Make sure we are enrolled as a student on this course.
            if (\block_newgu_spdetails\api::return_isstudent($course->id, $USER->id)) {
                $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
                $activities = self::get_activities($course->id);
                if ($activities) {
                    foreach ($activities as $activityitem) {
                        if (!in_array($activityitem->itemmodule, \block_newgu_spdetails\activity::$excludedactivities)) {
                            $cm = get_coursemodule_from_instance($activityitem->itemmodule, $activityitem->iteminstance,
                            $activityitem->courseid);
                            $modinfo = get_fast_modinfo($activityitem->courseid);
                            $cms = $modinfo->get_cms();
                            if (array_key_exists($cm->id, $cms)) {
                                $cm = $modinfo->get_cm($cm->id);
                                if ($cm->uservisible) {
                                    // MGU-576/MGU-802 - Only include LTI activities if they have been selected.
                                    // Note that LTI activities only become a "gradable" activity when they have been set to accept grades!
                                    if ($activityitem->itemmodule == 'lti') {
                                        if (is_array($ltiactivities) && !in_array($activityitem->iteminstance, $ltiactivities)) {
                                            continue;
                                        }
                                    }

                                    // We had overlooked that we needed to check the course type when collating these numbers.
                                    // If the course that this activity belongs to is a MyGrades course, first check if we have
                                    // any 'Released' grades, for the Graded section.
                                    $grade = '';
                                    $gradeclass = '';
                                    $gradeprovisional = '';
                                    $gradefeedback = '';
                                    $gradefeedbacklink = '';
                                    if ($charttype == 3) {
                                        if ($course->gugradesenabled) {
                                            $params = [
                                                'courseid' => $activityitem->courseid,
                                                'gradeitemid' => $activityitem->id,
                                                'userid' => $USER->id,
                                                'gradetype' => 'RELEASED',
                                                'iscurrent' => 1,
                                            ];
                                            if ($usergrades = $DB->get_records('local_gugrades_grade', $params)) {
                                                // Swap all of this for the relevant mygrades API calls - if/when one exists.
                                                $gradestatus = new stdClass();
                                                foreach ($usergrades as $usergrade) {
                                                    // MGU-631 - Honour hidden grades and hidden activities.
                                                    $isgradehidden = \local_gugrades\api::is_grade_hidden($activityitem->id, $USER->id);
                                                    if (!$isgradehidden) {
                                                        $gradestatus->grade_date = $usergrade->audittimecreated;
                                                        $gradestatus->assessment_url = $CFG->wwwroot . '/' . $activityitem->itemtype . '/'
                                                        . $activityitem->itemmodule . '/view.php?id=' . $cm->id;
                                                        $gradestatus->grade_status = get_string('status_graded', 'block_newgu_spdetails');
                                                        $gradestatus->status_text = get_string('status_text_graded', 'block_newgu_spdetails');
                                                        $gradestatus->status_class = get_string('status_class_graded', 'block_newgu_spdetails');
                                                        $gradestatus->status_link = '';
                                                        $gradestatus->grade_to_display = get_string('status_text_graded', 'block_newgu_spdetails');

                                                        $grade =  $usergrade->displaygrade;
                                                        $gradeclass = true;
                                                        $gradeprovisional = false;
                                                        $gradefeedback = get_string('status_text_viewfeedback', 'block_newgu_spdetails');
                                                        // Because we don't have an activity instance, we can't call get_assessmenturl().
                                                        $gradefeedbacklink = $CFG->wwwroot . '/' . $activityitem->itemtype . '/'
                                                        . $activityitem->itemmodule . '/view.php?id=' . $cm->id . '#page-footer';;
                                                    }
                                                    break;
                                                }
                                            } else {
                                                // Get the activity based on its type...
                                                $gradestatus = \block_newgu_spdetails\grade::get_grade_status_and_feedback($activityitem->courseid,
                                                    $activityitem->id,
                                                    $USER->id,
                                                    $activityitem->gradetype,
                                                    $activityitem->scaleid,
                                                    $activityitem->grademax,
                                                    '',
                                                );

                                                $grade = $gradestatus->grade_to_display;
                                                $gradeclass = $gradestatus->grade_class;
                                                $gradeprovisional = $gradestatus->grade_provisional;
                                                $gradefeedback = $gradestatus->grade_feedback;
                                                $gradefeedbacklink = $gradestatus->grade_feedback_link;

                                                if (($gradestatus->grade_to_display != null) && ($gradestatus->grade_to_display ==
                                                    get_string('status_text_tobeconfirmed', 'block_newgu_spdetails'))) {
                                                        continue;
                                                }
                                            }
                                        } else {
                                            // Get the activity based on its type...
                                            $gradestatus = \block_newgu_spdetails\grade::get_grade_status_and_feedback($activityitem->courseid,
                                                $activityitem->id,
                                                $USER->id,
                                                $activityitem->gradetype,
                                                $activityitem->scaleid,
                                                $activityitem->grademax,
                                                '',
                                            );

                                            $grade = $gradestatus->grade_to_display;
                                            $gradeclass = $gradestatus->grade_class;
                                            $gradeprovisional = $gradestatus->grade_provisional;
                                            $gradefeedback = $gradestatus->grade_feedback;
                                            $gradefeedbacklink = $gradestatus->grade_feedback_link;

                                            if (($gradestatus->grade_to_display != null) && ($gradestatus->grade_to_display ==
                                                get_string('status_text_tobeconfirmed', 'block_newgu_spdetails'))) {
                                                    continue;
                                            }
                                        }
                                    } else {

                                        // Get the activity based on its type...
                                        $gradestatus = \block_newgu_spdetails\grade::get_grade_status_and_feedback($activityitem->courseid,
                                            $activityitem->id,
                                            $USER->id,
                                            $activityitem->gradetype,
                                            $activityitem->scaleid,
                                            $activityitem->grademax,
                                            '',
                                        );
                                    }

                                    $status = $gradestatus->grade_status;
                                    $date = '';
                                    $rawduedate = '';

                                    if ($status == $whichstatus) {
                                        $itemicon = '';
                                        $iconalt = '';
                                        if ($iconurl = $cm->get_icon_url()->out(false)) {
                                            $itemicon = $iconurl;
                                            $a = new \stdClass();
                                            $a->modulename = get_string('modulename', $activityitem->itemmodule);
                                            $a->activityname = $cm->name;
                                            $iconalt = get_string('icon_alt_text', 'block_newgu_spdetails', $a);
                                        }

                                        switch($charttype) {
                                            case 3:
                                                if (property_exists($gradestatus, 'grade_date') && $gradestatus->grade_date != '') {
                                                    $date = userdate($gradestatus->grade_date);
                                                    $rawduedate = $gradestatus->grade_date;
                                                }
                                                break;
                                            default:
                                                $date = $gradestatus->due_date;
                                                $rawduedate = $gradestatus->raw_due_date;
                                                break;
                                        }

                                        $assessmenttype = self::return_assessmenttype($course->fullname,
                                        $activityitem->aggregationcoef);
                                        $assessmentweight = self::return_weight($activityitem->aggregationcoef);
                                        $tmp = [
                                            'id' => $activityitem->id,
                                            'courseurl' => $courseurl->out(),
                                            'coursename' => $course->shortname,
                                            'assessment_url' => $gradestatus->assessment_url,
                                            'item_icon' => $itemicon,
                                            'icon_alt' => $iconalt,
                                            'item_name' => $activityitem->itemname,
                                            'assessment_type' => $assessmenttype,
                                            'assessment_weight' => $assessmentweight . '%',
                                            'raw_assessment_weight' => $assessmentweight,
                                            'due_date' => $date,
                                            'raw_due_date' => $rawduedate,
                                            'grade_status' => $gradestatus->grade_status,
                                            'status_link' => $gradestatus->status_link,
                                            'status_class' => $gradestatus->status_class,
                                            'status_text' => $gradestatus->status_text,
                                            'grade' => $grade,
                                            'grade_class' => $gradeclass,
                                            'grade_provisional' => $gradeprovisional,
                                            'grade_feedback' => $gradefeedback,
                                            'grade_feedback_link' => $gradefeedbacklink,
                                            'gradebookenabled' => '',
                                        ];

                                        $assessmentdata[] = $tmp;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $assessmentsdue['chart_header'] = $assessmentsummaryheader;

        if (!$assessmentdata) {
            return $assessmentsdue;
        }

        $assessmentsdue['date_header'] = $dateheader;
        $assessmentsdue['show_grade_column'] = $show_grade_column;
        $assessmentsdue['assessmentitems'] = $assessmentdata;

        return $assessmentsdue;
    }

}
