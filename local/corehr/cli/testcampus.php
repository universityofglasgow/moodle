<?php

define('CLI_SCRIPT', true);

require('../../../config.php');

if (!array_key_exists(1, $argv)) {
    echo "Usage: php testcampus.php <guid>\n";
    die;
}

$config = get_config('local_corehr');
$idnumber = isset($argv[2]) ? $argv[2] : 0;
$campus = new \local_corehr\campus($config->campusendpoint, $config->campususername, $config->campuspassword, $idnumber);

$result = $campus->get_status($argv[1]);
var_dump($result);