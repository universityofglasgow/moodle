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
 * Web Service to return the assessment summary by type: submitted, overdue etc.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * This class provides the web service description for returning an assessment summary by type.
 */
class get_assessmentsummarybytype extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'charttype' => new external_value(PARAM_INT, 'The selected type', VALUE_DEFAULT),
            'activetab' => new external_value(PARAM_ALPHA, 'The active tab', VALUE_DEFAULT),
            'coursefilter' => new external_value(PARAM_ALPHA, 'The course filter', VALUE_DEFAULT),
        ]);
    }

    /**
     * Return the assessments.
     *
     * @param int $charttype
     * @param string $activetab
     * @param string $coursefilter
     * @return array of assessments.
     * @throws \invalid_parameter_exception
     */
    public static function execute(int $charttype, string $activetab, string $coursefilter): array {
        $params = self::validate_parameters(self::execute_parameters(),
            [
                'charttype' => $charttype,
                'activetab' => $activetab,
                'coursefilter' => $coursefilter,
            ]);
        return [
            'result' => json_encode(\block_newgu_spdetails\api::get_assessmentsummarybytype(
                $params['charttype'], $params['activetab'], $params['coursefilter'], )),
        ];
    }

    /**
     * Describes what will be returned to the caller.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'result' => new external_value(PARAM_TEXT, 'The assessment summary, filtered by type - in JSON format'),
        ]);
    }
}
