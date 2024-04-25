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
 * Privacy Subsystem implementation for enrol_gudatabase
 *
 * @package    local_coreht
 * @copyright  2018 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_corehr\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for local_corehr
 *
 * @copyright  2018 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider,
                          \core_privacy\local\request\plugin\provider,
                          \core_privacy\local\request\core_userlist_provider {

    /**
     * Indicate that user course data is stored in local db table
     * @param collection $collecton
     * @return  collection
     */
    public static function get_metadata(collection $collection) : collection {

        $collection->add_database_table(
            'local_corehr_log',
            [
                'userid' => 'privacy:metadata:local_corehr_log:userid',
                'courseid' => 'privacy:metadata:local_corehr_log:courseid',
                'personnelno' => 'privacy:metadata:local_corehr_log:personnelno',
                'coursecode' => 'privacy:metadata:local_corehr_log:coursecode',
                'enddate' => 'privacy:metadata:local_corehr_log:enddate',
                'wsstatus' => 'privacy:metadata:local_corehr_log:wsstatus'
            ],
            'privacy:metadata:local_corehr_log'
        );

        $collection->add_database_table(
            'local_corehr_extract',
            [
                'userid' => 'privacy:metadata:local_corehr_extract:userid',
                'college' => 'privacy:metadata:local_corehr_extract:college',
                'collegedesc' => 'privacy:metadata:local_corehr_extract:collegedesc',
                'costcentre' => 'privacy:metadata:local_corehr_extract:costcentre',
                'costcentredesc' => 'privacy:metadata:local_corehr_extract:costcentredesc',
                'title' => 'privacy:metadata:local_corehr_extract:title',
                'forename' => 'privacy:metadata:local_corehr_extract:forename',
                'middlename' => 'privacy:metadata:local_corehr_extract:middlename',
                'surname' => 'privacy:metadata:local_corehr_extract:surname',
                'knownas' => 'privacy:metadata:local_corehr_extract:knownas',
                'orgunitno' => 'privacy:metadata:local_corehr_extract:orgunitno',
                'orgunitdesc' => 'privacy:metadata:local_corehr_extract:orgunitdesc',
                'school' => 'privacy:metadata:local_corehr_extract:school',
                'schooldesc' => 'privacy:metadata:local_corehr_extract:schooldesc',
                'jobtitle' => 'privacy:metadata:local_corehr_extract:jobtitle',
                'jobtitledesc' => 'privacy:metadata:local_corehr_extract:jobtitledesc',
            ],
            'privacy:metadata:local_corehr_extract'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     * I *think* this means the course contexts for the given user but the documentation
     * is useless so I don't know.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        $sql = "SELECT c.id
            FROM {context} c
            JOIN {course} co ON co.id = c.instanceid
            JOIN {local_corehr_log} lcl ON lcl.courseid = co.id
            WHERE c.contextlevel = :contextlevel
            AND lcl.userid = :userid
        ";

        $params = [
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        $sql = "SELECT c.id
            FROM {context} c
            JOIN {local_corehr_extract} lce ON lce.userid = c.instanceid
            WHERE c.contextlevel = :contextlevel
            AND lce.userid = :userid
        ";
        $params = [
            'contextlevel' => CONTEXT_USER,
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
    public static function get_users_in_context(userlist $userlist) { }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) { }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;       

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "SELECT lcl.id AS id, co.id AS courseid, co.fullname AS fullname, lcl.personnelno AS personnelno, lcl.coursecode AS coursecode, lcl.enddate AS enddate, lcl.wsstatus AS wsstatus
            FROM {context} c
            JOIN {course} co ON co.id = c.instanceid
            JOIN {local_corehr_log} lcl ON lcl.courseid = co.id
            WHERE c.id $contextsql
            AND lcl.userid = :userid
            AND c.contextlevel = :contextlevel
        ";
   
        $params = [
            'userid' => $user->id,
            'contextlevel' => CONTEXT_COURSE
        ] + $contextparams; 

        $logs = $DB->get_records_sql($sql, $params);

        // Export the data 
        foreach ($logs as $log) {
            if ($log->wsstatus != 'OK') {
                $log->wsstatus = 'ERROR';
            } 
            $context = \context_course::instance($log->courseid);
            $contextdata = helper::get_context_data($context, $user);

            // Create data to export
            $logdata = [
                'personnelno' => $log->personnelno,
                'coursecode' => $log->coursecode,
                'enddate' => $log->enddate,
                'wsstatus' => $log->wsstatus,
            ];

            // Merge data
            $contextdata = (object)array_merge((array)$contextdata, $logdata);
            writer::with_context($context)->export_data(['CoreHR'], $contextdata);   
        }

        // Get extract stuff
        $sql = "SELECT lce.* FROM {local_corehr_extract} lce
            JOIN {context} c ON c.instanceid = lce.userid
            WHERE c.id $contextsql
            AND lce.userid = :userid
            AND c.contextlevel = :contextlevel
        ";
        $params['contextlevel'] = CONTEXT_USER;

        $extracts = $DB->get_records_sql($sql, $params);
        foreach ($extracts as $extract) {
            $context = \context_user::instance($extract->userid);
            $contextdata = helper::get_context_data($context, $user);

            $contextdata = (object)array_merge((array)contextdata, (array)$extract);
            writer::with_context($context)->export_data(['CoreHR'], $contextdata);
        }

    }

    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
 
        if ($context->contextlevel == CONTEXT_COURSE) {
 
            // Delete all entries in the enrolment codes table for this context
            $courseid = $context->instanceid;
            $DB->delete_records('local_corehr_log', ['courseid' => $courseid]);
        }

        if ($context->contextlevel == CONTEXT_USER) {
            $userid = $context->instanceid;
            $DB->delete_record('local_corehr_extract', ['userid' => $userid]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
 
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                $courseid = $context->instanceid;
                $DB->delete_records('local_corehr_log', ['courseid' => $courseid, 'userid' => $userid]);
            }  
            if ($context->contextlevel == CONTEXT_USER) {
                $userid = $context->instanceid;
                $DB->delete_record('local_corehr_extract', ['userid' => $userid]);
            }  
        }
    }

}
