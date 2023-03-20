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
 * This file contains all necessary code to view a helixmedia activity instance
 *
 * @package    mod
 * @subpackage helixmedia
 * @copyright  2009 Marc Alier, Jordi Piguillem, Nikolas Galanis
 *  marc.alier@upc.edu
 * @copyright  2009 Universitat Politecnica de Catalunya http://www.upc.edu
 * @author     Marc Alier
 * @author     Jordi Piguillem
 * @author     Nikolas Galanis
 * @author     Chris Scribner
 * @author     Tim Williams for Streaming LTD 2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/helixmedia/lib.php');
require_once($CFG->dirroot.'/mod/helixmedia/locallib.php');

global $CFG, $PAGE;

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$l = optional_param('l', 0, PARAM_INT);  // HML ID.
$debug = optional_param('debuglaunch', 0, PARAM_INT);

if ($l) { // Two ways to specify the module.
    $hmli = $DB->get_record('helixmedia', array('id' => $l), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('helixmedia', $hmli->id, $hmli->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('helixmedia', $id, 0, false, MUST_EXIST);
    $hmli = $DB->get_record('helixmedia', array('id' => $cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$toolconfig = array();
$toolconfig["launchcontainer"] = get_config("helixmedia", "default_launch");

$PAGE->set_cm($cm, $course); // Set's up global $COURSE.

if (method_exists("context_module", "instance")) {
    $context = context_module::instance($cm->id);
} else {
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
}

$PAGE->set_context($context);

$url = new moodle_url('/mod/helixmedia/view.php', array('id' => $cm->id));
$PAGE->set_url($url);

$launchcontainer = lti_get_launch_container($hmli, $toolconfig);

$lparams = array('type' => HML_LAUNCH_NORMAL, 'id' => $cm->id);
//$launchurl = "launch.php?type=".HML_LAUNCH_NORMAL."&id=".$cm->id;

if ($debug) {
    //$launchurl .= "&debuglaunch=1";
    $lparams['debuglaunch'] = 1;
}

$launchurl = new moodle_url('/mod/helixmedia/launch.php', $lparams);

if ($launchcontainer == LTI_LAUNCH_CONTAINER_EMBED_NO_BLOCKS) {
    $PAGE->set_pagelayout('base'); 
    $PAGE->blocks->show_only_fake_blocks();
} else {
    $PAGE->set_pagelayout('incourse');
}

require_login($course);

helixmedia_view($hmli, $course, $cm, $context);

$pagetitle = strip_tags($course->shortname.': '.format_string($hmli->name));
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

// Update_module_button has been deprecated, but since we don't show the admin block on this page we still need the
// update button, so create it directly.

if (has_capability('mod/helixmedia:addinstance', $context) && has_capability('moodle/course:manageactivities', $context)) {
     $string = get_string('updatethis', '', get_string("modulename", "helixmedia"));
     $url = new moodle_url("/course/mod.php", array('update' => $cm->id, 'return' => true, 'sesskey' => sesskey()));
     $PAGE->set_button($OUTPUT->single_button($url, $string));
}

// Print the page header.
echo $OUTPUT->header();

if ($hmli->showtitlelaunch) {
    // Print the main part of the page.
    echo $OUTPUT->heading(format_string($hmli->name));
}

if ($hmli->showdescriptionlaunch && $hmli->intro) {
    echo $OUTPUT->box($hmli->intro, 'generalbox description', 'intro');
}

if ( $launchcontainer == LTI_LAUNCH_CONTAINER_WINDOW ) {
    $output = $PAGE->get_renderer('mod_helixmedia');
    $disp = new \mod_helixmedia\output\viewwindow($launchurl->out(false), 0);
    echo $output->render($disp);
} else {
    $size = helixmedia_get_instance_size($hmli->preid, $course->id);

    if ($size->audioonly) {
        $height = 100;
    } else {
        $height = 0;
    }

    $output = $PAGE->get_renderer('mod_helixmedia');
    $disp = new \mod_helixmedia\output\view($launchurl->out(true), $height);
    echo $output->render($disp);
}
// Finish the page.
echo $OUTPUT->footer();

