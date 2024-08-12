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
 * Unit tests for the Student Dashboard block plugin.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_newgu_spdetails\external;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot .'/config.php');
require_once($CFG->dirroot .'/blocks/moodleblock.class.php');
require_once($CFG->dirroot .'/blocks/newgu_spdetails/block_newgu_spdetails.php');

/**
 * Unit tests for block_newgu_spdetails
 */
class block_newgu_spdetails_test extends \advanced_testcase {

    /**
     * @var object $spdetails
     */
    protected $spdetails;

    /**
     * Called before every test
     */
    protected function setUp(): void
    {
        $this->resetAfterTest(true);
        $spdetails = new \block_newgu_spdetails();

        $this->spdetails = $spdetails;

        // The $PAGE object is never instantiated in our test conditions, hence...
        $page = new \moodle_page();
        $this->spdetails->page = $page;
    }

    /**
     * Check that has_config returns a bool
     */
    public function test_has_config() {
        $returned = $this->spdetails->has_config();
        $this->assertIsBool($returned);
    }

    /**
     * Test the applicable_formats() method.
     *
     * @return void
     */
    public function test_applicable_formats() {
        $returned = $this->spdetails->applicable_formats();
        $this->assertEquals($returned, ['admin' => true]);
    }

    /**
     * Test the get_content() method.
     *
     * @return void
     * @throws dml_exception
     */
    public function test_get_content() {

        $returned = $this->spdetails->get_content();
        $this->assertNotEmpty($returned->text);
    }
}
