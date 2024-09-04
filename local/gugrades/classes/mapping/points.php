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
 * Handle points grades
 */
class points extends base {

    /**
     * "Human" name of this type of grade
     * @return string
     */
    public function name() {
        return 'Points';
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
        return \local_gugrades\GRADETYPE_POINTS;
    }

    /**
     * Handle imported grade
     * Create both converted grade (actual value) and display grade
     * @param float $grade
     * @return [float, string]
     */
    public function import(float $grade) {
        return [$grade, $grade];
    }

}
