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
 * UofG Database enrolment plugin.
 *
 * This plugin synchronises enrolment and roles with external database table.
 *
 * @package    enrol
 * @subpackage gudatabase
 * @copyright  2012 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Use of custom enrol table fields
 * customint1 - role id if change role on expire is used
 * customint2 - 0/1, create course groups if = 1
 * customint3 - 0/1, also get codes from shortname/idnumber if = 1
 * customint4 - 0/1, allow unenrol if = 1
 * customint5 - 0/1, allow remove from groups if = 1
 * customint6 - 0/1, allow enrol/unenrol even if course hidden if = 1
 *
 * customtext1 - code list
 * customtext2 - (serialised) groups enabled
 */

defined('MOODLE_INTERNAL') || die();

require_once( $CFG->dirroot . '/group/lib.php' );

// We inherit from vanilla database plugin.
require_once( $CFG->dirroot . '/enrol/database/lib.php' );

/**
 * UofG Database enrolment plugin implementation.
 * @author  Howard Miller - inherited from code by Petr Skoda
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_gudatabase_plugin extends enrol_database_plugin {

    // Need to store this for error function.
    protected $trace;

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     * @return bool
     */
    public function instance_deleteable($instance) {
        return true;
    }

    /**
     * Does this plugin assign protected roles are can they be manually removed?
     * @return bool - false means anybody may tweak roles, it does not use itemid and component when assigning roles
     */
    public function roles_protected() {
        return true;
    }


    /**
     * Does this plugin allow manual unenrolment of a specific user?
     * Yes, but only if user suspended...
     *
     * @param stdClass $instance course enrol instance
     * @param stdClass $ue record from user_enrolments table
     *
     * @return bool - true means user with 'enrol/xxx:unenrol'
     * may unenrol this user, false means nobody may touch this user enrolment
     */
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        return true;
    }

    /**
     * Does this plugin allow manual changes in user_enrolments table?
     *
     * All plugins allowing this must implement 'enrol/xxx:manage' capability
     *
     * @param stdClass $instance course enrol instance
     * @return bool - true means it is possible to change enrol period and status in user_enrolments table
     */
    public function allow_manage(stdClass $instance) {
        return true;
    }

    /**
     * Does this plugin allow manual unenrolment of all users?
     * All plugins allowing this must implement 'enrol/xxx:unenrol' capability
     *
     * @param stdClass $instance course enrol instance
     * @return bool - true means user with 'enrol/xxx:unenrol'
     * may unenrol others freely, false means nobody may touch user_enrolments
     */
    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    /**
     * Returns localised name of enrol instance
     *
     * @param stdClass $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        global $DB;

        if (empty($instance->name)) {
            if (!empty($instance->roleid) and $role = $DB->get_record('role', array('id' => $instance->roleid))) {
                $role = ' (' . role_get_name($role, context_course::instance($instance->courseid, IGNORE_MISSING)) . ')';
            } else {
                $role = '';
            }
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol) . $role;
        } else {
            return format_string($instance->name);
        }
    }

    /**
     * Return true if we can add a new instance to this course.
     *
     * @param int $courseid
     * @return boolean
     */
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/gudatabase:config', $context)) {
            return false;
        }

        return true;
    }

    /**
     * We are a good plugin and don't invent our own UI/validation code path.
     *
     * @return boolean
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability('enrol/gudatabase:unenrol', $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'),
                    $url, array('class' => 'unenrollink', 'rel' => $ue->id));
        }
        return $actions;
    }

    /**
     * Check if this appears to be configured
     * @return boolean
     */
    public function is_configured() {
        if (!$this->get_config('dbtype') or
                !$this->get_config('dbhost') or
                !$this->get_config('remoteenroltable') or
                !$this->get_config('remotecoursefield') or
                !$this->get_config('remoteuserfield')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * synchronise enrollments for particular course
     * @param object $course
     */
    public function sync_course_enrolments($course) {
        global $CFG, $DB;

        if (!$this->is_configured()) {
            return false;
        }
    }

    /**
     * split the course code into an array accounting
     * for multiple delimeters etc.
     * @param string $code (list of) course codes
     * @return array array of course codes
     */
    public function split_code($code) {

        // Split on comma or space.
        $codes = preg_split("/[\s,]+/", $code, null, PREG_SPLIT_NO_EMPTY );

        return $codes;
    }

    /**
     * get enrollment data from external table
     * @param array $codes list of course codes
     * @param string $userid user id
     * @return array
     */
    public function external_enrolments($codes=null, $userid=null) {
        global $CFG, $DB;

        // Codes and userid can't both be null.
        if (!$codes && !$userid) {
            $this->error( 'A value must be supplied for codes or userid in external_enrolments' );
            return false;
        }

        // Connect to external db.
        if (!$extdb = $this->db_init()) {
            $this->error('Error while communicating with external enrolment database');
            return false;
        }

        // Get connection details.
        $table            = $this->get_config('remoteenroltable');
        $coursefield      = strtolower($this->get_config('remotecoursefield'));
        $userfield        = strtolower($this->get_config('remoteuserfield'));

        // Work out appropriate sql.
        $sql = "select * from $table where ";

        // If $codes is supplied.
        if (!empty( $codes )) {
            $quotedcodes = array();
            foreach ($codes as $code) {
                $quotedcodes[] = "'" . $this->db_addslashes($code) . "'";
            }
            $codestring = implode(',', $quotedcodes);
            $sql .= "$coursefield in ($codestring) ";
        } else if (!empty($userid)) {
            $sql .= "$userfield = '" . $this->db_addslashes($userid) . "'";
        }

        // Read the data from external db.
        $enrolments = array();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($row = $rs->FetchRow()) {
                    $enrolment = (object)$row;
                    $enrolments[] = $enrolment;
                }
            }
            $rs->Close();
        } else {
            $msg = $extdb->ErrorMsg();
            $this->error('Error executing query in UofG enrolment table "'.$msg.'" - '.$sql);
            return false;
        }

        $extdb->Close();
        return $enrolments;
    }

    /**
     * split code into alpha and numeric bits
     */
    private function decode_code($code) {
        preg_match( '/^([[:alpha:]]+)(.+)/ ', $code, $matches );
        return array($matches[1], $matches[2]);
    }

    /**
     * Get list of classes for given code
     * @param string $code
     * @param return array
     */
    public function external_classes($code) {
        global $CFG, $DB;

        // Connect to external db.
        if (!$extdb = $this->db_init()) {
            $this->error('Error while communicating with external enrolment database');
            return false;
        }

        // Get table name from plugin config.
        $table = $this->get_config('classlisttable');

        list($subjectcode, $catcode) = $this->decode_code($code);

        $sql = "select ClassGroupDesc from $table where ";
        $sql .= "SubjectCode = '$subjectcode' and CourseCatCode = '$catcode' ";
        $sql .= "group by ClassGroupDesc";

        // Read the data from external db.
        $classes = array();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($row = $rs->FetchRow()) {
                    $class = (object)$row;
                    $classname = $class->ClassGroupDesc;
                    $classes[$classname] = $classname;
                }
            }
            $rs->Close();
        } else {
            $msg = $extdb->ErrorMsg();
            $this->error('Error executing query in UofG enrolment table "'.$msg.'" - '.$sql);
            return false;
        }

        $extdb->Close();
        return $classes;
    }

    /**
     * Get list of users for given class
     * @param string $code
     * @param string $class 'lecture', 'fieldwork' etc.
     * @param return array
     */
    public function external_class_users($code, $class) {
        global $CFG, $DB;

        // Connect to external db.
        if (!$extdb = $this->db_init()) {
            $this->error('Error while communicating with external enrolment database');
            return false;
        }

        // Get table name from plugin config.
        $table = $this->get_config('classlisttable');

        list($subjectcode, $coursecatcode) = $this->decode_code($code);

        $sql = "select matricno, ClassGroupCode from $table where ";
        $sql .= "SubjectCode = '$subjectcode' and CourseCatCode = '$coursecatcode' ";
        $sql .= "and ClassGroupDesc = '$class' ";

        // Read the data from external db.
        $matricnos = array();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($row = $rs->FetchRow()) {
                    $class = (object)$row;
                    $matricno = $class->matricno;
                    $groupcode = $class->ClassGroupCode;
                    $matricnos[$matricno] = $groupcode;
                }
            }
            $rs->Close();
        } else {
            $msg = $extdb->ErrorMsg();
            $this->error('Error executing query in UofG enrolment table "'.$msg.'" - '.$sql);
            return false;
        }

        $extdb->Close();
        return $matricnos;
    }

    /**
     * get course information from
     * external database
     * @param string $code course code
     * @return object course details (false if not found)
     */
    protected function external_coursedata( $code ) {
        global $CFG, $DB;

        // Connect to external db.
        if (!$extdb = $this->db_init()) {
            $this->error('Error while communicating with external enrolment database');
            return false;
        }

        // Get connection details.
        $table = $this->get_config('codesenroltable');

        // If table not defined then we can't do anything.
        if (empty($table)) {
            return false;
        }

        // Create the sql.
        $sql = "select * from $table where CourseCat='" . $this->db_addslashes($code) . "'";

        // And run the query.
        $data = false;
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                $row = $rs->FetchRow();
                $data = (object)$row;
            }
            $rs->Close();
        } else {
            $msg = $extdb->ErrorMsg();
            return false;
        }

        if (!$data && (strpos($code, '*') !== false)) {
            $enrolments = $this->external_enrolments(array($code));
            if ($enrolments) {
                $data = new stdClass();
                $data->Crse_cd_Subject = '-';
                $data->Crse_cd_nbr = 0;
                $data->Crse_name = '-';
                $data->ou_name = '-';
                $data->ou_cd = 0;
            }
        }

        $extdb->Close();
        return $data;
    }

    /**
     * get course information from
     * external database by user
     * @param string $user user object
     * @return array of objects course details (false if not found)
     */
    protected function external_userdata( $user ) {
        global $CFG, $DB;

        // Connect to external db.
        if (!$extdb = $this->db_init()) {
            $this->error('Error while communicating with external enrolment database');
            return false;
        }

        // Get connection details.
        $table = $this->get_config('remoteenroltable');

        // GUIDs can't be trusted in the external database, So...
        // match on $user->idnumber against (external) matric_no.

        // Create the sql. In the event idnumber (matric number)
        // not specified, just need to go with username (GUID).
        $sql = "select * from $table where ";
        if (empty($user->idnumber)) {
            $sql .= " UserName = '" . $this->db_addslashes($user->username) . "'";
        } else {
            $sql .= " matric_no = '" . $this->db_addslashes($user->idnumber) . "'";
        }

        // And run the query.
        $data = array();
        if ($rs = $extdb->Execute($sql)) {
            while (!$rs->EOF) {
                $row = $rs->FetchRow();
                $data[] = (object)$row;
            }
            $rs->Close();
        }

        $extdb->Close();
        return $data;
    }

    /**
     * utility function to get user's list of (external) courses
     * in form suitable for report
     * @param string $guid user's GUID
     * @return array list of courses
     */
    public function get_user_courses( $guid ) {

        // If it looks like a student guid then make the matric no
        // which is more reliable.
        $guid = strtolower( $guid );
        if (preg_match('/^\d+[a-z]$/', $guid)) {
            $matric = substr( $guid, 0, -1 );
        } else {
            $matric = '';
        }

        // Make a fake user object.
        $user = new stdClass();
        $user->username = $guid;
        $user->idnumber = $matric;

        // Get the courses.
        if (!$courses = $this->external_userdata( $user )) {
            return false;
        }

        // Add the courses information.
        foreach ($courses as $course) {
            $code = $course->courses;
            if ($coursedata = $this->external_coursedata( $code )) {
                $course->name = fix_utf8($coursedata->Crse_name);
                $course->ou = $coursedata->ou_name;
            } else {
                $course->name = '-';
                $course->ou = '-';
            }
        }

        return $courses;
    }

    /**
     * Creates a bare-bones user record
     * Copied (and modified) from moodlelib.php
     *
     * @param string $username New user's username to add to record
     * @param string $matricid New user's matriculation number
     * @return stdClass A complete user object
     */
    public function create_user_record($username, $matricid) {
        global $CFG, $DB;

        // Just in case check text case.
        $username = trim(core_text::strtolower($username));

        // We will be using 'guid' ldap plugin only.
        $authplugin = get_auth_plugin('guid');

        // Build up new user object.
        $newuser = new stdClass();

        // Get user info from guid auth plugin.
        if ($newinfo = $authplugin->get_userinfo($username, $matricid)) {
            $newinfo = truncate_userinfo($newinfo);
            foreach ($newinfo as $key => $value) {
                $newuser->$key = $value;
            }
        } else {

            // We didn't find the user in LDAP so there's not much else we can do.
            return false;
        }

        // Sanity check
        // Make sure we have pulled basic data from LDAP (something is wrong if we don't)
        // Don't think we need worry about alternate email as these should be legit students.
        if (empty($newuser->firstname) || empty($newuser->lastname) || empty($newuser->email)) {
            return false;
        }

        // From here on in the username will be the uid (if it
        // exists). This is the definitive GUID.
        if (!empty($newuser->uid)) {
            $username = trim(core_text::strtolower($newuser->uid));
            $newuser->username = $username;
        }

        // Check for dodgy email.
        if (!empty($newuser->email)) {
            if (email_is_not_allowed($newuser->email)) {
                unset($newuser->email);
            }
        }

        // This shouldn't happen, but default city is
        // always Glasgow.
        if (!isset($newuser->city)) {
            $newuser->city = 'Glasgow';
        }

        // Fix for MDL-8480
        // user CFG lang for user if $newuser->lang is empty
        // or $user->lang is not an installed language.
        if (empty($newuser->lang) || !get_string_manager()->translation_exists($newuser->lang)) {
            $newuser->lang = $CFG->lang;
        }

        // Basic settings.
        $newuser->auth = 'guid';
        $newuser->username = $username;
        $newuser->confirmed = 1;
        $newuser->lastip = getremoteaddr();
        $newuser->timecreated = time();
        $newuser->timemodified = $newuser->timecreated;
        $newuser->mnethostid = $CFG->mnet_localhost_id;

        $newuser->id = $DB->insert_record('user', $newuser);
        $user = get_complete_user_data('id', $newuser->id);
        update_internal_user_password($user, '');

        return $user;
    }

    /**
     * save codes:
     * maintain a table of codes versus course
     * so we can use in cron and reports
     * NB: we will check it exists here too
     * @param object $course
     * @param array list of 'enhanced' codes
     * @return array list of 'real' codes
     */
    public function save_codes($course, $advcodes) {
        global $CFG, $DB;

        // Track codes that are deemed to exist.
        $realcodes = array();

        // Run through codes finding data.
        foreach ($advcodes as $advcode) {
            $code = $advcode->code;
            $coursedata = $this->external_coursedata( $code );

            // It's possible (and ok) that nothing is found.
            if (!empty($coursedata)) {
                $realcodes[] = $code;

                // Create data record.
                $coursecode = new stdClass;
                $coursecode->code = $code;
                $coursecode->courseid = $course->id;
                $coursecode->subject = $coursedata->Crse_cd_Subject;
                $coursecode->location = $advcode->location;
                $coursecode->instanceid = $advcode->instanceid;

                // COCK UP: these codes can contain letters at the end
                // but we'll just strip them off for now.
                $coursecode->coursenumber = clean_param($coursedata->Crse_cd_nbr, PARAM_INT);
                $coursecode->coursename = fix_utf8($coursedata->Crse_name);
                $coursecode->subjectname = fix_utf8($coursedata->ou_name);
                $coursecode->subjectnumber = $coursedata->ou_cd;

                // Is there already a record for this combination.
                if ($record = $DB->get_record( 'enrol_gudatabase_codes', [
                        'code' => $code,
                        'courseid' => $course->id,
                        'location' => $advcode->location,
                        'instanceid' => $advcode->instanceid,
                    ])) {
                    $coursecode->id = $record->id;
                    $DB->update_record( 'enrol_gudatabase_codes', $coursecode );
                } else {
                    $coursecode->timeadded = time();
                    $DB->insert_record( 'enrol_gudatabase_codes', $coursecode );
                }
            }
        }

        // Now need to check if there are entries for that course
        // that should be deleted.
        $codes = [];
        foreach ($advcodes as $advcode) {
            $codes[] = $advcode->code;
        }
        $entries = $DB->get_records( 'enrol_gudatabase_codes', array( 'courseid' => $course->id ));
        if (!empty($entries)) {
            foreach ($entries as $entry) {
                if (!in_array($entry->code, $codes)) {
                    $DB->delete_records( 'enrol_gudatabase_codes', array( 'id' => $entry->id ));
                }
            }
        }

        return $realcodes;
    }

    /**
     * cache user enrolment
     * @param object $course
     * @param object $user
     * @param string $code
     */
    private function cache_user_enrolment( $course, $user, $code) {
        global $DB;

        // Construct database object.
        $courseuser = new stdClass;
        $courseuser->userid = $user->id;
        $courseuser->courseid = $course->id;
        $courseuser->code = $code;
        $courseuser->timeupdated = time();

        // Insert or update?
        if ($record = $DB->get_record('enrol_gudatabase_users', array('userid' => $user->id, 'courseid' => $course->id))) {
            $courseuser->id = $record->id;
            $DB->update_record( 'enrol_gudatabase_users', $courseuser );
        } else {
            $DB->insert_record( 'enrol_gudatabase_users', $courseuser );
        }
    }

    /**
     * Check if user is enrolled (already)
     * @param object $instance
     * @param int $userid
     * @return boolean
     */
    private function is_user_enrolled($instance, $userid) {
        global $DB;

        if ($ue = $DB->get_record('user_enrolments', array('enrolid' => $instance->id, 'userid' => $userid))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * work out end time from enrolperiod and enrolenddate
     * - if neither are defined return 0
     * - if one is defined return that one
     * - if both are defined return earliest
     */
    private function end_date($instance) {

        // Enrolperiod time end.
        if ($instance->enrolperiod) {
            $periodtimeend = time() + $instance->enrolperiod;
        } else {
            $periodtimeend = 0;
        }

        // Enrolenddate time end.
        if ($instance->enrolenddate) {

            // If in the past - forget it.
            if ($instance->enrolenddate < time()) {
                $enddatetimeend = 0;
            } else {
                $enddatetimeend = $instance->enrolenddate;
            }
        } else {
            $enddatetimeend = 0;
        }

        // Which to return?
        if ($enddatetimeend && $periodtimeend) {
            if ($periodtimeend < $enddatetimeend) {
                return $periodtimeend;
            } else {
                return $enddatetimeend;
            }
        } else {
            if ($periodtimeend) {
                return $periodtimeend;
            } else {
                return $enddatetimeend;
            }
        }
    }

    /**
     * Add the codes to a list also containining info
     * about how they were defined. Saved in a table
     * and used for reporting.
     * @param array $advcodes enhanced codes
     * @param string/array $codes of codes
     * @param string $location (shortname, idnumber, plugin)
     * @param int $instanceid (of plugin, if appropriate)
     */
    protected function log_codes(&$advcodes, $codes, $location, $instanceid = 0) {
        if (!$codes) {
            return;
        }
        if (!is_array($codes)) {
            $codes = [$codes];
        }
        foreach ($codes as $code) {
            $advcode = new stdClass;
            $advcode->code = $code;
            $advcode->location = $location;
            $advcode->instanceid = $instanceid;

            // Mad key is used to make sure we only include each unique code once.
            $advcodes[$code . $location . $instanceid] = $advcode;
        }
    }

    /**
     * Get the list of (legacy) codes for the given course
     * @param object $course course object
     * @return array list of codes
     */
    public function get_codes($course, $instance) {

        // Variables
        $advcodes = [];

        // If customint3 is false then no settings codes.
        if (!empty($instance->customint3)) {
            $shortname = $course->shortname;
            $idnumber = $course->idnumber;
            $codes = $this->split_code($idnumber);
            $this->log_codes($advcodes, $codes, 'idnumber');
            $shortnamecode = clean_param($shortname, PARAM_RAW);
            $this->log_codes($advcodes, $shortnamecode, 'shortname');
            $codes[] = $shortnamecode;
        } else {
            $codes = array();
        }

        // Add codes from customtext1.
        $morecodes = isset($instance->customtext1) && (!$instance->status)? $instance->customtext1 : '';
        if ($morecodes) {
            $morecodes = str_replace("\n\r", "\n", $morecodes);
            $mcodes = explode("\n", $morecodes);
            foreach ($mcodes as $index => $mcode) {
                $mcodes[$index] = clean_param( trim($mcode), PARAM_TEXT );
            }
            $this->log_codes($advcodes, $mcodes, 'plugin', $instance->id);
            $codes = array_merge($codes, $mcodes);
        }
        $verifiedcodes = $this->save_codes($course, $advcodes);
        return $verifiedcodes;
    }

    /**
     * Get enrollments for given course
     * and add users
     * @parm object $course
     * @param object $instance of enrol plugin
     * @return boolean success
     */
    public function enrol_course_users($course, $instance) {
        global $CFG, $DB;

        // Is the plugin enabled for this instance?
        if ($instance->status != ENROL_INSTANCE_ENABLED) {
            return true;
        }

        // Is enrollment allowed?
        if (!$this->enrolment_possible($course, $instance)) {
            return true;
        }

        $context = context_course::instance($course->id);
        $config = get_config('enrol_gudatabase');

        // First need to get a list of possible course codes
        // we will aggregate single code from course shortname
        // and (possible) list from idnumber.
        $codes = $this->get_codes($course, $instance);

        // If there are none, we have nothing to do.
        if (!$codes) {
            return false;
        }

        // Find the default role .
        $defaultrole = $this->get_config('defaultrole');

        // Get the external data for these codes.
        $enrolments = $this->external_enrolments( $codes );
        if ($enrolments === false) {
            return false;
        }

        // Keep list of enrolled users.
        $enrolledusers = array();

        // Iterate over the enrolments and deal.
        foreach ($enrolments as $enrolment) {
            $username = $enrolment->UserName;
            $matricno = $enrolment->matric_no;

            // Can we find this user?
            // Check against idnumber <=> matric_no if possible
            // NOTE: the username in enrol database should be correct but some
            // are not. The matricno<=>idnumber is definitive however.
            if (!$user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {

                // If we get here, couldn't find with username, so
                // let's just have another go with idnumber.
                if (!$user = $DB->get_record('user', array('idnumber' => $matricno))) {

                    // Try to create the new user.
                    // If it fails then there's not much else we can do with this user.
                    if (!$user = $this->create_user_record($username, $matricno)) {
                        continue;
                    }
                }
            }

            // Enrolment period.
            $timestart = time();
            $timeend = $this->end_date($instance);

            // Role to use (existing instances will not have a 'roleid').
            if (empty($instance->roleid)) {
                $instance->roleid = $defaultrole;
                $DB->update_record('enrol', $instance);
            }

            // Enrol user into course (if not already).
            if (!$this->is_user_enrolled($instance, $user->id)) {
                $this->enrol_user( $instance, $user->id, $instance->roleid, $timestart, $timeend, ENROL_USER_ACTIVE );
            }
            $enrolledusers[$user->id] = $user->id;

            // Cache enrolment.
            $this->cache_user_enrolment( $course, $user, $enrolment->courses );
        }

        // If required unenrol remaining users.
        // Only works if enddate is set
        if (!empty($instance->customint4) && $this->get_config('allowunenrol') && !empty($course->enddate)) {

            // Guard time - don't unenrol users enrolled longer than this
            $unenrolguard = $config->unenrolguard;

            // Get list of users enrolled in this instance.
            $enrolments = $DB->get_records('user_enrolments', array('enrolid' => $instance->id));

            // Check they should still be here.
            foreach ($enrolments as $enrolment) {
                if (array_key_exists($enrolment->userid, $enrolledusers)) {
                    continue;
                } else {

                    // Check guard time if enabled (0 = disabled)
                    $enrolduration = time() - $enrolment->timestart;
                    if ($unenrolguard && ($enrolduration > $unenrolguard)) {
                        continue;
                    }

                    // DISABLE THIS FEATURE FOR NOW
                    $this->unenrol_user($instance, $enrolment->userid);
                }
            }
        }

        return true;
    }

    /**
     * Add new instance of gudatabaseenrol plugin when there isn't one already
     * using appropriate defaults.
     * @param object $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_first_instance($course, array $fields = null) {

        $fields['roleid'] = $this->get_config('defaultrole');
        $fields['customint1'] = 0; // Expiry role.
        $fields['customint2'] = 0; // course code groups
        $fields['customint3'] = 1; // honour settings codes
        $fields['customint4'] = 0; // allow unenrol
        $fields['customint5'] = 0; // allow remove from groups
        $fields['customint6'] = 0; // allow instance if course hidden
        $fields['customtext1'] = ''; // course codes
        $fields['customtext2'] = ''; // serialised group enrolments.
        return $this->add_instance($course, $fields);
    }

    /**
     * check if course has at least one instance of this plugin
     * add if not
     * @param object $course
     * @return int instanceid
     */
    public function check_instance($course) {

        // Get all instances in this course.
        $instances = enrol_get_instances($course->id, true);

        // Search for this one.
        $found = false;
        foreach ($instances as $instance) {
            if ($instance->enrol == $this->get_name()) {
                $found = true;
                $instanceid = $instance->id;
            }
        }

        // If we didn't find it then add it.
        if (!$found) {
            $instanceid = $this->add_first_instance($course);
        }

        return $instanceid;
    }

    /**
     * Enrolment guard - is enrolment allowed?
     * @param object $course
     * @return boolean
     */
    public function enrolment_guard($course) {
        $enrolguard = $this->get_config('enrolguard');
        if (!$enrolguard) {
            return true;
        }

        // As long as we're before course start date + guard time it's all good
        return time() < ($course->startdate + $enrolguard);
    }

    /**
     * Check if automatic enrolment possible.
     * Do not do anything if course outside of date range
     * or not visible
     * @param object $course
     * @param object $instance (if we know it)
     * @return boolean
     */
    public function enrolment_possible($course, $instance = null) {

        // If option to enforce end date then check there is one
        if ($this->get_config('enforceenddate') && !$course->enddate) {
            return false;
        }

        // Ignore hidden courses, unless customint6 = 1, in which case skip this check
        if (empty($instance->customint6) && !$course->visible) {
            return false;
        }

        // Ignore courses after end date
        // (enddate == 0 means disabled)
        if ($course->enddate && (time() > $course->enddate)) {
            return false;
        }

        // Ignore courses before start
        if (time() < $course->startdate) {
            return false;
        }

        // Ignore courses where enrolguard applies
        if (!$this->enrolment_guard($course)) {
            return false;
        }

        // In which case...
        return true;
    }

    /**
     * Called after updating/inserting course.
     * Should set off adhoc task for course update
     *
     * @param bool $inserted true if course just inserted
     * @param object $course
     * @param object $data form data
     * @return void
     */
    public function course_updated($inserted, $course, $data) {

        // We want all our new courses to have this plugin.
        if ($inserted) {
            $instanceid = $this->add_first_instance($course);
        }

        // Ad-hoc task to enrol users.
        $synccourse = new \enrol_gudatabase\task\sync_course();
        $data = [
            'newcourse' => $inserted,
            'courseid' => $course->id,
        ];
        $synccourse->set_custom_data($data);
        \core\task\manager::queue_adhoc_task($synccourse);

        return true;
    }

    /**
     * Process a single course
     *
     * @param bool $newcourse true if course just created
     * @param object $course
     * @param object $data form data
     * @return void
     */
    public function process_course($newcourse, $course) {
        global $DB;

        // Make sure we have config.
        $this->load_config();

        // Check if we can proceed.
        if (!$this->enrolment_possible($course)) {
            return true;
        }

        // Get the instances of the enrolment plugin.
        $instances = $DB->get_records('enrol', array('courseid' => $course->id, 'enrol' => 'gudatabase'));

        // Add the users to the course.
        foreach ($instances as $instance) {
            $this->enrol_course_users($course, $instance);
            $this->sync_groups($course, $instance);
        }
        cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($course->id));

        return true;
    }

    /**
     * Create a new group (nicked from group/groups.php)
     * @param string $name
     * @param object $course
     * @return object new group
     */
    private function create_group($name, $course) {
        global $DB;

        $group = new stdClass();
        $group->courseid = $course->id;
        $group->name = $name;
        $group->idnumber = '';
        $group->description = '';
        $group->descriptionformat = 1;
        $group->enrolmentkey = '';
        $group->picture = 0;
        $group->hidepicture = 0;
        $group->timecreated = time();
        $group->timemodified = time();
        $groupid = $DB->insert_record('groups', $group);
        $group->id = $groupid;

        // Invalidate the grouping cache for the course.
        cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($course->id));

        // Trigger group event.
        $context = context_course::instance($course->id);
        $params = array(
            'context' => $context,
            'objectid' => $groupid
        );
        $event = \core\event\group_created::create($params);
        $event->add_record_snapshot('groups', $group);
        $event->trigger();

        return $group;
    }

    /**
     * Convert array of matricno/groupcode data returned
     * from database into a groupcode->array(matricnos) format
     */
    private function convert_matricnos($matricnos) {
        $classgroups = array();
        foreach ($matricnos as $matricno => $classgroupcode) {
            if (!isset($classgroups[$classgroupcode])) {
                $classgroups[$classgroupcode] = array();
            }
            $classgroups[$classgroupcode][] = $matricno;
        }

        return $classgroups;
    }

    /**
     * Clean up local groups. This is run before we sync the groups.
     * Need to make sure that groups referenced in local groups table
     * actually exist - i.e., they have not been deleted out from underneath us.
     * @param int $courseid
     */
    private function clean_up_groups($courseid) {
        global $DB;

        if ($groups = $DB->get_records('enrol_gudatabase_groups', array('courseid' => $courseid))) {
            foreach ($groups as $group) {
                if (!$DB->get_record('groups', array('id' => $group->groupid))) {
                    $DB->delete_records('enrol_gudatabase_groups', array('id' => $group->id));
                }
            }
        }
    }

    /**
     * Get group id given default name and course id
     * Could have been changed, so we keep them in our own table
     * (It's possible a group isn't in this table - yet - as it may have been created
     * before this feature was added)
     * @param string $originalname - the name we would have called it when created
     * @param int $courseid
     * @param int $instanceid (of plugin)
     * @return int groupid (from groups table) or false if group does not exist
     */
    private function get_local_groupid($originalname, $courseid, $instanceid) {
        global $DB;

        // Is the group in the local table?
        if ($enrolgroup = $DB->get_record('enrol_gudatabase_groups',
            array('originalname' => $originalname, 'courseid' => $courseid))) {
            return $enrolgroup->groupid;
        } else {

            // The group name might exist in the course even if not in our table!
            if ($groupid = groups_get_group_by_name($courseid, $originalname)) {

                // In which case save it.
                $enrolgroup = new stdClass;
                $enrolgroup->originalname = $originalname;
                $enrolgroup->courseid = $courseid;
                $enrolgroup->groupid = $groupid;
                $enrolgroup->instanceid = $instanceid;
                $DB->insert_record('enrol_gudatabase_groups', $enrolgroup);
                return $groupid;
            } else {

                // It's not in the course (by name) nor in our table so the group doesn't exist.
                return false;
            }
        }
    }

    /**
     * sync the auto-groups for a given course
     * @param object $course
     * @param object $instance
     */
    public function sync_groups($course, $instance) {
        global $DB;

        $config = get_config('enrol_gudatabase');

        // Shall we?
        if ($instance->status != ENROL_INSTANCE_ENABLED) {
            return false;
        }

        // Check if we can proceed.
        if (!$this->enrolment_possible($course, $instance)) {
            return false;
        }

        // Make sure local group table is clean.
        $this->clean_up_groups($course->id);

        // Get group settings from instance.
        // Nothing to do if this doesn't work.
        $selectedgroups = unserialize($instance->customtext2);

        // Synchronise course groups.
        if ($instance->customint2) {
            $codes = $this->get_codes($course, $instance);
            if ($codes) {
                foreach ($codes as $code) {

                    // See if group exists, if not create it.
                    $groupname = $code;
                    if (!$groupid = $this->get_local_groupid($groupname, $course->id, $instance->id)) {
                        $group = $this->create_group($groupname, $course);

                        // When creating a group also add to local group list.
                        $this->get_local_groupid($groupname, $course->id, $instance->id);
                    } else {
                        $group = groups_get_group($groupid);
                    }

                    // Get enrolments.
                    $enrolments = $this->external_enrolments(array($code));
                    foreach ($enrolments as $enrolment) {
                        if ($user = $DB->get_record('user', array('idnumber' => $enrolment->matric_no))) {
                            groups_add_member($group, $user);
                        }
                    }
                }
            }
        }

        // Run through selected groups and classes.
        if ($selectedgroups) {
            foreach ($selectedgroups as $code => $selectedgroup) {
                foreach ($selectedgroup as $class => $enabled) {
                    if ($enabled) {
                        $groupbasename = "{$code} {$class}";
                        $matricnos = $this->external_class_users($code, $class);
                        $classgroups = $this->convert_matricnos($matricnos);

                        foreach ($classgroups as $classgroupcode => $memberids) {
                            $groupname = "$groupbasename $classgroupcode";

                            // See if group exists, if not create it.
                            if (!$groupid = $this->get_local_groupid($groupname, $course->id, $instance->id)) {
                                $group = $this->create_group($groupname, $course);

                                // When creating a group also add to local group list.
                                $this->get_local_groupid($groupname, $course->id, $instance->id);
                            } else {
                                $group = groups_get_group($groupid);
                            }

                            // Find all the users for this group combination.
                            $enrolledusers = array();
                            foreach ($memberids as $memberid) {
                                if ($user = $DB->get_record('user', array('idnumber' => $memberid))) {

                                    // Add to the group.
                                    groups_add_member($group, $user);
                                    $enrolledusers[$user->id] = $user->id;
                                }
                            }

                            // Remove group members no longer in classgroup.
                            // There MUST be an end date
                            $enrolguard = $config->enrolguard;
                            if (!empty($instance->customint5) && $this->get_config('allowunenrol') && !empty($course->enddate)) {
                                if ($members = $DB->get_records('groups_members', array('groupid' => $groupid))) {
                                    foreach ($members as $member) {

                                        // Check guard time if enabled (0 = disabled)
                                        if ($enrolment = $DB->get_record('user_enrolments', ['enrolid' => $instance->id, 'userid' => $member->userid])) {
                                            $enrolduration = time() - $enrolment->timestart;
                                            if ($unenrolguard && ($enrolduration > $unenrolguard)) {
                                                continue;
                                            }
                                        }
                                        if (!in_array($member->userid, $enrolledusers)) {
                                            groups_remove_member($groupid, $member->userid);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->group_cleanup($course, $instance, $selectedgroups);
    }

    /**
     * Group cleanup.
     * If group removal is enabled, check for class groups that have been removed or disabled 
     * and remove users as required
     * @param object $course
     * @param object $instance
     * @param array $selectedgroups
     */
    public function group_cleanup($course, $instance, $selectedgroups) {
        global $DB;

        // Make sure it's an array
        if (!$selectedgroups) {
            $selectedgroups = [];
        }

        // Must be enabled in this plugin, sitewide AND the course must have an end date
        if (empty($instance->customint5) || !$this->get_config('allowunenrol') || empty($course->enddate)) {
            return;
        }

        // Get the course codes used in the groups table
        $sql = "select distinct substring_index(originalname, ' ', 1) from {enrol_gudatabase_groups} where courseid=:courseid";
        if ($coursecodes = $DB->get_records_sql($sql, ['courseid' => $course->id])) {
            foreach ($coursecodes as $code => $junk) {
                
                // Is this course code valid?
                if (array_key_exists($code, $selectedgroups)) {
                    continue;
                }

                // Code is not in use so find associated groups.
                $sql = "select distinct groupid from {enrol_gudatabase_groups} where substring_index(originalname, ' ', 1) = :code";
                if ($groupids = $DB->get_records_sql($sql, ['code' => $code])) {
                    foreach ($groupids as $groupid => $morejunk) {
                        groups_delete_group($groupid);
                        $DB->delete_records('enrol_gudatabase_groups', ['groupid' => $groupid]);
                    }
                } 
            }
        }

        // Find any groups in the database that are no longer selected
        if ($savedgroups = $DB->get_records('enrol_gudatabase_groups', ['courseid' => $course->id])) {
            foreach ($savedgroups as $savedgroup) {
            
                // if it's just a coursecode then it's a classgroup
                // don't touch those.
                if (strpos($savedgroup->originalname, ' ') === false) {
                    if (!$instance->customint2) {
                        groups_delete_group($savedgroup->groupid);
                        $DB->delete_records('enrol_gudatabase_groups', ['id' => $savedgroup->id]);
                    }
                    continue;
                }

                // if this group exists then do nothing
                $found = false;
                foreach ($selectedgroups as $code => $classes) {

                    foreach ($classes as $class => $selected) {
                        $groupbasename = "{$code} {$class}";
                        if ($selected && (strpos($savedgroup->originalname, $groupbasename) !== false)) {
                            $found = true;
                            break;
                        }
                    }
                }
                if (!$found) {
                    groups_delete_group($savedgroup->groupid);
                    $DB->delete_records('enrol_gudatabase_groups', ['id' => $savedgroup->id]);
                }
            }
        }
    }

    /**
     * synchronise enrollments when user logs in
     *
     * @param object $user user record
     * @return void
     */
    public function sync_user_enrolments($user) {
        $this->process_user_enrolments($user, false);
    }

    /**
     * Process user enrolments
     * Option to print progress for GUID report
     *
     * @param object $user user record
     * @param boolean $print
     * @return void
     */
    public function process_user_enrolments($user, $print = false) {
        global $CFG, $DB;

        // Trace.
        if ($print) {
            $trace = new text_progress_trace();
            $this->trace = $trace;
            $trace->output('Processing enrolments for user ' . fullname($user));
        }

        // This is just a bodge to kill this for admin users.
        $admins = explode( ',', $CFG->siteadmins );
        if (in_array($user->id, $admins)) {
            return true;
        }

        // Get the list of courses for current user.
        $enrolments = $this->external_userdata( $user );

        // If there aren't any then there's nothing to see here.
        if (empty($enrolments)) {
            return true;
        }

        // There could be duplicate courses going this way, so we'll
        // build an array to filter them out.
        $uniquecourses = array();

        // Go through list of codes and find the courses.
        foreach ($enrolments as $enrolment) {

            // We need to find the courses in our own table of courses
            // to allow for multiple codes.
            $codes = $DB->get_records('enrol_gudatabase_codes', array('code' => $enrolment->courses));
            if (!empty($codes)) {
                foreach ($codes as $code) {
                    $uniquecourses[ $code->courseid ] = $code;
                    if ($print) {
                        $trace->output('Found local course for code ' . $code->code . ' (course ID ' . $code->courseid . ')');
                    }
                }
            }
        }

        // Find the default role .
        $defaultrole = $this->get_config('defaultrole');

        // Go through the list of course codes and enrol student.
        if (!empty($uniquecourses)) {
            foreach ($uniquecourses as $courseid => $code) {

                if ($print) {
                    $trace->output('Processing code ' . $code->code);
                }

                // Find last updated time for this user/course. If it was last updated within 24 hours
                // then we won't do it again.
                // Skip if 'admin' run (we're always doing it)
                if (false) {
                    $dayago = time() - (24 * 60 * 60);
                    if ($gudusers = $DB->get_record('enrol_gudatabase_users', array('userid' => $user->id, 'courseid' => $courseid))) {
                        if ($gudusers->timeupdated > $dayago) {
                            continue;
                        }
                    }
                }

                // Get course object.
                if (!$course = $DB->get_record('course', array('id' => $courseid))) {
                    continue;
                }

                // Trace if debug
                if ($print) {
                    $courselink = new moodle_url('/course/view.php', ['id' => $courseid]);
                    $trace->output('    Sync user course - <a href="' . $courselink . '">' . $course->fullname . '</a>');
                }

                // If course is not visible then do not enrol.
                //if (!$course->visible) {
                //    continue;
                //}

		        // If course is outside date range then do not enrol
                //if (($course->startdate > time()) || ($course->enddate < time())) {
                //    continue;
                //}

                // Make sure it has this enrolment plugin.
                $instanceid = $this->check_instance( $course );

                // Get the instances of the enrolment plugin.
                $instances = $DB->get_records('enrol', array('courseid' => $courseid, 'enrol' => 'gudatabase'));
                foreach ($instances as $instance) {
                    if ($instance->status != ENROL_INSTANCE_ENABLED) {
                        continue;
                    }

                    // check if enrolment is possible 
                    if (!$this->enrolment_possible($course, $instance)) {
                        continue;
                    }

                    // Now need to confirm that this instance is the one that defined this code.
                    $instcodes = $this->get_codes($course, $instance);
                    if (empty($instcodes) || !in_array($code->code, $instcodes)) {
                        continue;
                    }

                    // Enrolment period.
                    $timestart = time();
                    $timeend = $this->end_date($instance);
                    if ($instance->enrolperiod) {
                        $timeend = $timestart + $instance->enrolperiod;
                    } else {
                        $timeend = 0;
                    }

                    // Role to use (existing instances will not have a 'roleid').
                    if (empty($instance->roleid)) {
                        $instance->roleid = $defaultrole;
                        $DB->update_record('enrol', $instance);
                    }

                    // Enrol user into course (if not already).
                    if (!$this->is_user_enrolled($instance, $user->id)) {
                        $this->enrol_user( $instance, $user->id, $instance->roleid, $timestart, $timeend, ENROL_USER_ACTIVE );
                        if ($print) {
                            $trace->output('        Enrolling onto course');
                        }
                    } else if ($print) {
                        $trace->output('        Already enrolled');
                    }

                    // Cache enrolment.
                    $this->cache_user_enrolment( $course, $user, $code->code );

                    // Sync the courses groups.
                    $this->sync_groups($course, $instance);
                }
            }
        }

        return true;
    }

    /**
     * Get list of coursedescriptions for form
     * @param object $course
     * @param object $instance
     * @return array of codeclases + coursedescriptions
     */
    protected function get_coursedescriptions($course, $instance) {
        global $PAGE;

        // Get renderer.
        $output = $PAGE->get_renderer('enrol_gudatabase');

        // Get current codes.
        $codes = $this->get_codes($course, $instance);

        // Loop through to get current classes.
        $codeclasses = array();
        $coursedescriptions = array();
        foreach ($codes as $code) {
            $classes = $this->external_classes($code);
            $codeclasses[$code] = $classes;
            $coursedescriptions[$code] = $output->courseinfo($course->id, $code);
        }

        return array($codeclasses, $coursedescriptions);
    }

    /**
     * Get course object from instance
     * @param object $instance
     * @return object course
     */
    protected function get_course($instance) {
        global $DB;

        return $DB->get_record('course', array('id' => $instance->courseid), '*', MUST_EXIST);
    }

    /**
     * Add elements to the edit instance form.
     *
     * @param stdClass $instance
     * @param MoodleQuickForm $mform
     * @param context $context
     * @return bool
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $PAGE;

        $course = $this->get_course($instance);
        list($codeclasses, $coursedescriptions) = $this->get_coursedescriptions($course, $instance);

        // Get renderer.
        $output = $PAGE->get_renderer('enrol_gudatabase');

        // Unpack groups.
        if (empty($instance->customtext2)) {
            $instance->customtext2 = '';
        }
        if (!$groups = unserialize($instance->customtext2)) {
            $groups = array();
        }

        if ($this->enrolment_possible($course, $instance)) {
            $mform->addElement('html', '<div class="alert alert-info">' . get_string('savewarning', 'enrol_gudatabase') . '</div>');
        } else {
            $mform->addElement('html', '<div class="alert alert-danger">' . get_string('savedisabled', 'enrol_gudatabase') . '</div>');
        }

        if (empty($course->enddate)) {
            $link = new moodle_url('/course/edit.php', ['id' => $course->id]);
            $mform->addElement('html', '<div class="alert alert-warning">' . get_string('noenddatealert', 'enrol_gudatabase') .
                ' - <b><a href="' . $link . '">' . get_string('settings') . '</a></b></div>');
        }

        if (!$this->enrolment_guard($course)) {
            $mform->addElement('html', '<div class="alert alert-danger">' . get_string('enrolguardwarning', 'enrol_gudatabase') . '</div>');
        }

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_gudatabase'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_gudatabase');
        $mform->setDefault('status', $this->get_config('status'));

        $yesno = array(
            0 => get_string('no'),
            1 => get_string('yes'),
        );
        $mform->addElement('select', 'customint3', get_string('settingscodes', 'enrol_gudatabase'), $yesno);
        $mform->addHelpButton('customint3', 'settingscodes', 'enrol_gudatabase');
        $mform->setDefault('customint3', 0);

        $mform->addElement('select', 'customint6', get_string('allowhidden', 'enrol_gudatabase'), $yesno);
        $mform->addHelpButton('customint6', 'allowhidden', 'enrol_gudatabase');
        $mform->setDefault('customint6', 0);

        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $this->get_config('roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('defaultrole', 'role'), $roles);
        $mform->setDefault('roleid', 5);

        $mform->addElement('duration', 'enrolperiod', get_string('defaultperiod', 'enrol_gudatabase'),
            array('optional' => true, 'defaultunit' => 86400));
        $mform->setDefault('enrolperiod', $this->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'defaultperiod', 'enrol_gudatabase');

        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_gudatabase'),
            array('optional' => true));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_gudatabase');

        $roles = array(0 => get_string('unenrol', 'enrol_gudatabase')) + $roles;
        $mform->addElement('select', 'expireroleid', get_string('expirerole', 'enrol_gudatabase'), $roles);
        $mform->setDefault('expireroleid', $this->get_config('expireroleid'));
        $mform->addHelpButton('expireroleid', 'expirerole', 'enrol_gudatabase');

        if (has_capability('enrol/gudatabase:enableunenrol', $context)) {
	    $mform->addElement('html','<div class="alert alert-danger">' . get_string('removewarning', 'enrol_gudatabase') . '</div>');

            $mform->addElement('select', 'customint4', get_string('enableunenrol', 'enrol_gudatabase'), $yesno);
            $mform->setDefault('customint4', 0);
            $mform->addHelpButton('customint4', 'enableunenrol', 'enrol_gudatabase');

            $mform->addElement('select', 'customint5', get_string('enablegroupremove', 'enrol_gudatabase'), $yesno);
            $mform->setDefault('customint5', 0);
            $mform->addHelpButton('customint5', 'enablegroupremove', 'enrol_gudatabase');
        }

        // Automatic enrolment (codes) settings.
        $mform->addElement('header', 'codesettings', get_string('codesettings', 'enrol_gudatabase'));

        $codes = $this->get_codes($course, $instance);
        if (empty($instance->customint3)) {
            $instance->customint3 = 0;
        }

        $mform->addElement('html', $output->print_codes($course->id, $codes, $instance->customint3, $this->enrolment_possible($course, $instance)));

        $mform->addElement('textarea', 'customtext1', get_string('codelist', 'enrol_gudatabase'),
            'rows="15" cols="25" style="height: auto; width:auto;"');
        $mform->addHelpButton('customtext1', 'codelist', 'enrol_gudatabase');
        $mform->setType('customtext1', PARAM_TEXT);

        // Automatic groups settings.
        $mform->addElement('header', 'groupsettings', get_string('groupsettings', 'enrol_gudatabase'));

        if ($coursedescriptions) {
            $mform->addElement('html', '<div class="alert alert-info">' .
                get_string('groupsinstruction', 'enrol_gudatabase') . '</div>');
        } else {
            $mform->addElement('html', '<div class="alert alert-warning">' .
                get_string('nolegacycodes', 'enrol_gudatabase') . '</div>');
        }

        if ($coursedescriptions) {
            $mform->addElement('advcheckbox', 'coursegroups', get_string('coursegroups', 'enrol_gudatabase'), '');
            $mform->setDefault('coursegroups', $instance->customint2);
            $mform->addHelpButton('coursegroups', 'coursegroups', 'enrol_gudatabase');
        }

        foreach ($codeclasses as $code => $classes) {
            $description = $coursedescriptions[$code];
            $mform->addElement('html', "<h3>$code ($description)</h3>");
            foreach ($classes as $class) {
                $classnospace = str_replace(' ', '_', $class);
                $selector = "{$code}_{$classnospace}";
                $mform->addElement('advcheckbox', $selector, $class, '');
                $mform->setDefault($selector, !empty($groups[$code][$class]));
            }
        }

        $mform->closeHeaderBefore('groupsettings');
    }

    /**
     * Update instance of enrol plugin.
     * @param stdClass $instance
     * @param stdClass $data modified instance fields
     * @return boolean
     */
    public function update_instance($instance, $data) {
        global $DB;

        // Needed data.
        $course = $this->get_course($instance);
        list($codeclasses, $coursedescriptions) = $this->get_coursedescriptions($course, $instance);

        // Standard settings.
        $data->customint1 = $data->expireroleid;

        // Codes settings.
        $instance->customtext1 = strtoupper($data->customtext1);

        // Course groups.
        $instance->customint2 = isset($data->coursegroups) ? $data->coursegroups : '';

        // Group settings.
        $groups = array();
        foreach ($codeclasses as $code => $codeclass) {
            $groups[$code] = array();
            foreach ($codeclass as $class) {
                $classnospace = str_replace(' ', '_', $class);
                $selector = "{$code}_{$classnospace}";

                // If code has just been added, expected classes are not on the form.
                if (!isset($data->$selector)) {
                    continue;
                }
                $groups[$code][$class] = $data->$selector == 1;
            }
        }
        $data->customtext2 = serialize($groups);

        // Update enrolments.
        $this->course_updated(false, $course, null);

        return parent::update_instance($instance, $data);
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param object $instance The instance loaded from the DB
     * @param context $context The context of the instance we are editing
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @return void
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = array();

        // Valid data.
        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $this->get_config('roleid'));
        }
        $roles = array_keys($roles);
        $yesno = array(0, 1);

        // Parameters to validate.
        $rules = array(
            'customint3' => $yesno,
            'roleid' => $roles,
            'expireroleid' => [0] + $roles,
            'customint5' => $yesno,
        );

        $errors = $this->validate_param_types($data, $rules);

        return $errors;
    }

    /**
     * cron service to update course enrolments
     */
    public function scheduled() {
        global $CFG;
        global $DB;

        // Trace.
        $trace = new text_progress_trace();
        $this->trace = $trace;

        // Get the start time, we'll limit
        // how long this runs for.
        $starttime = time();

        // Get plugin config.
        $config = get_config( 'enrol_gudatabase' );

        // Are we set up?
        if (empty($config->dbhost)) {
            $trace->output( 'enrol_gudatabase: not configured' );
            return false;
        }

        // Get the last course index we processed.
        if (empty($config->startcourseindex)) {
            $startcourseindex = 0;
        } else {
            $startcourseindex = $config->startcourseindex;
        }
        $trace->output( "enrol_gudatabase: starting at course index $startcourseindex" );

        // Get the basics of all visible courses
        // don't load the whole course records!!
        $courses = $DB->get_records( 'course', array('visible' => 1), '', 'id' );

        // Convert courses to simple array.
        $courses = array_values( $courses );
        $highestindex = count($courses) - 1;
        $trace->output( "enrol_gudatabase: highest course index is $highestindex" );
        $trace->output( "enrol_gudatabase: configured time limit is {$config->timelimit} seconds" );

        // Process from current index to (potentially) the end.
        for ($i = $startcourseindex; $i <= $highestindex; $i++) {
            $course = $DB->get_record('course', array('id' => $courses[$i]->id));

            // Avoid site and front page.
            if ($course->id > 1) {
                $instanceid = $this->check_instance($course);
                $updatestart = microtime(true);
                $trace->output( "enrol_gudatabase: updating enrolments for course '{$course->shortname}'" );
                $this->process_course(false, $course);
                $updateend = microtime(true);
                $updatetime = number_format($updateend - $updatestart, 4);

                // Process expired users.
                $this->process_expirations($trace, $course->id);
                $trace->output( "enrol_gudatabase: --- course {$course->shortname} took $updatetime seconds to update");
            }
            $lastcourseprocessed = $i;

            // If we've used too much time then bail out.
            $elapsedtime = time() - $starttime;
            if ($elapsedtime > $config->timelimit) {
                break;
            }
        }

        // Set new value of index.
        if ($lastcourseprocessed >= $highestindex) {
            $nextcoursetoprocess = 0;
        } else {
            $nextcoursetoprocess = $lastcourseprocessed + 1;
        }
        set_config( 'startcourseindex', $nextcoursetoprocess, 'enrol_gudatabase' );
        $trace->output( "enrol_gudatabase: next course index to process is $nextcoursetoprocess" );

        // Create very poor average course process.
        $oldaverage = empty($config->average) ? 0 : $config->average;
        $newaverage = ($oldaverage + $lastcourseprocessed - $startcourseindex) / 2;
        set_config( 'average', $newaverage, 'enrol_gudatabase' );
        $elapsedtime = time() - $starttime;
        $trace->output( 'enrol_gudatabase: completed, processed courses = ' . ($lastcourseprocessed - $startcourseindex) );
        $trace->output( "enrol_gudatabase: actual elapsed time was $elapsedtime seconds" );
    }

    /**
     * Handle error messages appropriately
     * for cli or web based operation
     * @param string $message message to display/log
     */
    private function error($message) {
        if (defined('CLI_SCRIPT') and CLI_SCRIPT) {
            $this->trace->output($message);
        } else {
            print_error('autherror', 'enrol_gudatabase', '', $message);
        }
    }

    /**
     * Automatic enrol sync executed during restore.
     * TODO: This needs to do something or justify why not
     * @param stdClass $course course record
     */
    public function restore_sync_course($course) {
        $this->process_course(false, $course);
    }

    /**
     * Do any enrolments need expiration processing.
     *
     * Plugins that want to call this functionality must implement 'expiredaction' config setting.
     *
     * @param progress_trace $trace
     * @param int $courseid one course, empty mean all
     * @return bool true if any data processed, false if not
     */
    public function process_expirations(progress_trace $trace, $courseid = null) {
        global $DB;

        $name = $this->get_name();
        if (!enrol_is_enabled($name)) {
            $trace->finished();
            return false;
        }

        $processed = false;
        $params = array();
        $coursesql = "";
        if ($courseid) {
            $coursesql = "AND e.courseid = :courseid";
        }

        $instances = array();
        $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :enrol)
                  JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                 WHERE ue.timeend > 0 AND ue.timeend < :now $coursesql";
        $params = array(
            'now' => time(),
            'courselevel' => CONTEXT_COURSE,
            'enrol' => $name,
            'courseid' => $courseid
        );

        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $ue) {
            if (!$processed) {
                $trace->output("enrol_gudatabase: Starting processing of enrol_$name expirations...");
                $processed = true;
            }
            if (empty($instances[$ue->enrolid])) {
                $instances[$ue->enrolid] = $DB->get_record('enrol', array('id' => $ue->enrolid));
            }
            $instance = $instances[$ue->enrolid];

            // Depending on customint1 (target roleid) we will just remove them or
            // change their role.
            if (!$instance->customint1) {

                // Let's just guess what extra roles are supposed to be removed.
                if ($instance->roleid) {
                    role_unassign($instance->roleid, $ue->userid, $ue->contextid);
                }

                // The unenrol cleans up all subcontexts if this is the only course enrolment for this user.
                $this->unenrol_user($instance, $ue->userid);
                $trace->output("enrol_gudatabase: Unenrolling expired user $ue->userid from course $instance->courseid", 1);
            } else {

                // Swap role.
                if ($instance->roleid) {
                    role_assign($instance->customint1, $ue->userid, $ue->contextid);
                    role_unassign($instance->roleid, $ue->userid, $ue->contextid);

                    // Enrolment now has no time limit.
                    $this->update_user_enrol($instance, $ue->userid, null, 0, 0);

                    $trace->output("enrol_gudatabase: Moving role for expired user $ue->userid from course $instance->courseid", 1);
                }
            }
        }
        $rs->close();
        unset($instances);

        if ($processed) {
            $trace->output("enrol_gudatabase: ...finished processing of enrol_$name expirations");
        } else {
            $trace->output("enrol_gudatabase: No expired enrol_$name enrolments detected");
        }
        $trace->finished();

        return $processed;
    }

    /**
     * The gudatabase plugin has a delete bulk operation
     * @param course_enrolment_manager $manager
     * @return array
     */
    public function get_bulk_operations(course_enrolment_manager $manager) {
	    global $CFG, $DB;

	    $course = $manager->get_course();
        //if ($this->enrolment_possible($course)) {
        //    return [];
        //}

        $context = $manager->get_context();
        $bulkoperations = array();
        if (has_capability("enrol/gudatabase:unenrol", $context)) {
            $bulkoperations['deleteselectedusers'] = new \enrol_gudatabase\deleteselectedusers_operation($manager, $this);
        }
        return $bulkoperations;
    }

    /**
     * Test plugin settings, print info to output.
     */
    public function test_settings() {
        global $CFG, $OUTPUT;

        // NOTE: this is not localised intentionally, admins are supposed to understand English at least a bit...

        raise_memory_limit(MEMORY_HUGE);

        $this->load_config();

        $enroltable = $this->get_config('remoteenroltable');
        $codestable = $this->get_config('codesenroltable');
        $classlisttable = $this->get_config('classlisttable');

        if (empty($enroltable)) {
            echo $OUTPUT->notification('External enrolment table not specified.', 'notifyproblem');
            return;
        }

        if (empty($codestable)) {
            echo $OUTPUT->notification('Remote codes table not specified.', 'notifyproblem');
        }

        if (empty($classlisttable)) {
            echo $OUTPUT->notification('Class list table not specified.', 'notifyproblem');
        }

        $olddebug = $CFG->debug;
        $olddisplay = ini_get('display_errors');
        ini_set('display_errors', '1');
        $CFG->debug = DEBUG_DEVELOPER;
        $olddebugdb = $this->config->debugdb;
        $this->config->debugdb = 1;
        error_reporting($CFG->debug);

        $adodb = $this->db_init();

        if (!$adodb or !$adodb->IsConnected()) {
            $this->config->debugdb = $olddebugdb;
            $CFG->debug = $olddebug;
            ini_set('display_errors', $olddisplay);
            error_reporting($CFG->debug);
            ob_end_flush();

            echo $OUTPUT->notification('Cannot connect the database.', 'notifyproblem');
            return;
        }

        if (!empty($enroltable)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $enroltable");
            if (!$rs) {
                echo $OUTPUT->notification('Can not read external enrol table.', 'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification('External enrol table is empty.', 'notifyproblem');
                $rs->Close();

            } else {
                $fieldsobj = $rs->FetchObj();
                $columns = array_keys((array)$fieldsobj);

                echo $OUTPUT->notification('External enrolment table contains following columns:<br />'.
                    implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        if (!empty($codestable)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $codestable");
            if (!$rs) {
                echo $OUTPUT->notification('Can not read remote codes table.', 'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification('Remote codes table is empty.', 'notifyproblem');
                $rs->Close();

            } else {
                $fieldsobj = $rs->FetchObj();
                $columns = array_keys((array)$fieldsobj);

                echo $OUTPUT->notification('Remote codes table contains following columns:<br />'.
                    implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        if (!empty($classlisttable)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $classlisttable");
            if (!$rs) {
                echo $OUTPUT->notification('Can not read class list table.', 'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification('Class list table is empty.', 'notifyproblem');
                $rs->Close();

            } else {
                $fieldsobj = $rs->FetchObj();
                $columns = array_keys((array)$fieldsobj);

                echo $OUTPUT->notification('Class list table contains following columns:<br />'.
                    implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        $adodb->Close();

        $this->config->debugdb = $olddebugdb;
        $CFG->debug = $olddebug;
        ini_set('display_errors', $olddisplay);
        error_reporting($CFG->debug);
        ob_end_flush();
    }
}
