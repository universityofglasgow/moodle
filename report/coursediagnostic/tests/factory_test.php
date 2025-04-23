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

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/report/coursediagnostic/classes/diagnostic_factory.php');

/**
 * Unit test for the factory creation
 *
 * @package    report_coursediagnostic
 * @copyright  2023 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class factory_test extends \basic_testcase {

    /**
     * test_create_diagnostic_test_from_config
     *
     * @covers ::create_diagnostic_test_from_config
     *
     * @dataProvider diagnostic_test_provider
     * @param string|null $name The name of the test being performed.
     * @param object $course A course object.
     * @return null
     */
    public function test_create_diagnostic_test_from_config(?string $name, object $course) {
        $factory = \report_coursediagnostic\diagnostic_factory::instance();
        $classname = "course_{$name}_test";
        $namespace = '\\report_coursediagnostic';
        $fully_qualified_class_name = "$namespace\\$classname";
        $object = new $fully_qualified_class_name;
        $this->assertInstanceOf($object, $factory->create_diagnostic_test_from_config($name,$course));

        return;
    }

    /**
     * Data provider for {@see test_create_diagnostic_test_from_config()}.
     *
     * @return array List of data sets - (string) data set name => (array) data
     */
    public function diagnostic_test_provider() {

        $course = new \stdClass();
        $course->id = 1;
        $course->name = 'Test Course';
        $course->enablecompletion = 0;
        $course->startdate = date('Y-m-d h:i:s');
        $course->visible = true;

        return [
            'Activity Completion' => [
                'name' => 'activitycompletion',
                'course' => $course
            ],
            'Course Start Date' => [
                'name' => 'startdate',
                'course' => $course
            ],
            'Course Visibility' => [
                'name' => 'visibility',
                'course' => $course
            ],
        ];
    }
}
