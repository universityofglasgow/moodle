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
 * Test functions around releasing aggregated grades
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_aggregation_testcase.php');

/**
 * Test(s) around releaseing aggregated grades.
 */
final class release_aggregated_grades_test extends \local_gugrades\external\gugrades_aggregation_testcase {

    /**
     * @var int $gradeitemsecondx
     */
    protected int $gradeitemsecondx;

    /**
     * @var array $gradeitemids
     */
    protected array $gradeitemids;

    /**
     * @var object $gradecatsummative
     */
    protected object $gradecatsummative;

    /**
     * @var int $mapid
     */
    protected int $mapid;

    /**
     * Called before every test
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();

        // Install test schema.
        $this->gradeitemids = $this->load_schema('schema4');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

        // Make a conversion map.
        $this->mapid = $this->make_conversion_map();
    }

    /**
     * Checking basic (good) get page
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     * @return void
     */
    public function test_release_aggregated_grades(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data4a', $this->student->id);

        // Import ALL gradeitems.
        foreach ($this->gradeitemids as $gradeitemid) {
            $this->import_grades($this->course->id, $gradeitemid, $userlist);
        }

        // Get grade categoryid for summer exam
        $summercategoryid = $this->get_grade_category('Summer exam');

        // Convert summer exam.
        $nothing = select_conversion::execute($this->course->id, 0, $summercategoryid, $this->mapid);
        $nothing = external_api::clean_returnvalue(
            select_conversion::execute_returns(),
            $nothing
        );

        // Get the page.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['allowrelease']);

        // Get gradeitemid for summer exam.
        $summeritemid = $this->get_gradeitemid_from_grade_category('Summer exam');

        // Release aggregated grade "Summer Exam".
        $status = release_grades::execute($this->course->id, $summeritemid, 0, false);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Get resulting grades.
        $grades = $DB->get_records('local_gugrades_grade', ['courseid' => $this->course->id, 'gradeitemid' => $summeritemid]);

        // Released grades should be there (where there are grades to release).
        $grades = array_values($grades);
        $this->assertCount(3, $grades);
        $this->assertEquals('RELEASED', $grades[1]->gradetype);
        $this->assertEquals('A2', $grades[1]->displaygrade);

        // Get the page for the Summer exam. Should now have a released column.
        $page = get_aggregation_page::execute($this->course->id, $summercategoryid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['released']);
        $fred = $page['users'][0];
        $this->assertEquals('A2', $fred['releasegrade']);

        // Addition check for direct API call (used by Student MyGrades).
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $summercategoryid, $this->student->id);

        $this->assertEquals('A2', $user->parent->displaygrade);
        $this->assertTrue($user->parent->released);

        // Get the aggregation page for the parent (summative) category
        // To check that the child category is marked as released.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $columns = $page['columns'];
        $this->assertEquals('Summer exam', $columns[0]['shortname']);
        $this->assertTrue($columns[0]['released']);
        $this->assertEquals('Item 1', $columns[1]['shortname']);
        $this->assertFalse($columns[1]['released']);

        // Change one of the grades so the aggregated grade changes.
        $q2id = $this->get_gradeitemid('Question 2');
        $nothing = write_additional_grade::execute(
            courseid:           $this->course->id,
            gradeitemid:        $q2id,
            userid:             $this->student->id,
            reason:             'SECOND',
            other:              '',
            admingrade:         'NOSUBMISSION',
            scale:              0,
            grade:              0,
            notes:              'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get the page, should be difference.
        $page = get_aggregation_page::execute($this->course->id, $summercategoryid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['released']);
        $fred = $page['users'][0];
        $this->assertEquals('A2', $fred['releasegrade']);
        $this->assertEquals('NS', $fred['displaygrade']);
        $this->assertTrue($fred['mismatch']);

        // Addition check for direct API call (used by Student MyGrades).
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $summercategoryid, $this->student->id);

        $this->assertEquals('A2', $user->releasegrade);
        $this->assertEquals('NS', $user->displaygrade);
        $this->assertTrue($user->mismatch);
        $this->assertTrue($user->parent->released);

        // Release grades for a second time.
        $status = release_grades::execute($this->course->id, $summeritemid, 0, false);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Get resulting grades.
        $grades = $DB->get_records('local_gugrades_grade', ['courseid' => $this->course->id, 'gradeitemid' => $summeritemid]);

        // Get the page. Released and displaygrades should match once again.
        $page = get_aggregation_page::execute($this->course->id, $summercategoryid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['released']);
        $fred = $page['users'][0];
        $this->assertEquals('NS', $fred['releasegrade']);
        $this->assertEquals('NS', $fred['displaygrade']);

        // Addition check for direct API call (used by Student MyGrades).
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $summercategoryid, $this->student->id);

        $this->assertEquals('NS', $user->releasegrade);
        $this->assertEquals('NS', $user->displaygrade);
        $this->assertFalse($user->mismatch);
        $this->assertTrue($user->parent->released);

        // Revert release.
        $status = release_grades::execute($this->course->id, $summeritemid, 0, true);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Get the page. Again.
        $page = get_aggregation_page::execute($this->course->id, $summercategoryid, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['released']);
        $fred = $page['users'][0];
        $this->assertEquals('', $fred['releasegrade']);
        $this->assertEquals('NS', $fred['displaygrade']);

        // Addition check for direct API call (used by Student MyGrades).
        $user = \local_gugrades\api::get_aggregation_dashboard_user($this->course->id, $summercategoryid, $this->student->id);

        $this->assertEquals('', $user->releasegrade);
        $this->assertEquals('NS', $user->displaygrade);
        $this->assertFalse($user->mismatch);
        $this->assertFalse($user->parent->released);

        // Release grades for Item 1 to check that is flagged properly
        $item1id = $this->get_gradeitemid('Item 1');
        $status = release_grades::execute($this->course->id, $item1id, 0, false);
        $status = external_api::clean_returnvalue(
            release_grades::execute_returns(),
            $status
        );

        // Get the aggregation page for the parent (summative) category
        // To check that Item1 is marked as released.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $columns = $page['columns'];
        $this->assertEquals('Summer exam', $columns[0]['shortname']);
        $this->assertFalse($columns[0]['released']);
        $this->assertEquals('Item 1', $columns[1]['shortname']);
        $this->assertTrue($columns[1]['released']);
    }
}
