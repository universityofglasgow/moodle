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
 * Sychronise completion data for CoreHR
 *
 * @package    local_corehr
 * @copyright  2018 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_corehr\task;

class send extends \core\task\scheduled_task {

    public function get_name() {
        // Shown in admin screens
        return get_string('send', 'local_corehr');
    }

    public function execute() {
        global $DB;

        // Get list of possible error codes
        $errors = \local_corehr\api::getErrors();

        // Find records that (might) need sent/resent
        $statii = $DB->get_records('local_corehr_status', ['status' => 'pending']);
        foreach ($statii as $status) {
            $delay = \local_corehr\api::get_delay($status->retrycount);
            $targettime = $status->lasttry + $delay;
            if ($targettime > time()) {
                continue;
            }

            // Attempt to send to Campus or CoreHR
            if ($status->coursecode == 'CAMPUS') {
                $config = get_config('local_corehr');
                $campus = new \local_corehr\campus($config->campusendpoint, $config->campususername, $config->campuspassword);
                $campus->send($status);
            } else {
                $message = \local_corehr\api::send($status);

                // Deal sensibly with message
                $message = trim($message);
                $status->lasttry = time();
                if ($message == 'OK') {
                    $status->status = 'OK';
                } else if (array_key_exists($message, $errors)) {
                    $permanent = $errors[$message];
                    $status->error = substr($message, 0, 49);
                    if ($permanent) {
                        $status->status = 'error';
                    } else {
                        $status->retrycount++;
                        \local_corehr\api::mtrace('local_corehr: Retry count for user ' . $status->userid . ' is now ' . $status->retrycount);
                        if ($status->retrycount > 12) {
                            $status->status = 'timeout';
                        }
                    }
                }
            }

            $DB->update_record('local_corehr_status', $status);
        }
    }
}
