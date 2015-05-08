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
 * lib.php - Contains Plagiarism plugin specific functions called by Modules.
 *
 * @since 2.0
 * @package    plagiarism_urkund
 * @subpackage plagiarism
 * @author     Dan Marsden <dan@danmarsden.com>
 * @copyright  2011 Dan Marsden http://danmarsden.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

// Get global class.
global $CFG;
require_once($CFG->dirroot.'/plagiarism/lib.php');
require_once($CFG->dirroot . '/lib/filelib.php');

// There is a new URKUND API - The Integration Service - we only currently use this to verify the receiver address.
// If we convert the existing calls to send file/get score we should move this to a config setting.
define('URKUND_INTEGRATION_SERVICE', 'https://secure.urkund.com/api');

define('URKUND_MAX_SUBMISSION_ATTEMPTS', 6); // Maximum number of times to try and send a submission to URKUND.
define('URKUND_MAX_SUBMISSION_DELAY', 60); // Maximum time to wait between submissions (defined in minutes).
define('URKUND_SUBMISSION_DELAY', 15); // Initial delay, doubled each time a check is made until the max_submission_delay is met.
define('URKUND_MAX_STATUS_ATTEMPTS', 10); // Maximum number of times to try and obtain the status of a submission.
define('URKUND_MAX_STATUS_DELAY', 1440); // Maximum time to wait between checks (defined in minutes).
define('URKUND_STATUS_DELAY', 30); // Initial delay, doubled each time a check is made until the max_status_delay is met.
define('URKUND_STATUSCODE_PROCESSED', '200');
define('URKUND_STATUSCODE_ACCEPTED', '202');
define('URKUND_STATUSCODE_ACCEPTED_OLD', '202-old'); // File submitted before we changed the way the identifiers were stored.
define('URKUND_STATUSCODE_BAD_REQUEST', '400');
define('URKUND_STATUSCODE_NOT_FOUND', '404');
define('URKUND_STATUSCODE_GONE', '410'); // Receiver is inactive or deleted.
define('URKUND_STATUSCODE_UNSUPPORTED', '415');
define('URKUND_STATUSCODE_TOO_LARGE', '413');
define('URKUND_STATUSCODE_NORECEIVER', '444');
define('URKUND_STATUSCODE_INVALID_RESPONSE', '613'); // Invalid response received from URKUND.

// Url to external xml that states URKUNDS allowed file type list.
define('URKUND_FILETYPE_URL', 'https://secure.urkund.com/ws/integration/accepted-formats.xml');

define('URKUND_FILETYPE_URL_UPDATE', '168'); // How often to check for updated file types (defined in hours).

define('PLAGIARISM_URKUND_SHOW_NEVER', 0);
define('PLAGIARISM_URKUND_SHOW_ALWAYS', 1);
define('PLAGIARISM_URKUND_SHOW_CLOSED', 2);

define('PLAGIARISM_URKUND_DRAFTSUBMIT_IMMEDIATE', 0);
define('PLAGIARISM_URKUND_DRAFTSUBMIT_FINAL', 1);


class plagiarism_plugin_urkund extends plagiarism_plugin {
    /**
     * This function should be used to initialise settings and check if plagiarism is enabled.
     *
     * @return mixed - false if not enabled, or returns an array of relevant settings.
     */
    static public function get_settings() {
        static $plagiarismsettings;
        if (!empty($plagiarismsettings) || $plagiarismsettings === false) {
            return $plagiarismsettings;
        }
        $plagiarismsettings = (array)get_config('plagiarism');
        // Check if enabled.
        if (isset($plagiarismsettings['urkund_use']) && $plagiarismsettings['urkund_use']) {
            // Now check to make sure required settings are set!
            if (empty($plagiarismsettings['urkund_api'])) {
                error("URKUND API URL not set!");
            }
            return $plagiarismsettings;
        } else {
            return false;
        }
    }
    /**
     * Function which returns an array of all the module instance settings.
     *
     * @return array
     *
     */
    public function config_options() {
        return array('use_urkund', 'urkund_show_student_score', 'urkund_show_student_report',
                     'urkund_draft_submit', 'urkund_receiver', 'urkund_studentemail');
    }
    /**
     * Hook to allow plagiarism specific information to be displayed beside a submission.
     * @param array  $linkarraycontains all relevant information for the plugin to generate a link.
     * @return string
     *
     */
    public function get_links($linkarray) {
        global $COURSE, $OUTPUT, $CFG;
        $cmid = $linkarray['cmid'];
        $userid = $linkarray['userid'];
        if (!empty($linkarray['content'])) {
            $filename = "content-" . $COURSE->id . "-" . $cmid . "-". $userid . ".htm";
            $filepath = $CFG->tempdir."/urkund/" . $filename;
            $file = new stdclass();
            $file->type = "tempurkund";
            $file->filename = $filename;
            $file->timestamp = time();
            $file->identifier = sha1($linkarray['content']);
            $file->filepath = $filepath;
        } else if (!empty($linkarray['file'])) {
            $file = new stdclass();
            $file->filename = $linkarray['file']->get_filename();
            $file->timestamp = time();
            $file->identifier = $linkarray['file']->get_contenthash();
            $file->filepath = $linkarray['file']->get_filepath();
        }
        $results = $this->get_file_results($cmid, $userid, $file);
        if (empty($results)) {
            // Info about this file is not available to this user.
            return '';
        }
        $modulecontext = context_module::instance($cmid);

        $output = '';
        if ($results['statuscode'] == 'pending') {
            // TODO: check to make sure there is a pending event entry for this file - if not add one.
            $output .= '<span class="plagiarismreport">'.
                       '<img src="'.$OUTPUT->pix_url('processing', 'plagiarism_urkund') .
                        '" alt="'.get_string('pending', 'plagiarism_urkund').'" '.
                        '" title="'.get_string('pending', 'plagiarism_urkund').'" />'.
                        '</span>';
            return $output;
        }
        if ($results['statuscode'] == 'Analyzed') {
            // Normal situation - URKUND has successfully analyzed the file.
            $rank = urkund_get_css_rank($results['score']);
            $output .= '<span class="plagiarismreport">';
            if (!empty($results['reporturl'])) {
                // User is allowed to view the report.
                // Score is contained in report, so they can see the score too.
                $output .= '<a href="'.$results['reporturl'].'" target="_blank">';
                $output .= get_string('similarity', 'plagiarism_urkund') . ':';
                $output .= '<span class="'.$rank.'">'.$results['score'].'%</span>';
                $output .= '</a>';
            } else if ($results['score'] !== '') {
                // User is allowed to view only the score.
                $output .= get_string('similarity', 'plagiarism_urkund') . ':';
                $output .= '<span class="' . $rank . '">' . $results['score'] . '%</span>';
            }
            if (!empty($results['optoutlink'])) {
                // Display opt-out link.
                $output .= '&nbsp;<span class"plagiarismoptout">' .
                        '<a href="' . $results['optoutlink'] . '" target="_blank">' .
                        get_string('optout', 'plagiarism_urkund') .
                        '</a></span>';
            }
            if (!empty($results['renamed'])) {
                $output .= $results['renamed'];
            }
            $output .= '</span>';
        } else if ($results['statuscode'] == URKUND_STATUSCODE_ACCEPTED) {
            $output .= '<span class="plagiarismreport">'.
                       '<img src="'.$OUTPUT->pix_url('processing', 'plagiarism_urkund') .
                        '" alt="'.get_string('processing', 'plagiarism_urkund').'" '.
                        '" title="'.get_string('processing', 'plagiarism_urkund').'" />'.
                        '</span>';
        } else if ($results['statuscode'] == URKUND_STATUSCODE_UNSUPPORTED) {
            $output .= '<span class="plagiarismreport">'.
                       '<img src="'.$OUTPUT->pix_url('warning', 'plagiarism_urkund') .
                        '" alt="'.get_string('unsupportedfiletype', 'plagiarism_urkund').'" '.
                        '" title="'.get_string('unsupportedfiletype', 'plagiarism_urkund').'" />'.
                        '</span>';
        } else if ($results['statuscode'] == URKUND_STATUSCODE_TOO_LARGE) {
            $output .= '<span class="plagiarismreport">'.
                       '<img src="'.$OUTPUT->pix_url('warning', 'plagiarism_urkund') .
                        '" alt="'.get_string('toolarge', 'plagiarism_urkund').'" '.
                        '" title="'.get_string('toolarge', 'plagiarism_urkund').'" />'.
                        '</span>';
        } else {
            $title = get_string('unknownwarning', 'plagiarism_urkund');
            $reset = '';
            if (has_capability('plagiarism/urkund:resetfile', $modulecontext) &&
                !empty($results['error'])) { // This is a teacher viewing the responses.
                // Strip out some possible known text to tidy it up.
                $erroresponse = format_text($results['error'], FORMAT_PLAIN);
                $erroresponse = str_replace('{&quot;LocalisedMessage&quot;:&quot;', '', $erroresponse);
                $erroresponse = str_replace('&quot;,&quot;Message&quot;:null}', '', $erroresponse);
                $title .= ': ' . $erroresponse;
                $url = new moodle_url('/plagiarism/urkund/reset.php', array('cmid' => $cmid, 'pf' => $results['pid'],
                                                                            'sesskey' => sesskey()));
                $reset = "<a href='$url'>".get_string('reset')."</a>";
            }
            $output .= '<span class="plagiarismreport">'.
                       '<img src="'.$OUTPUT->pix_url('warning', 'plagiarism_urkund') .
                        '" alt="'.get_string('unknownwarning', 'plagiarism_urkund').'" '.
                        '" title="'.$title.'" />'.$reset.'</span>';
        }
        return $output;
    }

