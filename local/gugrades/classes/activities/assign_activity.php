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
 * Concrete implementation for mod_assign
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\activities;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Specific implementation for assignment
 */
class assign_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $assign
     */
    private $assign;

    /**
     * Constructor, set grade itemid
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        parent::__construct($gradeitemid, $courseid, $groupid);

        // Get the assignment object.
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
     * Get details for individual user
     * @param object $assigninstance
     * @param object $user
     * @return object
     */
    private function get_user_info(object $assigninstance, object $user) {
        $uniqueid = \assign::get_uniqueid_for_user_static($assigninstance->id, $user->id);
        $user->uniqueid = $uniqueid;
        $user->fullname = fullname($user);
        $hidden = $this->is_names_hidden();
        if ($hidden) {
            $user->displayname = get_string('participantnumber', 'local_gugrades', $uniqueid);
            if ($this->viewfullnames) {
                $user->displayname .= ' (' . fullname($user) . ')';
            }
        } else {
            $user->displayname = fullname($user);
        }

        return $user;
    }

    /**
     * Implement get_users()
     */
    public function get_users() {
        $context = \context_course::instance($this->courseid);
        $users = \local_gugrades\users::get_available_users_from_cm(
            $this->cm, $context, $this->firstnamefilter, $this->lastnamefilter, $this->groupid);

        $assigninstance = $this->assign->get_instance();

        // Displayname and uniqueid.
        $hidden = $this->is_names_hidden();
        foreach ($users as $id => $user) {
            $users[$id] = $this->get_user_info($assigninstance, $user);
        }

        // Re-order by uniqueid.
        if ($hidden) {
            usort($users, function($a, $b) {
                return $a->uniqueid <=> $b->uniqueid;
            });
        }

        return $users;
    }

    /**
     * Get (and check) single user
     * @param int $user
     * @return object
     */
    public function get_user(int $userid) {
        $user = parent::get_user($userid);
        $assigninstance = $this->assign->get_instance();
        $user = $this->get_user_info($assigninstance, $user);

        return $user;
    }

    /**
     * Implement is_names_hidden()
     */
    public function is_names_hidden() {
        $assigninstance = $this->assign->get_instance();
        return $assigninstance->blindmarking && !$assigninstance->revealidentities;
    }

    /**
     * Set viewfullnames
     * Show fullnames if has capability
     * @param bool $viewfullnames
     */
    public function set_viewfullnames(bool $viewfullnames) {
        $context = \context_course::instance($this->courseid);
        if (has_capability('local/gugrades:viewhiddennames', $context)) {
            $this->viewfullnames = $viewfullnames;
        } else {
            $this->viewfullnames = false;
        }
    }

    /**
     * Implement get_first_grade
     * @param int $userid
     */
    public function get_first_grade(int $userid) {
        global $DB;

        // If the grade is overridden in the Gradebook then we can
        // revert to the base - i.e., get the grade from the Gradebook.
        if ($grade = $DB->get_record('grade_grades', ['itemid' => $this->gradeitemid, 'userid' => $userid])) {
            if ($grade->overridden) {
                return parent::get_first_grade($userid);
            }
        }

        // This just pulls the grade from assign. Not sure it's that simple
        // False, means do not create grade if it does not exist
        // This is the grade object from mdl_assign_grades (check negative values).
        $assigngrade = $this->assign->get_user_grade($userid, false);

        // Valid possibilities are NULL (grade deleted) or -1 (never graded)
        // Either of these represent no grade for our purposes.
        if (($assigngrade !== false) && ($assigngrade->grade != -1) && ($assigngrade->grade != null)) {

            return $assigngrade->grade;
        }

        return false;
    }

    /**
     * Get item type
     * @return string
     */
    public function get_itemtype() {
        return 'assign';
    }

    /**
     * Action to take when releasing grades
     * For Assignment, update workflow
     * @param int $userid
     */
    public function release_grades(int $userid) {
        $data = (object) [
            'attemptnumber' => 0,
            'workflowstate' => 'released',
            'feedbackformat' => 0,
            'assignfeedbackcomments_editor' => [
                'text' => '',
                'format' => 0,
            ],
        ];
        $this->assign->save_grade($userid, $data);

        return;
    }

    /**
     * Action to take when un-releasing grades
     * Default is to do nothing
     * @param int $userid
     */
    public function unrelease_grades(int $userid) {
        $data = (object) [
            'attemptnumber' => 0,
            'workflowstate' => 'readyforrelease',
            'feedbackformat' => 0,
            'assignfeedbackcomments_editor' => [
                'text' => '',
                'format' => 0,
            ],
        ];
        $this->assign->save_grade($userid, $data);

        return;
    }

}
