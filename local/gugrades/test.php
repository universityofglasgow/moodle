<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');

$grades = new local_gugrades\grades(7);
echo "<pre>";
$tree = $grades->get_activitytree(6);
var_dump($tree);