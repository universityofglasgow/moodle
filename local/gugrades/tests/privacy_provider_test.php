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
 * local_gugrades privacy provider test
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;
use format_topcoll\privacy\provider;

/**
 * Privacy unit tests for the Collapsed Topics course format.
 * @group format_topcoll
 */
final class privacy_provider_test extends \core_privacy\tests\provider_testcase {

    /**
     * Set up.
     */
    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    /**
     * Write grade to MyGrades
     * @param int $courseid
     * @param int $userid
     * @param int $gradeitemid
     * @param float $grade
     */
    protected function write_mygrades_grade(int $courseid, int $userid, int $gradeitemid, float $grade): void {
        \local_gugrades\grades::write_grade(
            courseid:       $courseid,
            gradeitemid:    $gradeitemid,
            userid:         $userid,
            admingrade:     '',
            rawgrade:       $grade,
            convertedgrade: $grade,
            displaygrade:   $grade,
            weightedgrade:  0,
            gradetype:      'FIRST',
            other:          '',
            iscurrent:      true,
            iserror:        false,
            auditcomment:   '',
            ispoints:       true,
            overwrite:      false
        );
    }

    /**
     * Ensure that get_metadata exports valid content.
     */
    public function test_get_metadata(): void {
        $items = new collection('local_gugrades');
        $result = provider::get_metadata($items);
        $this->assertSame($items, $result);
        $this->assertInstanceOf(collection::class, $result);
    }

    /**
     * Test that user who has no grades is not returned
     */
    public function test_user_no_grades(): void {

        // Basic course and user set up
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user(['idnumber' => '1234567', 'firstname' => 'Fred', 'lastname' => 'Bloggs']);
        $teacher = $this->getDataGenerator()->create_user(['idnumber' => '123', 'firstname' => 'Jim', 'lastname' => 'Smith']);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);

        // Test that no contexts were retrieved.
        $contextlist = $this->get_contexts_for_userid($student->id, 'local_gugrades');
        $contexts = $contextlist->get_contextids();

        $this->assertCount(0, $contexts);
    }

    /**
     * Test that user who HAS grades IS returned
     */
    public function test_user_with_grades(): void {

        // Basic course and user set up
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user(['idnumber' => '1234567', 'firstname' => 'Fred', 'lastname' => 'Bloggs']);
        $teacher = $this->getDataGenerator()->create_user(['idnumber' => '123', 'firstname' => 'Jim', 'lastname' => 'Smith']);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $gradeitem = $this->getDataGenerator()->create_grade_item(['courseid' => $course->id]);

        // Write grade to MyGrades
        $this->write_mygrades_grade($course->id, $student->id, $gradeitem->id, 50.0);

        // Test that no contexts were retrieved.
        $contextlist = $this->get_contexts_for_userid($student->id, 'local_gugrades');
        $contexts = $contextlist->get_contextids();

        $this->assertCount(1, $contexts);
    }

    /**
     * Test for provider::get_users_in_context().
     */
    public function test_get_users_in_context(): void {

        // Basic course and user set up
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user(['idnumber' => '1234567', 'firstname' => 'Fred', 'lastname' => 'Bloggs']);
        $teacher = $this->getDataGenerator()->create_user(['idnumber' => '123', 'firstname' => 'Jim', 'lastname' => 'Smith']);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $gradeitem = $this->getDataGenerator()->create_grade_item(['courseid' => $course->id]);

        // Write grade to MyGrades
        $this->write_mygrades_grade($course->id, $student->id, $gradeitem->id, 50.0);

        $context = \context_course::instance($course->id);
        $userlist = new \core_privacy\local\request\userlist($context, 'local_gugrades');
        $users = \local_gugrades\privacy\provider::get_users_in_context($userlist);

        $userids = $users->get_userids();
        $this->assertCount(1, $userids);
    }


}