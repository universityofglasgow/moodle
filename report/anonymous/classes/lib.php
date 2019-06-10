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
 * @copyright  2013-2019 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_anonymous;

define('FILENAME_SHORTEN', 30);

//use \ForceUTF8\Encoding;
use \assignfeedback_editpdf\document_services;
use stdClass;

class lib {

    /**
     * get blind assignments for this course
     * @param int $id course id
     * @return array
     */
    public static function get_assignments($id) {
        global $DB;

        $assignments = $DB->get_records('assign', ['course' => $id]);

        // add plagiarism and feedback status
        foreach ($assignments as $assid => $assignment) {
            $assignment->urkundenabled = self::urkund_enabled($assid);
            $assignment->turnitinenabled = self::turnitin_enabled($assid);
        }

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
        if (!$groups = $DB->get_records_sql($sql, [$courseid, $userid])) {
            return '-';
        } else {
            $names = [];
            $ids = [];
            foreach ($groups as $group) {
                $names[] = shorten_text($group->name, 30);
                $ids[] = $group->id;
            }
            return [implode(', ', $names), $ids];
        }
    }

    /**
     * Get Urkund score
     * If multiple scores, get the latest
     * @param int $assid
     * @param int $cmid 
     * @param int $userid
     * @return int
     */
    protected static function get_urkund_score($assid, $cmid, $userid) {
        global $DB; 

        if (self::urkund_enabled($assid)) {
            if ($urkunds = $DB->get_records('plagiarism_urkund_files', ['cm' => $cmid, 'userid' => $userid, 'statuscode' => 'Analyzed'], 'id DESC')) {
                return reset($urkunds);
            } 
            if ($urkunds = $DB->get_records('plagiarism_urkund_files', ['cm' => $cmid, 'relateduserid' => $userid, 'statuscode' => 'Analyzed'], 'id DESC')) {
                return reset($urkunds);
            }  
            return null;
        } else {
            return null;
        }
    }

    /**
     * Get Turnitin score
     * If multiple scores, get the latest
     * @param int $assid
     * @param int $cmid 
     * @param int $userid
     * @return int
     */
    protected static function get_turnitin_score($assid, $cmid, $userid) {
        global $DB; 

        if (self::turnitin_enabled($assid)) {
            if ($turnitins = $DB->get_records('plagiarism_turnitin_files', ['cm' => $cmid, 'userid' => $userid, 'statuscode' => 'success'], 'id DESC')) {
                return reset($turnitins);
            }  
            return null;
        } else {
            return null;
        }
    }

    /**
     * Get file submission plugin 
     * and check it is enabled
     * @param object $assign
     * @return mixed
     */
    public static function get_submission_plugin_files($assign) {
        $filesubmission = $assign->get_submission_plugin_by_type('file');
        if ($filesubmission->is_enabled()) {
            return $filesubmission;
        } else {
            return null;
        }
    }

    /**
     * Get files for user
     * @param object $assign
     * @param object $filesubmission
     * @param object $submission
     * @param int $userid
     * @return array
     */
    protected static function get_submission_files($assign, $filesubmission, $submission, $userid) {
        if (!$filesubmission || !$submission) {
            return '-';
        }

        $fs = get_file_storage();
        $context = $assign->get_context();
        $files = $fs->get_area_files(
            $context->id,
            'assignsubmission_file',
            'submission_files',
            $submission->id
        );

        if ($files) {
            $filenames = [];
            foreach ($files as $file) {
                if ($file->get_filename() == '.') {
                    continue;
                }
                $filenames[] = shorten_text($file->get_filename(), FILENAME_SHORTEN);
            }
            return implode(', ', $filenames);
        }

        return '-';
    }
    
    /**
     * Get feedback
     * @param object $assign
     * @param object $filesubmission
     * @param object $submission
     * @param int $userid
     * @return array
     */
    protected static function get_feedback_files($assign, $filesubmission, $submission, $userid) {
        if (!$filesubmission || !$submission) {
            return '-';
        }

        $fs = get_file_storage();
        $context = $assign->get_context();
        //echo "<pre>"; var_dump($context); die;
        $files = $fs->get_area_files(
            $context->id,
            'assignsubmission_file',
            'submission_files',
            $submission->id
        );

        if ($files) {
            $filenames = [];
            foreach ($files as $file) {
                if ($file->get_filename() == '.') {
                    continue;
                }
                $filenames[] = $file->get_filename();
            }
            return implode(', ', $filenames);
        }

        return '-';
    }
    /**
     * Get the name of the allocated marker
     * @param object $grade
     * @return string
     */
    protected static function get_grader($grade) {
        global $DB;

        if (empty($grade)) {
            return '-';
        }

        if ($grade->grader > 0) {
            if ($user = $DB->get_record('user', ['id' => $grade->grader])) {
                return fullname($user);
            }
        }

        return '-';
    }

