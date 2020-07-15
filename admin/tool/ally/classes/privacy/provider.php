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
 * Privacy provider.
 *
 * @package   tool_ally
 * @copyright Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_ally\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider.
 *
 * @package   tool_ally
 * @copyright Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection) : collection {
        $collection->add_external_location_link('files', [
            'courseid'     => 'privacy:metadata:files:courseid',
            'action'       => 'privacy:metadata:files:action',
            'timemodified' => 'privacy:metadata:files:timemodified',
            'mimetype'     => 'privacy:metadata:files:mimetype',
            'pathnamehash' => 'privacy:metadata:files:pathnamehash',
            'contenthash'  => 'privacy:metadata:files:contenthash',
            'filecontents' => 'privacy:metadata:files:filecontents',
        ], 'privacy:metadata:files:externalpurpose');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid) : contextlist {
        return new contextlist();
    }

    public static function export_user_data(approved_contextlist $contextlist) {
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
    }

    public static function get_users_in_context(userlist $userlist) {
    }

    public static function delete_data_for_users(approved_userlist $userlist) {
    }
}