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
 * Course group leaderboard.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\leaderboard;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\utils\external_utils;
use block_xp\local\xp\levels_info;
use context_course;
use local_xp\local\team\static_team;
use local_xp\local\xp\levelless_group_state;
use moodle_database;
use stdClass;

/**
 * Course group leaderboard.
 *
 * Supports selecting the groups by using the default grouping of the course.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_group_leaderboard extends grouped_leaderboard {

    /** @var array The group static cache. */
    protected $groupcache;
    /** @var int The grouping ID to use. 0 for none. */
    protected $groupingid = 0;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param int $courseid The course ID.
     * @param array $columns The columns.
     * @param levels_info $levelsinfo The levels info.
     * @param int $orderby An orderby constant.
     */
    public function __construct(moodle_database $db, $courseid, $columns, levels_info $levelsinfo,
            $orderby = self::ORDER_BY_POINTS) {

        $ultimatexp = $levelsinfo->get_level($levelsinfo->get_count())->get_xp_required();
        $this->groupingid = (int) $db->get_field('course', 'defaultgroupingid', ['id' => $courseid]);

        parent::__construct($db, $courseid, $columns, [], $ultimatexp, $orderby);
    }

    /**
     * Prepare SQL.
     *
     * @return void
     */
    protected function prepare_sql() {

        // Update the team IDs by handling the grouping isolation.
        if (!empty($this->groupingid)) {
            $this->teamids = $this->get_group_ids();
        }

        parent::prepare_sql();
    }

    /**
     * Get a group.
     *
     * @param int $id The ID.
     * @return stdClass
     */
    protected function get_group($id) {
        $groups = $this->get_groups();
        return $groups[$id];
    }

    /**
     * Get group IDs.
     *
     * @return array
     */
    protected function get_group_ids() {
        $groups = $this->get_groups();
        return array_keys($groups);
    }

    /**
     * Get groups.
     *
     * @return array
     */
    protected function get_groups() {
        if ($this->groupcache === null) {
            $this->groupcache = groups_get_all_groups($this->courseid, 0, $this->groupingid);
        }
        return $this->groupcache;
    }

    /**
     * Get the team join.
     *
     * @return \local_xp\local\sql\join
     */
    protected function get_team_join() {
        $joins = "JOIN {groups} t
                    ON t.courseid = x.courseid
                  JOIN {groups_members} tm
                    ON tm.userid = x.userid
                   AND tm.groupid = t.id";
        return new \local_xp\local\sql\join($joins);
    }

    /**
     * Get team table.
     *
     * @return string
     */
    protected function get_team_table() {
        return 'groups';
    }

    /**
     * Get the teams of a member.
     *
     * @param int $memberid The member ID.
     * @return \local_xp\local\team\team[] The teams.
     */
    public function get_teams_of_member($memberid) {
        $groupids = [];
        if (!empty($this->groupingid)) {
            $groupids = $this->get_group_ids();
        }

        $insql = ' != 0';
        $inparams = [];
        if (!empty($groupids)) {
            list($insql, $inparams) = $this->db->get_in_or_equal($groupids, SQL_PARAMS_NAMED);
        }

        $sql = "SELECT t.*
                  FROM {groups} t
                  JOIN {groups_members} tm
                    ON t.id = tm.groupid
                 WHERE t.courseid = :courseid
                   AND tm.userid = :userid
                   AND t.id $insql
              ORDER BY t.name";
        $params = array_merge(['courseid' => $this->courseid, 'userid' => $memberid], $inparams);

        return array_map(function($group) {
            $context = context_course::instance($group->courseid);
            return new static_team($group->id, external_utils::format_string($group->name, $context->id));
        }, $this->db->get_records_sql($sql, $params));
    }

    /**
     * Make a state from the record.
     *
     * @param stdClass $record The row.
     * @return state
     */
    protected function make_state_from_record(stdClass $record) {
        $xp = !empty($record->xp) ? $record->xp : 0;
        return new levelless_group_state($this->get_group($record->id), $xp, $this->ultimatexp * $record->membercount);
    }
}
