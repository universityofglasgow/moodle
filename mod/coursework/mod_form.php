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
 * The main coursework module configuration form. Presented to the user when they make a new
 * instance of this module
 *
 * @package    mod
 * @subpackage coursework
 * @copyright  2011 University of London Computer Centre {@link ulcc.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/coursework/lib.php');

/**
 * Mod form that allows a new coursework to ber created, or for the settings of an existing one to be altered.
 */
class mod_coursework_mod_form extends moodleform_mod {

    private function form() {
        return $this->_form;
    }

    /**
     */
    public function definition() {

        $this->set_form_attributes();

        $this->add_general_header();

        $this->add_name_field();
        $this->standard_intro_elements(get_string('description', 'coursework'));


        $this->add_availability_header();

        $this->add_start_date_field();
        $this->add_submission_deadline_field();

        $this->add_allow_early_finalisation_field();
        $this->add_allow_late_submissions_field();

        $this->add_submissions_header();

        $this->add_turnitin_files_settings_waring();
        $this->add_file_types_field();
        $this->add_max_file_size_field();
        $this->add_number_of_files_field();


        $this->add_marking_workflow_header();

        $this->add_number_of_initial_assessors_field();
        $this->add_enable_allocation_field();
        $this->add_assessor_allocation_strategy_field_checkboxes();
        $this->add_enable_sampling_checkbox();
        $this->add_automatic_agreement_enabled();
        $this->add_view_initial_assessors_grade();
        $this->add_enable_agreed_grade_delay();

        $this->add_blind_marking_header();

        $this->add_enable_blind_marking_field();

        $this->add_assessor_anonymity_header();

        $this->add_enable_assessor_anonymity_field();


        $this->add_feedback_header();

        $this->add_general_feedback_release_date_field();
        $this->add_individual_feedback_release_date_field();
        $this->add_email_individual_feedback_notification_field();
        $this->add_all_feedbacks_field();


        $this->add_extensions_header();

        $this->add_enable_extensions_field();

        $this->add_group_submission_header();

        $this->add_use_groups_field();
        $this->add_grouping_field();


        $this->add_plagiarism_elements_to_form();

        $this->standard_grading_coursemodule_elements();
        $this->add_tweaks_to_standard_grading_form_elements();

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();

    }


    /**
     * Adds all default data to the form elements.
     *
     * @global moodle_database $DB
     * @param $default_values
     * @return void
     */
    public function set_data($default_values) {
        $default_values = (array)$default_values;

        foreach ($this->_form->_elements as $element) {

            // Some form elements are replaced with static HTML e.g. if there is a submission now
            // And they should not be editable.
            //
            // For any elements that won't have any default (static thingys we are using for
            // non-editing display), add the data from its corresponding real element, which is
            // now hidden. This is the only way to get around the issue of Moodle requiring all
            // the form's required data to be resubmitted if you want to edit any part of it.
            // Using a rule to disable an element means it doesn't get submitted, which breaks
            // stuff. Just having a static element pre-fills with defaults, but won't get
            // resubmitted, so we have to use a hidden value, then another static one with
            // 'html' suffixed (arbitrarily) which we add the same default data to here.
            if (isset($element->_attributes['name']) && substr($element->_attributes['name'], -6) == 'static') {
                // TODO this is using private attributes directly. Need to switch to proper
                // Getters and setters.
                if (isset($default_values[substr($element->_attributes['name'], 0, -6)])) {
                    $default = $default_values[substr($element->_attributes['name'], 0, -6)];
                    $default_values[$element->_attributes['name']] = $default;
                }
            }
        }
        parent::set_data($default_values);
    }

    /**
     * We can't do this with $mform->addRule() because the compare function works with the raw form values, which is
     * an array of date components. Here, Moodle's internals have processed those values into a Unix timestamp, so the
     * comparison works.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {

        $errors = array();

       if ($data['startdate'] != 0 && $data['startdate'] > $data['deadline']){
           $errors['startdate'] = get_string('must_be_before_dealdine', 'mod_coursework');
       }

       if ($data['individualfeedback'] != 0 && $data['individualfeedback'] < $data['deadline']) {
            $errors['individualfeedback'] = get_string('must_be_after_dealdine', 'mod_coursework');
        }

        if ($data['generalfeedback'] != 0 && $data['generalfeedback'] < $data['deadline']) {
            $errors['generalfeedback'] = get_string('must_be_after_dealdine', 'mod_coursework');
        }

        $parent_errors = parent::validation($data, $files);
        return array_merge($errors, $parent_errors);

    }

    /**
     * Get data from the form and manipulate it
     * @return bool|object
     */
    function get_data() {
        global $CFG;
        $data = parent::get_data();

        if (!$data){
            return false;
        }

        if ($this->forceblindmarking() == 1){
           $data->blindmarking = $CFG->coursework_blindmarking;
        }

        return $data;
    }

    /**
     * @throws coding_exception
     */
    protected function add_submissions_header() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('header', 'submissions', get_string('submissions', 'mod_coursework'));
        // We want it expanded by default
        $moodle_form->setExpanded('submissions');
    }

    /**
     * @throws coding_exception
     */
    protected function add_availability_header() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('header', 'availability', get_string('availability', 'mod_coursework'));
        // We want it expanded by default
        $moodle_form->setExpanded('availability');
    }

    /**
     * @throws coding_exception
     */
    protected function add_name_field() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('text',
                                 'name',
                                 get_string('courseworkname', 'coursework'),
                                 array('size' => '64'));
        $moodle_form->addRule('name', null, 'required', null, 'client');
        $moodle_form->addRule('name',
                              get_string('maximumchars', '', 255),
                              'maxlength',
                              255,
                              'client');
        $moodle_form->setType('name', PARAM_TEXT);
    }

    /**
     * @throws coding_exception
     */
    protected function add_submission_deadline_field() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('date_time_selector',
                                 'deadline',
                                 get_string('deadline', 'coursework'),
                                 array('optional' => false));

         $moodle_form->addElement('html', '<div class ="submission_deadline_info alert">');
         $moodle_form->addElement('html', get_string('submissionsdeadlineinfo','mod_coursework'));
         $moodle_form->addElement('html', '</div>');

        $moodle_form->setDefault('deadline', strtotime('+2 weeks'));
        $moodle_form->addHelpButton('deadline', 'deadline', 'mod_coursework');
    }

    /**
     * @throws coding_exception
     */
    protected function add_start_date_field() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('date_time_selector',
                                 'startdate',
                                 get_string('startdate', 'coursework'),
                                 array('optional' => true, 'disabled' => true)
        );

        $timestamp = strtotime('+2 weeks');

        $default = array(
            'day' => date('j', $timestamp),
            'month' => date('n', $timestamp),
            'year' => date('Y', $timestamp),
            'hour' => date('G', $timestamp),
            'minute' => date('i', $timestamp),
        );

        $moodle_form->setDefault('startdate', $default);
        $moodle_form->addHelpButton('startdate', 'startdate', 'mod_coursework');
    }

    /**
     * @throws coding_exception
     */
    protected function add_allow_early_finalisation_field() {
        $moodle_form =& $this->_form;
        $options = array( 0 => get_string('no'), 1 => get_string('yes'));
        $moodle_form->addElement('select',
                                 'allowearlyfinalisation',
                                 get_string('allowearlyfinalisation', 'mod_coursework'),$options);
        $moodle_form->setType('allowearlyfinalisation', PARAM_INT);
    }


    /**
     * @throws coding_exception
     */
    protected function add_group_submission_header(){

        $moodle_form =& $this->_form;

        $moodle_form->addElement('header', 'group_submission', get_string('groupsubmissionsettings', 'mod_coursework'));
        // We want it expanded by default
        $moodle_form->setExpanded('group_submission');


    }

    /**
     * @throws coding_exception
     */
    protected function add_use_groups_field() {
        $moodle_form =& $this->_form;

        $options = array( 0 => get_string('no'), 1 => get_string('yes'));
        $moodle_form->addElement('select', 'use_groups', get_string('use_groups', 'mod_coursework'),$options);
        $moodle_form->addHelpButton('use_groups', 'use_groups', 'mod_coursework');
    }

    /**
     * @throws coding_exception
     */
    protected function add_grouping_field() {
        global $COURSE, $DB;

        $moodle_form =& $this->_form;

        $groups_options_result = $DB->get_records('groupings', array('courseid'=>$COURSE->id), 'name', 'id, name');
        $groups_options = array();
        if ($groups_options_result !== false) {
            foreach ($groups_options_result as $result) {
                $groups_options[$result->id] = $result->name;
            }
        }

        // Not calling it groupingid as this conflicts with the groupingid field in the common module
        // settings.
        $default_groups_options = array(0 => 'Use all groups');
        $groups_options = $default_groups_options + $groups_options;
        $moodle_form->addElement('select',
                                 'grouping_id',
                                 get_string('grouping_id', 'mod_coursework'),
                                 $groups_options);
        $moodle_form->addHelpButton('grouping_id', 'grouping_id', 'mod_coursework');
        $moodle_form->disabledIf('grouping_id', 'use_groups', 'eq', 0);
    }

    /**
     * @throws coding_exception
     */
    protected function add_general_header() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('header', 'generalstuff', get_string('general', 'form'));
    }

    /**
     * @throws coding_exception
     */
    protected function add_file_types_field() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('text',
                                 'filetypes',
                                 get_string('filetypes', 'coursework'),
                                 array('placeholder' => 'e.g. doc, docx, txt, rtf'));
        $moodle_form->addHelpButton('filetypes', 'filetypes', 'mod_coursework');
        $moodle_form->setType('filetypes', PARAM_TEXT);
        $moodle_form->disabledIf('filetypes', 'use_turnitin', 'eq', '1');
    }

    /**
     * @throws coding_exception
     */
    protected function add_max_file_size_field() {
        global $CFG, $COURSE;

        $moodle_form =& $this->_form;

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        //$choices[0] = get_string('maximumupload') . ' (' . display_size($COURSE->maxbytes) . ')';
        $choices[0] = get_string('maximumupload'). ' set in course';
        $moodle_form->addElement('select',
                                 'maxbytes',
                                 get_string('maximumsize', 'coursework'),
                                 $choices);
        $moodle_form->setDefault('maxbytes', $CFG->coursework_maxbytes);
       /* $moodle_form->addElement('static',
                                 'maxbyteslabel',
                                 '',
                                 get_string('maximumsizelabel', 'coursework'));*/
        $moodle_form->addHelpButton('maxbytes','maximumsize','mod_coursework');
        $moodle_form->disabledIf('maxbytes', 'use_turnitin', 'eq', '1');

        $moodle_form->closeHeaderBefore('submissiontype');

    }

    /**
     * @throws coding_exception
     */
    protected function add_number_of_files_field() {

        $moodle_form =& $this->_form;

        // Maximum number of files:
        $choices = array(1 => 1,
                         2 => 2,
                         3 => 3,
                         4 => 4,
                         5 => 5,
                         6 => 6,
                         7 => 7,
                         8 => 8,
                         9 => 9,
                         10 => 10);
        $moodle_form->addElement('select',
                                 'maxfiles',
                                 get_string('maxfiles', 'coursework'),
                                 $choices);
        $moodle_form->setDefault('maxfiles', 1);
        $moodle_form->setType('maxfiles', PARAM_INT);
        $moodle_form->addHelpButton('maxfiles','maxfiles','mod_coursework');
        $moodle_form->disabledIf('maxfiles', 'use_turnitin', 'eq', '1');

    }

    /**
     * @throws coding_exception
     */
    protected function add_marking_workflow_header() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('header', 'markingworkflow', get_string('markingworkflow', 'mod_coursework'));
    }

    /**
     * @param $moodle_form
     * @throws coding_exception
     */
    protected function add_number_of_initial_assessors_field() {

        $moodle_form =& $this->_form;
        $courseworkid = $this->get_coursework_id();

        $multi_options = array(
            // Don't want to give the option for 0!
            1 => 1,
            2 => 2,
            3 => 3
        );
        // Remove all options lower than the current maximum number of feedbacks that any student has.
        $currentmaxfeedbacks = coursework_get_current_max_feedbacks($courseworkid);
        if ($currentmaxfeedbacks) {
            foreach ($multi_options as $key => $option) {
                if ($key < $currentmaxfeedbacks) {
                    unset($multi_options[$key]);
                }
            }
        }
        $moodle_form->addElement('select',
                                 'numberofmarkers',
                                 get_string('numberofmarkers', 'mod_coursework'),
                                 $multi_options);
        $moodle_form->addHelpButton('numberofmarkers', 'numberofmarkers', 'mod_coursework');
        $moodle_form->setDefault('numberofmarkers', 1);
    }

    /**
     * @return int
     * @throws coding_exception
     */
    protected function get_coursework_id() {
        $upcmid = optional_param('update', -1, PARAM_INT);
        $cm = get_coursemodule_from_id('coursework', $upcmid);
        $courseworkid = 0;
        if ($cm) {
            $courseworkid = $cm->instance;
            return $courseworkid;
        }
        return $courseworkid;
    }

    /**
     * @throws coding_exception
     */
    protected function add_enable_allocation_field() {
        $moodle_form =& $this->_form;

        $options = array( 0 => get_string('no'), 1 => get_string('yes'));
        $moodle_form->addElement('select', 'allocationenabled', get_string('allocationenabled', 'mod_coursework'),$options);
        $moodle_form->addHelpButton('allocationenabled', 'allocationenabled', 'mod_coursework');
    }

    /**
     * @throws coding_exception
     */
    protected function add_assessor_allocation_strategy_field_rdb() {
        $moodle_form =& $this->_form;

        $options = mod_coursework\allocation\manager::get_allocation_classnames();

        $radioarray = array();
        $keys = array_keys($options);

        foreach ($keys as $key) {
            $radioarray[] =& $moodle_form->createElement('radio', 'assessorallocationstrategy', '',$options[$key], $key, '');
        }
        $moodle_form->addGroup($radioarray, 'radioarray',  get_string('assessorallocationstrategy', 'mod_coursework'), array(' '), false);
        $moodle_form->addHelpButton('radioarray', 'assessorallocationstrategy', 'mod_coursework');
        $moodle_form->disabledIf('radioarray', 'allocationenabled', 'eq', 0);
    }

    protected function add_assessor_allocation_strategy_field_checkboxes() {
        $moodle_form =& $this->_form;

        $options = mod_coursework\allocation\manager::get_allocation_classnames();

        $moodle_form->addElement('select', 'assessorallocationstrategy', get_string('assessorallocationstrategy', 'mod_coursework'),$options);
        $moodle_form->addHelpButton('assessorallocationstrategy', 'assessorallocationstrategy', 'mod_coursework');
        $moodle_form->disabledIf('assessorallocationstrategy', 'allocationenabled', 'eq', 0);
    }

    /**
     * @throws coding_exception
     */
    protected function add_blind_marking_header() {

        $moodle_form =& $this->_form;

        $moodle_form->addElement('header', 'anonymity', get_string('blindmarking', 'mod_coursework'));
        $moodle_form->addElement('html', '<div class ="blind_marking_info">');
        $moodle_form->addElement('html',
            get_string('anonymitydescription', 'mod_coursework'));
        $moodle_form->addElement('html', '</div>');

    }

    /**
     * @throws coding_exception
     */
    protected function add_assessor_anonymity_header(){
        $moodle_form =& $this->_form;

        $moodle_form->addElement('header', 'assessoranonymityheader', get_string('assessoranonymity', 'mod_coursework'));
        $moodle_form->addElement('html', '<div class ="assessor_anonymity_info">');
        $moodle_form->addElement('html',
            get_string('assessoranonymity_desc', 'mod_coursework'));
        $moodle_form->addElement('html', '</div>');
    }

    /**
     * @throws coding_exception
     */
    protected function add_enable_blind_marking_field() {
        global $CFG;

        $moodle_form =& $this->_form;

        $options = array( 0 => get_string('no'), 1 => get_string('yes'));
        $moodle_form->addElement('select', 'blindmarking', get_string('blindmarking', 'mod_coursework'),$options);
        $moodle_form->addHelpButton('blindmarking', 'blindmarking', 'mod_coursework');
        $moodle_form->setDefault('blindmarking', $CFG->coursework_blindmarking);
        $moodle_form->disabledIf('blindmarking', 'submission_exists', 'eq', 1);

        //disable blindmarking if forceblindmarking is enabled, process data for DB in get_data()
        if ($this->forceblindmarking() == 1) {
            $moodle_form->addElement('hidden','forceblindmarking', $this->forceblindmarking());
            $moodle_form->setType('forceblindmarking', PARAM_INT);
            $moodle_form->disabledIf('blindmarking', 'forceblindmarking', 'eq', 1);
            $moodle_form->addElement('static', 'forceblindmarking_explanation', '', get_string('forcedglobalsetting', 'mod_coursework'));
        }
    }

    /**
     * @throws coding_exception
     */
    protected function add_enable_assessor_anonymity_field(){
        global $CFG;

        $moodle_form =& $this->_form;
        $options = array(0 => get_string('no'), 1 => get_string('yes'));
        $moodle_form->addElement('select', 'assessoranonymity', get_string('assessoranonymity', 'mod_coursework'),$options);
        $moodle_form->addHelpButton('assessoranonymity', 'assessoranonymity', 'mod_coursework');
        $moodle_form->setDefault('assessoranonymity', $CFG->coursework_assessoranonymity);

    }


    /**
     * @throws coding_exception
     */
    protected function add_feedback_header() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('header', 'feedbacktypes', get_string('feedbacktypes', 'mod_coursework'));
    }

    /**
     * @throws coding_exception
     */
    protected function add_individual_feedback_release_date_field() {

        global $CFG;

        $moodle_form =& $this->_form;

        $timestamp = strtotime('+' . $CFG->coursework_individualfeedback . ' weeks');

        $default = array(
            'day' => date('j', $timestamp),
            'month' => date('n', $timestamp),
            'year' => date('Y', $timestamp),
            'hour' => date('G', $timestamp),
            'minute' => date('i', $timestamp),
            'enabled' => 1,
        );

        $moodle_form->addElement('date_time_selector',
                                 'individualfeedback',
                                 get_string('individualfeedback', 'coursework'),
                                 array('optional' => true));
        $moodle_form->setDefault('individualfeedback', $default);
        $moodle_form->addHelpButton('individualfeedback', 'individualfeedback', 'mod_coursework');
//        $moodle_form->addRule(array('individualfeedback', 'deadline'), get_string('must_be_after_dealdine', 'mod_coursework'), 'compare', 'gt');
    }

    /**
     * @throws coding_exception
     */
    protected function add_email_individual_feedback_notification_field(){
        global $CFG;

        $moodle_form =& $this->_form;
        $options = array(0 => get_string('no'), 1 => get_string('yes'));
        $moodle_form->addElement('select', 'feedbackreleaseemail', get_string('feedbackreleaseemail', 'mod_coursework'),$options);
        $moodle_form->addHelpButton('feedbackreleaseemail', 'feedbackreleaseemail', 'mod_coursework');
        $moodle_form->setDefault('feedbackreleaseemail', $CFG->coursework_feedbackreleaseemail);
    }

    /**
     * @throws coding_exception
     */
    protected function add_general_feedback_release_date_field() {
        global $CFG;

        $moodle_form =& $this->_form;

        $moodle_form->addElement('date_time_selector',
                                 'generalfeedback',
                                 get_string('generalfeedbackreleasedate', 'coursework'),
                                 array('optional' => true, 'disabled' => true));
        // We have a field which is sometimes disabled. Disabled fields are not sent back to the
        // server, so the default is used.
        $timestamp = strtotime('+' . $CFG->coursework_generalfeedback . ' weeks');

        $default = array(
            'day' => date('j', $timestamp),
            'month' => date('n', $timestamp),
            'year' => date('Y', $timestamp),
            'hour' => date('G', $timestamp),
            'minute' => date('i', $timestamp),
        );
        $moodle_form->setDefault('generalfeedback', $default);
        $moodle_form->addHelpButton('generalfeedback', 'generalfeedback', 'mod_coursework');
    }


    /**
     */
    protected function add_plagiarism_elements_to_form() {
        global $COURSE, $CFG;

        $moodle_form =& $this->_form;

        $course_context = context_course::instance($COURSE->id);
        $version_required = '2016091401'; // version of plagiarism_turnitin modified for courseowrk
        $plagiarismsettings = (array)get_config('plagiarism');

        // if plagiarism enabled and modified version of plagiarism installed
        if (!empty($CFG->enableplagiarism) && !empty($plagiarismsettings['turnitin_use'])) {
            if (get_config('plagiarism_turnitin', 'version') >= $version_required) {
                plagiarism_get_form_elements_module($moodle_form, $course_context, 'mod_coursework');

            } else {
                $moodle_form->addElement('header', 'plugin_header', get_string('turnitinpluginsettings', 'plagiarism_turnitin'));
                $moodle_form->addElement('html', '<div class ="plagiarism_tii_version">' .
                    get_string('tii_plagiarism_version_warning', 'mod_coursework', $version_required) . '</div>');
            }
        }
    }

    /**
     */
    protected function add_tweaks_to_standard_grading_form_elements() {
        $moodle_form =& $this->_form;

        $moodle_form->addHelpButton('grade', 'grade', 'mod_coursework');
        $moodle_form->setExpanded('modstandardgrade');

        // Don't think this belongs here...
//        $options = array(0 => get_string('no'), 1 => get_string('yes'));
//        $moodle_form->addElement('select', 'automaticagreement', get_string('automaticagreement', 'mod_coursework'),$options);
//        $moodle_form->addHelpButton('automaticagreement', 'automaticagreement', 'mod_coursework');
//        $moodle_form->setDefault('automaticagreement',0);
//        $moodle_form->disabledIf('automaticagreement', 'numberofmarkers', 'eq', '1');
//
//        $moodle_form->addElement('text', 'automaticagreementrange', get_string('automaticagreementrange', 'mod_coursework'),array('size'=>3));
//        $moodle_form->addHelpButton('automaticagreementrange', 'automaticagreementrange', 'mod_coursework');
//        $moodle_form->setDefault('automaticagreementrange',0);
//        $moodle_form->disabledIf('automaticagreementrange', 'automaticagreement', 'eq', '0');

    }

    /**
     */
    protected function set_form_attributes() {
        $moodle_form =& $this->_form;

        $moodle_form->_attributes['name'] = 'ocm_update_form';
    }

    protected function add_turnitin_files_settings_waring() {
        $this->_form->addElement('html', '<div class ="notification_tii">');
        $this->_form->addElement('html',
                                 get_string('turnitinfilesettingswarning', 'mod_coursework'));
        $this->_form->addElement('html', '</div>');
    }

    private function add_allow_late_submissions_field() {
        global $CFG;
        $moodle_form =& $this->_form;
        $options = array( 0 => get_string('no'), 1 => get_string('yes'));
        $moodle_form->addElement('select',
                                 'allowlatesubmissions',
                                 get_string('allowlatesubmissions', 'mod_coursework'),$options);
        $moodle_form->setType('allowlatesubmissions', PARAM_INT);
        $moodle_form->setDefault('allowlatesubmissions', $CFG->coursework_allowlatesubmissions);

    }


    protected function add_all_feedbacks_field(){

        $moodle_form =& $this->_form;

        $options = array( 0 => get_string('no'), 1 => get_string('yes'));
        $moodle_form->addElement('select',
                                 'showallfeedbacks',
                                 get_string('showallfeedbacks', 'mod_coursework'),$options);
        $moodle_form->setDefault('showallfeedbacks', 0);
        $moodle_form->disabledIf('showallfeedbacks', 'numberofmarkers', 'eq', 1);
        $moodle_form->addHelpButton('showallfeedbacks', 'showallfeedbacks', 'mod_coursework');
    }

    /**
     * @throws coding_exception
     */
    private function add_enable_sampling_checkbox() {
        $moodle_form =& $this->_form;

        $moodle_form->addElement('selectyesno', 'samplingenabled', get_string('samplingenabled', 'mod_coursework'));
        $moodle_form->addHelpButton('samplingenabled', 'samplingenabled', 'mod_coursework');
        $moodle_form->disabledIf('samplingenabled', 'numberofmarkers', 'eq', 1);

    }

    /**
     * @throws coding_exception
     */
    private function add_view_initial_assessors_grade(){

        $moodle_form =& $this->_form;

        $moodle_form->addElement('selectyesno', 'viewinitialgradeenabled', get_string('viewinitialgradeenabled', 'mod_coursework'));
        $moodle_form->addHelpButton('viewinitialgradeenabled', 'viewinitialgradeenabled', 'mod_coursework');

        $moodle_form->disabledIf('viewinitialgradeenabled', 'numberofmarkers', 'eq', 1);
    }


    private function add_enable_agreed_grade_delay()    {

        global  $CFG;

        $moodle_form =& $this->_form;

        $options    =   array('0'=>get_string('disabled', 'mod_coursework'));
        $options['1800']  =   get_string('timedminutes', 'mod_coursework','30');
        $options['3600']  = get_string('timedhour', 'mod_coursework','1');
        $options['7200']  = get_string('timedhours', 'mod_coursework','2');
        $options['18000']  = get_string('timedhours', 'mod_coursework','5');
        $options['43200']  = get_string('timedhours', 'mod_coursework','12');
        $options['86400']  = get_string('aday', 'mod_coursework');

        $moodle_form->addElement('select', 'gradeeditingtime', get_string('gradeeditingtime', 'mod_coursework'), $options);
        $moodle_form->addHelpButton('gradeeditingtime', 'gradeeditingtime', 'mod_coursework');
        $moodle_form->setDefault('gradeeditingtime', $CFG->coursework_grade_editing);



        $moodle_form->disabledIf('gradeeditingtime', 'numberofmarkers', 'eq', 1);

    }

    private function forceblindmarking(){
        global $CFG;
        return $CFG->coursework_forceblindmarking;

    }

    private function add_extensions_header(){
        $moodle_form =& $this->_form;

        $moodle_form->addElement('header', 'extensions', get_string('extensions', 'mod_coursework'));
    }

    private function add_enable_extensions_field(){
        global $CFG;
        $moodle_form =& $this->_form;

        $options = array( 0 => get_string('no'), 1 => get_string('yes'));
        $moodle_form->addElement('select', 'extensionsenabled', get_string('individual_extension', 'mod_coursework'), $options);
        $moodle_form->addHelpButton('extensionsenabled', 'individual_extension', 'mod_coursework');
        $moodle_form->setDefault('extensionsenabled', $CFG->coursework_individual_extension);
    }

    private function add_automatic_agreement_enabled() {
        $options = array('null' => 'none',
                         'percentage_distance' => 'percentage distance');
        $this->form()->addelement('select',
                                  'automaticagreementstrategy',
                                  get_string('automaticagreementofgrades', 'mod_coursework'),
                                  $options);
        $this->form()->settype('automaticagreementstrategy', PARAM_ALPHAEXT);
        $this->form()->addhelpbutton('automaticagreementstrategy', 'automaticagreement', 'mod_coursework');

        $this->form()->disabledif('automaticagreementstrategy', 'numberofmarkers', 'eq', 1);
        $this->form()->disabledIf('automaticagreementrange', 'automaticagreementstrategy', 'eq', 'null');

        $this->form()->addElement('select',
                                  'automaticagreementrange',
                                  get_string('automaticagreementrange', 'mod_coursework'),
                                  range(0, 100));
        $this->form()->setType('automaticagreementrange', PARAM_INT);
        $this->form()->setDefault('automaticagreementrange', 10);


    }
}
