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
 * Are there inactive users enrolled on this course.
 *
 * This test checks for users that have been inactive on a course.
 * Initially, we determine this by if the last login was greater than 1 year.
 *
 * @package    report_coursediagnositc
 * @copyright  2023 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;
class course_inactiveenrolment_test implements \report_coursediagnostic\course_diagnostic_interface {

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
        global $PAGE, $CFG;
        require_once("$CFG->dirroot/enrol/locallib.php");

        $manager = new \course_enrolment_manager($PAGE, $this->course, null, '', '', 0, -1);
        $users = $manager->get_users('id');

        $counter = 0;
        $inactiveusers = false;
        $participantsurl = new \moodle_url('/user/index.php', ['id' => $this->course->id]);
        $participantslink = \html_writer::link($participantsurl, get_string('participants_link_text', 'report_coursediagnostic'));
        if (!empty($users)) {
            // As the criteria needs firming up a bit, last access is starting at 90 days.
            $now = new \DateTimeImmutable(date('Y-m-d'));
            foreach ($users as $user) {
                if ($user->lastcourseaccess > 0) {
                    $lastaccessed = new \DateTimeImmutable(userdate($user->lastcourseaccess));
                    $interval = $lastaccessed->diff($now);
                }

                if (($user->lastcourseaccess == 0) || (isset($interval) && $interval->days >= 90)) {
                    $counter++;
                    $inactiveusers = true;
                }

                $interval = null;
            }
        }

        // Yes, we are inverting our result. We want to return false if there
        // are inactive users, thereby failing. But we want to return true,
        // thereby passing, if there aren't any inactive users.
        $this->testresult = [
            'testresult' => !$inactiveusers,
            'participantslink' => $participantslink,
            'inactiveusercount' => $counter,
            'word1' => (($counter > 1) ? get_string('plural_5', 'report_coursediagnostic') :
                get_string('singular_5', 'report_coursediagnostic')),
            'word2' => (($counter > 1) ? get_string('plural_6', 'report_coursediagnostic') :
                get_string('singular_6', 'report_coursediagnostic')),
            'word3' => (($counter > 1) ? get_string('plural_2', 'report_coursediagnostic') :
                get_string('singular_2', 'report_coursediagnostic'))
        ];

        return $this->testresult;
    }
}
