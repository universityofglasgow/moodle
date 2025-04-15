<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp\local\leaderboard\participation;

use block_xp\di;
use block_xp\local\world;
use core_date;
use DateTimeImmutable;

/**
 * Service.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class optinout_service implements service {

    /** @var int */
    protected $contextid;
    /** @var bool */
    protected $createstateonjoin;
    /** @var bool */
    protected $nullisparticipant;
    /** @var \moodle_database */
    protected $db;
    /** @var world */
    protected $world;

    /**
     * Constructor.
     *
     * @param int $contextid
     */
    public function __construct(world $world, bool $nullisparticipant = true, bool $createstateonjoin = false) {
        $this->db = di::get('db');
        $this->contextid = $world->get_context()->id;
        $this->world = $world;
        $this->nullisparticipant = $nullisparticipant;
        $this->createstateonjoin = $createstateonjoin;
    }

    /**
     * Add participant.
     *
     * @param int $userid
     */
    public function add_participant(int $userid): void {
        $lockedvalue = (new DateTimeImmutable("+4 days", core_date::get_server_timezone_object()))->setTime(0, 0, 0, 0);

        $record = $this->get_record($userid);
        $record->ladderparticipation = 1;
        $record->ladderparticipationlocked = $lockedvalue ? $lockedvalue->getTimestamp() : 0;
        $this->commit_record($record);

        // This operation ensures that the state exists.
        if ($this->createstateonjoin) {
            $this->world->get_store()->increase($userid, 0);
        }
    }

    /**
     * Get state.
     *
     * @return state
     */
    public function get_state(int $userid): state {
        $record = $this->get_record($userid);
        $dt = $record->ladderparticipationlocked ? (new DateTimeImmutable('@' . $record->ladderparticipationlocked)) : null;
        $nullisparticipant = $this->nullisparticipant;
        $isparticipant = $nullisparticipant && $record->ladderparticipation === null ? true : (bool) $record->ladderparticipation;
        return new state($isparticipant, $dt);
    }

    /**
     * Remove participant.
     *
     * @param int $userid
     */
    public function remove_participant(int $userid): void {
        $record = $this->get_record($userid);
        $record->ladderparticipation = 0;
        $record->ladderparticipationlocked = 0;
        $this->commit_record($record);
    }

    /**
     * Lock state.
     *
     * @param int $userid
     * @param \DateTimeImmutable|null $dt
     */
    public function lock_state(int $userid, ?\DateTimeImmutable $dt): void {
        $ts = $dt ? $dt->getTimestamp() : 0;
        $this->db->set_field_select(
            'local_xp_user_flag',
            'ladderparticipationlocked',
            $ts,
            'contextid = ? AND userid = ? AND ladderparticipation IS NOT NULL',
            [$this->contextid, $userid]
        );
    }

    /**
     * Reset all states.
     */
    public function reset_states(): void {
        $this->db->execute('UPDATE {local_xp_user_flag}
                               SET ladderparticipation = NULL,
                                   ladderparticipationlocked = 0
                             WHERE contextid = ?', ['contextid' => $this->contextid]);
    }

    /**
     * Commit record.
     *
     * @param \stdClass $record
     */
    protected function commit_record($record) {
        if (empty($record->id)) {
            $this->db->insert_record('local_xp_user_flag', $record);
        } else {
            $this->db->update_record('local_xp_user_flag', $record);
        }
    }

    /**
     * Get the record.
     *
     * @param int $userid
     */
    protected function get_record(int $userid) {
        return $this->db->get_record('local_xp_user_flag', [
            'contextid' => $this->contextid,
            'userid' => $userid,
        ], 'id, userid, ladderparticipation, ladderparticipationlocked', IGNORE_MISSING) ?: (object) [
            'contextid' => $this->contextid,
            'userid' => $userid,
            'ladderparticipation' => null,
            'ladderparticipationlocked' => 0,
        ];
    }
}
