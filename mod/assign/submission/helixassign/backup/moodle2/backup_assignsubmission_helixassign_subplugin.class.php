<?php

/**
 * This file contains the class for backup of this submission plugin
 *
 * @package assignsubmission_helixassign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup helixassign submissions
 *
 * This just adds its filearea to the annotations and records the submissiontext and format
 *
 * @package assignsubmission_helixassign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_assignsubmission_helixassign_subplugin extends backup_subplugin {

    /**
     *
     * Returns the subplugin information to attach to submission element
     * @return backup_subplugin_element
     */
    protected function define_submission_subplugin_structure() {

        // create XML elements
        $subplugin = $this->get_subplugin_element(); // virtual optigroup element
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('submission_helixassign', null, array('servicesalt', 'preid', 'submission'));

        // connect XML elements into the tree
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        // set source to populate the data
        $subpluginelement->set_source_table('assignsubmission_helixassign', array('submission' => backup::VAR_PARENTID));

        $subpluginelement->annotate_files('assignsubmission_helixassign', 'submission_helixassign', 'submission');
        return $subplugin;
    }

}
