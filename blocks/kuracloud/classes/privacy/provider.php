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
 * kuraCloud privacy provider.
 *
 * Implement support for describing and managing private data.
 *
 * @package    block_kuracloud
 * @category   privacy
 * @copyright  2020 KuraCloud
 * @author     John Gee
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kuracloud\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\userlist;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\approved_userlist;


/**
 * Delete user data for target user which is no longer associated with a valid course.
 *
 * @param  int $userid The target used.
 */
function kuracloud_delete_stale_data_for_user(int $userid) {
    global $DB;

    // Get list of stale records, which are no longer associated with a course.
    $sql = "SELECT kc_users.id
              FROM {block_kuracloud_users} kc_users
         LEFT JOIN {block_kuracloud_courses} kc_courses on kc_users.remote_instanceid = kc_courses.remote_instanceid AND kc_users.remote_courseid = kc_courses.remote_courseid
         LEFT JOIN {course} c on c.id = kc_courses.courseid
             WHERE c.id IS NULL AND kc_users.userid = :userid
    ";
    $params = [
        'userid' => $userid,
    ];
    $ids = $DB->get_fieldset_sql($sql, $params);

    if (!empty($ids)) {
        list($idinsql, $idinparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
        // Putting the userid in again for extra safety.
        $params = array_merge(['userid' => $userid], $idinparams);
        $sql = "userid = :userid AND id {$idinsql}";
        $DB->delete_records_select('block_kuracloud_users', $sql, $params);
    }
}


class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe the user data being stored and exported.
     *
     * @param   collection    $collection
     * @return  collection
     */
    public static function get_metadata(collection $collection) : collection {
        // Describe the data we are storing locally.
        $collection->add_database_table(
            'block_kuracloud_users',
            [
                'userid' => 'privacy:metadata:block_kuracloud_users:userid',
                'remote_studentid' => 'privacy:metadata:block_kuracloud_users:remote_studentid',

            ],
            'privacy:metadata:block_kuracloud_users'
        );

        // Describe the data exported to kuraCloud.
        $collection->add_external_location_link('kuracloud_sync', [
            'firstname' => 'privacy:metadata:kuracloud_sync:firstname',
            'lastname' => 'privacy:metadata:kuracloud_sync:lastname',
            'idnumber' => 'privacy:metadata:kuracloud_sync:idnumber',
            'email' => 'privacy:metadata:kuracloud_sync:email',
        ], 'privacy:metadata:kuracloud_sync');

        return $collection;
    }


    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        global $DB;
        $contextlist = new contextlist();

        // Purge stale data no longer associated wth a course. This is a side-affect, but convenient time as about to export or delete so clean up first.
        kuracloud_delete_stale_data_for_user($userid);

        // Add the contexts for this user still associated with courses.
        $sql = "SELECT context.id
                  FROM {context} context
            INNER JOIN {course} course on course.id = context.instanceid AND (context.contextlevel = :contextlevel)
            INNER JOIN {block_kuracloud_courses} kc_courses on kc_courses.courseid = course.id
            INNER JOIN {block_kuracloud_users} kc_users on kc_users.remote_instanceid = kc_courses.remote_instanceid AND kc_users.remote_courseid = kc_courses.remote_courseid
                 WHERE kc_users.userid = :userid
        ";
        $params = [
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }


    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $sql = "SELECT kc_users.userid
                  FROM {block_kuracloud_courses} kc_courses
            INNER JOIN {block_kuracloud_users} kc_users on kc_users.remote_instanceid = kc_courses.remote_instanceid AND kc_users.remote_courseid = kc_courses.remote_courseid
                 WHERE kc_courses.courseid = :courseid
        ";
        $params = [
            'courseid' => $context->instanceid,
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }


    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        global $DB;

        // Get a map from courseid to the user PII.
        $sql = "SELECT kc_courses.courseid, kc_users.userid, kc_users.remote_studentid
                  FROM {block_kuracloud_courses} kc_courses
            INNER JOIN {block_kuracloud_users} kc_users on kc_users.remote_instanceid = kc_courses.remote_instanceid AND kc_users.remote_courseid = kc_courses.remote_courseid
                 WHERE kc_users.userid = :userid
        ";
        $params = [
            'userid' => $userid,
        ];
        $details = $DB->get_records_sql($sql, $params);

        // Export the course related user information.
        foreach ($contextlist->get_contexts() as $context) {
            $courseid = $context->instanceid; // Tentative, as also need to check contextlevel.
            if ($context->contextlevel === CONTEXT_COURSE && array_key_exists($courseid, $details)) {
                $data = new \stdClass();
                $data->moodleuserid = $details[$courseid]->userid;
                $data->kuracloudstudentid = $details[$courseid]->remote_studentid;
                // studentid is within instance and course, but not much value in adding them to exported data.
                writer::with_context($context)->export_data(['kuraCloud'], $data);
            }
        }
    }


    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        global $DB;
        $mapping = $DB->get_record('block_kuracloud_courses', array('courseid' => $context->instanceid));
        if ($mapping) {
            $DB->delete_records('block_kuracloud_users', array('remote_instanceid' => $mapping->remote_instanceid, 'remote_courseid' => $mapping->remote_courseid));
        }
    }


    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param  approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }

        global $DB;
        $userid = $contextlist->get_user()->id;

        // Get a map from courseid to the related user record.
        $sql = "SELECT kc_courses.courseid, kc_users.id
                  FROM {block_kuracloud_courses} kc_courses
            INNER JOIN {block_kuracloud_users} kc_users on kc_users.remote_instanceid = kc_courses.remote_instanceid AND kc_users.remote_courseid = kc_courses.remote_courseid
                 WHERE kc_users.userid = :userid
        ";
        $params = [
            'userid' => $userid,
        ];
        $details = $DB->get_records_sql($sql, $params);

        foreach ($contextlist->get_contexts() as $context) {
            $courseid = $context->instanceid; // Tentative, as also need to check contextlevel.
            if ($context->contextlevel === CONTEXT_COURSE && array_key_exists($courseid, $details)) {
                $DB->delete_records('block_kuracloud_users', ['id' => $details[$courseid]->id, 'userid' => $userid]);
            }
        }
    }


    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        if (empty($userlist->count())) {
            return;
        }
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        global $DB;
        $mapping = $DB->get_record('block_kuracloud_courses', array('courseid' => $context->instanceid));
        if ($mapping) {
            list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
            $params = array_merge(['remote_instanceid' => $mapping->remote_instanceid, 'remote_courseid' => $mapping->remote_courseid], $userinparams);
            $sql = "remote_instanceid = :remote_instanceid AND remote_courseid = :remote_courseid AND userid {$userinsql}";
            $DB->delete_records_select('block_kuracloud_users', $sql, $params);
        }
    }
}
