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
 * Library of functions used by the quiz module.
 *
 * This contains functions that are called from within the quiz group sub_module only
 *
 * @package   quiz_group
 * @copyright 2017 Camille Tardy, University of Geneva
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Return grouping used in Group quiz or false if not found
 * @param int $quizid
 * @return int $groupingid
 */
function get_groupquiz_groupingid($quizid) {
    global $DB;

    // Todo use get_fieldset_select instead of get_record ??
    $quizgroupgroupingid = $DB->get_record('quiz_group', array('quizid' => $quizid), 'groupingid', 'IGNORE_MISSING');

    if ($quizgroupgroupingid == false) {
        $groupingid = null;
    } else {
        $groupingid = $quizgroupgroupingid->groupingid;
    }

    return $groupingid;
}

/**
 * Retrieve group id for the quiz according to the user's groups and the quiz grouping
 * -> returns grpid = 0 if user not in grouping
 *
 * @param int $userid
 * @param int $quizid
 * @param int $courseid
 * @param int $groupingid
 *
 * @return int $grpid
 */
function get_user_group_for_groupquiz($userid, $quizid, $courseid, $groupingid = null) {
    // Retreive all groups for user.
    $usergrpids = groups_get_user_groups($courseid, $userid);
    // Keep only grp ids.
    $usergrps = array();
    foreach ($usergrpids as $key => $gpid) {
        foreach ($gpid as $k => $gid) {
            // If not alreday in array add id.
            if (!in_array($gid, $usergrps, false)) {
                $usergrps[] = $gid;
            }
        }
    }

    // Retrieve grouping ID used in Quiz_group.
    if ($groupingid == null ) {
        $groupingid = get_groupquiz_groupingid($quizid);
    }

    // Filter group from grouping.
    $grpsingrouping = groups_get_all_groups(intval($courseid), null, intval($groupingid));
    $grpsinging = array();
    // Keep only grp ids.
    foreach ($grpsingrouping as $gp) {
        $grpsinging[] = $gp->id;
    }

    // Compare the 2 arrays and retrieve group id.
    $grpid = 0;
    $grpsintersect = array_intersect($usergrps, $grpsinging);
    // If not empty grp_intersect pick the first group.

    if (!empty($grpsintersect)) {
        $grpid = $grpsintersect[0];
    }

    return $grpid;
}


/**
 * Transform an attempt obj (event) in a group attempt object to save in DB
 *
 * @param quiz_attempt $attempt
 * @param int $quizid
 * @param int $grpid
 * @param int $groupingid
 *
 * @return group_attempt $grp_attempt
 */
function quiz_group_attempt_to_groupattempt_dbobject($attempt, $quizid, $grpid, $groupingid) {

    // Fetch the informations.
    $userid = $attempt['userid'];

    // Fill in the group_attempt object.
    $grpattempt = new \stdClass();
    // Attemptid cannot be found here as attempt not yet saved in DB, set default to null.
    $grpattempt->attemptid = null;
    $grpattempt->quizid = $quizid;
    $grpattempt->userid = $userid;
    $grpattempt->groupid = $grpid;
    $grpattempt->groupingid = $groupingid;
    $grpattempt->timemodified = time();

    return $grpattempt;
}


/**
 * Create group attempt in DB
 * from quiz attempt in DB
 *
 * @param attempt $attempt
 * @param int $courseid
 */
function create_groupattempt_from_attempt($attempt, $courseid) {
    global $DB;

    $userid = $attempt->userid;
    $quizid = $attempt->quiz;
    $groupingid = get_groupquiz_groupingid($quizid);

    $grpatt = new stdClass();
    $grpatt->attemptid = $attempt->id;
    $grpatt->quizid = $quizid;
    $grpatt->userid = $userid;
    $grpatt->groupingid = $groupingid;
    $grpatt->timemodified = time();

    $grpatt->groupid = get_user_group_for_groupquiz($userid, $quizid, $courseid, $groupingid);

    if ($groupingid > 0 && $grpatt->groupid > 0) {
        // Create grp attempt in DB.
        $DB->insert_record('quiz_group_attempts', $grpatt);
    } else if ($groupingid > 0 && $grpatt->groupid == 0) {
        // Do not save group attempt if its value is 0, and display error message.
        // Dispaly error message user not in grouing selected for group quiz.
        \core\notification::error(get_string('user_notin_grouping', 'quiz_group'));
    }

}


/**
 * Dispatch grade function.
 *
 * @param quiz $quiz
 * @param int $groupingID
 *
 */
