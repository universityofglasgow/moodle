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
 * Activities with duplicate names linked to restriction rules.
 *
 * Activities can be duplicated with the same name, and then linked to a restriction rule.
 * This can potentially cause confusion when an activity 'points' to the wrong restriction.
 *
 * @package    report_coursediagnositc
 * @copyright  2023 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

class course_duplicateactivityrestrictions_test implements course_diagnostic_interface
{

    /** @var string The name of the test - needed w/in the report */
    public string $testname;

    /** @var object The course object */
    public object $course;

    /** @var array $testresult whether the test has passed or failed. */
    public array $testresult;

    /**
     * @param $name
     * @param $course
     */
    public function __construct($name, $course) {
        $this->testname = $name;
        $this->course = $course;
    }

    /**
     * @return array
     */
    public function runtest(): array {

        $moduleinfo = get_fast_modinfo($this->course->id);
        $context = \context_course::instance($moduleinfo->courseid);
        $modulenames = $moduleinfo->get_used_module_names();

        foreach ($modulenames as $modulename => $value) {
            $cm_info = $moduleinfo->get_instances_of($modulename);
            $test = 0;
        }

        $this->testresult = [
            'testresult' => true,
        ];

        return $this->testresult;
    }
}