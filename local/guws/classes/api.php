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
 * Export custom web services used by UofG systems
 *
 * @package    local_guws
 * @copyright  2023 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_guws;

defined('MOODLE_INTERNAL') || die;

class api {

    /**
     * Get list of courses for staff/student portal
     * @param string $guid
     * @param int $maxresults
     */
    public static function get_portal_courses($guid, $maxresults = 100) {
        global $DB, $CFG;

        // Find userid from GUID
        if (!$user = $DB->get_record('user', ['username' => $guid, 'mnethostid' => $CFG->mnet_localhost_id])) {
            return [];
        }

            // Get student's courses
        $fields = ['id', 'fullname', 'shortname', 'visible'];
        $courses = enrol_get_all_users_courses($user->id, true, $fields);
        if (!$courses) {
            return [];
        }

        // Get starred courses
        $starorder = $DB->get_record('user_preferences', ['userid' => $user->id, 'name' => 'theme_hillhead_starorder']);
        if ($starorder) {
            $stars = explode(',', $starorder->value);
        } else {
            $stars = null;
        }

        // Build additional course data 
        foreach ($courses as $course) {
            if ($lastaccess = $DB->get_record('user_lastaccess', ['userid' => $user->id, 'courseid' => $course->id])) {
                $course->lastaccess = $lastaccess->timeaccess;
            } else {
                $course->lastaccess = 0;
            }

            // ...because there's an index...
            $context = \context_course::instance($course->id);
            if ($favourite = $DB->get_record('favourite', ['component' => 'core_course', 'itemtype' => 'courses', 'contextid' => $context->id, 'userid' => $user->id])) {
                $course->starred = true;
            } else {
                $course->starred = false;
            }

            // formatted time
            if ($course->lastaccess) {
                $course->formattedlastaccess = userdate($course->lastaccess);
            } else {
                $course->formattedlastaccess = '-';
            }
        }

        // sort by stars and lastaccess (in that order)
        usort($courses, function($a, $b) use ($stars) {
            if ($a->starred && !$b->starred) {
                return -1;
            }
            if (!$a->starred && $b->starred) {
                return 1;
            }
            if (!empty($stars)) {
                $posA = array_search($a->id, $stars);
                $posB = array_search($b->id, $stars);
                $starsort = ($posA !== false) && ($posB !== false);
            } else {
                $starsort = false;
            }
            if ($starsort && $a->starred && $b->starred) {
                return $posA - $posB;
            }
            return $b->lastaccess - $a->lastaccess;
        });

        // Get the first $maxresults
        $courses = array_slice($courses, 0, $maxresults-1);

        return $courses;
    }

}