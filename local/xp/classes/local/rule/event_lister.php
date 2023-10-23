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
 * Event lister.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

/**
 * Event lister class.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_lister extends \block_xp\local\rule\event_lister {

    /**
     * Get the core events.
     *
     * @return array The keys are translated subsystem names, the values are the classes.
     */
    protected function get_core_events() {
        global $CFG;
        $events = parent::get_core_events();
        if ($this->forwholesite) {
            if (!empty($CFG->enableblogs)) {
                $blog = get_string('blog', 'core_blog');
                if (!array_key_exists($blog, $events)) {
                    $events[$blog] = [];
                }
                $events[$blog] = array_unique(array_merge($events[$blog], [
                    '\\core\\event\\blog_comment_created',
                    '\\core\\event\\blog_entries_viewed',
                    '\\core\\event\\blog_entry_created',
                    '\\core\\event\\blog_entry_updated'
                ]));
            }

            if (class_exists('core_competency\\api') && \core_competency\api::is_enabled()) {
                $competencies = get_string('competencies', 'core_competency');
                if (!array_key_exists($competencies, $events)) {
                    $events[$competencies] = [];
                }
                $events[$competencies] = array_unique(array_merge($events[$competencies], [
                    '\\core\\event\\competency_viewed',
                    '\\core\\event\\competency_plan_created',
                    '\\core\\event\\competency_plan_updated',
                    '\\core\\event\\competency_plan_viewed',
                    '\\core\\event\\competency_plan_review_requested',
                    '\\core\\event\\competency_user_evidence_created',
                    '\\core\\event\\competency_user_evidence_updated',

                ]));
            }

            if (!empty($CFG->messaging)) {
                $messages = get_string('messages', 'core_message');
                if (!array_key_exists($messages, $events)) {
                    $events[$messages] = [];
                }
                $events[$messages] = array_unique(array_merge($events[$messages], [
                    '\\core\\event\\message_sent',
                    '\\core\\event\\message_viewed',
                    '\\core\\event\\group_message_sent',
                    '\\core\\event\\message_contact_added',
                ]));
            }

            $notifications = get_string('notifications', 'core_message');
            if (!array_key_exists($notifications, $events)) {
                $events[$notifications] = [];
            }
            $events[$notifications] = array_unique(array_merge($events[$notifications], [
                '\\core\\event\\notification_viewed'
            ]));

            $user = get_string('user', 'core');
            if (!array_key_exists($user, $events)) {
                $events[$user] = [];
            }
            $events[$user] = array_unique(array_merge($events[$user], [
                '\\core\\event\\user_loggedin',
                '\\core\\event\\user_profile_viewed',
            ]));
        }

        return $events;
    }

}
