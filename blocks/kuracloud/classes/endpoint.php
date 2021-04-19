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
 * A kuraCloud API endpoint
 *
 * @copyright 2017 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class endpoint {

    /**
     * API object
     *
     * @var api
     */
    public $api;

    /**
     * Name of endpoint
     *
     * @var string
     */
    public $name;

    /**
     * InstanceID of the endpoint
     *
     * @var string
     */
    public $instanceid;

    /**
     * Construct object containing transport object
     *
     * @param \stdClass $endpoint record from the DB
     */
    public function __construct($endpoint) {
        $this->name = $endpoint->name;
        $this->instanceid = $endpoint->instanceid;
        $this->api = new api(new transport($endpoint->api_endpoint, $endpoint->token));
    }
}