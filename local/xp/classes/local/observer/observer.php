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
 * Observer.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\observer;

use block_xp\di;
use local_xp\local\xp\level_with_badge_award;

/**
 * Observer.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Act when a course is deleted.
     *
     * @param  \core\event\course_deleted $event The event.
     * @return void
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        $courseid = $event->objectid;
        $contextid = $event->contextid;

        // Clean up the data that could be left behind.
        $conditions = ['courseid' => $courseid];
        $DB->delete_records('local_xp_config', $conditions);
        $DB->delete_records('local_xp_drops', $conditions);
        $DB->delete_records('local_xp_log', ['contextid' => $contextid]);

        // Delete the files.
        $fs = get_file_storage();
        $fs->delete_area_files($event->contextid, 'local_xp', 'currency');
    }

    /**
     * Act when a user leveled up.
     *
     * @param \block_xp\event\user_leveledup $event The event.
     */
    public static function user_leveledup(\block_xp\event\user_leveledup $event) {
        $world = di::get('course_world_factory')->get_world($event->courseid);
        $levelsinfo = $world->get_levels_info();

        $levelnum = $event->other['level'] ?? 0;
        if ($levelnum < 2 || $levelnum > $levelsinfo->get_count()) {
            return;
        }

        $level = $levelsinfo->get_level($levelnum);
        if (!($level instanceof level_with_badge_award) || !$level->get_badge_award_id()) {
            return;
        }

        $badgemanager = di::get('badge_manager');
        $badgemanager->award_badge($event->relateduserid, $level->get_badge_award_id(), $level->get_badge_award_issuer_id());
    }
}