    /**
     * Get assign object
     * @param object $course
     * @param int $assignid
     * @return object
     */
    public static function get_assign($course, $assignid) {

        // get course module
        $cm = get_coursemodule_from_instance('assign', $assignid);

        // Create assignment object
        $coursemodulecontext = \context_module::instance($cm->id);
        $assign = new \assign($coursemodulecontext, $cm, $course);

        return $assign;
    }

    /**
     * Add assignment data
     * @param int $assid
     * @param int $cmid
     * @param assign $assign
     * @param array $submissions
     * @return array
     */
    public static function add_assignment_data($courseid, $assid, $cmid, $assign, $submissions) {

        // Report date format
        $dateformat = get_string('strftimedatetimeshort', 'langconfig');

        // Get sub plugins
        $filesubmission = self::get_submission_plugin_files($assign);

        // Get instance
        $instance = $assign->get_instance();

        foreach ($submissions as $submission) {
            $userid = $submission->id;
            if ($instance->teamsubmission) {
                $usersubmission = $assign->get_group_submission($userid, 0, false);
            } else {
                $usersubmission = $assign->get_user_submission($userid, false);
            }
            if ($usersubmission) {
                $submission->created = userdate($usersubmission->timecreated, $dateformat);
                $submission->modified = userdate($usersubmission->timemodified, $dateformat);
                $submission->status = $usersubmission->status;
                $submissionid = $usersubmission->id;
                $grade = $assign->get_user_grade($userid, false);
                $gradevalue = empty($grade) ? null : $grade->grade;
                $displaygrade = $assign->display_grade($gradevalue, false, $userid);
                $submission->grade = $displaygrade;
                $submission->grader = self::get_grader($grade);
            } else {
                $submission->created = '-';
                $submission->modified = '-';
                $submission->status = '-';
                $submission->grade = '-';
            }
            //echo "<pre>"; var_dump($usersubmission); die;
            list($submission->groups, $submission->groupids) = self::get_user_groups($userid, $courseid);
            $submission->urkund = self::get_urkund_score($assid, $cmid, $userid);
            $submission->turnitin = self::get_turnitin_score($assid, $cmid, $userid);
            $submission->files = self::get_submission_files($assign, $filesubmission, $usersubmission, $userid);
        }

        return $submissions;
    }

    /**
     * This function will take an int or an assignment instance and
     * return an assignment instance. It is just for convenience.
     * Stolen from private function in assignment code
     * @param int|\assign $assignment
     * @return assign
     */
    private static function get_assignment_from_param($assignment) {
        global $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        if (!is_object($assignment)) {
            $cm = \get_coursemodule_from_instance('assign', $assignment, 0, false, MUST_EXIST);
            $context = \context_module::instance($cm->id);

            $assignment = new \assign($context, null, null);
        }
        return $assignment;
    }

