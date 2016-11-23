<?php

class enrol_gudatabase_observer {

    /**
     * Triggered when a course is reset
     */
    public static function course_reset_ended(\core\event\course_reset_ended $event) {
        global $DB;

        $courseid = $event->courseid;
        $plugin = enrol_get_plugin('gudatabase');

        // Delete the cached entries for the course codes in enrol_gudatabase_codes
        // This might give problems with students logging in to recently reset courses
        // but I don't care - it won't be many
        $DB->delete_records('enrol_gudatabase_codes', array('courseid' => $courseid));
        return;
    }
}
