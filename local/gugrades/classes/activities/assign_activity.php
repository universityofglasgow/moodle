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

require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Access data in course activities
 * Specific implementation for assignment
 */
class assign_activity implements activity_interface {

    private $gradeitemid; 

    private $courseid;

    private $firstnamefilter;

    private $lastnamefilter;

    private $cm;

    private $assign;

    /**
     * Constructor, set grade itemid
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     */
    public function __construct(int $gradeitemid, int $courseid) {
        $this->gradeitemid = $gradeitemid;
        $this->courseid = $courseid;

        // Get the assignment object
        $this->cm = \local_gugrades\users::get_cm_from_grade_item($gradeitemid, $courseid);
        $this->assign = $this->get_assign($this->cm);
    }

    /**
     * Get assignment object
     * @param object $cm course module
     * @return object
     */
    private function get_assign($cm) {
        global $DB; 

        $course = $DB->get_record('course', ['id' => $this->courseid], '*', MUST_EXIST);
        $coursemodulecontext = \context_module::instance($cm->id);
        $assign = new \assign($coursemodulecontext, $cm, $course);

        return $assign;
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
        $users = \local_gugrades\users::get_available_users_from_cm($this->cm, $context, $this->firstnamefilter, $this->lastnamefilter);

        $assigninstance = $this->assign->get_instance();

        // Displayname
        $hidden = $this->is_names_hidden();
        foreach ($users as $user) {
            if ($hidden) {
                $uniqueid = \assign::get_uniqueid_for_user_static($assigninstance->id, $user->id);
                $user->displayname = get_string('participantnumber', 'local_gugrades', $uniqueid);
                $user->uniqueid = $uniqueid;
            } else {
                $user->displayname = fullname($user);
            }
        }

        // Re-order by uniqueid
        if ($hidden) {
            usort($users, function($a, $b) {
                return $a->uniqueid > $b->uniqueid;
            });
        }

        return $users;
    }

    /**
     * Implement is_names_hidden()
     */
    public function is_names_hidden() {
        $assigninstance = $this->assign->get_instance();
        return $assigninstance->blindmarking && !$assigninstance->revealidentities;
    }

    /**
     * Implement get_first_grade
     */
    public function get_first_grade(int $userid) {
        
        // This just pulls the grade from assign. Not sure it's that simple
        // False, means do not create grade if it does not exist
        // This is the grade object from mdl_assign_grades (check negative values)
        $assigngrade = $this->assign->get_user_grade($userid, false);

        if ($assigngrade !== false) {

            return $assigngrade->grade;
        } 

        return false;
    }

}