    public function get_file_results($cmid, $userid, $file) {
        global $DB, $USER, $CFG;
        $plagiarismsettings = $this->get_settings();
        if (empty($plagiarismsettings)) {
            // Urkund is not enabled.
            return false;
        }
        $plagiarismvalues = urkund_cm_use($cmid);
        if (empty($plagiarismvalues)) {
            // Urkund not enabled for this cm.
            return false;
        }

        // Collect detail about the specified coursemodule.
        $filehash = $file->identifier;
        $modulesql = 'SELECT m.id, m.name, cm.instance'.
                ' FROM {course_modules} cm' .
                ' INNER JOIN {modules} m on cm.module = m.id ' .
                'WHERE cm.id = ?';
        $moduledetail = $DB->get_record_sql($modulesql, array($cmid));
        if (!empty($moduledetail)) {
            $sql = "SELECT * FROM " . $CFG->prefix . $moduledetail->name . " WHERE id= ?";
            $module = $DB->get_record_sql($sql, array($moduledetail->instance));
        }
        if (empty($module)) {
            // No such cmid.
            return false;
        }

        $modulecontext = context_module::instance($cmid);
        // If the user has permission to see result of all items in this course module.
        $viewscore = $viewreport = has_capability('plagiarism/urkund:viewreport', $modulecontext);

        // Determine if the activity is closed.
        // If report is closed, this can make the report available to more users.
        $assignclosed = false;
        $time = time();
        if (!empty($module->preventlate) && !empty($module->timedue)) {
            $assignclosed = ($module->timeavailable <= $time && $time <= $module->timedue);
        } else if (!empty($module->timeavailable)) {
            $assignclosed = ($module->timeavailable <= $time);
        }

        // Under certain circumstances, users are allowed to see plagiarism info
        // even if they don't have view report capability.
        if ($USER->id == $userid) {
            $selfreport = true;
            if (isset($plagiarismvalues['urkund_show_student_report']) &&
                    ($plagiarismvalues['urkund_show_student_report'] == PLAGIARISM_URKUND_SHOW_ALWAYS ||
                     $plagiarismvalues['urkund_show_student_report'] == PLAGIARISM_URKUND_SHOW_CLOSED && $assignclosed)) {
                $viewreport = true;
            }
            if (isset($plagiarismvalues['urkund_show_student_score']) &&
                    ($plagiarismvalues['urkund_show_student_score'] == PLAGIARISM_URKUND_SHOW_ALWAYS) ||
                    ($plagiarismvalues['urkund_show_student_score'] == PLAGIARISM_URKUND_SHOW_CLOSED && $assignclosed)) {
                $viewscore = true;
            }
        } else {
            $selfreport = false;
        }
        // End of rights checking.

        if (!$viewscore && !$viewreport && !$selfreport) {
            // User is not permitted to see any details.
            return false;
        }
        $plagiarismfile = $DB->get_record_sql(
                    "SELECT * FROM {plagiarism_urkund_files}
                    WHERE cm = ? AND userid = ? AND " .
                    "identifier = ?",
                    array($cmid, $userid, $filehash));
        if (empty($plagiarismfile)) {
            // No record of that submitted file.
            return false;
        }

        // Returns after this point will include a result set describing information about
        // interactions with urkund servers.
        $results = array('statuscode' => '', 'error' => '', 'reporturl' => '',
                'score' => '', 'pid' => '', 'optoutlink' => '', 'renamed' => '',
                'analyzed' => 0,
                );
        if ($plagiarismfile->statuscode == 'pending') {
            $results['statuscode'] = 'pending';
            return $results;
        }

        // Now check for differing filename and display info related to it.
        $previouslysubmitted = '';
        if ($file->filename !== $plagiarismfile->filename) {
            $previouslysubmitted = '('.get_string('previouslysubmitted', 'plagiarism_urkund').': '.$plagiarismfile->filename.')';
        }

        $results['statuscode'] = $plagiarismfile->statuscode;
        $results['pid'] = $plagiarismfile->id;
        $results['error'] = $plagiarismfile->errorresponse;
        if ($plagiarismfile->statuscode == 'Analyzed') {
            $results['analyzed'] = 1;
            // File has been successfully analyzed - return all appropriate details.
            if ($viewscore || $viewreport) {
                // If user can see the report, they can see the score on the report
                // so make it directly available.
                $results['score'] = $plagiarismfile->similarityscore;
            }
            if ($viewreport) {
                $results['reporturl'] = $plagiarismfile->reporturl;
            }
            if (!empty($plagiarismfile->optout) && $selfreport) {
                $results['optoutlink'] = $plagiarismfile->optout;
            }
            $results['renamed'] = $previouslysubmitted;
        }
        return $results;
    }
    /* Hook to save plagiarism specific settings on a module settings page.
     * @param object $data - data from an mform submission.
    */
    public function save_form_elements($data) {
        global $DB;
        if (!$this->get_settings()) {
            return;
        }
        if (isset($data->use_urkund)) {
            // Array of possible plagiarism config options.
            $plagiarismelements = $this->config_options();
            // First get existing values.
            $existingelements = $DB->get_records_menu('plagiarism_urkund_config', array('cm' => $data->coursemodule),
                                                      '', 'name, id');
            foreach ($plagiarismelements as $element) {
                $newelement = new stdClass();
                $newelement->cm = $data->coursemodule;
                $newelement->name = $element;
                $newelement->value = (isset($data->$element) ? $data->$element : 0);
                if (isset($existingelements[$element])) {
                    $newelement->id = $existingelements[$element];
                    $DB->update_record('plagiarism_urkund_config', $newelement);
                } else {
                    $DB->insert_record('plagiarism_urkund_config', $newelement);
                }

            }
            if (!empty($data->urkund_receiver)) {
                set_user_preference('urkund_receiver', trim($data->urkund_receiver));
            }
        }
    }

