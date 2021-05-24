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
 * @package    block_gu_spdetails
 * @copyright  2021 Accenture
 * @author     Franco Louie Magpusao, Jose Maria Abreu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
define('ASSESSMENTS_PER_PAGE', 12);
define('TAB_CURRENT', 'current');
define('TAB_PAST', 'past');
define('SORTBY_COURSE', 'coursetitle');
define('SORTBY_DATE', 'duedate');
define('SORTBY_STARTDATE', 'startdate');
define('SORTBY_ENDDATE', 'enddate');
define('SORTORDER_ASC', 'asc');
define('SORTORDER_DESC', 'desc');

require_once($CFG->libdir . '/gradelib.php');

class assessments_details {

    /**
     * Retrieves paginated assessments
     *
     * @param string $activetab
     * @param int $page
     * @param string $sortby
     * @param string $sortorder
     * @return string HTML
     */
    public static function retrieve_assessments($activetab, $page, $sortby, $sortorder, $subcategory = null) {
        global $DB, $USER, $OUTPUT, $PAGE;
        $PAGE->set_context(context_system::instance());

        $userid = $USER->id;
        $limit = ASSESSMENTS_PER_PAGE;
        $offset = $page * $limit;
        $params = array('activetab' => $activetab, 'page' => $page,
                            'sortby' => $sortby, 'sortorder' => $sortorder);
        $url = new moodle_url('/index.php', $params);

        $currentsortby = array(SORTBY_COURSE => get_string('option_course', 'block_gu_spdetails'),
                                SORTBY_DATE => get_string('option_date', 'block_gu_spdetails'));
        $pastsortby = array(SORTBY_COURSE => get_string('option_course', 'block_gu_spdetails'),
                            SORTBY_STARTDATE => get_string('option_startdate', 'block_gu_spdetails'),
                            SORTBY_ENDDATE => get_string('option_enddate', 'block_gu_spdetails'));

        $issubcategory = !is_null($subcategory);

        $items = self::retrieve_gradable_activities($activetab, $userid, $sortby, $sortorder, $subcategory);

        $totalassessments = 0;
        $html = null;

        if ($items) {
            $totalassessments = count($items);
            $paginatedassessments = array_splice($items, $offset, $limit);

            $html .= html_writer::start_tag('div', array('class' => 'assessments-details-sort-container'));
            if ($activetab === TAB_CURRENT && is_null($subcategory)) {
                $selectsortby = get_string('select_currentsortby', 'block_gu_spdetails');
                $html .= html_writer::tag('label', get_string('label_sortby', 'block_gu_spdetails'),
                                                array('class' => 'assessments-details-sort-label h5',
                                                    'for' => 'menu'.$selectsortby));
                $html .= html_writer::select($currentsortby, $selectsortby, $sortby, false);
            } else if ($activetab === TAB_PAST) {
                $selectsortby = get_string('select_pastsortby', 'block_gu_spdetails');
                $html .= html_writer::tag('label', get_string('label_sortby', 'block_gu_spdetails'),
                                                array('class' => 'assessments-details-sort-label h5',
                                                    'for' => 'menu'.$selectsortby));
                $html .= html_writer::select($pastsortby, $selectsortby, $sortby, false);
            }
            $html .= html_writer::end_tag('div');
            $html .= html_writer::start_tag('table', array('class' =>
                                                'table table-responsive assessments-details-table'));
            $html .= html_writer::start_tag('thead');
            $html .= html_writer::start_tag('tr');
            $html .= $issubcategory ? "" : html_writer::tag('th', get_string('header_course', 'block_gu_spdetails'),
                                            array('id' => 'sortby_course', 'class' => 'td20 th-sortable',
                                                'data-value' => ''));
            $html .= html_writer::tag('th', $issubcategory ? get_string('component', 'report_eventlist')
                                                           : get_string('header_assessment', 'block_gu_spdetails'),
                                            array('class' => 'td20'));
            $html .= $issubcategory ? "" : html_writer::tag('th', get_string('header_type', 'block_gu_spdetails'),
                                            array('class' => 'td10'));
            $html .= html_writer::tag('th', get_string('header_weight', 'block_gu_spdetails'),
                                            array('class' => 'td05'));
            if ($activetab === TAB_CURRENT) {
                $html .= html_writer::tag('th', get_string('header_duedate', 'block_gu_spdetails'),
                                                array('id' => 'sortby_date', 'class' => 'td10 th-sortable',
                                                    'data-value' => ''));
                $html .= html_writer::tag('th', get_string('header_status', 'block_gu_spdetails'),
                                                array('class' => 'td15'));
            } else if ($activetab === TAB_PAST) {
                $html .= html_writer::tag('th', get_string('header_coursestartdate', 'block_gu_spdetails'),
                                                array('id' => 'sortby_startdate', 'class' => 'td05 th-sortable',
                                                'data-value' => ''));
                $html .= html_writer::tag('th', get_string('header_courseenddate', 'block_gu_spdetails'),
                                                array('id' => 'sortby_enddate', 'class' => 'td05 th-sortable',
                                                'data-value' => ''));
                $html .= html_writer::tag('th', get_string('header_submission', 'block_gu_spdetails'),
                                                array('class' => 'td15'));
            }
            $html .= html_writer::tag('th', get_string('header_grade', 'block_gu_spdetails'),
                                            array('class' => 'td15'));
            $html .= html_writer::tag('th', get_string('header_feedback', 'block_gu_spdetails'),
                                            array('class' => 'td10'));
            $html .= html_writer::end_tag('tr');
            $html .= html_writer::end_tag('thead');
            $html .= html_writer::start_tag('tbody');

            foreach ($paginatedassessments as $assessment) {
                $html .= html_writer::start_tag('tr');
                // Course title.
                if (!$issubcategory) {
                        $html .= html_writer::start_tag('td', array('class' => 'td20'));
                        $html .= html_writer::tag('a', $assessment->coursetitle,
                                                array('href' => $assessment->courseurl));
                        $html .= html_writer::end_tag('td');
                }
                // Assessment name.
                $html .= html_writer::start_tag('td', array('class' => 'td20'));
                $html .= html_writer::tag('a', $assessment->assessmentname,
                                                array('href' => ($assessment->feedback->issubcategory) ? "#assessments-container"
                                                                : $assessment->assessmenturl,
                                                    'class' => ($assessment->feedback->issubcategory) ? 'subcategory-row' : "",
                                                    'data-id' => $assessment->id,
                                                    'data-name' => $assessment->assessmentname,
                                                    'data-course' => $assessment->coursetitle,
                                                    'data-grade' => $assessment->grading->gradetext,
                                                    'data-weight' => $assessment->weight));
                $html .= html_writer::end_tag('td');
                // Assessment type.
                $html .= $issubcategory ? "" : html_writer::tag('td', $assessment->assessmenttype, array('class' => 'td10'));
                // Weight.
                $html .= html_writer::tag('td', $assessment->weight, array('class' => 'td05'));
                if ($activetab !== TAB_PAST) {
                    // Due date.
                    $html .= html_writer::start_tag('td', array('class' => 'td10'));
                    if ($assessment->feedback->issubcategory) {
                        $html .= html_writer::tag('a', $assessment->feedback->feedbacktext,
                        array('href' => "#assessments-container",
                                'class' => 'subcategory-row',
                                'data-id' => $assessment->id,
                                'data-name' => $assessment->assessmentname,
                                'data-course' => $assessment->coursetitle,
                                'data-grade' => $assessment->grading->gradetext,
                                'data-weight' => $assessment->weight));
                    } else {
                        $html .= $assessment->formattedduedate;
                    }
                    if ($assessment->hasextension) {
                        $html .= html_writer::start_span('extended').'*';
                        $html .= html_writer::start_span('extended-tooltip').
                                                            get_string('extended', 'block_gu_spdetails');
                        $html .= html_writer::end_span();
                        $html .= html_writer::end_span();
                    }
                    $html .= html_writer::end_tag('td');
                    // Status.
                    $html .= html_writer::start_tag('td', array('class' => 'td15'));
                    if ($assessment->status->hasstatusurl) {
                        $html .= html_writer::start_tag('a',
                                                            array('href' => $assessment->assessmenturl,
                                                            'class' => get_string('class_link', 'block_gu_spdetails')));
                        $html .= html_writer::start_span(get_string('class_default', 'block_gu_spdetails').
                                                            $assessment->status->class).$assessment->status->statustext;
                        $html .= html_writer::end_span();
                        $html .= html_writer::end_tag('a');
                    } else {
                        $html .= html_writer::start_span(get_string('class_default', 'block_gu_spdetails').
                                                            $assessment->status->class).$assessment->status->statustext;
                        $html .= html_writer::end_span();
                    }
                    $html .= html_writer::end_tag('td');
                } else {
                    $html .= html_writer::tag('td', $assessment->formattedstartdate, array('class' => 'td05'));
                    $html .= html_writer::tag('td', $assessment->formattedenddate, array('class' => 'td05'));
                    $html .= html_writer::start_tag('td', array('class' => 'td15'));
                    if ($assessment->feedback->issubcategory) {
                        $html .= html_writer::tag('a', $assessment->feedback->feedbacktext,
                                                array('href' => "#assessments-container",
                                                            'class' => 'subcategory-row',
                                                            'data-id' => $assessment->id,
                                                            'data-name' => $assessment->assessmentname,
                                                            'data-course' => $assessment->coursetitle,
                                                            'data-grade' => $assessment->grading->gradetext,
                                                            'data-weight' => $assessment->weight));
                    } else {
                        $html .= html_writer::tag('a', get_string('viewsubmission', 'block_gu_spdetails'),
                                                array('href' => $assessment->assessmenturl));
                    }
                    $html .= html_writer::end_tag('td');
                }
                // Grade.
                $html .= html_writer::start_tag('td', array('class' => 'td15'));
                if ($assessment->grading->hasgrade) {
                    $html .= html_writer::start_span('graded').$assessment->grading->gradetext;
                    $html .= html_writer::end_span();
                    if ($assessment->grading->isprovisional) {
                        $html .= get_string('provisional', 'block_gu_spdetails');
                    }
                } else {
                    $html .= $assessment->grading->gradetext;
                }
                $html .= html_writer::end_tag('td');
                // Feedback.
                $html .= html_writer::start_tag('td', array('class' => 'td10'));
                if ($assessment->feedback->hasfeedback) {
                        $html .= html_writer::tag('a', $assessment->feedback->feedbacktext,
                                                    array('href' => ($assessment->feedback->issubcategory)
                                                            ? "#assessments-container" : $assessment->feedback->feedbackurl,
                                                        'class' => ($assessment->feedback->issubcategory) ? 'subcategory-row' : "",
                                                        'data-id' => $assessment->id,
                                                        'data-name' => $assessment->assessmentname,
                                                        'data-course' => $assessment->coursetitle,
                                                        'data-grade' => $assessment->grading->gradetext,
                                                        'data-weight' => $assessment->weight));
                } else {
                        $html .= $assessment->feedback->feedbacktext;
                }
                $html .= html_writer::end_tag('td');
                $html .= html_writer::end_tag('tr');
            }

            $html .= html_writer::end_tag('tbody');
            $html .= html_writer::end_tag('table');
            $html .= $OUTPUT->paging_bar($totalassessments, $page, $limit, $url);
        } else {
            $html .= html_writer::start_tag('div', array('class' => 'text-xs-center text-center mt-3'));
            $html .= html_writer::tag('img', '', array('class' => 'empty-placeholder-image-lg mt-1',
                                                    'src' => $OUTPUT->image_url('noassessments', 'theme'),
                                                    'alt' => get_string('noassessments', 'block_gu_spdetails')));
            $html .= html_writer::tag('p', get_string('noassessments', 'block_gu_spdetails'),
                                                array('class' => 'text-muted mt-3'));
            $html .= html_writer::end_tag('div');
        }

        return $html;
    }

