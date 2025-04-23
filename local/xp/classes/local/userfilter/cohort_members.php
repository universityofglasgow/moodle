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
 * User filter.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\userfilter;

use block_xp\di;
use block_xp\local\userfilter\user_filter;
use moodle_database;

/**
 * User filter.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cohort_members implements user_filter {

    /** @var int[] */
    protected $cohortids;
    /** @var moodle_database */
    protected $db;
    /** @var bool */
    protected $exclude = false;

    /**
     * Constructor.
     *
     * @param array $cohortids The cohort IDs.
     * @param bool $exclude Whether to exclude, instead of include.
     */
    public function __construct(array $cohortids, $exclude = false) {
        $this->db = di::get('db');
        $this->cohortids = $cohortids;
        $this->exclude = $exclude;
    }

    /**
     * Get the SQL fragment to filter users.
     *
     * @return array Containing both SQL fragment, and params.
     */
    public function get_sql(string $useridalias): array {
        if (empty($this->cohortids)) {
            // If we exclude nothing, we return everyone.
            return [$this->exclude ? '1=1' : '1=0', []];
        }
        [$insql, $inparams] = $this->db->get_in_or_equal($this->cohortids, SQL_PARAMS_NAMED);
        $operator = $this->exclude ? 'NOT IN' : 'IN';
        return ["{$useridalias} {$operator} (SELECT userid FROM {cohort_members} WHERE cohortid {$insql})", $inparams];
    }

}
