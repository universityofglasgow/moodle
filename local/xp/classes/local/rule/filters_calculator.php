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
 * Calculator.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

/**
 * Calculator.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filters_calculator implements result_calculator {

    /** @var block_xp_filter The filters. */
    protected $filters;

    /**
     * Constructor.
     *
     * @param array $filters Filters.
     * @param calculator|null $altcalculator Alternate calculator.
     */
    public function __construct(array $filters) {
        $this->filters = $filters;
    }

    /**
     * Get the points for this subject.
     *
     * @param subject $subject The subject.
     * @return int Or null.
     */
    public function get_points(subject $subject) {
        if (!$subject instanceof event_subject) {
            return null;
        }

        $e = $subject->get_event();
        foreach ($this->filters as $filter) {
            if ($filter->match($e)) {
                return $filter->get_points();
            }
        }

        return null;
    }

    /**
     * Get the result.
     *
     * @param subject $subject The subject.
     * @return result
     */
    public function get_result(subject $subject) {
        return new static_result($this->get_points($subject));
    }

}
