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
 * UofG LDAP / login operations
 *
 * @package    local_guladp
 * @copyright  2022 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_guldap;

defined('MOODLE_INTERNAL') || die;

class observer {

    /**
     * Handle logged in event
     */
    public static function user_loggedin(\core\event\user_loggedin $event) {
        $config = get_config('local_guldap');
        if ($config->loginhook) {
            $userid = $event->userid;
            $user = \local_guldap\api::normalise_user($userid);
            \local_guldap\api::login_actions($user);
        }
    }
}