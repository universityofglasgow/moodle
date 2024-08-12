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
 * Web Service to return the assessments for a given student
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * This class provides the web service description for returning assessments, otherwise known as activities.
 */
class get_assessments extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'activetab' => new external_value(PARAM_ALPHA, 'The active tab', VALUE_DEFAULT),
            'page' => new external_value(PARAM_INT, 'The page number', VALUE_DEFAULT),
            'sortby' => new external_value(PARAM_ALPHA, 'Sort columns by', VALUE_DEFAULT),
            'sortorder' => new external_value(PARAM_ALPHA, 'Sort by order', VALUE_DEFAULT),
            'subcategory' => new external_value(PARAM_INT, 'Subcategory id', VALUE_DEFAULT),
        ]);
    }

    /**
     * Return the assessments.
     *
     * @param string $activetab
     * @param int $page
     * @param string $sortby
     * @param string $sortorder
     * @param int $subcategory
     * @return array of assessments, grouped by course.
     * @throws \invalid_parameter_exception
     */
    public static function execute(string $activetab, int $page, string $sortby, string $sortorder,
    int $subcategory = null): array {
        $params = self::validate_parameters(self::execute_parameters(),
            [
                'activetab' => $activetab,
                'page' => $page,
                'sortby' => $sortby,
                'sortorder' => $sortorder,
                'subcategory' => $subcategory,
            ]);
        return [
            'result' => json_encode(
                \block_newgu_spdetails\api::retrieve_assessments(
                    $params['activetab'],
                    $params['page'],
                    $params['sortby'],
                    $params['sortorder'],
                    $params['subcategory']
            )),
        ];
    }

    /**
     * Describes what will be returned to the caller.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'result' => new external_value(PARAM_TEXT, 'The course structure in JSON format'),
        ]);
    }
}
