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
 * Test that the correct weighting for a given course 'type' is returned.
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
 * Test get_weight function.
 */
class get_weight_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    public function test_return_weight() {
        $aggregationcoef = 10;
        $expected1 = round($aggregationcoef, 2);
        $this->assertEquals($expected1, $this->courseapi->return_weight($aggregationcoef));

        $aggregationcoef = 1;
        $expected2 = round($aggregationcoef * 100, 2);
        $this->assertEquals($expected2, $this->courseapi->return_weight($aggregationcoef));

        $aggregationcoef = 0;
        $expected3 = 0;
        $this->assertEquals($expected3, $this->courseapi->return_weight($aggregationcoef));
    }
}
