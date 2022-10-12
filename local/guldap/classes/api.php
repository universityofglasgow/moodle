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
        if (!$data = $DB->get_record('user_info_data', ['fieldid' => $field->id, 'userid' => $userid])) {
            return '';
        }
        return $data->data;
    }

    /**
     * Process user account to put stuff "in the right place"
     * @param int $userid
     * @return object $user
     */
    public static function normalise_user($userid) {
        global $DB, $PAGE, $CFG;

        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        // Do they have a valid email address
        if (!$user->email) {

            // If they didn't have email set then this is the first time
            // so make the email private (they can unset this if they want).
            $DB->set_field('user', 'maildisplay', 0, ['id' => $user->id]);

            // Copy home email to primary email (if there is one).
            $homeemail = self::get_profile_field('UofG', 'homeemailaddress', $userid);
            if ($homeemail) {
                $DB->set_field('user', 'email', $homeemail, ['id' => $userid]);
                $user->email = $homeemail;
            } else {
                $PAGE->set_context(\context_system::instance());
                notice(get_string('noemail', 'local_guldap'), $CFG->wwwroot);
            }
        }

        // Check city.
        if (empty($user->city)) {
            $DB->set_field('user', 'city', 'Glasgow', ['id' => $userid]);
            $user->city = 'Glasgow';
        }

        // Check country.
        if (empty($user->country)) {
            $DB->set_field('user', 'country', 'GB', ['id' => $userid]);
            $user->country = 'GB';
        }

        return $user;
    }

    /**
     * Login actions - stuff we kick off when somebody logs in.
     * @param object $user
     */
    public static function login_actions($user) {
        global $CFG, $DB;

        // Get CoreHR data and check for 'known as' name.
        // only if not a student
        $isstudent = preg_match("/\d{7}[a-z]/i", $user->username);
        if (!$isstudent) {
            $corehr = \local_corehr\api::get_extract($user->username);
            if ($corehr) {
                $firstname = $corehr->knownas;
                if (trim($firstname)) {
                    $user->firstname = $firstname;
                }

                $user->institution = $corehr->collegedesc;
                $user->department = $corehr->schooldesc;
                $DB->update_record('user', $user);

                // If they exist in CoreHR then we can safely apply
                // training course auto-enrol.
                \local_corehr\api::auto_enrol($user->name);
            }
        }
    }
}