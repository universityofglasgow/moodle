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
 * State store points for a user.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\provider;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use block_xp\local\reason\reason;

/**
 * State store points for a user.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_state_store_points {

    /** Action to increase points by the given amount. */
    const ACTION_INCREASE = 1;
    /** Action to set points to given amount. */
    const ACTION_SET = 0;

    /** @var object The user. */
    protected $user;
    /** @var int The points. */
    protected $points;
    /** @var int The action. */
    protected $action;
    /** @var string An arbitrary message. */
    protected $message;
    /** @var reason|null The reason. */
    protected $reason;

    /**
     * Constructor.
     *
     * @param object $user The user.
     * @param int $points The points.
     * @param int $action The action constant.
     * @param reason|null $reason The reason.
     * @param string|null $message A message.
     */
    public function __construct($user, $points, $action, reason $reason = null, $message = null) {
        $this->user = $user;
        $this->points = $points;
        $this->action = $action;
        $this->reason = $reason;
        $this->message = $message;

        if ($action !== self::ACTION_INCREASE && $action !== self::ACTION_SET) {
            throw new coding_exception('Invalid action');
        }
    }

    /**
     * Get the action.
     *
     * @return int
     */
    public function get_action() {
        return $this->action;
    }

    /**
     * Get the receiver ID.
     *
     * @return int
     */
    public function get_id() {
        return $this->user->id;
    }

    /**
     * Get the message.
     *
     * @return string
     */
    public function get_message() {
        return $this->message;
    }

    /**
     * Get the points.
     *
     * @return int
     */
    public function get_points() {
        return $this->points;
    }

    /**
     * Get the reason.
     *
     * @return reason|null
     */
    public function get_reason() {
        return $this->reason;
    }

    /**
     * Get the receiver user.
     *
     * @return object
     */
    public function get_user() {
        return $this->user;
    }
}
