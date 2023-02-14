<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/grade/lib.php');

$grades = new local_gugrades\grades(7);
echo "<pre>";
$categories = $grades->get_firstlevel();
foreach ($categories as $category) {
    var_dump($category);
    die;
}