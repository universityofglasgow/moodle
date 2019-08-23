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
 * Email class
 *
 * @package    report_enhance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_enhance;

defined('MOODLE_INTERNAL') || die();

class email {

    /**
     * Send message
     *
     * Message elements
     *  component string Component name. must exist in message_providers
     *  name string Message type name. must exist in message_providers
     *  userfrom object|int The user sending the message
     *  userto object|int The message recipient
     *  subject string The message subject
     *  fullmessage string The full message in a given format
     *  fullmessageformat int The format if the full message (FORMAT_MOODLE, FORMAT_HTML, ..)
     *  fullmessagehtml string The full version (the message processor will choose with one to use)
     *  smallmessage string The small version of the message
     *
     * @param array $elements of message elemements
     */
    protected static function send($elements) {
        $message = new \core\message\message();
        foreach ($elements as $name => $value) {
            $message->$name = $value;
        }
        message_send($message);
    }

    /**
     * Send notification when a new request is logged
     * @param object $requestor user logging request
     * @param object $request request db object
     */
    public static function newrequest($requestor, $request) {
        global $CFG, $DB;

        // Find users with report/enhance:emailnotifynew
        $context = \context_system::instance();
        $users = get_users_by_capability($context, 'report/enhance:emailnotifynew');

        // Get user details
        $user = $DB->get_record('user', ['id' => $request->userid], '*', MUST_EXIST);
        $request->username = fullname($user);

        // Link
        $link = new \moodle_url('/report/enhance/more.php', ['courseid' => 1, 'id' => $request->id]);
        $request->link = strval($link);

        foreach ($users as $user) {
            $elements = [
                'component' => 'report_enhance',
                'name' => 'newrequest',
                'userfrom' => 2,
                'userto' => $user,
                'subject' => get_string('notifynewsubject', 'report_enhance'),
                'fullmessage' => get_string('notifynewfull', 'report_enhance', $request),
                'fullmessageformat' => FORMAT_HTML,
                'fullmessagehtml' => get_string('notifynewfull', 'report_enhance', $request),
                'smallmessage' => get_string('notifynewsubject', 'report_enhance'),
            ];
            self::send($elements);
        }

    }

}
