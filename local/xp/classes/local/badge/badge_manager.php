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
 * Badge manager.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\badge;

use core_user;

/**
 * Badge manager.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class badge_manager extends \block_xp\local\badge\badge_manager {

    /**
     * Award a badge.
     *
     * @param int $userid The user ID.
     * @param int $badgeid The badge ID.
     * @param int $issuerid The issuer ID.
     */
    public function award_badge($userid, $badgeid, $issuerid) {
        global $CFG;

        if (empty($CFG->enablebadges)) {
            return;
        }

        try {
            if (class_exists('core_badges\badge')) {
                $badge = new \core_badges\badge($badgeid);
            } else if (class_exists('badge')) {
                $badge = new \badge($badgeid);
            } else {
                return;
            }
        } catch (\moodle_exception $e) {
            // The badge probably no longer exists.
            return;
        }

        // The user must be allowed to receive a badge.
        if (!has_capability('moodle/badges:earnbadge', $badge->get_context(), $userid)) {
            return;
        }

        if (!$badge->is_active()) {
            return;
        } else if (!$badge->has_manual_award_criteria()) {
            return;
        }

        $crit = $badge->criteria[BADGE_CRITERIA_TYPE_MANUAL];
        $roleids = array_keys($crit->params);
        if (empty($roleids)) {
            return;
        }

        // When only one role is required, we just keep one.
        if ($crit->method != BADGE_CRITERIA_AGGREGATION_ALL) {
            $roleids = [$roleids[0]];
        }

        // Validate the issuer ID.
        if (!core_user::is_real_user($issuerid, true)) {
            $issuerid = get_admin() ? get_admin()->id : null;
            if (!$issuerid) {
                return;
            }
        }

        // Award the badge by each role.
        foreach ($roleids as $roleid) {
            process_manual_award($userid, $issuerid, $roleid, $badgeid);
        }

        // Finally, trigger an update.
        $data = new \stdClass();
        $data->crit = $crit;
        $data->userid = $userid;
        badges_award_handle_manual_criteria_review($data);
    }

    /**
     * Get the compatible badges.
     *
     * @param \context $context The context.
     * @param int $userid The user ID.
     * @return object[] Contains id, name and type.
     */
    public function get_compatible_badges(\context $context, $userid) {
        $cachekey = $context->id . '-' . $userid;
        if (!isset($badgescache[$cachekey])) {
            $badgescache[$cachekey] = $this->fetch_compatible_badges($context, $userid);
        }
        return $badgescache[$cachekey];
    }

    /**
     * Fetch the compatible badges.
     *
     * @param \context $context The context.
     * @param int $userid The user ID.
     * @return array Indexed by badge ID.
     */
    protected function fetch_compatible_badges(\context $context, $userid) {
        global $CFG;

        if (empty($CFG->enablebadges)) {
            return [];
        }

        $canincourse = false;
        $coursecontext = $context->get_course_context(false);
        if ($coursecontext) {
            $canincourse = has_all_capabilities(['moodle/badges:viewbadges', 'moodle/badges:awardbadge'],
                $coursecontext, $userid);
        }
        $caninsystem = has_all_capabilities(['moodle/badges:viewbadges', 'moodle/badges:awardbadge'],
            \context_system::instance(), $userid);

        if (!$canincourse && !$caninsystem) {
            return [];
        }

        $params = [];
        $fragsql = [];
        if ($canincourse) {
            $fragsql[] = '(b.type = :typecourse AND b.courseid = :courseid)';
            $params['typecourse'] = BADGE_TYPE_COURSE;
            $params['courseid'] = $coursecontext ? $coursecontext->instanceid : 0;
        }
        if ($caninsystem) {
            $fragsql[] = '(b.type = :typesite)';
            $params['typesite'] = BADGE_TYPE_SITE;
        }

        $cansql = implode(' OR ', $fragsql);
        $sql = "
            SELECT b.id, b.name, b.type
              FROM {badge} b
              JOIN {badge_criteria} ba
                ON ba.badgeid = b.id
               AND ba.criteriatype = :criteriatype
             WHERE b.status IN (:active, :activelocked)
               AND ($cansql)
          ORDER BY b.name ASC";

        $params += [
            'active' => BADGE_STATUS_ACTIVE,
            'activelocked' => BADGE_STATUS_ACTIVE_LOCKED,
            'criteriatype' => BADGE_CRITERIA_TYPE_MANUAL,
        ];

        return array_map(function($badge) {
            $badge->id = (int) $badge->id;
            $badge->type = (int) $badge->type;
            return $badge;
        }, $this->db->get_records_sql($sql, $params));
    }

}
