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
 * Tiny Echo360 external functions and service definitions.
 *
 * @package    tiny_echo360
 * @category   webservice
 * @copyright  2023 Echo360
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'tiny_echo360_request_lti_configuration' => array(
        'classname'   => 'tiny_echo360\external\lti_request_configuration',
        'description' => 'Retrieve the LTI configuration information.',
        'type'        => 'read',
        'capabilities' => 'tiny/echo360:visible',
        'ajax'          => true,
    )
);
