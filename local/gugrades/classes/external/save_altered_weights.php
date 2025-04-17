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
 * Define function save_altered_weights
 * @package    local_gugrades
 * @copyright  2024
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
 * Write the data from the 'add grade' button
 */
class save_altered_weights extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'categoryid' => new external_value(PARAM_INT, 'Grade category ID'),
            'userid' => new external_value(PARAM_INT, 'User ID'),
            'revert' => new external_value(PARAM_BOOL, 'Revert adjusted weights (items ignored).'),
            'reason' => new external_value(PARAM_TEXT, 'Reason for change (for audit only)'),
            'items' => new external_multiple_structure(
                new external_single_structure([
                    'gradeitemid' => new external_value(PARAM_INT, 'Grade item ID'),
                    'weight' => new external_value(PARAM_FLOAT, 'New weight'),
                ])
            ),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $categoryid
     * @param int $userid
     * @param bool $revert
     * @param string $reason
     * @param array $settings
     * @return array
     */
    public static function execute($courseid, $categoryid, $userid, $revert, $reason, $items) {
        global $DB;

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'categoryid' => $categoryid,
            'userid' => $userid,
            'revert' => $revert,
            'reason' => $reason,
            'items' => $items,
        ]);

        // More security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        \local_gugrades\api::save_altered_weights($courseid, $categoryid, $userid, $revert, $reason, $items);

        // Log.
        /*
        $event = \local_gugrades\event\settings_updated::create([
            'objectid' => $gradeitemid,
            'context' => \context_course::instance($courseid),
        ]);
        $event->trigger();
        */

        // Audit.
        $gradeitemid = \local_gugrades\grades::get_gradeitemid_from_gradecategoryid($categoryid);
        if ($revert) {
            \local_gugrades\audit::write($courseid, $userid, $gradeitemid, 'Altered weights reverted');
        } else {
            $addreason = empty($reason) ? '' : ' - "' . $reason . '"';
            \local_gugrades\audit::write($courseid, $userid, $gradeitemid, 'Weights altered for user' . $addreason);
        }

        return [];
    }

    /**
     * Define function result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([

        ]);
    }

}
