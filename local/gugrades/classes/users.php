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
     * @return array
     */
    public static function get_gradeable_users(\context $context) {
        $users = get_enrolled_users($context, 'moodle/grade:view');

        return $users;
    }

    /**
     * Get available users for given activity
     * @param object $cmi (cm_info)
     * @param context $context
     * @return array
     */
    public static function get_available_users_from_cm($cmi, $context) {
        
        //See https://moodledev.io/docs/apis/subsystems/availability
        $info = new \core_availability\info_module($cmi);

        // Get all the possible users in this course
        $users = self::get_gradeable_users($context);

        // Filter using availability API.
        $filteredusers = $info->filter_user_list($users);

        return array_values($filteredusers);
    }
}