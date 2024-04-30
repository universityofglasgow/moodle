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
 * Level-less cohort state.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;

use block_xp\local\utils\external_utils;
use stdClass;

/**
 * Level-less cohort state.
 *
 * Simple implementation where the level is not computed.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levelless_cohort_state extends levelless_state {

    /** @var stdClass The cohort object. */
    protected $cohort;

    /**
     * Constructor.
     *
     * @param stdClass $cohort The cohort object.
     * @param int $xp The cohort XP.
     * @param int|null $totalxp The total amount of XP.
     */
    public function __construct(stdClass $cohort, $xp, $totalxp = null) {
        parent::__construct($xp, $cohort->id, '', $totalxp);
        $this->cohort = $cohort;
    }

    /**
     * Return the cohort object.
     *
     * @return stdClass
     */
    public function get_cohort() {
        return $this->cohort;
    }

    public function get_name() {
        return external_utils::format_string($this->cohort->name, $this->cohort->contextid);
    }

}
