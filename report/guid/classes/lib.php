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
 * @copyright  2013-19 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_guid;

defined('MOODLE_INTERNAL') || die;

class lib {

    /**
     * Get all the settings from the GUID auth plugin
     */
    public static function settings() {
        $auth = get_auth_plugin('guid');
        $config = $auth->config;
        if (empty($config->host_url) || empty($config->contexts)) {
            throw new \Exception('host_url and contexts must be defined in auth_guid settings');
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
        if (ldap_count_entries($dv, $search) < 1) {
            return array();
        }

        // Get results.
        if (!$results = ldap_get_entries($dv, $search)) {
            debugging( 'Failed to extract ldap results '.ldap_error( $dv ) );
            return false;
        }

        //echo "<pre>"; var_dump($results); die;

        // Unravel results.
        //$results = array_map(function($entry) use ($dv) {
        //    return $entry->dn = ldap_get_dn($dv, $entry);
        //}, $results);
        $results = self::format_ldap( $results );

        return $results;
    }

    /**
     * Process weirdly formatted LDAP results into something
     * We can display. 
     * @param array $entries
     */
    public static function format_ldap($entries) {
    
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
    public static function format_enrolments($enrolments) {
        global $DB;

        if (empty($enrolments)) {
            return [];
        }
        $formattedenrolments = [];

        // Run through enrolments.
        foreach ($enrolments as $enrolment) {

            // Check target course actually exists
            if ($course = $DB->get_record('course', ['id' => $enrolment->courseid])) {
                $courselink = new \moodle_url('/course/view.php', ['id' => $enrolment->courseid]);
                $ended = ($course->enddate) && (time() > $course->enddate);
                $notstarted = time() < $course->startdate;
            } else {
                $courselink = '';
                $ended = false;
                $notstarted = false;
            }    
            if (empty($enrolment->timelastaccess)) {
                $lasttime = get_string('never');
            } else {
                $lasttime = date('d/M/y H:i', $enrolment->timelastaccess);
            }
            $formattedenrolments[] = (object)[
                'courselink' => $courselink,
                'name' => $enrolment->name,
                'lastaccess' => $lasttime,
                'ended' => $ended,
                'notstarted' => $notstarted,
            ];
        }

        return $formattedenrolments;
    }

    /**
     * print MyCampus data
     */
    public static function format_mycampus($courses, $guid) {

        // Normalise.
        $guid = strtolower( $guid );
        $formatted = [];

        if (empty($courses)) {
            return [];
        }

        // Run through the courses.
        foreach ($courses as $course) {
            $gucourses = self::mycampus_code($course->courses);
            foreach ($gucourses as $gucourse) {
                $gucourse->link = new \moodle_url('/course/view.php', ['id' => $gucourse->id]);
            }

            $formatted[] = (object)[
                'code' => $course->courses,
                'isnamed' => $course->name != '-',
                'name' => $course->name,
                'ou' => $course->ou,
                'gucourses' => array_values($gucourses),
                'isgucourses' => !empty($gucourses),
                'usernamemismatch' => $course->UserName != $guid,
            ];
        }

        return $formatted;
    }

    public static function array_prettyprint($rows) {
        $html = '';
        $html .=  '<dl class="row" style="line-height: 0.8rem">';
        foreach ($rows as $name => $row) {
            if (is_array( $row )) {
                $html .= '<dt class="col-sm-3">' . $name . '</dt>';
                $html .= '<dd class="col-sm-9">' . self::array_prettyprint( $row ) . '</dd>';;
            } else {
                $row = empty(trim($row)) ? '-' : $row;
                $html .= '<dt class="col-sm-3">' . $name . '</dt><dd class="col-sm-9">' . $row . '</dd>';
            }
        }
        $html .= '</dl>';

        return $html;
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
        if (!empty($result['workforceid']) && !empty($config->field_map_idnumber)) {
            $user->idnumber = $result[$config->field_map_idnumber];
        } else {
            $user->idnumber = '';
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

        $sql = 'SELECT cc.* from {course} cc
            JOIN {enrol_gudatabase_codes} egc ON egc.courseid = cc.id
            WHERE code = :code';
        $gucourses = $DB->get_records_sql($sql, ['code' => $code]);
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
            $group = (object) [
                'name' => $groupname,
                'description' => '',
                'enrolmentkey' => '',
                'courseid' => $courseid,
            ];
            $groupid = groups_create_group($group);
            return $groupid;
        }
    }

}
