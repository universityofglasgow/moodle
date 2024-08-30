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
 * Test functions around get_aggregation_page
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
 * Test(s) for get_aggregation_page webservice
 */
final class get_aggregation_page_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
        $this->gradeitemids = $this->load_schema('schema1');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

        // Make a conversion map.
        $this->mapid = $this->make_conversion_map();
    }

    /**
     * Create default conversion map
     * @return int
     */
    protected function make_conversion_map() {

        // Read map with id 0 (new map) for Schedule A.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map';
        $schedule = 'schedulea';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapida = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapida = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapida
        );
        $mapida = $mapida['mapid'];

        return $mapida;
    }

    /**
     * Checking basic (good) get page
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     * @return void
     */
    public function test_basic_aggregation_page(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data1a', $this->student->id);

        // Import ALL gradeitems.
        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get first csv test string.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, true);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $users = $page['users'];
        $this->assertCount(2, $users);
        $juan = $users[1];
        $this->assertEquals('Grades missing', $juan['error']);
        $this->assertEquals('No data', $juan['fields'][2]['display']);
        $fred = $users[0];
        $this->assertEquals("47.23333", $fred['fields'][0]['display']);

        // Get page again but without aggregation step.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $users = $page['users'];
        $this->assertCount(2, $users);
        $juan = $users[1];
        $this->assertEquals('Grades missing', $juan['error']);
        $this->assertEquals('No data', $juan['fields'][2]['display']);
        $fred = $users[0];
        $this->assertEquals("47.23333", $fred['fields'][0]['display']);

        // Test aggregation recalculate
        $nothing = recalculate::execute($this->course->id, $this->gradecatsummative->id);
        $nothing = external_api::clean_returnvalue(
            recalculate::execute_returns(),
            $nothing
        );

        // Get page again after recalculation
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // Nothing should have changed
        $users = $page['users'];
        $this->assertCount(2, $users);
        $juan = $users[1];
        $this->assertEquals('Grades missing', $juan['error']);
        $this->assertEquals('No data', $juan['fields'][2]['display']);
        $fred = $users[0];
        $this->assertEquals("47.23333", $fred['fields'][0]['display']);
    }

    /**
     * Incomplete data to check completion percentage
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_completion_score(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data1b', $this->student->id);

        // Import ALL gradeitems.
        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get first csv test string.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals("0", $fred['completed']);
        $this->assertEquals("Grades missing", $fred['error']);

        // Convert
        // Apply the test conversion map to all items.
        foreach ($this->gradeitemids as $gradeitemid) {
            $nothing = select_conversion::execute($this->course->id, $gradeitemid, 0, $this->mapid);
            $nothing = external_api::clean_returnvalue(
                select_conversion::execute_returns(),
                $nothing
            );
        }

        // Get aggregated page, now it should all be Schedule A.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals("29", $fred['completed']);
        $this->assertEquals('C2', $fred['fields'][2]['display']);

        // Add an admin grade.
        $item3 = $DB->get_record('grade_items', ['courseid' => $this->course->id, 'itemname' => 'Item 3'], '*', MUST_EXIST);
        $nothing = write_additional_grade::execute(
            $this->course->id,
            $item3->id,
            $this->student->id,
            'SECOND',
            '',
            'MV',
            0,
            0,
            'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregated page, now it should now reflect admin grade.
        $page = get_aggregation_page::execute($this->course->id, $this->gradecatsummative->id, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $fred = $page['users'][0];
        $this->assertEquals("14", $fred['completed']);
        $this->assertEquals('MV', $fred['fields'][4]['display']);
    }

    /**
     * Test sub-category aggregated data
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_sub_category(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data1b', $this->student->id);

        // Import ALL gradeitems.
        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get category id for grade category 'Summer exam'.
        $summerexamid = $this->get_grade_category('Summer exam');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $summerexamid, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('P', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(47.23333, $fred['total']);
        $this->assertEquals('', $fred['error']);
    }

    /**
     * Test sub-category aggregated data with Schedule B
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_scheduleb_sub_category(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data1a', $this->student->id);

        // Import ALL gradeitems.
        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get category id for grade category 'Schedule B exam'.
        $scaleexamid = $this->get_grade_category("Schedule B exam");

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $scaleexamid, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('B', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(100, $fred['completed']);
        $this->assertEquals("D0", $fred['displaygrade']);
        $this->assertEquals(11.70588, $fred['rawgrade']);
        $this->assertEquals(11, $fred['total']);
    }

    /**
     * Test sub-category aggregated data with Schedule A
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_schedulea_sub_category(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data1a', $this->student->id);

        // Import ALL gradeitems.
        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get category id for grade category 'Schedule B exam'.
        $scaleexamid = $this->get_grade_category('Scale exam');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $scaleexamid, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertFalse($page['toplevel']);
        $this->assertEquals('A', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(100, $fred['completed']);
        $this->assertEquals("C1", $fred['displaygrade']);
        $this->assertEquals(13.58974, $fred['rawgrade']);
        $this->assertEquals(14.0, $fred['total']);
    }

    /**
     * Test top-level aggregation, Schedule B only
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_scheduleb(): void {

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data1b', $this->student->id);

        // Import ALL gradeitems.
        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get category id for grade category 'Schedule B exam'.
        $summativebid = $this->get_grade_category('SummativeB');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $summativebid, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        $this->assertTrue($page['toplevel']);
        $this->assertEquals('B', $page['atype']);
        $fred = $page['users'][0];
        $this->assertEquals(100, $fred['completed']);
        $this->assertEquals("B0 (15.5)", $fred['displaygrade']);
        $this->assertEquals(15.5, $fred['rawgrade']);
        $this->assertEquals(17, $fred['total']);
    }

    /**
     * Test get_add_grade_form for aggregated categories
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     */
    public function test_override_category(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Import grades only for one student (so far).
        $userlist = [
            $this->student->id,
        ];

        // Install test data for student.
        $this->load_data('data1a', $this->student->id);

        // Import ALL gradeitems.
        foreach ($this->gradeitemids as $gradeitemid) {
            $status = import_grades_users::execute($this->course->id, $gradeitemid, false, false, $userlist);
            $status = external_api::clean_returnvalue(
                import_grades_users::execute_returns(),
                $status
            );
        }

        // Get categoryid for 'Scale exam' which should be Schedule A
        $scaleexamid = $this->get_grade_category('Scale exam');

        // Get aggregation page for above.
        $page = get_aggregation_page::execute($this->course->id, $scaleexamid, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // Get corresponding itemid for Scaleexam
        $scaleexamitem = $DB->get_record('grade_items', ['itemtype' => 'category', 'iteminstance' => $scaleexamid], '*', MUST_EXIST);

        // Get the corresponding form for this category
        $form = get_add_grade_form::execute($this->course->id, $scaleexamitem->id, $this->student->id);
        $form = external_api::clean_returnvalue(
            get_add_grade_form::execute_returns(),
            $form
        );

        // This should reflect Schedule A.
        $this->assertTrue($form['usescale']);
        $this->assertEquals('Scale exam', $form['itemname']);
        $this->assertCount(23, $form['scalemenu']);
        $this->assertCount(9, $form['gradetypes']);
        $this->assertCount(5, $form['adminmenu']);
    }
}
