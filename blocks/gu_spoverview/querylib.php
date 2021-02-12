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
 * Contains methods for Assessments at a Glance block
 *
 * @package    block_gu_spdetails
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns an object that counts the status of active assessments
 *
 * @param int $userid
 * @return stdClass $counter
 */
function return_assessments_count($userid, $courseids) {
    global $DB;
    $enddate = time();

    $assignfields = "cm.id, a.course AS courseid,
                    gi.itemmodule AS modname,
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
                    gg.finalgrade,
                    gg.feedback,
                    `as`.`status`, NULL AS submissions, c.enddate";
    $assignjoins = "LEFT JOIN {assign_overrides} ao ON (ao.assignid = a.id AND ao.userid = ?)
                    LEFT JOIN {assign_user_flags} auf ON (auf.assignment = a.id AND auf.userid = ?)
                    LEFT JOIN (SELECT a.* FROM {assign_submission} a
                                LEFT OUTER JOIN {assign_submission} b
                                ON a.id = b.id AND a.attemptnumber < b.attemptnumber
                                WHERE b.id IS NULL) `as`
                        ON (`as`.assignment = a.id AND `as`.userid = ?)
                    LEFT JOIN {modules} m ON (m.name = 'assign')
                    LEFT JOIN {course_modules} cm ON (cm.course = a.course AND cm.`instance` = a.id
                        AND cm.module = m.id AND cm.deletioninprogress = 0)
                    LEFT JOIN {grade_items} gi ON (gi.iteminstance = cm.`instance` AND gi.courseid = a.course
                        AND gi.itemtype = 'mod' AND gi.itemmodule = 'assign')
                    LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = ?)
                    LEFT JOIN {course} c ON c.id = a.course";
    $assignenddate = "AND (c.enddate + 86400 * 30 > ? OR
                    CASE
                    WHEN auf.extensionduedate IS NOT NULL AND auf.extensionduedate != 0
                    THEN auf.extensionduedate
                    WHEN ao.duedate IS NOT NULL THEN ao.duedate
                    ELSE a.duedate END + 86400 * 30 > ?)";
    $assignwhere = "a.course IN ($courseids) $assignenddate";
    $assignsql = "SELECT $assignfields FROM {assign} a $assignjoins WHERE $assignwhere";
    $assignparams = array($userid, $userid, $userid, $userid, $enddate, $enddate);

    $forumfields = "cm.id, f.course,
                    gi.itemmodule AS modname,
                    NULL AS `allowsubmissionsfromdate`,
                    f.duedate, f.cutoffdate, f.cutoffdate AS gradingduedate,
                    gg.finalgrade, gg.feedback, NULL AS `status`,
                    NULL AS submissions, c.enddate";
    $forumjoins = "LEFT JOIN {modules} m ON (m.name = 'forum')
                LEFT JOIN {course_modules} cm ON (cm.course = f.course AND cm.`instance` = f.id
                    AND cm.module = m.id AND cm.deletioninprogress = 0)
                LEFT JOIN {course} c ON c.id = f.course
                JOIN (SELECT gi1.id, gi1.categoryid, gi1.gradetype, gi1.grademax, gi1.grademin,
                    gi1.gradepass, gi1.scaleid, gi1.aggregationcoef, gi1.aggregationcoef2,
                    gi1.iteminstance, gi1.courseid, gi1.itemmodule
                    FROM {grade_items} gi1
                    LEFT JOIN {grade_items} gi2 ON (gi2.iteminstance = gi1.iteminstance
                    AND gi2.itemmodule = gi1.itemmodule AND gi2.itemnumber <> gi1.itemnumber)
                    WHERE gi1.itemtype = 'mod' AND gi1.gradetype != 0
                        AND (gi1.itemnumber = 0 OR gi2.itemnumber IS NULL)
                        AND gi1.itemmodule = 'forum') gi
                        ON (gi.iteminstance = cm.instance AND gi.courseid = c.id)
                LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = ?)";
    $forumenddate = "AND (c.enddate + 86400 * 30 > ?
                    OR f.duedate + 86400 * 30 > ?)";
    $forumwhere = "f.course IN ($courseids) $forumenddate";
    $forumsql = "SELECT $forumfields FROM {forum} f $forumjoins WHERE $forumwhere";
    $forumparams = array($userid, $enddate, $enddate);

    $quizfields = "cm.id, q.course AS courseid,
                    gi.itemmodule AS modname,
                    q.timeopen AS allowsubmissionsfromdate,
                    CASE
                        WHEN qo.timeclose IS NOT NULL THEN qo.timeclose
                        ELSE q.timeclose END AS duedate,
                    NULL AS cutoffdate,
                    CASE
                        WHEN qo.timeclose IS NOT NULL THEN qo.timeclose
                        ELSE q.timeclose END AS gradingduedate,
                    gg.finalgrade, qf.feedbacktext AS feedback,
                    qa.state AS `status`, NULL AS submissions, c.enddate";
    $quizjoins = "LEFT JOIN {quiz_overrides} AS qo ON (qo.quiz = q.id AND qo.userid = ?)
                    LEFT JOIN {quiz_grades} AS qg ON (qg.quiz = q.id AND qg.userid = ?)
                    LEFT JOIN {quiz_feedback} AS qf ON (qf.quizid = q.id AND qg.grade IS NOT NULL
                        AND (qg.grade > qf.mingrade OR (qg.grade = 0 AND qf.mingrade = 0))
                        AND qg.grade <= qf.maxgrade)
                    LEFT JOIN {quiz_attempts} AS qa ON (qa.quiz = q.id AND qa.userid = ?
                        AND qa.sumgrades IS NULL)
                    LEFT JOIN {modules} m ON (m.name = 'quiz')
                    LEFT JOIN {course_modules} cm ON (cm.course = q.course AND cm.`instance` = q.id
                        AND cm.module = m.id AND cm.deletioninprogress = 0)
                    LEFT JOIN {grade_items} gi ON (gi.iteminstance = cm.`instance`
                        AND gi.courseid = q.course AND gi.itemtype = 'mod' AND gi.itemmodule = 'quiz')
                    LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = ?)
                    LEFT JOIN {course} c ON c.id = q.course";
    $quizenddate = "AND (c.enddate + 86400 * 30 > ? OR
                    CASE
                    WHEN qo.timeclose IS NOT NULL THEN qo.timeclose
                    ELSE q.timeclose END + 86400 * 30 > ?)";
    $quizwhere = "q.course IN ($courseids) $quizenddate";
    $quizsql = "SELECT $quizfields FROM {quiz} q $quizjoins WHERE $quizwhere";
    $quizparams = array($userid, $userid, $userid, $userid, $enddate, $enddate);

    $workshopfields = "cm.id, w.course AS courseid,
                        gi.itemmodule AS modname,
                        w.submissionstart AS allowsubmissionsfromdate,
                        w.submissionend AS duedate, NULL AS cutoffdate,
                        w.assessmentend AS gradingduedate,
                        gg.finalgrade, gg.feedback,
                        NULL AS `status`, ws.title AS submissions, c.enddate";
    $workshopjoins = "LEFT JOIN {workshop_submissions} ws
                    ON (ws.workshopid = w.id AND ws.authorid = ?)
                    LEFT JOIN mdl_modules m ON (m.name = 'workshop')
                    LEFT JOIN mdl_course_modules cm ON (cm.course = w.course
                        AND cm.`instance` = w.id AND cm.module = m.id
                        AND cm.deletioninprogress = 0)
                    LEFT JOIN mdl_grade_items gi ON (gi.iteminstance = cm.`instance`
                        AND gi.courseid = w.course AND gi.itemtype = 'mod'
                        AND gi.itemmodule = 'workshop' AND gi.itemnumber = 0)
                    LEFT JOIN mdl_grade_grades gg ON (gg.itemid = gi.id AND gg.userid = ?)
                    LEFT JOIN mdl_course c ON c.id = w.course";
    $workshopenddate = "AND (c.enddate + 86400 * 30 > ?
                        OR w.submissionend + 86400 * 30 > ?)";
    $workshopwhere = "w.course IN ($courseids) $workshopenddate";
    $workshopsql = "SELECT $workshopfields FROM {workshop} w $workshopjoins WHERE $workshopwhere";
    $workshopparams = array($userid, $userid, $enddate, $enddate);

    $unionsql = "($assignsql) UNION ($forumsql) UNION ($quizsql) UNION ($workshopsql)";
    $unionparams = array_merge($assignparams, $forumparams, $quizparams, $workshopparams);

    $records = $DB->get_records_sql($unionsql, $unionparams);
    $counter = new stdClass;
    $counter->submitted = 0;
    $counter->tosubmit = 0;
    $counter->overdue = 0;
    $counter->marked = 0;

    if($records) {
        $recordsarray = (array) $records;
        foreach($recordsarray as $record) {
            $modinfo = get_fast_modinfo($record->courseid);
            $cm = $modinfo->get_cm($record->id);
            // check if course module is visible to the user
            $iscmvisible = $cm->uservisible;

            if($iscmvisible) {
                if(isset($record->finalgrade)) {
                    $counter->marked++;
                    $counter->submitted++;
                }else if($record->feedback === 'NS' && $record->duedate < time()
                         && $record->cutoffdate > time() && $record->gradingduedate > time()) {
                    $counter->overdue++;
                    $counter->tosubmit++;
                }else{
                    switch($record->modname) {
                        case 'assign':
                            if($record->status === 'submitted') {
                                $counter->submitted++;
                            }else{
                                if($record->allowsubmissionsfromdate <= time() || $record->duedate != 0) {
                                    if($record->duedate < time()) {
                                        if($record->cutoffdate == 0 || $record->cutoffdate > time()) {
                                            $counter->overdue++;
                                            $counter->tosubmit++;
                                        }
                                    }else{
                                        $counter->tosubmit++;
                                    }
                                }
                            }
                            break;
                        case 'quiz':
                            if($record->allowsubmissionsfromdate <= time()) { 
                                if($record->status === 'finished'){
                                    $counter->submitted++;
                                }else{
                                    $counter->tosubmit++;
                                }
                            }
                            break;
                        case 'workshop':
                            if(!empty($record->submissions)) {
                                $counter->submitted++;
                            }else{
                                if($record->allowsubmissionsfromdate <= time() || $record->duedate != 0) { 
                                    if($record->duedate > time()) {
                                        $counter->tosubmit++;
                                    }
                                }
                            }
                            break;
                        // forum
                        default:
                            if($record->duedate < time()) {
                                if($record->cutoffdate == 0 || $record->cutoffdate > time()) {
                                    $counter->overdue++;
                                    $counter->tosubmit++;
                                }
                            }else{
                                $counter->tosubmit++;
                            }
                            break;
                    }
                }
            }
        }
    }
    return $counter;
}
