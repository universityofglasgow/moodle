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
 * Admin settings and defaults.
 *
 * @package local_guldap
 * @copyright  2022 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_guldap;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/ldaplib.php');

class ldap {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->config = get_config('local_guldap');
        if (empty($this->config->ldapencoding)) {
            $this->config->ldapencoding = 'utf-8';
        }
        if (empty($this->config->user_type)) {
            $this->config->user_type = 'default';
        }

        $ldap_usertypes = ldap_supported_usertypes();
        $this->config->user_type_name = $ldap_usertypes[$this->config->user_type];
        unset($ldap_usertypes);

        $default = ldap_getdefaults();

        // Use defaults if values not given
        foreach ($default as $key => $value) {
            // watch out - 0, false are correct values too
            if (!isset($this->config->{$key}) or $this->config->{$key} == '') {
                $this->config->{$key} = $value[$this->config->user_type];
            }
        }

        // Hack prefix to objectclass
        $this->config->objectclass = ldap_normalise_objectclass($this->config->objectclass);
    }

    /**
     * Connect to the LDAP server, using the plugin configured
     * settings. It's actually a wrapper around ldap_connect_moodle()
     *
     * @return resource A valid LDAP connection (or dies if it can't connect)
     */
    function connect() {
        // Cache ldap connections. They are expensive to set up
        // and can drain the TCP/IP ressources on the server if we
        // are syncing a lot of users (as we try to open a new connection
        // to get the user details). This is the least invasive way
        // to reuse existing connections without greater code surgery.
        if(!empty($this->ldapconnection)) {
            $this->ldapconns++;
            return $this->ldapconnection;
        }

        if($ldapconnection = ldap_connect_moodle($this->config->host_url, $this->config->ldap_version,
                                                 $this->config->user_type, $this->config->bind_dn,
                                                 $this->config->bind_pw, $this->config->opt_deref,
                                                 $debuginfo, $this->config->start_tls)) {
            $this->ldapconns = 1;
            $this->ldapconnection = $ldapconnection;
            return $ldapconnection;
        }

        throw new \moodle_exception('auth_ldap_noconnect_all', 'auth_ldap', '', $debuginfo);
        return false;
    }

    /**
     * Disconnects from a LDAP server
     *
     * @param force boolean Forces closing the real connection to the LDAP server, ignoring any
     *                      cached connections. This is needed when we've used paged results
     *                      and want to use normal results again.
     */
    function close($force=false) {
        $this->ldapconns--;
        if (($this->ldapconns == 0) || ($force)) {
            $this->ldapconns = 0;
            @ldap_close($this->ldapconnection);
            unset($this->ldapconnection);
        }
    }

    /**
     * Process weirdly formatted LDAP results into something
     * We can display. 
     * @param array $entries
     */
    private function format_ldap($entries) {
    
        // First entry is the count, don't need it
        array_shift($entries);

        $formattedldap = [];

        foreach ($entries as $entry) {
            $newentry = [];
            foreach ($entry as $name => $value) {

                // Numeric fields don't contain data
                // plus check really is an array
                if (!is_array($value)) {
                    continue;
                }

                // First element is spurious count
                array_shift($value);

                // Either store the single data value
                // Or the array of data. 
                if (count($value) == 1) {
                    $newentry[$name] = $value[0];
                } else {
                    $newentry[$name] = $value;
                }
            }

            // Add dn value, which doesn't look like other data. 
            $newentry['dn'] = $entry['dn'];

            $formattedldap[] = $newentry;
        }

        return $formattedldap;
    }

    /**
     * LDAP Search
     * @param ldap connection
     * @param string search filter
     * @return array
     */
    public function search($dv, $filter) {

        // Search.
        if (!$search = @ldap_search($dv, $this->config->contexts, $filter)) {
            debugging('ldap search failed for filter "' . $filter . '" ' . ldap_error( $dv ));
            return false;
        }

        // Check for errors returned.
        // (particularly partial results as GUID is limited to 100).
        $errorcode = ldap_errno($dv);
        $errorstring = ldap_error($dv);

        // If error returned then...
        // Need to check for string.
        if ($errorcode != 0) {
            return $errorstring;
        }

        // Check if we got any results.
        if (ldap_count_entries($dv, $search) < 1) {
            return [];
        }

        // Get results.
        if (!$results = ldap_get_entries($dv, $search)) {
            debugging( 'Failed to extract ldap results '.ldap_error($dv));
            return false;
        }

        $results = $this->format_ldap($results);

        return $results;
    }
}