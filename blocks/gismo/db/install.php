<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_block_gismo_install() {
    global $DB;

    // define config data
    $config_data = array();
    $config_data[] = (object) array("name" => "last_export_time", "value" => 0, "type" => "integer");
    $config_data[] = (object) array("name" => "last_export_max_log_id", "value" => 0, "type" => "integer");

    // add records to the 'block_gismo_config' table
    if (is_array($config_data) AND count($config_data) > 0) {
        foreach ($config_data as $entry) {
            try {
                $DB->insert_record("block_gismo_config", $entry);
            } catch (Exception $e) {
                echo 'Error: ' . $e->getMessage() . "\n";
            }
        }
    }
}
