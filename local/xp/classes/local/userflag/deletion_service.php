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

namespace local_xp\local\userflag;

/**
 * Deletion service.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deletion_service {

    /** @var \moodle_database */
    protected $db;

    /**
     * Constructor.
     */
    public function __construct(\moodle_database $db) {
        $this->db = $db;
    }

    /**
     * Delete flags for the user in a context.
     *
     * @param int $userid
     * @param int $contextid
     */
    public function delete_for_user_in_context(int $userid, int $contextid) {
        $this->db->delete_records('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $userid]);
    }

}
