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
use context;
use context_course;

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
        global $DB, $USER;

        $status = new \report_enhance\status();

        $context = context_course::instance($this->course->id);

        foreach ($requests as $request) {
            $user = $DB->get_record('user', array('id' => $request->userid), '*', MUST_EXIST);
            $request->username = fullname($user);
            $request->userdate = userdate($request->timecreated);
            $request->timeago = $this->ago($request->timecreated);
            $request->statusformatted = $status->getStatus($request->status);
            $request->statusicon = $status->getStatusIcon($request->status);
            $request->statuscolour = $status->getStatusColour($request->status);
            $request->link = new \moodle_url('/report/enhance/edit.php', array('courseid' => $this->course->id, 'id' => $request->id));
            $request->more = new \moodle_url('/report/enhance/more.php', array('courseid' => $this->course->id, 'id' => $request->id));
            $request->review = new \moodle_url('/report/enhance/review.php', array('courseid' => $this->course->id, 'id' => $request->id));
            $request->allowedit = has_capability('report/enhance:editall', $context) ||
                ($request->userid == $USER->id && ($request->status == ENHANCE_STATUS_NEW || $request->status == ENHANCE_STATUS_MOREINFORMATION));
            $request->allowreview = has_capability('report/enhance:review', $context);
            $request->cardclasses = \report_enhance\lib::cardclasses($request);
            list($request->votecount, $request->ownrequest, $request->voted) = \report_enhance\lib::getvotes($request);
        }

        return $requests;
    }

    /**
     * Export data for list of enhancements
     */
    public function export_for_template(renderer_base $output) {

        return [
            'course' => $this->course,
            'formurl' => new \moodle_url('/report/enhance/edit.php', ['courseid' => $this->course->id]),
            'moreurl' => new \moodle_url('/report/enhance/more.php', ['courseid' => $this->course->id]),
            'exporturl' => new \moodle_url('/report/enhance/index.php', ['courseid' => $this->course->id, 'export' => 1]),
            'requests' => array_values($this->requests),
            'status' => $output->single_select('', 'filterstatus', $this->statuses, '', array('' => 'choosedots'), null, ['class' => 'form-control']),
            'reviewer' => has_capability('report/enhance:review', context_course::instance($this->course->id)),
        ];
    }

    public static function ago($ptime) {
        $etime = time() - $ptime;

        if ($etime < 1) {
            return '0 seconds';
        }

        $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                    30 * 24 * 60 * 60       =>  'month',
                    7 * 24 * 60 * 60 		=>  'week',
                    24 * 60 * 60            =>  'day',
                    60 * 60                 =>  'hour',
                    60                      =>  'minute',
                    1                       =>  'second'
                    );

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . $str . ($r > 1 ? 's' : '');
            }
        }
    }
}
