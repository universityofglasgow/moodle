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

    private $context;

    private $attachments;

    private $comments;

    /**
     * Constructor
     */
    public function __construct($course, $request, $context, $attachments, $comments) {
        $this->course = $course;
        $this->request = $this->format_request($request);
        $this->context = $context;
        $this->attachments = $attachments;
        $this->comments = $comments;
    }

    /** 
     * Format comments
     * @param array $comments
     * @return array
     */
    private function format_comments($comments) {
        global $DB, $USER, $output;

        foreach ($comments as $comment) {
            $user = $DB->get_record('user', ['id' => $comment->userid], '*', MUST_EXIST);
            $comment->avatar = $output->user_picture($user, ['size' => 42]);
            $comment->name = fullname($user);
            $comment->created = userdate($comment->timeadded);
            if ($comment->timeedited) {
                $comment->edited = '(' . get_string('editedon', 'report_enhance', userdate($comment->timeedited)) . ')';
            } else {
                $comment->edited = '';
            }
            $comment->canedit = has_capability('report/enhance:editallcomments', $this->context) || $USER->id == $comment->userid;
            $comment->editurl = new \moodle_url('/report/enhance/comment.php', ['courseid' => $this->course->id, 'id' => $this->request->id, 'commentid' => $comment->id]);
        }

        return array_values($comments);
    }

    private function format_request($request) {
        global $DB;

        $status = new \report_enhance\status;

        $user = $DB->get_record('user', array('id' => $request->userid), '*', MUST_EXIST);
        $request->user = $user;
        $request->username = fullname($user);
        $request->userdate = userdate($request->timecreated);
        $request->statusformatted = $status->getStatus($request->status);
        $request->statusicon = $status->getStatusIcon($request->status);
        $request->statuscolour = $status->getStatusColour($request->status);
        list($request->votes) = \report_enhance\lib::getvotes($request);

        return $request;
    }

    /**
     * Export data for list of enhancements
     */
    public function export_for_template(renderer_base $output) {
        global $USER;

        return [
            'request' => $this->request,
            'back' => new \moodle_url('/report/enhance/index.php', ['courseid' => $this->course->id]),
	        'editurl' => new \moodle_url('/report/enhance/edit.php', ['courseid' => $this->course->id, 'id' => $this->request->id]),
            'reviewurl' => new \moodle_url('/report/enhance/review.php', ['courseid' => $this->course->id, 'id' => $this->request->id]),
            'allowedit' => has_capability('report/enhance:editall', $this->context) ||
                ($this->request->userid == $USER->id && ($this->request->status == ENHANCE_STATUS_NEW || $this->request->status == ENHANCE_STATUS_MOREINFORMATION)),
            'allowreview' => has_capability('report/enhance:review', $this->context),
            'allowcomments' => has_capability('report/enhance:addcomment', $this->context),
            'commenturl' => new \moodle_url('/report/enhance/comment.php', ['courseid' => $this->course->id, 'id' => $this->request->id]),
            'comments' => $this->format_comments($this->comments),
            'hascomments' => !empty($this->comments),
            'attachments' => $this->attachments,
            'hasattachments' => !empty($this->attachments),
            'userpicture' => $output->user_picture($this->request->user, ['size' => 64, 'alttext' => false]),
            'userlink' => new \moodle_url('/user/view.php', ['id' => $this->request->userid]),
            'voteslink' => new \moodle_url('/report/enhance/voters.php', ['courseid' => $this->course->id, 'id' => $this->request->id]),
            'reviewer' => has_capability('report/enhance:review', \context_course::instance($this->course->id)),
            'priority' => \report_enhance\lib::getpriorities()[$this->request->priority],
        ];
    }

}

