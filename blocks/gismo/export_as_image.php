<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// include moodle config file
define('ROOT', substr(realpath(dirname(__FILE__)), 0, stripos(realpath(dirname(__FILE__)), "blocks", 0)) . 'blocks/gismo/');
require_once realpath(ROOT . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config.php");

$data = required_param('data',PARAM_RAW);
$chart_id_val = required_param('chart_id',PARAM_RAW);
// data
$img_mime = "image/png";
$img_data = (isset($data)) ? base64_decode($data) : "";
$chart_id = "";

if (isset($chart_id_val)) {
    $pieces = explode("-", $chart_id_val);
    if (is_array($pieces) AND count($pieces) > 0) {
        foreach ($pieces as $p) {
            $chart_id .= ucfirst(str_replace(array(" ", ".", "_", "\\", "/"), array(""), trim($p)));
        }
    }
}

// send headers
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT\n");
header("Content-type: " . $img_mime . ";\n");
header("Content-Disposition: attachment; filename=\"GISMOChart" . $chart_id . ".png\";\n");

// send output
echo $img_data;
?>