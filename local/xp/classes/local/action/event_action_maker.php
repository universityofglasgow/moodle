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
 * Maker.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\action;

use block_xp\local\action\maker_from_event;
use block_xp\local\action\static_action;

/**
 * Maker.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_action_maker implements maker_from_event {

    /**
     * Make actions from event.
     *
     * @param \core\event\base $event The event.
     * @return action[]
     */
    public function make_from_event(\core\event\base $event): iterable {
        $actions = [];
        $context = $event->get_context();

        // We cannot trust that the event gives us a context.
        if (!$context) {
            return $actions;
        }

        if ($event instanceof \core\event\course_module_completion_updated) {
            $data = $event->get_record_snapshot('course_modules_completion', $event->objectid);
            $state = $data->completionstate;
            if ($state == COMPLETION_COMPLETE || $state == COMPLETION_COMPLETE_PASS) {
                return [new static_action('cm_completed', $context, $event->relateduserid, $context->instanceid)];
            }

        } else if ($event instanceof \core\event\course_completed) {
            return [new static_action('course_completed', $context, $event->relateduserid, $event->courseid)];
        }

        return $actions;
    }

}
