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
            9 => get_string('desirable', 'report_enhance'),
        );
        
        $this->statusicons = array(
            1 => 'star-o',
            2 => 'clock-o',
            3 => 'gavel',
            4 => 'info-circle',
            5 => 'thumbs-o-up',
            6 => 'gear',
            7 => 'check-circle',
            8 => 'thumbs-o-down',
            9 => 'heart',
        );
        
        $this->statusclass = array(
            1 => 'info',
            2 => 'good',
            3 => 'wait',
            4 => 'info',
            5 => 'good',
            6 => 'wait',
            7 => 'good',
            8 => 'fail',
            9 => 'wait',
        );
    }

    public function getStatuses() {
        return $this->statuses;
    }

    public function getStatus($id) {
        return $this->statuses[$id];
    }
    
    public function getStatusIcon($id) {
        return $this->statusicons[$id];
    }
    
    public function getStatusColour($id) {
        return $this->statusclass[$id];
    }


}
