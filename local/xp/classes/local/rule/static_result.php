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
 * Static implementation of result.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

/**
 * Static implementation of result.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class static_result implements result {

    /** @var bool Whether the result is final. */
    protected $isfinal;
    /** @var int|null The points. */
    protected $points;

    /**
     * Constructor.
     *
     * @param int|null $points The points
     * @param bool $isfinal Whether final.
     */
    public function __construct($points, $isfinal = false) {
        $this->points = $points;
        $this->isfinal = $isfinal;
    }

    /**
     * Get the computed points.
     *
     * This should return null when the points could not be established
     * by the calculator.
     *
     * @return int|null
     */
    public function get_points() {
        return $this->points;
    }

    /**
     * Whether the result is final.
     *
     * A final result means that other calculators must not be probed
     * for their result. A non-final result does not mean that other
     * calculators must be involved.
     *
     * @return bool
     */
    public function is_final() {
        return $this->isfinal;
    }

}
