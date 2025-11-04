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

namespace tool_gudelete;
defined('MOODLE_INTERNAL') || die();

/**
 * Class course_archiving_helper
 *
 * @package    tool_gudelete
 * @copyright  2024 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_archiving_helper {

    public function process_archivment($config) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/course/lib.php');

        // Get courses.
        $result = $this->check_courses($config);

        $archived_list = '';
        foreach ($result->archive as $course) {
            // Move and hide.
            $course->category = $config->targetcat;
            $course->visible = 0;
            update_course($course);
            $archived_list .= $course->fullname."<br />";

            // Unenrol students.
            /**$context = \context_course::instance($course->id);
            $role = $DB->get_record('role', array('shortname' => 'student'));
            $users = get_role_users($role->id, $context);
            foreach ($users as $user) {
                $instances = $DB->get_records('enrol', array('courseid' => $course->id));
                if (!empty ($instances)) {
                    foreach ($instances as $instance) {
                        $user_enrolment = $DB->get_record('user_enrolments',
                            array('enrolid' => $instance->id, 'userid' => $user->id));
                        if (!empty ($user_enrolment)) {
                            $plugin = enrol_get_plugin($instance->enrol);
                            $plugin->unenrol_user($instance, $user->id);
                        }
                    }
                }
            }*/
        }

        // Delete older courses.
        $deleted_list = '';
        ob_start();
        foreach ($result->delete as $course) {
            $sucess = delete_course($course);
            if ($sucess) {
                $deleted_list .= $course->fullname.get_string('remove_success', 'tool_gudelete')."<br />";
                fix_course_sortorder();
            } else {
                $deleted_list .= $course->fullname.get_string('remove_error', 'tool_gudelete')."<br />";
            }
        }
        ob_end_clean();

        $a = new \stdClass();
        $a->archived = $archived_list;
        $a->deleted = $deleted_list;
        return $a;
    }

    public function check_courses($config) {
        global $DB;

        // Get Timestamps.
        $since = time() - ($config->days * 24 * 60 * 60);
        $now = time();

        // Get courses to archive.
        if ($config->include_subcategories) {
            $top_categories = explode(',', $config->sourcecat);
            $categories = array();
            foreach ($top_categories as $category) {
                $categories[$category] = $category;
            }
            $categories = $this->get_all_subcategories($categories);
        } else {
            $categories = explode(',', $config->sourcecat);
        }

        if ($config->targettimestamp == 'timemodified') {
            $courses = $this->get_courses_by_modified($categories, $since, $now);
        } else if ($config->targettimestamp == 'last_activity') {
            $courses = $this->get_courses_by_last_access($categories, $since, $now);
        }

        // Get courses to delete.
        if ($config->targettimestamp == 'timemodified') {
            $old_params = array($config->targetcat, $since);
            $sql = 'SELECT * FROM {course} WHERE category = ? AND timemodified < ?';
            $old_courses = $DB->get_records_sql($sql, $old_params);
        } else if ($config->targettimestamp == 'last_activity') {
            $since2 = $since - ($config->days * 24 * 60 * 60);
            $old_params = array($config->targetcat, $since2);
            $sql = 'SELECT * FROM {course} c,(SELECT courseid, max(timecreated) AS timecreated
                FROM {logstore_standard_log}
                WHERE action = \'updated\' OR action = \'created\'
                GROUP BY courseid) AS log
                WHERE category = ?
                AND log.timecreated < ?
                AND log.courseid = c.id';
            $old_courses = $DB->get_records_sql($sql, $old_params);
        }

        $result = new \stdClass();
        $result->archive = $courses;
        $result->delete = $old_courses;
        return $result;
    }

    private function get_courses_by_last_access($categories, $since) {
        global $DB;

        list($qrypart, $params) = $DB->get_in_or_equal($categories);
        $params[] = $since;
        $sql = 'SELECT * FROM {course} c,(SELECT courseid, max(timecreated) AS timecreated
                FROM {logstore_standard_log}
                WHERE action = \'updated\' OR action = \'created\'
                GROUP BY courseid) AS log
                WHERE category '.$qrypart.'
                AND log.timecreated < ?
                AND log.courseid = c.id';
        return $DB->get_records_sql($sql, $params);
    }

    private function get_courses_by_modified($categories, $since, $now) {
        global $DB;

        list($qrypart, $params) = $DB->get_in_or_equal($categories);
        $params[] = $since;
        $params[] = $now;
        $sql = 'SELECT * FROM {course} c WHERE category '.$qrypart.' AND timemodified < ? AND c.id NOT IN(select courseid from mdl_logstore_standard_log WHERE action <> \'viewed\' and timecreated > '.$since.')';
        return $DB->get_records_sql($sql, $params);
    }

    private function get_all_subcategories($categories) {
        global $CFG;

        $return = array();
        foreach ($categories as $categoryid) {
            $return[$categoryid] = $categoryid;
            $childs = \core_course_category::get($categoryid)->get_children();
            foreach ($childs as $child) {
                // Add childs.
                $return[$child->id] = $child->id;
                // Check for childs child.
                $sub_childs = $this->get_child_categories($child->id);
                $return = $return + $sub_childs;
            }
        }
        return $return;
    }

    private function get_child_categories($categoryid) {
        $return = array();
        $childs = \core_course_category::get($categoryid)->get_children();
        foreach ($childs as $child) {
            // Add childs.
            $return[$child->id] = $child->id;
            // Check for childs child.
            $sub_childs = $this->get_child_categories($child->id);
            $return = $return + $sub_childs;
        }
        return $return;
    }
}
