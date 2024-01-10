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


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/classes/event/attempt_started.php');
require_once($CFG->dirroot . '/mod/quiz/report/group/locallib.php');



/**
 * This file defines the function triggered by the event observer.
 *
 * @package   quiz_group
 * @copyright 2017 Camille Tardy, University of Geneva
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class quiz_group_observer {

    /**
     * Event processor - attempt started
     * Create new attempt record in group quiz table
     *
     * @param \mod_quiz\event\attempt_started $event
     * @return bool
     */
    public static function attempt_started(core\event\base $event) {
        global $DB, $PAGE;

        $attempt = $event->get_data();
        $cm = $PAGE->cm;
        $quizid = $cm->instance;

        $groupingid = get_groupquiz_groupingid($quizid);

        if ($groupingid == null || $groupingid == 0) {
            // If grp_quiz is not enabled do nothing.
        } else {
            // Check if a user from the same group is trying to attempt quiz when we already have an attempt for this group.
            $usergrp = get_user_group_for_groupquiz($attempt['userid'], $quizid, $attempt['courseid']);

            $attemptgrpindb = $DB->get_records(
                'quiz_group_attempts',
                array('quizid' => $quizid, 'groupid' => $usergrp, 'groupingid' => $groupingid)
            );
            if (!empty($attemptgrpindb)) {
                // An attempt already exist for this group block current user attempt.
                $grpattemptid = 0;
                $grpname = $DB->get_field('groups', 'name', array('id' => $usergrp));
                // Return to view quiz page with message  : warning(yellow) --> NOTIFY_WARNING // error(red) --> NOTIFY_ERROR.
                redirect(
                    new moodle_url('/mod/quiz/view.php', array('id' => $cm->id)),
                    get_string('group_attempt_already_created', 'quiz_group', $grpname),
                    null,
                    \core\output\notification::NOTIFY_ERROR
                );

            } else {
                // No attempt yet for this group : proceed with current user.

                $groupattempt = quiz_group_attempt_to_groupattempt_dbobject($attempt, $quizid, $usergrp, $groupingid);

                // Save in DB.
                $grpattemptid = $DB->insert_record('quiz_group_attempts', $groupattempt, true);
            }

            return $grpattemptid;
        }

    }

    /**
     * Event processor - attempt submited
     * edit the group attemp object actual attempt id
     *
     * @param \mod_quiz\event\attempt_submitted $event
     * @return bool
     */
    public static function attempt_submitted(core\event\base $event) {
        global $DB;

        $attempt = $event->get_data();
        $quizid = $attempt['other']['quizid'];
        $userid = $attempt['userid'];
        $attemptid = $attempt['objectid'];
        $courseid = $attempt['courseid'];

        $groupingid = get_groupquiz_groupingid($quizid);

        if ($groupingid == null || $groupingid == 0) {
            // If grp_quiz is not enabled do nothing.
        } else {

            $gid = get_user_group_for_groupquiz($userid, $quizid, $courseid);

            // Retrieve grp attempt object.
            $grpattempt = $DB->get_record('quiz_group_attempts', array('groupid' => $gid, 'quizid' => $quizid));

            if (!empty($grpattempt)) {
                // Edit grp attempt.
                $grpattempt->attemptid = $attemptid;
                // Save in DB.
                $DB->update_record('quiz_group_attempts',  $grpattempt, false);
            } else {
                // ERROR : Grp attempt not in DB
                // create grp_attempt if not in DB.
                create_groupattempt_from_attempt($attempt, $courseid);
            }
        }

        return true;
    }

    /**
     * Event processor - attempt deleted
     * delete the group attempt record in group quiz table
     *
     * @param \mod_quiz\event\attempt_started $event
     * @return bool
     */
    public static function attempt_deleted(core\event\base $event) {
        global $DB;

        $attempt = $event->get_data();
        $quizid = $attempt['other']['quizid'];
        $userid = $attempt['relateduserid'];

        // Attempt can be null in grp_attempt if attempt never submitted by user
        // better to retreive attempt via quizid and userid.

        // Delete record in DB.
        $DB->delete_records('quiz_group_attempts', array('quizid' => $quizid, 'userid' => $userid));

        return true;
    }

    /**
     * Event processor - attempt abandoned
     * delete the group attempt record in group quiz table
     *
     * @param \mod_quiz\event\attempt_abandoned $event
     * @return bool
     */
    public static function attempt_abandoned(core\event\base $event) {
        global $DB;

        $attempt = $event->get_data();
        $quizid = $attempt['other']['quizid'];
        $userid = $attempt['other']['userid'];

        // Delete record in DB for group in quiz.
        $DB->delete_records('quiz_group_attempts', array('quizid' => $quizid, 'userid' => $userid));

        return true;
    }



    /**
     * Flag whether a course reset is in progress or not.
     *
     * @var int The course ID.
     */
    protected static $resetinprogress = false;

    /**
     * A course reset has started.
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function course_reset_started($event) {
        self::$resetinprogress = $event->courseid;
    }

    /**
     * A course reset has ended.
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function course_reset_ended($event) {
        if (!empty(self::$resetinprogress)) {
            if (!empty($event->other['reset_options']['reset_groups_remove'])) {
                quiz_process_grp_deleted_in_course($event->courseid);
            }

            if ($event->other['reset_options']['reset_gradebook_grades'] || $event->other['reset_options']['reset_quiz_attempts']) {
                quiz_process_delete_group_attempts($event->courseid);
            }
        }

        self::$resetinprogress = null;
    }

    /**
     * A group was deleted.
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function group_deleted($event) {
        if (!empty(self::$resetinprogress)) {
            // We will take care of that once the course reset ends.
            return;
        }
        quiz_process_grp_deleted_in_course($event->courseid);
    }

}