    /**
     * Get converted pdf files associated with user
     * @param int $assignid assignment id
     * @param int $userid id user id
     */
    public static function get_converted_files($assignid, $userid) {
        global $DB;

        $fs = get_file_storage();
        $context = \context_system::instance();
        $path = '/pdf/';

        // Get the possible assignment submission plugins
        $assignment = self::get_assignment_from_param($assignid);
        $plugins = $assignment->get_submission_plugins();

        // Get submission and user
        if ($assignment->get_instance()->teamsubmission) {
            $submission = $assignment->get_group_submission($userid, 0, false);
        } else {
            $submission = $assignment->get_user_submission($userid, false);
        }
        if (!$submission) {
            return false;
        }
        $user = $DB->get_record('user', array('id' => $userid));

        // run through active plugins
        $files = array();
        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $pluginfiles = $plugin->get_files($submission, $user);
                foreach ($pluginfiles as $filename => $file) {
                    if ($file instanceof \stored_file) {
                        if ($file->get_mimetype() !== 'application/pdf') {
                            $conversion = $fs->get_file($context->id, 'core', 'documentconversion', 0, $path, $file->get_contenthash());
                            if ($conversion) {
                                $files[$filename] = $conversion;
                            }
                        }
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Find combined pdf
     * @param int $assignid assignment id
     * @param int $userid id user id
     */
    public static function get_combined_pdf($assignid, $userid) {
        $fs = get_file_storage();
        $assignment = self::get_assignment_from_param($assignid);
        $grade = $assignment->get_user_grade($userid, true, -1);
        if ($assignment->get_instance()->teamsubmission) {
            $submission = $assignment->get_group_submission($userid, 0, false);
        } else {
            $submission = $assignment->get_user_submission($userid, false);
        }

        $contextid = $assignment->get_context()->id;
        $component = 'assignfeedback_editpdf';
        $filearea = document_services::COMBINED_PDF_FILEAREA;
        $itemid = $grade->id;
        $filepath = '/';
        $filename = document_services::COMBINED_PDF_FILENAME;

        $combinedpdf = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);

        return $combinedpdf;
    }

    /**
     * Delete converted PDFs
     * @param int $assignid assignment id
     * @param int $userid id user id
     */
    public static function delete_pdfs($assignid, $userid) {
        $fs = get_file_storage();
        $files = self::get_converted_files($assignid, $userid);
        foreach ($files as $file) {
            $file->delete();
        }

        // Now need to delete the combined pdf (the thing you mark) if there is one
        $combinedpdf = self::get_combined_pdf($assignid, $userid);
        if ($combinedpdf) {
            $combinedpdf->delete();
        }
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
                $filetime = empty($s->urkundtimesubmitted) ? $s->submission->timemodified : $s->urkundtimesubmitted;
                $record->date = date('d/m/Y H:i', $filetime);
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
                $userurl = new \moodle_url('/user/view.php', ['id' => $s->user->id, 'course' => $courseid]);
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

            // UserID
            $record->userid = $s->user->id;

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

            // Converted PDFs
            $record->convertedfiles = $s->convertedfiles;
      
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
        global $CFG, $DB;

        // Is plagiarism enabled?
        if (empty($CFG->enableplagiarism)) {
            return false;
        }

        // Is the Urkund plugin installed?
        $plugininfo = \core_plugin_manager::instance()->get_plugin_info('plagiarism_urkund');
        if (!$plugininfo) {
            return false;
        }
        
        $cm = get_coursemodule_from_instance('assign', $assignmentid);
        if ($urkund = $DB->get_record('plagiarism_urkund_config', ['cm' => $cm->id, 'name' => 'use_urkund'])) {
            if ($urkund->value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Is Turnitin enabled on this assignment
     * @param int $assignmentid
     * @return boolean
     */
    public static function turnitin_enabled($assignmentid) {
        global $CFG, $DB;

        // Is plagiarism enabled?
        if (empty($CFG->enableplagiarism)) {
            return false;
        }

        // Is the Urkund plugin installed?
        $plugininfo = \core_plugin_manager::instance()->get_plugin_info('plagiarism_turnitin');
        if (!$plugininfo) {
            return false;
        }
        
        $cm = get_coursemodule_from_instance('assign', $assignmentid);
        if ($turnitin = $DB->get_record('plagiarism_turnitin_config', ['cm' => $cm->id, 'name' => 'use_turnitin'])) {
            if ($turnitin->value) {
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

    /**
     * Create filename for zipfile
     * @param int $assignid
     * @return string zip file name
     */
    private static function get_zipfilename($assignid) {
        global $DB;

        // Get assignment and course
        $assign = $DB->get_record('assign', array('id' => $assignid), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $assign->course), '*', MUST_EXIST);

        // Construct out of bits
        $zipfilename = clean_filename(implode('_', array(
            $course->shortname,
            $assign->name,
        )));

        return $zipfilename . '.zip';
    }

    /**
     * Generate zip file from array of given files.
     *
     * @param array $filesforzipping - array of files to pass into archive_to_pathname.
     *                                 This array is indexed by the final file name and each
     *                                 element in the array is an instance of a stored_file object.
     * @return path of temp file - note this returned file does
     *         not have a .zip extension - it is a temp file.
     */
    private static function pack_files($filesforzipping) {
        global $CFG;

        // Create path for new zip file.
        $tempzip = tempnam($CFG->tempdir . '/', 'assignment_');

        // Zip files.
        $zipper = new zip_packer();
        if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
            return $tempzip;
        }
        return false;
    }

    /**
     * Download feedback files
     * @param int assignid
     */
    public static function feedback_files($assignid) {
        global $DB;

        $cm = get_coursemodule_from_instance('assign', $assignid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);

        // find the feedback files
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'assignfeedback_file', 'feedback_files');
        if (!$files) {
            return;
        }

        // Zipfiles array of 'path/name in zip' => file_object.
        $zipfiles = array(); 

        // Build zipfiles array
        foreach ($files as $f) {

            // Skip those pesky . files
            if ($f->get_filename() == '.') {
                continue;
            }

            // use itemid (maps to id in assign_grades) to track down user
            $grade = $DB->get_record('assign_grades', array('id' => $f->get_itemid()), '*', MUST_EXIST);
            $user = $DB->get_record('user', array('id' => $grade->userid), '*', MUST_EXIST);

            // Try to create a unique filename from idnumber or username
            $prefix = $user->idnumber ? $user->idnumber : $user->username;
            $filename = $prefix . '_' . $f->get_filename();
        
            $zipfiles[$filename] = $f;   
        }

        // No point if there are no files
        if (count($zipfiles) == 0) {
            return;
        }

        // Pack zip file for export
        if ($zip = self::pack_files($zipfiles)) {
            $zipfilename = self::get_zipfilename($assignid);
            send_temp_file($zip, $zipfilename);
        }
    }    

}
