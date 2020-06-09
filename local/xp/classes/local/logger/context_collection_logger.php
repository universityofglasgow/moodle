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
 * Collection logger.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\logger;
defined('MOODLE_INTERNAL') || die();

use context;
use DateTime;
use moodle_database;
use block_xp\local\activity\user_recent_activity_repository;
use block_xp\local\activity\xp_activity;
use block_xp\local\reason\reason;
use block_xp\local\logger\collection_logger_with_group_reset;
use block_xp\local\logger\reason_collection_logger;
use local_xp\local\reason\reason_with_short_description;
use local_xp\local\reason\maker_from_type_and_signature;
/**
 * Collection logger.
 *
 * For faster 'has happened before' lookups, we use serialise a reason
 * into a hash. The hash only includes reason information, not user or points.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class context_collection_logger implements
        reason_collection_logger,
        collection_logger_with_group_reset,
        collection_counts_indicator,
        reason_collection_counts_indicator,
        reason_occurance_indicator,
        user_recent_activity_repository
    {

    protected $db;
    protected $table = 'local_xp_log';
    protected $context;
    protected $reasonmaker;

    public function __construct(moodle_database $db, context $context, maker_from_type_and_signature $reasonmaker) {
        $this->db = $db;
        $this->context = $context;
        $this->reasonmaker = $reasonmaker;
    }

    public function count_collections_since($userid, DateTime $since) {
        return $this->db->count_records_select(
            $this->table,
            'contextid = :contextid AND time >= :time AND userid = :userid',
            [
                'contextid' => $this->context->id,
                'userid' => $userid,
                'time' => $since->getTimestamp(),
            ]
        );
    }

    public function count_collections_with_reason_since($id, reason $reason, DateTime $since) {
        return $this->db->count_records_select(
            $this->table,
            'contextid = :contextid AND time >= :time AND hashkey = :hashkey AND userid = :userid',
            [
                'contextid' => $this->context->id,
                'userid' => $userid,
                'time' => $since->getTimestamp(),
                'hashkey' => $this->make_hash_key($reason),
            ]
        );
    }

    public function delete_older_than(DateTime $dt) {
        $this->db->delete_records_select(
            $this->table,
            'contextid = :contextid AND time < :time',
            [
                'contextid' => $this->context->id,
                'time' => $dt->getTimestamp()
            ]
        );
    }

    public function get_collected_points_since($userid, DateTime $since) {
        return $this->db->get_field_select(
            $this->table,
            'COALESCE(SUM(points), 0)',
            'contextid = :contextid AND time >= :time AND userid = :userid',
            [
                'contextid' => $this->context->id,
                'userid' => $userid,
                'time' => $since->getTimestamp(),
            ]
        );
    }

    public function get_points_collected_with_reason_since($userid, reason $reason, DateTime $since) {
        return $this->db->get_field_select(
            $this->table,
            'COALESCE(SUM(points), 0)',
            'contextid = :contextid AND time >= :time AND hashkey = :hashkey AND userid = :userid',
            [
                'contextid' => $this->context->id,
                'userid' => $userid,
                'time' => $since->getTimestamp(),
                'hashkey' => $this->make_hash_key($reason),
            ]
        );
    }

    public function get_user_recent_activity($userid, $count = 0) {
        $results = $this->db->get_records_select($this->table, 'contextid = :contextid AND userid = :userid AND points > 0', [
            'contextid' => $this->context->id,
            'userid' => $userid,
        ], 'time DESC, id DESC', '*', 0, $count);

        return array_map(function($row) {
            $reason = $this->reasonmaker->make_from_type_and_signature($row->type, $row->signature);

            $desc = '';
            if ($reason instanceof reason_with_short_description) {
                $desc = $reason->get_short_description();
            }

            return new xp_activity(
                new DateTime('@' . $row->time),
                $desc,
                $row->points
            );

        }, $results);
    }

    public function has_reason_happened_since($userid, reason $reason, DateTime $since) {
        return $this->db->record_exists_select(
            $this->table,
            'contextid = :contextid AND time >= :time AND hashkey = :hashkey AND userid = :userid',
            [
                'contextid' => $this->context->id,
                'userid' => $userid,
                'time' => $since->getTimestamp(),
                'hashkey' => $this->make_hash_key($reason),
            ]
        );
    }

    public function log($userid, $points, $signature, DateTime $time = null) {
        $record = (object) [
            'contextid' => $this->context->id,
            'userid' => $userid,
            'type' => '?',
            'signature' => $signature,
            'points' => $points,
            'time' => $time ? $time->getTimestamp() : time(),
        ];
        $this->db->insert_record($this->table, $record);
    }

    /**
     * Log a thing.
     *
     * @param int $id The target.
     * @param int $points The points.
     * @param reason $reason The reason.
     * @param DateTime|null $time When that happened.
     * @return void
     */
    public function log_reason($id, $points, reason $reason, DateTime $time = null) {
        $record = (object) [
            'contextid' => $this->context->id,
            'userid' => $id,
            'type' => $reason->get_type(),
            'signature' => $reason->get_signature(),
            'points' => $points,
            'time' => $time ? $time->getTimestamp() : time(),
            'hashkey' => $this->make_hash_key($reason)
        ];
        $this->db->insert_record($this->table, $record);
    }

    /**
     * Make a hash key.
     *
     * @param reason $reason The reason.
     * @return string
     */
    protected function make_hash_key(reason $reason) {
        return sha1($reason->get_type() . ':' . $reason->get_signature());
    }

    /**
     * Purge all logs.
     *
     * @return void
     */
    public function reset() {
        $this->db->delete_records(
            $this->table,
            [
                'contextid' => $this->context->id,
            ]
        );
    }

    /**
     * Purge logs for users in a group.
     *
     * @param int $groupid The group ID.
     * @return void
     */
    public function reset_by_group($groupid) {
        $sql = "DELETE
                  FROM {{$this->table}}
                 WHERE contextid = :contextid
                   AND userid IN
               (SELECT gm.userid
                  FROM {groups_members} gm
                 WHERE gm.groupid = :groupid)";

        $params = [
            'contextid' => $this->context->id,
            'groupid' => $groupid
        ];

        $this->db->execute($sql, $params);
    }

}
