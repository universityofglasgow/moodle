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
 * Conversion class for exactly out of 22. 
 * This is a proxy for Schedule A when a maximum points grade of exactly
 * 22 is specified.
 *
 * @package    local_gugrades
 * @copyright  2025
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\mapping;

/**
 * Note that this extends ScheduleA, instead of base
 */
class points22 extends schedulea {

    /**
     * Constructor. Get grade info
     * @param int $courseid
     * @param int $gradeitemid
     * @param bool $converted
     */
    public function __construct(int $courseid, int $gradeitemid, bool $converted = false) {
        global $DB;

        parent::__construct($courseid, $gradeitemid, $converted);

        // As maxgrade = 22, use the build in map.
        $map = $this->get_map();
        $this->items = array_flip($map);
    }

    /**
     * "Human" name of this type of grade
     * @return string
     */
    public function name() {
        return 'Schedule A (22)';
    }

    /**
     * Is this the *special* grade out of 22 case?
     * @return bool
     */
    public function is_exactgrade22() {
        return true;
    }

    /**
     * Validate the CSV grade
     * It should be within grademin and grademax otherwise we'll reject it
     * This is because (I think) the old GCAT can write an invalid 0 into assign_grade / grade_grade
     * @param float $grade
     * @return bool
     */
    public function validate_csv(float $grade) {
        $grademin = 0;
        $grademax = 22;

        return ($grade >= $grademin) && ($grade <= $grademax);
    }

    /**
     * Does this represent Schedule A/B or neither
     * Return A, B or empty string
     */
    public function get_schedule() {
        return \local_gugrades\GRADETYPE_SCHEDULEA;
    }

    /**
     * Handle imported grade
     * Create both converted grade (actual value) and display grade
     * @param float|null $floatgrade
     * @return [float, string]
     */
    public function import(float|null $floatgrade) {
        global $DB;

        // MGU-1293 null represents no grade.
        if (is_null($floatgrade)) {
            return [null, get_string('nograde', 'local_gugrades')];
        }

        // It's a scale, so it can't be a decimal.
        $grade = round($floatgrade);

        $map = $this->get_map();
        if (!array_key_exists($grade, $map)) {
            throw new \moodle_exception('Grade ' . $grade . 'is not in Schedule A');
        } else {
            return [$grade, $map[$grade]];
        }

        if (isset($this->scaleitems[$grade])) {
            $scaleitem = $this->scaleitems[$grade];
        } else {
            throw new \moodle_exception('Scale item does not exist. Scale id = ' .
                $this->gradeitem->scaleid . ', value = ' . $grade);
        }

        // Convert to value using scalevalue.
        if (array_key_exists($scaleitem, $this->items)) {
            $converted = $this->items[$scaleitem];
        } else {
            throw new \moodle_exception('Scale item "' . $scaleitem . '" does not exist in scale id = ' .
                $this->gradeitem->scaleid);
        }

        return [$converted, $scaleitem];
    }

}
