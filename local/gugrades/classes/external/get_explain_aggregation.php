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
 * Get data for aggregation explain page
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
 * Define function get_aggregation_page
 */
class get_explain_aggregation extends external_api {

    /**
     * Define function parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'gradecategoryid' => new external_value(PARAM_INT, 'Grade category id number'),
            'userid' => new external_value(PARAM_INT, 'User ID of user to examine'),
        ]);
    }

    /**
     * Execute function
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $userid
     * @return array
     */
    public static function execute($courseid, $gradecategoryid, $userid) {

        \local_gugrades\development::increase_debugging();

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'gradecategoryid' => $gradecategoryid,
            'userid' => $userid,
        ]);

        // Security.
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $page = \local_gugrades\api::get_explain_aggregation(
            $courseid, $gradecategoryid, $userid);

        return $page;
    }

    /**
     * Define result
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'User ID'),
            'displayname' => new external_value(PARAM_TEXT, 'Name to display for this user'),
            'firstinitial' => new external_value(PARAM_TEXT, 'First initial for filtering'),
            'lastinitial' => new external_value(PARAM_TEXT, 'Last initial for filtering'),
            'itemname' => new external_value(PARAM_TEXT, 'Name of grade item'),
            'pictureurl' => new external_value(PARAM_URL, 'URL of user avatar'),
            'profileurl' => new external_value(PARAM_URL, 'Like to user profile page'),
            'idnumber' => new external_value(PARAM_TEXT, 'User ID number'),
            'resitrequired' => new external_value(PARAM_BOOL, 'Is resit required?'),
            'completed' => new external_value(PARAM_FLOAT, '%age of course completed'),
            'displaygrade' => new external_value(PARAM_TEXT, 'Content for total column'),
            'releasegrade' => new external_value(PARAM_TEXT, 'Grade to show in the released column (if any)'),
            'mismatch' => new external_value(PARAM_BOOL, 'Released and display grades do not match'),
            'rawgrade' => new external_value(PARAM_FLOAT, 'Aggregated grade before any conversion'),
            'total' => new external_value(PARAM_FLOAT, 'Total grade used for ongoing aggregation'),
            'overridden' => new external_value(PARAM_BOOL, 'Has grade been overridden?'),
            'alteredweight' => new external_value(PARAM_BOOL, 'Have the weights been altered for this user?'),
            'showweights' => new external_value(PARAM_BOOL, 'Should weights be shown in headers?'),
            'strategy' => new external_value(PARAM_TEXT, 'Aggregation strategy formatted for display'),
            'atype' => new external_value(PARAM_TEXT, 'Aggregated grade type (A, B, P, E - if mixed'),
            'formattedatype' => new external_value(PARAM_TEXT, 'Human readable form of atype'),
            'error' => new external_value(PARAM_TEXT, 'Error condition'),
            'explain' => new external_value(PARAM_RAW, 'Explanation'),
            'fields' => new external_multiple_structure(
                new external_single_structure([
                    'fieldname' => new external_value(PARAM_TEXT, 'Identifier for column'),
                    'itemname' => new external_value(PARAM_TEXT, 'Shortened item name (for debugging, mostly)'),
                    'fullname' => new external_value(PARAM_TEXT, 'Full item name'),
                    'display' => new external_value(PARAM_TEXT, 'Grade for display'),
                    'dropped' => new external_value(PARAM_BOOL, 'Has this grade been dropped?'),
                    'isadmin' => new external_value(PARAM_BOOL, 'Is this an admin grade (for styling purposes)?'),
                    'hidden' => new external_value(PARAM_BOOL, 'Is grade hidden?'),
                    'overridden' => new external_value(PARAM_BOOL, 'Has grade been overridden?'),
                    'available' => new external_value(PARAM_BOOL, 'Is grade item available to this user?'),
                    'weight' => new external_value(PARAM_FLOAT, 'Item weighting'),
                    'normalisedweight' => new external_value(PARAM_FLOAT, '(Normalised) item weighting'),
                    'alteredweight' => new external_value(PARAM_FLOAT, 'Altered weight (if applicable)'),
                ])
            ),
        ]);
    }

}
