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
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace local_gugrades\external;

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class get_history extends \external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradeitemid' => new external_value(PARAM_INT, 'Grade item id number'),
            'userid' => new external_value(PARAM_INT, 'User ID'),
        ]);
    }

    public static function execute($courseid, $gradeitemid, $userid) {

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'userid' => $userid,
        ]);
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $grades = \local_gugrades\api::get_history($gradeitemid, $userid);

        return $grades;
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Mystery id'),
                'reasonname' => new external_value(PARAM_TEXT, 'Reason description'),
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'gradeitemid' => new external_value(PARAM_INT, 'Grade item ID'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'grade' => new external_value(PARAM_NUMBER, 'Raw grade value'),
                'weightedgrade' => new external_value(PARAM_NUMBER, 'Weighted grade'),
                'reason' => new external_value(PARAM_INT, 'Reason ID'),
                'other' => new external_value(PARAM_TEXT, 'if reason = other'),
                'iscurrent' => new external_value(PARAM_BOOL, 'Is this the current value?'),
                'current' => new external_value(PARAM_TEXT, 'Formatted is this current value?'),
                'auditby' => new external_value(PARAM_INT, 'User ID. User who added grade'),
                'audittimecreated' => new external_value(PARAM_INT, 'Time created (unix time stamp)'),
                'time' => new external_value(PARAM_TEXT, 'Formatted time'),
                'auditcomment' => new external_value(PARAM_TEXT, 'Audit comment'),
            ])
        );

    }

}