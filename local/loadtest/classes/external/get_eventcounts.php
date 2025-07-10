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
 * @package    local_loadtest
 * @copyright  2025 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_loadtest\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

class get_eventcounts extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'starttime' => new external_value(PARAM_INT, 'Earliest event time (unix timestamp'),
            'events' => new external_multiple_structure(
                new external_single_structure([
                    'component' => new external_value(PARAM_TEXT, 'Name of component (e.g. core) to count'),
                    'action' => new external_value(PARAM_TEXT, 'Name of action on the component'),
                    'target' => new external_value(PARAM_TEXT, 'Target for action (e.g. course)'),
                ])
            )
        ]);
    }

    /**
     * Execute function
     * @param int $starttime
     * @param array $events
     */
    public static function execute(int $starttime, array $events) {

        $params = self::validate_parameters(self::execute_parameters(), [
            'starttime' => $starttime,
            'events' => $events,
        ]);

        return \local_loadtest\load::get_eventcounts($starttime, $events);
    }

    /**
     * Define function result
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                    'component' => new external_value(PARAM_TEXT, 'Name of component (e.g. core) to count'),
                    'action' => new external_value(PARAM_TEXT, 'Name of action on the component'),
                    'target' => new external_value(PARAM_TEXT, 'Target for action (e.g. course)'),
                    'count' => new external_value(PARAM_INT, 'Count for selected event'),
            ])
        );
    }

}