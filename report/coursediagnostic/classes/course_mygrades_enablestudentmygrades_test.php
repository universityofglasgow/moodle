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
 * Course settings - "Enable Student MyGrades" checkbox test.
 *
 * Has the checkbox "Enable Student MyGrades" been checked - which controls
 * if this course appears on Student MyGrades or not.
 *
 * @package    report_coursediagnostic
 * @copyright  2025 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;

class course_mygrades_enablestudentmygrades_test implements \report_coursediagnostic\course_diagnostic_interface {

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
     * Return true or false, if the checkbox for
     * "Enable Student MyGrades" has been checked or not.
     * 
     * @return array
     */
    public function runtest(): array {

        global $DB;
        $ischecked = false;
        $outcometext = get_string('mygrades_enablestudentmygrades_success_text', 'report_coursediagnostic');
        
        // We determine if the checlbox has been checked by querying the following tables.
        if ($customfield = $DB->get_record('customfield_field', ['shortname' => 'studentmygrades'], '*', MUST_EXIST)) {
            if ($data = $DB->get_record('customfield_data', ['fieldid' => $customfield->id, 'instanceid' => $this->course->id])) {
                if ($data->intvalue == 1) {
                    $ischecked = true;
                }
            }
        }

        if ($ischecked == false) {
            $courseurl = new \moodle_url('/course/edit.php', ['id' => $this->course->id]);
            $courseurllink = \html_writer::link($courseurl,
                get_string('settings_link_text', 'report_coursediagnostic'));
            $options = [
                'coursesettingslink' => $courseurllink
            ];
            $outcometext = get_string('mygrades_enablestudentmygrades_not_checked_text', 'report_coursediagnostic', $options);

        }

        $this->testresult = [
            'testresult' => $ischecked,
            'outcometext' => $outcometext,
        ];

        return $this->testresult;
    }

}
