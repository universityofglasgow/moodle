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
 * Cohort leaderboard.
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
use block_xp\local\xp\levels_info;
use local_xp\local\xp\levelless_cohort_state;

/**
 * Cohort leaderboard.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cohort_leaderboard extends grouped_leaderboard {

    /** @var array The cohort static cache. */
    protected $cohortcache;
    /** @var int[] Cohort IDs. */
    protected $cohortids;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param int $courseid The course ID.
     * @param int[] $cohortids The cohort IDs.
     * @param array $columns The columns.
     * @param levels_info $levelsinfo The levels info.
     * @param int $orderby An orderby constant.
     */
    public function __construct(moodle_database $db, $courseid, array $cohortids, array $columns, levels_info $levelsinfo,
            $orderby = self::ORDER_BY_POINTS) {
        $ultimatexp = $levelsinfo->get_level($levelsinfo->get_count())->get_xp_required();
        $this->cohortids = $cohortids;
        parent::__construct($db, $courseid, $columns, $cohortids, $ultimatexp, $orderby);
    }

    /**
     * Get a cohort.
     *
     * @param int $id The ID.
     * @return stdClass
     */
    protected function get_cohort($id) {
        if ($this->cohortcache === null) {
            $sql = '';
            $params = [];
            if (!empty($this->cohortids)) {
                list($sql, $params) = $this->db->get_in_or_equal($this->cohortids);
            }
            $this->cohortcache = $this->db->get_records_select('cohort', $sql, $params);
        }
        return $this->cohortcache[$id];
    }

    /**
     * Get the team join.
     *
     * @return \core\dml\sql_join
     */
    protected function get_team_join() {
        $joins = "JOIN {cohort_members} tm
                    ON tm.userid = x.userid
                  JOIN {cohort} t
                    ON t.id = tm.cohortid";
        return new \core\dml\sql_join($joins, 't.visible = 1');
    }

    /**
     * Get team table.
     *
     * @return string
     */
    protected function get_team_table() {
        return 'cohort';
    }

    /**
     * Make a state from the record.
     *
     * @param stdClass $record The row.
     * @return state
     */
    protected function make_state_from_record(stdClass $record) {
        $xp = !empty($record->xp) ? $record->xp : 0;
        return new levelless_cohort_state($this->get_cohort($record->id), $xp, $this->ultimatexp * $record->membercount);
    }
}
