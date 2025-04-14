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
 * This file contains the definition for the library class for helixassign submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package assignsubmission_helixassign
 * @copyright Streaming LTD 2013
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot.'/mod/helixmedia/lib.php');
require_once($CFG->dirroot.'/mod/helixmedia/locallib.php');

/**
 * library class for helixassign submission plugin extending submission plugin base class
 *
 * @package assignsubmission_helixassign
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_helixassign extends assign_submission_plugin {

    // Used for group assignments on the submission summary page so we have a unique frame ID
    private $count = 0;

    /**
     * Get the name of the online text submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('helixassign', 'assignsubmission_helixassign');
    }


    /**
     * Get helixassign submission information from the database
     *
     * @param  int $submissionid
     * @return mixed
     */
    private function get_helixassign_submission($submissionid) {
        global $DB;
        $aid = $this->assignment->get_instance()->id;
        if ($aid) {
            $ret = $DB->get_record('assignsubmission_helixassign', array('assignment' => $aid, 'submission' => $submissionid));
            if ($ret) {
                return $ret;
            }
        }

        return false;
    }

    /**
     * Get the default setting for file submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $PAGE, $DB;

        if (!has_capability('assignsubmission/helixassign:can_use', $PAGE->context)) {
            $add = optional_param("add", "none", PARAM_TEXT);
            if ($add == "none") {
                $aid = $this->assignment->get_instance()->id;
                $plconf = $DB->get_record('assign_plugin_config',
                    array('assignment' => $aid, 'plugin' => 'helixassign', 'subtype' => 'assignsubmission', 'name' => 'enabled'));

                $disable = '';
                if (!$plconf->value) {
                    $disable = 'ha.checked=false;';
                }
            } else {
                $disable = 'ha.checked=false;';
            }

            $mform->addElement('html',
                '<script type="text/javascript">'.
                'var ha=document.getElementById("id_assignsubmission_helixassign_enabled");'.
                $disable.
                'ha.disabled=true;'.
                'ha.title="'.get_string('nopermission', 'assignsubmission_helixassign').'";'.
                '</script>');
        }
    }

    /**
     * Add form elements for settings
     *
     * @param mixed $submission can be null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return true if elements were added to the form
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $CFG, $COURSE, $PAGE, $USER;

        $elements = array();

        $submissionid = $submission ? $submission->id : 0;

        $mform->addElement('hidden', 'helixassign_preid');
        $mform->setType('helixassign_preid', PARAM_INT);

        $thumbparams = array('type' => HML_LAUNCH_STUDENT_SUBMIT_THUMBNAILS);
        $params = array('type' => HML_LAUNCH_STUDENT_SUBMIT);

        if ($submission) {
            $helixassignsubmission = $this->get_helixassign_submission($submission->id);
            if ($helixassignsubmission) {
                $preid = $helixassignsubmission->preid;
                $thumbparams['e_assign'] = $helixassignsubmission->preid;
                $params['e_assign'] = $helixassignsubmission->preid;
                $mform->setDefault('helixassign_preid', $helixassignsubmission->preid);
            }
        }

        if (!array_key_exists('e_assign', $params)) {
            $nassign = optional_param('helixassign_preid', false, PARAM_INT);
            if ($nassign === FALSE) {
                $preid = helixmedia_preallocate_id();
            } else {
                $preid = $nassign;
            }
            $thumbparams['n_assign'] = $preid;
            $thumbparams['aid'] = $PAGE->cm->id;
            $params['n_assign'] = $preid;
            $params['aid'] = $PAGE->cm->id;
            $mform->setDefault('helixassign_preid', $preid);
        }

        $output = $PAGE->get_renderer('mod_helixmedia');
        $disp = new \mod_helixmedia\output\modal($preid, $thumbparams, $params, true);
        $html = $output->render($disp);

        $PAGE->requires->js_call_amd('assignsubmission_helixassign/cancel', 'init', array($preid, $USER->id, helixmedia_get_status_url()));
        $mform->addElement('static', 'helixassign_choosemedia', "", $html);

        return true;
    }

     /**
      * Save data to the database
      *
      * @param stdClass $submission
      * @param stdClass $data
      * @return bool
      */
    public function save(stdClass $submission, stdClass $data) {
        global $DB, $USER, $CFG;

        if (helixmedia_is_preid_empty($data->helixassign_preid, $this, $submission->userid)) {
            return true;
        }

        $helixassignsubmission = $this->get_helixassign_submission($submission->id);

        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(
                'pathnamehashes' => array(),
                'content' => '',
                'format' => false
            )
        );
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }

        // Use an event which is compatible with the legacy log if legacy log logging is enabled.
        // Moodle 4.2+ no longer supports legacy logging  and will emit a notice if the methods are present in events.
        if ($CFG->version < 2023042400 && get_config('logstore_legacy', 'loglegacy') == 1) {
            $event = \assignsubmission_helixassign\event\assessable_compat_uploaded::create($params);
        } else {
            $event = \assignsubmission_helixassign\event\assessable_uploaded::create($params);
        }
        $event->trigger();

        $groupname = null;
        $groupid = 0;
        // Get the group name as other fields are not transcribed in the logs and this information is important.
        if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), '*', MUST_EXIST);
            $groupid = $submission->groupid;
        } else {
            $params['relateduserid'] = $submission->userid;
        }

        // Unset the objectid and other field from params for use in submission events.
        unset($params['objectid']);
        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            'groupid' => $groupid,
            'groupname' => $groupname
        );

        if ($helixassignsubmission) {
            $params['objectid'] = $helixassignsubmission->id;
            $event = \assignsubmission_helixassign\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return true;
        } else {
            $helixassignsubmission = new stdClass();
            $helixassignsubmission->assignment = $this->assignment->get_instance()->id;
            $prerec = $DB->get_record('helixmedia_pre', array('id' => $data->helixassign_preid));
            $helixassignsubmission->preid = $prerec->id;
            $helixassignsubmission->submission = $submission->id;
            $helixassignsubmission->servicesalt = $prerec->servicesalt;
            $helixassignsubmission->id = $DB->insert_record('assignsubmission_helixassign', $helixassignsubmission);
            $params['objectid'] = $helixassignsubmission->id;
            $event = \assignsubmission_helixassign\event\submission_created::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $helixassignsubmission->id > 0;
        }
    }

     /**
      * Display helixassign summary in the submission status table
      *
      * @param stdClass $submission
      * @param bool $showviewlink - If the summary has been truncated set this to true
      * @return string
      */
    public function view_summary(stdClass $submission, &$showviewlink) {
        // We want to show just the link on the grading table to keep things condensed, otherwise the normal graphic button.
        if (optional_param('action', false, PARAM_TEXT) != 'grading') {
            return $this->view($submission);
        }

        $helixassignsubmission = $this->get_helixassign_submission($submission->id);

        if ($helixassignsubmission) {
            global $PAGE;
            $params = array('e_assign' =>$helixassignsubmission->preid, 'userid' => $submission->userid);

            if (has_capability('mod/assign:grade', $PAGE->context)) {
                $params['type'] = HML_LAUNCH_VIEW_SUBMISSIONS;
            } else {
                $params['type'] = HML_LAUNCH_STUDENT_SUBMIT_PREVIEW;
            }

            if (!empty($submission->groupid)) {
                $extraid = $this->count;
                $this->count++;
            } else {
                $extraid = false;
            }

            $output = $PAGE->get_renderer('mod_helixmedia');
            $disp = new \mod_helixmedia\output\modal($helixassignsubmission->preid, array(), $params, false,
                get_string('view_submission', 'assignsubmission_helixassign'), false, false, 'row', $extraid);
            return $output->render($disp);
        }

        return "<br /><br /><div class='box generalbox boxaligncenter'><p style='text-align:center;'>"
            .get_string('nosubmissionshort', 'assignsubmission_helixassign')."</p></div>";
    }

    /**
     * Display the assignment view links
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        global $CFG;

        $helixassignsubmission = $this->get_helixassign_submission($submission->id);

        if ($helixassignsubmission) {

            global $PAGE;

            $thumbparams = array('e_assign' =>$helixassignsubmission->preid, 'userid' => $submission->userid);
            $params = array('e_assign' =>$helixassignsubmission->preid, 'userid' => $submission->userid);

            if (has_capability('mod/assign:grade', $PAGE->context)) {
                $thumbparams['type'] = HML_LAUNCH_VIEW_SUBMISSIONS_THUMBNAILS;
                $params['type'] = HML_LAUNCH_VIEW_SUBMISSIONS;
                $align = 'column';
            } else {
                $thumbparams['type'] = HML_LAUNCH_STUDENT_SUBMIT_THUMBNAILS;
                $params['type'] = HML_LAUNCH_STUDENT_SUBMIT_PREVIEW;
                $align = 'row';
            }

            $output = $PAGE->get_renderer('mod_helixmedia');
            $disp = new \mod_helixmedia\output\modal($helixassignsubmission->preid, $thumbparams, $params, "magnifier",
                get_string('view_submission', 'assignsubmission_helixassign'), false, false, $align);
            return $output->render($disp);
        }

        return "<br /><br /><div class='box generalbox boxaligncenter'><p style='text-align:center;'>"
            .get_string('nosubmission', 'assignsubmission_helixassign')."</p></div>";
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type and version.
     *
     * @param string $type old assignment subtype
     * @param int $version old assignment version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {
        return false;
    }


    /**
     * Upgrade the settings from the old assignment to the new plugin based one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment - the database for the old assignment instance
     * @param string $log record log events here
     * @return bool Was it a success?
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        return true;
    }

    /**
     * Upgrade the submission from the old assignment to the new one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment The data record for the old assignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $submission The data record for the new submission
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext, stdClass $oldassignment, stdClass $oldsubmission, stdClass $submission, & $log) {
        return true;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // Will throw exception on failure.
        $DB->delete_records('assignsubmission_helixassign', array('assignment' => $this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Has anything been submitted?
     *
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        $helixassignsubmission = $this->get_helixassign_submission($submission->id);

        if ($helixassignsubmission) {
            return helixmedia_is_preid_empty($helixassignsubmission->preid, $this, $submission->userid);
        }

        return true;
    }

    /**
     * Determine if a submission is empty
     *
     * This is distinct from is_empty in that it is intended to be used to
     * determine if a submission made before saving is empty.
     *
     * @param stdClass $data The submission data
     * @return bool
     */
    public function submission_is_empty(stdClass $data) {
        $status = helixmedia_get_media_status($data->helixassign_preid, $data->userid);

        // This will give the date the media was linked to the resource link id for MEDIAL 8.0.008 and better. Earlier versions will just be true or false
        if (is_bool($status)) {
            // Need to invert the status, the method will return true if there is something present, but we need to return true for empty.
            return !$status;
        }

        // If we are here, then we must have a date. Check it is more recent that then last sub date.
        if ($data->lastmodified > $status) {
            return true;
        }

        return false;
    }

    /**
     * Remove a submission.
     *
     * @param stdClass $submission The submission
     * @return boolean
     */
    public function remove(stdClass $submission) {
        global $DB;

        $submissionid = $submission ? $submission->id : 0;
        if ($submissionid) {
            $DB->delete_records('assignsubmission_helixassign', array('submission' => $submissionid));
        }
        return true;
    }
}


