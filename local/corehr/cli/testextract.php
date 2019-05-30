<?php

define('CLI_SCRIPT', true);

require('../../../config.php');
require_once($CFG->dirroot . '/local/corehr/locallib.php');

if (!array_key_exists(1, $argv)) {
    echo "Usage: php testextract.php <guid>\n";
    die;
}

$results = local_corehr_extract($argv[1]);
$vars = get_object_vars($results);
foreach ($vars as $name => $value) {
    if (empty($value)) {
        continue;
    }
    echo "$name => $value\n";
}

