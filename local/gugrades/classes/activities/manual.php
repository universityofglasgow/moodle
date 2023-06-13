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
 * This is the default. Override for anything that differs (e.g. Assignment)
 */
class manual implements activity_interface {

    private $gradeitemid; 

    private $courseid;

    private $firstnamefilter;

    private $lastnamefilter;

    private $gradeitem;

    /**
     * Constructor, set grade itemid
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     */
    public function __construct(int $gradeitemid, int $courseid) {
        global $DB;

        $this->gradeitemid = $gradeitemid;
        $this->courseid = $courseid;

        // Get grade item
        $this->gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
    }

    /**
     * Implement set_name_filter()
     */
    public function set_name_filter(string $firstnamefilter, string $lastnamefilter) {
        $this->firstnamefilter = $firstnamefilter;
        $this->lastnamefilter = $lastnamefilter;
    }

    /**
     * Implement get_users()
     */
    public function get_users() {
        $context = \context_course::instance($this->courseid);
        $users = \local_gugrades\users::get_gradeable_users($context, $this->firstnamefilter, $this->lastnamefilter);

        // Displayname
        foreach ($users as $user) {
            $user->displayname = fullname($user);
        }

        return array_values($users);
    }

    /**
     * Implement is_names_hidden()
     */
    public function is_names_hidden() {
        return false;
    }

    /**
     * Implement get_first_grade
     * (this is pulling 'finalgrade' instead of 'rawgrade'. Not sure if this is correct/complete)
     */
    public function get_first_grade(int $userid) {
        global $DB;
        
        if ($grade = $DB->get_record('grade_grades', ['itemid' => $this->gradeitemid, 'userid' => $userid])) {
            if ($grade->finalgrade) {
                return $grade->finalgrade;
            }
        }

        return false;
    }

    /**
     * Get item type
     * @return string
     */
    public function get_itemtype() {
        return  'manual';
    }

    /**
     * Get item name
     * @return string
     */
    public function get_itemname() {
        return $this->gradeitem->itemname;
    }
}