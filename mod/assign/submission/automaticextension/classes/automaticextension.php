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
 * This file contains the automatic extension class.
 *
 * @package    assignsubmission_automaticextension
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_automaticextension;

use assign;

/**
 * The automatic extension class.
 *
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class automaticextension {

    /**
     * The assign object.
     *
     * @var object $assign.
     */
    private $assign = null;

    /**
     * The assign due date.
     *
     * @var integer $duedate.
     */
    private $duedate = 0;

    /**
     * The extension due date.
     *
     * @var integer $extensionduedate.
     */
    private $extensionduedate = 0;

    /**
     * The user id.
     *
     * @var integer $userid.
     */
    private $userid = null;

    /**
     * The maximum number of requests that can be made.
     *
     * @var integer $maximumrequests.
     */
    private $maximumrequests = 0;

    /**
     * The automatic extension length in seconds.
     *
     * @var integer $extensionlength.
     */
    private $extensionlength = 0;

    /**
     * The maximum automatic extension length in seconds.
     *
     * @var integer $maximumextensionlength.
     */
    private $maximumextensionlength = 0;

    /**
     * Class constructor.
     *
     * @param assign $assign the assign object
     * @param integer $userid the user id
     */
    public function __construct(assign $assign, $userid) {
        $this->assign = $assign;
        $this->userid = $userid;
        $this->duedate = $assign->get_instance()->duedate;
        $flags = $assign->get_user_flags($userid, false);
        if ($flags) {
            $this->extensionduedate = $flags->extensionduedate;
        }
        $config = get_config('assignsubmission_automaticextension');
        if ($config) {
            $this->maximumrequests        = $config->maximumrequests;
            $this->extensionlength        = $config->extensionlength;
            $this->maximumextensionlength = $this->maximumrequests * $this->extensionlength;
        }
    }

    /**
     * Apply the automatic extension.
     *
     * @return boolean if extension was applied successfully
     */
    public function apply_extension() {
        $maximumextensionduedate = $this->duedate + $this->maximumextensionlength;
        if ($this->extensionduedate >= $maximumextensionduedate) {
            // Somehow we're trying to apply an extension when the current extension is past
            // or the same as the current extension, so let's just return false to be safe.
            return false;
        }

        // Calculate the new extensionduedate.
        $extensionduedate = $this->duedate + $this->extensionlength;
        if ($this->extensionduedate > 0) {
            $extensionduedate = $this->extensionduedate + $this->extensionlength;
        }

        // Apply the new extensionduedate.
        $flags = $this->assign->get_user_flags($this->userid, true);
        $flags->extensionduedate = $extensionduedate;
        if (!$this->assign->update_user_flags($flags)) {
            return false;
        }
        $this->extensionduedate = $extensionduedate;

        // Trigger the events.
        \mod_assign\event\extension_granted::create_from_assign($this->assign, $this->userid)->trigger();

        $eventdata = [
            'context' => $this->assign->get_context(),
            'objectid' => $this->assign->get_instance()->id,
            'other' => [
                'extensionduedate' => $this->extensionduedate,
            ],
        ];
        event\automatic_extension_applied::create($eventdata)->trigger();

        return true;
    }

    /**
     * Check if the user is able to request an extension.
     *
     * @return boolean
     */
    public function can_request_extension() {
        // Check for capability.
        $cap = 'assignsubmission/automaticextension:requestextension';
        if (!has_capability($cap, $this->assign->get_context(), $this->userid)) {
            return false;
        }

        // Student can't request an extension if they can't view or edit a submission.
        $canview = $this->assign->can_view_submission($this->userid);
        $canedit = $this->assign->submissions_open($this->userid) && $this->assign->is_any_submission_plugin_enabled();
        if (!$canview || !$canedit) {
            return false;
        }

        // Check config is set.
        if ($this->maximumrequests > 0 && $this->extensionlength > 0) {
            $now = time();
            $withinduedate = max($this->duedate, $this->extensionduedate) > $now;
            $withinmaximumrequests = ($this->duedate + $this->maximumextensionlength) > $this->extensionduedate;
            if ($withinduedate && $withinmaximumrequests) {
                // We are within the due date (either regular or extension) and haven't reached the maximum requests.
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the extension due date in human readable format.
     *
     * @return string
     */
    public function get_user_extension_due_date() {
        return userdate($this->extensionduedate, get_string('strftimedaydatetime', 'langconfig'));
    }
}
