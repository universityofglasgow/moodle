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
 * Moodle frontpage.
 *
 * @package    auth_guid
 * @copyright  2013-19 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/auth/ldap/auth.php' );

class auth_plugin_guid extends auth_plugin_ldap {

    /**
     * constructor
     */
    public function __construct() {
        parent::__construct();
        $this->authtype = 'guid';
        $this->errorlogtag = '[AUTH GUID]';
        $this->init_plugin($this->authtype);

        // stops notice when it isn't defined
        // $this->config->start_tls = false;
    }

    /**
     * translate college code to name
     * (No point translating these strings)
     * @param int $code single digit of cost centre code
     * @return string College name
     */
    private function translate_college_code($code) {
        $colleges = array(
            1 => 'College of Arts',
            2 => 'College of Medical Veterinary and Life Sciences',
            3 => 'College of Science and Engineering',
            4 => 'College of Social Sciences',
            9 => 'University Services',
        );
        if (isset($colleges[$code])) {
            return $colleges[$code];
        } else {
            return '';
        }
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (without system magic quotes)
     * @param string $password The password (without system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        if (! function_exists('ldap_bind')) {
            print_error('auth_ldapnotinstalled', 'auth_ldap');
            return false;
        }

        if (!$username or !$password) {
            return false;
        }

        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);
        $extpassword = core_text::convert($password, 'utf-8', $this->config->ldapencoding);

        $ldapconnection = $this->ldap_connect();
        $ldap_user_dn = $this->ldap_find_userdn($ldapconnection, $extusername);

        // If ldap_user_dn is empty, user does not exist.
        if (!$ldap_user_dn) {
            $this->ldap_close();
            return false;
        }

        // If we get this far (i.e. have a valid user) and debugmode
        // is on then we'll just say they are logged in (i.e. skip password check)
        // TESTING ONLY (obviously).
        if ($this->config->debugmode) {
            $this->ldap_close();
            return true;
        }

        // University of Glasgow ugly hack
        // use compare rather than bind to make sure all possible
        // users authenticate.
        // Try to compare with current username and password.
        $ldap_login = ldap_compare($ldapconnection, $ldap_user_dn, 'userPassword', $extpassword);
        $this->ldap_close();
        // Need this because ldap_compare returns -1 for error.
        if ($ldap_login === true) {
            return true;
        }

        return false;
    }

