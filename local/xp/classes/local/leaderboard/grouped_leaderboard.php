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
 * Grouped leaderboard.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\leaderboard;
defined('MOODLE_INTERNAL') || die();

use moodle_database;
use stdClass;
use block_xp\local\sql\limit;
use block_xp\local\xp\state_rank;
use local_xp\local\config\default_course_world_config;

/**
 * Grouped leaderboard.
 *
 * An abstract class to help creating grouped leaderboards. Grouped leaderboards are
 * effectively leaderboards of teams vs other teams. Teams' points are computed from
 * the sum of all of its members points. The progress of a team is also computed from
 * the number of members in the team multiplied by the expected amount of points.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class grouped_leaderboard implements \block_xp\local\leaderboard\leaderboard {

    /** Order by points. */
    const ORDER_BY_POINTS = default_course_world_config::GROUP_ORDER_BY_POINTS;
    /** Order by progress. */
    const ORDER_BY_PROGRESS = default_course_world_config::GROUP_ORDER_BY_PROGRESS;
    /** Order by points with compensation using team average. */
    const ORDER_BY_POINTS_COMPENSATED_BY_AVG = default_course_world_config::GROUP_ORDER_BY_POINTS_COMPENSATED_BY_AVG;

    /** @var moodle_database The database. */
    protected $db;
    /** @var int The course ID. */
    protected $courseid;
    /** @var array The columns. */
    protected $columns;
    /** @var string The DB table. */
    protected $table = 'block_xp';
    /** @var int[] The team IDs. */
    protected $teamids;
    /** @var int Points needed for the ultimate level. Required for ORDER_BY_PROGRESS. */
    protected $ultimatexp;
    /** @var int The ordering constant. */
    protected $orderby;

    /** @var string SQL Fragment. */
    protected $fields;
    /** @var string SQL Fragment. */
    protected $from;
    /** @var string SQL Fragment. */
    protected $where;
    /** @var string SQL Fragment. */
    protected $groupby;
    /** @var string SQL Fragment. */
    protected $order;

    /** @var int The highest member count cache. Do not use directly, use self::get_highest_member_count instead. */
    protected $highestmembercountcache;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param int $courseid The course ID.
     * @param int[] $teamids The team IDs to limit the leaderboard to.
     * @param array $columns The columns.
     * @param int $ultimatexp The ultimate XP.
     * @param int $orderby The sort by constant.
     */
    public function __construct(moodle_database $db, $courseid, $columns, $teamids = [], $ultimatexp = null,
            $orderby = self::ORDER_BY_POINTS) {
        $this->db = $db;
        $this->courseid = $courseid;
        $this->columns = $columns;
        $this->teamids = $teamids;
        $this->ultimatexp = $ultimatexp;
        $this->orderby = $orderby;

        $this->prepare_sql();
    }

    /**
     * Prepare the SQL fragments.
     *
     * @return void
     */
    protected function prepare_sql() {
        $teamjoin = $this->get_team_join();

        $this->from = "{{$this->table}} x " . $teamjoin->joins;
        $this->where = "x.courseid = :courseid";
        if (!empty($teamjoin->wheres)) {
            $this->where .= " AND ({$teamjoin->wheres})";
        }
        $this->groupby = "t.id";
        $this->params = array_merge([
            'courseid' => $this->courseid,
        ], $teamjoin->params);

        // Set the team filtering.
        if (!empty($this->teamids)) {
            list($insql, $inparams) = $this->db->get_in_or_equal($this->teamids, SQL_PARAMS_NAMED);
            $this->where .= " AND t.id $insql";
            $this->params = array_merge($this->params, $inparams);
        }

        // Set the fields to fetch. We assume that get_sum_expression and get_member_count_expression
        // do not depend on the ordering, as the ordering should likely depend on the fields themselves.
        $sumexpr = $this->get_sum_expression();
        $membercountexpr = $this->get_member_count_expression();
        $this->fields = "t.id, {$sumexpr} AS xp, {$membercountexpr} AS membercount";

        // Set the order.
        $this->order = "";
        if ($this->ultimatexp && $this->orderby == static::ORDER_BY_PROGRESS) {
            list($progress, $progressparams) = $this->get_progress_sql();
            $this->order .= "$progress DESC, ";
            $this->params = array_merge($this->params, $progressparams);
        }
        $this->order .= "{$sumexpr} DESC, t.id ASC";
    }

    /**
     * Get the leaderboard columns.
     *
     * @return array Where keys are column identifiers and values are lang_string objects.
     */
    public function get_columns() {
        return $this->columns;
    }

    /**
     * Get the number of rows in the leaderboard.
     *
     * @return int
     */
    public function get_count() {
        $table = $this->get_team_table();
        $sql = "SELECT COUNT('x')
                  FROM {{$table}} t2
                 WHERE t2.id IN (
                       SELECT t.id
                         FROM {$this->from}
                        WHERE {$this->where}
                    )";

        return $this->db->count_records_sql($sql, $this->params);
    }

    /**
     * Get the highest member count amongst the groups.
     *
     * Note that this is cached for the instance, thus if group membership changes
     * after this object was initialised, the results may not be accurate.
     *
     * @return int
     */
    protected function get_highest_member_count()   {
        if ($this->highestmembercountcache === null) {
            $sql = "SELECT MAX(q2.membercount) FROM (
                           SELECT COUNT(x.userid) AS membercount
                             FROM {$this->from}
                            WHERE {$this->where}
                         GROUP BY {$this->groupby}) q2";
            $params = $this->params;
            $this->highestmembercountcache = (int) $this->db->get_field_sql($sql, $params);
        }
        return $this->highestmembercountcache;
    }

    /**
     * Get member count expression.
     *
     * @return string
     */
    protected function get_member_count_expression() {
        if ($this->orderby == self::ORDER_BY_POINTS_COMPENSATED_BY_AVG) {
            return $this->get_highest_member_count();
        }
        return 'COUNT(x.userid)';
    }

    /**
     * Get the points of an object.
     *
     * @param int $id The object ID.
     * @return int|false False when not ranked.
     */
    protected function get_points($id) {
        $sumexpr = $this->get_sum_expression();
        $sql = "SELECT {$sumexpr} AS xp
                  FROM {$this->from}
                 WHERE {$this->where}
                   AND (t.id = :teamid)
              GROUP BY {$this->groupby}";
        $params = $this->params + ['teamid' => $id];
        return $this->db->get_field_sql($sql, $params);
    }

    /**
     * Get the progress of an object.
     *
     * @param int $id The object ID.
     * @return int|false False when not ranked.
     */
    protected function get_progress($id) {
        list($progress, $progressparams) = $this->get_progress_sql();
        $sql = "SELECT $progress AS progressratio
                  FROM {$this->from}
                 WHERE {$this->where}
                   AND (t.id = :teamid)
              GROUP BY {$this->groupby}";
        $params = $this->params + $progressparams + [
            'teamid' => $id,
        ];
        return $this->db->get_field_sql($sql, $params);
    }

    /**
     * Get progress SQL fragment.
     *
     * Note that we must round the value because precision may be lost when the
     * value is passed back to PHP, which then causes unexpected results with
     * the ordering in SQL when the same value is reused but was rounded by PHP.
     *
     * @return array With SQL and params.
     */
    protected function get_progress_sql() {
        static $i = 0;
        $param1 = 'ultimatexp' . $i++;
        $param2 = 'ultimatexp' . $i++;
        $progress = "(CASE WHEN COUNT(x.userid) * :$param1 > 0
                           THEN ROUND(SUM(x.xp) / (COUNT(x.userid) * :$param2), 8)
                           ELSE 0
                       END)";
        return [$progress, [$param1 => $this->ultimatexp, $param2 => $this->ultimatexp]];
    }

    /**
     * Return the position of the object.
     *
     * The position is used to determine how to paginate the leaderboard.
     *
     * @param int $id The object ID.
     * @return int Indexed from 0, null when not ranked.
     */
    public function get_position($id) {
        $xp = $this->get_points($id);
        if ($xp === false) {
            return null;
        }
        return $this->get_position_number($id, $xp);
    }

    /**
     * Get the position number.
     *
     * @param int $id The object ID.
     * @param int $xp The amount of XP.
     * @return int
     */
    protected function get_position_number($id, $xp) {
        if ($this->orderby == static::ORDER_BY_PROGRESS) {
            $progress = $this->get_progress($id);
            return $this->get_position_with_xp_and_progress($id, $xp, $progress);
        }
        return $this->get_position_with_xp($id, $xp);
    }

    /**
     * Get position based on ID and XP.
     *
     * @param int $id The object ID.
     * @param int $xp The amount of XP.
     * @return int Indexed from 0.
     */
    protected function get_position_with_xp($id, $xp) {
        $sumexpr = $this->get_sum_expression();
        $sql = "SELECT COUNT('x')
                  FROM (
                    SELECT t.id
                      FROM {$this->from}
                     WHERE {$this->where}
                  GROUP BY {$this->groupby}
                    HAVING ({$sumexpr} > :posxp
                        OR ({$sumexpr} = :posxpeq AND t.id < :posid))
                       ) countx ";
        $params = $this->params + [
            'posxp' => $xp,
            'posxpeq' => $xp,
            'posid' => $id
        ];
        return $this->db->count_records_sql($sql, $params);
    }

    /**
     * Get position based on ID and progress.
     *
     * @param int $id The object ID.
     * @param int $xp The amount of XP.
     * @param float $progress The percentage of progress.
     * @return int Indexed from 0.
     */
    protected function get_position_with_xp_and_progress($id, $xp, $progress) {
        list($progress1, $progress1params) = $this->get_progress_sql();
        list($progress2, $progress2params) = $this->get_progress_sql();
        list($progress3, $progress3params) = $this->get_progress_sql();
        $sql = "SELECT COUNT('x')
                  FROM (
                    SELECT t.id
                      FROM {$this->from}
                     WHERE {$this->where}
                  GROUP BY {$this->groupby}
                    HAVING ($progress1 > :posprogress)
                        OR ($progress2 = :posprogresseq1 AND SUM(x.xp) > :posxp)
                        OR ($progress3 = :posprogresseq2 AND SUM(x.xp) = :posxpeq AND t.id < :posid)
                       ) countx ";
        $params = $this->params + $progress1params + $progress2params + $progress3params + [
            'posprogress' => (float) $progress,
            'posprogresseq1' => (float) $progress,
            'posprogresseq2' => (float) $progress,
            'posxp' => $xp,
            'posxpeq' => $xp,
            'posid' => $id
        ];
        list($progress4, $progress4params) = $this->get_progress_sql();
        return $this->db->count_records_sql($sql, $params);
    }

    /**
     * Get the rank of an object.
     *
     * @param int $id The object ID.
     * @return rank|null
     */
    public function get_rank($id) {
        $state = $this->get_state($id);
        if (!$state) {
            return null;
        }
        return new state_rank($this->get_rank_number($id, $state->get_xp()), $state);
    }

    /**
     * Get the rank number.
     *
     * @param int $id The object ID.
     * @param int $xp The amount of XP it has.
     * @return int
     */
    protected function get_rank_number($id, $xp) {
        if ($this->orderby == static::ORDER_BY_PROGRESS) {
            return $this->get_rank_from_xp_and_progress($xp, $this->get_progress($id));
        }
        return $this->get_rank_from_xp($xp);
    }

    /**
     * Get the rank of an amount of XP.
     *
     * @param int $xp The xp.
     * @return int Indexed from 1.
     */
    protected function get_rank_from_xp($xp) {
        $sumexpr = $this->get_sum_expression();
        $sql = "SELECT COUNT('x')
                  FROM (
                    SELECT t.id
                      FROM {$this->from}
                     WHERE {$this->where}
                  GROUP BY {$this->groupby}
                    HAVING ({$sumexpr} > :posxp)
                  ) countx";
        return $this->db->count_records_sql($sql, $this->params + ['posxp' => $xp]) + 1;
    }

    /**
     * Get the rank of an amount of XP and progress.
     *
     * @param int $xp The xp.
     * @param float $progress The progress.
     * @return int Indexed from 1.
     */
    protected function get_rank_from_xp_and_progress($xp, $progress) {
        list($progress1, $progress1params) = $this->get_progress_sql();
        list($progress2, $progress2params) = $this->get_progress_sql();
        $sql = "SELECT COUNT('x')
                  FROM (
                    SELECT t.id
                      FROM {$this->from}
                     WHERE {$this->where}
                  GROUP BY {$this->groupby}
                    HAVING ($progress1 > :posprogress)
                        OR ($progress2 = :posprogresseq AND SUM(x.xp) > :posxp)
                  ) countx";
        $params = $this->params + $progress1params + $progress2params + [
            'posprogress' => (float) $progress,
            'posprogresseq' => (float) $progress,
            'posxp' => $xp
        ];
        return $this->db->count_records_sql($sql, $params) + 1;
    }

    /**
     * Get the ranking.
     *
     * @param limit $limit The limit.
     * @return Traversable
     */
    public function get_ranking(limit $limit) {
        $recordset = $this->get_ranking_recordset($limit);

        $rank = null;
        $offset = null;
        $lastxp = null;
        $ranking = [];

        foreach ($recordset as $record) {
            $state = $this->make_state_from_record($record);

            if ($rank === null || $lastxp !== $state->get_xp()) {
                if ($rank === null) {
                    $pos = $this->get_position_number($state->get_id(), $state->get_xp());
                    $rank = $this->get_rank_number($state->get_id(), $state->get_xp());
                    $offset = 1 + ($pos + 1 - $rank);
                } else {
                    $rank += $offset;
                    $offset = 1;
                }
                $lastxp = $state->get_xp();
            } else {
                $offset++;
            }

            $ranking[] = new state_rank($rank, $state);
        }

        $recordset->close();
        return $ranking;
    }

    /**
     * Get ranking recordset.
     *
     * @param limit $limit The limit.
     * @return moodle_recordset
     */
    protected function get_ranking_recordset(limit $limit) {
        $sql = "SELECT {$this->fields}
                  FROM {$this->from}
                 WHERE {$this->where}
              GROUP BY {$this->groupby}
              ORDER BY {$this->order}";
        if ($limit) {
            $recordset = $this->db->get_recordset_sql($sql, $this->params, $limit->get_offset(), $limit->get_count());
        } else {
            $recordset = $this->db->get_recordset_sql($sql, $this->params);
        }
        return $recordset;
    }

    /**
     * Get the state.
     *
     * @param int $id The object ID.
     * @return state|null
     */
    protected function get_state($id) {
        $sql = "SELECT {$this->fields}
                  FROM {$this->from}
                 WHERE {$this->where}
                   AND (t.id = :teamid)
              GROUP BY {$this->groupby}";
        $params = $this->params + ['teamid' => $id];
        $record = $this->db->get_record_sql($sql, $params);
        return !$record ? null : $this->make_state_from_record($record);
    }

    /**
     * Get the sum expression.
     *
     * @return string
     */
    protected function get_sum_expression() {
        if ($this->orderby == self::ORDER_BY_POINTS_COMPENSATED_BY_AVG) {
            $highestcount = $this->get_highest_member_count();
            return "FLOOR(SUM(x.xp) * (1.0 * {$highestcount} / COUNT(x.userid)))";
        }
        return 'SUM(x.xp)';
    }

    /**
     * Get the team join.
     *
     * By convension the team table should be prefixed 't'.
     *
     * @return \core\dml\sql_join
     */
    abstract protected function get_team_join();

    /**
     * Get the team table name.
     *
     * @return string
     */
    abstract protected function get_team_table();

    /**
     * Make a state from the record.
     *
     * @param stdClass $record The row.
     * @return state
     */
    abstract protected function make_state_from_record(stdClass $record);
}
