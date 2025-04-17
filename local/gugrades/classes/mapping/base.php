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
 * Language EN
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\mapping;

/**
 * Base class for scale mappings / conversions
 */
abstract class base {

    /**
     * @var int $courseid
     */
    protected int $courseid;

    /**
     * @var int $gradeitemid
     */
    protected int $gradeitemid;

    /**
     * @var array $items
     */
    protected array $items = [];

    /**
     * @var object $gradeitem
     */
    protected $gradeitem;

    /**
     * @var bool $converted
     */
    protected $converted;

    /**
     * Constructor. Get grade info
     * @param int $courseid
     * @param int $gradeitemid
     * @param bool $converted
     */
    public function __construct(int $courseid, int $gradeitemid, bool $converted = false) {
        global $DB;

        $this->courseid = $courseid;
        $this->gradeitemid = $gradeitemid;
        $this->converted = $converted;

        $this->gradeitem = \local_gugrades\grades::get_gradeitem($gradeitemid);;
    }

    /**
     * "Human" name of this type of grade
     * @return string
     */
    public function name() {
        return '';
    }

    /**
     * Get maximum grade
     */
    public function get_grademax() {
        return $this->gradeitem->grademax;
    }

    /**
     * Get gradecategoryid
     * @return int
     */
    public function get_gradecategoryid() {
        if (!empty($this->gradeitem->categoryid)) {
            return $this->gradeitem->categoryid;
        } else if ($this->gradeitem->itemtype == 'category') {
            return $this->gradeitem->iteminstance;
        } else {
            throw new \moodle_exception('Cannot locate gradecategoryid');
        }
    }

    /**
     * Is the conversion a 'converted' scale
     * @return bool
     */
    public function is_conversion() {
        return $this->converted;
    }

    /**
     * Is the conversion a scale (as opposed to points)?
     * @return bool
     */
    public function is_scale() {
        return false;
    }

    /**
     * Does this represent Schedule A/B or neither
     * Return A, B or empty string
     */
    public function get_schedule() {
        return '';
    }

    /**
     * Is this the *special* grade out of 22 case?
     * @return bool
     */
    public function is_exactgrade22() {
        return false;
    }

    /**
     * Maximum grade.
     * @return int
     */
    public function get_maximum_grade() {
        return (int)$this->gradeitem->grademax;
    }


    /**
     * Define scale mapping (if it's a scale)
     * Define array of (e.g.) 10 => 'E3' and so on
     * (Static so we can call it outside of the normal use)
     * @return mixed (array or false if not a scale)
     */
    public static function get_map() {
        throw new \moodle_exception('This function should be overridden');
    }

    /**
     * Handle imported grade
     * Create both converted grade (actual value) and display grade
     * @param float $grade
     * @return [float, string]
     */
    public function import(float $grade) {
        return [0.0, ''];
    }

    /**
     * Validate the CSV grade
     * It should be within grademin and grademax otherwise we'll reject it
     * This is because (I think) the old GCAT can write an invalid 0 into assign_grade / grade_grade
     * @param float $grade
     * @return bool
     */
    public function validate_csv(float $grade) {

        // Need to offset grademin and grademax by one, as Moodle stores scales 1 - based
        $grademin = $this->gradeitem->grademin - 1;
        $grademax = $this->gradeitem->grademax - 1;
        return ($grade >= $grademin) && ($grade <= $grademax);
    }

    /**
     * Get the band (A1, A2...) from its value
     * @param int $grade
     * @return $string
     */
    public function get_band(int $grade) {
        $map = $this->get_map();

        if (!array_key_exists($grade, $map)) {
            throw new \moodle_exception('Invalid grade - for scale');
        }

        return $map[$grade];
    }

    /**
     * Get CSV value
     * A string grade from a CSV file is validated and returned as a grade value
     * @param string $csvgrade
     * @return array [bool $valid, float $grade]
     */
    public function csv_value(string $csvgrade) {
        if ($this->is_scale()) {
            $csvgrade = trim($csvgrade);

            // Check if the grade is in the array of scale values.
            if (!array_key_exists($csvgrade, $this->items)) {

                // Failing that, we will accept the 'basic' grade (A1, A2...)
                if (!$grade = array_search($csvgrade, $this->get_map())) {
                    return [false, 0];
                }
            } else {
                $grade = $this->items[$csvgrade];
            }
        } else {
            $grade = floatval(trim($csvgrade));
        }
        if (!$this->validate_csv($grade)) {
            return [false, 0];
        }

        return [true, $grade];
    }

}