    /**
     * hook to add plagiarism specific settings to a module settings page
     * @param object $mform  - Moodle form
     * @param object $context - current context
     */
    public function get_form_elements_module($mform, $context, $modulename = "") {
        global $DB, $PAGE, $CFG;
        $plagiarismsettings = $this->get_settings();
        if (!$plagiarismsettings) {
            return;
        }
        $cmid = optional_param('update', 0, PARAM_INT); // Get cm as $this->_cm is not available here.
        if (!empty($modulename)) {
            $modname = 'urkund_enable_' . $modulename;
            if (empty($plagiarismsettings[$modname])) {
                return;             // Return if urkund is not enabled for the module.
            }
        }
        if (!empty($cmid)) {
            $plagiarismvalues = $DB->get_records_menu('plagiarism_urkund_config', array('cm' => $cmid), '', 'name, value');
        }
        // Get Defaults - cmid(0) is the default list.
        $plagiarismdefaults = $DB->get_records_menu('plagiarism_urkund_config', array('cm' => 0), '', 'name, value');
        $plagiarismelements = $this->config_options();
        if (has_capability('plagiarism/urkund:enable', $context)) {
            urkund_get_form_elements($mform);
            if ($mform->elementExists('urkund_draft_submit')) {
                if ($mform->elementExists('var4')) {
                    $mform->disabledIf('urkund_draft_submit', 'var4', 'eq', 0);
                } else if ($mform->elementExists('submissiondrafts')) {
                    $mform->disabledIf('urkund_draft_submit', 'submissiondrafts', 'eq', 0);
                }
            }
            // Disable all plagiarism elements if use_plagiarism eg 0.
            foreach ($plagiarismelements as $element) {
                if ($element <> 'use_urkund') { // Ignore this var.
                    $mform->disabledIf($element, 'use_urkund', 'eq', 0);
                }
            }
            // Check if files have been submitted and we need to disable the receiver address.
            if ($DB->record_exists('plagiarism_urkund_files', array('cm' => $cmid, 'statuscode' => 'pending'))) {
                $mform->disabledIf('urkund_receiver', 'use_urkund');
            }
        } else { // Add plagiarism settings as hidden vars.
            foreach ($plagiarismelements as $element) {
                $mform->addElement('hidden', $element);
                $mform->setType('use_urkund', PARAM_INT);
                $mform->setType('urkund_show_student_score', PARAM_INT);
                $mform->setType('urkund_show_student_report', PARAM_INT);
                $mform->setType('urkund_draft_submit', PARAM_INT);
                $mform->setType('urkund_receiver', PARAM_TEXT);
                $mform->setType('urkund_studentemail', PARAM_INT);
            }
        }
        // Now set defaults.
        foreach ($plagiarismelements as $element) {
            if (isset($plagiarismvalues[$element])) {
                $mform->setDefault($element, $plagiarismvalues[$element]);
            } else if ($element == 'urkund_receiver') {
                $def = get_user_preferences($element);
                if (!empty($def)) {
                    $mform->setDefault($element, $def);
                } else if (isset($plagiarismdefaults[$element])) {
                    $mform->setDefault($element, $plagiarismdefaults[$element]);
                }
            } else if (isset($plagiarismdefaults[$element])) {
                $mform->setDefault($element, $plagiarismdefaults[$element]);
            }
        }
        $mform->registerRule('urkundvalidatereceiver', null, 'urkundvalidatereceiver',
                             $CFG->dirroot.'/plagiarism/urkund/form_customrule.php');
        $mform->addRule('urkund_receiver', get_string('receivernotvalid', 'plagiarism_urkund'), 'urkundvalidatereceiver');

        // Now add JS to validate receiver indicator using Ajax.
        if (has_capability('plagiarism/urkund:enable', $context)) {
            $jsmodule = array(
                'name' => 'plagiarism_urkund',
                'fullpath' => '/plagiarism/urkund/checkreceiver.js',
                'requires' => array('json'),
            );
            $PAGE->requires->js_init_call('M.plagiarism_urkund.init', array($context->instanceid), true, $jsmodule);
        }
    }

    /**
     * Hook to allow a disclosure to be printed notifying users what will happen with their submission.
     * @param int $cmid - course module id
     * @return string
     */
    public function print_disclosure($cmid) {
        global $OUTPUT;

        $outputhtml = '';

        $urkunduse = urkund_cm_use($cmid);
        $plagiarismsettings = $this->get_settings();
        if (!empty($plagiarismsettings['urkund_student_disclosure']) &&
            !empty($urkunduse)) {
                $outputhtml .= $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
                $formatoptions = new stdClass;
                $formatoptions->noclean = true;
                $outputhtml .= format_text($plagiarismsettings['urkund_student_disclosure'], FORMAT_MOODLE, $formatoptions);
                $outputhtml .= $OUTPUT->box_end();
        }
        return $outputhtml;
    }

    /**
     * Hook to allow status of submitted files to be updated - called on grading/report pages.
     *
     * @param object $course - full Course object
     * @param object $cm - full cm object
     * @return string
     */
    public function update_status($course, $cm) {
        // Called at top of submissions/grading pages - allows printing of admin style links or updating status.
        return '';
    }

