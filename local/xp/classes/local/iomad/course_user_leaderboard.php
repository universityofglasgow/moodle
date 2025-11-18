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
 * Course user leaderboard.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\iomad;

use moodle_database;
use block_xp\local\leaderboard\ranker;
use block_xp\local\xp\levels_info;

/**
 * Course user leaderboard.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_user_leaderboard extends \local_xp\local\leaderboard\course_user_leaderboard {

    /** @var int The company ID. */
    protected $companyid;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param levels_info $levelsinfo The levels info.
     * @param int $courseid The course ID.
     * @param string[] $columns The name of the columns.
     * @param ranker|null $ranker An alternative ranker.
     * @param int $groupid The group ID.
     * @param Closure $userstatefactory The user state factory.
     * @param int $companyid The company ID.
     */
    public function __construct(
        moodle_database $db,
        levels_info $levelsinfo,
        $courseid,
        array $columns,
        ?ranker $ranker = null,
        $groupid = 0,
        $userstatefactory = null,
        $companyid = 0
    ) {

        parent::__construct($db, $levelsinfo, $courseid, $columns, $ranker, $groupid, $userstatefactory);

        $this->companyid = (int) $companyid;
    }

    protected function get_user_ids_sql($useridalias) {
        [$psql, $pparams] = parent::get_user_ids_sql($useridalias);

        $sql = "$useridalias IN (SELECT userid
                                   FROM {company_users}
                                  WHERE companyid = :iomadcompanyid)";
        $params = ['iomadcompanyid' => $this->companyid];

        $finalsql = '(' . implode(') AND (', [$psql, $sql]) . ')';
        $finalparams = $pparams + $params;

        return [$finalsql, $finalparams];
    }

}
