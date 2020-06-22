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
 * API for CoreHR access
 *
 * @package    local_corehr
 * @copyright  2016-19 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_corehr;

use SoapClient, StdClass;

define('COREHR_TTL', 86400);

class loginDetails {

    public function __construct($userName, $password) {
        $this->userName = $userName;
        $this->password = $password;
    }
}

class staffTrainingRecord {

    public function __construct($personnelNo, $courseCode, $trainingStatus, $startDate, $endDate) {
        $this->personnelNo = $personnelNo;
        $this->courseCode = $courseCode;
        $this->trainingStatus = $trainingStatus;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
}

class getPersonByGuid {

    public $username;

    public $password;

    public $guid;

    public function __construct($username, $password, $guid) {
        $this->username = $username;
        $this->password = $password;
        $this->guid = $guid;
    }
}

class api {

    /**
     * PHPUNIT_TEST savvy mtrace wrapper
     * @param string $message
     */
    public static function mtrace($message) {
        if (!PHPUNIT_TEST) {
            mtrace($message);
        }
    }

    /**
     * Get the retry delay for given retry value
     * @param $count retry count
     * @return int seconds (since last try)
     */
    public static function get_delay($count) {
        $counts = [
            0 => 0, // immediately
            1 => 0, // immediately
            2 => 300, // 5 mins
            3 => 3600, // 1 hour
            4 => 7200, // 2 hours
            5 => 14400, // 4 hours
            6 => 28800, // 8 hours
            7 => 57600, // 16 hours
            8 => 86400, // 1 day
            9 => 172800, // 2 days
            10 => 345600, // 4 days
            11 => 604800, // 1 week
            12 => 1209600, // 2 weeks
        ];
        if (array_key_exists($count, $counts)) {
            return $counts[$count];
        } else {
            return end($counts);
        }
    }

    /**
     * Get possible error codes for CoreHR API
     * and whether they are permanent or not
     * @return array (true means permanent error)
     */
    public static function getErrors() {
        return [
            'PERSON_NUMBER_DOES_NOT_EXIST' => true,
            'PERSON_NUMBER_EMPTY' => true,
            'PERSON_NUMBER_NOT_VALID' => true,
            'PERSON_IS_STUDENT' => true,
            'COURSE_CODE_EMPTY' => true,
            'START_DATE_EMPTY' => true,
            'START_DATE_INVALID' => true,
            'END_DATE_INVALID' => true,
            'COURSE_CODE_DOES_NOT_EXIST' => true,
            'TRAINING_STATUS_EMPTY' => true,
            'INTERNAL_SQL_ERROR' => false,
            'FAILED_LOGIN' => false,
            'RECORD_ALREADY_EXISTS' => true,
        ];
    }

    /**
     * A very basic check that the web service details are correct
     * @return string informative message
     */
    public static function test() {
        $config = get_config('local_corehr');
        if (empty($config->wsdltraining)) {
            return get_string('notconfigured', 'local_corehr');
        }
        $client = new SoapClient($config->wsdltraining);

        $functions = $client->__getFunctions();
        if (count($functions) > 1) {
            return get_string('testpass', 'local_corehr');
        }
    }

    /**
     * Web service 'add' function
     * @param object $staffTrainingRecord data object for web service
     * @return string status / error log from ws
     */
    private static function add($staffTrainingRecord) {
        $config = get_config('local_corehr');
        if (empty($config->wsdltraining)) {
            return get_string('notconfigured', 'local_corehr');
        }
        try {
            $client = new SoapClient($config->wsdltraining, array('trace' => 1, 'exceptions' => 0));
        } catch (Exception $e) {
            return $e->getMessage();
        }

        // Construct parameters
        $loginDetails = new loginDetails(
            $config->username,
            $config->password
        );
        $params = array(
            'loginDetails' => $loginDetails,
            'staffTrainingRecord' => $staffTrainingRecord,
        );

        // Try add request. Attempt to get some useful information if it fails.
        $result = $client->__soapCall('add', [$params]);
        if (is_a($result, 'SoapFault')) {
            $message = $result->faultcode;
        } else {

            // If it's not a fault, we'll assume it worked
            $message = 'OK';
        }

        return $message;
    }

