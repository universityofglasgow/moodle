<?php

$month = date('m');
$year = date('Y');


echo "$month $year \n";

if ($month > 7) {
    $year++;
}

$future = strtotime("$year-07-31");

echo date('Y-m-d', $future);