    /**
     * Returns current or past enrolled courses with enabled 'show_on_studentdashboard' custom field
     *
     * @param string $activetab
     * @param string $userid
     * @return array Array of Course IDs
     */
    public static function retrieve_courses($activetab, $userid) {
        global $DB;

        $fields = "c.id";
        $customfieldjoin = "JOIN {customfield_field} cff
                            ON cff.shortname = 'show_on_studentdashboard'
                            JOIN {customfield_data} cfd
                            ON (cfd.fieldid = cff.id AND cfd.instanceid = c.id)";
        $enrolmentselect = "SELECT DISTINCT e.courseid FROM {enrol} e
                            JOIN {user_enrolments} ue
                            ON (ue.enrolid = e.id AND ue.userid = ?)";
        $enrolmentjoin = "JOIN ($enrolmentselect) en ON (en.courseid = c.id)";

        if ($activetab === TAB_CURRENT) {
            $customfieldwhere = "cfd.value = 1";
            $param = array($userid);
        } else {
            $enddate = time();
            $customfieldwhere = "cfd.value = 1 AND c.enddate + 86400 * 30 <= ?";
            $param = array($userid, $enddate);
        }

        $sql = "SELECT $fields FROM {course} c $customfieldjoin $enrolmentjoin
                WHERE $customfieldwhere";
        $results = $DB->get_records_sql($sql, $param);

        if ($results) {
            $studentcourses = array();
            foreach ($results as $courseid => $courseobject) {
                if (self::return_isstudent($courseid)) {
                        array_push($studentcourses, $courseid);
                }
            }
            return $studentcourses;
        } else {
            return array();
        }
    }

    /**
     * Retrieves Parent and 2nd level category ids
     *
     * @param string $courseids
     * @return array $ids
     */
    public static function retrieve_parent_category($courseids) {
        global $DB;

        $courses = implode(', ', $courseids);
        $sql = "SELECT id FROM {grade_categories} WHERE parent IS NULL AND courseid IN ($courses)";
        $uncategorised = $DB->get_records_sql($sql);
        $ids = array();
        foreach ($uncategorised as $key => $value) {
            array_push($ids, $key);
        }
        return $ids;
    }

