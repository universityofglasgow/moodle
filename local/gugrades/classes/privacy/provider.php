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
 * local_gugrades privacy provider
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\{writer, transform, helper, contextlist, approved_contextlist, approved_userlist, userlist};
use stdClass;

class provider implements
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\metadata\provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {

        $collection->add_database_table(
            'local_gugrades_audit',
            [
                'courseid' => 'privacy:metadata:courseid',
                'userid' => 'privacy:metadata:userid',
                'relateduserid' => 'privacy:metadata:relateduserid',
                'gradeitemid' => 'privacy:metadata:gradeitemid',
                'timecreated' => 'privacy:metadata:timecreated',
                'message' => 'privacy:metadata:message',
            ],
            'privacy:metadata:local_gugrades_audit'
        );

        $collection->add_database_table(
            'local_gugrades_grade',
            [
                'courseid' => 'privacy:metadata:courseid',
                'userid' => 'privacy:metadata:userid',
                'gradeitemid' => 'privacy:metadata:gradeitemid',
                'points' => 'privacy:metadata:points',
                'rawgrade' => 'privacy:metadata:rawgrade',
                'admingrade' => 'privacy:metadata:admingrade',
                'displaygrade' => 'privacy:metadata:displaygrade',
                'gradetype' => 'privacy:metadata:gradetype',
                'iscurrent' => 'privacy:metadata:iscurrent',
                'auditby' => 'privacy:metadata:auditby',
                'auditcomment' => 'privacy:metadata:auditcomment',
            ],
            'privacy:metadata:local_gugrades_grades'
        );

        $collection->add_database_table(
            'local_gugrades_hidden',
            [
                'courseid' => 'privacy:metadata:courseid',
                'userid' => 'privacy:metadata:userid',
                'gradeitemid' => 'privacy:metadata:gradeitemid',
            ],
            'privacy:metadata:local_gugrades:hidden'
        );

        $collection->add_database_table(
            'local_gugrades_resitrequired',
            [
                'courseid' => 'privacy:metadata:courseid',
                'userid' => 'privacy:metadata:userid',
            ],
            'privacy:metadata:local_gugrades_resitrequired'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        // All course contexts for which this user has any data
        $sql = "SELECT DISTINCT c.id FROM {context} c
                    JOIN {local_gugrades_grade} ggg ON ggg.courseid = c.instanceid
                    WHERE ggg.userid = :userid
                    AND c.contextlevel = :contextlevel";
        $params = [
            'userid' => $userid,
            'contextlevel' => \CONTEXT_COURSE,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof \context_course) {
            return;
        }

        $sql = "SELECT DISTINCT ggg.userid FROM {local_gugrades_grade} ggg
                    JOIN {context} c ON ggg.courseid = c.instanceid AND c.contextlevel = :contextlevel
                    WHERE c.id = :contextid";
        $params = [
            'contextlevel' => \CONTEXT_COURSE,
            'contextid' => $context->id,
        ];

        $userlist->add_from_sql('userid', $sql, $params);

        return $userlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = (int)$contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_course) {
                return;
            }

            $courseid = $context->instanceid;

            // local_gugrades_audit
            $records = $DB->get_records('local_gugrades_audit', ['relateduserid' => $userid, 'courseid' => $courseid]);
            foreach ($records as $record) {
                writer::with_context($context)
                    ->export_data([], $record);
            }

            // local_gugrades_grade
            $records = $DB->get_records('local_gugrades_grade', ['userid' => $userid, 'courseid' => $courseid]);
            foreach ($records as $record) {
                writer::with_context($context)
                    ->export_data([], $record);
            }

            // local_gugrades_hidden
            $records = $DB->get_records('local_gugrades_hidden', ['userid' => $userid, 'courseid' => $courseid]);
            foreach ($records as $record) {
                writer::with_context($context)
                    ->export_data([], $record);
            }

            // local_gugrades_resitrequired
            $records = $DB->get_records('local_gugrades_resitrequired', ['userid' => $userid, 'courseid' => $courseid]);
            foreach ($records as $record) {
                writer::with_context($context)
                    ->export_data([], $record);
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_course) {
            return;
        }

        $courseid = $context->instanceid;
        $params = [
            'courseid' => $courseid,
        ];

        $DB->delete_records('local_gugrades_audit', $params);
        $DB->delete_records('local_gugrades_grade', $params);
        $DB->delete_records('local_gugrades_hidden', $params);
        $DB->delete_records('local_gugrades_resitrequired', $params);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = (int)$contextlist->get_user()->id;
        foreach ($contextlist as $context) {
            if (!$context instanceof \context_course) {
                continue;
            }

            $courseid = $context->instanceid;
            $params = ['userid' => $userid, 'courseid' => $courseid];

            $DB->delete_records('local_gugrades_audit', $params);
            $DB->delete_records('local_gugrades_grade', $params);
            $DB->delete_records('local_gugrades_hidden', $params);
            $DB->delete_records('local_gugrades_resitrequired', $params);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_course) {
            return;
        }

        // Prepare SQL to gather all completed IDs.
        $userids = $userlist->get_userids();
        $courseid = $context->instanceid;

        foreach ($userids as $userid) {
            $params = ['userid' => $userid, 'courseid' => $courseid];

            $DB->delete_records('local_gugrades_audit', $params);
            $DB->delete_records('local_gugrades_grade', $params);
            $DB->delete_records('local_gugrades_hidden', $params);
            $DB->delete_records('local_gugrades_resitrequired', $params);
        }

    }
}