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
 * Grade items check.
 *
 * Are there any grade items that haven't been allocated to a category.
 *
 * @package    report_coursediagnostic
 * @copyright  2025 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;

class course_mygrades_orphanedgradeitems_test implements \report_coursediagnostic\course_diagnostic_interface {

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
        $gradeitemsassigned = true;
        $outcometext = get_string('mygrades_orphanedgradeitems_success_text', 'report_coursediagnostic');
        
        // Determine if any of the grade items are orphaned.
        $gradecategories = $DB->get_records('grade_items', ['itemtype' => 'category', 'courseid' => $this->course->id], '',
            'iteminstance');
        if ($gradecategories) {
            $gradecategoryiteminstance = array_column($gradecategories, 'iteminstance', 'iteminstance');
            $params = ['courseid' => $this->course->id];
            if ($gradeitems = $DB->get_records_select('grade_items', 'courseid = :courseid AND itemtype IN (\'mod\', \'manual\')',
                $params, '', 'id, categoryid')) {
                foreach ($gradeitems as $gradeitem) {
                    if (!in_array($gradeitem->categoryid, $gradecategoryiteminstance)) {
                        $gradeitemsassigned = false;
                        break;
                    }
                }
            }
        }

        if ($gradeitemsassigned == false) {
            $gradebookurl = new \moodle_url('/grade/edit/tree/index.php', ['id' => $this->course->id]);
            $gradebookurllink = \html_writer::link($gradebookurl,
                get_string('mygrades_gradebook_link_text', 'report_coursediagnostic'));
            $options = [
                'gradebooksetuplink' => $gradebookurllink
            ];
            $outcometext = get_string('mygrades_orphanedgradeitems_exist_text', 'report_coursediagnostic', $options);

        }

        $this->testresult = [
            'testresult' => $gradeitemsassigned,
            'outcometext' => $outcometext,
        ];

        return $this->testresult;
    }

}
