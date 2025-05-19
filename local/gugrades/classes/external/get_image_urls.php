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
 * Get urls of Moodle images
 * @package    local_gugrades
 * @copyright  2025
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Get image urls
 */
class get_image_urls extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'images' => new external_multiple_structure(
                new external_single_structure([
                    'imagename' => new external_value(PARAM_TEXT, 'Image name'),
                    'component' => new external_value(PARAM_TEXT, 'Component'),
                ])
            )
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param array $images
     * @return array
     */
    public static function execute($courseid, $images) {

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'images' => $images,
        ]);

        $urls = \local_gugrades\api::get_image_urls($courseid, $images);

        return $urls;
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'url' => new external_value(PARAM_URL, 'Image URL'),
            ])
        );
    }

}
