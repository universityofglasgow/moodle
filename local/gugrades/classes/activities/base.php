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
 * Default class for grade/activity access classes
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
abstract class base {

    /**
     * @var string $firstnamefilter
     */
    protected string $firstnamefilter;

    /**
     * @var string $lastnamefilter
     */
    protected string $lastnamefilter;

    /**
     * @var int $gradeitemid
     */
    protected int $gradeitemid;

    /**
     * @var object $cm
     */
    protected $cm;

    /**
     * @var object $gradeitem
     */
    protected object $gradeitem;

    /**
     * @var int $courseid
     */
    protected int $courseid;

    /**
     * @var int $groupid
     */
    protected int $groupid;

    /**
     * @var string $itemtype
     */
    protected string $itemtype;

    /**
     * @var bool viewfullnames
     */
    protected bool $viewfullnames;

    /**
     * Constructor, set grade itemid
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        global $DB;

        $this->gradeitemid = $gradeitemid;
        $this->courseid = $courseid;
        $this->groupid = $groupid;

        // Default filter.
        $this->firstnamefilter = '';
        $this->lastnamefilter = '';

        // Get grade item.
        $this->gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $this->itemtype = $this->gradeitem->itemtype;

        $this->viewfullnames = false;

        // Get the course module (or false it it isn't one).
        $this->cm = \local_gugrades\users::get_cm_from_grade_item($gradeitemid, $courseid);
    }

    /**
     * Implement set_name_filter()
     * @param string $firstnamefilter
     * @param string $lastnamefilter
     */
    public function set_name_filter(string $firstnamefilter, string $lastnamefilter) {
        $this->firstnamefilter = $firstnamefilter;
        $this->lastnamefilter = $lastnamefilter;
    }

    /**
     * Implement get_users()
     */
    public function get_users() {

        $context = \core\context\course::instance($this->courseid);

        // If cm is defined then we'll get the available users for
        // whatever module it is. Failing that, just get everybody in the course.
        if ($this->cm) {
            $users = \local_gugrades\users::get_available_users_from_cm(
                $this->cm, $context, $this->firstnamefilter, $this->lastnamefilter, $this->groupid);
        } else {
            $users = \local_gugrades\users::get_gradeable_users($context, $this->firstnamefilter,
                $this->lastnamefilter, $this->groupid);
        }

        // Displayname.
        // This may get updated.
        foreach ($users as $user) {
            $user->displayname = fullname($user);

            // Initials
            [$user->firstinitial, $user->lastinitial] = \local_gugrades\users::get_initials($user);
        }

        return array_values($users);
    }

    /**
     * Get user IDs
     * This data is cached
     * @return array
     */
    public function get_user_ids() {
        $cache = \cache::make('local_gugrades', 'availableusers');

        // Unique cache tag for course and gradeitem.
        $cachetag = 'AVAILABLE_' . $this->courseid . '_' . $this->gradeitemid;

        // README: Disable cache for now as may be causing problems - MGU-1171
        /*
        if (!$userids = $cache->get($cachetag)) {
            $users = $this->get_users();
            $userids = array_column($users, 'id');
            $cache->set($cachetag, $userids);
        }*/

        $users = $this->get_users();
        $userids = array_column($users, 'id');

        return $userids;
    }

    /**
     * Get (and check) single user
     * @param int $userid
     * @return object
     */
    public function get_user(int $userid) {
        $context = \context_course::instance($this->courseid);
        $user = \local_gugrades\users::get_gradeable_user($context, $userid);

        // Add displayname.
        $user->displayname = \core_user::get_fullname($user);

        // Initials
        [$user->firstinitial, $user->lastinitial] = \local_gugrades\users::get_initials($user);

        return $user;
    }

    /**
     * Should the student names be hidden to normal users?
     * Probabl mostly applies to Assignment
     * @return boolean
     */
    public function is_names_hidden() {
        return false;
    }

    /**
     * Set viewfullnames
     * Show fullnames if activity supports hidden names
     * @param bool $viewfullnames
     */
    public function set_viewfullnames(bool $viewfullnames) {
        $this->viewfullnames = $viewfullnames;
    }

    /**
     * Implement get_first_grade
     * This is currently just the same as a manual grade
     * (this is pulling 'finalgrade' instead of 'rawgrade'. Not sure if this is correct/complete)
     * @param int $userid
     */
    public function get_first_grade(int $userid) {
        global $DB;

        // MGU-1293: If there's no grade return null (No grade)
        if ($grade = $DB->get_record('grade_grades', ['itemid' => $this->gradeitemid, 'userid' => $userid])) {

            return $grade->finalgrade;
        } else {
            return null;
        }
    }

    /**
     * Get item type
     * @return string
     */
    public function get_itemtype() {

    }

    /**
     * Get item name
     * @return string
     */
    public function get_itemname() {
        return $this->gradeitem->itemname;
    }

    /**
     * Action to take when releasing grades
     * Default is do nothing
     * @param int $userid
     */
    public function release_grades(int $userid) {
        return;
    }

    /**
     * Action to take when un-releasing grades
     * Default is to do nothing
     * @param int $userid
     */
    public function unrelease_grades(int $userid) {
        return;
    }

}
