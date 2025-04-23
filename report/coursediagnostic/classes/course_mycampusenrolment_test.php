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
 * Brief Description
 *
 * More indepth description.
 *
 * @package
 * @copyright  2023 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;

class course_mycampusenrolment_test implements \report_coursediagnostic\course_diagnostic_interface {

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

        global $CFG;
        require_once("$CFG->dirroot/enrol/locallib.php");

        $enrolmentplugins = enrol_get_instances($this->course->id, true);
        $usesmycampus = false;
        $enrolmentpluginsurl = new \moodle_url('/enrol/instances.php', ['id' => $this->course->id]);
        $enrolmentpluginslink = \html_writer::link($enrolmentpluginsurl,
            get_string('enrolmentplugins_link_text', 'report_coursediagnostic'));

        foreach ($enrolmentplugins as $enrolmentinstance) {
            if ($enrolmentinstance->enrol != 'gudatabase') {
                continue;
            }

            if ($enrolmentinstance->status==0) {
                $usesmycampus = true;
            }
        }

        $this->testresult = [
            'testresult' => $usesmycampus,
            'enrolmentmethodslink' => $enrolmentpluginslink
        ];

        return $this->testresult;
    }
}