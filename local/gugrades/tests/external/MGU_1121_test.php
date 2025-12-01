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
 * Testing completion % when grades are marked as 'not available'
 * @package    local_gugrades
 * @copyright  2024
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
 * Test(s) for get_all_strings webservice
 */
final class MGU_1121_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * @var int $gradeitemsecondx
     */
    protected int $gradeitemsecondx;

    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();

        // Final item has an invalid grade type.
        $seconditemx = $this->getDataGenerator()->create_grade_item(
            ['courseid' => $this->course->id, 'gradetype' => GRADE_TYPE_TEXT]
        );
        $this->move_gradeitem_to_category($seconditemx->id, $this->gradecatsecond->id);

        $this->gradeitemsecondx = $seconditemx->id;

        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_condition.php');
    }

    /**
     * Modify grade type to be 22-point scale
     * @param int $scaleid
     * @param int $gradeitemid
     */
    private function set_gradetype(int $scaleid, int $gradeitemid) {
        global $DB;

        $gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $gradeitem->gradetype = GRADE_TYPE_SCALE;
        $gradeitem->grademax = 23.0;
        $gradeitem->grademin = 1.0;
        $gradeitem->scaleid = $scaleid;
        $DB->update_record('grade_items', $gradeitem);
    }

    /**
     * Set category for assignment
     * @param int $gradeitemid
     * @param int $categoryid
     */
    private function update_category(int $gradeitemid, int $categoryid) {
        global $DB;

        $item = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $item->categoryid = $categoryid;
        $DB->update_record('grade_items', $item);
    }

    /**
     * Import grades
     * @param int $gradeitemid
     * @param int $studentid
     */
    private function import_student_grades(int $gradeitemid, int $studentid) {

        // Import grades.
        $userlist = [
            $studentid,
        ];

        // Call import WS.
        $this->import_grades($this->course->id, $gradeitemid, $userlist);
    }

    /**
     * Check get_capture_use for Schedule A grade item.
     *
     * @covers \local_gugrades\external\get_capture_page::execute
     */
    public function test_completion(): void {
        global $CFG, $DB;

        $course = $this->course;
        $scaleid = $this->scale->id;
        $student = $this->student;

        $CFG->enableavailability = 1;

        // Make sure that we're admin.
        $this->setAdminUser();

        // Create group and grouping.
        $group1 = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group 1']);
        $this->getDataGenerator()->create_group_member(['userid' => $student->id, 'groupid' => $group1->id]);
        $group2 = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group 2']);
        $this->getDataGenerator()->create_group_member(['userid' => $this->student2->id, 'groupid' => $group1->id]);
        $grouping1 = $this->getDataGenerator()->create_grouping(['courseid' => $course->id, 'name' => 'Grouping 1']);
        $this->getDataGenerator()->create_grouping_group(['groupingid' => $grouping1->id, 'groupid' => $group2->id]);

        // Create another TL category for this test.
        $summative = $this->getDataGenerator()->create_grade_category(['courseid' => $course->id, 'fullname' => 'Completion Test']);

        // Create 4 Assignments in that category.
        $assign1 = $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'name' => 'MGU1']);
        $assign2 = $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'name' => 'MGU2']);
        $assign3 = $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'name' => 'MGU3']);
        $assign4 = $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'name' => 'MGU4']);

        // Get grade items
        $gradeitemidassign1 = $this->get_grade_item('', 'assign', $assign1->id);
        $gradeitemidassign2 = $this->get_grade_item('', 'assign', $assign2->id);
        $gradeitemidassign3 = $this->get_grade_item('', 'assign', $assign3->id);
        $gradeitemidassign4 = $this->get_grade_item('', 'assign', $assign4->id);

        // Add them to the summative category
        $this->update_category($gradeitemidassign1, $summative->id);
        $this->update_category($gradeitemidassign2, $summative->id);
        $this->update_category($gradeitemidassign3, $summative->id);
        $this->update_category($gradeitemidassign4, $summative->id);

        // Shift them all to 22 point scale
        $this->set_gradetype($scaleid, $gradeitemidassign1);
        $this->set_gradetype($scaleid, $gradeitemidassign2);
        $this->set_gradetype($scaleid, $gradeitemidassign3);
        $this->set_gradetype($scaleid, $gradeitemidassign4);

        // Give then all a grade
        $this->add_assignment_grade($assign1->id, $student->id, 12);
        $this->add_assignment_grade($assign2->id, $student->id, 18);
        $this->add_assignment_grade($assign3->id, $student->id, 1);
        $this->add_assignment_grade($assign4->id, $student->id, 20);

        // Import
        $this->import_student_grades($gradeitemidassign1, $student->id);
        $this->import_student_grades($gradeitemidassign2, $student->id);
        $this->import_student_grades($gradeitemidassign3, $student->id);
        $this->import_student_grades($gradeitemidassign4, $student->id);

        // Get data for this user.
        $user = get_aggregation_user::execute($course->id, $summative->id, $student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );

        // Set up Assignment 2 for grouping2 (not our student).
        $cm = $DB->get_record('course_modules', ['id' => $assign2->cmid], '*', MUST_EXIST);
        $cm->groupmode = 1;
        $cm->groupingid = $grouping1->id;
        $DB->update_record('course_modules', $cm);
        get_fast_modinfo($course, $student->id, true);

        // Recalculate.
        $nothing = recalculate::execute($course->id, $summative->id);
        $nothing = external_api::clean_returnvalue(
            recalculate::execute_returns(),
            $nothing
        );

        // Get data for this user with restriction.
        $user = get_aggregation_user::execute($course->id, $summative->id, $student->id);
        $user = external_api::clean_returnvalue(
            get_aggregation_user::execute_returns(),
            $user
        );

        //var_dump($user);
    }

}
