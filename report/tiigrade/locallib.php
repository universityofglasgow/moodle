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
 * @subpackage tiigrade
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class report_tiigrade {

    /**
     * Get assignments
     * parts that go with them
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
     * @param int $assignid
     * @param array $assignments valid assignments
     * @return boolean true if ok
     */
    public static function allowed_to_view($assignid, $assignments) {
        if (array_key_exists($assignid, $assignments)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * sort users using callback
     */
    public static function sort_users($users, $onname=false) {
        uasort($users, function($a, $b) use ($onname) {
            if ($onname) {
                return strcasecmp(fullname($a), fullname($b));
            } else {
                return strcasecmp($a->idnumber, $b->idnumber);
            }
        });
        return $users;
    }

    /**
     * Get the list of submissions and their user data
     *
     * @return array list of users
     */
    public static function get_submissions($cmid, $assignid) {
        global $DB;

        // Get the list of submissions for this part.
        if (!$submissions = $DB->get_records('plagiarism_turnitin_files', array('cm' => $cmid))) {
            return array();
        }
        foreach ($submissions as $id => $submission) {
            if ($submission->userid) {
                $user = $DB->get_record('user', array('id' => $submission->userid));
                if ($mapping = $DB->get_record('assign_user_mapping', array('userid' => $user->id, 'assignment' => $assignid))) {
                    $user->blindid = $mapping->id;
                } else {
                    $user->blindid = null;
                }
                $submissions[$id]->user = $user;
            } else {
                $submissions[$id]->user = null;
            }
        }

        return $submissions;
    }

    public static function export($submissions, $reveal, $filename) {
        global $CFG;
        require_once($CFG->dirroot.'/lib/excellib.class.php');

        $workbook = new MoodleExcelWorkbook("-");
        // Sending HTTP headers.
        $workbook->send($filename);
        // Adding the worksheet.
        $myxls = $workbook->add_worksheet(get_string('workbook', 'report_anonymous'));

        // Headers.
        $myxls->write_string(0, 0, '#');
        $myxls->write_string(0, 1, get_string('idnumber'));
        $myxls->write_string(0, 2, get_string('blindid', 'report_tiigrade'));
        $myxls->write_string(0, 3,  get_string('paperid', 'report_tiigrade'));
        $i = 4;
        if ($reveal) {
            $myxls->write_string(0, 4, get_string('firstname'));
            $myxls->write_string(0, 5, get_string('lastname'));
            $myxls->write_string(0, 6, get_string('email'));
            $i = 7;
        }
        $myxls->write_string( 0, $i, get_string('grade'));
        $myxls->write_string( 0, $i + 1, get_string('similarity', 'report_tiigrade'));
        $myxls->write_string( 0, $i + 2, get_string('date'));

        // Add some data.
        $row = 1;
        foreach ($submissions as $submission) {
            $user = $submission->user;
            if (!$user) {
                continue;
            }
            $myxls->write_number($row, 0, $row);
            if ($user->idnumber) {
                $myxls->write_string($row, 1, $user->idnumber);
            } else {
                $myxls->write_string($row, 1, '-');
            }
            if ($user->blindid) {
                $myxls->write_string($row, 2, $user->blindid);
            } else {
                $myxls->write_string($row, 2, '-');
            }
            $myxls->write_string($row, 3, $submission->externalid);
            $i = 4;
            if ($reveal) {
                $myxls->write_string($row, 4, $user->firstname);
                $myxls->write_string($row, 5, $user->lastname);
                $myxls->write_string($row, 6, $user->email);
                $i = 7;
            }
            $myxls->write_number($row, $i, $submission->grade);
            $myxls->write_number($row, $i + 1, $submission->similarityscore);
            $myxls->write_string($row, $i + 2, date('d/M/Y H:i', $submission->lastmodified));
            $row++;
        }
        $workbook->close();
    }

}
