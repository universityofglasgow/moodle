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
 * User Alert notifications.
 *
 * Borrowing \core\notification.php as for our purposes, it requires tweaks.
 * Ideally, we would simply have extended the original class and overridden
 * the add method to do what we needed (namely select a different element).
 * However, it doesn't appear possible to do that in Moodle, hence having to
 * reinvent the wheel :-(
 *
 * @package    report_coursediagnostic
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

use stdClass;

defined('MOODLE_INTERNAL') || die();
class notification {

    /**
     * A notification of level 'warning'.
     */
    const WARNING = 'warning';

    /**
     * A notification of level 'info'.
     */
    const INFO = 'info';

    /**
     * A notification of level 'error'.
     */
    const ERROR = 'error';

    /**
     * Overriding the add method as we want a particular element.
     *
     * @param $message
     * @param $level
     * @return void
     * @throws \coding_exception
     */
    public static function add($message, $level = null) {

        global $PAGE, $SESSION;

        if ($PAGE && ($PAGE->state === \moodle_page::STATE_IN_BODY
                || $PAGE->state === \moodle_page::STATE_DONE)) {
            // Currently in the page body - just render and exit immediately.
            // We insert some code to immediately insert this into the user-notifications created by the header.
            $id = uniqid();
            echo \html_writer::span(
                $PAGE->get_renderer('core')->render(new \core\output\notification($message, $level)),
                '', ['id' => $id]);

            // Insert this JS here using a script directly rather than waiting for the page footer to load to avoid
            // ensure that the message is added to the user-notifications section as soon as possible after it is created.
            echo \html_writer::script(
                "(function() {" .
                "var parentDiv = document.getElementById('page-header').parentNode;" .
                "if (!parentDiv) { return; }" .
                "var thisNotification = document.getElementById('{$id}');" .
                "var childElement = document.getElementById('page-header');" .
                "if (!thisNotification) { return; }" .
                "parentDiv.insertBefore(thisNotification, childElement);" .
                "})();"
            );
            return;
        }

        // Add the notification directly to the session.
        // This will either be fetched in the header, or by JS in the footer.
        if (!isset($SESSION->notifications) || ![$SESSION->notifications]) {
            // Initialise $SESSION if necessary.
            if (!is_object($SESSION)) {
                $SESSION = new stdClass();
            }
            $SESSION->notifications = [];
        }
        $SESSION->notifications[] = (object) [
            'message' => $message,
            'type' => $level,
        ];
    }

    /**
     * Add a info message to the notification stack.
     *
     * @param string $message The message to add to the stack
     */
    public static function info($message) {
        return self::add($message, self::INFO);
    }

    /**
     * Add a warning message to the notification stack.
     *
     * @param string $message The message to add to the stack
     */
    public static function warning($message) {
        return self::add($message, self::WARNING);
    }

    /**
     * Add an error message to the notification stack.
     *
     * @param string $message The message to add to the stack
     */
    public static function error($message) {
        return self::add($message, self::ERROR);
    }
}
