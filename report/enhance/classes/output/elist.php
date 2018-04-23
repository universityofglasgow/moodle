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
 * Main class for course listing
 *
 * @package    report_enhance
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_enhance\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;

/**
 * Class contains data for report_enhance elist
 *
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class elist implements renderable, templatable {

    private $course;

    private $requests;

    private $statuses;

    /**
     * Constructor
     */
    public function __construct($course, $requests, $statuses) {
        $this->course = $course;
        $this->requests = $this->format_requests($requests);
        $this->statuses = $statuses;
    }

    private function format_requests($requests) {
        global $DB;

        $status = new \report_enhance\status();

        foreach ($requests as $request) {
            $user = $DB->get_record('user', array('id' => $request->userid), '*', MUST_EXIST);
            $request->username = fullname($user);
            $request->userdate = userdate($request->timecreated);
            $request->statusformatted = $status->getStatus($request->status);
            $request->link = new \moodle_url('/report/enhance/edit.php', array('courseid' => $this->course->id, 'id' => $request->id));
            $request->more = new \moodle_url('/report/enhance/more.php', array('courseid' => $this->course->id, 'id' => $request->id));
            $request->review = new \moodle_url('/report/enhance/review.php', array('courseid' => $this->course->id, 'id' => $request->id));
            $request->allowedit = $request->status == 1;
        }

        return $requests;
    }

    /** 
     * Export data for list of enhancements
     */
    public function export_for_template(renderer_base $output) {

        return [
            'formurl' => new \moodle_url('/report/enhance/edit.php', array('courseid' => $this->course->id)),
            'requests' => array_values($this->requests),
            'status' => $output->single_select('', 'filterstatus', $this->statuses, '', array('' => 'choosedots'), null, ['class' => 'form-control']),
        ];
    }

}

