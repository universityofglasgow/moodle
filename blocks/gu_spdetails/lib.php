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
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao, Jose Maria Abreu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
define('ASSESSMENTS_PER_PAGE', 12);
define('TAB_CURRENT', 'current');
define('TAB_PAST', 'past');
define('SORTBY_COURSE', 'course');
define('SORTBY_DATE', 'date');
define('SORTBY_STARTDATE', 'startdate');
define('SORTBY_ENDDATE', 'enddate');
define('SORTORDER_ASC', 'asc');
define('SORTORDER_DESC', 'desc');

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
     public static function retrieve_assessments($activetab, $page, $sortby, $sortorder) {
          global $DB, $USER, $OUTPUT;
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

          if($activetab === TAB_CURRENT) {
               $items = self::retrieve_current_gradable_activities($userid, $sortby, $sortorder);
          }else{
               $items = self::retrieve_past_gradable_activities($userid, $sortby, $sortorder);
          }

          $totalassessments = 0;
          $html = null;

          if($items) {
               $totalassessments = count($items);
               $paginatedassessments = array_splice($items, $offset, $limit);

               $html .= html_writer::start_tag('div', array('class' => 'assessments-details-sort-container'));
               if($activetab === TAB_CURRENT) {
                    $selectsortby = get_string('select_currentsortby', 'block_gu_spdetails');
                    $html .= html_writer::tag('label', get_string('label_sortby', 'block_gu_spdetails'),
                                              array('class' => 'assessments-details-sort-label h5',
                                                    'for' => 'menu'.$selectsortby));
                    $html .= html_writer::select($currentsortby, $selectsortby, $sortby, false);
               }else{
                    $selectsortby = get_string('select_pastsortby', 'block_gu_spdetails');
                    $html .= html_writer::tag('label', get_string('label_sortby', 'block_gu_spdetails'),
                                              array('class' => 'assessments-details-sort-label h5',
                                                    'for' => 'menu'.$selectsortby));
                    $html .= html_writer::select($pastsortby, $selectsortby, $sortby, false);
               }
               $html .= html_writer::end_tag('div');
               $html .= html_writer::start_tag('table', array('class' => 'table assessments-details-table'));
               $html .= html_writer::start_tag('thead');
               $html .= html_writer::start_tag('tr');
               $html .= html_writer::tag('th', get_string('header_course', 'block_gu_spdetails'),
                                         array('id' => 'sortby_course', 'class' => 'td20 th-sortable',
                                               'data-value' => ''));
               $html .= html_writer::tag('th', get_string('header_assessment', 'block_gu_spdetails'),
                                         array('class' => 'td20'));
               $html .= html_writer::tag('th', get_string('header_type', 'block_gu_spdetails'),
                                         array('class' => 'td10'));
               $html .= html_writer::tag('th', get_string('header_weight', 'block_gu_spdetails'),
                                         array('class' => 'td05'));
               if($activetab === TAB_CURRENT) {
                    $html .= html_writer::tag('th', get_string('header_duedate', 'block_gu_spdetails'),
                                              array('id' => 'sortby_date', 'class' => 'td10 th-sortable',
                                                    'data-value' => ''));
                    $html .= html_writer::tag('th', get_string('header_status', 'block_gu_spdetails'),
                                              array('class' => 'td15'));
               }else{
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
                                         array('class' => 'td10'));
               $html .= html_writer::tag('th', get_string('header_feedback', 'block_gu_spdetails'),
                                         array('class' => 'td10'));
               $html .= html_writer::end_tag('tr');
               $html .= html_writer::end_tag('thead');
               $html .= html_writer::start_tag('tbody');

               foreach($paginatedassessments as $assessment) {
                    $html .= html_writer::start_tag('tr');
                    $html .= html_writer::start_tag('td', array('class' => 'td20'));
                    $html .= html_writer::tag('a', $assessment->coursetitle, array('href' => $assessment->courseurl));
                    $html .= html_writer::end_tag('td');
                    $html .= html_writer::start_tag('td', array('class' => 'td20'));
                    $html .= html_writer::tag('a', $assessment->assessmentname, array('href' => $assessment->assessmenturl));
                    $html .= html_writer::end_tag('td');
                    $html .= html_writer::tag('td', $assessment->assessmenttype, array('class' => 'td10'));
                    $html .= html_writer::tag('td', $assessment->weight, array('class' => 'td05'));
                    if($activetab === TAB_CURRENT) {
                         $html .= html_writer::start_tag('td', array('class' => 'td10'));
                         $html .= $assessment->formattedduedate;
                         if($assessment->hasextension) {
                              $html .= html_writer::start_span('extended').'*';
                              $html .= html_writer::start_span('extended-tooltip').get_string('extended', 'block_gu_spdetails');
                              $html .= html_writer::end_span();
                              $html .= html_writer::end_span();
                         }
                         $html .= html_writer::end_tag('td');
                         $html .= html_writer::start_tag('td', array('class' => 'td15'));
                         if($assessment->status->hasstatusurl) {
                              $html .= html_writer::start_tag('a', array('href' => $assessment->assessmenturl,
                                                                         'class' => get_string('class_link', 'block_gu_spdetails')));
                              $html .= html_writer::start_span(get_string('class_default', 'block_gu_spdetails').$assessment->status->class).
                                                               $assessment->status->statustext;
                              $html .= html_writer::end_span();
                              $html .= html_writer::end_tag('a');
                         }else{
                              $html .= html_writer::start_span(get_string('class_default', 'block_gu_spdetails').$assessment->status->class).
                                                               $assessment->status->statustext;
                              $html .= html_writer::end_span();
                         }
                         $html .= html_writer::end_tag('td');
                    }else{
                         $html .= html_writer::tag('td', $assessment->formattedstartdate, array('class' => 'td05'));
                         $html .= html_writer::tag('td', $assessment->formattedenddate, array('class' => 'td05'));
                         $html .= html_writer::start_tag('td', array('class' => 'td15'));
                         $html .= html_writer::tag('a', get_string('viewsubmission', 'block_gu_spdetails'),
                                                   array('href' => $assessment->assessmenturl));
                         $html .= html_writer::end_tag('td');
                    }
                    $html .= html_writer::start_tag('td', array('class' => 'td10'));
                    if($assessment->grading->hasgrade) {
                         $html .= html_writer::start_span('graded').$assessment->grading->gradetext;
                         $html .= html_writer::end_span();
                         if($assessment->grading->isprovisional) {
                              $html .= get_string('provisional', 'block_gu_spdetails');
                         }
                    }else{
                         $html .= $assessment->grading->gradetext;
                    }
                    $html .= html_writer::end_tag('td');
                    // $html .= html_writer::tag('td', $assessment->feedback->feedbacktext);
                    $html .= html_writer::start_tag('td', array('class' => 'td10'));
                    if($assessment->feedback->hasfeedback) {
                         $html .= html_writer::tag('a', $assessment->feedback->feedbacktext,
                                                   array('href' => $assessment->feedback->feedbackurl));
                    }else{
                         $html .= $assessment->feedback->feedbacktext;
                    }
                    $html .= html_writer::end_tag('td');
                    $html .= html_writer::end_tag('tr');
               }

               $html .= html_writer::end_tag('tbody');
               $html .= html_writer::end_tag('table');
               $html .= $OUTPUT->paging_bar($totalassessments, $page, $limit, $url);
          }
     
          return $html;
     }

     public static function retrieve_current_gradable_activities($userid, $sortby, $sortorder) {
          global $DB;
          $onemonth = time() + (86400 * 30);

          if($sortby === SORTBY_COURSE) {
               $sortcolumn = 'coursetitle';
          }else{
               $sortcolumn = 'duedate';
          }

          $sql = "SELECT cm.id, c.id AS courseid, c.startdate, c.enddate,
                         cs.section, cs.name AS sectionname,
                         c.fullname AS coursefullname, c.shortname AS courseshortname,
                         cm.instance AS modinstance, m.name AS modname,
                         CASE
                         WHEN cs.name IS NOT NULL THEN cs.name
                         WHEN cs.section > 0 THEN CONCAT('Topic ', cs.section)
                         ELSE c.fullname END AS coursetitle,
                         gi.id AS gradeitemid, gi.gradetype, gi.scaleid,
                         gi.aggregationcoef, gi.aggregationcoef2,
                         gi.grademax, gi.grademin, gi.gradepass,
                         gg.finalgrade, gg.feedback, gg.feedbackformat,
                         gg.information AS gradeinformation,
                         gc.fullname AS gradecategoryname, gc.aggregation, 
                         s.scale, ua.*
                    FROM {course} c
                    JOIN {course_modules} cm
                         ON (c.id = cm.course AND cm.deletioninprogress = 0)
                    JOIN {course_sections} cs
                         ON (cs.course = c.id AND cs.id = cm.section)
                    JOIN {modules} m ON m.id = cm.module
                    JOIN (SELECT gi1.id, gi1.categoryid, gi1.gradetype,
                          gi1.grademax, gi1.grademin, gi1.gradepass, gi1.scaleid,
                          gi1.aggregationcoef, gi1.aggregationcoef2,
                          gi1.iteminstance, gi1.courseid, gi1.itemmodule
                          FROM {grade_items} gi1
                          LEFT JOIN {grade_items} gi2
                          ON (gi2.iteminstance = gi1.iteminstance
                          AND gi2.itemmodule = gi1.itemmodule
                          AND gi2.itemnumber <> gi1.itemnumber)
                          WHERE gi1.itemtype = 'mod' AND gi1.gradetype != 0
                          AND (gi1.itemnumber = 0 OR gi2.itemnumber IS NULL)) gi
                         ON (gi.iteminstance = cm.instance AND gi.courseid = c.id
                             AND gi.itemmodule = m.`name`)
                    JOIN {customfield_field} cff
                         ON cff.shortname = 'show_on_studentdashboard'
                    JOIN {customfield_data} cfd
                         ON (cfd.fieldid = cff.id AND cfd.instanceid = c.id)
                    LEFT JOIN {scale} s ON s.id = gi.scaleid
                    JOIN (SELECT DISTINCT e.courseid
                          FROM {enrol} e
                          JOIN {user_enrolments} ue
                          ON (ue.enrolid = e.id AND ue.userid = ?)) en
                         ON (en.courseid = c.id)
                    LEFT JOIN {grade_grades} gg
                         ON (gg.itemid = gi.id AND gg.userid = ?)
                    LEFT JOIN {grade_categories} gc ON gc.id = gi.categoryid
                    LEFT JOIN ((SELECT 'assign' AS modtype,
                               a.id AS activityid, a.name AS activityname,
                               CASE
                               WHEN auf.extensionduedate IS NOT NULL
                               AND auf.extensionduedate > 0
                               THEN auf.extensionduedate
                               WHEN ao.duedate IS NOT NULL THEN ao.duedate
                               ELSE a.duedate END AS duedate,
                               CASE
                               WHEN ao.allowsubmissionsfromdate IS NOT NULL
                               THEN ao.allowsubmissionsfromdate
                               ELSE a.allowsubmissionsfromdate
                               END AS allowsubmissionsfromdate,
                               CASE
                               WHEN ao.cutoffdate IS NOT NULL
                               THEN ao.cutoffdate
                               ELSE a.cutoffdate
                               END AS cutoffdate, a.gradingduedate,
                               CASE
                               WHEN auf.extensionduedate IS NOT NULL
                               AND auf.extensionduedate > 0
                               THEN 1
                               ELSE 0
                               END AS hasextension, a.nosubmissions,
                               ag.grade, `as`.`status`, aff.numfiles,
                               ptcfg.`value`,
                               NULL AS `feedbacktext`,
                               NULL AS `gradinggrade`,
                               NULL AS `assessmentstart`,
                               NULL AS `workshopsubmission`,
                               NULL AS `submissionsgrade`
                               FROM {assign} AS a
                               LEFT JOIN (SELECT assignid, duedate,
                                          allowsubmissionsfromdate, cutoffdate
                                          FROM {assign_overrides}
                                          WHERE userid = ?) AS ao
                                   ON (ao.assignid = a.id)
                               LEFT JOIN (SELECT assignment, extensionduedate
                               FROM {assign_user_flags} WHERE userid = ?) auf
                                   ON auf.assignment = a.id
                               LEFT JOIN {assign_grades} ag
                                   ON (ag.assignment = a.id AND ag.userid = ?)
                               LEFT JOIN {assign_submission} `as`
                                   ON (`as`.assignment = a.id AND `as`.userid = ?)
                               LEFT JOIN {assignfeedback_file} aff
                                   ON (aff.assignment = a.id)
                               LEFT JOIN {plagiarism_turnitin_config} ptcfg
                                   ON (ptcfg.name = 'use_turnitin' AND ptcfg.value = 1))
                    UNION (SELECT 'quiz' AS modtype, q.id AS activityid,
                           q.name AS activityname,
                           CASE
                           WHEN qo.timeclose IS NOT NULL
                           THEN qo.timeclose
                           ELSE q.timeclose END AS duedate,
                           q.timeopen AS allowsubmissionsfromdate,
                           NULL AS cutoffdate,
                           CASE
                           WHEN qo.timeclose IS NOT NULL
                           THEN qo.timeclose
                           ELSE q.timeclose END AS gradingduedate,
                           NULL AS hasextension, NULL as nosubmissions,
                           qg.grade, NULL AS `status`,
                           NULL AS numfiles, NULL AS `value`,
                           qf.feedbacktext,
                           NULL AS `gradinggrade`, NULL AS `assessmentstart`,
                           NULL AS `workshopsubmission`, NULL AS `submissionsgrade`
                           FROM {quiz} q
                           LEFT JOIN (SELECT * FROM {quiz_overrides}
                                      WHERE userid = ?) qo
                              ON qo.quiz = q.id
                           LEFT JOIN {quiz_grades} AS qg
                              ON (qg.quiz = q.id AND qg.userid = ?)
                           LEFT JOIN {quiz_feedback} AS qf
                              ON (qf.quizid = q.id AND qg.grade IS NOT NULL
                                  AND (qg.grade > qf.mingrade
                                  OR (qg.grade = 0 AND qf.mingrade = 0))
                                  AND qg.grade <= qf.maxgrade))
                    UNION (SELECT 'workshop' AS modtype, w.id AS activityid,
                           w.`name` AS activityname, w.submissionend AS duedate,
                           w.submissionstart AS allowsubmissionsfromdate,
                           NULL AS cutoffdate,
                           w.assessmentend AS gradingduedate,
                           NULL AS hasextension,  NULL as nosubmissions,
                           w.grade, NULL AS `status`,
                           NULL AS numfiles, NULL AS `value`,
                           NULL AS feedbacktext, w.gradinggrade,
                           w.assessmentstart, ws.title as `workshopsubmission`,
                           ws.grade AS `submissionsgrade`
                           FROM {workshop} w
                           LEFT JOIN {workshop_submissions} ws
                              ON (ws.workshopid = w.id AND ws.authorid = ?))
                    UNION (SELECT 'forum' AS modtype, id as activityid,
                           `name` as activityname, duedate,
                           NULL AS `allowsubmissionsfromdate`, cutoffdate,
                                   cutoffdate AS gradingduedate,
                           NULL AS hasextension,  NULL as nosubmissions,
                           grade_forum AS grade, assessed AS `status`,
                           NULL AS numfiles, NULL AS `value`, NULL AS feedbacktext,
                           NULL AS `gradinggrade`, NULL AS `assessmentstart`,
                           NULL AS `workshopsubmission`, NULL AS `submissionsgrade`
                           FROM {forum})) ua
                              ON (ua.modtype = m.`name` AND ua.activityid = cm.instance)
                    WHERE cfd.value > 0 AND m.`name` IN ('assign' , 'quiz', 'forum', 'workshop')
                    AND c.enddate > ? ORDER BY ".$sortcolumn." ".$sortorder;
          $params = array($userid, $userid, $userid, $userid, $userid,
                         $userid, $userid, $userid, $userid, $onemonth);
          $records = $DB->get_records_sql($sql, $params);
          $items = ($records) ? self::sanitize_records($records) : array();
          return $items;
     }

     public static function retrieve_past_gradable_activities($userid, $sortby, $sortorder) {
          global $DB;
          $onemonth = time() + (86400 * 30);

          if($sortby === SORTBY_COURSE) {
               $sortcolumn = 'coursetitle';
          }else if($sortby === SORTBY_STARTDATE){
               $sortcolumn = 'c.startdate';
          }else{
               $sortcolumn = 'c.enddate';
          }

          $sql = "SELECT cm.id, c.id AS courseid, c.startdate, c.enddate,
                         cs.section, cs.name AS sectionname,
                         c.fullname AS coursefullname, c.shortname AS courseshortname,
                         cm.instance AS modinstance, m.name AS modname,
                         CASE
                         WHEN cs.name IS NOT NULL THEN cs.name
                         WHEN cs.section > 0 THEN CONCAT('Topic ', cs.section)
                         ELSE c.fullname END AS coursetitle,
                         gi.id AS gradeitemid, gi.gradetype, gi.scaleid,
                         gi.aggregationcoef, gi.aggregationcoef2,
                         gi.grademax, gi.grademin, gi.gradepass,
                         gg.finalgrade, gg.feedback, gg.feedbackformat,
                         gg.information AS gradeinformation,
                         gc.fullname AS gradecategoryname, gc.aggregation, 
                         s.scale, ua.*
                    FROM {course} c
                    JOIN {course_modules} cm
                         ON (c.id = cm.course AND cm.deletioninprogress = 0)
                    JOIN {course_sections} cs
                         ON (cs.course = c.id AND cs.id = cm.section)
                    JOIN {modules} m ON m.id = cm.module
                    JOIN (SELECT gi1.id, gi1.categoryid, gi1.gradetype,
                          gi1.grademax, gi1.grademin, gi1.gradepass, gi1.scaleid,
                          gi1.aggregationcoef, gi1.aggregationcoef2,
                          gi1.iteminstance, gi1.courseid, gi1.itemmodule
                          FROM {grade_items} gi1
                          LEFT JOIN {grade_items} gi2
                          ON (gi2.iteminstance = gi1.iteminstance
                          AND gi2.itemmodule = gi1.itemmodule
                          AND gi2.itemnumber <> gi1.itemnumber)
                          WHERE gi1.itemtype = 'mod' AND gi1.gradetype != 0
                          AND (gi1.itemnumber = 0 OR gi2.itemnumber IS NULL)) gi
                         ON (gi.iteminstance = cm.instance AND gi.courseid = c.id
                             AND gi.itemmodule = m.`name`)
                    JOIN {customfield_field} cff
                         ON cff.shortname = 'show_on_studentdashboard'
                    JOIN {customfield_data} cfd
                         ON (cfd.fieldid = cff.id AND cfd.instanceid = c.id)
                    LEFT JOIN {scale} s ON s.id = gi.scaleid
                    JOIN (SELECT DISTINCT e.courseid
                          FROM {enrol} e
                          JOIN {user_enrolments} ue
                          ON (ue.enrolid = e.id AND ue.userid = ?)) en
                         ON (en.courseid = c.id)
                    LEFT JOIN {grade_grades} gg
                         ON (gg.itemid = gi.id AND gg.userid = ?)
                    LEFT JOIN {grade_categories} gc ON gc.id = gi.categoryid
                    LEFT JOIN ((SELECT 'assign' AS modtype,
                               a.id AS activityid, a.name AS activityname,
                               CASE
                               WHEN auf.extensionduedate IS NOT NULL
                               AND auf.extensionduedate > 0
                               THEN auf.extensionduedate
                               WHEN ao.duedate IS NOT NULL THEN ao.duedate
                               ELSE a.duedate END AS duedate,
                               CASE
                               WHEN ao.allowsubmissionsfromdate IS NOT NULL
                               THEN ao.allowsubmissionsfromdate
                               ELSE a.allowsubmissionsfromdate
                               END AS allowsubmissionsfromdate,
                               CASE
                               WHEN ao.cutoffdate IS NOT NULL
                               THEN ao.cutoffdate
                               ELSE a.cutoffdate
                               END AS cutoffdate, a.gradingduedate,
                               CASE
                               WHEN auf.extensionduedate IS NOT NULL
                               AND auf.extensionduedate > 0
                               THEN 1
                               ELSE 0
                               END AS hasextension, a.nosubmissions,
                               ag.grade, `as`.`status`,
                               aff.numfiles, ptcfg.`value`,
                               NULL AS `feedbacktext`,
                               NULL AS `gradinggrade`,
                               NULL AS `assessmentstart`,
                               NULL AS `workshopsubmission`,
                               NULL AS `submissionsgrade`
                               FROM {assign} AS a
                               LEFT JOIN (SELECT assignid, duedate,
                                          allowsubmissionsfromdate, cutoffdate
                                          FROM {assign_overrides}
                                          WHERE userid = ?) AS ao
                                   ON (ao.assignid = a.id)
                               LEFT JOIN (SELECT assignment, extensionduedate
                               FROM {assign_user_flags} WHERE userid = ?) auf
                                   ON auf.assignment = a.id
                               LEFT JOIN {assign_grades} ag
                                   ON (ag.assignment = a.id AND ag.userid = ?)
                               LEFT JOIN {assign_submission} `as`
                                   ON (`as`.assignment = a.id AND `as`.userid = ?)
                               LEFT JOIN {assignfeedback_file} aff
                                   ON (aff.assignment = a.id)
                               LEFT JOIN {plagiarism_turnitin_config} ptcfg
                                   ON (ptcfg.name = 'use_turnitin' AND ptcfg.value = 1))
                    UNION (SELECT 'quiz' AS modtype, q.id AS activityid,
                           q.name AS activityname,
                           CASE
                           WHEN qo.timeclose IS NOT NULL
                           THEN qo.timeclose
                           ELSE q.timeclose END AS duedate,
                           q.timeopen AS allowsubmissionsfromdate,
                           NULL AS cutoffdate,
                           CASE
                           WHEN qo.timeclose IS NOT NULL
                           THEN qo.timeclose
                           ELSE q.timeclose END AS gradingduedate,
                           NULL AS hasextension, NULL as nosubmissions,
                           qg.grade, NULL AS `status`,
                           NULL AS numfiles, NULL AS `value`,
                           qf.feedbacktext,
                           NULL AS `gradinggrade`, NULL AS `assessmentstart`,
                           NULL AS `workshopsubmission`, NULL AS `submissionsgrade`
                           FROM {quiz} q
                           LEFT JOIN (SELECT * FROM {quiz_overrides}
                                      WHERE userid = ?) qo
                              ON qo.quiz = q.id
                           LEFT JOIN {quiz_grades} AS qg
                              ON (qg.quiz = q.id AND qg.userid = ?)
                           LEFT JOIN {quiz_feedback} AS qf
                              ON (qf.quizid = q.id AND qg.grade IS NOT NULL
                                  AND (qg.grade > qf.mingrade
                                  OR (qg.grade = 0 AND qf.mingrade = 0))
                                  AND qg.grade <= qf.maxgrade))
                    UNION (SELECT 'workshop' AS modtype, w.id AS activityid,
                           w.`name` AS activityname, w.submissionend AS duedate,
                           w.submissionstart AS allowsubmissionsfromdate,
                           NULL AS cutoffdate,
                           w.assessmentend AS gradingduedate,
                           NULL AS hasextension, NULL as nosubmissions,
                           w.grade, NULL AS `status`,
                           NULL AS numfiles, NULL AS `value`,
                           NULL AS feedbacktext, w.gradinggrade, w.assessmentstart,
                                   ws.title as `workshopsubmission`, ws.grade AS `submissionsgrade`
                           FROM {workshop} w
                           LEFT JOIN {workshop_submissions} ws
                              ON (ws.workshopid = w.id AND ws.authorid = ?))
                    UNION (SELECT 'forum' AS modtype, id as activityid,
                           `name` as activityname, duedate,
                           NULL AS `allowsubmissionsfromdate`, cutoffdate,
                                   cutoffdate AS gradingduedate,
                           NULL AS hasextension, NULL as nosubmissions,
                           grade_forum AS grade, assessed AS `status`,
                           NULL AS numfiles, NULL AS `value`, NULL AS feedbacktext,
                           NULL AS `gradinggrade`, NULL AS `assessmentstart`,
                           NULL AS `workshopsubmission`, NULL AS `submissionsgrade`
                           FROM {forum})) ua
                              ON (ua.modtype = m.`name` AND ua.activityid = cm.instance)
                    WHERE cfd.value > 0 AND m.`name` IN ('assign' , 'quiz', 'forum', 'workshop')
                    AND c.enddate <= ? ORDER BY ".$sortcolumn." ".$sortorder;
          $params = array($userid, $userid, $userid, $userid, $userid,
                         $userid, $userid, $userid, $userid, $onemonth);
          $records = $DB->get_records_sql($sql, $params);
          $items = ($records) ? self::sanitize_records($records) : array();
          return $items;
     }

     public static function sanitize_records($records) {
          $items = array();

          if($records) {
               $recordsarray = (array) $records;
               foreach($recordsarray as $record) {
                    $isstudent = self::return_isstudent($record->modname, $record->id);
                    $modinfo = get_fast_modinfo($record->courseid);
                    $cm = $modinfo->get_cm($record->id);
                    // check if course module is visible to the user
                    $iscmvisible = $cm->uservisible;

                    if($isstudent && $iscmvisible) {
                         $item = new stdClass;
                         $item->coursetitle = $record->coursetitle;
                         $item->courseurl = self::return_courseurl($record->courseid);
                         $item->assessmenturl = self::return_assessmenturl($record->id, $record->modname);
                         $item->assessmentname = $record->activityname;
                         $item->assessmenttype = self::return_assessmenttype($record->gradecategoryname);
                         $item->weight = self::return_weight($item->assessmenttype, $record->aggregation,
                                                            $record->aggregationcoef, $record->aggregationcoef2);
                         $item->duedate = $record->duedate;
                         $item->formattedduedate = self::return_formattedduedate($record->duedate);
                         $item->hasextension = (!empty($record->hasextension)) ? true : false;
                         $item->startdate = $record->startdate;
                         $item->enddate = $record->enddate;
                         $item->formattedstartdate = date(get_string('date_m_y', 'block_gu_spdetails'), $record->startdate);
                         $item->formattedenddate = date(get_string('date_m_y', 'block_gu_spdetails'), $record->enddate);
                         $item->grading = self::return_grading($record->finalgrade, $record->gradetype,
                                                            $record->grademin, $record->grademax,
                                                            $record->gradeinformation, $record->gradingduedate,
                                                            $record->scale, $record->feedback);
                         $item->feedback = self::return_feedback($record->id, $record->modname, $item->grading->hasgrade,
                                                                 $record->feedback, $record->numfiles, $record->value,
                                                                 $record->feedbacktext, $record->gradingduedate);
                         $item->status = self::return_status($record->modname, $item->grading->hasgrade, $record->status,
                                                            $record->nosubmissions, $record->allowsubmissionsfromdate,
                                                            $record->duedate, $record->cutoffdate, $record->gradingduedate,
                                                            $item->hasextension, $record->workshopsubmission,
                                                            $record->feedback);
                         array_push($items, $item);
                    }
               }
          }

          return $items;
     }

     /**
      * Checks if the user has a 'student' role in the course
      *
      * @param string $modname expected arguments 'assign', 'quiz', 'forum' and 'workshop'
      * @param int $instance
      * @return boolean
      */
     public static function return_isstudent($modname, $instance) {
          $context = context_module::instance($instance);
          $isstudent = false;
          switch($modname) {
               case 'assign':
                    $isstudent = has_capability("mod/assign:submit", $context, null, false);
                    break;
               case 'quiz':
                    $isstudent = has_capability("mod/quiz:attempt", $context, null, false);
                    break;
               case 'forum':
                    $isstudent = has_capability("mod/forum:deleteownpost", $context, null, false) &&
                                 !has_capability("mod/forum:deleteanypost", $context, null, false);
                    break;
               case 'workshop':
                    $isstudent = has_capability("mod/workshop:submit", $context, null, false);
                    break;
          }
          return $isstudent;
     }

     /**
      * Returns the course URL
      *
      * @param int $courseid
      * @return string
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
      * @return string
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

          if($type === $formative || strpos($type, 'formative')) {
               $assessmenttype = get_string('formative', 'block_gu_spdetails');
          } else if($type === $summative || strpos($type, 'summative')) {
               $assessmenttype = get_string('summative', 'block_gu_spdetails');
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
      * @return string Weight (in percentage), or '—' if empty
      */
     public static function return_weight($assessmenttype, $aggregation, $aggregationcoef, $aggregationcoef2) {
          $summative = get_string('summative', 'block_gu_spdetails');
          $weight = 0;

          if($assessmenttype === $summative) {
               // $aggregation == '10', meaning 'Weighted mean of grades' is used
               $weight = ($aggregation == '10') ?
                         (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100) :
                         $aggregationcoef2 * 100;
          }

          $finalweight = ($weight > 0) ? round($weight, 2).'%' : get_string('emptyvalue', 'block_gu_spdetails');

          return $finalweight;
     }

     /**
      * Returns an object containing due date and related data
      *
      * @param int $duedate
      * @return string 
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
                                           $gradeinformation, $gradingduedate, $scale, $feedback) {
          $grading = new stdClass;
          $grading->gradetext = null;
          $grading->hasgrade = false;
          $grading->isprovisional = false;
          
          if(isset($finalgrade)) {
               $intgrade = round($finalgrade);
               $grading->hasgrade = true;
               $grading->isprovisional = ($gradeinformation) ? false : true;

               switch($gradetype) {
                    // gradetype = value
                    case '1':
                         $grading->gradetext = ($grademax == 22 && $grademin == 0) ?
                                               self::return_22grademaxpoint($intgrade) :
                                               round(($intgrade / ($grademax - $grademin)) * 100, 2).'%';
                         break;
                    // gradetype = scale
                    case '2':
                         $scalelist = make_menu_from_list($scale);
                         $scalegrade = $scalelist[$intgrade];

                         if(strpos($scalegrade, ':')){
                              $scalegradevalue = explode(':', $scalegrade);
                              $grading->gradetext  = $scalegradevalue[0];
                         }else{
                              $grading->gradetext  = $scalegrade;
                         }
                         break;
                    // gradetype = text
                    default:
                         $grading->gradetext = ($feedback) ? $feedback :
                                           get_string('emptyvalue', 'block_gu_spdetails');
                    break;
               }
          }else{
               $duedate = get_string('due', 'block_gu_spdetails').userdate($gradingduedate,
                          get_string('date_month_d', 'block_gu_spdetails'));
               $na = get_string('notavailable', 'block_gu_spdetails');
               $overdue = get_string('overdue', 'block_gu_spdetails');
               $tbc = get_string('tobeconfirmed', 'block_gu_spdetails');

               if($gradingduedate > 0) {
                    if($feedback === 'MV') {
                         $grading->gradetext = $duedate;
                    }

                    if($gradingduedate > time()) {
                         $grading->gradetext = $duedate;
                    }else{
                         $grading->gradetext = ($feedback === 'NS') ? $na : $overdue;
                    }
               }else{
                    $grading->gradetext = $tbc;
               }
          }

          return $grading;                               
     }

     /**
      * Returns the feedback object of an assessment
      *
      * @param int $id
      * @param string $modname
      * @param boolean $hasgrade
      * @param string $feedback
      * @param int $numfiles
      * @param int $value
      * @param string $feedbacktext
      * @return stdClass Object containing feedback text (could be feedback text for the link or feedback due date),
      *         hasfeedback, feedbackurl
      */
     public static function return_feedback($id, $modname, $hasgrade, $feedback,
                                            $numfiles, $value, $feedbacktext, $gradingduedate) {
          $fb = new stdClass;
          $fb->feedbacktext = null;
          $fb->hasfeedback = false;

          $duedate = get_string('due', 'block_gu_spdetails').userdate($gradingduedate,
                     get_string('date_month_d', 'block_gu_spdetails'));
          $na = get_string('notavailable', 'block_gu_spdetails');
          $overdue = get_string('overdue', 'block_gu_spdetails');
          $tbc = get_string('tobeconfirmed', 'block_gu_spdetails');

          if($hasgrade) {
               $readfeedback = get_string('readfeedback', 'block_gu_spdetails');
               $idintro = get_string('id_intro', 'block_gu_spdetails');
               $idfooter = get_string('id_pagefooter', 'block_gu_spdetails');
               $feedbackurl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));

               switch($modname) {
                    case 'assign':
                         $fb->feedbackurl = ($value > 0) ? $feedbackurl.$idintro :
                                            ((!empty($feedback) || $numfiles > 0) ? $feedbackurl.$idfooter : null);
                         if(isset($fb->feedbackurl)) {
                              $fb->feedbacktext = $readfeedback;
                              $fb->hasfeedback = true;
                         }else{
                              $fb->feedbacktext = ($gradingduedate > 0) ?
                                                  (($gradingduedate > time()) ? $duedate : $overdue) : $tbc;
                         }
                         break;
                    case 'quiz':
                         if($feedbacktext) {
                              $fb->feedbacktext = $readfeedback;
                              $fb->hasfeedback = true;
                              $idfeedback = get_string('id_feedback', 'block_gu_spdetails');
                              $fb->feedbackurl = $feedbackurl.$idfeedback;
                         }else{
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
                    // forum
                    default:
                         $fb->hasfeedback = true;
                         $fb->feedbacktext = $readfeedback;
                         $fb->feedbackurl = $feedbackurl.$idfooter;
                         break;

               }
          }else{
               if($gradingduedate > 0) {
                    if($feedback === 'MV') {
                         $fb->feedbacktext = $duedate;
                    }

                    if($gradingduedate > time()) {
                         $fb->feedbacktext = $duedate;
                    }else{
                         $fb->feedbacktext = ($feedback === 'NS') ? $na : $overdue;
                    }
               }else{
                    $fb->feedbacktext = $tbc;
               }
          }

          return $fb;
     }

     /**
      * Returns status object of an assessment
      * @todo Include 'feedback' MV and NS on logic
      *
      * @param string $modname
      * @param boolean $hasgrade
      * @param string $status
      * @param boolean $nosubmissions
      * @param int $allowsubmissionsfromdate
      * @param int $duedate
      * @param int $cutoffdate
      * @param boolean $hasextension
      * @param string $workshopsubmission
      * @param string $feedback
      * @return stdClass Object containing status text, status class, hasstatusurl
      */
     public static function return_status($modname, $hasgrade, $status, $nosubmissions,
                                          $allowsubmissionsfromdate, $duedate, $cutoffdate, $gradingduedate,
                                          $hasextension, $workshopsubmission, $feedback) {
          $graded = get_string('status_graded', 'block_gu_spdetails');
          $notopen = get_string('status_notopen', 'block_gu_spdetails');
          $notsubmitted = get_string('status_notsubmitted', 'block_gu_spdetails');
          $overdue = get_string('status_overdue', 'block_gu_spdetails');
          $submit = get_string('status_submit', 'block_gu_spdetails');
          $submitted = get_string('status_submitted', 'block_gu_spdetails');
          $unavailable = get_string('status_unavailable', 'block_gu_spdetails');

          $classgraded = get_string('class_graded', 'block_gu_spdetails');
          $classoverdue = get_string('class_overdue', 'block_gu_spdetails');
          $classsubmit = get_string('class_submit', 'block_gu_spdetails');
          $classsubmitted = get_string('class_submitted', 'block_gu_spdetails');

          $s = new stdClass;
          $s->statustext = $notopen;
          $s->class = null;
          $s->hasstatusurl = false;

          if($hasgrade) {
               $s->statustext = $graded;
               $s->class = $classgraded;
          }else if($feedback === 'NS' && $duedate < time() && $cutoffdate > time() && $gradingduedate > time()){
               $s->statustext = $overdue;
               $s->class = $classoverdue;
               $s->hasstatusurl = false;
          }else if($feedback === 'NS' && $duedate < time() && $cutoffdate < time() && $gradingduedate > time()){
               $s->statustext = $unavailable;
          }else if($feedback === 'NS' && $duedate < time() && $cutoffdate < time() && $gradingduedate < time()){
               $s->statustext = $notsubmitted;
          }else {
               switch($modname) {
                    case 'assign':
                         if($status === $submitted) {
                              $s->statustext = $submitted;
                              $s->class = $classsubmitted;
                         }else{
                              if($nosubmissions > 0) {
                                   $s->statustext = $unavailable;
                              }else{
                                   if($allowsubmissionsfromdate > time() || $duedate == 0) {
                                        $s->statustext = $notopen;
                                   }else{
                                        if($duedate < time()) {
                                             if($cutoffdate < time()) {
                                                  $s->statustext = $overdue;
                                                  $s->class = $classoverdue;
                                                  $s->hasstatusurl = ($cutoffdate == 0) ? true : false;
                                             }else{
                                                  $s->statustext = $notsubmitted;
                                             }
                                        }else{
                                             $s->hasstatusurl = true;
                                             $s->statustext = $submit;
                                             $s->class = $classsubmit;
                                        }
                                   }
                              }
                         }
                         break;
                    case 'quiz':
                         if($allowsubmissionsfromdate > time()) {
                              $s->statustext = $notopen;
                         }else{
                              if($duedate < time() && $duedate != 0) {
                                   $s->statustext = $notsubmitted;
                              }else{
                                   $s->hasstatusurl = true;
                                   $s->statustext = $submit;
                                   $s->class = $classsubmit;
                              }
                         }
                         break;
                    case 'workshop':
                         if(!empty($workshopsubmission)) {
                              $s->statustext = $submitted;
                              $s->class = $classsubmitted;
                         }else{
                              if($allowsubmissionsfromdate > time() || $duedate == 0) {
                                   $s->statustext = $notopen;
                               }else{
                                   if($duedate < time()) {
                                        $s->statustext = $notsubmitted;
                                   }else{
                                        $s->hasstatusurl = true;
                                        $s->statustext = $submit;
                                        $s->class = $classsubmit;
                                   }
                               }
                         }
                         break;
                    // forum
                    default:
                         if($duedate < time()) {
                              if($cutoffdate < time()) {
                                   $s->statustext = $overdue;
                                   $s->class = $classoverdue;
                                   $s->hasstatusurl = ($cutoffdate == 0) ? true : false;
                              }else{
                                   $s->statustext = $notsubmitted;
                              }
                         }else{
                              $s->hasstatusurl = true;
                              $s->statustext = $submit;
                              $s->class = $classsubmit;
                         }
                         break;
               }
          }
          return $s;
     }

     /**
      * Returns a corresponding value for grades with gradetype = "value" and grademax = "22"
      *
      * @param int $grade
      * @return string
      */
     public static function return_22grademaxpoint($grade) {
          $values = array('H', 'G2', 'G1', 'F3', 'F2', 'F1', 'E3', 'E2', 'E1', 'D3', 'D2', 'D1',
                         'C3', 'C2', 'C1', 'B3', 'B2', 'B1', 'A5', 'A4', 'A3', 'A2', 'A1');
          return $values[$grade];
     }
}
