<?php

/**
 * This file contains the class for restore of this submission plugin
 *
 * @package assignsubmission_helixassign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * restore subplugin class that provides the necessary information needed to restore one assign_submission subplugin.
 *
 * @package assignsubmission_helixassign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_assignsubmission_helixassign_subplugin extends restore_subplugin {

    /**
     *
     * Returns array the paths to be handled by the subplugin at assignment level
     * @return array
     */
    protected function define_submission_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('submission');
        $elepath = $this->get_pathfor('/submission_helixassign'); // we used get_recommended_name() so this works
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths
    }

    /**
     * Processes one assignsubmission_helixassign element
     *
     * @param mixed $data
     */
    public function process_assignsubmission_helixassign_submission($data) {
        global $DB;

        $data = (object)$data;
        $data->assignment = $this->get_new_parentid('assign');
        $oldsubmissionid = $data->submission;
        // the mapping is set in the restore for the core assign activity. When a submission node is processed
        $data->submission = $this->get_mappingid('submission', $data->submission);

        $DB->insert_record('assignsubmission_helixassign', $data);

        $this->add_related_files('assignsubmission_helixassign', 'submissions_helixassign', 'submission', null, $oldsubmissionid);
    }

}
