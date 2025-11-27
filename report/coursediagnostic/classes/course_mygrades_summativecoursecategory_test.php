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
 * Gradebook setup - course categories.
 *
 * Has a category named "Summative" been created, and is it at the top level.
 *
 * @package    report_coursediagnostic
 * @copyright  2025 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;

class course_mygrades_summativecoursecategory_test implements \report_coursediagnostic\course_diagnostic_interface {

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
     * Return true or false, if the course has a top level
     * Grade category named "Summative". We use depth to
     * determine its relative level in the structure.
     * 
     * @return array
     */
    public function runtest(): array {

        global $DB;
        $hassummativecategory = false;
        $outcometext = get_string('mygrades_summativecoursecategory_success_text', 'report_coursediagnostic');
        
        if ($gradecategories = $DB->get_records('grade_categories', ['courseid' => $this->course->id])) {
            foreach ($gradecategories as $gradecategory) {
                if (preg_match("/^Summative\b/i", $gradecategory->fullname)) {
                    if ($gradecategory->depth == 2) {
                        $hassummativecategory = true;
                        break;
                    }
                }
            }
        }

        if ($hassummativecategory == false) {
            $gradecategoryurl = new \moodle_url('/grade/edit/tree/index.php', ['id' => $this->course->id]);
            $gradecategorylink = \html_writer::link($gradecategoryurl,
                get_string('mygrades_gradebook_link_text', 'report_coursediagnostic'));
            $options = [
                'gradecategorylink' => $gradecategorylink
            ];
            $outcometext = get_string('mygrades_summativecoursecategory_no_category_text', 'report_coursediagnostic', $options);

        }

        $this->testresult = [
            'testresult' => $hassummativecategory,
            'outcometext' => $outcometext,
        ];

        return $this->testresult;
    }

}
