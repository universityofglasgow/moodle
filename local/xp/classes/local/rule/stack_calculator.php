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
 * Stack calculator.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

use coding_exception;

/**
 * Stack calculator.
 *
 * The stack calculator takes several calculators and will ask sequentially
 * ask each of them and will return the first non-null value result, unless
 * one of the results was marked as final, in the case of a result_calculator.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stack_calculator implements result_calculator {

    /** @var calculator[] The calculators. */
    protected $calculators;

    /**
     * Constructor.
     *
     * @param calculator[] $calculators Calculators.
     */
    public function __construct(array $calculators) {
        $this->calculators = $calculators;
    }

    /**
     * Get the points for this subject.
     *
     * @param subject $subject The subject.
     * @return int Or null.
     */
    public function get_points(subject $subject) {
        $points = null;
        foreach ($this->calculators as $calculator) {
            $points = $calculator->get_points($subject);
            if ($points !== null) {
                return $points;
            }
        }
        return $points;
    }

    /**
     * Get the result.
     *
     * @param subject $subject The subject.
     * @return result
     */
    public function get_result(subject $subject) {
        $result = new static_result(null);
        foreach ($this->calculators as $calculator) {
            if (!$calculator instanceof result_calculator) {
                throw new coding_exception('A subcalculator does not implement result_calculator');
            }
            $result = $calculator->get_result($subject);
            if ($result->is_final()) {
                return $result;
            } else if ($result->get_points() !== null) {
                return $result;
            }
        }
        return $result;
    }

}
