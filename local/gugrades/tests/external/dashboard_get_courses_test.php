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
 * Test dashboard_get_courses web service
 * @package    local_gugrades
 * @copyright  2023
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
 * Test get_activities web service.
 */
final class dashboard_get_courses_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Enable customfield setting for course
     * @param int $courseid
     * @param bool $enable
     */
    protected function enable_course(int $courseid, bool $enable): void {
        global $DB;

        $context = \context_course::instance($courseid);

        // Find the custom field
        $field = $DB->get_record('customfield_field', ['shortname' => 'studentmygrades'], '*', MUST_EXIST);

        // does the field exist
        if (!$data = $DB->get_record('customfield_data', ['fieldid' => $field->id, 'instanceid' => $courseid])) {
            $data = new \stdClass;
            $data->fieldid = $field->id;
            $data->instanceid = $courseid;
            $data->intvalue = $enable ? 1 : 0;
            $data->value = $enable ? 1 : 0;
            $data->valueformat = 0;
            $data->valuetrust = 0;
            $data->timecreated = time();
            $data->timemodified = time();
            $data->context = $context;
            $DB->insert_record('customfield_data', $data);
        } else {
            $data->intvalue = $enable ? 1 : 0;
            $data->value = $enable ? 1 : 0;
            $data->timemodified = time();
            $DB->update_record('customfield_data', $data);
        }
    }

    /**
     * Check that weird current/past filter works properly
     * Note that past/future 'cutoff' date is 30 days in the future
     *
     * @covers \local_gugrades\external\dashboard_get_courses::execute
     */
    public function test_filter_courses_by_date(): void {
        global $DB;

        // Staff for next bit
        $this->setUser($this->teacher->id);

        // Create some courses with suitable end dates.
        $oneyear = 86400 * 365;

        // Create courses with end date in the 'future'.
        $currentcourse1 = $this->getDataGenerator()->create_course([
            'fullname' => 'Current Course One',
            'startdate' => time() - $oneyear,
            'enddate' => time() + $oneyear,
        ]);
        $currentcourse2 = $this->getDataGenerator()->create_course([
            'fullname' => 'Current Course Two',
            'startdate' => time() - $oneyear,
            'enddate' => time() + 86400,
        ]);

        // Create courses with end date in the 'past'.
        $pastcourse1 = $this->getDataGenerator()->create_course([
            'fullname' => 'Past Course One',
            'startdate' => time() - (2 * $oneyear),
            'enddate' => time() - $oneyear,
        ]);
        $pastcourse2 = $this->getDataGenerator()->create_course([
            'fullname' => 'Past Course Two',
            'startdate' => time() - (2 * $oneyear),
            'enddate' => time() - (30 * 86400), // Last possible day!
        ]);

        // set 'startdateafter' config setting to allow past courses
        set_config('startdateafter', strtotime('2019-01-01'), 'local_gugrades');

        // Enrol student on all of the above
        // Note - the student is enrolled on a 5th course in setUp().
        $studentid = $this->student->id;
        $this->getDataGenerator()->enrol_user($studentid, $currentcourse1->id);
        $this->getDataGenerator()->enrol_user($studentid, $currentcourse2->id);
        $this->getDataGenerator()->enrol_user($studentid, $pastcourse1->id);
        $this->getDataGenerator()->enrol_user($studentid, $pastcourse2->id);

        // We're the test student.
        $this->setUser($this->student->id);

        // Check that NO courses are returned as they have not been enabled.
        $courses = dashboard_get_courses::execute($studentid, false, false, '');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );
        $this->assertIsArray($courses);
        $this->assertCount(0, $courses);

        // Enable some courses and try again.
        $this->enable_course($currentcourse1->id, true);
        $this->enable_course($pastcourse2->id, true);
        $courses = dashboard_get_courses::execute($studentid, false, false, '');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );

        $this->assertIsArray($courses);
        $this->assertCount(2, $courses);

        // Just enable the rest
        $this->enable_course($currentcourse2->id, true);
        $this->enable_course($pastcourse1->id, true);
        $this->enable_course($this->course->id, true);
        $courses = dashboard_get_courses::execute($studentid, false, false, '');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );

        // Check top-level grade categories.
        $catcourse = $courses[4];
        $this->assertCount(1, $catcourse['firstlevel']);
        $this->assertEquals('Summative', $catcourse['firstlevel'][0]['fullname']);
        $this->assertIsInt($catcourse['firstlevel'][0]['id']);

        // Get only 'current' courses
        // Default course should be included as enddate is disabled (= 0).
        $courses = dashboard_get_courses::execute($studentid, true, false, '');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );
        $this->assertIsArray($courses);
        $this->assertCount(3, $courses);
        $this->assertEquals('Current Course One', $courses[0]['fullname']);

        // Get only 'past' courses.
        $courses = dashboard_get_courses::execute($studentid, false, true, '');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );
        $this->assertIsArray($courses);
        $this->assertEquals('Past Course One', $courses[0]['fullname']);

        // Check the courses that should be not enabled for MyGrades.
        $this->assertFalse($courses[0]['gugradesenabled']);
        $this->assertFalse($courses[1]['gugradesenabled']);

        // Check sorting.
        $courses = dashboard_get_courses::execute($studentid, true, false, 'enddate');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );
        $this->assertIsArray($courses);
        $this->assertCount(3, $courses);
        $this->assertEquals('Current Course Two', $courses[1]['fullname']);

        // Staff for next bit
        $this->setUser($this->teacher->id);

        // MyGrades is enabled by releasing grades for a course.
        $userlist = [
            $this->student->id,
            $this->student2->id,
        ];
        $this->import_grades($this->course->id, $this->gradeitemidassign2, $userlist);
        $status = release_grades::execute($this->course->id, $this->gradeitemidassign2, 0, false);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // We're the test student (again).
        $this->setUser($this->student->id);

        // Check again for mygrades.
        $courses = dashboard_get_courses::execute($studentid, true, false, '');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );
        $this->assertCount(3, $courses);
        $this->assertTrue($courses[2]['gugradesenabled']);

        // Switch off the course.
        $this->disable_dashboard($this->course->id, true);

        // Check again for mygrades (above should have switched it off, despite release).
        $courses = dashboard_get_courses::execute($studentid, true, false, '');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );
        $this->assertCount(3, $courses);
        $this->assertFalse($courses[2]['gugradesenabled']);

        // set 'startdateafter' config setting to default to block past courses.
        set_config('startdateafter', strtotime('2024-08-05'), 'local_gugrades');
        $courses = dashboard_get_courses::execute($studentid, false, true, '');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );
        $this->assertCount(0, $courses);

        $courses = dashboard_get_courses::execute($studentid, true, false, '');
        $courses = external_api::clean_returnvalue(
            dashboard_get_courses::execute_returns(),
            $courses
        );

        // Should still be three courses (@see MGU-1245).
        $this->assertCount(3, $courses);
    }

}
