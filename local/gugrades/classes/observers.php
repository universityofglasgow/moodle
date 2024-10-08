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
 * Event observers
 *
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

/**
 * Static class implementation to deal with event observers
 */
class observers {


    /**
     * Handle deleted grade item
     * @param \core\event\grade_item_deleted $event
     */
    public static function grade_item_deleted(\core\event\grade_item_deleted $event) {
        $courseid = $event->courseid;
        $data = $event->get_data();
        $gradeitemid = $data['objectid'];

        \local_gugrades\grades::delete_grade_item($courseid, $gradeitemid);
    }

    /**
     * Handle deleted course
     * @param \core\event\course_deleted $event
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        $data = $event->get_data();
        $courseid = $data['objectid'];

        \local_gugrades\grades::delete_course($courseid);
    }

    /**
     * Handle dpdated grade item
     * @param \core\event\grade_item_updated $event
     */
    public static function grade_item_updated(\core\event\grade_item_updated $event) {
        $courseid = $event->courseid;
        $itemid = $event->objectid;

        \local_gugrades\grades::grade_item_updated($courseid, $itemid);
    }

}
