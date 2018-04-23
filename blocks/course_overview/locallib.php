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
 * Helper functions for course_overview block
 *
 * @package    block_course_overview
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE', '0');
define('BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_ONLY_PARENT_NAME', '1');
define('BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH', '2');

define('BLOCKS_COURSE_OVERVIEW_REORDER_NONE', '0');
define('BLOCKS_COURSE_OVERVIEW_REORDER_FULLNAME', '1');
define('BLOCKS_COURSE_OVERVIEW_REORDER_SHORTNAME', '2');
define('BLOCKS_COURSE_OVERVIEW_REORDER_ID', '3');

/**
 * Display overview for courses
 *
 * @param array $courses courses for which overview needs to be shown
 * @return array html overview
 */
function block_course_overview_get_overviews($courses) {
    global $CFG;

    // Tab may not have any courses.
    if (!$courses) {
        return array();
    }

    // Disable debugging mode because all course modules show debugging message in their print_overview.
    $debugmode = $CFG->debug;
    $CFG->debug ^= E_DEPRECATED;

    $htmlarray = array();

    if ($modules = get_plugin_list_with_function('mod', 'print_overview')) {
        // Split courses list into batches with no more than MAX_MODINFO_CACHE_SIZE courses in one batch.
        // Otherwise we exceed the cache limit in get_fast_modinfo() and rebuild it too often.
        if (defined('MAX_MODINFO_CACHE_SIZE') && MAX_MODINFO_CACHE_SIZE > 0 && count($courses) > MAX_MODINFO_CACHE_SIZE) {
            $batches = array_chunk($courses, MAX_MODINFO_CACHE_SIZE, true);
        } else {
            $batches = array($courses);
        }
        foreach ($batches as $courses) {
            foreach ($modules as $fname) {
                $fname($courses, $htmlarray);
            }
        }
    }
    $CFG->debug = $debugmode;
    return $htmlarray;
}

/**
 * Sets user preference for maximum courses to be displayed in course_overview block
 *
 * @param int $number maximum courses which should be visible
 */
function block_course_overview_update_mynumber($number) {
    set_user_preference('course_overview_number_of_courses', $number);
}

/**
 * Sets user course sorting preference in course_overview block
 *
 * @param array $sortorder list of course ids
 */
function block_course_overview_update_myorder($sortorder) {
    $value = implode(',', $sortorder);
    if (core_text::strlen($value) > 1333) {
        // The value won't fit into the user preference.
        // Remove courses in the end of the list (mostly likely user won't even notice).
        $value = preg_replace('/,[\d]*$/', '', core_text::substr($value, 0, 1334));
    }
    set_user_preference('course_overview_course_sortorder', $value);
}

/**
 * Gets user course sorting preference in course_overview block
 *
 * @return array list of course ids
 */
function block_course_overview_get_myorder() {
    if ($value = get_user_preferences('course_overview_course_sortorder')) {
        return explode(',', $value);
    }
    // If preference was not found, look in the old location and convert if found.
    $order = array();
    if ($value = get_user_preferences('course_overview_course_order')) {
        $order = unserialize_array($value);
        block_course_overview_update_myorder($order);
        unset_user_preference('course_overview_course_order');
    }
    return $order;
}

/**
 * Get the list of course favourites
 *
 * @return array list of course ids
 */
function block_course_overview_get_favourites() {
    if ($value = get_user_preferences('course_overview_favourites')) {
        return explode(',', $value);
    } else {
        return array();
    }
}

/**
 * Sets favourites
 *
 * @param array $favourites list of course ids
 */
function block_course_overview_update_favourites($favourites) {
    $value = implode(',', $favourites);
    if (core_text::strlen($value) > 1333) {
        // The value won't fit into the user preference.
        // Remove courses in the end of the list (mostly likely user won't even notice).
        $value = preg_replace('/,[\d]*$/', '', core_text::substr($value, 0, 1334));
    }
    set_user_preference('course_overview_favourites', $value);
}

/**
 * Get sort order preference
 * @return int
 */
function block_course_overview_get_sortorder() {
    if ($value = get_user_preferences('course_overview_sortorder')) {
        return $value;
    } else {
        return BLOCKS_COURSE_OVERVIEW_REORDER_NONE;
    }
}

/**
 * Set sort order preference
 * @param int $sortorder
 */
function block_course_overview_update_sortorder($sortorder) {
    set_user_preference('course_overview_sortorder', $sortorder);
}

