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
 * Language EN
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

/**
 * Group functions used to manipulate user-related data
 */
class users {

    /**
     * Get course module from grade item
     * @param int $itemid Grade item ID
     * @param int $courseid
     * @return object
     */
    public static function get_cm_from_grade_item(int $itemid, int $courseid) {
        global $DB;

        $item = $DB->get_record('grade_items', ['id' => $itemid], '*', MUST_EXIST);

        // This only works when itemtype is mod (not surprisingly)
        if ($item->itemtype != 'mod') {
            return false;
        }

        // Get course module.
        //$cm = get_coursemodule_from_instance($item->itemmodule, $item->iteminstance, $courseid, false, MUST_EXIST);

        // "Set to -1 to avoid calculation of dynamic user-depended data".
        $modinfo = get_fast_modinfo($courseid, -1);
        if (!$cm = $modinfo->instances[$item->itemmodule][$item->iteminstance]) {
            throw new \moodle_exception('Unable to find course module for gradeitemid = ' . $gradeitemid);
        }
        return $cm;
    }

    /**
     * Get availability for user.
     * @param int $gradeitemid
     * @param int $userid
     * @return boolean
     */
    public static function available_for_user(int $gradeitemid, int $userid) {
        global $DB;

        $item = \local_gugrades\grades::get_gradeitem($gradeitemid);

        // If the gradeitem is NOT is module then it's simply available.
        if ($item->itemtype != 'mod') {
            return true;
        }

        // Get course module.
        $courseid = $item->courseid;
        $modinfo = get_fast_modinfo($courseid, $userid);
        if (!$cm = $modinfo->instances[$item->itemmodule][$item->iteminstance]) {
            throw new \moodle_exception('Unable to find course module for gradeitemid = ' . $gradeitemid);
        }

        return $cm->visible;
    }

    /**
     * Factory to get correct class for assignment type
     * These are found in local_gugrades/classes/activities
     * Pick manual for manual grades, xxx_activity for activity xxx (if exists) or default_activity
     * for everything else
     * @param int $gradeitemid
     * @param int $courseid
     * @param int $groupid
     * @return object
     */
    public static function activity_factory(int $gradeitemid, int $courseid, int $groupid = 0) {
        global $DB;

        $item = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $module = $item->itemmodule;
        if ($item->itemtype == 'manual') {
            return new \local_gugrades\activities\manual($gradeitemid, $courseid, $groupid);
        } else {
            $classname = '\\local_gugrades\\activities\\' . $module . '_activity';
            if (class_exists($classname, true)) {
                return new $classname($gradeitemid, $courseid, $groupid);
            } else {
                return new \local_gugrades\activities\default_activity($gradeitemid, $courseid, $groupid);
            }
        }
    }

    /**
     * Get users who can "be graded". Usually students.
     * @param \context $context
     * @param string $firstname (first letter of first name)
     * @param string $lastname (first letter of last name)
     * @param int $groupid (0 means ignore groups)
     * @return array
     */
    public static function get_gradeable_users(\context $context, $firstname = '', $lastname = '', $groupid = 0) {
        $fields = 'u.id, u.username, u.idnumber, u.firstname, u.lastname, u.email,
            u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename, u.picture, u.imagealt';
        $users = get_enrolled_users($context, 'moodle/grade:view', $groupid, $fields);

        // Filter.
        if ($firstname || $lastname) {
            $users = array_filter($users, function($user) use ($firstname, $lastname) {
                if ($firstname && (strcasecmp(substr($user->firstname, 0, 1), $firstname))) {
                    return false;
                }
                if ($lastname && (strcasecmp(substr($user->lastname, 0, 1), $lastname))) {
                    return false;
                }
                return true;
            });
        }

        return $users;
    }

    /**
     * Convenience function to get a count of all users in the course
     * @param int $courseid
     * @return int
     */
    public static function count_enrolled_users(int $courseid) {
        $context = \context_course::instance($courseid);
        $users = self::get_gradeable_users($context);

        return count($users);
    }

    /**
     * Get user record from userid
     * Check that user is a valid "student" in the course
     * @param \context $connext
     * @param int $userid
     * @return object
     */
    public static function get_gradeable_user(\context $context, int $userid) {
        global $DB;

        if (!is_enrolled($context, $userid, 'moodle/grade:view')) {
            throw new \moodle_exception('Not a gradeable user in this course. Userid = ' . $userid);
        }

        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        return $user;
    }

