<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Status class
 *
 * @package    report_enhance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_enhance;

define('ENHANCE_STATUS_NEW', 1);
define('ENHANCE_STATUS_PENDINGREVIEW', 2);
define('ENHANCE_STATUS_UNDERREVIEW', 3);
define('ENHANCE_STATUS_MOREINFORMATION', 4);
define('ENHANCE_STATUS_WAITINGDEVELOPMENT', 5);
define('ENHANCE_STATUS_DEVELOPMENTINPROGRESS', 6);
define('ENHANCE_STATUS_COMPLETE', 7);
define('ENHANCE_STATUS_REJECTED', 8);
define('ENHANCE_STATUS_DESIRABLE', 9);

defined('MOODLE_INTERNAL') || die();

class status {

    private $statuses = array();

    public function __construct() {

        $this->statuses = array(
            ENHANCE_STATUS_NEW => get_string('new', 'report_enhance'),
            ENHANCE_STATUS_PENDINGREVIEW => get_string('pendingreview', 'report_enhance'),
            ENHANCE_STATUS_UNDERREVIEW => get_string('underreview', 'report_enhance'),
            ENHANCE_STATUS_MOREINFORMATION => get_string('moreinformation', 'report_enhance'),
            ENHANCE_STATUS_WAITINGDEVELOPMENT => get_string('waitingdevelopment', 'report_enhance'),
            ENHANCE_STATUS_DEVELOPMENTINPROGRESS => get_string('developmentinprogress', 'report_enhance'),
            ENHANCE_STATUS_COMPLETE => get_string('complete', 'report_enhance'),
            ENHANCE_STATUS_REJECTED => get_string('rejected', 'report_enhance'),
            ENHANCE_STATUS_DESIRABLE => get_string('desirable', 'report_enhance'),
        );
        
        $this->statusicons = array(
            ENHANCE_STATUS_NEW => 'star-o',
            ENHANCE_STATUS_PENDINGREVIEW => 'clock-o',
            ENHANCE_STATUS_UNDERREVIEW => 'gavel',
            ENHANCE_STATUS_MOREINFORMATION => 'info-circle',
            ENHANCE_STATUS_WAITINGDEVELOPMENT => 'thumbs-o-up',
            ENHANCE_STATUS_DEVELOPMENTINPROGRESS => 'gear',
            ENHANCE_STATUS_COMPLETE => 'check-circle',
            ENHANCE_STATUS_REJECTED => 'thumbs-o-down',
            ENHANCE_STATUS_DESIRABLE => 'heart',
        );
        
        $this->statusclass = array(
            ENHANCE_STATUS_NEW => 'info',
            ENHANCE_STATUS_PENDINGREVIEW => 'good',
            ENHANCE_STATUS_UNDERREVIEW => 'wait',
            ENHANCE_STATUS_MOREINFORMATION => 'info',
            ENHANCE_STATUS_WAITINGDEVELOPMENT => 'good',
            ENHANCE_STATUS_DEVELOPMENTINPROGRESS => 'wait',
            ENHANCE_STATUS_COMPLETE => 'good',
            ENHANCE_STATUS_REJECTED => 'fail',
            ENHANCE_STATUS_DESIRABLE => 'wait',
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
