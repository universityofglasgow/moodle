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
 * Class to handle assessment events.
 *
 * The cache needs to be cleared when certain assessment events occcur.
 * This is needed by the charts on the dashboard to pull in the correct
 * assessment summaries.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails;

/**
 * This class handles the subscriber events we need in order to maintain the cache.
 *
 * We are trying to prevent expensive database queries, thus these events allow us
 * flush the cache only at certain points.
 */
class observer {

    /** @var string Our key in the cache. */
    const CACHE_KEY = 'studentid_summary:';

    /**
     * Handle the core assessable submission event.
     * Not sure if this is deemed a catch all event?
     *
     * @param \core\event\assessable_submitted $event
     * @return bool
     */
    public static function core_assessable_submitted(\core\event\assessable_submitted $event): bool {

        // Invalidate the cache.
        if ((!empty($event->userid)) && $event->userid != 1) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:assignment submission created event.
     *
     * @param \mod_assign\event\submission_created $event
     * @return bool
     */
    public static function submission_created(\mod_assign\event\submission_created $event): bool {

        // Invalidate the cache.
        if ((!empty($event->userid)) && $event->userid != 1) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the assessable submitted event.
     *
     * @param \mod_assign\event\assessable_submitted $event
     * @return bool
     */
    public static function assessable_submitted(\mod_assign\event\assessable_submitted $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:assignment submission removed event.
     *
     * @param \mod_assign\event\submission_removed $event
     * @return bool
     */
    public static function submission_removed(\mod_assign\event\submission_removed $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:assignment extension granted event.
     *
     * @param \mod_assign\event\extension_granted $event
     * @return bool
     */
    public static function extension_granted(\mod_assign\event\extension_granted $event) {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:assignment identities revealed event.
     * I'm taking this to mean where grades have been released to students.
     *
     * @param \mod_assign\event\identities_revealed $event
     * @return bool
     */
    public static function identities_revealed(\mod_assign\event\identities_revealed $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:peerwork submission created event.
     *
     * @param \mod_peerwork\event\submission_created $event
     * @return bool
     */
    public static function peerwork_submission_created(\mod_peerwork\event\submission_created $event): bool {

        // Invalidate the cache.
        if ((!empty($event->userid)) && $event->userid != 1) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:peerwork assessable submitted event.
     *
     * @param \mod_peerwork\event\assessable_submitted $event
     * @return bool
     */
    public static function peerwork_assessable_submitted(\mod_peerwork\event\assessable_submitted $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:peerwork assessable updated event.
     *
     * @param \mod_peerwork\event\submission_updated $event
     * @return bool
     */
    public static function peerwork_submission_updated(\mod_peerwork\event\submission_updated $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:peerwork graded event.
     *
     * @param \mod_peerwork\event\submission_graded $event
     * @return bool
     */
    public static function peerwork_submission_graded(\mod_peerwork\event\submission_graded $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:peerwork grades released event.
     * I'm taking this to mean where grades have been released to students.
     *
     * @param \mod_peerwork\event\grades_released $event
     * @return bool
     */
    public static function peerwork_grades_released(\mod_peerwork\event\grades_released $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:quiz manual grading complete.
     *
     * @param \mod_quiz\event\attempt_manual_grading_completed $event
     * @return bool
     */
    public static function attempt_manual_grading_completed(\mod_quiz\event\attempt_manual_grading_completed $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:SCORM status submitted event.
     * @param \mod_scorm\event\status_submitted $event
     * @return bool
     */
    public static function scorm_status_submitted(\mod_scorm\event\status_submitted $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }
    }

    /**
     * Handle the activity type:workshop submission created event.
     *
     * @param \mod_workshop\event\submission_created $event
     * @return bool
     */
    public static function workshop_submission_created(\mod_workshop\event\submission_created $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:workshop submission updated event.
     *
     * @param \mod_workshop\event\submission_updated $event
     * @return bool
     */
    public static function workshop_submission_updated(\mod_workshop\event\submission_updated $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:workshop assessed event.
     *
     * @param \mod_workshop\event\submission_assessed $event
     * @return bool
     */
    public static function workshop_submission_assessed(\mod_workshop\event\submission_assessed $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle the activity type:workshop submission deleted event.
     *
     * @param \mod_workshop\event\submission_deleted $event
     * @return bool
     */
    public static function workshop_submission_deleted(\mod_workshop\event\submission_deleted $event): bool {

        // Invalidate the cache.
        if (!empty($event->userid)) {
            return self::delete_key_from_cache($event->userid);
        }

        return false;
    }

    /**
     * Handle a logout event.
     *
     * @param \core\event\user_loggedout $event
     *
     * The session data for which tab/category has been selected doesn't get
     * cleared as expected - because the page session remains active as long
     * as the tab/browser is open.
     * https://developer.mozilla.org/en-US/docs/Web/API/Window/sessionStorage
     */
    public static function user_loggedout(\core\event\user_loggedout $event): bool {
        return true;
    }

    /**
     * Utility method to save violating DRY rules.
     * @param int $userid
     * @return bool
     */
    public static function delete_key_from_cache(int $userid): bool {

        $cache = \cache::make('block_newgu_spdetails', 'studentdashboarddata');
        $cachekey = self::CACHE_KEY . $userid;
        $cachedata = $cache->get_many([$cachekey]);

        if ($cachedata[$cachekey] != false) {
            $cache->delete($cachekey);

            return true;
        }

        return false;
    }
}
