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

require_once(dirname(__FILE__) . '/html2text/html2text.php');
require_once(dirname(__FILE__) . '/forceutf8/src/ForceUTF8/Encoding.php');

use \ForceUTF8\Encoding;

class report_anonymous {

    /**
     * get blind assignments for this course
     * @param int $id course id
     * @return array
     */
    public static function get_assignments($id) {
        global $DB;

        $assignments = $DB->get_records('assign', array('course' => $id));

        // add URKUND status
        foreach ($assignments as $assignment) {
            $assignment->urkundenabled = self::urkund_enabled($assignment->id);
        }

        return $assignments;
    }

    /** 
     * Estimate submission return time from number of attempts
     * @param int $attempts 
     * @return int return time in minutes
     */
    private static function get_returntime($attempts) {
       
        // cron interval (minutes)
        $croninterval = 5;

        // Urkund retry table
        $attemptTimes = Array(
             '1'    => '0',
             '2'    => '5',
             '3'    => '10',
             '4'    => '15',
             '5'    => '30',
             '6'    => '60',
             '7'    => '90',
             '8'    => '120',
             '9'    => '240',
             '10'   => '360',
             '11'   => '480',
             '12'   => '600',
             '13'   => '720',
             '14'   => '960',
             '15'   => '1200',
             '16'   => '1440',
             '17'   => '1680',
             '18'   => '1920',
             '19'   => '2160',
             '20'   => '2400',
             '21'   => '2640',
             '22'   => '2880',
             '23'   => '3660',
             '24'   => '3840',
             '25'   => '4320',
             '26'   => '4800',
             '27'   => '5280',
             '28'   => '5760',
             '29'   => '6240'
        );

        // Estimate time
        if (!$attempts) {
            return $croninterval;
        } else if (array_key_exists($attempts, $attemptTimes)) {
            return $attemptTimes[$attempts] + (2 * $croninterval);
        } else {
            return false;
        }
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
     * Get the group(s) a user is a member of
     * @param int $userid
     * @param int $courseid
     * @return string
     */
    public static function get_user_groups($userid, $courseid) {
        global $DB;

        $sql = 'SELECT gg.id, userid, courseid, name FROM {groups} gg
            JOIN {groups_members} gm ON (gg.id = gm.groupid)
            WHERE gg.courseid = ?
            AND gm.userid = ?';
        if (!$groups = $DB->get_records_sql($sql, array($courseid, $userid))) {
            return '-';
        } else {
            $names = array();
            foreach ($groups as $group) {
                $names[] = $group->name;
            }
            return implode(', ', $names);
        }
    }

    /**
     * Find user's allocated marker if there is one
     * @param int $assignid assignment id
     * @param int $userid 
     * @return string formatted user name or '-'
     */
    public static function get_allocatedmarker($assignid, $userid) {
        global $DB;

        if ($flags = $DB->get_record('assign_user_flags', array('userid' => $userid, 'assignment' => $assignid))) {
            if ($flags->allocatedmarker) {
                if ($user = $DB->get_record('user', array('id' => $flags->allocatedmarker))) {
                    return fullname($user);
                } 
            }
        }
        return '-';
    }

    /**
     * Strip HTML from feedback comments for export
     * @param string $feedback
     * @return string sanitised feedback
     */
    private static function sanitise_feedback($feedback) {
        if (!$feedback) {
            return '-';
        }
        $errorlevel = error_reporting();
        error_reporting(0);
        $text = convert_html_to_text($feedback);
        error_reporting($errorlevel);
        $text = Encoding::fixUTF8($text);

        return $text;
    }

    /**
     * Find  grades for the submission
     * @param int $courseid
     * @param int $assignid assignment id
     * @param array $users
     * @return string grade or '-' if no grade
     */
    public static function get_grades($courseid, $assignid, $users) {
        global $DB;
        
        // convert user array to list of ids
        $userids = array_keys($users);

        // Get grade info
        $gradeinfo = grade_get_grades($courseid, 'mod', 'assign', $assignid);
        $gradeinfo = $gradeinfo->items[0];
        if (!empty($gradeinfo->scaleid)) {
            $scale = $DB->get_record('scale', array('id' => $gradeinfo->scaleid), '*', MUST_EXIST);
            $scaleitems = explode(',', $scale->scale);
            $scaleitems = array_map('trim', $scaleitems);
        } else {
            $scaleitems = null;
        }

        // Get grade objects for chosen assignment
        $grades = array();
        $usergrades = grade_get_grades($courseid, 'mod', 'assign', $assignid, array_keys($users));
        foreach ($users as $user) {
            $finalgrade = $usergrades->items[0]->grades[$user->id];
            $grade = $finalgrade->grade;
            $feedback = $finalgrade->feedback;
            if ($grade === null) {
                $grade = '-'; 
            } else if ($scaleitems) {
                $grade = $scaleitems[(int)$grade - 1];
            } else {
                $grade = rtrim($grade, '0.');
            }
            $record = new stdClass;
            $record->grade = $grade;
            $record->feedback = self::sanitise_feedback($feedback);
            $grades[$user->id] = $record;
        }

        return $grades;
    }

    /**
     * get the user's submissions (or null for none)
     * @param int $assignid assignment id
     * @param array $users list of user objects
     * @return array list of submissions indexed by user (null where not submitted)
     */
    public static function get_submissions($assignid, $users, $group) {
        global $DB;

        // Is Urkund in use
        $urkund = self::urkund_enabled($assignid);

        $submissions = array();
        foreach ($users as $user) {

            // is user in group
            if ($group) {
                if (!groups_is_member($group, $user->id)) {
                    continue;
                }
            }
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

            // check if this user has an allocated marker
            $instance->allocatedmarker = self::get_allocatedmarker($assignid, $user->id);

            // check for urkund files
            if ($urkund) {
                $files = self::urkund_files($assignid, $user->id);
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $urkundinstance = clone $instance;
                        $urkundinstance->urkundstatus = self::urkund_status($file->statuscode);
                        $urkundinstance->urkundfilename = $file->filename;
                        $urkundinstance->urkundscore = $file->similarityscore;
                        $urkundinstance->submittedonbehalf = null;
                        $urkundinstance->returntime = self::get_returntime($file->attempt);
                        if ($file->relateduserid) {
                            if ($subuser = $DB->get_record('user', array('id' => $file->userid))) {
                                $urkundinstance->submittedonbehalf = fullname($subuser);
                            }
                        }
                        $submissions[] = $urkundinstance;
                    }
                } else {
                    $submissions[] = $instance;
                }
            } else {
                $submissions[] = $instance;
            }
        }

