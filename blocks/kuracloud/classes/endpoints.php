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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kuracloud;

defined('MOODLE_INTERNAL') || die();

/**
 * All kuraCloud endpoints
 *
 * @copyright 2017 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class endpoints {

    /**
     * Get all API endpoints from the DB
     *
     * @return endpoint[]
     */
    public static function get_all() {
        global $DB;

        $endpoints = $DB->get_records('block_kuracloud_endpoints');

        $return = array();

        foreach ($endpoints as $endpoint) {
            $endpoint->token = self::decrypt_token($endpoint->encrypted_token);

            $return[$endpoint->instanceid] = new endpoint($endpoint);
        }

        return $return;
    }

    /**
     * Get a single API endpoint
     *
     * @param integer $tokenid id of the record for the endpoint
     * @return endpoint
     */
    public static function get($tokenid) {
        global $DB;

        $endpoint = $DB->get_record('block_kuracloud_endpoints', array('id' => $tokenid));

        if (!$endpoint) {
            return false;
        }

        $endpoint->token = self::decrypt_token($endpoint->encrypted_token);

        return new endpoint($endpoint);
    }

    /**
     * Add an API endpoint
     *
     * @param string $apiendpoint URL of endpoint
     * @param string $token API token for endpoint
     * @return boolean
     */
    public static function add($apiendpoint, $token) {
        global $DB;

        $api = new api(new transport($apiendpoint, $token));

        $resp = $api->get_instance();

        $instanceid = clean_param($resp->instanceId, PARAM_TEXT);
        $name = clean_param($resp->displayName, PARAM_TEXT);

        $data = new \stdClass;

        $encryptedtoken = self::encrypt_token($token);

        if (!$encryptedtoken) {
            return false;
        }

        $data->api_endpoint = $apiendpoint;
        $data->encrypted_token = $encryptedtoken;
        $data->instanceid = $instanceid;
        $data->name = $name;

        // Check instance doesn't already exits.
        if ($existingid = $DB->get_field('block_kuracloud_endpoints', 'id', array(
                'instanceid' => $instanceid,
                'api_endpoint' => $apiendpoint))) {

            $data->id = $existingid;
            return $DB->update_record('block_kuracloud_endpoints', $data);
        }
        return $DB->insert_record('block_kuracloud_endpoints', $data);
    }

    /**
     * Delete an API endpoint
     *
     * @param string $instanceid kuraCloud instance id
     * @return boolean
     */
    public static function delete($instanceid) {
        global $DB;
        $courses = new courses;
        $courses->delete_all_mappings($instanceid);
        return $DB->delete_records('block_kuracloud_endpoints', array('instanceid' => $instanceid));
    }

    /**
     * Decrypt an API token
     *
     * @param string $encryptedtoken base64encoded aes-256-cbc encrypted token
     * @return string
     */
    private static function decrypt_token($encryptedtoken) {
        $key = self::get_key();

        list($iv, $data) = explode(':', $encryptedtoken);

        return openssl_decrypt(base64_decode($data), 'aes-256-cbc', $key, 0, base64_decode($iv));
    }

    /**
     * Encrypt an API token
     *
     * @param string $token API token
     * @return string
     */
    private static function encrypt_token($token) {
        $key = self::get_key();

        if (!$key) {
            return false;
        }

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = base64_encode(openssl_encrypt($token, 'aes-256-cbc', $key, 0, $iv));

        return base64_encode($iv).':'.$encrypted;
    }


    /**
     * Get key used to encrypt/decrypt tokens
     *
     * Generated at plugin install time
     *
     * @return string
     */
    private static function get_key() {
        $fs = get_file_storage();

        $fileinfo = array(
            'contextid' => \context_system::instance()->id,
            'component' => 'block_kuracloud',
            'filearea' => 'tokenkey',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'token.key');

        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                      $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        if (!$file) {
            return false;
        }
        return $file->get_content();
    }

}