    /**
     * Retrieve 2nd level category ids
     *
     * @param string $parentids
     * @return array $ids
     */
    public static function retrieve_2nd_level($parentids) {
        global $DB;

        $parents = implode(', ', $parentids);
        $sql = "SELECT id FROM {grade_categories} WHERE parent IN ($parents) AND fullname != 'DO NOT USE'";
        $level2 = $DB->get_records_sql($sql);
        $ids = array();
        foreach ($level2 as $key => $value) {
            array_push($ids, $key);
        }
        return $ids;
    }
    /**
     * Retrieves gradable activities ('assign', 'forum', 'quiz', 'workshop') from the database
     *
     * @param string $activetab
     * @param int $page
     * @param string $sortby
     * @param string $sortorder
     * @return array $items
     */
    public static function retrieve_gradable_activities($activetab, $userid, $sortby, $sortorder, $subcategory) {
        global $DB;
        $enddate = time();

        $courses = self::retrieve_courses($activetab, $userid);
        $courseids = implode(', ', $courses);
        $issubcategory = !is_null($subcategory);
        $subcategoryparent = new stdClass;

        if ($issubcategory) {
            $subcat = grade_category::fetch(array('id' => $subcategory));
            $subcategoryparent = is_null($subcat->parent) ? new stdClass : grade_category::fetch(array('id' => $subcat->parent));
        }

        if (!empty($courses)) {
            $parentids = self::retrieve_parent_category($courses);
            $level2ids = self::retrieve_2nd_level($parentids);
            $categories = array_merge($parentids, $level2ids);
            $categoryids = $issubcategory ? $subcategory : implode(', ', $categories);

            $categorylimit = "AND gi.categoryid IN ($categoryids)";
            $convertedgradeselect = "gic.id as convertedgradeid, gp.finalgrade as provisionalgrade,
                                     gip.idnumber, NULL as outcomeid";
            $convertedgradejoin = "LEFT JOIN {grade_items} gic ON (gic.iteminfo = gi.id AND gic.itemname = 'Converted Grade')
                                    LEFT JOIN {grade_items} gip ON (gip.iteminfo = gi.id AND gip.itemname = 'Provisional Grade')
                                    LEFT JOIN {grade_grades} gp ON (gp.itemid = gip.id AND gp.userid = ?)
                                    LEFT JOIN {scale} sp ON (sp.id = gip.idnumber)";
            $assignfields = "cm.id, a.course AS courseid,
                                CASE
                                WHEN cs.name IS NOT NULL THEN cs.name
                                WHEN cs.section != 0 THEN CONCAT('Topic ', cs.section)
                                ELSE c.fullname
                                END AS coursetitle,
                                gi.itemmodule AS modname, a.name AS activityname,
                                gc.fullname AS gradecategoryname, gc.aggregation,
                                gi.aggregationcoef, gi.aggregationcoef2,
                                CASE
                                WHEN ao.allowsubmissionsfromdate IS NOT NULL
                                THEN ao.allowsubmissionsfromdate
                                ELSE a.allowsubmissionsfromdate
                                END AS allowsubmissionsfromdate,
                                CASE
                                WHEN auf.extensionduedate IS NOT NULL AND auf.extensionduedate != 0
                                THEN auf.extensionduedate
                                WHEN ao.duedate IS NOT NULL THEN ao.duedate
                                ELSE a.duedate
                                END AS duedate,
                                CASE
                                WHEN ao.cutoffdate IS NOT NULL THEN ao.cutoffdate
                                ELSE a.cutoffdate
                                END AS cutoffdate,
                                a.gradingduedate,
                                CASE
                                WHEN auf.extensionduedate IS NOT NULL AND auf.extensionduedate != 0
                                THEN 1
                                ELSE 0
                                END AS hasextension,
                                gi.gradetype, gi.grademin, gi.grademax, s.scale, gg.finalgrade,
                                gg.information AS gradeinformation, gg.feedback,
                                aff.numfiles AS feedbackfiles, ptcfg.value AS hasturnitin,
                                asub.status, a.nosubmissions AS submissions,
                                NULL as quizfeedback, c.startdate, c.enddate, $convertedgradeselect";
            $assignjoins = "LEFT JOIN {assign_overrides} ao ON (ao.assignid = a.id AND ao.userid = ?)
                            LEFT JOIN {assign_user_flags} auf ON (auf.assignment = a.id AND auf.userid = ?)
                            LEFT JOIN {assign_grades} ag ON (ag.assignment = a.id AND ag.userid = ?)
                            LEFT JOIN (SELECT a.* FROM {assign_submission} a
                                            LEFT OUTER JOIN {assign_submission} b
                                            ON a.id = b.id AND a.attemptnumber < b.attemptnumber
                                            WHERE b.id IS NULL) asub
                                ON (asub.assignment = a.id AND asub.userid = ?)
                            LEFT JOIN {assignfeedback_file} aff ON (aff.assignment = a.id AND aff.grade = ag.id)
                            LEFT JOIN {modules} m ON (m.name = 'assign')
                            JOIN {course_modules} cm ON (cm.course = a.course AND cm.instance = a.id
                                AND cm.module = m.id AND cm.deletioninprogress = 0)
                            LEFT JOIN {plagiarism_turnitin_config} ptcfg ON (ptcfg.name = 'use_turnitin'
                                AND ptcfg.value = 1 AND ptcfg.cm = cm.id)
                            LEFT JOIN {grade_items} gi ON (gi.iteminstance = cm.instance AND gi.courseid = a.course
                                AND gi.itemtype = 'mod' AND gi.itemmodule = 'assign')
                            LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = ?)
                            LEFT JOIN {grade_categories} gc ON gc.id = gi.categoryid
                            LEFT JOIN {scale} s ON s.id = gi.scaleid
                            LEFT JOIN {course} c ON c.id = a.course
                            LEFT JOIN {course_sections} cs ON (cs.course = c.id AND cs.id = cm.section)
                            $convertedgradejoin";
            $assignenddate = ($activetab === TAB_CURRENT) ?
                            "AND (c.enddate + 86400 * 30 > ? OR
                            CASE
                            WHEN auf.extensionduedate IS NOT NULL AND auf.extensionduedate != 0
                            THEN auf.extensionduedate
                            WHEN ao.duedate IS NOT NULL THEN ao.duedate
                            ELSE a.duedate END + 86400 * 30 > ?)" :
                            "AND c.enddate  + 86400 * 30 <= ? AND
                            CASE
                            WHEN auf.extensionduedate IS NOT NULL AND auf.extensionduedate != 0
                            THEN auf.extensionduedate
                            WHEN ao.duedate IS NOT NULL THEN ao.duedate
                            ELSE a.duedate END + 86400 * 30 <= ?";
            $assignwhere = "a.course IN ($courseids) $assignenddate $categorylimit";
            $assignsql = "SELECT $assignfields FROM {assign} a $assignjoins WHERE $assignwhere";
            $assignparams = array($userid, $userid, $userid, $userid, $userid, $userid, $enddate, $enddate);