        return $submissions;
    }

    /**
     * Organise submission/user data into displayable form
     */
    public static function datatodisplay($submissions, $grades, $courseid, $showname=false) {
        $output = array();

        foreach ($submissions as $s) {
            $record = new stdClass;

            // Matric/ID Number
            if ($s->user->idnumber) {
                $record->idnumber = $s->user->idnumber;
            } else {
                $record->idnumber = '-';
            }

            // Participant number
            $record->participantid = $s->user->participantid;

            // Submitted (timemodified retained for sort)
            if ($s->submission) {
                $record->date = date('d/m/Y H:i', $s->submission->timemodified);
                if ($s->submission->status == 'new') {
                    $record->date = '-';
                    $record->status = '-';
                    $record->timemodified = 0;
                } else {
                    $record->status = $s->submission->status == 'new' ? '-' : $s->submission->status;
                    $record->timemodified = $s->submission->timemodified;
                }
            } else {
                $record->date = '-';
                $record->status = '-';
                $record->timemodified = 0;
            }

            // Name
            if ($showname) {
                $userurl = new moodle_url('/user/view.php', array('id' => $s->user->id, 'course' => $courseid));
                $record->name = "<a href=\"$userurl\">".fullname($s->user)."</a>";
                $record->fullname = fullname($s->user);
            } else {
                $record->name = get_string('hidden', 'report_anonymous');
                $record->fullname = '-';
            }

            // If submitted on behalf of (by urkund)
            if (!empty($s->submittedonbehalf)) {
                $record->name .= ' (' . $s->submittedonbehalf . ')';
            }

            // EMail
            $record->email = $s->user->email;

            // Group(s)
            $record->groups = self::get_user_groups($s->user->id, $courseid);

            // Allocated marker
            $record->allocatedmarker = $s->allocatedmarker;

            // Grade
            $record->grade = $grades[$s->user->id]->grade;

            // Feedback
            $record->feedback = $grades[$s->user->id]->feedback;

            $record->urkundfilename = isset($s->urkundfilename) ? $s->urkundfilename : '-';
            $record->urkundstatus = isset($s->urkundstatus) ? $s->urkundstatus : '-';
            $record->urkundscore = isset($s->urkundscore) ? $s->urkundscore : '-';
            $record->returntime = isset($s->returntime) ? $s->returntime : '-';
      
            $output[] = $record;
        }

        return $output;
    }

    /**
     * Is Urkund enabled on this assignment
     * @param int $assignmentid
     * @return boolean
     */
    public static function urkund_enabled($assignmentid) {
        global $DB;
        
        $cm = get_coursemodule_from_instance('assign', $assignmentid);
        if ($urkund = $DB->get_record('plagiarism_urkund_config', array('cm' => $cm->id, 'name' => 'use_urkund'))) {
            if ($urkund->value) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Get Urkund status, filename and similarity
     * @param int $assignmentid
     * @param int $userid
     * @return array of files or false
     */
    public static function urkund_files($assignmentid, $userid) {
        global $DB;

        $cm = get_coursemodule_from_instance('assign', $assignmentid);
        if ($files = $DB->get_records('plagiarism_urkund_files', array('cm' => $cm->id, 'userid' => $userid))) {
            return $files;
        } else {

            // if this was "submitted on behalf of" then record will refer to 'relateduserid'
            if ($files = $DB->get_records('plagiarism_urkund_files', array('cm' => $cm->id, 'relateduserid' => $userid))) {
                return $files;
            }
        }
        return false;
    }

    /**
     * Translate Urkund status code
     * @param string $statuscode
     * @return string 
     */
    public static function urkund_status($statuscode) {
        $codes = array(
            '200' => get_string('statusprocessed', 'report_anonymous'),
            '202' => get_string('statusaccepted', 'report_anonymous'),
            '202-old' => get_string('statusacceptedold', 'report_anonymous'),
            '400' => get_string('statusbadrequest', 'report_anonymous'),
            '404' => get_string('statusnotfound', 'report_anonymous'),
            '410' => get_string('statusgone', 'report_anonymous'),
            '415' => get_string('statusunsupported', 'report_anonymous'),
            '413' => get_string('statustoolarge', 'report_anonymous'),
            '444' => get_string('statusnoreceiver', 'report_anonymous'),
            '613' => get_string('statusinvalid', 'report_anonymous'),
            'Analyzed' => get_string('statusanalyzed', 'report_anonymous'),
            'Pending' => get_string('statuspending', 'report_anonymous'),
            'Rejected' => get_string('statusrejected', 'report_anonymous'),
            'Error' => get_string('statuserror', 'report_anonymous'),
            'timeout' => get_string('statustimeout', 'report_anonymous'),
        );
        if (isset($codes[$statuscode])) {
            return $codes[$statuscode];
        } else {
            return get_string('statusother', 'report_anonymous', $statuscode);
        }
    }

    /**
     * sort users using callback
     */
    public static function sort_submissions($submissions, $dir, $fieldname) {
        if ($fieldname=='name') {
            $fieldname = 'fullname';
        }
        if ($fieldname=='date') {
            $fieldname = 'timemodified';
        }
        uasort($submissions, function($a, $b) use ($fieldname, $dir) {
            if ($fieldname == 'urkundscore') {
                if ($dir=='asc') {
                    return ($a->$fieldname > $b->$fieldname) ? 1 : -1;
                } else {
                    return ($b->$fieldname > $a->$fieldname) ? 1 : -1;
                }
            } else {
                if ($dir=='asc') {
                    return strcasecmp($a->$fieldname, $b->$fieldname);
                } else {
                    return strcasecmp($b->$fieldname, $a->$fieldname);
                }
            }
        });
        return $submissions;
    }

    public static function export($assignment, $submissions, $reveal, $filename, $urkund) {
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
        $i = 0;
        $myxls->write_string(3, $i++, '#');
        $myxls->write_string(3, $i++, get_string('idnumber', 'report_anonymous'));
        $myxls->write_string(3, $i++, get_string('participantnumber', 'report_anonymous'));
        $myxls->write_string(3, $i++, get_string('status', 'report_anonymous'));
        $myxls->write_string(3, $i++, get_string('submitdate', 'report_anonymous'));
        $myxls->write_string(3, $i++, get_string('name', 'report_anonymous'));
        $myxls->write_string(3, $i++, get_string('email', 'report_anonymous'));
        $myxls->write_string(3, $i++, get_string('group', 'report_anonymous'));
        $myxls->write_string(3, $i++, get_string('allocatedmarker', 'report_anonymous'));
        $myxls->write_string(3, $i++, get_string('grade', 'report_anonymous'));
        $myxls->write_string(3, $i++, get_string('feedback', 'report_anonymous'));
        if ($urkund) {
            $myxls->write_string(3, $i++, get_string('urkundfile', 'report_anonymous'));
            $myxls->write_string(3, $i++, get_string('urkundstatus', 'report_anonymous'));
            $myxls->write_string(3, $i++, get_string('urkundscore', 'report_anonymous'));
            $myxls->write_string(3, $i++, get_string('returntime', 'report_anonymous'));
        }

        // Add some data.
        $row = 4;
        foreach ($submissions as $s) {
            $i = 0;
            $myxls->write_number($row, $i++, $row);
            $myxls->write_string($row, $i++, $s->idnumber);
            $myxls->write_string($row, $i++, $s->participantid);
            $myxls->write_string($row, $i++, $s->status);
            $myxls->write_string($row, $i++, $s->date);
            $myxls->write_string($row, $i++, $s->fullname);
            $myxls->write_string($row, $i++, $s->email);
            $myxls->write_string($row, $i++, $s->groups);
            $myxls->write_string($row, $i++, $s->allocatedmarker);
            if (is_numeric($s->grade)) {
                $myxls->write_number($row, $i++, $s->grade);
            } else {
                $myxls->write_string($row, $i++, $s->grade);
            }
            $myxls->write_string($row, $i++, $s->feedback);
            if ($urkund) {
                $myxls->write_string($row, $i++, $s->urkundfilename);
                $myxls->write_string($row, $i++, $s->urkundstatus);
                $myxls->write_string($row, $i++, $s->urkundscore);
                $myxls->write_number($row, $i++, $s->returntime);
            }
            $row++;
        }
        $workbook->close();
    }

}