    /**
     * Called by admin/cron.php.
     *
     */
    public function cron() {
        global $CFG;
        // Do any scheduled task stuff.
        urkund_update_allowed_filetypes();
        // Weird hack to include filelib correctly before allowing use in event_handler.
        require_once($CFG->libdir.'/filelib.php');
        if ($plagiarismsettings = $this->get_settings()) {
            urkund_get_scores($plagiarismsettings);
        }
    }
    /**
     * Generic handler function for all events - triggers sending of files.
     * @return boolean
     */
    public function event_handler($eventdata) {
        global $DB, $CFG;

        $supportedevents = urkund_supported_events();
        if (!in_array($eventdata->eventtype, $supportedevents)) {
            return true; // Don't need to handle this event.
        }

        $plagiarismsettings = $this->get_settings();
        if (!$plagiarismsettings) {
            return true;
        }
        $cmid = (!empty($eventdata->cm->id)) ? $eventdata->cm->id : $eventdata->cmid;
        $plagiarismvalues = $DB->get_records_menu('plagiarism_urkund_config', array('cm' => $cmid), '', 'name, value');
        if (empty($plagiarismvalues['use_urkund'])) {
            // Urkund not in use for this cm - return.
            return true;
        }

        // Check if the module associated with this event still exists.
        if (!$DB->record_exists('course_modules', array('id' => $eventdata->cmid))) {
            return true;
        }

        if ($eventdata->eventtype == 'files_done' ||
            $eventdata->eventtype == 'content_done' ||
            ($eventdata->eventtype == 'assessable_submitted' && $eventdata->params['submission_editable'] == false)) {
            // Assignment-specific functionality:
            // This is a 'finalize' event. No files from this event itself,
            // but need to check if files from previous events need to be submitted for processing.
            mtrace("finalise");
            $result = true;
            if (isset($plagiarismvalues['urkund_draft_submit']) &&
                $plagiarismvalues['urkund_draft_submit'] == PLAGIARISM_URKUND_DRAFTSUBMIT_FINAL) {
                // Any files attached to previous events were not submitted.
                // These files are now finalized, and should be submitted for processing.
                if ($eventdata->modulename == 'assignment') {
                    // Hack to include filelib so that file_storage class is available.
                    require_once("$CFG->dirroot/mod/assignment/lib.php");
                    // We need to get a list of files attached to this assignment and put them in an array, so that
                    // we can submit each of them for processing.
                    $assignmentbase = new assignment_base($cmid);
                    $submission = $assignmentbase->get_submission($eventdata->userid);
                    $modulecontext = context_module::instance($eventdata->cmid);
                    $fs = get_file_storage();
                    if ($files = $fs->get_area_files($modulecontext->id, 'mod_assignment', 'submission', $submission->id,
                                                     "timemodified", false)) {
                        foreach ($files as $file) {
                            $sendresult = urkund_send_file($cmid, $eventdata->userid, $file, $plagiarismsettings);
                            $result = $result && $sendresult;
                        }
                    }
                } else if ($eventdata->modulename == 'assign') {
                    require_once("$CFG->dirroot/mod/assign/locallib.php");
                    require_once("$CFG->dirroot/mod/assign/submission/file/locallib.php");

                    $modulecontext = context_module::instance($eventdata->cmid);
                    $fs = get_file_storage();
                    if ($files = $fs->get_area_files($modulecontext->id, 'assignsubmission_file',
                                                     ASSIGNSUBMISSION_FILE_FILEAREA, $eventdata->itemid, "id", false)) {
                        foreach ($files as $file) {
                            $sendresult = urkund_send_file($cmid, $eventdata->userid, $file, $plagiarismsettings);
                            $result = $result && $sendresult;
                        }
                    }
                    $submission = $DB->get_record('assignsubmission_onlinetext', array('submission' => $eventdata->itemid));
                    if (!empty($submission)) {
                        $eventdata->content = trim(format_text($submission->onlinetext, $submission->onlineformat,
                                                               array('context' => $modulecontext)));
                        $file = urkund_create_temp_file($cmid, $eventdata);
                        $sendresult = urkund_send_file($cmid, $eventdata->userid, $file, $plagiarismsettings);
                        $result = $result && $sendresult;
                        unlink($file->filepath); // Delete temp file.
                    }
                }
            }
            return $result;
        }

        if (isset($plagiarismvalues['urkund_draft_submit']) &&
            $plagiarismvalues['urkund_draft_submit'] == PLAGIARISM_URKUND_DRAFTSUBMIT_FINAL) {
            // Assignment-specific functionality:
            // Files should only be sent for checking once "finalized".
            return true;
        }

        // Text is attached.
        $result = true;
        if (!empty($eventdata->content)) {
            $file = urkund_create_temp_file($cmid, $eventdata);
            $sendresult = urkund_send_file($cmid, $eventdata->userid, $file, $plagiarismsettings);
            $result = $result && $sendresult;
            unlink($file->filepath); // Delete temp file.
        }

        // Normal situation: 1 or more assessable files attached to event, ready to be checked.
        if (!empty($eventdata->pathnamehashes)) {
            foreach ($eventdata->pathnamehashes as $hash) {
                $fs = get_file_storage();
                $efile = $fs->get_file_by_hash($hash);

                if (empty($efile)) {
                    mtrace("nofilefound!");
                    continue;
                } else if ($efile->get_filename() === '.') {
                    // This 'file' is actually a directory - nothing to submit.
                    continue;
                }
                // Check if assign group submission is being used.
                if ($eventdata->modulename == 'assign') {
                    require_once("$CFG->dirroot/mod/assign/locallib.php");
                    $modulecontext = context_module::instance($eventdata->cmid);
                    $assign = new assign($modulecontext, false, false);
                    if (!empty($assign->get_instance()->teamsubmission)) {
                        $mygroups = groups_get_user_groups($assign->get_course()->id, $eventdata->userid);
                        if (count($mygroups) == 1) {
                            $groupid = reset($mygroups)[0];
                            // Only users with single groups are supported - otherwise just use the normal userid on this record.
                            // Get all users from this group.
                            $userids = array();
                            $users = groups_get_members($groupid, 'u.id');
                            foreach ($users as $u) {
                                $userids[] = $u->id;
                            }
                            // Find the earliest plagiarism record for this cm with any of these users.
                            $sql ='cm = ? AND userid IN ('.implode(',', $userids).')';
                            $previousfiles = $DB->get_records_select('plagiarism_urkund_files', $sql, array($eventdata->cmid), 'id');
                            $sanitycheckusers = 10; // Search through this number of users to find a valid previous submission.
                            $i = 0;
                            foreach ($previousfiles as $pf) {
                                if ($pf->userid == $eventdata->userid) {
                                    break; // The submission comes from this user so break.
                                }
                                // Sanity Check to make sure the user isn't in multiple groups.
                                $pfgroups = groups_get_user_groups($assign->get_course()->id, $pf->userid);
                                if (count($pfgroups) == 1) {
                                    // This user made the first valid submission so use their id when sending the file.
                                    $eventdata->userid = $pf->userid;
                                    break;
                                }
                                if ($i >= $sanitycheckusers) {
                                    // don't cause a massive loop here and break at a sensible limit.
                                    break;
                                }
                                $i++;
                            }
                        }
                    }
                }

                $sendresult = urkund_send_file($cmid, $eventdata->userid, $efile, $plagiarismsettings);
                $result = $result && $sendresult;
            }
        }
        return $result;
    }

    public function urkund_send_student_email($plagiarismfile) {
        global $DB, $CFG;
        if (empty($plagiarismfile->userid)) { // Sanity check.
            return false;
        }
        $user = $DB->get_record('user', array('id' => $plagiarismfile->userid));
        $site = get_site();
        $a = new stdClass();
        $cm = get_coursemodule_from_id('', $plagiarismfile->cm);
        $a->modulename = format_string($cm->name);
        $a->modulelink = $CFG->wwwroot.'/mod/'.$cm->modname.'/view.php?id='.$cm->id;
        $a->coursename = format_string($DB->get_field('course', 'fullname', array('id' => $cm->course)));
        $a->optoutlink = $plagiarismfile->optout;
        $emailsubject = get_string('studentemailsubject', 'plagiarism_urkund');
        $emailcontent = get_string('studentemailcontent', 'plagiarism_urkund', $a);
        email_to_user($user, $site->shortname, $emailsubject, $emailcontent);
    }

