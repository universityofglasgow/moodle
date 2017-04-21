<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true); //Comment this if u want to run behat tests

require_once __DIR__ . "/../../../../../config.php";

$config = get_config('block_gismo');
// This script is being called via the web, so check the password if there is one.
if (!empty($config->manualexportpassword)) {
    $pass = optional_param('password', '', PARAM_RAW);
    if ($pass != $config->manualexportpassword) {
        // wrong password.
        print_error('manualexportpassworderror', 'block_gismo');
        exit;
    }
}

// trace start
echo "GISMO - export data (start)!<br />";

$gdm = new block_gismo\GISMOdata_manager(true);

// purge
$purge_check = $gdm->purge_data();
if ($purge_check === true) {
    echo "Gismo data has been purged successfully!<br />";
} else {
    echo $purge_check . "<br />";
}

// sync
$sync_check = $gdm->sync_data();
if ($sync_check === true) {
    echo "Gismo data has been syncronized successfully!<br />";
} else {
    echo $sync_check . "<br />";
}

// trace end
echo "GISMO - export data (end)!<br />";

// ok     
return true;
?>