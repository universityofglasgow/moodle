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
 * UofG LDAP / login operations
 *
 * @package    local_guladp
 * @copyright  2022 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_guldap;

defined('MOODLE_INTERNAL') || die;

class api {

    /**
     * Get user profile field
     * @param string $category
     * @param string $field
     * @param int $userid
     * @return string (empty if nothing held)
     */
    private static function get_profile_field($category, $field, $userid) {
        global $DB;

        if (!$category = $DB->get_record('user_info_category', ['name' => $category])) {
            return '';
        }
        if (!$field = $DB->get_record('user_info_field', ['shortname' => $field, 'categoryid' => $category->id])) {
            return '';
        }
        if (!$data = $DB->get_record('user_info_data', ['fieldid' => $fieldid, 'userid' => $userid])) {
            return '';
        }
        return $data->data;
    }

    /**
     * Process user account to put stuff "in the right place"
     * @param int $userid
     */
    public static function normalise_user($userid) {
        global $DB;

        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        // Do they have a valid email address
        if (!$user->email) {
            echo "NO EMAIL"; die;
        }
    }
}