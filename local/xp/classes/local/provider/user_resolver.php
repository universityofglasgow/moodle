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
 * User resolver.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\provider;
defined('MOODLE_INTERNAL') || die();

use core_user;
use moodle_database;
use user_picture;

/**
 * User resolver.
 *
 * Specific implementation allowing to resolve a user from a set of arbitrary
 * fields while ensuring that permissions would allow the user to be known.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_resolver {

    /** @var moodle_database $db The database. */
    protected $db;
    /** @var array The user fields to fetch. */
    protected $fields;

    /**
     * Constructor.
     *
     * @param moodle_database $db The database.
     */
    public function __construct(moodle_database $db) {
        $this->db = $db;
        $this->fields = user_picture::fields();
    }

    /**
     * Resolve a user.
     *
     * @param string|number $info Arbitrary data to resolve the user with.
     * @return object|null User object containing enough fields for display purposes.
     */
    public function resolve($data) {
        global $CFG;

        $data = (string) $data;
        $fields = $this->fields;
        $user = null;

        // Search by user ID.
        if (ctype_digit($data)) {
            $user = core_user::get_user((int) $data, $fields);
        }

        // Search by email.
        if (!$user && strpos($data, '@')) {
            $user = $this->db->get_record('user', ['email' => $data, 'mnethostid' => $CFG->mnet_localhost_id], $fields);
        }

        // Search by username.
        if (!$user) {
            $user = core_user::get_user_by_username($data, $fields);
        }

        return $user ? $user : null;
    }

}
