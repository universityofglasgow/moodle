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
 * Test functions around resits
 * @package    local_gugrades
 * @copyright  2025
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
 * Test(s) for resit web services
 */
final class resits_test extends \local_gugrades\external\gugrades_aggregation_testcase {

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
        $this->gradeitemids = $this->load_schema('schema12_resits');

        // Get the grade category 'summative'.
        $this->gradecatsummative = $DB->get_record('grade_categories', ['fullname' => 'Summative'], '*', MUST_EXIST);

        // Make a conversion map.
        $this->mapid = $this->make_conversion_map();
    }

    /**
     * Checking getting tree structure for summative and simple case of setting grade item as resit.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     * @return void
     */
    public function test_resit_with_simple_grade_items(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Test get_activities web service. 
        $treejson = get_activities::execute($this->course->id, $this->gradecatsummative->id, true);
        $treejson = external_api::clean_returnvalue(
            get_activities::execute_returns(),
            $treejson
        );
        $tree = json_decode($treejson['activities']);

        // There should be resit candidates. 
        $this->assertTrue($tree->anyresitcandidates);

        // Start by examining 'Summer exam', which has two simple grade items.
        $summerexam = $tree->categories[0];
        $summerexamcat = $summerexam->category;

        // Should be a resit candidate but no resits.
        $this->assertTrue($summerexamcat->resitcandidate);
        $this->assertFalse($summerexamcat->resititemid);

        // Set the resit item.
        $resit = $summerexam->items[1];
        $this->assertEquals('Summer resit', $resit->itemname);
        $resitid = $resit->id;

        $result = save_resit_item::execute($this->course->id, $resitid, true);
        $result = external_api::clean_returnvalue(
            save_resit_item::execute_returns(),
            $result
        );

        // Get the updated data
        $treejson = get_activities::execute($this->course->id, $this->gradecatsummative->id, true);
        $treejson = external_api::clean_returnvalue(
            get_activities::execute_returns(),
            $treejson
        );
        $tree = json_decode($treejson['activities']);

        // Start by re-examining 'Summer exam', which has two simple grade items.
        $summerexam = $tree->categories[0];
        $summerexamcat = $summerexam->category;

        // Should be a resit candidate WITH resit set.
        $this->assertTrue($summerexamcat->resitcandidate);
        $this->assertEquals($resitid, $summerexamcat->resititemid);
    }

    /**
     * Checking getting tree structure for summative and simple case of a mix of
     * simple grade items and sub-category.
     *
     * @covers \local_gugrades\external\get_aggregation_page::execute
     * @return void
     */
    public function test_resit_with_complex_grade_items(): void {
        global $DB;

        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Test get_activities web service. 
        $treejson = get_activities::execute($this->course->id, $this->gradecatsummative->id, true);
        $treejson = external_api::clean_returnvalue(
            get_activities::execute_returns(),
            $treejson
        );
        $tree = json_decode($treejson['activities']);

        // Look at 'Scale exam'
        $scaleexam = $tree->categories[1];
        $scaleexamcat = $scaleexam->category;
        $this->assertEquals('Scale exam', $scaleexamcat->fullname);
        $this->assertTrue($scaleexamcat->resitcandidate);
        $this->assertFalse($scaleexamcat->resititemid);

        // Category that could be a resit.
        $scaleresit = $scaleexam->categories[0];
        $this->assertEquals('Scale resit', $scaleresit->category->fullname);
        $resitid = $scaleresit->category->itemid;

        // Set this as the resit item.
        $result = save_resit_item::execute($this->course->id, $resitid, true);
        $result = external_api::clean_returnvalue(
            save_resit_item::execute_returns(),
            $result
        );

        // Get the updated data
        $treejson = get_activities::execute($this->course->id, $this->gradecatsummative->id, true);
        $treejson = external_api::clean_returnvalue(
            get_activities::execute_returns(),
            $treejson
        );
        $tree = json_decode($treejson['activities']);

        // Re-check scale exam.
        $scaleexam = $tree->categories[1];
        $scaleexamcat = $scaleexam->category;
        $this->assertEquals('Scale exam', $scaleexamcat->fullname);
        $this->assertTrue($scaleexamcat->resitcandidate);
        $this->assertEquals($resitid, $scaleexamcat->resititemid);
    }    

    /**
     * Check aggregation works as expected with combinations of resit selected
     * grades and admin grades.
     */
    public function test_resit_aggregation(): void {
        
        // Make sure that we're a teacher.
        $this->setUser($this->teacher);

        // Set 'Summer resit' inside 'Summer exam' as resit.
        $summerfirstsittingid = $this->get_gradeitemid('Summer first sitting');
        $summerresitid = $this->get_gradeitemid('Summer resit');
        $result = save_resit_item::execute($this->course->id, $summerresitid, true);
        $result = external_api::clean_returnvalue(
            save_resit_item::execute_returns(),
            $result
        );

        // Write some random grade into 1st sitting. 
        // As resit is a missing grade then this should be the aggregated result
        $nothing = write_additional_grade::execute(
            courseid:          $this->course->id,
            gradeitemid:       $summerfirstsittingid,
            userid:            $this->student->id,
            reason:            'SECOND',
            other:             '',
            admingrade:        '',
            scale:             0,
            grade:             52,
            notes:             'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page for above 'Summer exam'.
        $summerexamcatid = $this->get_grade_category('Summer exam');
        $page = get_aggregation_page::execute($this->course->id, $summerexamcatid, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // Aggregated result should be the grade that exists 
        $fred = $page['users'][0];
        $this->assertEquals(52, $fred['displaygrade']);

        // Add a resit grade.
        $nothing = write_additional_grade::execute(
            courseid:          $this->course->id,
            gradeitemid:       $summerresitid,
            userid:            $this->student->id,
            reason:            'SECOND',
            other:             '',
            admingrade:        '',
            scale:             0,
            grade:             75,
            notes:             'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get aggregation page for above 'Summer exam'.
        $summerexamcatid = $this->get_grade_category('Summer exam');
        $page = get_aggregation_page::execute($this->course->id, $summerexamcatid, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // Aggregated result should simply be hightest grade.
        $fred = $page['users'][0];
        $this->assertEquals(75, $fred['displaygrade']);

        // Change first sitting grade to EC.
        $nothing = write_additional_grade::execute(
            courseid:          $this->course->id,
            gradeitemid:       $summerfirstsittingid,
            userid:            $this->student->id,
            reason:            'SECOND',
            other:             '',
            admingrade:        'GOODCAUSE_FO',
            scale:             0,
            grade:             0,
            notes:             'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get updated aggregation page.
        $summerexamcatid = $this->get_grade_category('Summer exam');
        $page = get_aggregation_page::execute($this->course->id, $summerexamcatid, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // Should still be 75 despite the EC. The resit "wins".
        $fred = $page['users'][0];
        $this->assertEquals(75, $fred['displaygrade']);

        // Change resit grade to NS.
        $nothing = write_additional_grade::execute(
            courseid:          $this->course->id,
            gradeitemid:       $summerresitid,
            userid:            $this->student->id,
            reason:            'SECOND',
            other:             '',
            admingrade:        'NOSUBMISSION',
            scale:             0,
            grade:             0,
            notes:             'Test notes'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );

        // Get updated aggregation page.
        $summerexamcatid = $this->get_grade_category('Summer exam');
        $page = get_aggregation_page::execute($this->course->id, $summerexamcatid, '', '', 0, false);
        $page = external_api::clean_returnvalue(
            get_aggregation_page::execute_returns(),
            $page
        );

        // Should now be NS.
        $fred = $page['users'][0];
        $this->assertEquals('NS', $fred['displaygrade']);
    }
}
