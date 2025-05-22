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
 * Parent class from which all other test cases can extend.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace block_newgu_spdetails\external;

use externallib_advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Base class from which all other test classes can extend. Creates a basic MyGrades type course,
 * enrols a teacher and some students. Extending classes can then modify the courses as they see fit,
 * e.g adding categories/sub categories/activities etc.
 */
class newgu_spdetails_base_testcase extends externallib_advanced_testcase {

    /**
     * @var object $api
     */
    protected $api;

    /**
     * @var object $courseapi
     */
    protected $courseapi;

    /**
     * @var object $activityapi
     */
    protected $activityapi;

    /**
     * @var object $gradeapi
     */
    protected $gradeapi;

    /**
     * @var object $mygradescourse
     */
    protected $mygradescourse;

    /**
     * @var object $teacher
     */
    protected $teacher;

    /**
     * @var object $student1
     */
    protected $student1;

    /**
     * @var object $student2
     */
    protected $student2;

    /**
     * @var object $student3
     */
    protected $student3;

    /**
     * @var object $student4
     */
    protected $student4;

    /**
     * @var object $scale
     */
    protected $scale;

    /**
     * Add assignment grade
     * @param int $assignid
     * @param int $studentid
     * @param int $graderid
     * @param float $gradeval
     * @param string $status
     */
    protected function add_assignment_grade(int $assignid, int $studentid, int $graderid, float $gradeval,
    string $status = ASSIGN_SUBMISSION_STATUS_NEW) {
        global $DB;

        $submission = new \stdClass();
        $submission->assignment = $assignid;
        $submission->userid = $studentid;
        $submission->status = $status;
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
        $grade->grader = $graderid;
        $grade->grade = $gradeval;
        $grade->attemptnumber = 0;
        $DB->insert_record('assign_grades', $grade);
    }

    /**
     * Utility function to provide the roleId.
     *
     * @param string $archetype
     * @return int
     * @throws dml_exception
     */
    public function get_roleid(string $archetype = 'student'): int {
        global $DB;

        $role = $DB->get_record("role", ['archetype' => $archetype]);
        return $role->id;
    }

    /**
     * Check for MyGrades custom course category and field
     * @var object $mygradescourse
     * @var object $context
     */
    protected function custom_course_field($mygradescourse, $context) {
        global $DB;

        $category = new \stdClass;
        $category->name = 'Student MyGrades';
        $category->descriptionformat = 0;
        $category->sortorder = 0;
        $category->component = 'core_course';
        $category->area = 'course';
        $category->itemid = 0;
        $category->contextid = 1;
        $category->timecreated = time();
        $category->timemodified = time();
        $categoryid = $DB->insert_record('customfield_category', $category);

        $field = new \stdClass;
        $field->shortname = 'studentmygrades';
        $field->name = 'Enable Student MyGrades';
        $field->type = 'checkbox';
        $field->description = 'Your text here';
        $field->descriptionformat = 1;
        $field->sortorder = 0;
        $field->categoryid = $categoryid;
        $field->configdata = json_encode((object) [
            'required' => '0',
            'uniquevalues' => '0',
            'checkbydefault' => '0',
            'locked' => '0',
            'visibility' => '2',
        ]);
        $field->timecreated = time();
        $field->timemodified = time();
        $DB->insert_record('customfield_field', $field);

        // Find the custom field.
        $field = $DB->get_record('customfield_field', ['shortname' => 'studentmygrades'], '*', MUST_EXIST);
        $data = new \stdClass;
        $data->fieldid = $field->id;
        $data->instanceid = $mygradescourse->id;
        $data->intvalue = 1;
        $data->value = 1;
        $data->valueformat = 0;
        $data->valuetrust = 0;
        $data->timecreated = time();
        $data->timemodified = time();
        $data->context = $context;
        $DB->insert_record('customfield_data', $data);
    }

