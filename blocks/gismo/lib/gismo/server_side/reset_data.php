<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);//Comment this if u want to run behat tests

require_once __DIR__ . "/../../../../../config.php";

// trace start
echo "GISMO - reset data (start)!<br />";

$gdm = new block_gismo\GISMOdata_manager(true);

$gdm->devel_mode_reset();

echo "GISMO - reset data (end)!<br />";
?>