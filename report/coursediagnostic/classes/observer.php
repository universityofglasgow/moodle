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
 * Class to handle course viewed/updated events.
 *
 * Retrieve cache data, or create a new cache instance if none exists,
 * or it has since expired, before passing the data to the suite of
 * tests, and then finally adding a link to the report on the page.
 *
 * @package    report_coursediagnositc
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die();
class observer {

    /** @var string Our key in the cache. */
    const CACHE_KEY = 'courseid:';

    /**
     * Handle the course created event.
     *
     * @param \core\event\course_created $event
     * @return bool
     */
    public static function course_created(\core\event\course_created $event): bool {

        // Invalidate the cache for the given course id - if it exists...
        if ((!empty($event->courseid)) && $event->courseid != 1) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * Handle the course viewed event.
     *
     * @param \core\event\course_viewed $event
     * @return bool
     */
    public static function course_viewed(\core\event\course_viewed $event) {

        $context = \context_course::instance($event->courseid);

        if (has_capability('report/coursediagnostic:view', $context)) {
            // ...courseid:1 appears to be the generic default course in Moodle - I don't think we need this.
            if ((!empty($event->courseid)) && $event->courseid != 1) {
                $settingscheck = \report_coursediagnostic\coursediagnostic::cfg_settings_check();

                if ($settingscheck) {

                    // Check that one or more tests have been enabled...
                    $diagnosticsettingscount = \report_coursediagnostic\coursediagnostic::get_settingscount();

                    if ($diagnosticsettingscount == 0) {
                        global $CFG;
                        $supportemail = $CFG->supportemail;
                        $link = \html_writer::link("mailto:{$supportemail}",
                            get_string('system_administrator', 'report_coursediagnostic'));
                        $phrase = get_string('no_tests_enabled', 'report_coursediagnostic', $link);
                        if (has_capability('moodle/site:config', \context_system::instance())) {
                            $url = new \moodle_url('/admin/settings.php', ['section' => 'course_diagnostic_settings']);
                            $link = \html_writer::link($url, get_string('admin_link_text', 'report_coursediagnostic'));
                            $phrase = get_string('no_tests_enabled_admin', 'report_coursediagnostic', $link);
                        }

                        return \report_coursediagnostic\notification::info($phrase);
                    }

                    \report_coursediagnostic\coursediagnostic::init_cache();
                    $cachedata = \report_coursediagnostic\coursediagnostic::cache_data_exists($event->courseid);

                    if ($cachedata[self::CACHE_KEY . $event->courseid]) {
                        $failedtests = \report_coursediagnostic\coursediagnostic::parse_results(
                            $cachedata[self::CACHE_KEY . $event->courseid]);
                    } else {
                        // Begin by creating the list of tests we need to perform...
                        $testsuite = \report_coursediagnostic\coursediagnostic::prepare_tests();

                        $diagnosticdata = \report_coursediagnostic\coursediagnostic::run_tests($testsuite, $event->courseid);
                        $failedtests = \report_coursediagnostic\coursediagnostic::fetch_test_results($event->courseid);
                        \report_coursediagnostic\coursediagnostic::prepare_cache($diagnosticdata, $event->courseid);
                    }

                    // Now hide/show the alert on the page that links to the report.
                    if ($failedtests > 0) {
                        \report_coursediagnostic\coursediagnostic::diagnostic_notification($failedtests, $event->courseid);

                        return true;
                    }

                    return false;
                }

                return false;
            }
        }

        return false;
    }

    /**
     * Handle the course updated event.
     *
     * @param \core\event\course_updated $event
     * @return bool
     */
    public static function course_updated(\core\event\course_updated $event): bool {

        // Invalidate the cache for the given course id - if it exists.
        if ((!empty($event->courseid)) && $event->courseid != 1) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * Handle the course deleted event.
     *
     * @param \core\event\course_deleted $event
     * @return bool
     */
    public static function course_deleted(\core\event\course_deleted $event): bool {

        // Invalidate the cache for the given course id - if it exists.
        if ((!empty($event->courseid)) && $event->courseid != 1) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * When the first student is added to a course, handle the session vars.
     * The config session variable needs to be removed in order to be re-gen'd.
     *
     * @param \core\event\user_enrolment_created $event
     * @return bool
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event): bool {

        global $PAGE;
        $studentyusers = count_role_users(5, $PAGE->context);

        if (!empty($event->relateduserid) && $studentyusers == 0) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * When the last student is removed from a course, handle the session vars.
     * The config session variable needs to be removed in order to be re-gen'd.
     *
     * @param \core\event\user_enrolment_deleted $event
     * @return bool
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event): bool {

        global $PAGE;
        $studentyusers = count_role_users(5, $PAGE->context);

        if (!empty($event->relateduserid) && $studentyusers == 0) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }


    /**
     * When a role is assigned to a user, handle the session variables.
     * The config session variable needs to be removed in order to be re-gen'd.
     * @param \core\event\role_assigned $event
     * @return bool
     */
    public static function role_assigned(\core\event\role_assigned $event): bool {
        if (!empty($event->relateduserid)) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * When a role is unassigned from a user, handle the session variables.
     * The config session variable needs to be removed in order to be re-gen'd.
     * @param \core\event\role_unassigned $event
     * @return bool
     */
    public static function role_unassigned(\core\event\role_unassigned $event): bool {
        if (!empty($event->relateduserid)) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * When a new enrolment method is added, we need to cater for it here.
     * The config session variable needs to be removed in order to be re-gen'd.
     *
     * @param enrol_instance_created $event
     * @return bool
     */
    public static function enrol_instance_created(\core\event\enrol_instance_created $event): bool {

        if ((!empty($event->courseid)) && $event->courseid != 1) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * When a new enrolment method is updated, we need to cater for it here.
     * The config session variable needs to be removed in order to be re-gen'd.
     *
     * @param enrol_instance_updated $event
     * @return bool
     */
    public static function enrol_instance_updated(\core\event\enrol_instance_updated $event): bool {

        if ((!empty($event->courseid)) && $event->courseid != 1) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * When a new enrolment method is deleted, we need to cater for it here.
     * The config session variable needs to be removed in order to be re-gen'd.
     *
     * @param enrol_instance_deleted $event
     * @return bool
     */
    public static function enrol_instance_deleted(\core\event\enrol_instance_deleted $event): bool {

        if ((!empty($event->courseid)) && $event->courseid != 1) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * We're looking specifically for when a course assignment is created.
     * @param \core\event\course_module_created $event
     * @return bool
     */
    public static function course_module_created(\core\event\course_module_created $event): bool {

        if ((!empty($event->courseid)) && $event->courseid != 1) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * We're looking specifically for when a course assignment is updated.
     * @param \core\event\course_module_updated $event
     * @return bool
     */
    public static function course_module_updated(\core\event\course_module_updated $event): bool {

        if ((!empty($event->courseid)) && $event->courseid != 1) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * We're looking specifically for when a course assignment is deleted.
     * @param \core\event\course_module_deleted $event
     * @return bool
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event): bool {

        if ((!empty($event->courseid)) && $event->courseid != 1) {
            return self::delete_key_from_cache($event->courseid);
        }

        return false;
    }

    /**
     * Utility method to save violating DRY rules.
     * @param $courseid
     * @return bool
     */
    public static function delete_key_from_cache($courseid): bool {

        $cache = \cache::make('report_coursediagnostic', 'coursediagnosticdata');
        $cachekey = self::CACHE_KEY . $courseid;
        $cachedata = $cache->get_many([$cachekey]);

        if ($cachedata[$cachekey] != false) {
            $cache->delete($cachekey);

            global $SESSION;
            unset($SESSION->report_coursediagnostic);
            unset($SESSION->report_coursediagnosticconfig);
            return true;
        }

        return false;
    }
}
