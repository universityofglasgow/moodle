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
 * Base class for multiple advanced test cases
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use externallib_advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Test(s) for (both) save_settings and get_settings webservices
 */
class gugrades_base_testcase extends externallib_advanced_testcase {

    /**
     * @var object $course
     */
    protected $course;

    /**
     * @var object $teacher
     */
    protected $teacher;

    /**
     * @var object $student
     */
    protected $student;

    /**
     * @var object $student
     */
    protected $student2;

    /**
     * @var object $scale
     */
    protected $scale;

    /**
     * @var object $scaleb
     */
    protected $scaleb;

    /**
     * Get gradeitemid
     * @param string $itemtype
     * @param string $itemmodule
     * @param int $iteminstance
     * @return int
     */
    protected function get_grade_item(string $itemtype, string $itemmodule, int $iteminstance) {
        global $DB;

        $params = [
            'iteminstance' => $iteminstance,
        ];
        if ($itemtype) {
            $params['itemtype'] = $itemtype;
        }
        if ($itemmodule) {
            $params['itemmodule'] = $itemmodule;
        }
        $gradeitem = $DB->get_record('grade_items', $params, '*', MUST_EXIST);

        return $gradeitem->id;
    }

    /**
     * Fill local_gugrades_scalevalue table
     * @param array $scale
     * @param int $scaleid
     * @param string $type
     */
    protected function fill_scalevalue($scale, $scaleid, $type) {
        global $DB;

        foreach ($scale as $value => $item) {
            $scalevalue = new \stdClass;
            $scalevalue->scaleid = $scaleid;
            $scalevalue->item = trim($item);
            $scalevalue->value = $value;
            $DB->insert_record('local_gugrades_scalevalue', $scalevalue);
        }

        $scaletype = new \stdClass;
        $scaletype->scaleid = $scaleid;
        $scaletype->type = $type;
        $DB->insert_record('local_gugrades_scaletype', $scaletype);
    }

    /**
     * Add assignment grade
     * @param int $assignid
     * @param int $studentid
     * @param float $gradeval
     */
    protected function add_assignment_grade(int $assignid, int $studentid, float $gradeval) {
        global $USER, $DB;

        $submission = new \stdClass();
        $submission->assignment = $assignid;
        $submission->userid = $studentid;
        $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
        $submission->latest = 0;
        $submission->attemptnumber = 0;
        $submission->groupid = 0;
        $submission->timecreated = time();
        $submission->timemodified = time();
        $DB->insert_record('assign_submission', $submission);

        $grade = new \stdClass();
        $grade->assignment = $assignid;
        $grade->userid = $studentid;
        $grade->timecreated = time();
        $grade->timemodified = time();
        $grade->grader = $USER->id;
        $grade->grade = $gradeval;
        $grade->attemptnumber = 0;
        $DB->insert_record('assign_grades', $grade);
    }

    /**
     * Enable/disable dashboard for MyGrades
     * @param int $courseid
     * @param bool $disable
     */
    protected function disable_dashboard(int $courseid, bool $disable) {
        global $DB;

        $value = $disable ? 1 : 0;
        if ($config = $DB->get_record('local_gugrades_config', ['courseid' => $courseid, 'name' => 'disabledashboard'])) {
            $config->value = $value;
            $DB->update_record('local_gugrades_config', $config);
        } else {
            $config = new \stdClass();
            $config->courseid = $courseid;
            $config->gradeitemid = 0;
            $config->name = 'disabledashboard';
            $config->value = $value;
            $DB->insert_record('local_gugrades_config', $config);
        }
    }

    /**
     * Move grade item into specified category
     * @param int $gradeitemid
     * @param int $gradecategoryid
     */
    protected function move_gradeitem_to_category(int $gradeitemid, int $gradecategoryid) {
        global $DB;

        $gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $gradeitem->categoryid = $gradecategoryid;
        $DB->update_record('grade_items', $gradeitem);
    }

    /**
     * Called before every test
     * This sets up the basic course, scales and some users
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->resetAfterTest(true);

        // Create a course to apply settings to.
        $course = $this->getDataGenerator()->create_course();

        // Add a scale.
        // Range 1 to 23.
        $scaleitems = 'H:0, G2:1, G1:2, F3:3, F2:4, F1:5, E3:6, E2:7, E1:8, D3:9, D2:10, D1:11,
            C3:12, C2:13, C1:14, B3:15, B2:16, B1:17, A5:18, A4:19, A3:20, A2:21, A1:22';
        $scale = $this->getDataGenerator()->create_scale([
            'name' => 'UofG 22 point scale',
            'scale' => $scaleitems,
            'courseid' => $course->id,
        ]);
        $schedulea = [
            0 => 'H:0', 1 => 'G2:1', 2 => 'G1:2', 3 => 'F3:3', 4 => 'F2:4', 5 => 'F1:5', 6 => 'E3:6', 7 => 'E2:7', 8 => 'E1:8',
            9 => 'D3:9', 10 => 'D2:10', 11 => 'D1:11', 12 => 'C3:12', 13 => 'C2:13', 14 => 'C1:14', 15 => 'B3:15', 16 => 'B2:16',
            17 => 'B1:17', 18 => 'A5:18', 19 => 'A4:19', 20 => 'A3:20', 21 => 'A2:21', 22 => 'A1:22',
        ];
        $this->fill_scalevalue($schedulea, $scale->id, 'schedulea');
        $this->scale = $scale;

        // Add another scale.
        // Range 1 to 8.
        $scaleitemsb = 'H, G0, F0, E0, D0, C0, B0, A0';
        $scaleb = $this->getDataGenerator()->create_scale([
            'name' => 'UofG Schedule B',
            'scale' => $scaleitemsb,
            'courseid' => $course->id,
        ]);
        $scheduleb = [0 => 'H', 2 => 'G0', 5 => 'F0', 8 => 'E0', 11 => 'D0', 14 => 'C0', 17 => 'B0', 22 => 'A0'];
        $this->fill_scalevalue($scheduleb, $scaleb->id, 'scheduleb');
        $this->scaleb = $scaleb;

        // Add a teacher to the course.
        // Teacher will be logged in unless changed in tests.
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);

        // Add some students to the course.
        $student = $this->getDataGenerator()->create_user(['idnumber' => '1234567', 'firstname' => 'Fred', 'lastname' => 'Bloggs']);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $student2 = $this->getDataGenerator()->create_user(['idnumber' => '1234560', 'firstname' => 'Juan', 'lastname' => 'Perez']);
        $this->getDataGenerator()->enrol_user($student2->id, $course->id, 'student');

        $this->course = $course;
        $this->teacher = $teacher;
        $this->student = $student;
        $this->student2 = $student2;
    }
}
