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
 * Default class for aggregation export classes
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\export;

/**
 * Access data in course activities
 *
 */
abstract class base {

    /**
     * Define name of export
     * @return string
     */
    public function get_name() {
        return '';
    }

    /**
     * Does the plugin define optional fields?
     * @return boolean
     */
    public function defines_optional_fields() {
        return false;
    }

    /**
     * Return list of fields for form
     * (called if defines_optional_fields() is true)
     * @param int $courseid
     * @param int $gradecategoryid
     * @return array
     */
    public function get_form_fields(int $courseid, int $gradecategoryid) {
        return [];
    }

    /**
     * Return data for CSV export
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $groupid
     * @param array $form
     * @return array
     */
    public function get_form_data(int $courseid, int $gradecategoryid, int $groupid, array $form) {
        return [];
    }

    /**
     * Convert array of arrays into CSV string
     * @param array $data
     * @return string
     */
    protected function convert_csv(array $data) {
        $csv = '';
        foreach ($data as $line) {
            $quoted = array_map(function($str) {
                return sprintf('"%s"', $str);
            }, $line);
            $csv .= implode(',', $quoted) . PHP_EOL;
        }

        return $csv;
    }

}
