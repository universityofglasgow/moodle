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
     * Write to log database given output from REST call.
     * @param string $url
     * @param string $response
     */
    protected function logresponse(string $url, string $response) {
        global $DB;

        if ($json = json_decode($response)) {
            $payload = $json->payload;
            $status = $json->status;
            $errorcode = isset($status->errorCode) ? $status->errorCode: 0;
            $errormessage = isset($status->errorMessage) ? $status->errorMessage: '';
            $executionid = $status->executionId;
            $payload = json_encode($payload);
        } else {
            $errocode = 0;
            $errormessage = 'Invalid json';
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
            ]
        ]);

        $result = $rest->get('', ['personGuid' => $guid]);
        $response = $result->response;

        // Log to database.
        $this->logresponse('personGuid=' . $guid, $response);

        // Get the JSON payload
        if ($json = json_decode($response)) {
            $payload = $json->payload;

            return $payload;
        } else {

            return null;
        }
    }
}