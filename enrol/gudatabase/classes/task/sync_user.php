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
 * @package    enrol_gudatabase
 * @copyright  2021 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_gudatabase\task;

defined('MOODLE_INTERNAL') || die;

class sync_user extends \core\task\adhoc_task {

    public function execute() {
        global $DB;

        // Get enrolment plugin
        $plugin = enrol_get_plugin('gudatabase');

        // Get custom data (and courseid)
        $data = $this->get_custom_data();
        $userid = $data->userid;
        if ($user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0])) {
            mtrace('enrol_gudatabase: processing user ' . $user->username);
            $plugin->process_user_enrolments($user);
        } else {
            mtrace('enrol_gudatabase: warning, user no longer exists/active id=' . $userid);
        }
    }

}