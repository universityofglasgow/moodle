<?php

namespace mod_coursework\controllers;

use mod_coursework\ability;
use mod_coursework\event\assessable_submitted;
use mod_coursework\exceptions\access_denied;
use mod_coursework\exceptions\late_submission;
use mod_coursework\forms\student_submission_form;
use mod_coursework\mailer;
use mod_coursework\models\coursework;
use mod_coursework\models\submission;
use mod_coursework\models\user;

defined('MOODLE_INTERNAL' || die());

/**
 * Class submissions_controller
 * @package mod_coursework\controllers
 */
class submissions_controller extends controller_base {

    /**
     * @var submission
     */
    protected $submission;

    /**
     * Makes the page where a user can create a new submission.
     *
     * @throws \coding_exception
     * @throws \unauthorized_access_exception
     */
    protected function new_submission() {
        global $USER, $PAGE;

        $user = user::find($USER);
        $validation = false;

        $submission = submission::build(array(
                                                'allocatableid' => $this->params['allocatableid'],
                                                'allocatabletype' => $this->params['allocatabletype'],
                                                'courseworkid' => $this->coursework->id,
                                                'createdby' => $user->id()));

        $ability = new ability($user, $this->coursework);
        if (!$ability->can('new', $submission)) {
            throw new access_denied($this->coursework);
        }

        $this->check_coursework_is_open($this->coursework);

        $urlparams = array('courseworkid' => $this->params['courseworkid']);
        $PAGE->set_url('/mod/coursework/actions/submissions/new.php', $urlparams);

        $path = $this->get_router()->get_path('create submission', array('coursework' => $this->coursework));
        $submit_form = new student_submission_form($path,
                                                    array(
                                                      'coursework' => $this->coursework,
                                                      'submission' => $submission
                                                    ));
        if ($submit_form->is_submitted()) {
            $validation =  $submit_form->validate_defined_fields();
        }
        if ($validation != true) {
            $this->get_page_renderer()->new_submission_page($submit_form, $submission);
            return true;
        }

    }

    /**
     * Receives the form input from the new submission page and saves it.
     */
    protected function create_submission() {
        global $USER, $CFG;

        $coursework_page_url = $this->get_path('coursework', array('coursework' => $this->coursework));
        if ($this->cancel_button_was_pressed()) {
            redirect($coursework_page_url);
        }

        $validated = $this->new_submission();

        if ($validated == true){
            return;
        }

        $submission = new submission();
        $submission->courseworkid = $this->coursework->id;
        $submission->finalised = $this->params['finalised'] ? 1 : 0;
        $submission->allocatableid = $this->params['allocatableid'];
        $submission->createdby = $USER->id;
        $submission->lastupdatedby = $USER->id;
        $submission->allocatabletype = $this->params['allocatabletype'];
        $submission->timesubmitted = time();

        // Automatically finalise any submissions that's past the deadline.
        if ($this->coursework->deadline_has_passed()) {
            $submission->finalised = 1;
        }

        $ability = new ability(user::find($USER), $this->coursework);

        $this->exception_if_late();

        if (!$ability->can('create', $submission)) {
            // if submission already exists, redirect user to cw instead of throwing an error
            if ($this->submissions_exists($submission)){
                redirect($CFG->wwwroot.'/mod/coursework/view.php?id='.$this->coursemodule->id, $ability->get_last_message());
            } else {
                throw new access_denied($this->coursework, $ability->get_last_message());
            }
        }

        $this->check_coursework_is_open($this->coursework);

        $submission->save();

        $files_id = file_get_submitted_draft_itemid('submission_manager');
        $submission->save_files($files_id);

        $context = \context_module::instance($this->coursemodule->id);
        // Trigger assessable_submitted event to show files are complete.
        $params = array(
            'context' => $context,
            'objectid' => $submission->id,
            'other' => array(
                'courseworkid' => $this->coursework->id
            )
        );
        $event = assessable_submitted::create($params);
        $event->trigger();

        $submission->submit_plagiarism();

        if ($submission->finalised) {
            $mailer = new mailer($this->coursework);
            foreach ($submission->get_students() as $student) {
                $mailer->send_submission_receipt($student);
            }

        }

        redirect($coursework_page_url);
    }

    /**
     * Makes the page where a user can edit an existing submission.
     * Might be someone editing the group feedback thing too, so we load based on the submission
     * user, not the current user.
     *
     * @throws \coding_exception
     * @throws \unauthorized_access_exception
     */
    protected function edit_submission() {
        global $USER, $PAGE;

        $submission = submission::find($this->params['submissionid']);
        $validation = false;

        $ability = new ability(user::find($USER), $this->coursework);
        if (!$ability->can('edit', $submission)) {
            throw new access_denied($this->coursework);
        }

        $urlparams = array('submissionid' => $this->params['submissionid']);
        $PAGE->set_url('/mod/coursework/actions/submissions/edit.php', $urlparams);

        $path = $this->get_router()->get_path('update submission', array('submission' => $submission));
        $submit_form = new student_submission_form($path,
                                                   array(
                                                       'coursework' => $this->coursework,
                                                       'submission' => $submission
                                                   ));
        if ($submit_form->is_submitted()) {
            $validation =   $submit_form->validate_defined_fields();
        }

        $submit_form->set_data($submission);

         if ($validation != true) {
            $this->get_page_renderer()->edit_submission_page($submit_form, $submission);
            return true;
         }
  }


