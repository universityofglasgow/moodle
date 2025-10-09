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
 * Web Service to return the assessments due soon for a given student
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * This class provides the web service description for returning assessments that are due in the near future.
 */
class get_assessmentsduesoon extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            // No params needed at this time.
        ]);
    }

    /**
     * Return the assessments due in the next 24 hours, 1 week and 1 month.
     *
     * We probably want to cache this on something like a 5 minute basis,
     * given that the service gets called each time the user visits the
     * dashboard.
     *
     * @return array of assessments, grouped by return time.
     * @throws \invalid_parameter_exception
     */
    public static function execute(): array {

        $assessmentsduesoon = \block_newgu_spdetails\api::get_assessmentsduesoon();
        $duein24hours = $assessmentsduesoon['duein24hours'];
        $duein7days = $assessmentsduesoon['duein7days'];
        $duein14days = $assessmentsduesoon['duein14days'];
        $duein1month = $assessmentsduesoon['duein1month'];

        $stats[] = [
            'duein24hours' => $duein24hours,
            'duein7days' => $duein7days,
            'duein14days' => $duein14days,
            'duein1month' => $duein1month,
        ];

        return $stats;
    }

    /**
     * Describes what will be returned to the caller.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'duein24hours' => new external_value(PARAM_INT, 'due in the next 24 hours'),
                'duein7days' => new external_value(PARAM_INT, 'due in the next week'),
                'duein14days' => new external_value(PARAM_INT, 'due in the next 14 days'),
                'duein1month' => new external_value(PARAM_INT, 'due by the end of the month'),
            ])
        );
    }
}
