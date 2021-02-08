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
function return_assessments_count($userid) {
    global $DB;
    $onemonth = time() + (86400 * 30);

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
                    AND c.enddate > ?";
    $params = array($userid, $userid, $userid, $userid, $userid,
                    $userid, $userid, $userid, $userid, $onemonth);
    $records = $DB->get_records_sql($sql, $params);
    $counter = new stdClass;
    $counter->submitted = 0;
    $counter->tosubmit = 0;
    $counter->overdue = 0;
    $counter->marked = 0;

    if($records) {
        $recordsarray = (array) $records;
        foreach($recordsarray as $record) {
            $isstudent = return_isstudent($record->modname, $record->id);
            $modinfo = get_fast_modinfo($record->courseid);
            $cm = $modinfo->get_cm($record->id);
            // check if course module is visible to the user
            $iscmvisible = $cm->uservisible;

            if($isstudent && $iscmvisible) {
                if(isset($record->finalgrade)) {
                    $counter->marked++;
                    $counter->submitted++;
                }else if($record->feedback === 'NS' && $record->duedate < time()
                         && $record->cutoffdate > time() && $record->gradingduedate > time()) {
                    $counter->overdue++;
                }else{
                    switch($record->modname) {
                        case 'assign':
                            if($record->status === 'submitted') {
                                $counter->submitted++;
                            }else{
                                if($record->duedate < time()) {
                                    if($record->cutoffdate == 0 || $record->cutoffdate > time()) {
                                        $counter->overdue++;
                                    }
                                }else{
                                    $counter->tosubmit++;
                                }
                            }
                            break;
                        case 'quiz':
                            if($record->duedate > time()) {
                                $counter->tosubmit++;
                            }
                            break;
                        case 'workshop':
                            if(!empty($record->workshopsubmission)) {
                                $counter->submitted++;
                            }else{
                                if($record->duedate > time()) {
                                    $counter->tosubmit++;
                                }
                            }
                            break;
                        // forum
                        default:
                            if($record->duedate < time()) {
                                if($record->cutoffdate == 0 || $record->cutoffdate > time()) {
                                    $counter->overdue++;
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

/**
 * Checks if the user has a 'student' role in the course
 *
 * @param string $modname expected arguments 'assign', 'quiz', 'forum' and 'workshop'
 * @param int $instance
 * @return boolean
 */
function return_isstudent($modname, $instance) {
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
