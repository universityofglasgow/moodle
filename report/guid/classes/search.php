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
 * GUID report
 *
 * @package    report_guid
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class report_guid_search {

    /**
     * Get all the settings from the GUID auth plugin
     */
    public static function settings() {
        $auth = get_auth_plugin('guid');
        $config = $auth->config;
        if (empty($config->host_url) || empty($config->contexts)) {
            throw new Exception('host_url and contexts must be defined in auth_guid settings');
        }
        if (empty($config->field_map_firstname)) {
            $config->field_map_firstname = 'givenName';
        }
        if (empty($config->field_map_lastname)) {
            $config->field_map_lastname = 'sn';
        }
        if (empty($config->field_map_email)) {
            $config->field_map_email = 'mail';
        }
        if (empty($config->user_attribute)) {
            $config->user_attribute = 'cn';
        }
        $config->field_map_firstname = strtolower($config->field_map_firstname);
        $config->field_map_lastname = strtolower($config->field_map_lastname);
        $config->field_map_email = strtolower($config->field_map_email);

        return $config;
    }

    public static function build_filter($firstname, $lastname, $guid, $email, $idnumber) {
        $config = self::settings();

        // LDAP filter doesn't like escaped characters.
        $lastname = stripslashes( $lastname );
        $firstname = stripslashes( $firstname );

        // If the GUID is supplied then we don't care about anything else.
        if (!empty($guid)) {
            return $config->user_attribute . "=$guid";
        }

        // If the idnumber is supplied then we'll go for that.
        if (!empty($idnumber) & is_numeric($idnumber)) {
            return $config->field_map_idnumber . "=$idnumber";
        }

        // If the email is supplied then we don't care about name.
        if (!empty($email)) {
            return $config->field_map_email . "=$email";
        }

        // Otherwise we'll take the name.
        if (empty($firstname) and !empty($lastname)) {
            return $config->field_map_lastname . "=$lastname";
        }
        if (!empty($firstname) and empty($lastname)) {
            return $config->field_map_firstname . "=$firstname";
        }
        if (!empty($firstname) and !empty($lastname)) {
            return "(&({$config->field_map_lastname}=$lastname)({$config->field_map_firstname}=$firstname))";
        }

        // Everything must have been empty.
        return false;
    }

    /**
     * Build filter and search
     * @param object $output renderer
     * @param string $firstname
     * @param string $lastname
     * @param string $guid
     * @param string $email
     * @param string $idnumber
     * @return mixed
     */
    public static function filter($output, $firstname, $lastname, $guid, $email, $idnumber) {
        if (!$filter = self::build_filter($firstname, $lastname, $guid, $email, $idnumber)) {
            $output->ldap_error(get_string('filtererror', 'report_guid'));
            die;
        }
        $config = self::settings();
        $result = self::ldapsearch($config, $filter);
        if (is_string( $result )) {
            $output->ldap_error(get_string('searcherror', 'report_guid', $result));
            die;
        }
        if ($result === false) {
            $output->error(get_string('ldapsearcherror', 'report_guid'));
            die;
        }

        return $result;
    }

    public static function ldapsearch($config, $filter) {

        // Connect to host.
        if (!$dv = ldap_connect( $config->host_url )) {
            debugging( 'Failed to connect to ldap host ' );
            return false;
        }

        // Bind.
        if (empty($config->bind_dn)) {
            if (!ldap_bind( $dv )) {
                debugging( 'Failed anonymous bind to ldap host '.ldap_error( $dv ) );
                return false;
            }
        } else {
            if (!ldap_bind( $dv, trim($config->bind_dn), trim($config->bind_pw ))) {
                debugging( 'Failed bind to ldap host '.ldap_error( $dv ) );
                return false;
            }
        }

        // Search.
        if (!$search = @ldap_search($dv, $config->contexts, $filter)) {
            debugging( 'ldap search failed for filter "'.$filter.'" '.ldap_error( $dv ) );
            return false;
        }

        // Check for errors returned.
        // (particularly partial results as GUID is limited to 100).
        $errorcode = ldap_errno( $dv );
        $errorstring = ldap_error( $dv );

        // If error returned then...
        // Need to check for string.
        if ($errorcode != 0) {
            return $errorstring;
        }

        // Check if we got any results.
        if (ldap_count_entries( $dv, $search) < 1) {
            return array();
        }

        // Get results.
        if (!$results = ldap_get_entries($dv, $search)) {
            debugging( 'Failed to extract ldap results '.ldap_error( $dv ) );
            return false;
        }

        // Unravel results.
        $results = self::cleanup_entry( $results );

        return $results;
    }

    public static function cleanup_entry($entry) {
        $retentry = array();
        for ($i = 0; $i < $entry['count']; $i++) {
            if (is_array($entry[$i])) {
                $subtree = $entry[$i];

                // This condition should be superfluous so just take the recursive call
                // adapted to your situation in order to increase perf..
                if ( !empty($subtree['dn']) && !isset($retentry[$subtree['dn']])) {
                    $retentry[$subtree['dn']] = self::cleanup_entry($subtree);
                } else {
                    $retentry[] = self::cleanup_entry($subtree);
                }
            } else {
                $attribute = $entry[$i];
                if ( $entry[$attribute]['count'] == 1 ) {
                    $retentry[$attribute] = $entry[$attribute][0];
                } else {
                    for ($j = 0; $j < $entry[$attribute]['count']; $j++) {
                        $retentry[$attribute][] = $entry[$attribute][$j];
                    }
                }
            }
        }
        return $retentry;
    }

    /**
     * convert array guid to a string
     * pick a likely looking option from the string
     */
    public static function array_to_guid($guids) {

        // Some guids look like an email.
        $chosenguid = '';
        foreach ($guids as $guid) {
            if (strpos($guid, '@') !== false) {
                continue;
            }
            $chosenguid = $guid;
        }

        // If that doesn't help, just pick the first one.
        if (!$chosenguid) {
            $chosenguid = reset($guids);
        }

        return $chosenguid;
    }


    public static function get_email($result) {

        // Try to find an email address to use.
        if (!empty($result['mail'])) {
            return array( 'primary' => true, 'mail' => $result['mail'] );
        }
        if (!empty($result['homeemailaddress'])) {
            $mail = $result['homeemailaddress'];
            return array( 'primary' => false, 'mail' => $mail );
        }
        return array( 'primary' => true, 'mail' => '' );
    }

    /**
     * go and find enrollments across all Moodles
     * from external enrollment tables
     */
    public static function get_all_enrolments( $guid ) {
        global $CFG;

        // Get plugin config for local_gusync.
        $config = get_config('local_gusync');

        // Is that plugin configured?
        if (empty($config->dbhost)) {
            return false;
        }

        // Just use local_gusync's library functions.
        if (file_exists($CFG->dirroot . '/local/gusync/lib.php')) {
            require_once($CFG->dirroot . '/local/gusync/lib.php');
        } else {
            return false;
        }

        // Attempt to connect to external db.
        if (!$extdb = local_gusync_dbinit($config)) {
            return false;
        }

        // SQL to find user enrolments.
        $sql = "select * from moodleenrolments join moodlecourses ";
        $sql .= "on (moodleenrolments.moodlecoursesid = moodlecourses.id) ";
        $sql .= "where guid='" . addslashes( $guid ) . "' ";
        $sql .= "order by site, timelastaccess desc ";
        $enrolments = local_gusync_query( $extdb, $sql );

        $extdb->Close();
        if (count($enrolments) == 0) {
            return array();
        } else {
            return $enrolments;
        }
    }

    /**
     * print enrolments
     */
    public static function print_enrolments( $enrolments, $name, $guid ) {
        global $OUTPUT;

        echo $OUTPUT->box_start();
        echo $OUTPUT->heading(get_string('enrolments', 'report_guid', $name));

        // Old site to see when site changes.
        $oldsite = '';

        // Run through enrolments.
        foreach ($enrolments as $enrolment) {
            $newsite = $enrolment->site;
            if ($newsite != $oldsite) {
                $sitelink = $enrolment->wwwroot;
                echo "<p>&nbsp;</p>";
                echo "<h3>".get_string('enrolmentsonsite', 'report_guid', "<a href=\"$sitelink\">$newsite</a>")."</h3>";
                $profilelink = $enrolment->wwwroot . '/user/view.php?id=' . $guid;
                $oldsite = $newsite;
            }
            $courselink = $enrolment->wwwroot . '/course/view.php?id=' . $enrolment->courseid;
            if (empty($enrolment->timelastaccess)) {
                $lasttime = get_string('never');
            } else {
                $lasttime = date( 'd/M/y H:i', $enrolment->timelastaccess );
            }
            echo "<a href=\"$courselink\">{$enrolment->name}</a> <i>(accessed $lasttime)</i><br />";
        }

        echo $OUTPUT->box_end();
    }

    /**
     * print MyCampus data
     */
    public static function print_mycampus($courses, $guid) {
        global $OUTPUT;

        // Normalise.
        $guid = strtolower( $guid );

        // Title.
        echo $OUTPUT->box_start();
        echo $OUTPUT->heading(get_string('mycampus', 'report_guid'));

        // Did we pick up any guid mismatches.
        $mismatch = false;

        // Run through the courses.
        foreach ($courses as $course) {
            $gucourses = self::mycampus_code($course->courses);
            echo "<p><strong>{$course->courses}</strong> ";
            if ($course->name != '-') {
                echo "'{$course->name}' in '{$course->ou}' ";
            }

            // Check for username discrepancy.
            if ($course->UserName != $guid) {
                echo "as <span class=\"label label-warning\">{$course->UserName}</span> ";
                $mismatch = true;
            }

            echo '<br />';

            // Display local courses (if there are any).
            if (strpos($course->courses, '*') === false) {
                echo '<small>';
                if ($gucourses) {
                    $links = array();
                    foreach ($gucourses as $gu) {
                        $link = new moodle_url('/course/view.php', array('id' => $gu->courseid));
                        $links[] = '<a href="' . $link . '">&quot;' . $gu->coursename . '&quot;</a>';
                    }
                    echo implode(', ', $links);
                } else {
                    echo get_string('nolocalcourses', 'report_guid');
                }
                echo "</small></p>";
            }
        }

        // Mismatch?
        if ($mismatch) {
            echo "<p><span class=\"label label-warning\">".get_string('guidnomatch', 'report_guid')."</span></p>";
        }

        echo $OUTPUT->box_end();
    }

    public static function array_prettyprint( $rows ) {
        echo "<ul>\n";
        foreach ($rows as $name => $row) {
            if (is_array( $row )) {
                echo "<li><strong>$name:</strong>";
                self::array_prettyprint( $row );
                echo "</li>\n";
            } else {
                echo "<li><strong>$name</strong> => $row</li>\n";
            }
        }
        echo "</ul>\n";
    }

    // Create new Moodle user.
    public static function create_user_from_ldap($result) {
        global $DB;

        $config = self::settings();

        // Check if multiple uids.
        $guid = $result[$config->user_attribute];
        if (is_array($guid)) {
            $guid = self::array_to_guid($guid);
        }

        // Sanity check that guid doesn't already exist.
        if ($user = $DB->get_record('user', array('username' => $guid))) {
            return $user;
        }

        $user = create_user_record( strtolower($guid), 'not cached', 'guid' );
        $user->firstname = $result[$config->field_map_firstname];
        $user->lastname = $result[$config->field_map_lastname];
        if (!empty($result['workforceid'])) {
            $user->idnumber = $result[$config->field_map_idnumber];
        }
        $user->city = 'Glasgow';
        $user->country = 'GB';
        $mailinfo = self::get_email( $result );
        $user->email = $mailinfo['mail'];
        if (!empty( $user->email )) {
            $DB->update_record( 'user', $user );

            // If not primary email make this email private.
            if (!$mailinfo['primary']) {
                $DB->set_field( 'user', 'maildisplay', 0, array('id' => $user->id));
            }
        }

        return $user;
    }

    // Find details about MyCampus codes.
    private static function mycampus_code($code) {
        global $DB;

        $gucourses = $DB->get_records('enrol_gudatabase_codes', array('code' => $code));
        return $gucourses;
    }

    /**
     * Get existing Moodle users that match
     * @return array
     */
    public static function user_search($firstname, $lastname, $guid, $email, $idnumber) {
        global $DB;

        $config = self::settings();

        // If the GUID is supplied then we don't care about anything else.
        if (!empty($guid)) {
            return $DB->get_records('user', array('username' => $guid, 'deleted' => 0));
        }

        // If the idnumber is supplied then we'll go for that.
        if (!empty($idnumber) & is_numeric($idnumber)) {
            return $DB->get_records('user', array('idnumber' => $idnumber, 'deleted' => 0));
        }

        // If the email is supplied then we don't care about name.
        if (!empty($email)) {
            return $DB->get_records('user', array('email' => $email, 'deleted' => 0));
        }

        // Otherwise we'll take the name.
        $sql = 'SELECT * FROM {user} WHERE deleted=0 AND ';
        $params = array();

        if (empty($firstname) && empty($lastname)) {
            return false;
        }
        if (!empty($firstname)) {
            if (strpos($firstname, '*') === false) {
                $sql .= $DB->sql_compare_text('firstname') . '= :firstname ';
            } else {
                $sql .= $DB->sql_like('firstname', ':firstname', false, false) . ' ';
                $firstname = str_replace('*', '%', $firstname);
            }
            $params['firstname'] = $firstname;
            if (!empty($lastname)) {
                $sql .= ' AND ';
            }
        }
        if (!empty($lastname)) {
            if (strpos($lastname, '*') === false) {
                $sql .= $DB->sql_compare_text('lastname') . '= :lastname ';
            } else {
                $sql .= $DB->sql_like('lastname', ':lastname', false, false);
                $lastname = str_replace('*', '%', $lastname);
            }
            $params['lastname'] = $lastname;
        }
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Add number of enrolled courses to user array
     * @param array $users
     * @return array
     */
    public static function add_enrol_counts($users) {
        foreach ($users as $user) {
            $courses = enrol_get_users_courses($user->id, true);
            $user->enrolcount = count($courses);
        }

        return $users;
    }

    /**
     * Check for duplicate username
     * @param string $newusername
     * @return mixed false or links to duplicate users
     */
    public static function isduplicate($newusername) {
        global $DB;

        if (!$users = $DB->get_records('user', array('username' => $newusername))) {
            return false;
        }

        return $users;
    }

    /**
     * Get student role from archetype
     * Just so we can use it as default
     * @return object or false
     */
    public static function getstudentrole() {
        global $DB;

        if ($studentroles = $DB->get_records('role', array('archetype' => 'student'))) {
            return reset($studentroles);
        } else {
            return false;
        }
    }

    /**
     * Find user in moodle
     * @param string $usermatch matching criteria
     * @param string $criteria currently guid (username) or idnumber
     * @return mixed user object or false
     */
    public static function findmoodleuser($usermatch, $criteria) {
        global $DB;

        if ($criteria == 'guid') {
            $criteria = 'username';
        }
        return $DB->get_record('user', array($criteria => $usermatch));
    }

    /**
     * Creat group (after checking if it exists)
     * @param string $groupname
     * @param int $courseid
     * @return int groupid
     */
    public static function create_group($groupname, $courseid) {
        if ($groupid = groups_get_group_by_name($courseid, $groupname)) {
            return $groupid;
        } else {
            $group = new stdClass;
            $group->name = $groupname;
            $group->description = '';
            $group->enrolmentkey = '';
            $group->courseid = $courseid;
            $groupid = groups_create_group($group);
            return $groupid;
        }
    }

}
