<?php

define('CLI_SCRIPT', true);

require('../../../config.php');

if (!array_key_exists(1, $argv)) {
    echo "Usage: php testcampus.php <id number>\n";
    die;
}

$config = get_config('local_corehr');;
$campus = new \local_corehr\campus($config->campusendpoint, $config->campususername, $config->campuspassword);

$result = $campus->get_status($argv[1]);
var_dump($result);