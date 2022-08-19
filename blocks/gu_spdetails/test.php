<?php

require_once('../../config.php');
require_once('lib.php');

require_login();

$assessments = assessments_details::retrieve_gradable_activities('current', 636, 'coursetitle', 'asc', null);

$grades = [];
foreach ($assessments as $assessment) {
    $grade = new stdClass();
    $grade->coursetitle = $assessment->coursetitle;
    $grade->courseurl = $assessment->courseurl->out();
    $grade->assessmentname = $assessment->assessmentname;
    $grade->assessmenttype = $assessment->assessmenttype;
    $grade->assessmenturl = $assessment->assessmenturl->out();

    $grades[] = $grade;
}



echo "<pre>"; var_dump($grades); var_dump($assessments);
