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
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\activities;

/**
 * Access data in course activities
 * 
 */
interface activity_interface {

    /**
     * Set (first letter of) firstname and last name for filtering
     * @param string $firstnamefilter
     * @param string $lastnamefilter
     */
    public function set_name_filter(string $firstnamefilter, string $lastnamefilter);

    /**
     * Get the list of users for this activity type
     * Filtered by firstname, lastname (if possible)
     * It should also return a field 'displayname' in each record. Whatever you want to show
     * @return array
     */
    public function get_users(); 

    /**
     * Should the student names be hidden to normal users?
     * Probabl mostly applies to Assignment
     * @return boolean
     */
    public function is_names_hidden();

    /**
     * Get initial grade for activity/item for user
     */
    public function get_first_grade(int $userid);

}