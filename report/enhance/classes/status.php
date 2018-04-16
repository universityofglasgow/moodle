<?php

namespace report_enhance;

class status {

    private $statuses = array();

    public function __construct() {

        $this->statuses = array(
            1 => get_string('new', 'report_enhance'),
            2 => get_string('pendingreview', 'report_enhance'),
            3 => get_string('underreview', 'report_enhance'),
            4 => get_string('moreinformation', 'report_enhance'),
            5 => get_string('waitingdevelopment', 'report_enhance'),
            6 => get_string('developmentinprogress', 'report_enhance'),
            7 => get_string('complete', 'report_enhance'),
            8 => get_string('rejected', 'report_enhance'),
        );
    }

    public function getStatuses() {
        return $this->statuses;
    }

    public function getStatus($id) {
        return $this->statuses[$id];
    }

}
