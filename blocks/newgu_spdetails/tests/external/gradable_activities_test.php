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
 * Unit tests for the block_newgu_spdetails class.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot .'/blocks/moodleblock.class.php');
require_once($CFG->dirroot . '/blocks/newgu_spdetails/tests/external/newgu_spdetails_advanced_testcase.php');

/**
 * Unit tests for gradable activities.
 */
final class gradable_activities_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * Test that only current course activities are returned.
     *
     * @covers \blocks\newgu_spdetails\api
     */
    public function test_retrieve_gradable_activities_current_courses(): void {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate1 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 5, date("Y"));

        $mygradesassignment1 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate1,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $mygradessummativesubcategoryid = $this->mygradessummativesubcategory->id;
        $params = [
            $mygradessummativesubcategoryid,
            $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment1->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries.
        $this->add_assignment_grade($mygradesassignment1->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_NEW);

        // Fake some more due dates.
        $duedate2 = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 5, date("Y"));

        $mygradesassignment2 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'October lab 2A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate2,
            'gradetype' => 2,
            'grademax' => 40,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $mygradessummativesubcategoryid,
            $mygradesassignment2->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment2->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries.
        $this->add_assignment_grade($mygradesassignment2->id, $this->student1->id, $this->teacher->id, 35,
        ASSIGN_SUBMISSION_STATUS_NEW);

        $activities = $this->api->retrieve_gradable_activities('current', $this->student1->id, 'duedate', 'asc',
            $mygradessummativesubcategoryid);

        $this->assertIsArray($activities);
        $this->assertArrayHasKey('coursedata', $activities);
        $this->assertCount(2, $activities['coursedata']['courseitems']);
    }

    /**
     * Test that only past course activities are returned.
     *
     * @covers \blocks\newgu_spdetails\api
     */
    public function test_retrieve_gradable_activities_past_courses(): void {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Some dates for our mock course.
        $startdate = mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1);
        $enddate  = mktime(0, 0, 0, date("m") + 5, date("d"), date("Y") - 1);

        $pastcourse = $this->getDataGenerator()->create_course([
            'fullname' => 'Past Course Test',
            'shortname' => 'PCT1',
            'startdate' => $startdate,
            'enddate' => $enddate,
        ]);

        // We need to enrol our student onto this course.
        $pastcoursecontext = \context_course::instance($pastcourse->id);
        $this->getDataGenerator()->enrol_user($this->student1->id, $pastcourse->id, $this->get_roleid());
        $this->getDataGenerator()->role_assign('student', $this->student1->id, $pastcoursecontext);

        // Create the parent grade category.
        $pastsummativecategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Summative Assessments',
            'courseid' => $pastcourse->id,
        ]);
        // Now create the sub categories that live under this parent.
        $pastsummativesubcategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Past Assessments Aggregated',
            'courseid' => $pastcourse->id,
            'parent' => $pastsummativecategory->id,
        ]);

        // Fake some due dates.
        $duedate1 = mktime(date("H"), date("i"), date("s"), date("m") + 2, date("d"), date("Y") - 1);

        $pastassignment1 = $this->getDataGenerator()->create_module('assign', [
            'name' => 'Past lab 1A',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $pastcourse->id,
            'duedate' => $duedate1,
            'gradetype' => 2,
            'grademax' => 40,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $pastsummativesubcategoryid = $pastsummativesubcategory->id;
        $params = [
            $pastsummativesubcategoryid,
            $pastassignment1->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $pastassignment1->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries.
        $this->add_assignment_grade($pastassignment1->id, $this->student1->id, $this->teacher->id, 30,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        $activities = $this->api->retrieve_gradable_activities('past', $this->student1->id, 'duedate', 'asc',
            $pastsummativesubcategoryid);

        $this->assertIsArray($activities);
        $this->assertArrayHasKey('coursedata', $activities);
        $this->assertCount(1, $activities['coursedata']['courseitems']);
    }
}
