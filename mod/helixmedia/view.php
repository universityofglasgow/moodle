<?php


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

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$l  = optional_param('l', 0, PARAM_INT);  // HML ID
$debug = optional_param('debuglaunch', 0, PARAM_INT);

if ($l) {  // Two ways to specify the module
    $hmli = $DB->get_record('helixmedia', array('id' => $l), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('helixmedia', $hmli->id, $hmli->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('helixmedia', $id, 0, false, MUST_EXIST);
    $hmli = $DB->get_record('helixmedia', array('id' => $cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$toolconfig = array();
$toolconfig["launchcontainer"]=get_config("helixmedia", "default_launch");

$PAGE->set_cm($cm, $course); // set's up global $COURSE

if (method_exists("context_module", "instance"))
    $context = context_module::instance($cm->id);
else
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

$PAGE->set_context($context);

$url = new moodle_url('/mod/helixmedia/view.php', array('id'=>$cm->id));
$PAGE->set_url($url);

$launchcontainer = lti_get_launch_container($hmli, $toolconfig);

$launchurl="launch.php?type=".HML_LAUNCH_NORMAL."&id=".$cm->id;

if ($debug) {
    $launchurl.="&debuglaunch=1";
}

if ($launchcontainer == LTI_LAUNCH_CONTAINER_REPLACE_MOODLE_WINDOW) {
    redirect($launchurl);
} else if ($launchcontainer == LTI_LAUNCH_CONTAINER_EMBED_NO_BLOCKS) {
    $PAGE->set_pagelayout('frametop'); //Most frametops don't include footer, and pre-post blocks
    $PAGE->blocks->show_only_fake_blocks(); //Disable blocks for layouts which do include pre-post blocks
} else {
    $PAGE->set_pagelayout('incourse');
}

require_login($course);

helixmedia_view($hmli, $course, $cm, $context);

$pagetitle = strip_tags($course->shortname.': '.format_string($hmli->name));
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

// update_module_button has been deprecated, but since we don't show the admin block on this page we still need the 
// update button, so create it directly.

if (has_capability('mod/helixmedia:addinstance', $context) && has_capability('moodle/course:manageactivities', $context)) {
     $string = get_string('updatethis', '', get_string("modulename", "helixmedia"));
     $url = new moodle_url("$CFG->wwwroot/course/mod.php", array('update' => $cm->id, 'return' => true, 'sesskey' => sesskey()));
     $PAGE->set_button($OUTPUT->single_button($url, $string));
}

// Print the page header
echo $OUTPUT->header();

if ($hmli->showtitlelaunch) {
    // Print the main part of the page
    echo $OUTPUT->heading(format_string($hmli->name));
}

if ($hmli->showdescriptionlaunch && $hmli->intro) {
    echo $OUTPUT->box($hmli->intro, 'generalbox description', 'intro');
}

if ( $launchcontainer == LTI_LAUNCH_CONTAINER_WINDOW ) {
    echo "<script type=\"text/javascript\">//<![CDATA[\n";
    echo "window.open('".$launchurl."','helixmedia');";
    echo "//]]\n";
    echo "</script>\n";
    echo "<p style='text-align:center;'>".get_string("hml_in_new_window_message", "helixmedia")."</p>";
    echo "<p style='text-align:center;'><a href='".$launchurl."' target='_blank'>".get_string("hml_in_new_window", "helixmedia")."</a></p>\n";
} else {
    $size = helixmedia_get_instance_size($hmli->preid, $course->id);

    if ($size->audioonly) {
        echo '<iframe allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" id="contentframe" height="100" width="100%" src="'.htmlspecialchars($launchurl).'"></iframe>';
    } else {

        // Request the launch content with an iframe tag
        echo '<iframe allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" id="contentframe" height="650" width="100%" src="'.htmlspecialchars($launchurl).'"></iframe>';

        //Output script to make the iframe tag be as large as possible
?>
        <script type="text/javascript">
        //<![CDATA[

                YUI().use("node", function() {
                var frame = document.getElementById("contentframe");
                var padding = 250; 
                var lastHeight;
                var resize = function(){
                    var viewportHeight = Y.one("body").get("winHeight");
                    if(lastHeight !== Math.min(Y.one("body").get("docHeight"), viewportHeight)){
                        frame.style.height = viewportHeight - Y.one('#contentframe').getY() + padding + "px";
                        lastHeight = Math.min(Y.one("body").get("docHeight"),viewportHeight);
                    }
                };
                resize();
                setTimeout(resize, 500);

            });
        //]]
        </script>
<?php
    }
}
// Finish the page
echo $OUTPUT->footer();

