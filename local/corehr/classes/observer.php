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
 * Sychronise completion data for CoreHR
 *
 * @package    local_corehr
 * @copyright  2016 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class local_corehr_observer {

    /**
     * Triggered when a course is marked complete
     */
    public static function course_completed(\core\event\course_completed $event) {

        $courseid = $event->courseid;
        $relateduserid = $event->relateduserid;
        \local_corehr\api::course_completed($courseid, $relateduserid);
        mtrace("local_corehr recording completion for courseid=" . $courseid . ", userid = " . $relateduserid);

        return;
    }
}
