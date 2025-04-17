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

/**
 * Service.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface service {

    /**
     * Add participant.
     *
     * @param int $userid
     */
    public function add_participant(int $userid): void;

    /**
     * Get state.
     *
     * @param int $userid
     * @return state
     */
    public function get_state(int $userid): state;

    /**
     * Remove participant.
     *
     * @param int $userid
     */
    public function remove_participant(int $userid): void;

    /**
     * Lock state.
     *
     * @param int $userid
     * @param \DateTimeImmutable|null $dt
     */
    public function lock_state(int $userid, ?\DateTimeImmutable $dt): void;

    /**
     * Reset all states.
     */
    public function reset_states(): void;

}
