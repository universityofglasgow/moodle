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
 * Test of the language string settings.
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
 * Unit tests for activity types.
 */
class get_assessment_type_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * Test of the language string settings against mock assessment types and weighting.
     */
    public function test_return_assessmenttype() {
        $lang = 'block_newgu_spdetails';
        $expected1 = get_string("formative", $lang);
        $expected2 = get_string("summative", $lang);
        $expected3 = get_string("emptyvalue", $lang);

        $this->assertEquals($expected1, $this->courseapi->return_assessmenttype("12312 formative", 0));
        $this->assertEquals($expected2, $this->courseapi->return_assessmenttype("12312 summative", 1));
        $this->assertEquals($expected2, $this->courseapi->return_assessmenttype("123123 summative", 0));
        $this->assertEquals($expected3, $this->courseapi->return_assessmenttype(time(), 0));
    }
}