    // Function to validate the receiver address.
    public function validate_receiver($receiver) {
        $plagiarismsettings = $this->get_settings();
        $url = URKUND_INTEGRATION_SERVICE .'/receivers'.'/'. trim($receiver);;

        $headers = array('Accept-Language: '.$plagiarismsettings['urkund_lang']);

        $allowedstatus = array(URKUND_STATUSCODE_PROCESSED,
                               URKUND_STATUSCODE_NOT_FOUND,
                               URKUND_STATUSCODE_BAD_REQUEST,
                               URKUND_STATUSCODE_GONE);

        // Use Moodle curl wrapper.
        $c = new curl(array('proxy' => true));
        $c->setopt(array());
        $c->setopt(array('CURLOPT_RETURNTRANSFER' => 1,
                         'CURLOPT_HTTPAUTH' => CURLAUTH_BASIC,
                         'CURLOPT_USERPWD' => $plagiarismsettings['urkund_username'].":".$plagiarismsettings['urkund_password']));

        $c->setHeader($headers);
        $response = $c->get($url);
        $httpstatus = $c->info['http_code'];
        if (!empty($httpstatus)) {
            if (in_array($httpstatus, $allowedstatus)) {
                if ($httpstatus == URKUND_STATUSCODE_PROCESSED) {
                    // Valid address found, return true.
                    return true;
                } else {
                    return $httpstatus;
                }
            }
        }
        return false;
    }
}

function urkund_create_temp_file($cmid, $eventdata) {
    global $CFG;
    if (!check_dir_exists($CFG->tempdir."/urkund", true, true)) {
        mkdir($CFG->tempdir."/urkund", 0700);
    }
    $filename = "content-" . $eventdata->courseid . "-" . $cmid . "-" . $eventdata->userid . ".htm";
    $filepath = $CFG->tempdir."/urkund/" . $filename;
    $fd = fopen($filepath, 'wb');   // Create if not exist, write binary.

    // Write html and body tags as it seems that Urkund doesn't works well without them.
    $content = '<html>' .
               '<head>' .
               '<meta charset="UTF-8">' .
               '</head>' .
               '<body>' .
               $eventdata->content .
               '</body></html>';

    fwrite($fd, $content);
    fclose($fd);
    $file = new stdclass();
    $file->type = "tempurkund";
    $file->filename = $filename;
    $file->timestamp = time();
    $file->identifier = sha1($eventdata->content);
    $file->filepath = $filepath;
    return $file;
}

function urkund_event_file_uploaded($eventdata) {
    $eventdata->eventtype = 'file_uploaded';
    $urkund = new plagiarism_plugin_urkund();
    return $urkund->event_handler($eventdata);
}
function urkund_event_files_done($eventdata) {
    $eventdata->eventtype = 'files_done';
    $urkund = new plagiarism_plugin_urkund();
    return $urkund->event_handler($eventdata);
}

function urkund_event_content_uploaded($eventdata) {
    $eventdata->eventtype = 'content_uploaded';
    $urkund = new plagiarism_plugin_urkund();
    return $urkund->event_handler($eventdata);
}

function urkund_event_content_done($eventdata) {
    $eventdata->eventtype = 'content_done';
    $urkund = new plagiarism_plugin_urkund();
    return $urkund->event_handler($eventdata);
}

function urkund_event_assessable_submitted($eventdata) {
    $eventdata->eventtype = 'assessable_submitted';
    $urkund = new plagiarism_plugin_urkund();
    return $urkund->event_handler($eventdata);
}

function urkund_event_mod_created($eventdata) {
    $result = true;
        // A new module has been created - this is a generic event that is called for all module types
        // make sure you check the type of module before handling if needed.

    return $result;
}

function urkund_event_mod_updated($eventdata) {
    $result = true;
        // A module has been updated - this is a generic event that is called for all module types
        // make sure you check the type of module before handling if needed.

    return $result;
}

function urkund_event_mod_deleted($eventdata) {
    $result = true;
        // A module has been deleted - this is a generic event that is called for all module types
        // make sure you check the type of module before handling if needed.

    return $result;
}

function urkund_supported_events() {
    $supportedevents = array('file_uploaded', 'files_done', 'content_uploaded', 'content_done', 'assessable_submitted');
    return $supportedevents;
}

/**
 * Adds the list of plagiarism settings to a form.
 *
 * @param object $mform - Moodle form object.
 */
function urkund_get_form_elements($mform) {
    $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
    $tiioptions = array(0 => get_string("never"), 1 => get_string("always"),
                        2 => get_string("showwhenclosed", "plagiarism_urkund"));
    $urkunddraftoptions = array(
            PLAGIARISM_URKUND_DRAFTSUBMIT_IMMEDIATE => get_string("submitondraft", "plagiarism_urkund"),
            PLAGIARISM_URKUND_DRAFTSUBMIT_FINAL => get_string("submitonfinal", "plagiarism_urkund")
            );

    $mform->addElement('header', 'plagiarismdesc', get_string('urkund', 'plagiarism_urkund'));
    $mform->addElement('select', 'use_urkund', get_string("useurkund", "plagiarism_urkund"), $ynoptions);
    $mform->addElement('text', 'urkund_receiver', get_string("urkund_receiver", "plagiarism_urkund"), array('size' => 40));
    $mform->addHelpButton('urkund_receiver', 'urkund_receiver', 'plagiarism_urkund');
    $mform->setType('urkund_receiver', PARAM_TEXT);
    $mform->addElement('select', 'urkund_show_student_score',
                       get_string("urkund_show_student_score", "plagiarism_urkund"), $tiioptions);
    $mform->addHelpButton('urkund_show_student_score', 'urkund_show_student_score', 'plagiarism_urkund');
    $mform->addElement('select', 'urkund_show_student_report',
                       get_string("urkund_show_student_report", "plagiarism_urkund"), $tiioptions);
    $mform->addHelpButton('urkund_show_student_report', 'urkund_show_student_report', 'plagiarism_urkund');
    if ($mform->elementExists('var4') ||
        $mform->elementExists('submissiondrafts')) {
        $mform->addElement('select', 'urkund_draft_submit',
                           get_string("urkund_draft_submit", "plagiarism_urkund"), $urkunddraftoptions);
    }
    $mform->addElement('select', 'urkund_studentemail', get_string("urkund_studentemail", "plagiarism_urkund"), $ynoptions);
    $mform->addHelpButton('urkund_studentemail', 'urkund_studentemail', 'plagiarism_urkund');
}

/**
 * Updates a urkund_files record.
 *
 * @param int $cmid - course module id
 * @param int $userid - user id
 * @param varied $identifier - identifier for this plagiarism record - hash of file, id of quiz question etc
 * @return int - id of urkund_files record
 */
