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
 * Test get_image_urls
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
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_advanced_testcase.php');

/**
 * Test get_image_urls
 */
final class get_image_urls_test extends \local_gugrades\external\gugrades_advanced_testcase {

    /**
     * Test that getting some image URLs works
     *
     * @covers \local_gugrades\external\resit_required::execute
     */
    public function test_urls(): void {

        // Get URL for some images
        $images = [
            ['imagename' => 'MyGradesLogoSmall', 'component' => 'local_gugrades']
        ];

        // Call external function
        $urls = get_image_urls::execute($this->course->id, $images);
        $urls = external_api::clean_returnvalue(
            get_image_urls::execute_returns(),
            $urls
        );

        $this->assertCount(1, $urls);
    }

}
