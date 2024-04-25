<?php
$avgs = sys_getloadavg();
foreach ($avgs as $avg) {
    echo $avg . "\n";
}
