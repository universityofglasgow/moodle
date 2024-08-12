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
 * Conversion class for Schedule A
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\conversion;

/**
 * Handle 22-point scale / Schedule A
 */
class schedulea extends base {

    /**
     * @var array $scaleitems
     */
    protected array $scaleitems = [];

    /**
     * If Schedule A as proxy for grade exactly out of 22
     * @var bool $exactgrade22
     */
    protected bool $exactgrade22;

    /**
     * Constructor. Get grade info
     * @param int $courseid
     * @param int $gradeitemid
     * @param bool $converted
     * @param bool $exactgrade22
     */
    public function __construct(int $courseid, int $gradeitemid, bool $converted = false, bool $exactgrade22 = false) {
        global $DB;

        parent::__construct($courseid, $gradeitemid, $converted);

        $this->exactgrade22 = $exactgrade22;

        // If maxgrade = 22 then use the build in map.
        if ($exactgrade22) {
            $map = $this->get_map();
            $this->items = array_flip($map);
        } else if (!$converted) {

            // If converted, use the built-in grade.

            // Get scale.
            $scale = $DB->get_record('scale', ['id' => $this->gradeitem->scaleid], '*', MUST_EXIST);
            $this->scaleitems = array_map('trim', explode(',', $scale->scale));

            // Get scale conversion.
            $items = $DB->get_records('local_gugrades_scalevalue', ['scaleid' => $this->gradeitem->scaleid]);
            foreach ($items as $item) {
                $this->items[$item->item] = $item->value;
            }
        }
    }

    /**
     * "Human" name of this type of grade
     * @return string
     */
    public function name() {
        return 'Schedule A';
    }

    /**
     * Get maximum grade
     */
    public function get_grademax() {
        return 22;
    }

    /**
     * Is the conversion a scale (as opposed to points)?
     * @return bool
     */
    public function is_scale() {
        return true;
    }

    /**
     * Is this the *special* grade out of 22 case?
     * @return bool
     */
    public function is_exactgrade22() {
        return $this->exactgrade22;
    }

    /**
     * Does this represent Schedule A/B or neither
     * Return A, B or empty string
     */
    public function get_schedule() {
        return \local_gugrades\GRADETYPE_SCHEDULEA;
    }

    /**
     * Define scale mapping
     * @return array
     */
    public static function get_map() {
        return [
            0 => 'H',
            1 => 'G2',
            2 => 'G1',
            3 => 'F3',
            4 => 'F2',
            5 => 'F1',
            6 => 'E3',
            7 => 'E2',
            8 => 'E1',
            9 => 'D3',
            10 => 'D2',
            11 => 'D1',
            12 => 'C3',
            13 => 'C2',
            14 => 'C1',
            15 => 'B3',
            16 => 'B2',
            17 => 'B1',
            18 => 'A5',
            19 => 'A4',
            20 => 'A3',
            21 => 'A2',
            22 => 'A1',
        ];
    }

    /**
     * Handle imported grade
     * Create both converted grade (actual value) and display grade
     * @param float $floatgrade
     * @return [float, string]
     */
    public function import(float $floatgrade) {
        global $DB;

        // It's a scale, so it can't be a decimal.
        $grade = round($floatgrade);

        // If converted OR maxgrade=22.
        if ($this->converted || $this->exactgrade22) {
            $map = $this->get_map();
            if (!array_key_exists($grade, $map)) {
                throw new \moodle_exception('Grade ' . $grade . 'is not in Schedule A');
            } else {
                return [$grade, $map[$grade]];
            }
        }

        // Get scale (scales start at 1 not 0, unless out of 22).
        if (!$this->exactgrade22) {
            $grade = $grade - 1;
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
