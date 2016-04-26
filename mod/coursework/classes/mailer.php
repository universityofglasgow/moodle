<?php

namespace mod_coursework;
use mod_coursework\models\submission;
use mod_coursework\models\user;

/**
 * This is where all emails are sent from. The methods need to be OK to run from a separate cron process,
 * so do not pass in entire objects. Just DB row ids, which the methods then retrieve.
 *
 * @package mod_coursework
 */
class mailer {

    /**
     * @var models\coursework
     */
    protected $coursework;

    /**
     * @param models/coursework $coursework
     */
    public function __construct($coursework) {
        $this->coursework = $coursework;
    }

    /**
     * This ought to only be triggered when the submission is finalised, not when the draft is uploaded.
     *
     * @param user $user
     * @throws \coding_exception
     */
    public function send_submission_receipt($user) {
        global $CFG;

        $submission = $this->coursework->get_user_submission($user);

        $email_data = new \stdClass();
        $email_data->name = $user->name();
        $dateformat = '%a, %d %b %Y, %H:%M';
        $email_data->submittedtime = userdate($submission->time_submitted(), $dateformat);
        $email_data->coursework_name = $this->coursework->name;
        $email_data->submissionid = $submission->id;

        $subject = get_string('save_email_subject', 'coursework');
        $text_body = get_string('save_email_text', 'coursework', $email_data);
        $html_body = get_string('save_email_html', 'coursework', $email_data);

        // New approach.
        $eventdata = new \stdClass();
        $eventdata->component = 'mod_coursework';
        $eventdata->name = 'submission_receipt';
        $eventdata->userfrom = $user->get_raw_record();
        $eventdata->userto = $user->get_raw_record();
        $eventdata->subject = $subject;
        $eventdata->fullmessage = $text_body;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml = $html_body;
        $eventdata->smallmessage = $text_body;
        $eventdata->notification = 1;
        $eventdata->contexturl = $CFG->wwwroot.'/mod/coursework/view.php?id='.$submission->get_coursework()->get_coursemodule_id();
        $eventdata->contexturlname = 'View your submission here';

        message_send($eventdata);

    }

    /**
     * @param submission $submission
     */
    public function send_late_submission_notification($submission) {
        global $CFG;

        $coursework = $submission->get_coursework();
        $student_or_group = $submission->get_allocatable();
        $recipients = $coursework->initial_assessors($student_or_group);
        foreach ($recipients as $recipient) {

            // New approach.
            $eventdata = new \stdClass();
            $eventdata->component = 'mod_coursework';
            $eventdata->name = 'submission_receipt';
            $eventdata->userfrom = \core_user::get_noreply_user();
            $eventdata->userto = $recipient;
            $eventdata->subject = 'Late submission for '.$coursework->name;
            $message_text =
                'A late submission was just submitted for ' . $student_or_group->type() . ' ' . $student_or_group->name();
            $eventdata->fullmessage = $message_text;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml = $message_text;
            $eventdata->smallmessage = $message_text;
            $eventdata->notification = 1;
            $eventdata->contexturl =
                $CFG->wwwroot . '/mod/coursework/view.php?id=' . $coursework->get_coursemodule_id();
            $eventdata->contexturlname = 'View the submission here';

            message_send($eventdata);
        }
    }


    /**
     * Send feedback notifications to users whose feedback was released
     *
     * @param submission $submission
     * @throws \coding_exception
     */
    public function send_feedback_notification($submission) {
        global $CFG;

        $email_data = new \stdClass();
        $email_data->coursework_name = $this->coursework->name;

        $subject = get_string('feedback_released_email_subject', 'coursework');

        // get a student or all students from a group
        $students = $submission->students_for_gradebook();

        foreach ($students as $student) {
            $student = \mod_coursework\models\user::find($student);

            $email_data->name = $student->name();
            $text_body = get_string('feedback_released_email_text', 'coursework', $email_data);
            $html_body = get_string('feedback_released_email_html', 'coursework', $email_data);

            $eventdata = new \stdClass();
            $eventdata->component = 'mod_coursework';
            $eventdata->name = 'feedback_released';
            $eventdata->userfrom = $student->get_raw_record();
            $eventdata->userto = $student->get_raw_record();
            $eventdata->subject = $subject;
            $eventdata->fullmessage = $text_body;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml = $html_body;
            $eventdata->smallmessage = $text_body;
            $eventdata->notification = 1;
            $eventdata->contexturl = $CFG->wwwroot . '/mod/coursework/view.php?id=' . $submission->get_coursework()->get_coursemodule_id();
            $eventdata->contexturlname = 'View your submission here';

            message_send($eventdata);
        }
    }
}