            $forumfields = "cm.id, f.course AS courseid,
                            CASE
                            WHEN cs.name IS NOT NULL THEN cs.name
                            WHEN cs.section > 0 THEN CONCAT('Topic ', cs.section)
                            ELSE c.fullname END AS coursetitle,
                            gi.itemmodule AS modname, f.name AS activityname,
                            gc.fullname AS gradecategoryname, gc.aggregation,
                            gi.aggregationcoef, gi.aggregationcoef2,
                            NULL AS allowsubmissionsfromdate,
                            f.duedate, f.cutoffdate, f.cutoffdate AS gradingduedate,
                            NULL AS hasextension, gi.gradetype, gi.grademin, gi.grademax,
                            s.scale, gg.finalgrade, gg.information AS gradeinformation,
                            gg.feedback, NULL AS feedbackfiles, NULL AS hasturnitin,
                            CASE
                            WHEN fd.id IS NOT NULL THEN 'submitted'
                            ELSE NULL
                            END AS status, NULL AS submissions,
                            NULL as quizfeedback, c.startdate, c.enddate, $convertedgradeselect";
            $forumjoins = "LEFT JOIN {modules} m ON (m.name = 'forum')
                            JOIN {course_modules} cm ON (cm.course = f.course AND cm.instance = f.id
                                AND cm.module = m.id AND cm.deletioninprogress = 0)
                            LEFT JOIN {course} c ON c.id = f.course
                            LEFT JOIN {course_sections} cs ON (cs.course = c.id AND cs.id = cm.section)
                            JOIN (SELECT gi1.id, gi1.categoryid, gi1.gradetype, gi1.grademax, gi1.grademin,
                                gi1.gradepass, gi1.scaleid, gi1.aggregationcoef, gi1.aggregationcoef2,
                                gi1.iteminstance, gi1.courseid, gi1.itemmodule
                                FROM {grade_items} gi1
                                LEFT JOIN {grade_items} gi2 ON (gi2.iteminstance = gi1.iteminstance
                                AND gi2.itemmodule = gi1.itemmodule AND gi2.itemnumber <> gi1.itemnumber)
                                WHERE gi1.itemtype = 'mod' AND gi1.gradetype != 0
                                    AND (gi1.itemnumber = 1 OR gi2.itemnumber IS NULL)
                                    AND gi1.itemmodule = 'forum') gi
                                    ON (gi.iteminstance = cm.instance AND gi.courseid = c.id)
                            LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = ?)
                            LEFT JOIN {grade_categories} gc ON gc.id = gi.categoryid
                            LEFT JOIN {scale} s ON s.id = gi.scaleid
                            LEFT JOIN {forum_discussions} fd ON (fd.course = c.id AND fd.forum = f.id
                                    AND fd.userid = gg.userid)
                            $convertedgradejoin";
            $forumenddate = ($activetab === TAB_CURRENT) ?
                            "AND (c.enddate + 86400 * 30 > ?
                            OR f.duedate + 86400 * 30 > ?)" :
                            "AND c.enddate + 86400 * 30 <= ?
                            AND f.duedate + 86400 * 30 <= ?";
            $forumwhere = "f.course IN ($courseids) $forumenddate $categorylimit";
            $forumsql = "SELECT $forumfields FROM {forum} f $forumjoins WHERE $forumwhere";
            $forumparams = array($userid, $userid, $enddate, $enddate);

            $quizfields = "cm.id, q.course AS courseid,
                            CASE
                            WHEN cs.name IS NOT NULL THEN cs.name
                            WHEN cs.section != 0 THEN CONCAT('Topic ', cs.section)
                            ELSE c.fullname END AS coursetitle,
                            gi.itemmodule AS modname, q.name AS activityname,
                            gc.fullname AS gradecategoryname, gc.aggregation,
                            gi.aggregationcoef, gi.aggregationcoef2,
                            q.timeopen AS allowsubmissionsfromdate,
                            CASE
                                WHEN qo.timeclose IS NOT NULL THEN qo.timeclose
                                ELSE q.timeclose END AS duedate,
                            NULL AS cutoffdate,
                            CASE
                                WHEN qo.timeclose IS NOT NULL THEN qo.timeclose
                                ELSE q.timeclose END AS gradingduedate,
                            NULL AS hasextension, gi.gradetype, gi.grademin, gi.grademax,
                            NULL AS scale, gg.finalgrade, gg.information AS gradeinformation,
                            gg.feedback, NULL AS feedbackfiles, NULL AS hasturnitin,
                            qa.state AS status, NULL AS submissions,
                            qf.feedbacktext as quizfeedback, c.startdate, c.enddate, $convertedgradeselect";
            $quizjoins = "LEFT JOIN {quiz_overrides} AS qo ON (qo.quiz = q.id AND qo.userid = ?)
                            LEFT JOIN {quiz_grades} AS qg ON (qg.quiz = q.id AND qg.userid = ?)
                            LEFT JOIN {quiz_feedback} AS qf ON (qf.quizid = q.id AND qg.grade IS NOT NULL
                                AND (qg.grade > qf.mingrade OR (qg.grade = 0 AND qf.mingrade = 0))
                                AND qg.grade <= qf.maxgrade)
                            LEFT JOIN {quiz_attempts} AS qa ON (qa.quiz = q.id AND qa.userid = ?
                                AND qa.sumgrades IS NULL)
                            LEFT JOIN {modules} m ON (m.name = 'quiz')
                            JOIN {course_modules} cm ON (cm.course = q.course AND cm.instance = q.id
                                AND cm.module = m.id AND cm.deletioninprogress = 0)
                            LEFT JOIN {grade_items} gi ON (gi.iteminstance = cm.instance
                                AND gi.courseid = q.course AND gi.itemtype = 'mod' AND gi.itemmodule = 'quiz')
                            LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = ?)
                            LEFT JOIN {grade_categories} gc ON gc.id = gi.categoryid
                            LEFT JOIN {course} c ON c.id = q.course
                            LEFT JOIN {course_sections} cs ON (cs.course = c.id AND cs.id = cm.section)
                            $convertedgradejoin";
            $quizenddate = ($activetab === TAB_CURRENT) ?
                            "AND (c.enddate + 86400 * 30 > ? OR
                            CASE
                            WHEN qo.timeclose IS NOT NULL THEN qo.timeclose
                            ELSE q.timeclose END + 86400 * 30 > ?)" :
                            "AND c.enddate + 86400 * 30 <= ? AND
                            (CASE
                            WHEN qo.timeclose IS NOT NULL THEN qo.timeclose
                            ELSE q.timeclose END + 86400 * 30 <= ?)";
            $quizwhere = "q.course IN ($courseids) $quizenddate $categorylimit";
            $quizsql = "SELECT $quizfields FROM {quiz} q $quizjoins WHERE $quizwhere";
            $quizparams = array($userid, $userid, $userid, $userid, $userid, $enddate, $enddate);

            $workshopfields = "cm.id, w.course AS courseid,
                                CASE
                                WHEN cs.name IS NOT NULL THEN cs.name
                                WHEN cs.section != 0 THEN CONCAT('Topic ', cs.section)
                                ELSE c.fullname END AS coursetitle,
                                gi.itemmodule AS modname, w.name AS activityname,
                                gc.fullname AS gradecategoryname, gc.aggregation,
                                gi.aggregationcoef, gi.aggregationcoef2,
                                w.submissionstart AS allowsubmissionsfromdate,
                                w.submissionend AS duedate, NULL AS cutoffdate,
                                w.assessmentend AS gradingduedate, NULL AS hasextension,
                                gi.gradetype, gi.grademin, gi.grademax, NULL AS scale,
                                gg.finalgrade, gg.information AS gradeinformation, gg.feedback,
                                NULL AS feedbackfiles, NULL AS hasturnitin, NULL AS status,
                                ws.title AS submissions, NULL as quizfeedback,
                                c.startdate, c.enddate, $convertedgradeselect";
            $workshopjoins = "LEFT JOIN {workshop_submissions} ws
                            ON (ws.workshopid = w.id AND ws.authorid = ?)
                            LEFT JOIN {modules} m ON (m.name = 'workshop')
                            JOIN {course_modules} cm ON (cm.course = w.course
                                AND cm.instance = w.id AND cm.module = m.id
                                AND cm.deletioninprogress = 0)
                            LEFT JOIN {grade_items} gi ON (gi.iteminstance = cm.instance
                                AND gi.courseid = w.course AND gi.itemtype = 'mod'
                                AND gi.itemmodule = 'workshop' AND gi.itemnumber = 0)
                            LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = ?)
                            LEFT JOIN {grade_categories} gc ON gc.id = gi.categoryid
                            LEFT JOIN {course} c ON c.id = w.course
                            LEFT JOIN {course_sections} cs ON (cs.course = c.id AND cs.id = cm.section)
                            $convertedgradejoin";
            $workshopenddate = ($activetab === TAB_CURRENT) ?
                                "AND (c.enddate + 86400 * 30 > ?
                                OR w.submissionend + 86400 * 30 > ?)" :
                                "AND c.enddate  + 86400 * 30 <= ?
                                AND w.submissionend  + 86400 * 30 <= ?";
            $workshopwhere = "w.course IN ($courseids) $workshopenddate $categorylimit";
            $workshopsql = "SELECT $workshopfields FROM {workshop} w $workshopjoins WHERE $workshopwhere";
            $workshopparams = array($userid, $userid, $userid, $enddate, $enddate);
            $orderclause = " ORDER BY $sortby $sortorder";
            $unionsql = "($assignsql) UNION ($forumsql) UNION ($quizsql) UNION ($workshopsql)";
            $unionparams = array_merge($assignparams, $forumparams, $quizparams, $workshopparams);
            if (!$issubcategory && count($level2ids) > 0) {
                $level2idtext = implode(', ', $level2ids);
                $topicnames = self::get_topicname($level2idtext);
                $coursetitle = self::generate_topicname_case_statement($topicnames);
                $subcategoryfields = "gc.id, gc.courseid, $coursetitle, gi.itemmodule AS modname,
                                        gc.fullname AS activityname, gp.fullname AS gradecategoryname, gp.aggregation,
                                        gi.aggregationcoef, gi.aggregationcoef2, NULL AS allowsubmissionsfromdate,
                                        0 AS duedate, 0 AS cutoffdate, 0 AS gradingduedate, NULL AS hasextension, gi.gradetype,
                                        CASE WHEN gi.grademin IS NOT NULL THEN gi.grademin ELSE gip.grademin END AS grademin,
                                        CASE WHEN gi.grademax IS NOT NULL THEN gi.grademax ELSE gip.grademax END AS grademax,
                                        sp.scale AS scale,
                                        CASE WHEN gg.finalgrade IS NOT NULL THEN gg.finalgrade
                                            WHEN gg.rawgrade IS NOT NULL THEN gg.rawgrade
                                            WHEN ggp.finalgrade IS NOT NULL THEN ggp.finalgrade ELSE ggp.rawgrade END AS finalgrade,
                                        gg.information AS gradeinformation, gg.feedback, NULL as feedbackfiles, NULL as hasturnitin,
                                        'category' AS status, NULL AS submissions, NULL AS quizfeedback,
                                        c.startdate, c.enddate, NULL AS convertedgradeid,
                                        CASE WHEN ggp.rawgrade IS NOT NULL THEN ggp.rawgrade ELSE ggp.finalgrade
                                        END AS provisionalgrade,
                                        gip.idnumber, gip.outcomeid";
                $subcategoryjoins = "INNER JOIN {grade_items} gi ON (itemtype = 'category' AND iteminstance = gc.id)
                                     INNER JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = ?)
                                     LEFT JOIN {course} c ON c.id = gc.courseid
                                     LEFT JOIN {grade_categories} gp ON (gp.id = gc.parent)
                                     LEFT JOIN {grade_items} gip ON (gip.itemname = 'Subcategory Grade' AND gip.iteminfo = gc.id)
                                     LEFT JOIN {grade_grades} ggp ON (ggp.itemid = gip.id AND ggp.userid = ?)
                                     LEFT JOIN {scale} sp ON (sp.id = CASE WHEN gip.idnumber IS NOT NULL
                                     AND NOT gip.idnumber = '' THEN gip.idnumber ELSE gip.outcomeid END)";
                $subcategorywhere = "gc.parent IN ($level2idtext) AND gc.fullname != 'DO NOT USE' $subcategoryenddate";
                $subcategoryenddate = ($activetab === TAB_CURRENT) ?
                                    "AND c.enddate + 86400 * 30 > ?" :
                                    "AND c.enddate  + 86400 * 30 <= ?";
                $subcategorysql = "SELECT $subcategoryfields FROM {grade_categories} gc $subcategoryjoins WHERE $subcategorywhere";
                array_push($unionparams, $userid, $userid, $enddate);
                $unionsql .= " UNION ($subcategorysql)";
            }
            $unionsql .= $orderclause;
            $records = $DB->get_records_sql($unionsql, $unionparams);
        } else {
            $records = null;
        }

        $items = ($records) ? self::sanitize_records($records, $subcategoryparent) : array();
        return $items;
    }

    /**
     * Returns Topic name for a category
     *
     * @param int $categoryid
     * @param string $coursetitle
     * @return string $coursetitle || $topicname
     */
    public static function get_topicname_category($categoryid, $coursetitle) {
        global $DB;

        $gradeitem = $DB->get_record('grade_items', array('categoryid' => $categoryid));
        if ($gradeitem) {
            $params = array($gradeitem->courseid, $gradeitem->iteminstance, $gradeitem->courseid, $gradeitem->itemmodule);
            $sql = "SELECT
            CASE
                WHEN cs.name IS NOT NULL THEN cs.name
                WHEN cs.section != 0 THEN CONCAT('Topic ', cs.section)
                ELSE c.fullname
            END AS coursetitle
            FROM mdl_modules AS m
                LEFT JOIN mdl_course_modules AS cm ON (cm.course = ?
                        AND cm.instance = ?
                        AND cm.module = m.id
                        AND cm.deletioninprogress = 0)
                LEFT JOIN mdl_course AS c ON c.id = ?
                LEFT JOIN mdl_course_sections AS cs ON (cs.course = c.id AND cs.id = cm.section)
            WHERE m.name = ?";
            $coursesection = $DB->get_record_sql($sql, $params);
            return $coursesection ? $coursesection->coursetitle : $coursetitle;
        } else {
            return $coursetitle;
        }
    }

    /**
     * Returns Topic names and Subcategories
     *
     * @param string $level2idtext
     * @return array $topicnames
     */
    public static function get_topicname($level2idtext) {
        global $DB;

        // Get subcategories.
        $sql = "SELECT gc.id, c.fullname
                FROM {grade_categories} as gc
                LEFT JOIN {course} as c ON (gc.courseid = c.id)
                WHERE
                gc.parent IN ($level2idtext)
                AND gc.fullname != 'DO NOT USE'";
        $subcategories = $DB->get_records_sql($sql);

        if ($subcategories) {
            $topicnames = array();
            foreach ($subcategories as $subcategory) {
                $name = self::get_topicname_category($subcategory->id, $subcategory->fullname);
                $topicelement = new stdClass;
                $topicelement->id = $subcategory->id;
                $topicelement->text = addslashes($name);
                array_push($topicnames, $topicelement);
            }
            return $topicnames;
        } else {
            return array();
        }
    }

    /**
     * Returns Generated Case Statement
     *
     * @param array $topicnames
     * @return string $casestatement
     */
    public static function generate_topicname_case_statement($topicnames) {
        if (count($topicnames) > 0) {
            $casestatement = "CASE ";
            foreach ($topicnames as $topicelement) {
                $casestatement .= "WHEN gc.id = $topicelement->id THEN '$topicelement->text' ";
            }
            $casestatement .= "ELSE c.fullname END AS coursetitle";
            return $casestatement;
        } else {
            return "c.fullname AS coursetitle";
        }
    }

    /**
     * Returns sanitized data based from query results
     *
     * @param array $records
     * @param grade_category $subcategoryparent
     * @return array $items
     */
    public static function sanitize_records($records, $subcategoryparent) {
        $items = array();

        if ($records) {
            $recordsarray = (array) $records;
            foreach ($recordsarray as $record) {
                $modinfo = get_fast_modinfo($record->courseid);
                $cm = ($record->status != 'category') ? $modinfo->get_cm($record->id) : null;
                // Check if course module is visible to the user.
                $iscmvisible = ($record->status != 'category') ? $cm->uservisible : true;

                if ($iscmvisible) {
                        $item = new stdClass;
                        $item->id = $record->id;
                        $item->coursetitle = $record->coursetitle;
                        $item->courseurl = self::return_courseurl($record->courseid);
                        $item->assessmenturl = self::return_assessmenturl($record->id, $record->modname);
                        $item->assessmentname = $record->activityname;
                        $item->assessmenttype = self::return_assessmenttype($record->gradecategoryname);
                        $item->weight = self::return_weight($item->assessmenttype, $record->aggregation,
                                                            $record->aggregationcoef, $record->aggregationcoef2,
                                                            isset($subcategoryparent->fullname) ? $subcategoryparent->fullname
                                                                                                : null);
                        $item->duedate = $record->duedate;
                        $item->formattedduedate = self::return_formattedduedate($record->duedate);
                        $item->hasextension = (!empty($record->hasextension)) ? true : false;
                        $item->startdate = $record->startdate;
                        $item->enddate = $record->enddate;
                        $item->formattedstartdate = date(get_string('date_m_y', 'block_gu_spdetails'),
                                                        $record->startdate);
                        $item->formattedenddate = date(get_string('date_m_y', 'block_gu_spdetails'),
                                                    $record->enddate);
                        $item->grading = self::return_grading($record->finalgrade, $record->gradetype,
                                                            $record->grademin, $record->grademax,
                                                            $record->gradeinformation,
                                                            $record->gradingduedate,
                                                            $record->duedate, $record->cutoffdate,
                                                            $record->scale, $record->feedback, $record->convertedgradeid,
                                                            $record->provisionalgrade, $record->status,
                                                            $record->idnumber, $record->outcomeid);
                        $item->feedback = self::return_feedback($record->id, $record->modname,
                                                                $item->grading->hasgrade,
                                                                $record->feedback, $record->feedbackfiles,
                                                                $record->hasturnitin, $record->gradingduedate,
                                                                $record->duedate, $record->cutoffdate,
                                                                $record->quizfeedback, $record->status);
                        $item->status = self::return_status($record->modname, $item->grading->hasgrade,
                                                            $record->status, $record->submissions,
                                                            $record->allowsubmissionsfromdate,
                                                            $record->duedate, $record->cutoffdate,
                                                            $record->gradingduedate, $item->hasextension,
                                                            $record->feedback);
                        array_push($items, $item);
                }
            }
        }

        return $items;
    }

    /**
     * Checks if user has capability of a student
     *
     * @param string $courseid
     * @param string $userid
     * @return boolean has_capability
     */
    public static function return_isstudent($courseid) {
        $context = context_course::instance($courseid);
        return has_capability('moodle/grade:view', $context, null, false);
    }

    /**
     * Returns the course URL
     *
     * @param int $courseid
     * @return string $courseurl
     */
    public static function return_courseurl($courseid) {
        $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
        return $courseurl;
    }

    /**
     * Returns the assessment URL
     *
     * @param int $id
     * @param string $modname
     * @return string $assessmenturl
     */
    public static function return_assessmenturl($id, $modname) {
        $assessmenturl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));
        return $assessmenturl;
    }

    /**
     * Returns the 'assessment type'
     *
     * @param string $gradecategoryname
     * @return string 'Formative', 'Summative', or '—'
     */
    public static function return_assessmenttype($gradecategoryname) {
        $type = strtolower($gradecategoryname);

        if (strpos($type, 'summative') !== false) {
            $assessmenttype = get_string('summative', 'block_gu_spdetails');
        } else if (strpos($type, 'formative') !== false) {
            $assessmenttype = get_string('formative', 'block_gu_spdetails');
        } else {
            $assessmenttype = get_string('emptyvalue', 'block_gu_spdetails');
        }

        return $assessmenttype;
    }

    /**
     * Returns the 'weight' in percentage
     *
     * @param string $assessmenttype
     * @param string $aggregation
     * @param string $aggregationcoef
     * @param string $aggregationcoef2
     * @param string $subcategoryparentfullname
     * @return string Weight (in percentage), or '—' if empty
     */
    public static function return_weight($assessmenttype, $aggregation, $aggregationcoef,
                                         $aggregationcoef2, $subcategoryparentfullname) {
        $summative = get_string('summative', 'block_gu_spdetails');
        $weight = 0;

        if ($assessmenttype === $summative || $subcategoryparentfullname === $summative) {
            // If $aggregation == '10', meaning 'Weighted mean of grades' is used.
            $weight = ($aggregation == '10') ?
                        (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100) :
                        $aggregationcoef2 * 100;
        }

        $finalweight = ($weight > 0) ? round($weight, 2).'%' : get_string('emptyvalue', 'block_gu_spdetails');

        return $finalweight;
    }

    /**
     * Returns formatted due date (month d)
     *
     * @param int $duedate
     * @return string $formattedduedate
     */
    public static function return_formattedduedate($duedate) {
        $formattedduedate = ($duedate > 0) ?
                            userdate($duedate, get_string('date_month_d', 'block_gu_spdetails')) :
                            get_string('emptyvalue', 'block_gu_spdetails');
        return $formattedduedate;
    }

    /**
     * Returns the grading object of an assessment
     *
     * @param float $finalgrade
     * @param string $gradetype
     * @param float $grademin
     * @param float $grademax
     * @param string $gradeinformation
     * @param int $gradingduedate
     * @param string $scale
     * @param string $feedback
     * @return stdClass Object containing grade text (could be actual grade or grading due date),
     *         hasgrade, isprovisional
     */
    public static function return_grading($finalgrade, $gradetype, $grademin, $grademax,
                                        $gradeinformation, $gradingduedate, $duedate,
                                        $cutoffdate, $scale, $feedback, $convertedgradeid,
                                        $provisionalgrade, $status, $idnumber, $outcomeid) {
        $grading = new stdClass;
        $grading->gradetext = null;
        $grading->hasgrade = false;
        $grading->isprovisional = false;
        $provisionalgraderound = round($provisionalgrade);
        $scheduleab = empty($outcomeid) ? $idnumber : $outcomeid;
        $isoutcomeid = !empty($outcomeid);

        if (!empty($scheduleab) && $scheduleab > 2) {
            $gradetype = '2';
        } else if (empty($scale)) {
            $gradetype = '1';
        }
        if (isset($finalgrade)) {
            $intgrade = !empty($convertedgradeid) ? (int)$finalgrade
                                                  : (is_null($provisionalgrade) ? (int)$finalgrade : (int)$provisionalgrade);
            $grading->hasgrade = true;
            $grading->isprovisional = ($gradeinformation || $status === 'category') ? false : true;
            $grademax = (int)$grademax;
            $convertedgrade = !is_null($convertedgradeid) || ($status === 'category' && !is_null($provisionalgrade)) ?
                                self::return_22grademaxpoint((int)$provisionalgraderound - 1, $scheduleab) : "";
            $onlyconverted = $isoutcomeid && $convertedgrade !== "";
            switch ($gradetype) {
                // Gradetype = value.
                case '1':
                    $grading->gradetext = ($grademax == 22 && $grademin == 0) ?
                                        self::return_22grademaxpoint($intgrade, $scheduleab) :
                                        self::return_gradetext((int)$finalgrade, $grademax, $convertedgrade, $onlyconverted);
                    break;
                // Gradetype = scale.
                case '2':
                    $scalelist = make_menu_from_list($scale);
                    foreach ($scalelist as $key => $value) {
                        if ($key == $intgrade) {
                            $scalegrade = $value;
                        }
                    }

                    if (strpos($scalegrade, ':')) {
                        $scalegradevalue = explode(':', $scalegrade);
                        $grading->gradetext  = $scalegradevalue[0];
                    } else {
                        $grading->gradetext  = $scalegrade;
                    }
                    break;
                // Gradetype = text.
                default:
                        $grading->gradetext = ($feedback) ? $feedback :
                                            get_string('emptyvalue', 'block_gu_spdetails');
                break;
            }
        } else {
            $duedate = get_string('due', 'block_gu_spdetails').userdate($gradingduedate,
                        get_string('date_month_d', 'block_gu_spdetails'));
            $na = get_string('notavailable', 'block_gu_spdetails');
            $overdue = get_string('overdue', 'block_gu_spdetails');
            $tbc = get_string('tobeconfirmed', 'block_gu_spdetails');

            if ($gradingduedate > 0) {
                if ($feedback === 'MV') {
                        $grading->gradetext = $duedate;
                }

                if ($gradingduedate > time()) {
                        $grading->gradetext = ($feedback === 'NS' &&
                                            $cutoffdate < time() &&
                                            $duedate < time()) ? $na : $duedate;
                } else {
                        $grading->gradetext = ($feedback === 'NS') ? $na : $overdue;
                }
            } else {
                $grading->gradetext = $tbc;
            }
        }

        return $grading;
    }

    /**
     * Returns formatted grade text of an assessment
     *
     * @param int $intgrade
     * @param int $grademax
     * @param string $convertedgrade
     * @param boolean $onlyconverted
     * @return string formatted grade text
     *
     */
    public static function return_gradetext($intgrade, $grademax, $convertedgrade, $onlyconverted) {
        return $onlyconverted ? $convertedgrade : "$intgrade / $grademax" . (!empty($convertedgrade) ? " - $convertedgrade" : "");
    }

    /**
     * Returns the feedback object of an assessment
     *
     * @param int $id
     * @param string $modname
     * @param boolean $hasgrade
     * @param string $feedback
     * @param int $feedbackfiles
     * @param int $hasturnitin
     * @return stdClass Object containing feedback text (could be feedback text for the link or feedback due date),
     *         hasfeedback, feedbackurl
     */
    public static function return_feedback($id, $modname, $hasgrade, $feedback, $feedbackfiles,
                                        $hasturnitin, $gradingduedate, $duedate, $cutoffdate,
                                        $quizfeedback, $status) {
        $fb = new stdClass;
        $fb->feedbacktext = null;
        $fb->hasfeedback = false;
        $fb->issubcategory = false;

        $duedate = get_string('due', 'block_gu_spdetails').userdate($gradingduedate,
                    get_string('date_month_d', 'block_gu_spdetails'));
        $na = get_string('notavailable', 'block_gu_spdetails');
        $overdue = get_string('overdue', 'block_gu_spdetails');
        $tbc = get_string('tobeconfirmed', 'block_gu_spdetails');
        $sic = get_string('see_individual_components', 'block_gu_spdetails');

        if ($status === 'category') {
            $fb->hasfeedback = true;
            $fb->feedbacktext = $sic;
            $fb->issubcategory = true;
        } else if ($hasgrade) {
            $readfeedback = get_string('readfeedback', 'block_gu_spdetails');
            $idintro = get_string('id_intro', 'block_gu_spdetails');
            $idfooter = get_string('id_pagefooter', 'block_gu_spdetails');
            $feedbackurl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));

            switch($modname) {
                case 'assign':
                    $fb->feedbackurl = ($hasturnitin > 0) ? $feedbackurl.$idintro :
                                    ((!empty($feedback) || $feedbackfiles > 0) ? $feedbackurl.$idfooter : null);
                    if (isset($fb->feedbackurl)) {
                        $fb->feedbacktext = $readfeedback;
                        $fb->hasfeedback = true;
                    } else {
                        $fb->feedbacktext = ($gradingduedate > 0) ?
                                            (($gradingduedate > time()) ? $duedate : $overdue) : $tbc;
                    }
                    break;
                case 'quiz':
                    if ($quizfeedback) {
                        $fb->feedbacktext = $readfeedback;
                        $fb->hasfeedback = true;
                        $idfeedback = get_string('id_feedback', 'block_gu_spdetails');
                        $fb->feedbackurl = $feedbackurl.$idfeedback;
                    } else {
                        $fb->feedbacktext = ($gradingduedate > 0) ?
                                            (($gradingduedate > time()) ? $duedate : $overdue) : $tbc;
                    }
                    break;
                case 'workshop':
                    $fb->hasfeedback = true;
                    $fb->feedbacktext = $readfeedback;
                    $workshopurl = new moodle_url('/mod/workshop/submission.php', array('cmid' => $id));
                    $fb->feedbackurl = $workshopurl.$idfooter;
                    break;
                // Forum.
                default:
                    $fb->hasfeedback = true;
                    $fb->feedbacktext = $readfeedback;
                    $fb->feedbackurl = $feedbackurl.$idfooter;
                    break;

            }
        } else {
            if ($gradingduedate > 0) {
                if ($feedback === 'MV') {
                        $fb->feedbacktext = $duedate;
                }

                if ($gradingduedate > time()) {
                        $fb->feedbacktext = ($feedback === 'NS' &&
                                            $cutoffdate < time() &&
                                            $duedate < time()) ? $na : $duedate;
                } else {
                        $fb->feedbacktext = ($feedback === 'NS') ? $na : $overdue;
                }
            } else {
                $fb->feedbacktext = $tbc;
            }
        }

        return $fb;
    }

    /**
     * Returns status object of an assessment
     *
     * @param string $modname
     * @param boolean $hasgrade
     * @param string $status
     * @param mixed $submissions
     * @param int $allowsubmissionsfromdate
     * @param int $duedate
     * @param int $cutoffdate
     * @param boolean $hasextension
     * @param string $feedback
     * @return stdClass Object containing status text, status class, hasstatusurl
     */
    public static function return_status($modname, $hasgrade, $status, $submissions,
                                        $allowsubmissionsfromdate, $duedate, $cutoffdate,
                                        $gradingduedate, $hasextension, $feedback) {
        $graded = get_string('status_graded', 'block_gu_spdetails');
        $notopen = get_string('status_notopen', 'block_gu_spdetails');
        $notsubmitted = get_string('status_notsubmitted', 'block_gu_spdetails');
        $overdue = get_string('status_overdue', 'block_gu_spdetails');
        $submit = get_string('status_submit', 'block_gu_spdetails');
        $submitted = get_string('status_submitted', 'block_gu_spdetails');
        $unavailable = get_string('status_unavailable', 'block_gu_spdetails');
        $individualcomponents = get_string('individual_components', 'block_gu_spdetails');

        $classgraded = get_string('class_graded', 'block_gu_spdetails');
        $classoverdue = get_string('class_overdue', 'block_gu_spdetails');
        $classsubmit = get_string('class_submit', 'block_gu_spdetails');
        $classsubmitted = get_string('class_submitted', 'block_gu_spdetails');

        $s = new stdClass;
        $s->statustext = $notopen;
        $s->class = null;
        $s->hasstatusurl = false;
        $s->issubcategory = false;

        if ($status === 'category') {
            $s->statustext = $individualcomponents;
            $s->class = $classgraded;
            $s->issubcategory = true;
        } else if ($hasgrade) {
            $s->statustext = $graded;
            $s->class = $classgraded;
        } else if ($feedback === 'NS' && $duedate < time() && $cutoffdate > time() && $gradingduedate > time()) {
            $s->statustext = $overdue;
            $s->class = $classoverdue;
            $s->hasstatusurl = true;
        } else if ($feedback === 'NS' && $duedate < time() && $cutoffdate < time() && $gradingduedate > time()) {
            $s->statustext = $unavailable;
        } else if ($feedback === 'NS' && $duedate < time() && $cutoffdate < time() && $gradingduedate < time()) {
            $s->statustext = $notsubmitted;
        } else {
            switch($modname) {
                case 'assign':
                    if ($status === $submitted) {
                        $s->statustext = $submitted;
                        $s->class = $classsubmitted;
                    } else {
                        if ($submissions > 0) {
                            $s->statustext = $unavailable;
                        } else {
                            if ($allowsubmissionsfromdate > time() || $duedate == 0) {
                                $s->statustext = $notopen;
                            } else {
                                if ($duedate < time()) {
                                    if ($cutoffdate == 0 || $cutoffdate > time()) {
                                        $s->statustext = $overdue;
                                        $s->class = $classoverdue;
                                        $s->hasstatusurl = true;
                                    } else {
                                        $s->statustext = $notsubmitted;
                                    }
                                } else {
                                    $s->hasstatusurl = true;
                                    $s->statustext = $submit;
                                    $s->class = $classsubmit;
                                }
                            }
                        }
                    }
                    break;
                case 'quiz':
                    if ($allowsubmissionsfromdate > time()) {
                        $s->statustext = $notopen;
                    } else {
                        if ($status === 'finished') {
                            $s->statustext = $submitted;
                            $s->class = $classsubmitted;
                        } else if ($duedate < time() && $duedate != 0) {
                            $s->statustext = $notsubmitted;
                        } else {
                            $s->hasstatusurl = true;
                            $s->statustext = $submit;
                            $s->class = $classsubmit;
                        }
                    }
                    break;
                case 'workshop':
                    if (!empty($submissions)) {
                        $s->statustext = $submitted;
                        $s->class = $classsubmitted;
                    } else {
                        if ($allowsubmissionsfromdate > time() || $duedate == 0) {
                            $s->statustext = $notopen;
                        } else {
                            if ($duedate < time()) {
                                $s->statustext = $notsubmitted;
                            } else {
                                $s->hasstatusurl = true;
                                $s->statustext = $submit;
                                $s->class = $classsubmit;
                            }
                        }
                    }
                    break;
                // Forum.
                default:
                    if ($duedate < time()) {
                        if ($status === 'submitted') {
                            $s->statustext = $submitted;
                            $s->class = $classsubmitted;
                        } else if ($cutoffdate == 0 || $cutoffdate > time()) {
                            $s->statustext = $overdue;
                            $s->class = $classoverdue;
                            $s->hasstatusurl = true;
                        } else {
                            $s->statustext = $notsubmitted;
                        }
                    } else {
                        $s->hasstatusurl = true;
                        $s->statustext = $submit;
                        $s->class = $classsubmit;
                    }
            }
        }
        return $s;
    }

    /**
     * Returns a corresponding value for grades with gradetype = "value" and grademax = "22"
     *
     * @param int $grade
     * @param int $idnumber = 1 - Schedule A, 2 - Schedule B
     * @return string 22-grade max point value
     */
    public static function return_22grademaxpoint($grade, $idnumber) {
        $values = array('H', 'G2', 'G1', 'F3', 'F2', 'F1', 'E3', 'E2', 'E1', 'D3', 'D2', 'D1',
                        'C3', 'C2', 'C1', 'B3', 'B2', 'B1', 'A5', 'A4', 'A3', 'A2', 'A1');
        $value = $values[$grade];
        if ($idnumber == 2) {
            $stringarray = str_split($value);
            if ($stringarray[0] != 'H') {
                $value = $stringarray[0] . '0';
            }
        }
        return $value;
    }
}
