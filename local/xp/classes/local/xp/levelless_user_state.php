<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp\local\xp;

use block_xp\local\utils\user_utils;
use block_xp\local\xp\state_with_user;
use moodle_url;
use stdClass;

/**
 * Levelless user state.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levelless_user_state extends levelless_state implements state_with_user {

    /** @var int The course ID. */
    protected $courseid;
    /** @var stdClass The user object. */
    protected $user;

    /**
     * Constructor.
     *
     * @param stdClass $user The user object.
     * @param int $xp The user XP.
     * @param ?int $courseid The course ID.
     */
    public function __construct(stdClass $user, $xp, $courseid = null) {
        parent::__construct($xp, $user->id, '');
        $this->user = $user;
        $this->courseid = !empty($courseid) ? $courseid : SITEID;
    }

    public function get_link() {
        $userid = $this->user->id;
        $profileurl = new moodle_url('/user/profile.php', ['id' => $userid]);
        if ($this->courseid != SITEID) {
            $profileurl = new moodle_url('/user/view.php', ['id' => $userid, 'course' => $this->courseid]);
        }
        return $profileurl;
    }

    public function get_name() {
        return fullname($this->user);
    }

    public function get_picture() {
        return user_utils::user_picture($this->user);
    }

    public function get_user() {
        return $this->user;
    }

}
