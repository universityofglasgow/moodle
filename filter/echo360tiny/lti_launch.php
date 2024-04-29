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
 * Echo360 view file for LTI content.
 *
 * @package    filter_echo360tiny
 * @copyright  2023 Echo360 Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("../../mod/lti/locallib.php");
require_once("../../lib/editor/atto/plugins/echo360attoplugin/LtiConfiguration.php");

global $USER, $PAGE, $COURSE, $SESSION;

// Query string parameters.
$url    = required_param('url', PARAM_URL);             // LTI url.
$cmid   = required_param('cmid', PARAM_INT);            // Course module id.
$width  = optional_param('width', null, PARAM_INT);     // IFrame width (optional to support lti_launch_url links).
$height = optional_param('height', null, PARAM_INT);    // IFrame height (optional to support lti_launch_url links).
$resourcelinkid = optional_param('resourcelinkid', "0", PARAM_TEXT);    // LTI resourcelinkidparam added by button and filter.

const ECHO360_LABEL_MOD_NAME = 'label';
const ECHO360TINYPLUGIN_NAME = 'tiny_echo360';
const ECHO360TINYPLUGIN_VERSION = '1.0.0';

if ($width != null && $height != null) {
    if ($width < 50 || $width > 3000) {
        $width = 600;
    }
    if ($height < 50 || $height > 3000) {
        $height = 400;
    }
}

$cmid = required_param('cmid', PARAM_INT);
$course = null;
$cm = null;
if ($cmid) {
    list($course, $cm) = get_course_and_cm_from_cmid($cmid);
    require_login($course, false, $cm);
    $context = context_module::instance($cm->id);
} else {
    require_login();
    $context = context_system::instance();
}


// Remote LTI call.
$lti        = new \tiny_echo360\lti\configuration($context, $course, $cm);
$params     = array();
if ($width != null && $height != null) {
    $params = array(
        'launch_presentation_document_target' => 'iframe',
        'launch_presentation_width'           => $width,
        'launch_presentation_height'          => $height
    );
}

$lticonfig = $lti->generate($params);

// Read Echo360 Atto Plugin optional LTI 1.3 settings.
$lti1p3configurationenabled = get_config(ECHO360TINYPLUGIN_NAME, 'lti1p3configurationenabled');
// Selected Echo360 LTI 1.3 Configuration's deployment id.
$toolid = get_config(ECHO360TINYPLUGIN_NAME, 'lti1p3configurationselection');

if (($lti1p3configurationenabled) && (!is_null($toolid)) && ($toolid != 0)) {
    // Retrieve LTI 1.3 external tool configuration.
    $config = lti_get_type_type_config($toolid);
    // Check if deep link url is same domain as LTI 1.3 config tool url.
    $configtoolurlparts = parse_url($config->lti_toolurl);
    $deeplinkurlparts = parse_url($url);
    if ($config->lti_ltiversion === LTI_VERSION_1P3 && $deeplinkurlparts['host'] === $configtoolurlparts['host']) {
        $deeplink = new \filter_echo360tiny\deep_link();
        $deeplink->toolurl = $url;
        // The svc-lti service expects only path.
        // 404!
        $customparams["custom_auth_request_path"] = '/lib/editor/tiny/plugins/echo360/auth.php';
        if (isset($SESSION->lti_initiatelogin_status)) {
            unset($SESSION->lti_initiatelogin_status);
        }
        $customdeeplink = new \filter_echo360tiny\custom_deep_link();
        echo $customdeeplink->lti_initiate_login($course->id, $resourcelinkid, $deeplink, $config, 'basic-lti-launch-request', '',
            '', $deeplink->toolurl, $customparams);
        exit;
    }
}

// Generate LTI launch form and post details.
$formid = 'form-' . rand(1000, 9999);
// XSS Protection, only launch to the configured URL.
$url = $lticonfig['launch_url'] . '?' . parse_url($url, PHP_URL_QUERY);
echo html_writer::start_tag('html');
echo html_writer::start_tag('body');
echo html_writer::start_tag('form', array('id' => $formid, 'action' => $url, 'method' => 'post'));
foreach ($lticonfig as $key => $value) {
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $key, 'value' => $value));
}
echo html_writer::end_tag('form');
echo html_writer::tag('script', 'document.getElementById("' . $formid . '").submit();', null);
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