    /**
     * Web service to get HR data extract
     * @param string $guid
     * @return mixed user data object or false
     */
    public static function extract($guid) {
        $config = get_config('local_corehr');
        if (empty($config->wsdlextract)) {
            return false;
        }
        try {
            $client = new SoapClient($config->wsdlextract, array('trace' => 1, 'exceptions' => true));
        } catch (\Throwable $e) {
            return false;
        }

        // Construct parameters
        $getPersonByGuid = new getPersonByGuid(
            $config->username,
            $config->password,
            $guid
        );
        $params = [
            'getPersonByGuid' => $getPersonByGuid
        ];

        // Try soap call. Attempt to get some useful information if it fails.
        $result = $client->__soapCall('getPersonByGuid', [$getPersonByGuid]);
        if (is_a($result, 'SoapFault')) {

            // This doesn't appear to throw an error, but just in case
            return false;
        } else {

            // 'return' contains the object of user data
            if (!empty($result->return)) {
                return $result->return;
            } else {
                return false;
            }
        }

        return $result->return;
    }

    /**
     * Write extracted corehr data to database
     * @param int $userid
     * @param object $extract
     */
    public static function store_extract($userid, $extract) {
        global $DB;

        if (!$data = $DB->get_record('local_corehr_extract', ['userid' => $userid])) {
            $data = new \stdClass;
            $data->userid = $userid;
        }

        $data->college = $extract->college;
        $data->collegedesc = $extract->collegeDesc;
        $data->costcentre = $extract->costCentre;
        $data->costcentredesc = $extract->costCentreDesc;
        $data->title = $extract->title;
        $data->forename = $extract->forename;
        $data->middlename = $extract->middleName;
        $data->surname = $extract->surname;
        $data->knownas = $extract->knownAs;
        $data->orgunitno = $extract->orgUnitNo;
        $data->orgunitdesc = $extract->orgUnitDesc;
        $data->school = !is_numeric($extract->school) ? 0 : $extract->school;
        $data->schooldesc = $extract->schoolDesc;
        $data->jobtitle = $extract->jobTitle;
        $data->jobtitledesc = $extract->jobTitleDesc;
        $data->timemodified = time();

        if (empty($data->id)) {
            $data->id = $DB->insert_record('local_corehr_extract', $data);
        } else {
            $DB->update_record('local_corehr_extract', $data);
        }

        return $data;
    }

    /**
     * Convenience function, store extract for GUID
     * @param string guid (username)
     * @param object $extract
     */
    public static function store_extract_guid($guid, $extract) {
        global $CFG, $DB;

        $user = $DB->get_record('user', ['username' => $guid, 'mnethostid' => $CFG->mnet_localhost_id]);
        if ($user) {
            self::store_extract($user->id, $extract);
        }
    }

    /**
     * Auto enrol courses
     * Automatically enrol staff user on selected courses 
     * @param string $guid
     */
    public static function auto_enrol($guid) {
        global $CFG, $DB;

        // Horrible bodge:
        // Assume student role id
        $studentroleid = 5;

        if (!$user = $DB->get_record('user', ['username' => $guid, 'mnethostid' => $CFG->mnet_localhost_id])) {
            return false;
        }
        if (!$courses = $DB->get_records('local_corehr', ['enrolallstaff' => 1])) {
            return false;
        }
        foreach ($courses as $corecourse) {
            $context = \context_course::instance($corecourse->courseid);
            if (is_enrolled($context, $user, '', true)) {
                continue;
            }
            enrol_try_internal_enrol($corecourse->courseid, $user->id, $studentroleid);
        }
    }

    /**
     * Get extract from database. Extract if does not exist
     * @param string $guid
     * @return object
     */
    public static function get_extract($guid) {
        global $CFG, $DB;

        if (!$user = $DB->get_record('user', ['username' => $guid, 'mnethostid'=>$CFG->mnet_localhost_id])) {
            return false;
        }
        $userid = $user->id;

        if ($coreextract = $DB->get_record('local_corehr_extract', ['userid' => $userid])) {
            if ($coreextract->timemodified > (time() - COREHR_TTL)) {
                return $coreextract;
            }
        }

        // Adhoc task to pull data
        $extract = new \local_corehr\task\extract();
        $extract->set_custom_data(['guid' => $guid]);
        \core\task\manager::queue_adhoc_task($extract);

        // If we got valid data then return that regardless
        if ($coreextract) {
            return $coreextract;
        } else {
            return false;
        }
    }

    /**
     * Log details
     * @param object $user
     * @param object $completion
     * @param int $courseid
     * @param string $coursecode
     * @param string $status
     */
    private static function log($user, $completion, $courseid, $coursecode, $status) {
        global $DB;

        // Record the details
        $corehr = new stdClass;
        $corehr->userid = $user->id;
        $corehr->courseid = $courseid;
        $corehr->personnelno = $user->idnumber;
        $corehr->coursecode = $coursecode;
        $corehr->trainingstatus = 'CO';
        $corehr->startdate = date('dmY', $completion->timestarted);
        $corehr->enddate = date('dmY', $completion->timecompleted);
        $corehr->wsstatus = $status;
        $DB->insert_record('local_corehr_log', $corehr);
    }

