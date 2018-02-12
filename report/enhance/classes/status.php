<?php

namespace report_enhance;

class status {

    private $statuses = array();

    public function __construct() {

        $this->statuses = array(
            1 => get_string('pending', 'report_enhance'),
            2 => get_string('approved', 'report_enhance'),
            3 => get_string('approvedindev', 'report_enhance'),
            4 => get_string('approvedreleased', 'report_enhance'),
            5 => get_string('proposed', 'report_enhance'),
            6 => get_string('rejected', 'report_enhance'),
        );
    }

    public function getStatuses() {
        return $this->statuses;
    }

    public function getStatus($id) {
        return $this->statuses[$id];
    }

}
