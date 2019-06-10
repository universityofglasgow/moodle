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
 * JSON Web Token implementation, based on this spec:
 * http://tools.ietf.org/html/draft-ietf-oauth-json-web-token-06
 *
 * PHP version 5
 *
 * @category Authentication
 * @package  Authentication_JWT
 * @author   Neuman Vong <neuman@twilio.com>
 * @author   Anant Narayanan <anant@php.net>
 * @license  http://opensource.org/licenses/BSD-3-Clause 3-clause BSD
 * @link     https://github.com/firebase/php-jwt
 */

defined('MOODLE_INTERNAL') || die();

class JWT
{

    /**
     * Converts and signs a PHP object or array into a JWT string.
     *
     * @param object|array $payload PHP object or array
     * @param string       $key     The secret key
     * @param string       $algo    The signing algorithm. Supported
     *                              algorithms are 'HS256', 'HS384' and 'HS512'
     *
     * @return string      A signed JWT
     * @uses jsonencode
     * @uses urlsafeb64encode
     */
    public static function encode($payload, $key, $algo = 'HS256') {
        $header = array('typ' => 'JWT', 'alg' => $algo);

        $segments = array();
        $segments[] = self::urlsafeb64encode(self::jsonencode($header));
        $segments[] = self::urlsafeb64encode(self::jsonencode($payload));
        $signinginput = implode('.', $segments);

        $signature = self::sign($signinginput, $key, $algo);
        $segments[] = self::urlsafeb64encode($signature);

        return implode('.', $segments);
    }

    /**
     * Sign a string with a given key and algorithm.
     *
     * @param string $msg    The message to sign
     * @param string $key    The secret key
     * @param string $method The signing algorithm. Supported
     *                       algorithms are 'HS256', 'HS384' and 'HS512'
     *
     * @return string          An encrypted message
     * @throws DomainException Unsupported algorithm was specified
     */
    public static function sign($msg, $key, $method = 'HS256') {
        $methods = array(
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        );
        if (empty($methods[$method])) {
            throw new DomainException('Algorithm not supported');
        }
        return hash_hmac($methods[$method], $msg, $key, true);
    }

    /**
     * Encode a PHP object into a JSON string.
     *
     * @param object|array $input A PHP object or array
     *
     * @return string          JSON representation of the PHP object or array
     * @throws DomainException Provided object could not be encoded to valid JSON
     */
    public static function jsonencode($input) {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            self::_handleJsonError($errno);
        } else if ($json === 'null' && $input !== null) {
            throw new DomainException('Null result with non-null input');
        }
        return $json;
    }

    /**
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string The base64 encode of what you passed in
     */
    public static function urlsafeb64encode($input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

}