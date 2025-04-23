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

namespace tiny_echo360\external;

require_once($CFG->dirroot . '/lib/externallib.php');
require_once($CFG->libdir . '/accesslib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

/**
 * Web Service to load the current LTI configuration.
 *
 * @package     tiny_echo360
 * @category    external
 * @copyright   2023 Echo360 Inc.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lti_request_configuration extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id', VALUE_REQUIRED),
        ]);
    }

    /**
     * Request the LTI configuration based on context ID.
     *
     * @param int $contextid The context id of the owner
     * @return null
     */
    public static function execute(int $contextid): array {
        [
            'contextid' => $contextid,
        ] = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
        ]);

		$contextinfo = get_context_info_array($contextid);
        $lti = new \tiny_echo360\lti\configuration($contextinfo[0], $contextinfo[1], $contextinfo[2]);
        $result = $lti->generate();

        return $result;
    }

    /**
     * Describe the return structure of the external service.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'lti_version' => new external_value(PARAM_ALPHANUMEXT, 'LTI version'),
            'lti_message_type' => new external_value(PARAM_ALPHANUMEXT, 'LTI message type'),
            'resource_link_id' => new external_value(PARAM_INT, 'Context Id'),
            'ext_content_intended_use' => new external_value(PARAM_ALPHANUMEXT, 'Intended use for the text area'),
            'tool_consumer_info_product_family_code' => new external_value(PARAM_ALPHANUMEXT, 'LTI product family'),
            'tool_consumer_info_version' => new external_value(PARAM_RAW, 'Moodle version'),
            'selection_directive' => new external_value(PARAM_ALPHANUMEXT, 'selection_directive'),
            'launch_url' => new external_value(PARAM_URL, 'Url to launch the LTI'),
            'context_id' => new external_value(PARAM_INT, 'Course Id'),
            'context_title' => new external_value(PARAM_RAW, 'Course fullname'),
            'context_label' => new external_value(PARAM_RAW, 'Course shortname'),
            'user_id' => new external_value(PARAM_INT, 'User Id'),
            'lis_person_name_full' => new external_value(PARAM_RAW, 'User full name'),
            'lis_person_name_family' => new external_value(PARAM_RAW, 'User family name'),
            'lis_person_name_given' => new external_value(PARAM_RAW, 'User given name'),
            'lis_person_contact_email_primary' => new external_value(PARAM_RAW, 'User email'),
            'roles' => new external_value(PARAM_RAW, 'User highest role'),
            'oauth_callback' => new external_value(PARAM_RAW, 'Not used'),
            'oauth_consumer_key' => new external_value(PARAM_RAW, 'OAuth consumer key'),
            'oauth_version' => new external_value(PARAM_RAW, 'OAuth version'),
            'oauth_nonce' => new external_value(PARAM_RAW, 'OAuth nonce'),
            'oauth_timestamp' => new external_value(PARAM_RAW, 'OAuth timestamp'),
            'oauth_signature_method' => new external_value(PARAM_ALPHANUMEXT, 'OAuth signature method'),
            'oauth_signature' => new external_value(PARAM_RAW, 'OAuth valid signature'),
            'custom_echo360_plugin_version' => new external_value(PARAM_RAW, 'Custom version number'),
        ]);
    }
}
