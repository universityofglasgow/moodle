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
 * Are there students enrolled on this course.
 *
 * This tests whether a course has students enrolled onto it or not.
 *
 * @package    report_coursediagnositc
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;
class course_studentenrolment_test implements \report_coursediagnostic\course_diagnostic_interface {

    /** @var string The name of the test - needed w/in the report */
    public string $testname;

    /** @var object The course object */
    public object $course;

    /** @var array $testresult whether the test has passed or failed. */
    public array $testresult;

    /**
     * @param $name
     */
    public function __construct($name, $course) {
        $this->testname = $name;
        $this->course = $course;
    }

    /**
     * @return array
     */
    public function runtest(): array {

        global $CFG, $PAGE, $DB;
        require_once("$CFG->libdir/accesslib.php");

        // The 'student' archetype can encompass many roles, e.g. revision student.
        // count_role_users() handily accepts an array of roleids to check for.
        $result = $DB->get_records('role', ['archetype' => 'student']);
        $roleids = [];
        foreach($result as $obj) {
            $roleids[] = $obj->id;
        }
        $studentyusers = count_role_users($roleids, $PAGE->context);
        $participantsurl = new \moodle_url('/user/index.php', ['id' => $this->course->id]);
        $participantslink = \html_writer::link($participantsurl, get_string('participants_link_text', 'report_coursediagnostic'));

        $this->testresult = [
            'testresult' => (bool) $studentyusers,
            'participantslink' => $participantslink,
        ];

        return $this->testresult;
    }
}