    /**
     * Get available users for given activity
     * @param object $cmi (cm_info)
     * @param \context $context
     * @param string $firstname (first letter of first name)
     * @param string $lastname (first letter of last name)
     * @param int $groupid
     * @return array
     */
    public static function get_available_users_from_cm($cmi, $context, $firstname, $lastname, $groupid) {

        // See https://moodledev.io/docs/apis/subsystems/availability.
        $info = new \core_availability\info_module($cmi);

        // Get all the possible users in this course.
        $users = self::get_gradeable_users($context, $firstname, $lastname, $groupid);

        // Filter using availability API.
        $filteredusers = $info->filter_user_list($users);

        return array_values($filteredusers);
    }

    /**
     * Check if individual user can access activity
     * @param object $cm
     * @param int $userid
     */
    /*
    public static function is_user_visible(object $cm, int $userid) {
        $info = new \core_availability\info_module($cm);

        return $info->is_user_visible($cm, $userid, false);
    }
    */

    /**
     * Add pictures to user records
     * @param int $courseid
     * @param array $users
     * @return array
     */
    public static function add_pictures_and_profiles_to_user_records(int $courseid, array $users) {
        foreach ($users as $id => $user) {
            $users[$id] = self::add_picture_and_profile_to_user_record($courseid, $user);
        }

        return $users;
    }

    /**
     * Add picture to single user record
     * @param int $couseid
     * @param object $user
     * @param return object
     */
    public static function add_picture_and_profile_to_user_record(int $courseid, object $user) {
        global $PAGE;

        $userpicture = new \user_picture($user);
        $user->pictureurl = $userpicture->get_url($PAGE)->out(false);

        // Also add profile url while we are here
        $profile = new \moodle_url('/user/view.php', ['course' => $courseid, 'id' => $user->id]);
        $user->profileurl = $profile->out(false);

        return $user;
    }

    /**
     * Add gradehidden flag to user records
     * @param array $users
     * @param int $gradeitemid
     * @return array
     */
    public static function add_gradehidden_to_user_records(array $users, int $gradeitemid) {
        foreach ($users as $id => $user) {
            $users[$id] = self::add_gradehidden_to_user_record($user, $gradeitemid);
        }

        return $users;
    }

    /**
     * Add gradehidden to user record
     * @param object $user
     * @param int $gradeitemid
     * @return $user
     */
    public static function add_gradehidden_to_user_record(object $user, int $gradeitemid) {
        global $DB;

        $user->gradehidden = $DB->record_exists('local_gugrades_hidden',
            ['gradeitemid' => $gradeitemid, 'userid' => $user->id]);

        return $user;
    }

    /**
     * Count the number of users in a given course
     * @param int $courseid
     * @return int
     */
    public static function count_participants(int $courseid) {
        $context = \context_course::instance($courseid);

        return count_enrolled_users($context);
    }

    /**
     * Get the course code from the gudatabase tables
     * @param int $courseid
     * @param int $userid
     * @return string
     */
    public static function get_course_code(int $courseid, int $userid) {
        global $DB;

        if ($gudatabasecode = $DB->get_record('enrol_gudatabase_users',
            ['userid' => $userid, 'courseid' => $courseid], '*', IGNORE_MULTIPLE)) {
            $code = $gudatabasecode->code;
        } else {
            $code = '';
        }

        return $code;
    }

    /**
     * Allow display of CSV import button
     * Only if one or more ID number
     * @param array $users
     * @return bool
     */
    public static function showcsvimport(array $users) {
        foreach ($users as $user) {
            if ($user->idnumber) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear availability cache.
     * @param int $courseid
     */
    public static function clear_availability_cache(int $courseid) {
        global $DB;

        $items = $DB->get_records('grade_items', ['courseid' => $courseid]);
        foreach ($items as $item) {
            $cachetag = 'AVAILABLE_' . $courseid . '_' . $item->id;
        }
    }

    /**
     * Get firstname and lastname initials
     * @param object $user
     * @return [string, string]
     */
    public static function get_initials(object $user) {
        $first = empty($user->firstname) ? '' : \core_text::substr($user->firstname, 0, 1);
        $last = empty($user->lastname) ? '' : \core_text::substr($user->lastname, 0, 1);

        $first = \core_text::strtoupper($first);
        $last = \core_text::strtoupper($last);

        return [$first, $last];
    }
}