    /**
     * Reads user information from ldap and returns it in array()
     *
     * Function should return all information available. If you are saving
     * this information to moodle user-table you should honor syncronization flags
     *
     * @param string $username username
     * @param string $matricid student matric number
     *
     * @return mixed array with no magic quotes or false on error
     */
    public function get_userinfo($username, $matricid='') {
        global $SESSION;

        // Because some of the ldap searches are slow
        // we'll set this high.
        set_time_limit(2 * 60);

        // Make sure username is utf-8.
        $extusername = core_text::convert($username, 'utf-8', $this->config->ldapencoding);

        // Find user in ldap
        // we first attempt to find using the username (fast). If that fails,
        // we try with the matricid (if supplied). The latter is much slower
        // but more reliable.
        // NOTE: using two different ldap_find_userdn() functions here!!
        $ldapconnection = $this->ldap_connect();
        if (!($user_dn = $this->ldap_find_userdn($ldapconnection, $extusername))) {
            if (!empty($matricid)) {
                $contexts = explode(';', $this->config->contexts);
                if (!$user_dn = ldap_find_userdn($ldapconnection, $matricid, $contexts, $this->config->objectclass, 'workforceid', $this->config->search_sub)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // TODO: The user_dn tells us a lot about the user (e.g. student)
        // We should really do something with this.
        $search_attribs = array();
        $attrmap = $this->ldap_attributes();
        foreach ($attrmap as $key => $values) {
            if (!is_array($values)) {
                $values = array($values);
            }
            foreach ($values as $value) {
                if (!in_array($value, $search_attribs)) {
                    array_push($search_attribs, $value);
                }
            }
        }

        // Ugly University of Glasgow hack
        // add additional fields to search attributes to get
        // optional emailaddress field and uid.
        $search_attribs[] = 'homeemailaddress';
        $search_attribs[] = 'costcenter';

        // Make sure uid is in the list.
        if (!in_array('uid', $search_attribs)) {
            $search_attribs[] = 'uid';
        }

        if (!$user_info_result = ldap_read($ldapconnection, $user_dn, '(objectClass=*)', $search_attribs)) {
            return false;
        }

        $user_entry = ldap_get_entries_moodle($ldapconnection, $user_info_result);
        if (empty($user_entry)) {
            return false;
        }

        // University of Glasgow Ugly Hack
        // if 'mail' field is empty consider using 'homeemailaddress'
        // field (if not empty).
        // As this is their
        // private email address they will need their email
        // visibility set to hidden. This will be stored for later
        // so we can check the visibility setting.
        $SESSION->gu_email = '';
        if (empty($user_entry[0]['mail'][0])) {
            if (!empty($user_entry[0]['homeemailaddress'][0])) {
                // Check for '3#' code and strip.
                $emailaddress = $user_entry[0]['homeemailaddress'][0];
                $SESSION->gu_email = $emailaddress;
            }
        }

        // Get the uid result
        // this is the proper GUID.
        if (!empty($user_entry[0]['uid'][0])) {
            $uid = $user_entry[0]['uid'][0];
        } else {
            $uid = '';
        }

        $result = array();
        foreach ($attrmap as $key => $values) {
            if (!is_array($values)) {
                $values = array($values);
            }
            $ldapval = null;
            foreach ($values as $value) {
                $entry = array_change_key_case($user_entry[0], CASE_LOWER);
                if (($value == 'dn') || ($value == 'distinguishedname')) {
                    $result[$key] = $user_dn;
                    continue;
                }
                if (!array_key_exists($value, $entry)) {
                    continue;
                }
                if (is_array($entry[$value])) {
                    $newval = core_text::convert($entry[$value][0], $this->config->ldapencoding, 'utf-8');
                } else {
                    $newval = core_text::convert($entry[$value], $this->config->ldapencoding, 'utf-8');
                }
                if (!empty($newval)) {
                    $ldapval = $newval;
                }
            }
            if (!is_null($ldapval)) {
                $result[$key] = $ldapval;
            }
        }

        // Get CoreHR data and check for 'known as' name.
        // only if not a student
        $isstudent = strpos($user_dn, 'ou=student') !== false;
        if (!$isstudent) {
            $corehr = \local_corehr\api::get_extract($username);
            if ($corehr) {
                if (trim($corehr->knownas)) {
                    $result['firstname'] = trim($corehr->knownas);
                }

                $result['institution'] = $corehr->collegedesc;
                $result['department'] = $corehr->schooldesc;
            }
        }

        // Check for 'proper' staff for CoreHR auto enrol
        $isstaff = strpos($user_dn, 'ou=staff') !== false;
        if ($isstaff) {
            \local_corehr\api::auto_enrol($username);
        }

        $this->ldap_close();
        $result['dn'] = $user_dn;
        $result['uid'] = $uid;
        return $result;
    }


    public function user_authenticated_hook( &$user, $username, $password ) {
        // Ugly University of Glasgow Hack
        // we're just going to use this to make sure that 'city' and
        // 'country' are set to something. If not we'll go for
        // 'Glasgow' and 'GB'.
        global $SESSION, $CFG, $SITE, $DB, $OUTPUT;

        // Check city.
        if (empty($user->city)) {
            $DB->set_field( 'user', 'city', 'Glasgow', array('id' => $user->id));
            $user->city = 'Glasgow';
        }

        // Check country.
        if (empty($user->country)) {
            $DB->set_field( 'user', 'country', 'GB', array('id' => $user->id));
            $user->country = 'GB';
        }

        // If the user doesn't have an email address
        // and $SESSION->gu_email exists we can use that but
        // we must hide their email address too (privacy)
        // if the gu_email is set then there is no 'mail' field
        // in GUID and we can safely use it to update the record every time.
        if (!empty($SESSION->gu_email)) {
            $DB->set_field( 'user', 'email', $SESSION->gu_email, array('id' => $user->id));

            // If they didn't have email set then this is the first time
            // so make the email private (they can unset this if they want).
            if (empty($user->email)) {
                $DB->set_field('user', 'maildisplay', 0, array('id' => $user->id));
            }

            $user->email = $SESSION->gu_email;
        }

        // If still no email then message.
        if (empty($user->email)) {
            echo $OUTPUT->header(strip_tags($SITE->fullname), $SITE->fullname, 'home');
            notice(get_string('noemail', 'auth_guid'), $CFG->wwwroot);
        }
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    public function process_config($config) {
        // Set to defaults if undefined.
        if (!isset($config->debugmode)) {
            $config->debugmode = '';
        }

        // Save settings.
        set_config('debugmode', $config->debugmode, $this->pluginconfig);

        // Do the LDAP settings.
        parent::process_config($config);

        return true;
    }

    /**
     * A chance to validate form data, and last chance to do stuff
     * before it is inserted in config_plugin
     * (We just want to avoid ntlm checks in LDAP)
     *
     * @param object object with submitted configuration settings (without system magic quotes)
     * @param array $err array of error messages (passed by reference)
     */
    public function validate_form($form, &$err) {
        return;
    }

    /**
     * Hook for overriding behaviour of login page.
     * This method is called from login/index.php page for all enabled auth plugins.
     *
     * This is used to catch Sharepoint sending us the 'urltogo' parameter
     * in their fake login form.
     * @global object
     * @global object
     */
    function loginpage_hook() {
        global $CFG, $SESSION, $USER;

        $urltogo = optional_param('urltogo', '', PARAM_URL);
        $username = optional_param('username', '', PARAM_USERNAME);
        if (!$urltogo) {
            return;
        }

        // make sure it points to *this* site
        if (strpos($urltogo, $CFG->wwwroot) === 0 or strpos($urltogo, str_replace('http://', 'https://', $CFG->wwwroot)) === 0) {

            // If the user is already logged in, we just want to go to 'urltogo' with no further messing
            if (isloggedin() and !isguestuser()) {

                // username has to match (i.e. from form)
                if (strtolower($USER->username) == strtolower(trim($username))) {
                    redirect($urltogo);
                }
            } else {
                $SESSION->wantsurl = $urltogo;
            }
        }

        return;
    }

}
