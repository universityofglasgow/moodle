<?php

namespace mod_coursework\render_helpers\grading_report\cells;
use coding_exception;
use core_user;
use html_writer;
use mod_coursework\ability;
use mod_coursework\grading_table_row_base;
use mod_coursework\models\deadline_extension;
use mod_coursework\models\user;
use pix_icon;

/**
 * Class feedback_cell
 */
class time_submitted_cell extends cell_base {

    /**
     * @param grading_table_row_base $row_object
     * @throws coding_exception
     * @return string
     */
    public function get_table_cell($row_object) {
        global $OUTPUT, $USER;

        $content = '';

        $coursework = $row_object->get_coursework();
        $submission = $row_object->get_submission();

        if ($submission) {

            // If we have groups enabled and this is not the student who submitted the
            // group files, show who did.
            if ($coursework->is_configured_to_have_group_submissions() && !$row_object->has_submission()) {
                $user = core_user::get_user($submission->userid);

                if ($row_object->can_view_username()) {
                    $content .= "Submitted by";
                    $content .= html_writer::empty_tag('br');
                    $content .= $OUTPUT->user_picture($user);
                    $content .= $row_object->get_user_name(true);
                }
                $content .= html_writer::empty_tag('br');
            }

            $time_submitted = $submission->time_submitted();

            $content .= userdate($time_submitted,'%a, %d %b %Y, %H:%M');
            $content .= html_writer::empty_tag('br');

            if ($submission->is_late() && (!$submission->has_extension() || !$submission->submitted_within_extension())) {

                // check if submission has personal deadline
                if ($coursework->personaldeadlineenabled ){
                    $deadline =  $submission->submission_personal_deadline();
                } else { // if not, use coursework default deadline
                    $deadline = $coursework->deadline;
                }

                $deadline = ($submission->has_extension()) ? $submission->extension_deadline() : $deadline;

                $lateseconds = $time_submitted - $deadline;

                $days = floor($lateseconds / 86400);
                $hours = floor($lateseconds / 3600) % 24;
                $minutes = floor($lateseconds / 60) % 60;
                $seconds = $lateseconds % 60;

                $content .= html_writer::start_span('late_submission');
                $content .= get_string('late', 'coursework');
                $content .= ' ('.$days . get_string('timedays', 'coursework') . ', ';
                $content .= $hours . get_string('timehours', 'coursework') . ', ';
                $content .= $minutes . get_string('timeminutes', 'coursework') . ', ';
                $content .= $seconds . get_string('timeseconds', 'coursework') . ')';
                $content .= html_writer::end_span();

            } else {
                $content .= html_writer::span('(' . get_string('ontime', 'mod_coursework') . ')','ontime_submission');
            }

            if ($submission->get_allocatable()->type() == 'group') {
                if ($row_object->can_view_username() || $row_object->is_published()) {
                    $content .= ' by ' . $submission->get_last_submitter()->profile_link();
                }
            }
        } else {

        }


        $new_extension_params = array(
            'allocatableid' => $row_object->get_allocatable()->id(),
            'allocatabletype' => $row_object->get_allocatable()->type(),
            'courseworkid' => $row_object->get_coursework()->id,
        );
        $extension = deadline_extension::find_or_build($new_extension_params);
        $ability = new ability(user::find($USER), $row_object->get_coursework());

        if ($extension->persisted()) {
            $content .= 'Extension: </br>'.userdate($extension->extended_deadline, '%a, %d %b %Y, %H:%M');
        }


        if ($ability->can('new', $extension) && $coursework->extensions_enabled()) {
            $link = $this->get_router()->get_path('new deadline extension', $new_extension_params);
            $title = 'New extension';
            $content .= $OUTPUT->action_link($link,
                $title,
                null,
                array('class' => 'new_deadline_extension'));

        } else if ($ability->can('edit', $extension) && $coursework->extensions_enabled()) {
            $link = $this->get_router()->get_path('edit deadline extension', array('id' => $extension->id));
            $icon = new pix_icon('edit', 'Edit extension', 'coursework');

            $content .= $OUTPUT->action_icon($link,
                                             $icon,
                                             null,
                                             array('class' => 'edit_deadline_extension'));
        }

        return $this->get_new_cell_with_class($content);
    }

    /**
     * @param array $options
     * @return string
     */
    public function get_table_header($options = array()) {

        //adding this line so that the sortable heading function will make a sortable link unique to the table
        //if tablename is set
        $tablename  =   (!empty($options['tablename']))  ? $options['tablename']  : ''  ;

        return $this->helper_sortable_heading(get_string('tableheadsubmissiondate', 'coursework'),
                                              'timesubmitted',
                                              $options['sorthow'],
                                              $options['sortby'],
                                              $tablename);
    }

    /**
     * @return string
     */
    public function get_table_header_class(){
        return 'tableheaddate';
    }

    /**
     * @return string
     */
    public function header_group() {
        return 'submission';
    }
}