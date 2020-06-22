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

    protected $idnumber = 0;

    /**
     * Constructor
     */
    public function __construct($endpoint, $username, $password, $idnumber = 0) {
        $this->endpoint = $endpoint;
        $this->username = $username;
        $this->password = $password;
        $this->idnumber = $idnumber;
    }

    /**
     * Get campus card status given username (guid)
     * @param string $username
     */
    public function get_status($username) {
        global $DB;

        if (!$this->idnumber) {
            $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
            if (!$idnumber = $user->idnumber) {
                return false;
            }
        } else {
            $idnumber = $this->idnumber;
        }

        $ch = curl_init($this->endpoint . 'campuscard/status/' . $idnumber);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadName);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($ch);
        curl_close($ch);

        list($headers, $body) = explode("\r\n\r\n", $return);

        return json_decode($body);
    }
}
