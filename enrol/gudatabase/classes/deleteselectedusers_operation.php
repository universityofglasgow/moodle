<?php
/**
 * A bulk operation for the manual enrolment plugin to delete selected users enrolments.
 *
 * @copyright 2019 Howard 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace enrol_gudatabase;

defined('MOODLE_INTERNAL') || die;

class deleteselectedusers_operation extends \enrol_bulk_enrolment_operation {

    /**
     * Returns the title to display for this bulk operation.
     *
     * @return string
     */
    public function get_identifier() {
        return 'deleteselectedusers';
    }

    /**
     * Returns the identifier for this bulk operation. This is the key used when the plugin
     * returns an array containing all of the bulk operations it supports.
     *
     * @return string
     */
    public function get_title() {
        return get_string('deleteselectedusers', 'enrol_manual');
    }

    /**
     * Returns a enrol_bulk_enrolment_operation extension form to be used
     * in collecting required information for this operation to be processed.
     *
     * @param string|moodle_url|null $defaultaction
     * @param mixed $defaultcustomdata
     * @return enrol_manual_editselectedusers_form
     */
    public function get_form($defaultaction = null, $defaultcustomdata = null) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/manual/bulkchangeforms.php');
        if (!array($defaultcustomdata)) {
            $defaultcustomdata = array();
        }
        $defaultcustomdata['title'] = $this->get_title();
        $defaultcustomdata['message'] = get_string('confirmbulkdeleteenrolment', 'enrol_manual');
        $defaultcustomdata['button'] = get_string('unenrolusers', 'enrol_manual');
        return new \enrol_gudatabase\forms\deleteselectedusers($defaultaction, $defaultcustomdata);
    }

    /**
     * Processes the bulk operation request for the given userids with the provided properties.
     *
     * @global moodle_database $DB
     * @param course_enrolment_manager $manager
     * @param array $userids
     * @param stdClass $properties The data returned by the form.
     */
    public function process(\course_enrolment_manager $manager, array $users, \stdClass $properties) {
        global $DB;

        if (!has_capability("enrol/gudatabase:unenrol", $manager->get_context())) {
            return false;
        }
        foreach ($users as $user) {
            foreach ($user->enrolments as $enrolment) {
                $plugin = $enrolment->enrolmentplugin;
                $instance = $enrolment->enrolmentinstance;
                if ($plugin->allow_unenrol_user($instance, $enrolment)) {
                    $plugin->unenrol_user($instance, $user->id);
                }
            }
        }
        return true;
    }
}