function urkund_get_plagiarism_file($cmid, $userid, $file) {
    global $DB;

    $filehash = (!empty($file->identifier)) ? $file->identifier : $file->get_contenthash();
    // Now update or insert record into urkund_files.
    $plagiarismfile = $DB->get_record_sql(
                                "SELECT * FROM {plagiarism_urkund_files}
                                 WHERE cm = ? AND userid = ? AND " .
                                "identifier = ?",
                                array($cmid, $userid, $filehash));
    if (!empty($plagiarismfile)) {
            return $plagiarismfile;
    } else {
        $plagiarismfile = new stdClass();
        $plagiarismfile->cm = $cmid;
        $plagiarismfile->userid = $userid;
        $plagiarismfile->identifier = $filehash;
        $plagiarismfile->filename = (!empty($file->filename)) ? $file->filename : $file->get_filename();
        $plagiarismfile->statuscode = 'pending';
        $plagiarismfile->attempt = 0;
        $plagiarismfile->timesubmitted = time();
        if (!$pid = $DB->insert_record('plagiarism_urkund_files', $plagiarismfile)) {
            debugging("insert into urkund_files failed");
        }
        $plagiarismfile->id = $pid;
        return $plagiarismfile;
    }
}
function urkund_send_file($cmid, $userid, $file, $plagiarismsettings) {
    global $DB;
    $plagiarismfile = urkund_get_plagiarism_file($cmid, $userid, $file);

    // Check if $plagiarismfile actually needs to be submitted.
    if ($plagiarismfile->statuscode <> 'pending') {
        return true;
    }
    $filename = (!empty($file->filename)) ? $file->filename : $file->get_filename();
    if ($plagiarismfile->filename !== $filename) {
        // This is a file that was previously submitted and not sent to urkund but the filename has changed so fix it.
        $plagiarismfile->filename = $filename;
        $DB->update_record('plagiarism_urkund_files', $plagiarismfile);
    }
    // Check to see if this is a valid file.
    $mimetype = urkund_check_file_type($filename);
    if (empty($mimetype)) {
        $plagiarismfile->statuscode = URKUND_STATUSCODE_UNSUPPORTED;
        $DB->update_record('plagiarism_urkund_files', $plagiarismfile);
        return true;
    }
    // Increment attempt number.
    $plagiarismfile->attempt = $plagiarismfile->attempt++;
    $DB->update_record('plagiarism_urkund_files', $plagiarismfile);

    return urkund_send_file_to_urkund($plagiarismfile, $plagiarismsettings, $file);
}
// Function to check timesubmitted and attempt to see if we need to delay an API check.
// also checks max attempts to see if it has exceeded.
function urkund_check_attempt_timeout($plagiarismfile) {
    global $DB;
    // The first time a file is submitted we don't need to wait at all.
    if (empty($plagiarismfile->attempt) && $plagiarismfile->statuscode == 'pending') {
        return true;
    }
    $now = time();
    // Set some initial defaults.
    $submissiondelay = 15;
    $maxsubmissiondelay = 60;
    $maxattempts = 4;
    if ($plagiarismfile->statuscode == 'pending') {
        $submissiondelay = URKUND_SUBMISSION_DELAY; // Initial delay,doubled each time a check is made until the max delay is met.
        $maxsubmissiondelay = URKUND_MAX_SUBMISSION_DELAY; // Maximum time to wait between submissions
        $maxattempts = URKUND_MAX_SUBMISSION_ATTEMPTS; // Maximum number of times to try and send a submission.
    } else if ($plagiarismfile->statuscode == URKUND_STATUSCODE_ACCEPTED) {
        $submissiondelay = URKUND_STATUS_DELAY; // Initial delay, doubled each time a check is made until the max delay is met.
        $maxsubmissiondelay = URKUND_MAX_STATUS_DELAY; // Maximum time to wait between checks
        $maxattempts = URKUND_MAX_STATUS_ATTEMPTS; // Maximum number of times to try and send a submission.
    }
    $wait = $submissiondelay;
    // Check if we have exceeded the max attempts.
    if ($plagiarismfile->attempt > $maxattempts) {
        $plagiarismfile->statuscode = 'timeout';
        $DB->update_record('plagiarism_urkund_files', $plagiarismfile);
        return true; // Return true to cancel the event.
    }
    // Now calculate wait time.
    $i = 0;
    $delay = 0;
    while ($i < $plagiarismfile->attempt) {
        $delay = $submissiondelay * pow(2,$i);
        if ($delay > $maxsubmissiondelay) {
            $delay = $maxsubmissiondelay;
        }
        $wait += $delay;
        $i++;
    }
    $wait = (int)$wait * 60;
    $timetocheck = (int)($plagiarismfile->timesubmitted + $wait);
    // Calculate when this should be checked next.

    if ($timetocheck < $now) {
        return true;
    } else {
        return false;
    }
}

function urkund_send_file_to_urkund($plagiarismfile, $plagiarismsettings, $file) {
    global $DB;

    $allowedstatus = array(URKUND_STATUSCODE_ACCEPTED,
                           URKUND_STATUSCODE_NOT_FOUND,
                           URKUND_STATUSCODE_TOO_LARGE,
                           URKUND_STATUSCODE_BAD_REQUEST,
                           URKUND_STATUSCODE_UNSUPPORTED);

    $filename = (!empty($file->filename)) ? $file->filename : $file->get_filename();
    $mimetype = urkund_check_file_type($filename);
    if (empty($mimetype)) {// Sanity check on filetype - this should already have been checked.
        $plagiarismfile->statuscode = URKUND_STATUSCODE_UNSUPPORTED;
        $DB->update_record('plagiarism_urkund_files', $plagiarismfile);
        return true;
    }
    mtrace("sendfile".$plagiarismfile->id);
    $useremail = $DB->get_field('user', 'email', array('id' => $plagiarismfile->userid));
    // Get url of api.
    $url = urkund_get_url($plagiarismsettings['urkund_api'], $plagiarismfile);
    if (empty($url)) {
        mtrace('ERROR: no receiver address found for this cm: '.$plagiarismfile->cm. ' Skipping file');
        $plagiarismfile->statuscode = URKUND_STATUSCODE_NORECEIVER;
        $plagiarismfile->errorresponse = get_string('noreceiver', 'plagiarism_urkund');
        $DB->update_record('plagiarism_urkund_files', $plagiarismfile);
        return true;
    }

    $headers = array('x-urkund-submitter: '.$useremail,
                    'Accept-Language: '.$plagiarismsettings['urkund_lang'],
                    'x-urkund-filename: '.base64_encode($filename),
                    'Content-Type: '.$mimetype);

    // Use Moodle curl wrapper to send file.
    $c = new curl(array('proxy' => true));
    $c->setopt(array());
    $c->setopt(array('CURLOPT_RETURNTRANSFER' => 1,
                     'CURLOPT_HTTPAUTH' => CURLAUTH_BASIC,
                     'CURLOPT_USERPWD' => $plagiarismsettings['urkund_username'].":".$plagiarismsettings['urkund_password']));

    $c->setHeader($headers);
    $filecontents = (!empty($file->filepath)) ? file_get_contents($file->filepath) : $file->get_content();
    $response = $c->post($url, $filecontents);
    $status = $c->info['http_code'];
    if (!empty($status)) {
        if (in_array($status, $allowedstatus)) {
            if ($status == URKUND_STATUSCODE_ACCEPTED) {
                $plagiarismfile->attempt = 0; // Reset attempts for status checks.
            } else {
                $plagiarismfile->errorresponse = $response;
            }
            $plagiarismfile->statuscode = $status;
            $DB->update_record('plagiarism_urkund_files', $plagiarismfile);
            return true;
        }
    }
    // Invalid response returned - increment attempt value and return false to allow this to be called again.
    $plagiarismfile->statuscode = URKUND_STATUSCODE_INVALID_RESPONSE;
    $plagiarismfile->errorresponse = $response;
    $DB->update_record('plagiarism_urkund_files', $plagiarismfile);
    return true;
}

