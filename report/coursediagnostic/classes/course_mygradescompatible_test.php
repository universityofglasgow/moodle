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
 * Has the course been configured with a Summative and/or Formative category
 *
 * This tests whether the course contains either a Summative or Formative category
 * as required by the MyGrades project, in order for the course to be presented
 * correctly on Student MyGrades
 *
 * @package    report_coursediagnositc
 * @copyright  2024 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

use grade_category;

defined('MOODLE_INTERNAL') || die;

class course_mygradescompatible_test implements \report_coursediagnostic\course_diagnostic_interface {
    
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

        $parent = grade_category::fetch(['courseid' => $this->course->id, 'depth' => 1]);
        $gradecategories = grade_category::fetch_all(['parent' => $parent->id]);
        $foundsummative = false;
        $foundformative = false;
        $mygradescompatible = false;
        if ($gradecategories) {
            foreach($gradecategories as $gradecategory) {
                if (stripos($gradecategory->fullname, 'summative') !== false) {
                    $foundsummative = true;
                    continue;
                }
                if (stripos($gradecategory->fullname, 'formative') !== false) {
                    $foundformative = true;
                    continue;
                }
            }

            if ($foundsummative == true && $foundformative == true) {
                $mygradescompatible = true;
            }
        }
        $settingsurl = new \moodle_url('/grade/edit/tree/index.php', ['id' => $this->course->id]);
        $settingslink = \html_writer::link($settingsurl, get_string('settings_link_text', 'report_coursediagnostic'));

        $this->testresult = [
            'testresult' => $mygradescompatible,
            'settingslink' => $settingslink,
        ];

        return $this->testresult;
    }

}
