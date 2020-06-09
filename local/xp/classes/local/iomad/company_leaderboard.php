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
 * Company leaderboard.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\iomad;
defined('MOODLE_INTERNAL') || die();

use moodle_database;
use stdClass;
use block_xp\local\xp\levels_info;
use local_xp\local\xp\levelless_state;
use local_xp\local\leaderboard\grouped_leaderboard;

/**
 * Company leaderboard.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class company_leaderboard extends grouped_leaderboard {

    /** @var iomad IOMAD. */
    protected $iomad;
    /** @var array The company static cache. */
    protected $companycache = [];
    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param int $courseid The course ID.
     * @param facade $iomad IOMAD.
     * @param int[] $companyids The company IDs.
     * @param array $columns The columns.
     * @param levels_info $levelsinfo The levels info.
     * @param int $orderby An orderby constant.
     */
    public function __construct(moodle_database $db, $courseid, facade $iomad, array $companyids, array $columns,
            levels_info $levelsinfo, $orderby = self::ORDER_BY_POINTS) {
        $this->iomad = $iomad;
        $ultimatexp = $levelsinfo->get_level($levelsinfo->get_count())->get_xp_required();
        parent::__construct($db, $courseid, $columns, $companyids, $ultimatexp, $orderby);
    }

    /**
     * Get a company.
     *
     * @param int $id The ID.
     * @return company
     */
    protected function get_company_name($id) {
        if (empty($this->companycache[$id])) {
            $this->companycache[$id] = $this->iomad->get_company_name($id);
        }
        return $this->companycache[$id];
    }

    /**
     * Get the team join.
     *
     * @return \core\dml\sql_join
     */
    protected function get_team_join() {
        $joins = "JOIN {company_users} cu
                    ON cu.userid = x.userid
                  JOIN {company} t
                    ON t.id = cu.companyid";
        return new \core\dml\sql_join($joins);
    }

    /**
     * Get team table.
     *
     * @return string
     */
    protected function get_team_table() {
        return 'company';
    }

    /**
     * Make a state from the record.
     *
     * @param stdClass $record The row.
     * @return state
     */
    protected function make_state_from_record(stdClass $record) {
        $xp = !empty($record->xp) ? $record->xp : 0;
        return new levelless_state($xp, $record->id, $this->get_company_name($record->id),
            $this->ultimatexp * $record->membercount);
    }
}
