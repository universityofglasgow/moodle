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
 * This file contains functions used by the participation report
 *
 * @package    report
 * @subpackage anonymous
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class report_anonymous {

    /**
     * get blind assignments for this course
     * @param int $id course id
     * @return array
     */
    public static function get_assignments($id) {
        global $DB;

        $assignments = $DB->get_records('assign', array('course' => $id));
        return $assignments;
    }

    /**
     * can the user view the data submitted
     * some checks
     * @param int $assignid assignment id
     * @param array $assignments list of valid assignments
     * @return boolean true if ok
     */
    public static function allowed_to_view($assignid, $assignments) {
        return array_key_exists($assignid, $assignments);
    }

    /**
     * Get the list of potential users for the assignment activity
     * @param object $context current role context
     * @return array list of users
     */
    public static function get_assign_users($context) {
        $currentgroup = null;
        return get_enrolled_users($context, "mod/assign:submit", $currentgroup);
    }

    /**
     * get the user's submissions (or null for none)
     * @param int $assignid assignment id
     * @param array $users list of user objects
     * @return array list of submissions indexed by user (null where not submitted)
     */
    public static function get_submissions($assignid, $users) {
        global $DB;

        $submissions = array();
        foreach ($users as $user) {
            $instance = new stdClass;
          
            // Check if this user has a participant number
            if ($mapping = $DB->get_record('assign_user_mapping', array('assignment' => $assignid, 'userid' => $user->id))) {
                $user->participantid = $mapping->id;
            } else {
                $user->participantid = '-';
            }
            $instance->user = $user;
            if ($submission = $DB->get_record('assign_submission', array('userid' => $user->id, 'assignment' => $assignid))) {
                $instance->submission = $submission;
            } else {
                $instance->submission = null;
            }
            $submissions[] = $instance;
        }

        return $submissions;
    }

    /**
     * sort users using callback
     */
    public static function sort_submissions($submissions, $onname=false) {
        uasort($submissions, function($a, $b) use ($onname) {
            if ((!$a->submission) xor (!$b->submission)) {
                return ($a->submission) ? 1 : -1;
            } else if ($onname) {
                return strcasecmp(fullname($a->user), fullname($b->user));
            } else {
                return strcasecmp($a->user->idnumber, $b->user->idnumber);
            }
        });
        return $submissions;
    }

    public static function export($assignment, $submissions, $reveal, $filename) {
        global $CFG;
        require_once($CFG->dirroot.'/lib/excellib.class.php');

        $workbook = new MoodleExcelWorkbook("-");
        // Sending HTTP headers.
        $workbook->send($filename);
        // Adding the worksheet.
        $myxls = $workbook->add_worksheet(get_string('workbook', 'report_anonymous'));

        // Titles.
        $myxls->write_string(0, 0, get_string('assignmentname', 'report_anonymous'));
        $myxls->write_string(0, 1, $assignment->name);

        // Headers.
        $myxls->write_string(3, 0, '#');
        $myxls->write_string(3, 1, get_string('idnumber'));
        $myxls->write_string(3, 2, get_string('email'));
        if ($reveal) {
            $myxls->write_string(3, 3, get_string('username'));
            $myxls->write_string(3, 4, get_string('fullname'));
        }
        $myxls->write_string(3, 5, get_string('submitted', 'report_anonymous'));
        $myxls->write_string(3, 6, get_string('participantnumber', 'report_anonymous'));

        // Add some data.
        $row = 4;
        foreach ($submissions as $s) {
            $myxls->write_number($row, 0, $row);
            if ($s->user->idnumber) {
                $myxls->write_string($row, 1, $s->user->idnumber);
            } else {
                $myxls->write_string($row, 1, '-');
            }
            $myxls->write_string($row, 2, $s->user->email);
            if ($reveal || !$assignment->blindmarking) {
                $myxls->write_string($row, 3, $s->user->username);
                $myxls->write_string($row, 4, fullname($s->user));
            }
            if ($s->submission) {
                $myxls->write_string($row, 5, userdate($s->submission->timemodified));
            } else {
                $myxls->write_string($row, 5, get_string('no'));
            }
            $myxls->write_string($row, 6, $s->user->participantid);
            $row++;
        }
        $workbook->close();
    }

}
