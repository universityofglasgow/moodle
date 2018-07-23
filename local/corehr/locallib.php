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
 * Sychronise completion data for CoreHR
 *
 * @package    local_corehr
 * @copyright  2016 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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

/**
 * Get the retry delay for given retry value
 * @param $count retry count
 * @return int seconds (since last try)
 */
function local_corehr_get_delay($count) {
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
 * A very basic check that the web service details are correct
 * @return string informative message
 */
function local_corehr_test() {
    $config = get_config('local_corehr');
    if (empty($config->wsdl)) {
        return get_string('notconfigured', 'local_corehr');
    }
    $client = new SoapClient($config->wsdl);

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
function local_corehr_add($staffTrainingRecord) {
    $config = get_config('local_corehr');
    if (empty($config->wsdl)) {
        return get_string('notconfigured', 'local_corehr');
    }
    try {
        $client = new SoapClient($config->wsdl, array('trace' => 1));
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
    try {
        $result = $client->add($params);
    } catch (Exception $e) {
        $request = $client->__getLastRequest();
        return $e->getMessage() . "\n\n" . $request;
    }

    // probably worked, so status ok
    return 'OK';
}

/**
 * Log details
 * @param object $user
 * @param object $completion
 * @param int $courseid
 * @param string $coursecode
 * @param string $status
 */
function local_corehr_log($user, $completion, $courseid, $coursecode, $status) {
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
function local_corehr_course_completed($courseid, $userid) {
    global $DB;

    // Is this enabled for this course
    if (!$corehr = $DB->get_record('local_corehr', array('courseid' => $courseid))) {
        mtrace('local_corehr: not configured for courseid = ' . $courseid . ', completing userid = ' . $userid);
        return;
    }

    // Get the course code
    $coursecode = $corehr->coursecode;
    mtrace("local_corehr: Processing completion for user=$userid, course=$courseid, coursecode=$coursecode");

    // Get the user.
    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

    // Attempt to get completion data (we'll go for the newest one)
    $completions = $DB->get_records('course_completions', array(
        'userid' => $userid,
        'course' => $courseid
        ), 'id asc');
    if (!$completions) {
        mtrace("local_corehr: No matching entries in course_comopletions table for user=$userid, course=$courseid");
        return false;
    }
    $completion = array_pop($completions);

    // Check if this user has already completed this
    // We don't make them do it twice (for the same course id)
    // If we really want them to do it again then create a new course. 
    if ($status = $DB->get_record('local_corehr_status', ['userid' => $userid, 'courseid' => $courseid, 'status' => 'OK'])) {
        local_corehr_log($user, $completion, $courseid, $coursecode, "Completed " . $status->id);
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
    $DB->insert_record('local_corehr_status', $status);

    return;
}

/**
 * Send data to CoreHR web service
 * @param object $status from local_corehr_status table
 * @return string data returned from web service
 */
function local_corehr_send($status) {
    global $DB;

    mtrace('Sending to corehr for userid = ' . $status->userid . ', coursecode = ' . $status->coursecode . ', retry = ' . $status->retrycount);

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
        mtrace("local_corehr: skipping web service for user (id=$userid) with no personnel number");
        $message = "No Personnel Number";
    } else {
        $message = local_corehr_add($staffTrainingRecord);
        mtrace("local_corehr: data sent to web service, status is $message");
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

    return $message;
}

/** 
 * Save/delete the 'coursecode' in the local_corehr table
 * A blank course code deletes the matching record
 * @param int $courseid Moodle course id
 * @param string $coursecode CoreHR course identifier (or empty)
 */
function local_corehr_savecoursecode($courseid, $coursecode) {
    global $DB;

    // find existing record
    $corehr = $DB->get_record('local_corehr', array('courseid' => $courseid));

    // if record exists and code is empty, delete it
    if ($corehr && !$coursecode) {
        $DB->delete_records('local_corehr', array('courseid' => $courseid));
        return;
    }

    // update or insert
    if ($corehr) {
        $corehr->coursecode = $coursecode;
        $DB->update_record('local_corehr', $corehr);
    } else {
        $corehr = new stdClass;
        $corehr->courseid = $courseid;
        $corehr->coursecode = $coursecode;
        $DB->insert_record('local_corehr', $corehr);
    }

    return;
}
