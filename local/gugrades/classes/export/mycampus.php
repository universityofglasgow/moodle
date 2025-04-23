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
class mycampus extends base {

    /**
     * Define name of export
     * @return string
     */
    public function get_name() {
        return get_string('mycampusexport', 'local_gugrades');
    }

    /**
     * Only give MyCampus valid grades
     * @param object $user
     * @return string
     */
    protected function sanitise_grade($user) {

        // First check if there is an admin grade.
        if ($user->admingrade) {
            $grade = $user->admingrade;

            // Change MV0 to MV
            if ($grade == 'MV0') {
                $grade = 'MV';
            }

            return $grade;
        }

        // Failing that, does it appear to be an actual grade?
        // Rawgrade must have some value.
        if ($user->rawgrade) {
            $grade = $user->displaygrade;

            // Remove the bracketted value
            $parts = explode(' ', $grade);

            return $parts[0];
        }

        return '';
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

        // Get list of students.
        $users = \local_gugrades\aggregation::get_users($courseid, $gradecategoryid, '', '', $groupid);

        // Aggregate all the users.
        [$columns] = \local_gugrades\aggregation::get_columns($courseid, $gradecategoryid);
        [$users] = \local_gugrades\aggregation::add_aggregation_fields_to_users($courseid, $gradecategoryid, $users, $columns);

        // Array holds CSV lines.
        $lines = [];

        // Header
        $lines[] = [
            'EMPLID',
            'Name',
            'Grade',
        ];

        // Iterate over users getting requested data.
        foreach ($users as $user) {
            $line = [];

            // EMPLID
            $line[] = $user->idnumber;

            // Name
            $line[] = $user->lastname . ',' . $user->firstname;

            // Grade
            $line[] = $this->sanitise_grade($user);

            $lines[] = $line;
        }

        return $this->convert_csv($lines);
    }
}