    /**
     *
     */
    protected function update_submission() {

        global $USER, $CFG;

        $coursework_page_url = $this->get_path('coursework', array('coursework' => $this->coursework));
        if ($this->cancel_button_was_pressed()) {
            redirect($coursework_page_url);
        }

         $validated =    $this->edit_submission();

        if ($validated == true){
            return;
        }

        $submission = submission::find($this->params['submissionid']);

        $ability = new ability(user::find($USER), $this->coursework);
        $this->exception_if_late();
        if (!$ability->can('update', $submission)) {
            throw new access_denied($this->coursework, $ability->get_last_message());
        }

        $notify_about_finalisation = false;
        $incoming_finalised_setting = $this->params['finalised'] ? 1 : 0;
        if ($incoming_finalised_setting == 1 && $submission->finalised == 0) {
            $notify_about_finalisation = true;
        }
        $submission->finalised = $incoming_finalised_setting;
        $submission->lastupdatedby = $USER->id;
        $submission->timesubmitted = time();

        $submission->save();

        $files_id = file_get_submitted_draft_itemid('submission_manager');
        $submission->save_files($files_id);

        $context = \context_module::instance($this->coursemodule->id);
        // Trigger assessable_submitted event to show files are complete.
        $params = array(
            'context' => $context,
            'objectid' => $submission->id,
            'other' => array(
                'courseworkid' => $this->coursework->id
            )
        );
        $event = assessable_submitted::create($params);
        $event->trigger();


        $submission->submit_plagiarism();

        // Do this here in case we failed to save and an exception was thrown.
        if ($notify_about_finalisation) {
            // Email the user. Best to do this as an event after 2.7 so as to keep the page fast.
            $mailer = new mailer($this->coursework);
            foreach ($submission->get_students() as $student) {
                $mailer->send_submission_receipt($student);
            }
        }

        redirect($coursework_page_url);

    }

    /**
     *
     */
    protected function finalise_submission() {

        global $USER;

        $coursework_page_url = $this->get_path('coursework', array('coursework' => $this->coursework));
        if ($this->cancel_button_was_pressed()) {
            redirect($coursework_page_url);
        }

        $submission = submission::find($this->params['submissionid']);

        $ability = new ability(user::find($USER), $this->coursework);
        if (!$ability->can('finalise', $submission)) {
            throw new access_denied($this->coursework);
        }

        $submission->finalised = 1;
        $submission->save();

        // Email the user. Best to do this as an event after 2.7 so as to keep the page fast.
        $mailer = new mailer($this->coursework);
        foreach ($submission->get_students() as $student) {
            $mailer->send_submission_receipt($student);
        }

        redirect($coursework_page_url);
    }

    protected function prepare_environment() {

        if (!empty($this->params['submissionid'])) {
            $this->submission = submission::find($this->params['submissionid']);
            $this->coursework = $this->submission->get_coursework();

        }

        parent::prepare_environment();
    }

    /**
     * Tells us whether the agree to terms checkbox was used.
     *
     * @return bool
     * @throws \coding_exception
     */
    private function terms_were_agreed_to() {
        return !!optional_param('termsagreed', false, PARAM_INT);
    }

    /**
     * @param coursework $coursework
     * @throws \coding_exception
     * @throws access_denied
     */
    protected function check_coursework_is_open($coursework) {
        if (!$coursework->start_date_has_passed()) {
            throw new access_denied($coursework, get_string('notstartedyet', 'mod_coursework', userdate($coursework->startdate)));
        }
    }

    /**
     * @throws late_submission
     */
    private function exception_if_late() {
        $could_have_submitted = has_capability('mod/coursework:submit', $this->coursework->get_context());
        $deadline_has_passed = $this->coursework->deadline_has_passed();

        if ($could_have_submitted && $deadline_has_passed && !$this->coursework->allow_late_submissions()) {
            throw new late_submission($this->coursework);
        }
    }

    /*
     * param submission $submission
     * return bool true if a matching record exists, else false
     */
    protected function submissions_exists($submission){
        global $DB;

        $sub_exists = $DB->record_exists('coursework_submissions',
                                     array('courseworkid' => $submission->courseworkid,
                                           'allocatableid' => $submission->allocatableid,
                                           'allocatabletype' => $submission->allocatabletype));

        return $sub_exists;
    }
}