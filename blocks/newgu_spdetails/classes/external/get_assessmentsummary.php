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
 * Web Service to return assessment statistics
 *
 * More indepth description.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * This class provides the web service description for returning an assessment summary.
 */
class get_assessmentsummary extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'activetab' => new external_value(PARAM_ALPHA, 'The active tab', VALUE_DEFAULT),
            'coursefilter' => new external_value(PARAM_ALPHA, 'The course filter', VALUE_DEFAULT),
        ]);
    }

    /**
     * Return the assessment summary statistics
     *
     * @param string $activetab
     * @param string $coursefilter
     * @return array of assessment summary statistics
     */
    public static function execute(string $activetab, string $coursefilter): array {

        $params = self::validate_parameters(self::execute_parameters(),
            [
                'activetab' => $activetab,
                'coursefilter' => $coursefilter,
            ]);
        $assessmentsummary = \block_newgu_spdetails\api::get_assessmentsummary($params['activetab'], $params['coursefilter']);
        $totalsubmissions = $assessmentsummary['total_submissions'];
        $totaltosubmit = $assessmentsummary['total_tosubmit'];
        $totaloverdue = $assessmentsummary['total_overdue'];
        $marked = $assessmentsummary['marked'];

        $stats[] = [
            'tobe_sub' => $totaltosubmit,
            'overdue' => $totaloverdue,
            'sub_assess' => $totalsubmissions,
            'assess_marked' => $marked,
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
                'tobe_sub' => new external_value(PARAM_INT, 'assignments to be submitted'),
                'overdue' => new external_value(PARAM_INT, 'assignments overdue'),
                'sub_assess' => new external_value(PARAM_INT, 'total submissions'),
                'assess_marked' => new external_value(PARAM_INT, 'assessments marked'),
            ])
        );
    }
}
