<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// mode (josn)
$error_mode = "json";

// libraries & acl
require_once "common.php";

// query
//$q = optional_param('q', '', PARAM_TEXT);
$config_data = optional_param_array('config_data',array(),PARAM_INT);
// decide what to do
switch ($q) {
    case "save":
        $result = array("status" => "false");
        if (isset($config_data ) AND is_array($config_data ) AND count($config_data ) > 0) {
            // serialize and encode config data
            $config_data_encode = base64_encode(serialize((object) $config_data ));
            // update config
            $check = $DB->set_field("block_instances", "configdata", $config_data_encode, array("id" => $srv_data->block_instance_id));
            if ($check !== false) {
                $result["status"] = "true";
            }
        }
        break;
    default:
        break;
}

// send response
echo json_encode($result);
?>
