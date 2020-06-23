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
 * API for Campus Card access
 *
 * @package    local_corehr
 * @copyright  2016-19 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_corehr;

class campus {

    protected $endpoint = '';

    protected $username = '';

    protected $password = '';

    /**
     * Constructor
     */
    public function __construct($endpoint, $username, $password) {
        $this->endpoint = $endpoint;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get campus card status given username (guid)
     * @param int $idnumber
     */
    public function get_status($idnumber) {
        global $DB;

        $ch = curl_init($this->endpoint . 'campuscard/status/' . $idnumber);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (($code == 200) && !$error) {
            list($headers, $body) = explode("\r\n\r\n", $return);
            return json_decode($body);
        } else {
            return [
                'response' => 'CURL_ERROR',
                'responseDescription' => $error,
                'personID' => $idnumber,
                'currentUserStatus' => '',
                'HTTPCode' => $code
            ];
        }
    }

    /**
     * Unban user given username
     * @param string $idnumber
     */
    public function unban($idnumber) {
        global $DB;

        $ch = curl_init($this->endpoint . 'campuscard/unban/' . $idnumber);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (($code == 200) && !$error) {
            list($headers, $body) = explode("\r\n\r\n", $return);
            return json_decode($body);
        } else {
            return [
                'response' => 'CURL_ERROR',
                'responseDescription' => $error,
                'personID' => $idnumber,
                'currentUserStatus' => '',
                'HTTPCode' => $code
            ];
        }
    }

    /**
     * Are errors permanent?
     * @param string $error 
     * @return array
     */
    private function is_error_permanent($error) {
        switch ($error) {
            case 'INTERNAL_SERVER_ERROR':
                return false;
            case 'NOT_FOUND':
                return true;
            case 'REQUEST_TIMEOUT':
                return false;
            case 'RECORD_MANUALLY_BANNED':
                return true;
            case 'CURL_ERROR':
                return false;
            default:
                return false;
        }
    }

    /**
     * Send to campus
     * @param object $status db table record
     */
    public function send($status) {
        global $DB;

        $response = unban($status->personnelno);

        // Deal sensibly with response
        $message = trim($message->response);
        $status->lasttry = time();
        if ($message == 'OK') {
            $status->status = 'OK';
        } else {
            $permanent = $this->is_error_permanent($message);
            $status->error = substr($message . ' (' . $response->responseDescription . ')', 0, 49);
            if ($permanent) {
                $status->status = 'error';
            } else {
                $status->retrycount++;
                \local_corehr\api::mtrace('local_corehr: Retry count for user ' . $status->userid . ' is now ' . $status->retrycount);
                if ($status->retrycount > 12) {
                    $status->status = 'timeout';
                }
            }
        }

        $DB->update_record('local_corehr_status', $status);
    }

}
