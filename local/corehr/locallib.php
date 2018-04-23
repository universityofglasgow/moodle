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

    // staffTrainingRecord 
    $staffTrainingRecord = new staffTrainingRecord(
        $user->idnumber,
        $coursecode,
        'CO',
        date('dmY', $completion->timestarted),
        date('dmY', $completion->timecompleted)
    );

    // Call CoreHR API to log completion.
    // We'll skip this if there is no personnel number
    if (empty($user->idnumber)) {
        mtrace("local_corehr: skipping web service for user (id=$userid) with no personnel number");
        $status = "No Personnel Number";
    } else {
        $status = local_corehr_add($staffTrainingRecord);
        mtrace("local_corehr: data sent to web service, status is $status");
    }

    // Record the details
    $corehr = new stdClass;
    $corehr->userid = $userid;
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
