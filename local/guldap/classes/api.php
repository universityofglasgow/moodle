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
     * Check if ldap is installed and configured
     * @return boolean
     */
    public static function isenabled() {
        $config = get_config('local_guldap');
        if (!function_exists('ldap_connect')) {
            return false;
        }
        if (!$config->host_url) {
            return false;
        }

        return true;
    }

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
     * Modified version of core_login_get_return_url
     * Avoid going to /user/edit.php
     */
    private static function get_return_url() {
        global $CFG, $SESSION, $USER;

        // Prepare redirection.
        if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0
                or strpos($SESSION->wantsurl, str_replace('http://', 'https://', $CFG->wwwroot)) === 0)) {
            $urltogo = $SESSION->wantsurl;    // Because it's an address in this site.
            unset($SESSION->wantsurl);
        } else {
            // No wantsurl stored or external - go to homepage.
            $urltogo = $CFG->wwwroot.'/';
            unset($SESSION->wantsurl);
        }
    
        // If the url to go to is the same as the site page, check for default homepage.
        if ($urltogo == ($CFG->wwwroot . '/')) {
            $homepage = get_home_page();
            // Go to my-moodle page instead of site homepage if defaulthomepage set to homepage_my.
            if ($homepage === HOMEPAGE_MY && !isguestuser()) {
                if ($urltogo == $CFG->wwwroot or $urltogo == $CFG->wwwroot.'/' or $urltogo == $CFG->wwwroot.'/index.php') {
                    $urltogo = $CFG->wwwroot.'/my/';
                }
            }
        }
        return $urltogo;
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
                \local_corehr\api::auto_enrol($user->username);
            }
        }

        // Redirect to correct home page (rather that potentially putting up profile page).
        $urltogo = self::get_return_url();
        redirect($urltogo);
    }

    /**
     * Search for ldap_users
     * @param string $filter
     * @return array
     */
    public static function ldap_search($filter) {
        $ldap = new ldap();
        $dv = $ldap->connect();
        $results = $ldap->search($dv, $filter);
        $ldap->close($dv);

        return $results;
    }

    /**
     * Create LDAP filter
     * @param string $firstname
     * @param string $lastname
     * @param string $guid
     * @param string $idnumber
     * @return array
     */
    public static function build_filter($firstname, $lastname, $guid, $email, $idnumber) {
        $config = get_config('local_guldap');

        // LDAP filter doesn't like escaped characters.
        $lastname = stripslashes($lastname);
        $firstname = stripslashes($firstname);

        // If the GUID is supplied then we don't care about anything else.
        if (!empty($guid)) {
            return $config->user_attribute . "=$guid";
        }

        // If the idnumber is supplied then we'll go for that.
        if (!empty($idnumber) & is_numeric($idnumber)) {
            return $config->map_idnumber . "=$idnumber";
        }

        // If the email is supplied then we don't care about name.
        if (!empty($email)) {
            return $config->map_email . "=$email";
        }

        // Otherwise we'll take the name.
        if (empty($firstname) and !empty($lastname)) {
            return $config->map_lastname . "=$lastname";
        }
        if (!empty($firstname) and empty($lastname)) {
            return $config->map_firstname . "=$firstname";
        }
        if (!empty($firstname) and !empty($lastname)) {
            return "(&({$config->map_lastname}=$lastname)({$config->map_firstname}=$firstname))";
        }

        // Everything must have been empty.
        return false;
    }

    /**
     * Build filter and search
     * @param string $firstname
     * @param string $lastname
     * @param string $guid
     * @param string $email
     * @param string $idnumber
     * @return [array, string]
     */
    public static function filter($firstname, $lastname, $guid, $email, $idnumber) {

        $result = [];
        $errormessage = '';

        if (!$filter = self::build_filter($firstname, $lastname, $guid, $email, $idnumber)) {
            $errormessage = get_string('filtererror', 'report_guid');
        }

        $result = self::ldap_search($filter);
        if (is_string( $result )) {
            $errormessage = get_string('searcherror', 'report_guid', $result);
        }
        if ($result === false) {
            $errormessage =  get_string('ldapsearcherror', 'report_guid');
        }

        return [$result, $errormessage];
    }

    /**
     * Find a user account using searching both username and idnumber
     * @param string $username
     * @param sting $idnumber
     * @return object
     */
    public static function find_user($username, $idnumber) {
        $config = get_config('local_guldap');

        // fields we want to (try to) extract - moodle => map
        $moodlefields = [
            'firstname' => 'map_firstname',
            'lastname' => 'map_lastname',
            'email' => 'map_email',
            'idnumber' => 'map_idnumber',
            'homeemailaddress' => 'map_homeemailaddress',
        ];

        list($results, $errormessage) = self::filter('', '', $username, '', '');
        $result = array_shift($results);
        if (!$result && !$errormessage) {
            list($results, $errormessage) = self::filter('', '', '', '', $idnumber);
            $result = array_shift($results);
        }

        // If we have a result, then lookup the field mappings
        $newuser = [];
        if ($result) {
            foreach ($moodlefields as $field => $mapping) {
                $newuser[$field] = isset($result[$config->$mapping]) ? $result[$config->$mapping] : '';
            }
        }

        return $newuser;
    }
}