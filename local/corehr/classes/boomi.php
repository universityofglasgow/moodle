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
 * API for CoreHR access via Boomi
 *
 * @package    local_corehr
 * @copyright  2025 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_corehr;

/**
 * Class to handle Boomi communication
 */
class boomi {

    /**
     * GetPersonByGUID url
     */
    private $getpersonurl = '';

    /**
     * TrainingRecord url
     */
    private $trainingrecordurl = '';

    /**
     * GetPersonByGUID username
     */
    private $boomiuser = '';

    /**
     * GetPersonByGUID password
     */
    private $boomipassword = '';

    /**
     * Last log insert id (for unit testing)
     */
    private $lastlogid = 0;

    /**
     * Constructor
     */
    public function __construct() {

        $this->getpersonurl = get_config('local_corehr', 'getpersonurl');
        $this->trainingrecordurl = get_config('local_corehr', 'trainingrecordurl');
        $this->boomiuser = get_config('local_corehr', 'boomiuser');
        $this->boomipassword = get_config('local_corehr', 'boomipassword');
    }

    /**
     * Work out if Boomi getPersonByGUID is configured
     */
    public function is_getperson_configured() {

        return !(empty($this->getpersonurl) || empty($this->boomiuser) || empty($this->boomipassword));
    }

    /**
     * Work out if Boomi TrainingRecord is configured
     */
    public function is_trainingrecord_configured() {

        return !(empty($this->trainingrecordurl) || empty($this->boomiuser) || empty($this->boomipassword));
    }

    /**
     * Write to log database given output from REST call.
     * @param string $url
     * @param string|null $response
     * @param string $curlerror
     */
    protected function logresponse(string $url, string|null $response, string $curlerror) {
        global $DB;

        if ($response && ($json = json_decode($response))) {
            $payload = empty($json->payload) ? '' : $json->payload;
            $status = empty($json->status) ? '' : $json->status;
            $errorcode = isset($status->errorCode) ? $status->errorCode: 0;
            $errormessage = isset($status->errorMessage) ? $status->errorMessage: '';
            $executionid = empty($status->executionid) ? '' : $status->executionId;
            $payload = json_encode($payload);
        } else {
            $errorcode = 0;
            $errormessage = $curlerror;
            $executionid = '';
            $payload = $response;
        }

        $log = new \stdClass();
        $log->url = $url;
        $log->errorcode = $errorcode;
        $log->errormessage = $errormessage;
        $log->executionid = $executionid;
        $log->payload = $payload;
        $log->timestamp = time();

        $this->lastlogid = $DB->insert_record('local_corehr_boomi_log', $log);
    }

    /**
     * Get lastlogid
     * @return integer
     */
    public function get_lastlogid() {
        return $this->lastlogid;
    }

    /**
     * Extract person details for GUID
     * @param string $guid
     */
    public function getpersonbyguid(string $guid) {

        $rest = new \local_corehr\restclient\restclient([
            'base_url' => $this->getpersonurl,
            'username' => $this->boomiuser,
            'password' => $this->boomipassword,
            'curl_options' => [
                \CURLOPT_HTTPAUTH => \CURLAUTH_BASIC,
                \CURLOPT_CONNECTTIMEOUT => 5,
            ]
        ]);

        $result = $rest->get('', ['personGuid' => $guid]);
        $response = $result->response;

        // Log to database.
        $this->logresponse($result->url, $response, $result->error);

        // Get the JSON payload
        if ($json = json_decode($response)) {
            $payload = $json->payload;

            return $payload;
        } else {

            return null;
        }
    }

    /**
     * Get CoreHR code for given Moodle course
     * Return false if there isn't one or it isn't enabled. 
     * @param int $courseid
     * @return string|bool
     */
    public function get_course_code(int $courseid) {
        global $DB;

        // Is this enabled for this course.
        if (!$corehr = $DB->get_record('local_corehr', ['courseid' => $courseid])) {
            return false;
        }

        // Is the plugin enabled
        if (!$corehr->enable) {
            return false;
        }

        return $corehr->coursecode;
    }

    /**
     * Get staff number/id number for userid
     * @param int $userid
     * @return int|bool
     */
    public function get_staff_number(int $userid) {
        global $DB;

        if (!$user = $DB->get_record('user', ['id' => $userid])) {
            return false;
        }

        if (empty($user->idnumber)) {
            return false;
        }

        if (!is_number($user->idnumber)) {
            return false;
        }

        return $user->idnumber;
    }

    /**
     * Update training record for 'course code' and personnel number
     * NOTE: Staff "number" isn't a number because it can have a letter at the beginning
     * Returns status
     * @param string $coursecode;
     * @param string $staffnumber
     * @param int $startdate
     * @return string
     */
    public function trainingrecord(string $coursecode, string $staffnumber, int $startdate) {

        $rest = new \local_corehr\restclient\restclient([
            'base_url' => $this->trainingrecordurl,
            'username' => $this->boomiuser,
            'password' => $this->boomipassword,
            'curl_options' => [
                \CURLOPT_HTTPAUTH => \CURLAUTH_BASIC,
                \CURLOPT_CONNECTTIMEOUT => 5,
            ]
        ]);

        $payload = (object)[
            'courseCode' => $coursecode,
            'personnelNo' => $staffnumber,
            'startDate' => date('dmY', $startdate),
        ];

        $result = $rest->post('', json_encode($payload));
        $response = $result->response;

        $this->logresponse($result->url, $response, $result->error);

        // Get response status
        if ($json = json_decode($response)) {
            return empty($json->status->errorMessage) ? 'OK' : $json->status->errorMessage;
        } else {
            return '';
        }
    }
}