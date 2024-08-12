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
 * Unit tests for gradable activities.
 */
class get_gradable_activities_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * Test that only current courses are returned.
     * Course "type" is irrelevant for this test - so we just pick a type.
     */
    public function test_retrieve_gradable_activities_current_courses() {
        $userid = $this->student1->id;
        $sortorder = 'asc';
        $mygradessummativecategoryid = $this->mygrades_summativecategory->id;
        $returned = $this->lib->retrieve_gradable_activities('current', $userid, 'duedate', $sortorder, $mygradessummativecategoryid);

        $this->assertIsArray($returned);
        $this->assertArrayHasKey('coursedata', $returned);
        $this->assertCount(1, $returned['coursedata']['assessmentitems']);
    }

    /**
     * Test that only past courses are returned.
     */
    public function test_retrieve_gradable_activities_past_courses() {
        $userid = $this->student1->id;
        $sortorder = 'asc';
        $summativecategorypastid = $this->summativecategory_past->id;
        $returned = $this->lib->retrieve_gradable_activities('past', $userid, 'duedate', $sortorder, $summativecategorypastid);

        $this->assertIsArray($returned);
        $this->assertArrayHasKey('coursedata', $returned);
        $this->assertCount(1, $returned['coursedata']['assessmentitems']);
    }

    /**
     * Test the different course types that can be in use in the system
     */
    public function test_retrieve_gradable_activities_by_course_type() {
        $userid = $this->student1->id;
        $sortorder = 'asc';

        // MyGrades course type.
        $mygradessummativesubcategoryid = $this->mygrades_summative_subcategory->id;
        $returned = $this->lib->retrieve_gradable_activities('current', $userid, 'duedate', $sortorder,
        $mygradessummativesubcategoryid);
        $this->assertEquals($this->mygradescourse->mygradesenabled,
        $returned['coursedata']['assessmentitems'][0]['mygradesenabled']);

        // Gradebook course type.
        $gradebookcategoryid = $this->gradebookcategory->id;
        $returned = $this->lib->retrieve_gradable_activities('current', $userid, 'duedate', $sortorder, $gradebookcategoryid);
        $this->assertEquals(true, $returned['coursedata']['assessmentitems'][0]['gradebookenabled']);
    }
}
