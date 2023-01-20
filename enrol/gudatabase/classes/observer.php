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
 * UofG Database enrolment plugin.
 *
 * This plugin synchronises enrolment and roles with external database table.
 *
 * @package    enrol
 * @subpackage gudatabase
 * @copyright  2012 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class enrol_gudatabase_observer {

    /**
     * Triggered when a course is reset
     */
    public static function course_reset_ended(\core\event\course_reset_ended $event) {
        global $DB;

        $courseid = $event->courseid;
        $plugin = enrol_get_plugin('gudatabase');

        // Delete the cached entries for the course codes in enrol_gudatabase_codes
        // This might give problems with students logging in to recently reset courses
        // but I don't care - it won't be many.
        $DB->delete_records('enrol_gudatabase_codes', array('courseid' => $courseid));
        return;
    }

    /**
     * Triggered when a course is created
     */
    public static function course_created(\core\event\course_created $event) {
        global $DB, $USER;

        $courseid = $event->objectid;
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

	// get default course length
        $courseconfig = get_config('moodlecourse');
        
        // how many days is current end
        $currentlength = $course->enddate - time();

        // Check if this is 'close' as Moodle does some odd things with timestamp
        // calculation
        if (abs($currentlength - $courseconfig->courseduration) < 3 * 86400) {

            // Set to end of july
            $month = date('m');
            $year = date('Y');
            if ($month > 7) {
                $year++;
            }
            $future = strtotime("$year-07-31");
            $course->enddate = $future;
            $DB->update_record('course', $course);
        }

        return;
    }

}
