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
 * Reason maker from event.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\reason;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\reason\reason;

/**
 * Reason maker from event.
 *
 * We can convert this to an interface later if there is a need for an interface.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class maker_from_event {

    /**
     * Make a reason from the event.
     *
     * @param \core\event\base $e The event.
     * @return event_reason
     */
    public function make_from_event(\core\event\base $e) {
        if ($e instanceof \core\event\course_module_completion_updated) {
            return activity_completion_reason::from_event($e);
        } else if ($e instanceof \core\event\course_completed) {
            return course_completed_reason::from_event($e);
        } else if ($e instanceof \core\event\user_graded) {
            return graded_reason::from_event($e);
        }
        return event_reason::from_event($e);
    }

}