/**
 * Return sorted list of user courses
 *
 * @param bool $favourites tab selected
 * @param bool $keepfavourites setting, show favs in course tab
 * @param array $exlude list of courses not to include (i.e. favs in courses list)A
 * @return array list of sorted courses and count of courses.
 */
function block_course_overview_get_sorted_courses($favourites, $keepfavourites = false, $exclude = []) {
    global $USER;

    // Get courses in order.
    $sortorder = block_course_overview_get_sortorder();
    if ($sortorder == BLOCKS_COURSE_OVERVIEW_REORDER_FULLNAME) {
        $sort = 'fullname ASC';
    } else if ($sortorder == BLOCKS_COURSE_OVERVIEW_REORDER_SHORTNAME) {
        $sort = 'shortname ASC';
    } else if ($sortorder == BLOCKS_COURSE_OVERVIEW_REORDER_ID) {
        $sort = 'id ASC';
    } else {
        $sort = 'visible DESC,sortorder ASC';
    }
    $courses = enrol_get_my_courses(null, $sort);
    $site = get_site();

    if (array_key_exists($site->id, $courses)) {
        unset($courses[$site->id]);
    }

    foreach ($courses as $c) {
        if (isset($USER->lastcourseaccess[$c->id])) {
            $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
        } else {
            $courses[$c->id]->lastaccess = 0;
        }
    }

    // Get remote courses.
    $remotecourses = array();
    if (is_enabled_auth('mnet')) {
        $remotecourses = get_my_remotecourses();
    }
    // Remote courses will have -ve remoteid as key, so it can be differentiated from normal courses.
    foreach ($remotecourses as $id => $val) {
        $remoteid = $val->remoteid * -1;
        $val->id = $remoteid;
        $courses[$remoteid] = $val;
    }

    if ($favourites) {
        $order = block_course_overview_get_favourites();
    } else {
        $order = block_course_overview_get_myorder();
    }

    $sortedcourses = array();

    // Accept list as-is or order by preference list.
    if (!$favourites && ($sortorder != BLOCKS_COURSE_OVERVIEW_REORDER_NONE)) {
        $sortedcourses = $courses;
    } else {
        $counter = 0;

        // Get courses in sort order into list.
        foreach ($order as $key => $cid) {

            // Make sure user is still enroled.
            if (isset($courses[$cid])) {
                $sortedcourses[$cid] = $courses[$cid];
                $counter++;
            }
        }

        // Append unsorted courses if limit allows & not favourites.
        if (!$favourites) {
            foreach ($courses as $c) {
                if (!in_array($c->id, $order)) {
                    $sortedcourses[$c->id] = $c;
                    $counter++;
                }
            }
        }
    }

    // If this is the courses tab and we are excluding favourites.
    if (!$favourites && !$keepfavourites) {
        foreach ($sortedcourses as $c) {
            if (in_array($c->id, $exclude)) {
                unset($sortedcourses[$c->id]);
            }
        }
    }

    // From list extract site courses for overview.
    $sitecourses = array();
    foreach ($sortedcourses as $key => $course) {
        if ($course->id > 0) {
            $sitecourses[$key] = $course;
        }
    }
    return array($sortedcourses, $sitecourses, count($sortedcourses));
}

/**
 * Add a course to favourites
 * @param int $favourite id of course
 */
function block_course_overview_add_favourite($favourite) {

    // Add to fabourites list.
    $favourites = block_course_overview_get_favourites();
    if (!in_array($favourite, $favourites)) {
        array_unshift($favourites, $favourite);
    }
    block_course_overview_update_favourites($favourites);

    // Remove from courses list.
    $order = block_course_overview_get_myorder();
    $key = array_search($favourite, $order);
    if ($key !== false) {
        unset($order[$key]);
    }
    block_course_overview_update_myorder($order);
}

/**
 * Remove course from favourites
 * @param int $favourite id of course
 */
function block_course_overview_remove_favourite($favourite) {

    // Remove from favourites list.
    $order = block_course_overview_get_favourites();
    $key = array_search($favourite, $order);
    if ($key !== false) {
        unset($order[$key]);
    }
    block_course_overview_update_favourites($order);

    // Add to courses list.
    $order = block_course_overview_get_myorder();
    if (!in_array($favourite, $order)) {
        $order[] = $favourite;
    }
    block_course_overview_update_myorder($order);
}
