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
 * Class for notification collection management.
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_template\local;

use \core\output\notification;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for notification collection management.
 *
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class notifications {

    /**
     * A notification of level 'success'.
     */
    public const SUCCESS = 'success';

    /**
     * A notification of level 'warning'.
     */
    public const WARNING = 'warning';

    /**
     * A notification of level 'info'.
     */
    public const INFO = 'info';

    /**
     * A notification of level 'error'.
     */
    public const ERROR = 'error';

    /**
     * @var array of core\output\notification notifications
     */
    private $notifications = [];

    /**
     *
     * Construct a new notifications collection
     *
     * @param string|array $notification the string to add, or array of notification objects to add. Optional.
     * @param string|null $messagetype one of the notification constants [ERROR, WARNING, INFO, SUCCESS]
     * @return void
    */
    public function __construct($notification = null, string $messagetype = null) {
        $this->notifications = [];
        if (!empty($notification)) {
            $this->add($notification, $messagetype);
        }
    }

    /**
     *
     * Returns string representation of notifications collection.
     *
     * @return string string representation of notifications collection.
     */
    public function __toString(){
        $notifications = '';
        foreach ($this->notifications as $notification) {
            $notifications .= $notification->get_message() . PHP_EOL;
        }
        return $notifications;
    }

    /**
     * Returns presence of error notifications in collection.
     *
     * @return bool true if collection contains errors, otherwise false.
     */
    public function has_errors() {
        foreach ($this->notifications as $notification) {
            if ($notification->get_message_type() == self::ERROR) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns string representation of max error severity.
     *
     * @return string string representation of [INFO, SUCCESS, WARNING, DANGER]
     */
    public function get_error_level() {
        $maxerrorlevel = 0;
        foreach ($this->notifications as $notification) {
            switch ($notification->get_message_type()) {
                case self::INFO:
                    $errorlevel = 1;
                    break;
                case self::SUCCESS:
                    $errorlevel = 2;
                    break;
                case self::WARNING:
                    $errorlevel = 3;
                    break;
                case self::ERROR:
                    $errorlevel = 4;
                    break;
            }

            // echo 'errorlevel: ' . $errorlevel . '<br>';
            if ($errorlevel > $maxerrorlevel) {
                $maxerrorlevel = $errorlevel;
            }
        }
        // echo 'maxerrorlevel: ' . $maxerrorlevel . '<br>';
        switch ($maxerrorlevel) {
            case 1:
                return self::INFO;
            case 2:
                return self::SUCCESS;
            case 3:
                return self::WARNING;
            case 4:
                return self::ERROR;
            default:
                return '';
        }
    }

    /**
     *
     * Add a notification string with severity to collection, or add a collection of notifications to the collection.
     *
     * @param string|notifications $notification the string to add, or array of notification objects to add
     * @param string|null $messagetype one of the notification constants [ERROR, WARNING, INFO, SUCCESS]
     * @return void
     */
    public function add($notification, string $messagetype = null) {
        if (is_a($notification, 'local_template\\local\\notifications')) {
            $notifications = $notification->notifications;
            foreach ($notifications as $notification) {
                $this->notifications[] = new \core\output\notification($notification->get_message(), $notification->get_message_type());
            }
        } else {
            $this->notifications[] = new \core\output\notification($notification, $messagetype);
        }
    }

    /**
     *
     * Output notifications to the current renderer.
     *
     * @param bool $collate whether to collate the notifications into their severity levels.
     * @return void
     */
    public function output(bool $collate = false, bool $truncate = true) {
        if ($collate) {
            $notifications = $this->collate();
        } else {
            $notifications = $this->notifications;
        }

        foreach ($notifications as $notification) {

            $message = $notification->get_message();
            if ($truncate && mb_strlen($message) > 1024) {
                $message = mb_substr($message, 0, 1024);
            }

            \core\notification::add($notification->get_message(), $notification->get_message_type());
        }
    }

    /**
     *
     * Return reorganised notifications collection with concatenated notification strings of the sames severity.
     * Does not change internal representation.
     *
     * @return array
     */
    public function collate() {

        $errormessages = '';
        $warningmessages = '';
        $infomessages = '';
        $successmessages = '';

        // Rollup all notifications into their severity.
        foreach ($this->notifications as $notification) {
            switch ($notification->get_message_type()) {
                case self::ERROR:
                    $errormessages .= $notification->get_message() . '<br>';
                    break;
                case self::WARNING:
                    $warningmessages .= $notification->get_message() . '<br>';
                    break;
                case self::INFO:
                    $infomessages .= $notification->get_message() . '<br>';
                    break;
                case self::SUCCESS:
                    $successmessages .= $notification->get_message() . '<br>';
                    break;
                default:
                    $errormessages .= $notification->get_message() . '<br>';
                    break;
            }
        }

        $notifications = [];
        if (!empty($errormessages)) {
            $notifications[] = new notification($errormessages, notification::NOTIFY_ERROR);
        }
        if (!empty($warningmessages)) {
            $notifications[] = new notification($warningmessages, notification::NOTIFY_WARNING);
        }
        if (!empty($infomessages)) {
            $notifications[] = new notification($infomessages, notification::NOTIFY_INFO);
        }
        if (!empty($successmessages)) {
            $notifications[] = new notification($successmessages, notification::NOTIFY_SUCCESS);
        }

        return $notifications;
    }

}