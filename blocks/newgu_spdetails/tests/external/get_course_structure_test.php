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
 * Test getting the course structure.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024
 * @author     Greg Pedder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace block_newgu_spdetails\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/newgu_spdetails/tests/external/newgu_spdetails_advanced_testcase.php');

/**
 * Unit tests for the course structure that is returned.
 */
class get_course_structure_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {
    /**
     * Test of the components of the course that get returned.
     */
    public function test_get_course_structure() {
        $returned = $this->courseapi->get_course_structure([$this->mygradescourse], true);

        $this->assertIsArray($returned);
        $this->assertArrayHasKey('coursedata', $returned);
        $this->assertIsString($returned['coursedata'][0]['coursename']);
        $this->assertEquals($this->mygradescourse->shortname, $returned['coursedata'][0]['coursename']);

        $this->assertIsArray($returned['coursedata'][0]['subcategories']);
        $this->assertArrayHasKey('subcategories', $returned['coursedata'][0]);
        $this->assertEquals($this->mygrades_summativecategory->fullname, $returned['coursedata'][0]['subcategories'][0]['name']);
        $this->assertEquals('Summative', $returned['coursedata'][0]['subcategories'][0]['assessmenttype']);
        $this->assertEquals('0%', $returned['coursedata'][0]['subcategories'][0]['subcatweight']);
    }
}
