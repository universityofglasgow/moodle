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
     * Get users who can "be graded". Usually students.
     * @param context $context
     * @param string $firstname (first letter of first name)
     * @param string $lastname (first letter of last name)
     * @param int $from (for paging, 0 based)
     * @param into $size (how many)
     * @return array
     */
    public static function get_gradeable_users(\context $context, $firstname = '', $lastname = '', $from, $size) {
        $fields = 'u.id, u.username, u.idnumber, u.firstname, u.lastname, u.email';
        $users = get_enrolled_users($context, 'moodle/grade:view', 0, $fields, null, $from, $size);

        // filter
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
     * Get available users for given activity
     * @param object $cmi (cm_info)
     * @param context $context
     * @param string $firstname (first letter of first name)
     * @param string $lastname (first letter of last name)
     * @param int $from (for paging, 0 based)
     * @param into $size (how many)
     * @return array
     */
    public static function get_available_users_from_cm($cmi, $context, $firstname, $lastname, $from, $size) {
        
        //See https://moodledev.io/docs/apis/subsystems/availability
        $info = new \core_availability\info_module($cmi);

        // Get all the possible users in this course
        $users = self::get_gradeable_users($context, $firstname, $lastname, $from, $size);

        // Filter using availability API.
        $filteredusers = $info->filter_user_list($users);

        return array_values($filteredusers);
    }
}