    /**
     * Called before every test.
     * This sets up the basic course, scales and some users.
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->resetAfterTest(true);

        // Create some oft used objects.
        $api = new \block_newgu_spdetails\api();
        $this->api = $api;

        $courseapi = new \block_newgu_spdetails\course();
        $this->courseapi = $courseapi;

        $activityapi = new \block_newgu_spdetails\activity();
        $this->activityapi = $activityapi;

        $gradeapi = new \block_newgu_spdetails\grade();
        $this->gradeapi = $gradeapi;

        // Some dates for our mock course.
        $startdate = mktime(0, 0, 0, 0, 1, date("Y"));
        $enddate  = mktime(0, 0, 0, date("m"), date("d"), date("Y") + 1);

        // Create the base MyGrades type course.
        $mygradescourse = $this->getDataGenerator()->create_course([
            'fullname' => 'MyGrades Test Course',
            'shortname' => 'MGTC1',
            'startdate' => $startdate,
            'enddate' => $enddate,
        ]);

        // This requires further mock "enabling" this as a MyGrades type course.
        $mygradesparams = [
            'courseid' => $mygradescourse->id,
            'name' => 'enabledashboard',
            'value' => 1,
        ];
        $DB->insert_record('local_gugrades_config', $mygradesparams);

        // Create some context.
        $mygradescontext = \context_course::instance($mygradescourse->id);

        // Create the MyGrades specific custom course field - normally done when visiting MyGrades w/in a course.
        $this->custom_course_field($mygradescourse, $mygradescontext);

        // Add some scales that this course can use.
        // Range 1 to 23.
        $scaleitems = 'H:0, G2:1, G1:2, F3:3, F2:4, F1:5, E3:6, E2:7, E1:8, D3:9, D2:10, D1:11,
            C3:12, C2:13, C1:14, B3:15, B2:16, B1:17, A5:18, A4:19, A3:20, A2:21, A1:22';
        $scale = $this->getDataGenerator()->create_scale([
            'name' => 'UofG 22 point scale',
            'scale' => $scaleitems,
            'courseid' => $mygradescourse->id,
        ]);

        // Set up, enrol and assign a role for the teacher...
        $teacher = $this->getDataGenerator()->create_user(['email' => 'teacher1@example.co.uk', 'username' => 'teacher1']);
        $this->getDataGenerator()->enrol_user($teacher->id, $mygradescourse->id, $this->get_roleid('editingteacher'));
        $this->getDataGenerator()->role_assign('editingteacher', $teacher->id, $mygradescontext);

        // Set up, enrol and assign a role for some fake students...
        $student1 = $this->getDataGenerator()->create_user(['email' => 'student1@example.co.uk', 'username' => 'student1']);
        $this->getDataGenerator()->enrol_user($student1->id, $mygradescourse->id, $this->get_roleid());
        $this->getDataGenerator()->role_assign('student', $student1->id, $mygradescontext);

        $student2 = $this->getDataGenerator()->create_user(['email' => 'student2@example.co.uk', 'username' => 'student2']);
        $this->getDataGenerator()->enrol_user($student2->id, $mygradescourse->id, $this->get_roleid());
        $this->getDataGenerator()->role_assign('student', $student2->id, $mygradescontext);

        $student3 = $this->getDataGenerator()->create_user(['email' => 'student3@example.co.uk', 'username' => 'student3']);
        $this->getDataGenerator()->enrol_user($student3->id, $mygradescourse->id, $this->get_roleid());
        $this->getDataGenerator()->role_assign('student', $student3->id, $mygradescontext);

        $student4 = $this->getDataGenerator()->create_user(['email' => 'student4@example.co.uk', 'username' => 'student4']);
        $this->getDataGenerator()->enrol_user($student4->id, $mygradescourse->id, $this->get_roleid());
        $this->getDataGenerator()->role_assign('student', $student4->id, $mygradescontext);

        // Finally assign everything to our main object.
        $this->mygradescourse = $mygradescourse;
        $this->scale = $scale;
        $this->teacher = $teacher;
        $this->student1 = $student1;
        $this->student2 = $student2;
        $this->student3 = $student3;
        $this->student4 = $student4;
    }
}