    /**
     * write completion data to CoreHR web service
     * @param int $courseid Course ID of completed course
     * @param int $userid User ID of completing user
     */
    public static function course_completed($courseid, $userid) {
        global $CFG, $DB;

        // Is this enabled for this course
        if (!$corehr = $DB->get_record('local_corehr', array('courseid' => $courseid))) {
            self::mtrace('local_corehr: not configured for courseid = ' . $courseid . ', completing userid = ' . $userid);
            return;
        }

        // Get the course code
        $coursecode = $corehr->coursecode;
        self::mtrace("local_corehr: Processing completion for user=$userid, course=$courseid, coursecode=$coursecode");

        // Get the user.
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        // Attempt to get completion data (we'll go for the newest one)
        $completions = $DB->get_records('course_completions', array(
            'userid' => $userid,
            'course' => $courseid
            ), 'id asc');
        if (!$completions) {
            self::mtrace("local_corehr: No matching entries in course_completions table for user=$userid, course=$courseid");
            return false;
        }
        $completion = array_pop($completions);

        // Check if this user has already completed this
        // We don't make them do it twice (for the same course id)
        // If we really want them to do it again then create a new course.
        if ($status = $DB->get_record('local_corehr_status', ['userid' => $userid, 'courseid' => $courseid, 'status' => 'OK'])) {
            self::log($user, $completion, $courseid, $coursecode, "Completed " . $status->id);
            return true;
        }

        // Write details to status record
        $status = new stdClass;
        $status->userid = $userid;
        $status->courseid = $courseid;
        $status->personnelno = $user->idnumber;
        $status->coursecode = $coursecode;
        $status->completed = time();
        $status->lasttry = time();
        $status->retrycount = 0;
        $status->status = 'pending';
        $status->error = '';
        $DB->insert_record('local_corehr_status', $status);

        return;
    }

    /**
     * Send data to CoreHR web service
     * @param object $status from local_corehr_status table
     * @return string data returned from web service
     */
    public static function send($status) {
        global $DB;

        self::mtrace('Sending to corehr for userid = ' . $status->userid . ', coursecode = ' . $status->coursecode . ', retry = ' . $status->retrycount . ' statusid = ' . $status->id);

        // staffTrainingRecord
        $staffTrainingRecord = new staffTrainingRecord(
            $status->personnelno,
            $status->coursecode,
            'CO',
            date('dmY', $status->completed),
            date('dmY', $status->completed)
        );

        // Call CoreHR API to log completion.
        // We'll skip this if there is no personnel number
        if (empty($status->personnelno)) {
            self::mtrace("local_corehr: skipping web service for userid = {$status->userid} with no personnel number");
            $message = "PERSON_NUMBER_EMPTY";
        } else {
            $message = self::add($staffTrainingRecord);
            self::mtrace("local_corehr: data sent to web service, status is $message");
        }

        // Record the details
        $corehr = new stdClass;
        $corehr->userid = $status->userid;
        $corehr->courseid = $status->courseid;
        $corehr->personnelno = $status->personnelno;
        $corehr->coursecode = $status->coursecode;
        $corehr->trainingstatus = 'CO';
        $corehr->startdate = date('dmY', $status->completed);
        $corehr->enddate = date('dmY', time());
        $corehr->wsstatus = $message;

        self::mtrace('local_corehr: Message returned for userid = ' . $status->userid . ' is ' . $message);

        return $message;
    }

    /**
     * Save/delete the 'coursecode' and 'enrolallstaff' in the local_corehr table
     * A blank course code deletes the matching record
     * @param int $courseid Moodle course id
     * @param int $enrolallstaff 
     * @param string $coursecode CoreHR course identifier (or empty)
     */
    public static function savecoursecode($courseid, $coursecode, $enrolallstaff) {
        global $DB;

        // find existing record
        $corehr = $DB->get_record('local_corehr', array('courseid' => $courseid));

        // if record exists and code is empty, delete it
        if ($corehr && !$coursecode) {
            $DB->delete_records('local_corehr', array('courseid' => $courseid));
            return;
        }

        // Only update or insert if we have a coursecode
        if (!$coursecode) {
            return;
        }

        // update or insert
        if ($corehr) {
            $corehr->coursecode = $coursecode;
            $corehr->enrolallstaff = $enrolallstaff;
            $DB->update_record('local_corehr', $corehr);
        } else {
            $corehr = new stdClass;
            $corehr->courseid = $courseid;
            $corehr->coursecode = $coursecode;
            $corehr->enrolallstaff = $enrolallstaff;
            $DB->insert_record('local_corehr', $corehr);
        }

        return;
    }

}
