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
 * @package    local_annoto
 * @copyright  Annoto Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Callback function - injects Annoto's JS into every page.
 */
function local_annoto_before_footer() {
    global $PAGE;

    // Start local_annoto only on the course page or at course module pages.
    if ((strpos($PAGE->pagetype, 'mod-') !== false) ||
        (strpos($PAGE->pagetype, 'course-view-') !== false)) {
        $jsparams = local_annoto_get_jsparams();
        $PAGE->requires->js_call_amd('local_annoto/annoto', 'init', array($jsparams));
    }
}

/**
 * Function prepares parameters for Anooto's JS script
 * @return array
 */
function local_annoto_get_jsparams() {
    global $CFG, $PAGE;

    // Get plugin global settings.
    $settings = get_config('local_annoto');

    // Set id of the video frame where script should be attached.
    $defaultplayerid = 'annoto_default_player_id';
    $isglobalscope = filter_var($settings->scope, FILTER_VALIDATE_BOOLEAN);

    // If scope is not Global - check if url is in access list.
    if (!$isglobalscope) {
        // ACL.
        $acltext = ($settings->acl) ? $settings->acl : null;
        $aclarr = preg_split("/\R/", $acltext);
        $iscourseinacl = false;
        $isurlinacl = false;

        if (is_object($PAGE->course)) {
            $iscourseinacl = in_array($PAGE->course->id, $aclarr);
        }
        if (!$iscourseinacl) {
            $pageurl = $PAGE->url->out();
            $isurlinacl = in_array($pageurl, $aclarr);
        }
        $isaclmatch = ($iscourseinacl || $isurlinacl);
    }

    // Get login, logout urls.
    $loginurl = $CFG->wwwroot . "/login/index.php";
    $logouturl = $CFG->wwwroot . "/login/logout.php?sesskey=" . sesskey();
    // Get activity data for mediaDetails.
    $cmtitle = $PAGE->cm->name ?? ''; // Set empty value, if user is on the course page.
    $cmintro = $PAGE->activityrecord->intro ?? ''; // Set empty value, if user is on the course page.

    // Get course info.
    if (is_object($PAGE->course)) {
        $courseid = $PAGE->course->id;
        $coursename = $PAGE->course->fullname;
        $coursesummary = $PAGE->course->summary;
    }

    // Locale settings.
    if ($settings->locale == "auto") {
        $lang = local_annoto_get_lang();
    } else {
        $lang = $settings->locale;
    }
    $widgetposition = 'right';
    $widgetverticalalign = 'center';
    if (stripos($settings->widgetposition, 'left') !== false) {
        $widgetposition = 'left';
    }
    if (stripos($settings->widgetposition, 'top') !== false) {
        $widgetverticalalign = 'top';
    }
    if (stripos($settings->widgetposition, 'bottom') !== false) {
        $widgetverticalalign = 'bottom';
    }

    $jsparams = array(
        'bootstrapUrl' => $settings->scripturl,
        'clientId' => $settings->clientid,
        'userToken' => local_annoto_get_user_token($settings),
        'position' => $widgetposition,
        'alignVertical' => $widgetverticalalign,
        'widgetOverlay' => $settings->widgetoverlay,
        'featureTab' => !empty($settings->tabs) ? filter_var($settings->tabs, FILTER_VALIDATE_BOOLEAN) : true,
        'featureCTA' => !empty($settings->cta) ? filter_var($settings->cta, FILTER_VALIDATE_BOOLEAN) : false,
        'loginUrl' => $loginurl,
        'logoutUrl' => $logouturl,
        'mediaTitle' => $cmtitle,
        'mediaDescription' => $cmintro,
        'mediaGroupId' => $courseid,
        'mediaGroupTitle' => $coursename,
        'mediaGroupDescription' => $coursesummary,
        'privateThread' => filter_var($settings->discussionscope, FILTER_VALIDATE_BOOLEAN),
        'locale' => $lang,
        'rtl' => filter_var((substr($lang, 0, 2) === "he"), FILTER_VALIDATE_BOOLEAN),
        'demoMode' => filter_var($settings->demomode, FILTER_VALIDATE_BOOLEAN),
        'defaultPlayerId' => $defaultplayerid,
        'zIndex' => !empty($settings->zindex) ? filter_var($settings->zindex, FILTER_VALIDATE_INT) : 100,
        'isGlobalScope' => $isglobalscope,
        'isACLmatch' => !empty($isaclmatch) ? filter_var($isaclmatch, FILTER_VALIDATE_BOOLEAN) : false,
    );

    return $jsparams;
}

/**
 * Function gets user token for Annoto script.
 * @return string
 */
function local_annoto_get_user_token($settings) {
    global $USER, $PAGE;

    // Is user logged in or is guest.
    $userloggined = isloggedin();
    if (!$userloggined) {
        return '';
    }
    $guestuser = isguestuser();

    // Provide page and js with data.
    // Get user's avatar.
    $userpicture = new user_picture($USER);
    $userpicture->size = 150;
    $userpictureurl = $userpicture->get_url($PAGE);

    // Create and encode JWT for Annoto script.
    require_once('JWT.php');                   // Load JWT lib.

    $issuedat = time();                        // Get current time.
    $expire = $issuedat + 60 * 20;             // Adding 20 minutes.

    // Check if user is a moderator.
    $moderator = local_annoto_is_moderator($settings);

    $payload = array(
        "jti" => $USER->id,                     // User's id in Moodle.
        "name" => fullname($USER),              // User's fullname in Moodle.
        "email" => $USER->email,                // User's email.
        "photoUrl" => is_object($userpictureurl) ? $userpictureurl->out() : '',  // User's avatar in Moodle.
        "iss" => $settings->clientid,           // ClientID from global settings.
        "exp" => $expire,                       // JWT token expiration time.
        "scope" => ($moderator ? 'super-mod' : 'user'),
    );

    return JWT::encode($payload, $settings->ssosecret);
}

/**
 * Function gets current language for Annoto script.
 * @return string
 */
function local_annoto_get_lang() {
    global $PAGE, $SESSION, $COURSE, $USER;

    if (isset($COURSE->lang) and !empty($COURSE->lang)) {
        return $COURSE->lang;
    }
    if (isset($SESSION->lang) and !empty($SESSION->lang)) {
        return $SESSION->lang;
    }
    if (isset($USER->lang) and !empty($USER->lang)) {
        return $USER->lang;
    }
    return current_language();
}

/**
 * Function defines either is current user a 'moderator' or not (in the context of Annoto script).
 * @return bolean
 */
function local_annoto_is_moderator($settings) {
    global $COURSE, $USER;

    $reqcapabilities = array(
        'local/annoto:moderatediscussion'
    );

    $coursecontext = context_course::instance($COURSE->id);

    // Check the minimum required capabilities.
    foreach ($reqcapabilities as $cap) {
        if (!has_capability($cap, $coursecontext)) {
            return false;
        }
    }

    // Check if user has a role as defined in settings.
    $userroles = get_user_roles($coursecontext, $USER->id, true);
    $allowedroles = explode(',', $settings->moderatorroles);

    foreach ($userroles as $role) {
        if (in_array($role->roleid, $allowedroles)) {
            return true;
        }
    }

    return false;
}
