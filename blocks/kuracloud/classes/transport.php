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
 * Interface to API transport
 */
interface apitransport {

    /**
     * Do an HTTP GET
     *
     * @param string $url
     * @return string
     */
    public function get($url);

    /**
     * Do an HTTP PUT
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function put($url, $params);

    /**
     * Do an HTTP POST
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function post($url, $params);
}

/**
 * API transport class
 *
 * @copyright 2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transport implements apitransport {

    /**
     * URL of API endpoint
     *
     * @var string
     */
    private $endpointurl;

    /**
     * API authentication token
     *
     * @var string;
     */
    private $token;

    /**
     * Content mime type of API payload
     *
     * @var string
     */
    private $contenttype;

    /**
     * Construct transport objects
     *
     * @param string $endpointurl URL to the endpoint
     * @param string $token API token
     * @param string $contenttype normally 'application/json'
     */
    public function __construct($endpointurl, $token, $contenttype = 'application/json') {
        $this->endpointurl = $endpointurl;
        $this->token = $token;
        $this->contenttype = $contenttype;
    }

    /**
     * Do an HTTP GET
     *
     * @param string $url to GET
     * @return array
     */
    public function get($url) {

        $fullurl = $this->endpointurl.$url;

        $curl = new kuracurl();
        $curl->setHeader("X-Kura-Token: {$this->token}");
        $curl->setHeader("Content-Type: {$this->contenttype}");

        $response = $curl->get($fullurl);

        return array($response, $curl->info['http_code'], $curl->error);
    }

    /**
     * Do an HTTP PUT
     *
     * @param string $url to PUT
     * @param array $params associative array of params
     * @return array
     */
    public function put($url, $params) {

        $fullurl = $this->endpointurl.$url;

        $curl = new kuracurl();
        $curl->setHeader("X-Kura-Token: {$this->token}");
        $curl->setHeader("Content-Type: {$this->contenttype}");

        $response = $curl->put($fullurl, $params);

        return array($response, $curl->info['http_code'], $curl->error);
    }

    /**
     * Do an HTTP POST
     *
     * @param string $url to POST
     * @param array $params associative array of params
     * @return array
     */
    public function post($url, $params) {

        $fullurl = $this->endpointurl.$url;

        $curl = new kuracurl();
        $curl->setHeader("X-Kura-Token: {$this->token}");
        $curl->setHeader("Content-Type: {$this->contenttype}");

        $response = $curl->post($fullurl, $params);

        return array($response, $curl->info['http_code'], $curl->error);
    }

}