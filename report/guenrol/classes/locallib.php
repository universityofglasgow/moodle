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
 * Main class for menu listing
 *
 * @package    report_enhance
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_guenrol;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/gusync/lib.php');

class locallib {

    protected $courseid;

    public function __construct($courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Find the last time any updates made to the enrolments
     * @return string userdate or 'never'
     */
    public function lastupdate() {
        global $DB;

        $sql = "select max(timeupdated) as updated from {enrol_gudatabase_users} where courseid=?";
        if ($updated = $DB->get_record_sql($sql, [$this->courseid])) {
            $lastupdate = userdate($updated->updated);
        } else {
            $lastupdate = get_string('never', 'report_guenrol');
        }

        return $lastupdate;
    }

    /**
     * Get the codes recorded for this course
     * @return array, array raw list and simplified list
     */
    public function getcodes() {
        global $DB;

        $codes = $DB->get_records( 'enrol_gudatabase_codes', ['courseid' => $this->courseid]);

        // convert to simple array
        $simplecodes = array();
        foreach ($codes as $code) {
            $simplecodes[$code->id] = $code->code; 
        }

        return [$codes, $simplecodes];
    }

    /**
     * Add user record in place by lastname/firstname in array
     * @param array $users
     * @param object $newuser
     */
    protected function add_user_in_place(&$users, $newuser) {
        $lower = [];
        $higher = [];
        foreach ($users as $user) {
            if (strcasecmp($newuser->lastname . $newuser->firstname, $user->lastname . $user->firstname) <= 0) {
                $higher[$user->id] = $user;
            } else {
                $lower[$user->id] = $user;
            }
        }
        $lower[$newuser->id] = $newuser;

        $users = $lower + $higher;
    }

    /**
     * Get list of users for given code
     * (or all users)
     * @param array $codes
     * @param int $courseid
     * @return array
     */
    public function get_code_users($codes, $courseid) {
        global $DB;

        // Get users who are actually enrolled
        $context = \context_course::instance($courseid);
        $enrolledusers = get_enrolled_users($context, '', 0, 'u.id');

        $gudatabase = enrol_get_plugin('gudatabase');
        $codeusers = $gudatabase->external_enrolments($codes);

        // Iterate over data and find actual users
        $users = [];

        // Check for users who are in the code table but not really enrolled.
        // Also add link. 
        foreach ($codeusers as $codeuser) {
            if ($user = $DB->get_record('user', ['username' => $codeuser->UserName])) {
                $user->link = new \moodle_url('/user/view.php', ['id' => $user->id, 'course' => $courseid]);
                $user->enrolled = array_key_exists($user->id, $enrolledusers);
                $user->code = $codeuser->courses;
                $this->add_user_in_place($users, $user);
            }
        }

        return $users;
    }

    /**
     * Get all 'gudatabase' enrolments 
     * @param int $courseid
     * @return array
     */
    public function get_gudatabase_enrolments($courseid) {
        global $DB;

        // Get the gudatabase enrolments in this course.
        $sql = 'SELECT u.*, e.id AS instance from {user} u ';
        $sql .= 'JOIN {user_enrolments} ue ON (ue.userid=u.id) ';
        $sql .= 'JOIN {enrol} e ON (ue.enrolid=e.id) ';
        $sql .= 'WHERE e.courseid=:courseid AND e.enrol=:enrol ';
        $sql .= 'ORDER BY lastname, firstname ';
        $users = $DB->get_records_sql($sql, ['courseid' =>$courseid, 'enrol' => 'gudatabase']);

        return $users;
    }

    /**
     * Get list of users who will be 'removed'
     * That is, they are in the course with 'gudatabase' enrolment type
     * but do not appear in the MyCampus list
     * @param array $codes (simple)
     * @param int $courseid
     * @return array
     */
    public function get_remove_users($codes, $courseid) {
        $gudatabaseusers = $this->get_gudatabase_enrolments($courseid);
        $codeusers = $this->get_code_users($codes, $courseid);
        $users = [];
        foreach ($gudatabaseusers as $user) {
            if (!array_key_exists($user->id, $codeusers)) {
                $user->enrolled = true;
                $user->link = new \moodle_url('/user/view.php', ['id' => $user->id, 'course' => $courseid]);
                $user->code = get_string('nocode', 'report_guenrol');
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * Disable auto enrolment
     * @param array $codes
     */
    public function disable_enrolment($codes) {
        global $DB;

        // Get course - we'll probably need it
        $course = $DB->get_record('course', ['id' => $this->courseid], '*', MUST_EXIST);

        foreach ($codes as $code) {
            if ($code->location == 'plugin') {

                // Disable plugin instance
                $instance = $DB->get_record('enrol', ['id' => $code->instanceid], '*', MUST_EXIST);
                $instance->status = 1;
                $DB->update_record('enrol', $instance);
            } else if ($code->location == 'shortname') {

                // Add underscores to shortname (may have to add more than one).
                $underscore = '_';
                while ($DB->get_record('course', ['shortname' => $underscore . $course->shortname])) {
                    $underscore .= '_';
                    if ($underscore == '__________') {
                        break;
                    }
                }
                $course->shortname = $underscore . $course->shortname;
                $DB->update_record('course', $course);
            } else if ($code->location == 'idnumber') {

                // Modify just this string
                $course->idnumber = str_replace($code->code, '_' . $code->code, $course->idnumber);
                $DB->update_record('course', $course); 
            }
        }
    }

    /**
     * Get user enrolment instances
     * @param array $users
     */
    public function get_instances($users) {
        global $DB;

        $sql = 'SELECT ue.userid, ue.enrolid FROM {user_enrolments} ue
            JOIN {enrol} er ON ue.enrolid = er.id
            WHERE er.enrol = "gudatabase"
            AND er.courseid = :courseid';
        $enrolids = $DB->get_records_sql($sql, ['courseid' => $this->courseid]);

        foreach ($users as $user) {
            if (array_key_exists($user->id, $enrolids)) {
                $user->instance = $enrolids[$user->id]->enrolid;
            }
        }
    }

    /**
     * Use list generated in get_remove_users to unenrol them from the course
     * @param array $users
     * @return int $count number unenrolled
     */
    public function remove_users($users) {
        global $DB;

        $count = 0;

        $instances = array();
        $gudatabase = enrol_get_plugin('gudatabase');
        foreach ($users as $user) {
            if (!empty($user->instance)) {
                if (!array_key_exists($user->instance, $instances)) {
                    $instances[$user->instance] = $DB->get_record('enrol', ['id' => $user->instance], '*', MUST_EXIST);
                }
                $gudatabase->unenrol_user($instances[$user->instance], $user->id);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Function used to handle mtrace by outputting the text to normal browser window.
     *
     * @param string $message Message to output
     * @param string $eol End of line character
     */
    public static function mtrace_wrapper($message, $eol) {
        echo s($message . $eol);
    }

    /**
     * Force enrolment process to be run for given course.
     * @param object $output (renderer)
     * @param object $course
     */
    public function sync($output, $course) {
        global $CFG, $DB;

        // Note that text is output directly to indicate progress
        $CFG->mtrace_wrapper = 'report_guenrol\locallib::mtrace_wrapper';
        echo "<pre>";

        // Get the enrolment plugin.
        $plugin = enrol_get_plugin('gudatabase');

        if ($instances = $DB->get_records('enrol', array('courseid' => $course->id, 'enrol' => 'gudatabase'))) {
            foreach ($instances as $instance) {
                if (!$plugin->enrolment_possible($course, $instance)) {
                    mtrace(get_string('notpossible', 'report_guenrol'));
                }
                mtrace(get_string('processinginstance', 'enrol_gudatabase', $plugin->get_instance_name($instance)));
                mtrace(get_string('syncusers', 'enrol_gudatabase'));
                $plugin->enrol_course_users($course, $instance);
                mtrace(get_string('syncgroups', 'enrol_gudatabase'));
                $plugin->sync_groups($course, $instance);
           }
        }
        \cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($course->id));

        // Export enrolments to 'Sharepoint' database.
        mtrace(get_string('syncexport', 'enrol_gudatabase'));
        local_gusync_sync_one($course->id);
        echo "</pre>";
        echo $output->continue_button(new \moodle_url('/report/guenrol/index.php', ['id' => $course->id]));
    }

}
