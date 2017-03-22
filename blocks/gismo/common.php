<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// error mode
$error_mode = (isset($error_mode) AND in_array($error_mode, array("json", "moodle"))) ? $error_mode : "moodle";

// define constants

if (!defined('ROOT')) {
    //define('ROOT', (realpath(dirname( __FILE__ )) . DIRECTORY_SEPARATOR));
    define('ROOT', substr(realpath(dirname(__FILE__)), 0, stripos(realpath(dirname(__FILE__)), "blocks", 0)) . 'blocks/gismo/');
}
//$path_base=substr(realpath(dirname( __FILE__ )),0,stripos(realpath(dirname( __FILE__ )),"blocks",0)).'blocks/gismo/'; 

if (!defined('LIB_DIR')) {
    define('LIB_DIR', ROOT . "lib" . DIRECTORY_SEPARATOR);
    //define('LIB_DIR', $path_base . "lib" . DIRECTORY_SEPARATOR);
}

// include moodle config file

require_once realpath(ROOT . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config.php");

$q = optional_param('q', '', PARAM_TEXT);
$srv_data_encoded = required_param('srv_data',PARAM_RAW);

// query filter between pages
$query = (isset($q)) ? addslashes($q) : '';

// LIBRARIES MANAGEMENT
// Please use this section to set server side and cliend side libraries to be included    
// server side: please note that '.php' extension will be automatically added                                             

$server_side_libraries = array("third_parties" => array());

// client side: please note that '.js' extension will NOT be automatically added, in order to allow to create file thgat can be parsed by PHP

$client_side_libraries = array("gismo" => array("gismo.js.php", "top_menu.js.php", "left_menu.js.php", "time_line.js", "gismo_util.js"),
    "third_parties" => array("jquery/jquery-1.10.0.min.js",
        "jquery-ui-1.10.3/js/jquery-ui-1.10.3.custom.min.js",
        "jqplot.1.0.8r1250/jquery.jqplot.min.js",
        "jqplot.1.0.8r1250/plugins/jqplot.barRenderer.min.js",
        "jqplot.1.0.8r1250/plugins/jqplot.canvasAxisLabelRenderer.min.js",
        "jqplot.1.0.8r1250/plugins/jqplot.canvasAxisTickRenderer.min.js",
        "jqplot.1.0.8r1250/plugins/jqplot.canvasTextRenderer.min.js",
        "jqplot.1.0.8r1250/plugins/jqplot.categoryAxisRenderer.min.js",
        "jqplot.1.0.8r1250/plugins/jqplot.dateAxisRenderer.min.js",
        "jqplot.1.0.8r1250/plugins/jqplot.highlighter.min.js",
        "jqplot.1.0.8r1250/plugins/jqplot.pointLabels.min.js",
        "simpleFadeSlideShow/fadeSlideShow.min.js"
        ));

// include server-side libraries libraries

if (is_array($server_side_libraries) AND count($server_side_libraries) > 0) {

    foreach ($server_side_libraries as $key => $server_side_libs) {

        if (is_array($server_side_libs) AND count($server_side_libs) > 0) {

            foreach ($server_side_libs as $server_side_lib) {

                $lib_full_path = LIB_DIR . $key . DIRECTORY_SEPARATOR . "server_side" . DIRECTORY_SEPARATOR . $server_side_lib . ".php";

                if (is_file($lib_full_path) AND is_readable($lib_full_path)) {

                    require_once $lib_full_path;
                }
            }
        }
    }
}



// check input data

if (!isset($srv_data_encoded)) {

    block_gismo\GISMOutil::gismo_error('err_srv_data_not_set', $error_mode);

    exit;
}

$srv_data = (object) unserialize(base64_decode(urldecode($srv_data_encoded)));



// course id

if (!property_exists($srv_data, "course_id")) {

    block_gismo\GISMOutil::gismo_error('err_course_not_set', $error_mode);

    exit;
}



// block instance id

if (!property_exists($srv_data, "block_instance_id")) {

    block_gismo\GISMOutil::gismo_error('err_block_instance_id_not_set', $error_mode);

    exit;
}



// check authentication

switch ($error_mode) {

    case "json":

        try {

            require_login($srv_data->course_id, false, NULL, true, true);
        } catch (Exception $e) {

            block_gismo\GISMOutil::gismo_error("err_authentication", $error_mode);

            exit;
        }

        break;

    case "moodle":

    default:

        require_login();

        break;
}



// extract the course    

if (!$course = $DB->get_record("course", array("id" => intval($srv_data->course_id)))) {

    block_gismo\GISMOutil::gismo_error('err_course_not_set', $error_mode);

    exit;
}


// context 

$context_obj = context_block::instance(intval($srv_data->block_instance_id));


//Get block_gismo settings
$gismoconfig = get_config('block_gismo');
if ($gismoconfig->student_reporting === "false") {
    // check authorization
    require_capability('block/gismo:view', $context_obj);
}
// get gismo settings

$gismo_settings = $DB->get_field("block_instances", "configdata", array("id" => intval($srv_data->block_instance_id)));

if (is_null($gismo_settings) OR $gismo_settings === "") {

    $gismo_settings = get_object_vars(block_gismo\GISMOutil::get_default_options());
} else {

    $gismo_settings = get_object_vars(unserialize(base64_decode($gismo_settings)));

    if (is_array($gismo_settings) AND count($gismo_settings) > 0) {

        foreach ($gismo_settings as $key => $value) {

            if (is_numeric($value)) {

                if (strval(intval($value)) === strval($value)) {

                    $gismo_settings[$key] = intval($value);
                } else if (strval(floatval($value)) === strval($value)) {

                    $gismo_settings[$key] = floatval($value);
                }
            }
        }
    }

    // include_hidden_items

    if (!array_key_exists("include_hidden_items", $gismo_settings)) {

        $gismo_settings["include_hidden_items"] = 1;
    }
}

$block_gismo_config = json_encode($gismo_settings);



// actor (teacher or student)

$actor = "student";

if (has_capability("block/gismo:trackuser", $context_obj)) {

    $actor = "student";
}

if (has_capability("block/gismo:trackteacher", $context_obj)) {

    $actor = "teacher";
}
?>
