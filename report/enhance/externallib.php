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
 * Web service declarations
 *
 * @package    report_enhance
 * @copyright  2019 Howard Miller (howardsmiller@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class report_enhance_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_votes_parameters() {
        return new external_function_parameters(
            array(
                'requestid' => new external_value(PARAM_INT, 'ID of improvement request'),
            )
        );
    }

    /**
     * Returns description of method result
     * @return external_description
     */
    public static function get_votes_returns() {
        return new external_single_structure(
            array(
                'count' => new external_value(PARAM_INT, 'Current vote count'),
                'ownrequest' => new external_value(PARAM_BOOL, 'Did current user file request?'),
                'voted' => new external_value(PARAM_BOOL, 'Has current user voted for this request?'),
            )
        );
    }

    /**
     * Get voting details for user/request
     * @param int $requestid 
     */
    public static function get_votes($requestid) {
        global $DB;

        // Validate params
        $params = self::validate_parameters(self::get_votes_parameters(), ['requestid' => $requestid]);

        // Get request
        $request = $DB->get_record('report_enhance', ['id' => $params['requestid']], '*', MUST_EXIST);

        $votes = [];
        list($votes['count'], $votes['ownrequest'], $votes['voted']) = \report_enhance\lib::getvotes($request);

        return $votes;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function set_vote_parameters() {
        return new external_function_parameters(
            array(
                'requestid' => new external_value(PARAM_INT, 'ID of improvement request'),
                'vote' => new external_value(PARAM_BOOL, 'True = vote, False = unvote'),
            )
        );
    }

    /**
     * Returns description of method result
     * @return external_description
     */
    public static function set_vote_returns() {
        return new external_single_structure(
            array(
                'count' => new external_value(PARAM_INT, 'Current vote count'),
                'ownrequest' => new external_value(PARAM_BOOL, 'Did current user file request?'),
                'voted' => new external_value(PARAM_BOOL, 'Has current user voted for this request?'),
            )
        );
    }

    /**
     * Set vote
     * @param int $requestid 
     * 
     */
    public static function set_vote($requestid, $vote) {
        global $DB;

        // Validate params
        $params = self::validate_parameters(self::set_vote_parameters(), ['requestid' => $requestid, 'vote' => $vote]);

        // Get request
        $request = $DB->get_record('report_enhance', ['id' => $params['requestid']], '*', MUST_EXIST);

        // Update vote.
        \report_enhance\lib::vote($request, $params['vote']);

        $votes = [];
        list($votes['count'], $votes['ownrequest'], $votes['voted']) = \report_enhance\lib::getvotes($request);

        return $votes;
    }

}
