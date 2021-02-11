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
    $enddate = time() + (86400 * 30);

    $sql = "SELECT cm.id, c.id AS courseid, m.name AS modname,
                gg.finalgrade, gg.feedback, ua.duedate,
                ua.cutoffdate, ua.gradingduedate,
                ua.status, ua.workshopsubmission
            FROM mdl_course c
            JOIN mdl_course_modules cm
            ON (c.id = cm.course AND cm.deletioninprogress = 0)
            JOIN mdl_course_sections cs
            ON (cs.course = c.id AND cs.id = cm.section)
            JOIN mdl_modules m ON m.id = cm.module
            JOIN (SELECT gi1.id, gi1.categoryid, gi1.gradetype,
                    gi1.grademax, gi1.grademin, gi1.gradepass, gi1.scaleid,
                    gi1.aggregationcoef, gi1.aggregationcoef2,
                    gi1.iteminstance, gi1.courseid, gi1.itemmodule
                    FROM mdl_grade_items gi1
                    LEFT JOIN mdl_grade_items gi2
                    ON (gi2.iteminstance = gi1.iteminstance
                    AND gi2.itemmodule = gi1.itemmodule
                    AND gi2.itemnumber <> gi1.itemnumber)
                    WHERE gi1.itemtype = 'mod' AND gi1.gradetype != 0
                    AND (gi1.itemnumber = 0 OR gi2.itemnumber IS NULL)) gi
                    ON (gi.iteminstance = cm.instance AND gi.courseid = c.id
                    AND gi.itemmodule = m.`name`)
            JOIN mdl_customfield_field cff
            ON cff.shortname = 'show_on_studentdashboard'
            JOIN mdl_customfield_data cfd
            ON (cfd.fieldid = cff.id AND cfd.instanceid = c.id)
            LEFT JOIN mdl_grade_grades gg
            ON (gg.itemid = gi.id AND gg.userid = ?)
            LEFT JOIN ((SELECT 'assign' AS modtype, a.id AS activityid,
                        CASE
                        WHEN auf.extensionduedate IS NOT NULL
                        AND auf.extensionduedate > 0
                        THEN auf.extensionduedate
                        WHEN ao.duedate IS NOT NULL THEN ao.duedate
                        ELSE a.duedate END AS duedate,
                        CASE
                        WHEN ao.cutoffdate IS NOT NULL
                        THEN ao.cutoffdate
                        ELSE a.cutoffdate
                        END AS cutoffdate,
                        a.gradingduedate,
                        `as`.`status`,
                        NULL AS `workshopsubmission`
                        FROM mdl_assign AS a
                        LEFT JOIN mdl_assign_overrides ao
                            ON (ao.assignid = a.id)
                        LEFT JOIN mdl_assign_user_flags auf
                            ON auf.assignment = a.id
                        LEFT JOIN mdl_assign_submission `as`
                            ON (`as`.assignment = a.id AND `as`.userid = ?))
            UNION (SELECT 'quiz' AS modtype, q.id AS activityid,
                    CASE
                    WHEN qo.timeclose IS NOT NULL
                    THEN qo.timeclose
                    ELSE q.timeclose END AS duedate,
                    NULL AS cutoffdate,
                    CASE
                    WHEN qo.timeclose IS NOT NULL
                    THEN qo.timeclose
                    ELSE q.timeclose END AS gradingduedate,
                    NULL AS `status`,
                    NULL AS `workshopsubmission`
                    FROM mdl_quiz q
                    LEFT JOIN mdl_quiz_overrides qo
                        ON qo.quiz = q.id
                    LEFT JOIN mdl_quiz_grades AS qg
                        ON (qg.quiz = q.id AND qg.userid = ?)
                    LEFT JOIN mdl_quiz_feedback AS qf
                        ON (qf.quizid = q.id AND qg.grade IS NOT NULL
                            AND (qg.grade > qf.mingrade
                            OR (qg.grade = 0 AND qf.mingrade = 0))
                            AND qg.grade <= qf.maxgrade))
            UNION (SELECT 'workshop' AS modtype, w.id AS activityid,
                    w.submissionend AS duedate,
                    NULL AS cutoffdate,
                    w.assessmentend AS gradingduedate,
                    NULL AS `status`,
                    ws.title as `workshopsubmission`
                    FROM mdl_workshop w
                    LEFT JOIN mdl_workshop_submissions ws
                        ON (ws.workshopid = w.id AND ws.authorid = ?))
            UNION (SELECT 'forum' AS modtype, id as activityid,
                    duedate,
                    cutoffdate,
                    cutoffdate AS gradingduedate,
                    assessed AS `status`,
                    NULL AS `workshopsubmission`
                    FROM mdl_forum)) ua
            ON (ua.modtype = m.`name` AND ua.activityid = cm.instance)
            WHERE cfd.value > 0 AND m.`name` IN ('assign' , 'quiz', 'forum', 'workshop')
            AND cm.visible = 1 AND c.enddate > ?";
    $params = array($userid, $userid, $userid, $userid, $enddate);
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
