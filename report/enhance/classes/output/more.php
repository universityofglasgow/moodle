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
 * Class contains data for report_enhance more
 *
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class more implements renderable, templatable {

    private $request;

    private $course;

    /**
     * Constructor
     */
    public function __construct($course, $request) {
        $this->course = $course;
        $this->request = $this->format_request($request);
    }

    private function format_request($request) {
        global $DB;

        $status = new \report_enhance\status;

        $user = $DB->get_record('user', array('id' => $request->userid), '*', MUST_EXIST);
        $request->username = fullname($user);
        $request->userdate = userdate($request->timecreated);
        $request->statusformatted = $status->getStatus($request->status);

        return $request;
    }

    /** 
     * Export data for list of enhancements
     */
    public function export_for_template(renderer_base $output) {

        return [
            'request' => $this->request,
            'back' => new \moodle_url('/report/enhance/index.php', ['courseid' => $this->course->id]),
        ];
    }

}

