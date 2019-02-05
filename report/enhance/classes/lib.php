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
 * Library functions
 *
 * @package    report_enhance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_enhance;

defined('MOODLE_INTERNAL') || die();

class lib {

    /**
     * Get voting status for this user/request
     * @param object $request
     * @return array [$count, $ownrequest, $voted]
     */
    public static function getvotes($request) {
        global $DB, $USER;

        // Get the possible votes for this request
        $votes = $DB->get_records('report_enhance_vote', ['enhanceid' => $request->id]);

        $count = count($votes);
        $ownrequest = $request->userid == $USER->id;
        $voted = !empty(array_filter($votes, function($vote) {
            global $USER;
            return $vote->userid == $USER->id;
            }));

        return [$count, $ownrequest, $voted];
    }

    /**
     * Set/remove vote for current user
     * @param object $request
     * @param bool $vote true = vote, false = remove vote
     * @return bool success
     */
    public static function vote($request, $vote) {
        global $DB, $USER;

        // You can't vote on your own request
        if ($request->userid == $USER->id) {
            return false;
        }

        if ($vote) {
            if (!$DB->get_record('report_enhance_vote', ['enhanceid' => $request->id, 'userid' => $USER->id])) {
                $enhancevote = new \stdClass;
                $enhancevote->enhanceid = $request->id;
                $enhancevote->userid = $USER->id;
                $DB->insert_record('report_enhance_vote', $enhancevote);
            }
        } else {
            $DB->delete_records('report_enhance_vote', ['enhanceid' => $request->id, 'userid' => $USER->id]);
        }

        return true;
    }

    /**
     * Get classes for list cards
     * @param object $request
     * @return string
     */
    public static function cardclasses($request) {
        global $USER;

        $requestClasses = [];
        $requestClasses[] = 'filter-status-' . $request->status;
        if($request->userid == $USER->id) {
            $requestClasses[] = 'filter-me';
        }

        return implode(" ", $requestClasses);
    }


}
