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
 * Atto text editor integration version file.
 *
 * @package    atto_helixatto
 * @copyright  2014 Streaming LTD
 * @author     Tim Williams (tmw@autotrain.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/mod/helixmedia/locallib.php');

/**
 * Initialise this plugin
 * @param string $elementid
 */
function atto_helixatto_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('insert',
                                          'cancel',
                                          'dialogtitle',
                                          'showvideo'),
                                    'atto_helixatto');
}

/**
 * Parses a list of module types and checks if they match the one we are in.
 */
function atto_helixatto_checklist($param) {
    global $PAGE, $DB;
    $config = get_config('atto_helixatto', $param);
    $types = explode("\n", $config);

    for ($i = 0; $i < count($types); $i++) {
        $types[$i] = trim($types[$i]);
        if (strlen($types[$i]) > 0 && strpos($PAGE->url, '/'.$types[$i].'/') !== false &&
            $DB->get_record('modules', array('name' => $types[$i]))) {
            return $types[$i];
        }
    }
    return false;
}


/**
 * Return the js params required for this module.
 * @return array of additional params to pass to javascript init function for this module.
 */
function atto_helixatto_params_for_js($elementid, $options, $fpoptions) {
    global $USER, $COURSE, $CFG;

    /**Switch of button when using the activity module.
       Use PARAM_RAW type here in case "add" is used for something other than a plugin name in other parts of moodle**/
    $add = optional_param("add", "none", PARAM_RAW);
    $action = optional_param("action", "none", PARAM_RAW);

    $coursecontext = context_course::instance($COURSE->id);
    $usercontextid = context_user::instance($USER->id)->id;

    $params = array();
    $params['usercontextid'] = $usercontextid;
    $params['disabled'] = true;
    $params['linkonly'] = false;
    $params['modtype'] = "";

    if (atto_helixatto_checklist('uselinkdesc')) {
        $params['linkonly'] = true;
    }

    $mtype = atto_helixatto_checklist('modtypeperm');

    if ($mtype) {
        if (has_capability('atto/helixatto:visiblemodtype', $coursecontext)) {
            $params['disabled'] = false;
            $params['modtype'] = $mtype;
            $params['linkonly'] = true;
        }
    } else {
        if (has_capability('atto/helixatto:visible', $coursecontext)) {
            $params['disabled'] = false;
        }
    }

    if ($add == "helixmedia" || $action == "grader" || $action == "grade") {
        $params['disabled'] = true;
    }

    $params['baseurl'] = $CFG->wwwroot;
    $params['ltiurl'] = get_config("helixmedia", "launchurl");
    $params['statusurl'] = helixmedia_get_status_url();
    $params['userid'] = $USER->id;
    $params['insertdelay'] = get_config('helixmedia', 'modal_delay');
    $params['oauthConsumerKey'] = get_config('helixmedia', 'consumer_key');
    if ($params['insertdelay'] > -1) {
        $params['hideinsert'] = get_config('atto_helixatto', 'hideinsert');
    } else {
        $params['hideinsert'] = "0";
    }
    return $params;
}

