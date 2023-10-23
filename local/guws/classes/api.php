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
        $tmpcourses = enrol_get_all_users_courses($user->id, true, $fields);
        if (!$tmpcourses) {
            return [];
        }

        // Get starred courses
        // I don't think this will work anymore, now that the left nav has gone there is no way to add/remove these.
        // Which means we will potentially return courses that the user can no longer 'unstar'. Do we lose this entirely?
        //$starorder = $DB->get_record('user_preferences', ['userid' => $user->id, 'name' => 'theme_hillhead_starorder']);
        //if ($starorder) {
        //    $stars = explode(',', $starorder->value);
        //} else {
        //    $stars = null;
        //}

        // Build additional course data
        $courses = [];
        foreach ($tmpcourses as $tmpcourse) {

            $tmpcourse->hasaccessed = false;
            // Guard against courses that are [or have been inadvertently?] hidden...
            if ($tmpcourse->visible) {

                if ($lastaccess = $DB->get_record('user_lastaccess', ['userid' => $user->id, 'courseid' => $tmpcourse->id])) {
                    $tmpcourse->lastaccess = $lastaccess->timeaccess;
                    $tmpcourse->hasaccessed = true;
                } else {
                    // Here we need to go and get the enrolment date on the course, so we can order by that instead.
                    $sql = "SELECT timestart AS enrolstart
                    FROM {user_enrolments} AS mue
                    INNER JOIN {enrol} AS me ON (me.id = mue.enrolid AND me.courseid = :courseid AND mue.userid = :userid)
                    INNER JOIN {course} AS c ON (c.id = me.courseid AND c.visible = 1)";
                    $params['courseid'] = $tmpcourse->id;
                    $params['userid'] = $user->id;

                    if ($enrolmentdatequery = $DB->get_records_sql($sql, $params)) {

                        $now = new \DateTimeImmutable(date('Y-m-d'));

                        foreach ($enrolmentdatequery as $enrolmentdate) {
                            $lastaccessed = new \DateTimeImmutable(userdate($enrolmentdate->enrolstart));
                            $interval = $lastaccessed->diff($now);

                            // Define a cutoff date so that we're not going to send back a course from say, 3 years ago for example...
                            // TODO - make this a settings value
                            if (isset($interval) && $interval->days >= 90) {
                                continue 2;
                            } else {
                                $tmpcourse->lastaccess = $enrolmentdate->enrolstart;
                            }
                        }
                    } else {
                        $tmpcourse->lastaccess = 0;
                    }
                }

                // ...because there's an index...
                $context = \context_course::instance($tmpcourse->id);
                if ($favourite = $DB->get_record('favourite', ['component' => 'core_course', 'itemtype' => 'courses', 'contextid' => $context->id, 'userid' => $user->id])) {
                    $tmpcourse->starred = true;
                } else {
                    $tmpcourse->starred = false;
                }

                // formatted time
                if ($tmpcourse->lastaccess) {
                    $tmpcourse->formattedlastaccess = userdate($tmpcourse->lastaccess);
                } else {
                    $tmpcourse->formattedlastaccess = '-';
                }

                $courses[$tmpcourse->id] = $tmpcourse;
            }
        }

        // sort by starred, lastaccess and hasaccessed (in that order)
        // What about a course that's been starred but never visited - how should that be ordered?
//        usort($courses, function($a, $b) /**use ($stars)**/ {
//            if ($a->starred && !$b->starred) {
//                return -1;
//            }
//            if (!$a->starred && $b->starred) {
//                return 1;
//            }
//            if (!empty($stars)) {
//                $posA = array_search($a->id, $stars);
//                $posB = array_search($b->id, $stars);
//                $starsort = ($posA !== false) && ($posB !== false);
//            } else {
//                $starsort = false;
//            }
//            if ($starsort && $a->starred && $b->starred) {
//                return $posA - $posB;
//            }
//            return $b->lastaccess - $a->lastaccess;
//        });
        usort($courses, fn ($a, $b): int =>
            [$b->starred, $b->lastaccess, $a->hasaccessed]
            <=>
            [$a->starred, $a->lastaccess, $b->hasaccessed]
        );

        // Get the first $maxresults
        $courses = array_slice($courses, 0, $maxresults-1);

        return $courses;
    }

}