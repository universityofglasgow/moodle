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
 * Unit tests for the block_newgu_spdetails class.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot .'/blocks/moodleblock.class.php');
require_once($CFG->dirroot . '/blocks/newgu_spdetails/tests/external/newgu_spdetails_advanced_testcase.php');

/**
 * Unit tests for retrieving grade, status and feedback.
 */
class get_grade_status_and_feedback_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * For a MyGrades course - we have the situation where if grades
     * haven't been imported/released, then it defaults to retrieving
     * them from gradebook. These tests should account for this, i.e.
     * as we're only dealing with released grades from local_gugrades -
     * there isn't a notion of provisional grades.
     */
    public function test_get_grade_status_and_feedback_mygrades() {
        $userid = $this->student1->id;
        $sortorder = 'asc';

        $mygradessummativesubcategory2 = $this->mygrades_summative_subcategory2->id;
        $mygradesgradeditems = $this->lib->retrieve_gradable_activities('current', $userid, 'duedate', $sortorder,
        $mygradessummativesubcategory2);

        $this->assertIsArray($mygradesgradeditems);
        $this->assertCount(2, $mygradesgradeditems['coursedata']['assessmentitems']);

        // Check for the raw grade/provisional on the first assignment.
        $this->assertArrayHasKey('grade_provisional', $mygradesgradeditems['coursedata']['assessmentitems'][0]);
        $this->assertTrue($mygradesgradeditems['coursedata']['assessmentitems'][0]['grade_provisional']);
        // Check for the feedback.
        $this->assertStringContainsString(get_string('status_text_tobeconfirmed', 'block_newgu_spdetails'),
        $mygradesgradeditems['coursedata']['assessmentitems'][0]['grade_feedback']);

        // Check for an overridden grade.
        // Check for the feedback.

        // Check for the final grade.
        $this->assertArrayHasKey('grade_class', $mygradesgradeditems['coursedata']['assessmentitems'][1]);
        $this->assertFalse($mygradesgradeditems['coursedata']['assessmentitems'][1]['grade_provisional']);
        // Check for the feedback.
        $this->assertStringContainsString(get_string('status_text_viewfeedback', 'block_newgu_spdetails'),
        $mygradesgradeditems['coursedata']['assessmentitems'][1]['grade_feedback']);
    }

    /**
     * For generic Gradebook courses, the data should be coming directly
     * from gradebook.
     */
    public function test_get_grade_status_and_feedback_gradebook() {
        $userid = $this->student1->id;
        $sortorder = 'asc';

        $gradebookcategory = $this->gradebookcategory->id;
        $gradebookgradeditems = $this->lib->retrieve_gradable_activities('current', $userid, 'duedate', $sortorder,
        $gradebookcategory);

        $this->assertIsArray($gradebookgradeditems);
        $this->assertCount(2, $gradebookgradeditems['coursedata']['assessmentitems']);

        // Check for the raw grade/provisional on the first assignment.
        $this->assertArrayHasKey('grade_provisional', $gradebookgradeditems['coursedata']['assessmentitems'][0]);
        $this->assertTrue($gradebookgradeditems['coursedata']['assessmentitems'][1]['grade_provisional']);
        // Check for the feedback.
        $this->assertStringContainsString(get_string('status_text_tobeconfirmed', 'block_newgu_spdetails'),
        $gradebookgradeditems['coursedata']['assessmentitems'][1]['grade_feedback']);

        // Check for an overridden grade.
        // Check for the feedback.

        // Check for the final grade.
        $this->assertArrayHasKey('grade_class', $gradebookgradeditems['coursedata']['assessmentitems'][1]);
        $this->assertFalse($gradebookgradeditems['coursedata']['assessmentitems'][0]['grade_provisional']);
        // Check for the feedback.
        $this->assertStringContainsString(get_string('status_text_viewfeedback', 'block_newgu_spdetails'),
        $gradebookgradeditems['coursedata']['assessmentitems'][0]['grade_feedback']);
    }
}
