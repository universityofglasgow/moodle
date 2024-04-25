<?php

define('CLI_SCRIPT', true);

require('../../../config.php');

if (!array_key_exists(1, $argv)) {
    echo "Usage: php testextract.php <courseid>\n";
    die;
}

$courseid = $argv[1];
\local_corehr\api::extract_course_staff($courseid);