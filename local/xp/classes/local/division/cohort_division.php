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

namespace local_xp\local\division;

use block_xp\local\division\division;
use block_xp\local\userfilter\user_filter;
use local_xp\local\userfilter\cohort_members;

/**
 * Division.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cohort_division implements division {

    /** @var int The cohort ID. */
    protected $cohortid;

    /**
     * Constructor.
     *
     * @param int $cohortid
     */
    public function __construct(int $cohortid) {
        $this->cohortid = (int) $cohortid;
    }

    public function get_id() {
        return $this->cohortid;
    }

    public function get_name(): string {
        return '';
    }

    public function get_user_filter(): user_filter {
        return new cohort_members([$this->cohortid]);
    }

}
