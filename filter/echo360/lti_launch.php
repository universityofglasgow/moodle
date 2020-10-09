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
 * @package local_echo360
 * @author  Echo360
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');
require_once($CFG->dirroot . '/lib/editor/atto/plugins/echo360attoplugin/LtiConfiguration.php');

const ECHO360_LABEL_MOD_NAME = 'label';

global $USER, $PAGE, $COURSE;

// Query string parameters.
$url    = required_param('url', PARAM_URL);             // LTI url.
$cmid   = required_param('cmid', PARAM_INT);            // Course module id.
$width  = optional_param('width', null, PARAM_INT);     // IFrame width (optional to support lti_launch_url links).
$height = optional_param('height', null, PARAM_INT);    // IFrame height (optional to support lti_launch_url links).

if ($width != null && $height != null) {
    if ($width < 50 || $width > 3000) {
        $width = 600;
    }
    if ($height < 50 || $height > 3000) {
        $height = 400;
    }
}

try {
    $cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $context = context_course::instance($course->id);
    $PAGE->set_cm($cm, $course); // Set identified Course Module context for cmid value in authenticated embedded Echo360 media link.
} catch (Exception $e) {
    // Do not know the Course by the Course Module specified in the embedded Echo360 media link, use HTTP referer to determine Course context.
    if (isset($_SERVER['HTTP_REFERER'])) {
        // Check HTTP_REFERER is a view.php page to extract the id / course query value.
        if (substr(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH), -strlen('view.php')) === 'view.php') {
            parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $refererparams);

            if (isset($refererparams['id']) && is_numeric($refererparams['id'])) {
                $courseid = $refererparams['id'];
            } else if (isset($refererparams['course']) && is_numeric($refererparams['course'])) {
                $courseid = $refererparams['course'];
            } else {
                $courseid = null;
            }
        } else {
            $courseid = null;
        }
    }

    if (isset($courseid)) {
        $course = $DB->get_record('course', array('id' => $courseid));
        $context = context_course::instance($course->id);
    } else {
        $context = context_system::instance(); // Unable to determine Course context, default to Site context.
    }
    $PAGE->set_context($context); // Set identified Course / Site context for cmid value in authenticated embedded Echo360 media link.
}

// Verify user access.
if (isset($course)) {
    require_login($course, true);
} else {
    require_login();
}

// Remote LTI call.
const ATTO_PLUGIN_NAME = 'atto_echo360attoplugin';
$lti        = new Echo360\LtiConfiguration($context, ATTO_PLUGIN_NAME);
$params     = array();
if ($width != null && $height != null) {
    $params = array(
        'launch_presentation_document_target' => 'iframe',
        'launch_presentation_width'           => $width,
        'launch_presentation_height'          => $height
    );
}
$lticonfig = $lti->generate_lti_configuration($params);

$formid = 'form-' . rand(1000, 9999);
$action = $url;
echo '<html><body>';
echo '<form id="' . $formid . '" action="' . $url . '" method="post">' . "\n";
foreach ($lticonfig as $key => $value) {
    echo '<input type="hidden" name="' . $key . '" value="' . $value . '">' . "\n";
}
echo '</form>';
echo '<script>document.getElementById("' . $formid . '").submit();</script>';
echo '</body></html>';