function dispatch_grade($quiz, $groupingid) {
    global $DB, $PAGE;
    $quizid = $quiz->id;
    $courseid = $PAGE->course->id;

    $grpattemptsarray = $DB->get_records('quiz_group_attempts', array('quizid' => $quizid, 'groupingid' => $groupingid));
    // Change order of fields to get userid as index for grade array.
    $quizgradesarray = $DB->get_records('quiz_grades', array('quiz' => $quizid), '', 'userid, id, quiz, grade, timemodified');

    // If no grp attempt : create from DB if they exist.
    if (empty($grpattemptsarray)) {
        // Check if attempts exist in attempt table that didnt get saved in grp attempt dB; if yes copy them in grp attempt table.
        $quizattemptsarray = $DB->get_records('quiz_attempts', array('quiz' => $quizid, 'state' => 'finished'));

        foreach ($quizattemptsarray as $att) {
            // If user not in correct grouping do not create.
            $grpid = get_user_group_for_groupquiz($att->userid, $quizid, $courseid);
            if ($grpid > 0) {
                create_groupattempt_from_attempt($att, $courseid);
            }
            // If user not in grouping do not create grp_attempt.
        }

        $grpattemptsarray = $DB->get_records('quiz_group_attempts', array('quizid' => $quizid, 'groupingid' => $groupingid));
    }

    foreach ($grpattemptsarray as $grpattempt) {
        // Get group id.
        $groupid = $grpattempt->groupid;
        $attemptid = $grpattempt->attemptid;
        // Get attempt for grp_attempt.
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));

        // Get all user for group id.
        $users = groups_get_members($groupid, 'u.id');

        // Retrieve grade from this user.
        $insertgrade = new \stdClass();
        foreach ($quizgradesarray as $qg) {
            if ($qg->userid == $attempt->userid) {
                // Copy grade value to insert item.
                $insertgrade->quiz = $qg->quiz;
                $insertgrade->userid = $qg->userid;
                $insertgrade->grade = $qg->grade;
                $insertgrade->timemodified = $qg->timemodified;
            }
        }

        // Duplicate grade for each user iin list.
        foreach ($users as $u) {
            // Delete current user of users list.
            if ($u->id == $attempt->userid) {
                // User of original grade, do nothing.
                continue;
            } else {

                // Deal with quiz grade table.
                $insertgrade->userid = $u->id;
                // If not already in DB.
                $userquizgradedb = $DB->get_record('quiz_grades', array('quiz' => $quizid, 'userid' => $u->id));
                if ($userquizgradedb == false) {
                    // If not exist insert in DB.
                    $DB->insert_record('quiz_grades', $insertgrade, false);
                } else if ($userquizgradedb->grade !== $qg->grade) {
                    // If exist but grade different, update grade.

                    $update = new stdClass();
                    $update->id = $quizgradesarray[$u->id]->id;
                    $update->grade = $insertgrade->grade;
                    $DB->update_record('quiz_grades', $update, false);
                }

                // Deal with gradeBook
                // get user grade for quiz.
                $gradeforquiz = quiz_get_user_grades($quiz, $u->id);
                if ($gradeforquiz && ($gradeforquiz[$u->id]->rawgrade !== $insertgrade->grade)) {
                    // If exist, update if grade is different.
                    quiz_grade_item_update($quiz, $gradeforquiz);
                } else if (empty($gradeforquiz)) {
                    // If don't exist create grade.
                    $grade = new stdClass();
                    $grade->userid = $u->id;
                    $grade->rawgrade = $insertgrade->grade;

                    quiz_grade_item_update($quiz, $grade);

                }
            }

        }
    }

    // Display validation message.
    \core\notification::success(get_string('dispatchgrade_done', 'quiz_group'));

}


/**
 * Logic to happen when a/some group(s) has/have been deleted in a course.
 * Check which grps are valid in a given course, which quiz exist in course (quiz_id)
 * for each quiz verify if grp_attempt exist and
 * delete those that belong to a group that is no longer in the active group list
 *
 * @param int $courseid The course ID.
 * @return void
 */
function quiz_process_grp_deleted_in_course($courseid) {
    global $DB;

    // Get course group (return :  array of group objects (id, courseid, name, enrolmentkey).
    // Translate in text list the ids.
    $groups = $DB->get_records('groups', array('courseid' => $courseid), '', 'id');
    $groupslist = "";
    foreach ($groups as $g) {
        $groupslist .= '"'.$g->id.'",';
    }

    // Get all course quizs id.
    $quizsid = $DB->get_records('quiz', array('course' => $courseid), '', 'id, name');

    // Get all grp attempts foreach quizs id ang groups not in list (--> deleted).
    foreach ($quizsid as $key => $q) {
        $sql = "SELECT id FROM {quiz_group_attempts} WHERE quizid = ? AND groupid NOT IN (?)";
        $grpattemptsid = $DB->get_records_sql($sql, array($q->id, $groupslist));
        // Delete each grp attempt from deleted grp.
        foreach ($grpattemptsid as $ga) {
            // Delete record in DB.
            $attid = $ga->id;
            $DB->delete_records('quiz_group_attempts', array('id' => $attid));
        }
    }

}

function quiz_process_delete_group_attempts($courseid) {
    global $DB;

    // Get all course quizs id.
    $quizsid = $DB->get_records('quiz', array('course' => $courseid), '', 'id, name');

    // Get all grp attempts foreach quizs id ang groups not in list (--> deleted).
    foreach ($quizsid as $key => $q) {
        $sql = "SELECT id FROM {quiz_group_attempts} WHERE quizid = ?";
        $grpattemptsid = $DB->get_records_sql($sql, [$q->id]);
        // Delete each grp attempt from deleted grp.
        foreach ($grpattemptsid as $ga) {
            // Delete record in DB.
            $attid = $ga->id;
            $DB->delete_records('quiz_group_attempts', array('id' => $attid));
        }
    }
}
