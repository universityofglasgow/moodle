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
 * Test scenario where user is enrolled after grades have been released
 * @package    local_gugrades
 * @copyright  2025
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_advanced_testcase.php');

/**
 * Test import_grades_users web service.
 */
final class missing_released_user_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Import first grades.
     *
     * @covers \local_gugrades\external\import_grades_users::execute
     */
    public function test_categories_returned(): void {
        global $DB;

        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];

        // Assign2 (which is useing scale).
        $this->import_grades($this->course->id, $this->gradeitemidassign2, $userlist);

        // Release the grade
        $status = release_grades::execute($this->course->id, $this->gradeitemidassign2, 0, false);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Get aggregated data for this category
        //$userdata = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $this->gradecatsumm->id, $this->student->id);

        // Enrol a new student into the course.
        $newstudent = $this->getDataGenerator()->create_user(['idnumber' => '1234569', 'firstname' => 'Greg', 'lastname' => 'Pedder']);
        $this->getDataGenerator()->enrol_user($newstudent->id, $this->course->id, 'student');

        // Try to get data for this student.
        $newuserdata = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $this->gradecatsumm->id, $newstudent->id);

    }
}
