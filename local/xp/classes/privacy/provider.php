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
 * Data provider.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\privacy;
defined('MOODLE_INTERNAL') || die();

use context;
use context_course;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Data provider class.
 *
 * This provider exports and deletes data from block_xp itself, however it does report on its metadata.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \block_xp\local\privacy\addon_provider,
    \block_xp\local\privacy\addon_userlist_provider {

    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function _get_metadata(collection $collection) {

        $collection->add_database_table('local_xp_log', [
            'userid' => 'privacy:metadata:log:userid',
            'type' => 'privacy:metadata:log:type',
            'signature' => 'privacy:metadata:log:signature',
            'points' => 'privacy:metadata:log:points',
            'time' => 'privacy:metadata:log:time',
        ], 'privacy:metadata:log');

        return $collection;
    }

    /**
     * Add the list of contexts for user.
     *
     * @param contextlist $contextlist The context list.
     * @param int $userid The user to search.
     */
    public static function add_addon_contexts_for_userid(contextlist $contextlist, $userid) {
        $sql = "
            SELECT DISTINCT l.contextid
              FROM {local_xp_log} l
             WHERE l.userid = :userid";
        $params = ['userid' => $userid];
        $contextlist->add_from_sql($sql, $params);
    }

    /**
     * Add the list of users who have data within a context.
     *
     * @param userlist $userlist The user list.
     */
    public static function add_addon_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_xp_log} WHERE contextid = ?', [$context->id]);
    }

    /**
     * Export user preferences.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_addon_user_preferences($userid) {
    }

    /**
     * Export the addon user data.
     *
     * @param array $rootpath The root path to export at.
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_addon_user_data(array $rootpath, approved_contextlist $contextlist) {
        $db = \block_xp\di::get('db');
        $user = $contextlist->get_user();

        list($insql, $inparams) = $db->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        // Fetch the logs.
        $sql = "
            SELECT l.userid, l.type, l.signature, l.points, l.time, l.contextid
              FROM {local_xp_log} l
             WHERE l.contextid $insql
               AND l.userid = :userid
          ORDER BY l.contextid, l.time";
        $params = ['userid' => $user->id] + $inparams;

        $path = array_merge($rootpath, [get_string('privacy:path:logs', 'block_xp')]);
        $flushlogs = function($contextid, $data) use ($path) {
            $context = context::instance_by_id($contextid);
            writer::with_context($context)->export_data($path, (object) ['data' => $data]);
        };

        // Export the logs for each course.
        $reasonmaker = new \local_xp\local\reason\maker_from_type_and_signature();
        $recordset = $db->get_recordset_sql($sql, $params);
        $logs = [];
        $lastcontextid = null;
        foreach ($recordset as $record) {

            if ($lastcontextid && $lastcontextid != $record->contextid) {
                $flushlogs($lastcontextid, $logs);
                $logs = [];
            }

            $reason = $reasonmaker->make_from_type_and_signature($record->type, $record->signature);
            if ($reason instanceof \local_xp\local\reason\reason_with_short_description) {
                $desc = $reason->get_short_description();
            } else {
                $desc = '';
            }

            $logs[] = (object) [
                'description' => $desc,
                'type' => $reason->get_type(),
                'signature' => $reason->get_signature(),
                'time' => transform::datetime($record->time),
                'userid' => transform::user($record->userid),
                'points' => $record->points,
            ];
            $lastcontextid = $record->contextid;
        }

        // Flush the last iteration.
        if ($lastcontextid) {
            $flushlogs($lastcontextid, $logs);
        }

        $recordset->close();
    }

    /**
     * Delete addon data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_addon_data_for_all_users_in_context(context $context) {
        $db = \block_xp\di::get('db');
        $db->delete_records('local_xp_log', ['contextid' => $context->id]);
    }

    /**
     * Delete addon data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_addon_data_for_user(approved_contextlist $contextlist) {
        $db = \block_xp\di::get('db');
        $user = $contextlist->get_user();
        $userid = $user->id;

        list($insql, $inparams) = $db->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "contextid $insql AND userid = :userid";
        $params = ['userid' => $userid] + $inparams;
        $db->delete_records_select('local_xp_log', $sql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The user list.
     */
    public static function delete_addon_data_for_users(approved_userlist $userlist) {
        $db = \block_xp\di::get('db');
        list($insql, $inparams) = $db->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $sql = "contextid = :contextid AND userid $insql";
        $params = ['contextid' => $userlist->get_context()->id] + $inparams;
        $db->delete_records_select('local_xp_log', $sql, $params);
    }

}
