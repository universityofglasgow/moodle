<?php


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

require_once ($CFG->dirroot.'/mod/helixmedia/lib.php');
require_once ($CFG->dirroot.'/mod/helixmedia/locallib.php');

/**
 * library class for helixassign submission plugin extending submission plugin base class
 *
 * @package assignsubmission_helixassign
 * 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_helixassign extends assign_submission_plugin {

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
        $aid=$this->assignment->get_instance()->id;
        if ($aid)
        {
            $ret=$DB->get_record('assignsubmission_helixassign', array('assignment'=>$aid, 'submission'=>$submissionid));
            if ($ret)
                return $ret;
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

        if (!has_capability('assignsubmission/helixassign:can_use', $PAGE->context))
        {
            $add=optional_param("add", "none", PARAM_TEXT);
            if ($add=="none")
            {
                $aid=$this->assignment->get_instance()->id;
                $pl_conf=$DB->get_record('assign_plugin_config',
                    array('assignment'=>$aid, 'plugin'=>'helixassign', 'subtype'=>'assignsubmission', 'name'=>'enabled'));

                $disable='';
                if (!$pl_conf->value)
                    $disable='ha.checked=false;';
            }
            else
            {
                $disable='ha.checked=false;';
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
        global $CFG, $COURSE, $PAGE;

        $elements = array();

        $submissionid = $submission ? $submission->id : 0;

        $mform->addElement('hidden', 'helixassign_preid');
        $mform->setType('helixassign_preid', PARAM_INT);

        $mform->addElement('hidden', 'helixassign_activated');
        $mform->setType('helixassign_activated', PARAM_INT);

        if ($submission) {
            $helixassignsubmission = $this->get_helixassign_submission($submission->id);
            if ($helixassignsubmission) {
                $preid=$helixassignsubmission->preid;
                $param="e_assign=".$helixassignsubmission->preid;
                $mform->setDefault('helixassign_preid', $helixassignsubmission->preid);
            }
        }

        if (!isset($param)) {
            $preid=helixmedia_preallocate_id();
            $param="n_assign=".$preid."&aid=".$PAGE->cm->id;
            $mform->setDefault('helixassign_preid', $preid);
        }

        $html=helixmedia_get_modal_dialog($preid, "type=".HML_LAUNCH_STUDENT_SUBMIT_THUMBNAILS."&".$param,
                "type=".HML_LAUNCH_STUDENT_SUBMIT."&".$param);
        $html.="<script type='text/javascript'>\n".
            "document.addEventListener('DOMContentLoaded', function() {\n".
            "var cbtn=document.getElementById('id_cancel');\n".
            "if (cbtn!=null) {\n".
            "cbtn.addEventListener('click', helixCancelClick);\n".
            "}\n".
            "});\n".
            "function helixCancelClick()\n".
            "{\n".
            "var xmlDoc=null;\n".

            "    if (typeof window.ActiveXObject != 'undefined' )\n".
            "        xmlDoc = new ActiveXObject('Microsoft.XMLHTTP');\n".
            "    else\n".
            "        xmlDoc = new XMLHttpRequest();\n".

            "    var params='resource_link_id='+resID+'&user_id='+userID;\n".
            "    xmlDoc.open('POST', statusURL , false);\n".
            "    xmlDoc.setRequestHeader('Content-type','application/x-www-form-urlencoded');\n".
            "    xmlDoc.send(params);\n".
            "}\n".
            "</script>";

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
        global $DB, $USER;
        $helixassignsubmission = $this->get_helixassign_submission($submission->id);

        if ($data->helixassign_activated!=1) {
            return true;
        }

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
        $event = \assignsubmission_helixassign\event\assessable_uploaded::create($params);
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
            $pre_rec=$DB->get_record('helixmedia_pre', array('id'=>$data->helixassign_preid));
            $helixassignsubmission->preid = $pre_rec->id;
            $helixassignsubmission->submission= $submission->id;
            $helixassignsubmission->servicesalt = $pre_rec->servicesalt;
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
    public function view_summary(stdClass $submission, & $showviewlink) {
        $showviewlink = true;
        $helixassignsubmission = $this->get_helixassign_submission($submission->id);
        if ($helixassignsubmission) {

            global $PAGE;

            $type=HML_LAUNCH_STUDENT_SUBMIT_PREVIEW;
            $thumb_type=HML_LAUNCH_STUDENT_SUBMIT_THUMBNAILS;
            if (has_capability('mod/assign:grade', $PAGE->context))
            {
                $type=HML_LAUNCH_VIEW_SUBMISSIONS;
                $thumb_type=HML_LAUNCH_VIEW_SUBMISSIONS_THUMBNAILS;
            }

            $param="e_assign=".$helixassignsubmission->preid."&userid=".$submission->userid;
            return helixmedia_get_modal_dialog($helixassignsubmission->preid,
                "type=".$thumb_type."&".$param,
                "type=".$type."&".$param, "margin-left:auto;margin-right:auto;",
                get_string('view_submission', 'assignsubmission_helixassign'), -1, -1, -1, "false");
        }

        return "<br /><br /><div class='box generalbox boxaligncenter'><p style='text-align:center;'>"
            .get_string('nosubmission', 'assignsubmission_helixassign')."</p></div>";
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

            $type=HML_LAUNCH_STUDENT_SUBMIT_PREVIEW;
            $thumb_type=HML_LAUNCH_STUDENT_SUBMIT_THUMBNAILS;
            if (has_capability('mod/assign:grade', $PAGE->context))
            {
                $type=HML_LAUNCH_VIEW_SUBMISSIONS;
                $thumb_type=HML_LAUNCH_VIEW_SUBMISSIONS_THUMBNAILS;
            }

            $splitline = false;
            if ($CFG->version >= 2016052300 ) {
                 $splitline = true;
            }

            $param="e_assign=".$helixassignsubmission->preid."&userid=".$submission->userid;
            return helixmedia_get_modal_dialog($helixassignsubmission->preid,
                "type=".$thumb_type."&".$param,
                "type=".$type."&".$param, "margin-left:auto;margin-right:auto;",
                "moodle-lti-viewsub-btn.png", "", "", -1, "false", true);
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
     * Formatting for log info
     *
     * @param stdClass $submission The new submission
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        return get_string('helixsubmissionlog', 'assignsubmission_helixassign');
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // will throw exception on failure
        $DB->delete_records('assignsubmission_helixassign', array('assignment'=>$this->assignment->get_instance()->id));

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

}


