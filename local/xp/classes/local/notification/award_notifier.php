<?php
// This file is part of a 3rd party created module for Moodle - http://moodle.org/
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
 * Award notifier.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\notification;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core_user;
use block_xp\local\config\config;
use block_xp\local\course_world;

/**
 * Award notifier.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class award_notifier {

    /** @var stdClass The user to send from. */
    protected $awardedby;
    /** @var config The admin config. */
    protected $config;
    /** @var int The course ID. */
    protected $world;

    /**
     * Constructor.
     *
     * @param config $config The admin config.
     * @param course_world $world The course world.
     * @param object $awardedby The user awarding the points.
     */
    public function __construct(config $config, course_world $world, $awardedby) {
        $this->config = $config;
        $this->awardedby = $awardedby;
        $this->world = $world;
    }

    /**
     * Send the notification.
     *
     * @param int $userid The user to send to.
     * @param int $points The points
     * @param string|null $message An optional additional message.
     * @return void
     */
    public function notify($userid, $points, $message = null) {
        $issite = $this->config->get('context') == CONTEXT_SYSTEM;

        $messagekey = !$issite ? 'manualawardnotificationwithcourse' : 'manualawardnotification';
        $smallmessage = get_string($messagekey, 'local_xp', [
            'coursename' => $this->world->get_context()->get_context_name(false),
            'fullname' => fullname($this->awardedby),
            'points' => $points
        ]);
        $fullmessage = $smallmessage;
        if (!empty($message)) {
            $fullmessage .= " " . get_string('theyleftthefollowingmessage', 'local_xp') . "\n\n" . $message;
        }

        // We do not define 'contexturl' because the default UI behaviour when clicking on the notification
        // is to send the user there, which means that may miss the full message of the notification. Not
        // including the 'contexturl' means that clicking the notification leads to a page to view it in full.
        $message = new \core\message\message();
        $message->component = 'local_xp';
        $message->name = 'manualaward';
        $message->userfrom = core_user::get_noreply_user();
        $message->userto = $userid;
        $message->subject = get_string('manualawardsubject', 'local_xp', ['points' => $points]);
        $message->fullmessage = $fullmessage;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = markdown_to_html($fullmessage);
        $message->smallmessage = $smallmessage;
        $message->notification = 1;

        try {
            $message->courseid = $this->world->get_courseid();
        } catch (coding_exception $e) {
            // The property courseid did not exist in older versions.
        }

        message_send($message);
    }

}
