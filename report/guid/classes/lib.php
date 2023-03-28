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
        //$auth = get_auth_plugin('guid');
        $config = get_config('local_guldap');
        if (!\local_guldap\api::isenabled()) {
            throw new \Exception('host_url and contexts must be defined in local_guldap settings');
        }

        return $config;
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

        $config = self::settings();

        // Try to find an email address to use.
        if (!empty($result['mail'])) {
            return array( 'primary' => true, 'mail' => $result['mail'] );
        }
        if (!empty($result[$config->map_homeemailaddress])) {
            $mail = $result[$config->map_homeemailaddress];
            return array( 'primary' => false, 'mail' => $mail );
        }
        return array( 'primary' => true, 'mail' => '' );
    }

    /**
     * go and find enrollments across all Moodles
     * from external enrollment tables
     * @param object $user
     * @return array
     */
    public static function get_all_enrolments($user) {
        global $CFG;

        // Get student's courses
        $fields = ['id', 'fullname', 'shortname', 'visible', 'enddate'];
        $courses = enrol_get_all_users_courses($user->id, false, $fields);
        if (!$courses) {
            return [];
        } else {
            return $courses;
        }
    }

    /**
     * print enrolments
     */
    public static function format_enrolments($userid, $courses) {
        global $DB;

        if (empty($courses)) {
            return [];
        }
        $formattedenrolments = [];

        // Get GCAT customfield id
        if ($customfield = $DB->get_record('customfield_field', ['shortname' => 'show_on_studentdashboard'])) {
            $gcatid = $customfield->id;
        } else {
            $gcatid = 0;
        }

        // Run through enrolments.
        foreach ($courses as $course) {
            $courselink = new \moodle_url('/course/view.php', ['id' => $course->id]);
            $ended = ($course->enddate) && (time() > $course->enddate);
            $notstarted = time() < $course->startdate;
    
            if (!$lastaccess = $DB->get_record('user_lastaccess', ['userid' => $userid, 'courseid' => $course->id])) {
                $lasttime = get_string('never');
            } else {
                $lasttime = userdate($lastaccess->timeaccess);
            }
            if ($DB->get_record('customfield_data', ['fieldid' => $gcatid, 'instanceid' => $course->id, 'intvalue' => 1])) {
                $gcatenabled = true;
            } else {
                $gcatenabled = false;
            }
            $formattedenrolments[] = (object)[
                'courselink' => $courselink,
                'name' => $course->fullname,
                'lastaccess' => $lasttime,
                'ended' => $ended,
                'notstarted' => $notstarted,
                'gcatenabled' => $gcatenabled,
                'hidden' => !$course->visible,
            ];
        }

        return $formattedenrolments;
    }

    /**
     * print MyCampus data
     */
    public static function format_mycampus($courses, $guid, $enrolments) {

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
                $gucourse->enrolled = array_key_exists($gucourse->id, $enrolments);
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
        if (!$rows) {
            return '';
        }
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

        $user = create_user_record( strtolower($guid), 'not cached', 'saml2' );
        $user->firstname = $result[$config->map_firstname];
        $user->lastname = $result[$config->map_lastname];
        if (!empty($result['workforceid']) && !empty($config->map_idnumber)) {
            $user->idnumber = $result[$config->map_idnumber];
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

        /*$sql = 'SELECT cc.* from {course} cc
            JOIN {enrol_gudatabase_codes} egc ON egc.courseid = cc.id
            WHERE code = :code';*/
        $sql = 'SELECT cc.* from {course} cc
            WHERE cc.id IN (SELECT courseid FROM {enrol_gudatabase_codes} WHERE code = :code GROUP BY code)';
        $gucourses = $DB->get_records_sql($sql, ['code' => $code]);
        return $gucourses;
    }

    /**
     * Get existing Moodle users that match
     * @return array
     */
    public static function user_search($firstname, $lastname, $guid, $email, $idnumber) {
        global $DB;

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

    /**
     * Get Turnitin EULA status
     * @param int $userid
     * @return boolean
     */
    public static function get_tii_eula($userid) {
        global $DB;

        if ($tiiuser = $DB->get_record('plagiarism_turnitin_users', ['userid' => $userid])) {
            return $tiiuser->user_agreement_accepted == 1;
        } else { 
            return false;
        }
    }

    /**
     * Get activity url from cmid
     * @param int cmid
     * @return string
     */
    private static function get_cm_link($cmid) {
        global $CFG, $DB;

        if ($cm = $DB->get_record('course_modules', ['id' => $cmid])) {
            $module = $DB->get_record('modules', ['id' => $cm->module], '*', MUST_EXIST);
            $link = $CFG->wwwroot . '/mod/' . $module->name . '/view.php?id=' . $cmid;
        } else {
            $link = '-';
        }

        return $link;
    }

    /**
     * Get Turnitin data
     * @param int $userid
     * @param string $guid
     * @return array
     */
    public static function get_turnitin($userid, $guid) {
        global $DB;

        if ($tiifiles = $DB->get_records('plagiarism_turnitin_files', ['userid' => $userid])) {
            foreach ($tiifiles as $tiifile) {
                $tiifile->formattedexternalid = !empty($tiifile->externalid) ? $tiifile->externalid : '-';
                $tiifile->formattedsimilarityscore = $tiifile->similarityscore !== null ? $tiifile->similarityscore : '-';
                $tiifile->formattedlastmodified = userdate($tiifile->lastmodified);
                $tiifile->errortext = !empty($tiifile->errorcode) ? get_string('errorcode' . $tiifile->errorcode, 'plagiarism_turnitin') : ' ';
                $tiifile->oktoresend = $tiifile->statuscode != 'queued';
                $tiifile->resendlink = new \moodle_url('/report/guid/index.php', [
                    'guid' => $guid,
                    'action' => 'tiiresend',
                    'tid' => $tiifile->id,
                ]);
                $tiifile->link = self::get_cm_link($tiifile->cm);
            }
            return array_values($tiifiles);
        } else {
            return false;
        }
    }

    /**
     * Reset turnitin submission
     * @param int $tid
     */
    public static function reset_turnitin($tid) {
        global $DB;

        $plagiarismfile = $DB->get_record('plagiarism_turnitin_files', ['id' => $tid], '*', MUST_EXIST);
        $plagiarismfile->statuscode = 'queued';
        $plagiarismfile->similarityscore = null;
        $plagiarismfile->errorcode = null;
        $plagiarismfile->errormsg = null;
        $DB->update_record('plagiarism_turnitin_files', $plagiarismfile);
    }

    /**
     * Get plan for individual user
     * @param object $user
     */
    public static function get_plan_for_user($user) {
        $gudatabase = enrol_get_plugin('gudatabase');
        $gudatabase->external_programdata($user);
    }

    /**
     * Populate user plan data
     */
    public static function populate_user_plan() {
        global $CFG, $DB;

        $gudatabase = enrol_get_plugin('gudatabase');

        // Get all users logged in within previous 12 months
        $lasttime = time() - (365 * 24 * 3600);
        $select = "lastlogin > " . $lasttime;
        $users = $DB->get_recordset_select('user', $select);
        foreach ($users as $user) {
            $gudatabase->external_programdata($user);
        }

        $users->close();
    }

    /**
     * Check add user profile field categories and fields
     */
    public static function check_create_userprofile() {
        global $CFG, $DB;

        require_once($CFG->dirroot.'/user/profile/definelib.php');

        // Category.
        $categoryname = 'Student Plan';

        // Fields.
        $fields = [
            'program' => 'program name',
            'year' => 'year of study',
            'school' => 'school code',
            'costcode' => 'Cost centre code',
            'ugpg' => 'Undergrad, Postgrad etc.',
            'method' => 'Method of study',
            'attendance' => 'Attendance',
            'finalyear' => 'Final year flag',
        ];

        // Category for student plan fields
        if (!$category = $DB->get_record('user_info_category', ['name' => $categoryname])) {
            $category = new \stdClass;
            $category->name = $categoryname;
            \profile_save_category($category);
            var_dump($category);
        }

        // Fields to check
        foreach ($fields as $name => $description) {
            if (!$field = $DB->get_record('user_info_field', ['shortname' => $name])) {
                $field = new \stdClass;
                $field->shortname = $name;
                $field->name = $name;
                $field->datatype = 'text';
                $field->description['text'] = $description;
                $field->description['format'] = 1;
                $field->categoryid = $category->id;
                $field->sortorder = 1;
                $field->required = 0;
                $field->locked = 1;
                $field->visible = 3;
                $field->forceunique = 0;
                $field->signup = 0;
                $field->defaultdata = 0;
                $field->defaultdataformat = 0;
                \profile_save_field($field, []);
            }
        }
    }

}
