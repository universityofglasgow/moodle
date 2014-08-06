<?php

$observers = array(

    array(
        'eventname' => '\core\event\course_reset_ended',
        'callback' => 'enrol_gudatabase_observer::course_reset_ended', 
    ),

);
