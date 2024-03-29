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
 * Target resolver from event.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\strategy;
defined('MOODLE_INTERNAL') || die();

/**
 * Target resolver from event.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_collection_target_resolver implements collection_target_resolver_from_event {

    /**
     * Get the target.
     *
     * @param core\event\base $event The event.
     * @return int
     */
    public function get_target_from_event(\core\event\base $event) {

        if ($event instanceof \core\event\user_graded) {
            return $event->relateduserid;
        }

        if ($event->edulevel !== \core\event\base::LEVEL_PARTICIPATING) {
            return null;
        }

        if ($event instanceof \core\event\course_completed) {
            return $event->relateduserid;
        } else if ($event instanceof \core\event\course_module_completion_updated) {
            return $event->relateduserid;
        } else if ($event instanceof \local_xp\event\section_completed) {
            return $event->relateduserid;
        }

        return $event->userid;
    }

}

