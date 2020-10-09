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
 * Calculator mock.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

use local_xp\local\rule\result;
use local_xp\local\rule\result_calculator;
use local_xp\local\rule\static_result;
use local_xp\local\rule\subject;

/**
 * Calculator mock.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_calculator_mock implements result_calculator {

    /** @var int Points. */
    public $points;
    /** @var result Result. */
    public $result;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->result = new static_result(null, false);
    }

    /**
     * Get the points for this subject.
     *
     * @param subject $subject The subject.
     * @return int Or null.
     */
    public function get_points(subject $subject) {
        return $this->points;
    }

    /**
     * Get the result for this subject.
     *
     * @param subject $subject The subject.
     * @return result
     */
    public function get_result(subject $subject) {
        return $this->result;
    }

}
