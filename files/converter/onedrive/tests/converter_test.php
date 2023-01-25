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
 * Base class for unit tests for fileconverter_onedrive.
 *
 * @package    fileconverter_onedrive
 * @copyright  2020 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace fileconverter_onedrive;

use advanced_testcase;

/**
 * Unit tests for fileconverter_onedrive/converter
 *
 * @copyright  2020 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group fileconverter_onedrive
 * @group uon
 */
class converter_test extends advanced_testcase {
    /**
     * Tests that the supports method works correctly.
     *
     * @param string $from
     * @param string $to
     * @param bool $expected
     * @dataProvider data_supports
     */
    public function test_supports(string $from, string $to, bool $expected): void {
        self::assertEquals($expected, converter::supports($from, $to));
    }

    /**
     * Data used to test the supports method.
     *
     * @return array
     */
    public function data_supports(): array {
        return [
            'lowercase' => ['docx', 'pdf', true],
            'uppercase' => ['DOCX', 'PDF', true],
            'invalid from' => ['invalidFileFormat', 'pdf', false],
            'invalid to' => ['docx', 'invalidFileFormat', false],
        ];
    }
}
