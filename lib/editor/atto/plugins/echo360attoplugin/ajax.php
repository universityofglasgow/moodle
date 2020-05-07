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
 * AJAX script to return configuraton parameters for LTI launch request.
 *
 * @package   atto_echo360attoplugin
 * @copyright COPYRIGHTINFO
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../../../config.php');
require($CFG->dirroot.'/lib/editor/atto/plugins/echo360attoplugin/LtiConfiguration.php');
use Echo360\LtiConfiguration;

const ECHO360ATTOPLUGIN_NAME = 'atto_echo360attoplugin';
const ECHO360ATTOPLUGIN_VERSION = '1.0.19';

$contextcourseid = required_param('contextcourseid', PARAM_INT);
$pagetype = required_param('pagetype', PARAM_TEXT);
list($context, $course, $cm) = get_context_info_array($contextcourseid);
require_login($course, false, $cm);
require_sesskey();

return request_lti_configuration($course->id, $pagetype);

/**
 * Return LTI configuration parameters for LTI launch request.
 *
 * @param  $courseid
 * @return mixed
 */
function request_lti_configuration($courseid, $pagetype) {
    try {
        $context = context_course::instance($courseid);
        $lti = new LtiConfiguration($context, ECHO360ATTOPLUGIN_NAME, $pagetype);
        $customparams = array('custom_echo360_plugin_version' => ECHO360ATTOPLUGIN_VERSION);
        echo LtiConfiguration::object_to_json($lti->generate_lti_configuration($customparams));
    } catch (Exception $e) {
        echo LtiConfiguration::object_to_json($e->getMessage());
        http_response_code(404);
    }
}