// Function to check for the allowed file types, returns the mimetype that URKUND expects.
function urkund_check_file_type($filename, $checkdb = true) {
    $pathinfo = pathinfo($filename);

    if (empty($pathinfo['extension'])) {
        return '';
    }
    $ext = strtolower($pathinfo['extension']);
    $filetypes = array('doc'  => 'application/msword',
                       'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                       'sxw'  => 'application/vnd.sun.xml.writer',
                       'pdf'  => 'application/pdf',
                       'txt'  => 'text/plain',
                       'rtf'  => 'application/rtf',
                       'html' => 'text/html',
                       'htm'  => 'text/html',
                       'wps'  => 'application/vnd.ms-works',
                       'odt'  => 'application/vnd.oasis.opendocument.text');

    if (!empty($filetypes[$ext])) {
        return $filetypes[$ext];
    }
    // Check for updated allowed filetypes.
    if ($checkdb) {
        return get_config('plagiarism_urkund', 'ext_'.$ext);
    } else {
        return false;
    }
}

/**
 * Used to obtain similarity scores from URKUND for submitted files.
 *
 * @param object $plagiarismsettings - from a call to plagiarism_get_settings.
 *
 */
function urkund_get_scores($plagiarismsettings) {
    global $DB;

    mtrace("getting URKUND similarity scores");
    // Get all files set that have been submitted.
    $files = $DB->get_recordset('plagiarism_urkund_files', array('statuscode' => URKUND_STATUSCODE_ACCEPTED));
    foreach ($files as $plagiarismfile) {
        urkund_get_score($plagiarismsettings, $plagiarismfile);
    }
    $files->close();
    // Get all old files using the old identifier.
    $files = $DB->get_recordset('plagiarism_urkund_files', array('statuscode' => URKUND_STATUSCODE_ACCEPTED_OLD));
    foreach ($files as $plagiarismfile) {
        urkund_get_score($plagiarismsettings, $plagiarismfile);
    }
    $files->close();
}

function urkund_get_score($plagiarismsettings, $plagiarismfile, $force = false) {
    global $DB;
    // Check if we need to delay this submission.
    if (!$force) {
        $attemptallowed = urkund_check_attempt_timeout($plagiarismfile);
        if (!$attemptallowed) {
            return '';
        }
    }

    $allowedstatus = array(URKUND_STATUSCODE_PROCESSED,
                           URKUND_STATUSCODE_NOT_FOUND,
                           URKUND_STATUSCODE_BAD_REQUEST);
    $successfulstates = array('Analyzed', 'Rejected', 'Error');
    if ($plagiarismfile->statuscode == URKUND_STATUSCODE_ACCEPTED_OLD) {
        $url = old_urkund_get_url($plagiarismsettings['urkund_api'], $plagiarismfile);
    } else {
        $url = urkund_get_url($plagiarismsettings['urkund_api'], $plagiarismfile);
    }

    if (empty($url)) {
        mtrace('ERROR: no receiver address found for this cm: '.$plagiarismfile->cm);
        $plagiarismfile->statuscode = URKUND_STATUSCODE_NORECEIVER;
        $plagiarismfile->errorresponse = get_string('noreceiver', 'plagiarism_urkund');
        $DB->update_record('plagiarism_urkund_files', $plagiarismfile);
        return '';
    }
    $headers = array('Accept-Language: '.$plagiarismsettings['urkund_lang']);

    // Use Moodle curl wrapper to send file.
    $c = new curl(array('proxy' => true));
    $c->setopt(array());
    $c->setopt(array('CURLOPT_RETURNTRANSFER' => 1,
        'CURLOPT_HTTPAUTH' => CURLAUTH_BASIC,
        'CURLOPT_USERPWD' => $plagiarismsettings['urkund_username'].":".$plagiarismsettings['urkund_password']));

    $c->setHeader($headers);
    $response = $c->get($url);
    $httpstatus = $c->info['http_code'];
    if (!empty($httpstatus)) {
        if (in_array($httpstatus, $allowedstatus)) {
            if ($httpstatus == URKUND_STATUSCODE_PROCESSED) {
                // Get similarity score from xml.
                $xml = new SimpleXMLElement($response);
                $status = (string)$xml->SubmissionData[0]->Status[0]->State[0];
                if (!empty($status) && in_array($status, $successfulstates)) {
                    $plagiarismfile->statuscode = $status;
                }
                if (!empty($status) && $status == 'Analyzed') {
                    $plagiarismfile->reporturl = (string)$xml->SubmissionData[0]->Report[0]->ReportUrl[0];
                    $plagiarismfile->similarityscore = (int)$xml->SubmissionData[0]->Report[0]->Significance[0];
                    $plagiarismfile->optout = (string)$xml->SubmissionData[0]->Document[0]->OptOutInfo[0]->Url[0];
                    // Now send e-mail to user.
                    $emailstudents = $DB->get_field('plagiarism_urkund_config', 'value',
                                                    array('cm' => $plagiarismfile->cm, 'name' => 'urkund_studentemail'));
                    if (!empty($emailstudents)) {
                        $urkund = new plagiarism_plugin_urkund();
                        $urkund->urkund_send_student_email($plagiarismfile);
                    }
                }
            } else {
                $plagiarismfile->statuscode = $httpstatus;
            }
        }
    }
    $plagiarismfile->attempt = $plagiarismfile->attempt + 1;
    $DB->update_record('plagiarism_urkund_files', $plagiarismfile);
    return $plagiarismfile;
}

function urkund_get_url($baseurl, $plagiarismfile) {
    // Get url of api.
    global $DB;
    $receiver = $DB->get_field('plagiarism_urkund_config', 'value', array('cm' => $plagiarismfile->cm,
                                                                          'name' => 'urkund_receiver'));
    if (empty($receiver)) {
        return;
    }

    // Site id passed to Urkund to identify this file is:
    // first 8 chars of this site_indentifier full id isn't used as Urkund has a 64 char limit on the identifier passed,
    // Then the course module id of this plugin,
    // Then the id from the plagiarism_files table,
    // Then the full contenthash of the file.

    $siteid = substr(md5(get_site_identifier()), 0, 8);
    $urkundid = $siteid.'_'.$plagiarismfile->cm.'_'.$plagiarismfile->id.'_'.$plagiarismfile->identifier;

    return $baseurl.'/' .trim($receiver).'/'.$urkundid;
}

// Helper function to save multiple db calls.
function urkund_cm_use($cmid) {
    global $DB;
    static $useurkund = array();
    if (!isset($useurkund[$cmid])) {
        $pvalues = $DB->get_records_menu('plagiarism_urkund_config', array('cm' => $cmid), '', 'name,value');
        if (!empty($pvalues['use_urkund'])) {
            $useurkund[$cmid] = $pvalues;
        } else {
            $useurkund[$cmid] = false;
        }
    }
    return $useurkund[$cmid];
}

/**
 * Function that returns the name of the css class to use for a given similarity score.
 * @param integer $score - the similarity score
 * @return string - string name of css class
 */
function urkund_get_css_rank ($score) {
    $rank = "none";
    if ($score > 90) {
        $rank = "1";
    } else if ($score > 80) {
        $rank = "2";
    } else if ($score > 70) {
        $rank = "3";
    } else if ($score > 60) {
        $rank = "4";
    } else if ($score > 50) {
        $rank = "5";
    } else if ($score > 40) {
        $rank = "6";
    } else if ($score > 30) {
        $rank = "7";
    } else if ($score > 20) {
        $rank = "8";
    } else if ($score > 10) {
        $rank = "9";
    } else if ($score >= 0) {
        $rank = "10";
    }

    return "rank$rank";
}

/**
 * Function that checks Urkund to see if there are any newly supported filetypes.
 *
 */
