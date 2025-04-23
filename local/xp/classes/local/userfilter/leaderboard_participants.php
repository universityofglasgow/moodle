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

namespace local_xp\local\userfilter;

use block_xp\di;
use block_xp\local\userfilter\user_filter;
use local_xp\local\config\default_course_world_config;
use moodle_database;

/**
 * User filter.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class leaderboard_participants implements user_filter {

    /** @var int */
    protected $contextid;
    /** @var moodle_database */
    protected $db;
    /** @var int A LEADERBOARD_PARTICIPATION_* constant. */
    protected $mode;

    /**
     * Constructor.
     *
     * @param array $cohortids The cohort IDs.
     * @param bool $exclude Whether to exclude, instead of include.
     */
    public function __construct(int $contextid, int $mode = default_course_world_config::LEADERBOARD_PARTICIPATION_FORCED) {
        $this->db = di::get('db');
        $this->contextid = $contextid;
        $this->mode = $mode;
    }

    /**
     * Get the SQL fragment to filter users.
     *
     * @return array Containing both SQL fragment, and params.
     */
    public function get_sql(string $useridalias): array {
        if ($this->mode === default_course_world_config::LEADERBOARD_PARTICIPATION_OPTIN) {
            // Explicit opt-in, flag must be present, and set to 1.
            return ["{$useridalias} IN (SELECT userid
                                          FROM {local_xp_user_flag}
                                         WHERE contextid = :lpctxid
                                           AND ladderparticipation = 1)", [
                'lpctxid' => $this->contextid,
            ]];

        } else if ($this->mode === default_course_world_config::LEADERBOARD_PARTICIPATION_OPTOUT) {
            // Optional opt-out, if the flag does not exist either missing or different value, we select.
            return ["{$useridalias} NOT IN (SELECT userid
                                              FROM {local_xp_user_flag}
                                             WHERE contextid = :lpctxid
                                               AND ladderparticipation = 0)", [
                'lpctxid' => $this->contextid,
            ]];
        }

        return ['1=1', []];
    }

}