function urkund_update_allowed_filetypes() {
    global $CFG, $DB;
    $configvars = get_config('plagiarism_urkund');
    $now = time();
    $wait = (int)URKUND_FILETYPE_URL_UPDATE * 60 * 60;

    if (!isset($configvars->lastupdatedfiletypes)) {
        // First time this has run.
        $configvars->lastupdatedfiletypes = 0;
    }

    $timetocheck = (int)($configvars->lastupdatedfiletypes + $wait);

    if (empty($configvars->lastupdatedfiletypes) ||
        $timetocheck < $now ) {
        // Need to update filetypes.
        // Get list of existing options.
        $existing = array();
        foreach ($configvars as $name => $value) {
            if (strpos($name, 'ext_') !== false) {
                $existing[$name] = $value;
            }
        }

        require_once($CFG->libdir.'/filelib.php');
        $url = URKUND_FILETYPE_URL;
        $c = new curl(array('proxy' => true));
        $response = $c->get($url);
        $xml = new SimpleXMLElement($response);
        foreach ($xml->format as $format) {
            $type = (string)$format->attributes()->type;
            $suffix = (string)$format->attributes()->suffix;
            unset($existing['ext_'.$suffix]);
            if (!urkund_check_file_type('test.'.$suffix, false)) {
                set_config('ext_'.$suffix, $type, 'plagiarism_urkund');
            }
        }
        // Clean up old vars.
        if (!empty($existing)) {
            foreach ($existing as $name => $value) {
                $DB->delete_records('config_plugins', array('plugin' => 'plagiarism_urkund', 'name' => $name));
            }
        }
        set_config('lastupdatedfiletypes', $now, 'plagiarism_urkund');
    }
}

// Function to check for invalid event_handlers.
function urkund_check_event_handlers() {
    global $DB, $CFG;
    $invalidhandlers = array();
    $eventhandlers = $DB->get_records('events_handlers');
    foreach ($eventhandlers as $handler) {
        $function = unserialize($handler->handlerfunction);

        if (is_callable($function)) { // This function is fine.
            continue;
        } else if (file_exists($CFG->dirroot.$handler->handlerfile)) {
            include_once($CFG->dirroot.$handler->handlerfile);
            if (is_callable($function)) { // This function is fine.
                continue;
            }
        }
        $invalidhandlers[] = $handler; // This function can't be found.
    }
    return $invalidhandlers;
}

// Old function to get url using old method of generating an indentifier.
// This function should only be used if the file is known to have been
// generated using old code.
function old_urkund_get_url($baseurl, $plagiarismfile) {
    // Get url of api.
    global $DB;
    $receiver = $DB->get_field('plagiarism_urkund_config', 'value', array('cm' => $plagiarismfile->cm,
                                                                          'name' => 'urkund_receiver'));
    if (empty($receiver)) {
        return '';
    }
    return $baseurl.'/' .$receiver.'/'.md5(get_site_identifier()).
    '_'.$plagiarismfile->cm.'_'.$plagiarismfile->id;
}

function urkund_reset_file($id) {
    global $DB, $CFG;
    $plagiarismfile = $DB->get_record('plagiarism_urkund_files', array('id' => $id), '*', MUST_EXIST);
    if ($plagiarismfile->statuscode == 'Analyzed' ||
        $plagiarismfile->statuscode == URKUND_STATUSCODE_ACCEPTED) { // Sanity Check.
        return true;
    }
    // Set some new values.
    $plagiarismfile->statuscode = 'pending';
    $plagiarismfile->attempt = 0;
    $plagiarismfile->timesubmitted = time();

    $cm = get_coursemodule_from_id('', $plagiarismfile->cm);
    $modulecontext = context_module::instance($plagiarismfile->cm);
    $fs = get_file_storage();
    if ($cm->modname == 'assignment') {
        $submission = $DB->get_record('assignment_submissions', array('assignment' => $cm->instance,
                                                                      'userid' => $plagiarismfile->userid));
        $files = $fs->get_area_files($modulecontext->id, 'mod_assignment', 'submission', $submission->id);
        foreach ($files as $file) {
            if ($file->get_contenthash() == $plagiarismfile->identifier) {
                $DB->update_record('plagiarism_urkund_files', $plagiarismfile); // Update before trying to send again.
                return urkund_send_file($plagiarismfile->cm, $plagiarismfile->userid, $file,
                                        plagiarism_plugin_urkund::get_settings());
            }
        }
    } else if ($cm->modname == 'assign') {
        require_once($CFG->dirroot.'/mod/assign/locallib.php');
        $assign = new assign($modulecontext, null, null);
        $submissionplugins = $assign->get_submission_plugins();

        $dbparams = array('assignment' => $assign->get_instance()->id, 'userid' => $plagiarismfile->userid);
        $submissions = $DB->get_records('assign_submission', $dbparams);
        foreach ($submissions as $submission) {
            foreach ($submissionplugins as $submissionplugin) {
                $component = $submissionplugin->get_subtype().'_'.$submissionplugin->get_type();
                $fileareas = $submissionplugin->get_file_areas();
                foreach ($fileareas as $filearea => $name) {
                    $files = $fs->get_area_files(
                        $assign->get_context()->id,
                        $component,
                        $filearea,
                        $submission->id,
                        "timemodified",
                        false
                    );
                    foreach ($files as $file) {
                        if ($file->get_contenthash() == $plagiarismfile->identifier) {
                            $DB->update_record('plagiarism_urkund_files', $plagiarismfile); // Update before trying to send again.
                            return urkund_send_file($plagiarismfile->cm, $plagiarismfile->userid, $file,
                                                    plagiarism_plugin_urkund::get_settings());
                        }
                    }
                }
            }
        }

    } else if ($cm->modname == 'workshop') {
        require_once($CFG->dirroot.'/mod/workshop/locallib.php');
        $cm     = get_coursemodule_from_id('workshop', $plagiarismfile->cm, 0, false, MUST_EXIST);
        $workshop = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $workshop = new workshop($workshop, $cm, $course);
        $submissions = $workshop->get_submissions($plagiarismfile->userid);
        foreach ($submissions as $submission) {
            $files = $fs->get_area_files($workshop->context->id, 'mod_workshop', 'submission_attachment', $submission->id);
            foreach ($files as $file) {
                if ($file->get_contenthash() == $plagiarismfile->identifier) {
                    $DB->update_record('plagiarism_urkund_files', $plagiarismfile); // Update before trying to send again.
                    return urkund_send_file($plagiarismfile->cm, $plagiarismfile->userid, $file,
                                            plagiarism_plugin_urkund::get_settings());
                }
            }
        }
    } else if ($cm->modname == 'forum') {
        require_once($CFG->dirroot.'/mod/forum/lib.php');
        $cm     = get_coursemodule_from_id('forum', $plagiarismfile->cm, 0, false, MUST_EXIST);
        $posts = forum_get_user_posts($cm->instance, $plagiarismfile->userid);
        foreach ($posts as $post) {
            $files = $fs->get_area_files($modulecontext->id, 'mod_forum', 'attachment', $post->id, "timemodified", false);
            foreach ($files as $file) {
                if ($file->get_contenthash() == $plagiarismfile->identifier) {
                    $DB->update_record('plagiarism_urkund_files', $plagiarismfile); // Update before trying to send again.
                    return urkund_send_file($plagiarismfile->cm, $plagiarismfile->userid, $file,
                                            plagiarism_plugin_urkund::get_settings());
                }
            }
        }

    }
}
